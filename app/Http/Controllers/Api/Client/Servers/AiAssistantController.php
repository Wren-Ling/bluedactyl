<?php

namespace Pterodactyl\Http\Controllers\Api\Client\Servers;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Pterodactyl\Facades\Activity;
use Pterodactyl\Models\Permission;
use Pterodactyl\Models\Server;
use Pterodactyl\Exceptions\DisplayException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;
use Pterodactyl\Services\Ai\AiConversationService;
use Pterodactyl\Services\Ai\AiSettingsService;
use Pterodactyl\Exceptions\Http\HttpForbiddenException;

/**
 * Serves the in-panel AI assistant.
 *
 * The browser never sees the upstream key or talks to the provider directly:
 * it sends a conversation to this endpoint and receives text back. All upstream
 * credentials stay in the encrypted settings table.
 */
class AiAssistantController extends ClientApiController
{
    public function __construct(
        private AiSettingsService $settings,
        private AiConversationService $conversation,
    ) {
        parent::__construct();
    }

    /**
     * Report whether the assistant is available for this server.
     */
    public function config(Request $request, Server $server): JsonResponse
    {
        $this->assertAssistantPermission($request, $server);

        return new JsonResponse(array_merge($this->settings->toPublicArray(), [
            'server' => $server->uuid,
        ]));
    }

    /**
     * Non-streaming conversation turn.
     */
    public function chat(Request $request, Server $server): JsonResponse
    {
        $this->assertAssistantPermission($request, $server);

        $request->validate([
            'messages' => ['required', 'array', 'min:1', 'max:40'],
            'messages.*.role' => ['required', 'string', 'in:user,assistant'],
            'messages.*.content' => ['required', 'string', 'max:100000'],
            'console' => ['nullable', 'array', 'max:1000'],
            'console.*' => ['nullable', 'string', 'max:8192'],
        ]);

        try {
            $reply = $this->conversation->reply(
                $server,
                $request->input('messages', []),
                AiConversationService::ACTION_CHAT,
                $request->input('console'),
            );
        } catch (DisplayException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            Log::error('AI assistant chat failed', [
                'server' => $server->uuid,
                'error' => $exception->getMessage(),
            ]);

            throw new DisplayException('The AI assistant request failed: ' . $exception->getMessage());
        }

        Activity::event('server:ai.chat')
            ->property(['messages' => count($request->input('messages', []))])
            ->log();

        return new JsonResponse(['reply' => $reply]);
    }

    /**
     * Streaming conversation turn over server-sent events.
     *
     * Every frame is a JSON object so the client can distinguish deltas from
     * the terminal error frame without guessing.
     */
    public function stream(Request $request, Server $server): StreamedResponse
    {
        $this->assertAssistantPermission($request, $server);

        $request->validate([
            'messages' => ['required', 'array', 'min:1', 'max:40'],
            'messages.*.role' => ['required', 'string', 'in:user,assistant'],
            'messages.*.content' => ['required', 'string', 'max:100000'],
            'console' => ['nullable', 'array', 'max:1000'],
            'console.*' => ['nullable', 'string', 'max:8192'],
        ]);

        $messages = $request->input('messages', []);
        $console = $request->input('console');
        $action = $request->input('action') === AiConversationService::ACTION_CRASH_ANALYSIS
            ? AiConversationService::ACTION_CRASH_ANALYSIS
            : AiConversationService::ACTION_CHAT;

        return response()->stream(function () use ($server, $messages, $console, $action) {
            $send = function (array $payload) {
                echo 'data: ' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\n";

                if (function_exists('ob_get_level') && ob_get_level() > 0) {
                    @ob_flush();
                }

                @flush();
            };

            try {
                $this->conversation->streamReply(
                    $server,
                    $messages,
                    $action,
                    fn (string $delta) => $send(['type' => 'delta', 'text' => $delta]),
                    is_array($console) ? $console : null,
                );

                $send(['type' => 'done']);
            } catch (DisplayException $exception) {
                $send(['type' => 'error', 'message' => $exception->getMessage()]);
            } catch (Throwable $exception) {
                Log::error('AI assistant stream failed', [
                    'server' => $server->uuid,
                    'error' => $exception->getMessage(),
                ]);

                $send(['type' => 'error', 'message' => 'The AI assistant request failed.']);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Require the subuser permission that gates the assistant.
     *
     * The server owner and root admins short-circuit this. File reads are
     * additionally limited by the daemon, which only exposes the server's own
     * data directory, so a granted permission cannot reach host files.
     *
     * @param  string|null  $extraPermission  an additional native permission the
     *                                        assistant must not be able to bypass
     *                                        (file.read, file.read-content)
     */
    private function assertAssistantPermission(Request $request, Server $server, ?string $extraPermission = null): void
    {
        $user = $request->user();

        if ($user->id === $server->owner_id || $user->root_admin) {
            return;
        }

        if (!$user->can(Permission::ACTION_AI_USE, $server)) {
            throw new HttpForbiddenException('You do not have permission to use the AI assistant on this server.');
        }

        // The assistant must never widen what the user can already see: a
        // subuser who cannot list or read files through the file manager must
        // not be able to read them through the assistant either.
        if ($extraPermission !== null && !$user->can($extraPermission, $server)) {
            throw new HttpForbiddenException('You do not have permission to read this server\'s files.');
        }
    }

    /**
     * Diagnose an unexpected stop.
     */
    public function crashAnalysis(Request $request, Server $server): JsonResponse
    {
        $this->assertAssistantPermission($request, $server);

        if (!$this->settings->allowCrashAnalysis()) {
            throw new DisplayException('Crash analysis is disabled on this panel.');
        }

        $request->validate([
            'console' => ['nullable', 'array', 'max:1000'],
            'console.*' => ['nullable', 'string', 'max:8192'],
        ]);

        try {
            $reply = $this->conversation->analyzeCrash($server, $request->input('console', []));
        } catch (DisplayException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            Log::error('AI crash analysis failed', [
                'server' => $server->uuid,
                'error' => $exception->getMessage(),
            ]);

            throw new DisplayException('Crash analysis failed: ' . $exception->getMessage());
        }

        Activity::event('server:ai.crash-analysis')->log();

        return new JsonResponse(['reply' => $reply]);
    }

    /**
     * Read a directory so the model (and the user) can see the server files.
     */
    public function directory(Request $request, Server $server): JsonResponse
    {
        $this->assertAssistantPermission($request, $server, Permission::ACTION_FILE_READ);

        $request->validate([
            'directory' => ['nullable', 'string', 'max:512'],
        ]);

        try {
            $result = $this->conversation->listDirectory($server, (string) $request->input('directory', '/'));
        } catch (DisplayException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new DisplayException('Could not read that directory: ' . $exception->getMessage());
        }

        return new JsonResponse($result);
    }

    /**
     * Read a text file so it can be attached to the conversation.
     */
    public function readFile(Request $request, Server $server): JsonResponse
    {
        $this->assertAssistantPermission($request, $server, Permission::ACTION_FILE_READ_CONTENT);

        $request->validate([
            'file' => ['required', 'string', 'max:512'],
        ]);

        try {
            $contents = $this->conversation->readFile($server, (string) $request->input('file'));
        } catch (Throwable $exception) {
            throw new DisplayException('Could not read that file: ' . $exception->getMessage());
        }

        Activity::event('server:ai.file-read')->property('file', $request->input('file'))->log();

        return new JsonResponse(['contents' => $contents]);
    }
}
