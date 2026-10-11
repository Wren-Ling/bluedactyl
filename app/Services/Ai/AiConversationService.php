<?php

namespace Pterodactyl\Services\Ai;

use Pterodactyl\Models\Server;
use Pterodactyl\Exceptions\DisplayException;

/**
 * Turns a server plus a chat history into an upstream conversation.
 *
 * The system prompt, the server facts and the live console tail are assembled
 * here so that every entry point (console panel, crash analysis, ad hoc
 * requests) gets the same grounding without duplicating prompt plumbing.
 */
class AiConversationService
{
    /**
     * Actions the client may ask the assistant to perform on the server.
     */
    public const ACTION_CHAT = 'chat';
    public const ACTION_CRASH_ANALYSIS = 'crash_analysis';
    public const ACTION_FILE_LIST = 'file_list';
    public const ACTION_FILE_READ = 'file_read';

    private const DEFAULT_SYSTEM_PROMPT = <<<'PROMPT'
You are the AI assistant built into a Pterodactyl game server panel. You help the panel user understand their server.

You may be given the server configuration, recent console output, directory listings and file contents. Ground every answer in that material: quote the specific log lines or configuration values you are relying on, and say plainly when the material does not contain the answer instead of guessing.

When diagnosing a crash, work from the evidence outward: name the failing component, explain the cause, then give concrete steps in the order they should be performed. Prefer commands and configuration changes that fit the server's actual software and version.

Answers appear in a narrow side panel. Lead with the conclusion, keep prose tight, and use short code blocks for commands and file paths. Do not claim to have changed, restarted or inspected anything you were not given; you have read-only visibility.
PROMPT;

    public function __construct(
        private AiSettingsService $settings,
        private AiClient $client,
        private ServerContextService $context,
    ) {}

    /**
     * Build the system prompt for an action.
     */
    public function systemPrompt(string $action, Server $server, ?string $consoleTail = null): string
    {
        $base = $this->settings->systemPrompt() ?: self::DEFAULT_SYSTEM_PROMPT;

        $sections = [$base];

        $sections[] = "## Server\n" . $this->context->describeServer($server);

        if ($action === self::ACTION_CRASH_ANALYSIS) {
            $sections[] = <<<'PROMPT'
## Task
The server has stopped unexpectedly. Diagnose the cause and give the user a fix.

Structure the reply as:
1. **Cause** — one or two sentences naming the root cause, citing the log line that shows it.
2. **Why** — a brief explanation of what triggered it.
3. **Fix** — numbered, concrete steps. Include the exact commands, configuration keys or file paths involved.
4. **Prevention** — only when there is a durable change worth making.

If the evidence points to a plugin, mod or dependency, name it and give the version interaction you see. If the logs are inconclusive, say which additional file or setting would settle it.
PROMPT;
        }

        if ($consoleTail !== null && trim($consoleTail) !== '') {
            $sections[] = "## Recent console output\n```\n" . trim($consoleTail) . "\n```";
        }

        return implode("\n\n", $sections);
    }

    /**
     * Run a conversation turn and return the reply as a single string.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     *
     * @throws DisplayException
     */
    public function reply(Server $server, array $messages, string $action = self::ACTION_CHAT, ?array $consoleLines = null): string
    {
        $this->assertUsable();

        $messages = $this->normalizeMessages($messages);
        $consoleTail = $this->consoleTail($consoleLines);

        return $this->client->chat(
            $messages,
            $this->systemPrompt($action, $server, $consoleTail),
            null,
        );
    }

    /**
     * Run a conversation turn, forwarding each text fragment to $onDelta.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @param  callable(string): void  $onDelta
     *
     * @throws DisplayException
     */
    public function streamReply(
        Server $server,
        array $messages,
        string $action,
        callable $onDelta,
        ?array $consoleLines = null,
    ): string {
        $this->assertUsable();

        $messages = $this->normalizeMessages($messages);
        $consoleTail = $this->consoleTail($consoleLines);

        return $this->client->stream(
            $messages,
            $this->systemPrompt($action, $server, $consoleTail),
            null,
            false,
            $onDelta,
        );
    }

    /**
     * Assemble the crash report and ask for a diagnosis.
     *
     * @param  string[]  $consoleLines
     */
    public function analyzeCrash(Server $server, array $consoleLines): string
    {
        $this->assertUsable();

        $report = $this->context->buildCrashReport($server, $consoleLines);

        $crashReport = $this->context->readLatestCrashReport($server);
        if ($crashReport !== null) {
            $report .= "\n\n## Latest crash report\n" . $crashReport;
        }

        $messages = [[
            'role' => 'user',
            'content' => "My server stopped unexpectedly. Please diagnose it.\n\n" . $report,
        ]];

        return $this->client->chat(
            $messages,
            $this->systemPrompt(self::ACTION_CRASH_ANALYSIS, $server, null),
            null,
        );
    }

    /**
     * Directory listing formatted for display in the panel.
     *
     * @return array{path: string, entries: array<int, array{name: string, is_file: bool, size: int, modified_at: ?string}>}
     */
    public function listDirectory(Server $server, string $path): array
    {
        $description = $this->context->describeDirectory($server, $path);

        // Reuse the same listing for the structured view the frontend renders.
        $entries = [];
        foreach (explode("\n", $description) as $index => $line) {
            if ($index === 0 || $line === '' || str_starts_with($line, '(listing')) {
                continue;
            }

            $parts = preg_split('/\s+/', $line, 3);
            if (count($parts) < 3) {
                continue;
            }

            $entries[] = [
                'is_file' => $parts[0] === '-',
                'size' => $this->parseSize($parts[1]),
                'name' => $parts[2],
                'modified_at' => null,
            ];
        }

        return ['path' => $path, 'entries' => $entries];
    }

    /**
     * Render a file read result for display.
     */
    public function readFile(Server $server, string $path): string
    {
        return $this->context->describeFile($server, $path);
    }

    private function assertUsable(): void
    {
        if (!$this->settings->isEnabled()) {
            throw new DisplayException('The AI assistant is not enabled on this panel.');
        }

        if (!$this->settings->isUsable()) {
            throw new DisplayException(
                'The AI assistant is not fully configured. An administrator must finish the setup in the admin area.'
            );
        }
    }

    /**
     * Validate and trim an incoming conversation.
     *
     * The input arrives as decoded JSON, so every field is treated as unknown
     * until checked here rather than being trusted from the declared type.
     *
     * @param  array<int, mixed>  $messages
     * @return array<int, array{role: string, content: string}>
     */
    private function normalizeMessages(array $messages): array
    {
        $normalized = [];

        foreach (array_slice($messages, -20) as $message) {
            if (!is_array($message)) {
                continue;
            }

            $role = $message['role'] ?? null;
            $content = $message['content'] ?? null;

            if (!is_string($role) || !in_array($role, ['user', 'assistant'], true)) {
                continue;
            }

            if (!is_string($content) || trim($content) === '') {
                continue;
            }

            $normalized[] = ['role' => $role, 'content' => $this->clamp($content)];
        }

        if ($normalized === []) {
            throw new DisplayException('A message is required.');
        }

        return $normalized;
    }

    /**
     * @param  string[]|null  $lines
     */
    private function consoleTail(?array $lines): ?string
    {
        if ($lines === null || $lines === []) {
            return null;
        }

        $limit = $this->settings->consoleBufferLines();
        $lines = array_map(fn ($line) => (string) $line, $lines);

        if (count($lines) > $limit) {
            $lines = array_slice($lines, -$limit);
        }

        return implode("\n", $lines);
    }

    /**
     * Keep any single message within a sane size so a runaway client cannot
     * balloon a request.
     */
    private function clamp(string $content, int $limit = 60000): string
    {
        return strlen($content) > $limit ? substr($content, -$limit) : $content;
    }

    private function parseSize(string $value): int
    {
        if (!preg_match('/^([\d.]+)([BKMGT])B?$/i', $value, $matches)) {
            return 0;
        }

        $units = ['B' => 1, 'K' => 1024, 'M' => 1048576, 'G' => 1073741824, 'T' => 1099511627776];

        return (int) ((float) $matches[1] * ($units[strtoupper($matches[2])] ?? 1));
    }
}
