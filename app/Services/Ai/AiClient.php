<?php

namespace Pterodactyl\Services\Ai;

use Pterodactyl\Enums\Ai\ChatFormat;
use Pterodactyl\Enums\Ai\Provider;
use Pterodactyl\Exceptions\DisplayException;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\ConnectionException;

/**
 * Speaks every supported upstream dialect.
 *
 * The rest of the panel deals only in a normalized message list; this class is
 * the single place that knows how each vendor shapes its request body and how
 * to pull the assistant text back out of the response.
 */
class AiClient
{
    public function __construct(
        private AiSettingsService $settings,
        private ProviderRouter $router,
    ) {}

    /**
     * Send a conversation and return the assistant reply.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     *
     * @throws DisplayException
     */
    public function chat(array $messages, ?string $systemPrompt = null, ?string $model = null, bool $small = false): string
    {
        $response = $this->request($messages, $systemPrompt, $model, $small);

        return $this->decode($response['format'], $response['body']);
    }

    /**
     * Stream a conversation, invoking $onDelta for each text fragment.
     *
     * Returns the accumulated text. Providers that do not stream cleanly fall
     * back to a single non-streaming call.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @param  callable(string): void  $onDelta
     *
     * @throws DisplayException
     */
    public function stream(array $messages, ?string $systemPrompt, ?string $model, bool $small, callable $onDelta): string
    {
        $provider = $this->settings->provider();
        if (!$provider instanceof Provider) {
            throw new DisplayException('No AI provider is configured.');
        }

        $target = $this->resolveTarget($provider, $model, $small);
        $payload = $this->buildPayload($target['format'], $messages, $systemPrompt, $model, $small);
        $payload['stream'] = true;

        $accumulated = '';

        $stream = $this->openStream($target['url'], $target['format'], $provider, $target['api_key'], $payload);

        while (!feof($stream)) {
            $line = fgets($stream);
            if ($line === false) {
                break;
            }

            $line = trim($line);
            if ($line === '' || !str_starts_with($line, 'data:')) {
                continue;
            }

            $data = trim(substr($line, 5));
            if ($data === '[DONE]') {
                break;
            }

            $decoded = json_decode($data, true);
            if (!is_array($decoded)) {
                continue;
            }

            $delta = $this->extractStreamDelta($target['format'], $decoded);
            if ($delta !== null && $delta !== '') {
                $accumulated .= $delta;
                $onDelta($delta);
            }
        }

        fclose($stream);

        return $accumulated;
    }

    /**
     * List the models the configured provider exposes.
     *
     * @return string[]
     *
     * @throws DisplayException
     */
    public function listModels(): array
    {
        $provider = $this->settings->provider();
        if (!$provider instanceof Provider) {
            throw new DisplayException('No AI provider is configured.');
        }

        if (!$provider->supportsModelListing()) {
            throw new DisplayException("{$provider->label()} does not expose a model listing endpoint. Enter the model name manually.");
        }

        $base = $this->settings->resolvedBaseUrl();
        $path = $provider->modelsPath();

        if (!$base || !$path) {
            throw new DisplayException('A base URL is required before models can be listed.');
        }

        try {
            $client = $this->http($base . '/' . ltrim($path, '/'), $provider->format(), $provider, $this->settings->apiKey());

            if ($provider === Provider::OLLAMA) {
                return $this->extractOllamaModels($client->get($base . $path)->throw()->json() ?? []);
            }

            if ($provider === Provider::GOOGLE_AI_STUDIO) {
                $url = $base . $path . '?key=' . rawurlencode((string) $this->settings->apiKey());

                return $this->extractGeminiModels($client->get($url)->throw()->json() ?? []);
            }

            return $this->extractOpenAiModels($client->get($base . $path)->throw()->json() ?? []);
        } catch (RequestException $exception) {
            throw new DisplayException($this->describeError($exception));
        } catch (ConnectionException $exception) {
            throw new DisplayException('Could not reach the AI provider: ' . $exception->getMessage());
        }
    }

    /**
     * Verify the stored configuration actually works.
     *
     * @throws DisplayException
     */
    public function testConnection(): string
    {
        if (!$this->settings->isEnabled()) {
            throw new DisplayException('The AI assistant is currently disabled.');
        }

        $reply = $this->chat(
            [['role' => 'user', 'content' => 'Reply with exactly: OK']],
            'You are a connection test. Reply with exactly the word OK.',
            $this->settings->modelSmall() ?? $this->settings->model(),
        );

        return trim($reply) === '' ? 'The provider returned an empty response.' : 'Connection succeeded.';
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     *
     * @return array{format: ChatFormat, body: array}
     */
    private function request(array $messages, ?string $systemPrompt, ?string $model, bool $small): array
    {
        $provider = $this->settings->provider();
        if (!$provider instanceof Provider) {
            throw new DisplayException('No AI provider is configured.');
        }

        $target = $this->resolveTarget($provider, $model, $small);
        $payload = $this->buildPayload($target['format'], $messages, $systemPrompt, $model, $small);

        try {
            $response = $this->http($target['url'], $target['format'], $provider, $target['api_key'])
                ->post($target['url'], $payload)
                ->throw();
        } catch (RequestException $exception) {
            throw new DisplayException($this->describeError($exception));
        } catch (ConnectionException $exception) {
            throw new DisplayException('Could not reach the AI provider: ' . $exception->getMessage());
        }

        return ['format' => $target['format'], 'body' => $response->json() ?? []];
    }

    /**
     * Resolve the concrete URL, key placement and dialect for this call.
     *
     * @return array{url: string, format: ChatFormat, api_key: ?string}
     */
    private function resolveTarget(Provider $provider, ?string $model, bool $small): array
    {
        $base = $this->settings->resolvedBaseUrl();

        if (!$base) {
            throw new DisplayException('The AI assistant has no base URL configured.');
        }

        $model ??= $small
            ? ($this->settings->modelSmall() ?? $this->settings->model())
            : $this->settings->model();

        if (!$model) {
            throw new DisplayException('The AI assistant has no model configured.');
        }

        $resolved = $this->router->resolve(
            $provider,
            $base,
            $model,
            $this->settings->endpointOverride(),
            $this->settings->apiKey(),
        );

        return [
            'url' => $resolved['url'],
            'format' => $resolved['format'],
            'api_key' => $this->settings->apiKey(),
        ];
    }

    /**
     * Build the request body for a dialect.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    private function buildPayload(ChatFormat $format, array $messages, ?string $systemPrompt, ?string $model, bool $small): array
    {
        $model ??= $small
            ? ($this->settings->modelSmall() ?? $this->settings->model())
            : $this->settings->model();

        $temperature = $this->settings->temperature();
        $maxTokens = $this->settings->maxTokens();

        return match ($format) {
            ChatFormat::ANTHROPIC_MESSAGES => $this->withoutNulls([
                'model' => $model,
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
                'system' => $systemPrompt,
                'messages' => array_map(
                    fn (array $message) => ['role' => $message['role'], 'content' => $message['content']],
                    $messages,
                ),
            ]),
            ChatFormat::OPENAI_CHAT => $this->withoutNulls([
                'model' => $model,
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
                'messages' => array_merge(
                    $systemPrompt ? [['role' => 'system', 'content' => $systemPrompt]] : [],
                    array_map(
                        fn (array $message) => ['role' => $message['role'], 'content' => $message['content']],
                        $messages,
                    ),
                ),
            ]),
            ChatFormat::OPENAI_RESPONSES => $this->withoutNulls([
                'model' => $model,
                'temperature' => $temperature,
                'max_output_tokens' => $maxTokens,
                'instructions' => $systemPrompt,
                'input' => array_map(
                    fn (array $message) => [
                        'role' => $message['role'],
                        'content' => [['type' => $message['role'] === 'assistant' ? 'output_text' : 'input_text', 'text' => $message['content']]],
                    ],
                    $messages,
                ),
            ]),
            ChatFormat::GEMINI_GENERATE_CONTENT => $this->withoutNulls([
                'systemInstruction' => $systemPrompt ? ['parts' => [['text' => $systemPrompt]]] : null,
                'contents' => array_map(
                    fn (array $message) => [
                        'role' => $message['role'] === 'assistant' ? 'model' : 'user',
                        'parts' => [['text' => $message['content']]],
                    ],
                    $messages,
                ),
                'generationConfig' => [
                    'temperature' => $temperature,
                    'maxOutputTokens' => $maxTokens,
                ],
            ]),
            ChatFormat::OLLAMA_CHAT => $this->withoutNulls([
                'model' => $model,
                'stream' => false,
                'messages' => array_merge(
                    $systemPrompt ? [['role' => 'system', 'content' => $systemPrompt]] : [],
                    array_map(
                        fn (array $message) => ['role' => $message['role'], 'content' => $message['content']],
                        $messages,
                    ),
                ),
                'options' => [
                    'temperature' => $temperature,
                    'num_predict' => $maxTokens,
                ],
            ]),
        };
    }

    /**
     * Drop absent optional fields while keeping falsy values such as temperature 0.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function withoutNulls(array $payload): array
    {
        return array_filter($payload, fn ($value) => $value !== null);
    }

    /**
     * Pull the assistant text out of a non-streaming response body.
     */
    private function decode(ChatFormat $format, array $body): string
    {
        $text = match ($format) {
            ChatFormat::ANTHROPIC_MESSAGES => $this->decodeAnthropic($body),
            ChatFormat::OPENAI_CHAT => $this->decodeOpenAiChat($body),
            ChatFormat::OPENAI_RESPONSES => $this->decodeOpenAiResponses($body),
            ChatFormat::GEMINI_GENERATE_CONTENT => $this->decodeGemini($body),
            ChatFormat::OLLAMA_CHAT => $this->decodeOllama($body),
        };

        if (trim($text) === '') {
            throw new DisplayException('The AI provider returned an empty response.');
        }

        return trim($text);
    }

    private function decodeAnthropic(array $body): string
    {
        $parts = [];
        foreach ($body['content'] ?? [] as $block) {
            if (($block['type'] ?? null) === 'text' && isset($block['text'])) {
                $parts[] = $block['text'];
            }
        }

        return implode("\n", $parts);
    }

    private function decodeOpenAiChat(array $body): string
    {
        $message = $body['choices'][0]['message'] ?? [];

        if (is_string($message['content'] ?? null)) {
            return $message['content'];
        }

        // Some gateways return content as an array of typed parts.
        if (is_array($message['content'] ?? null)) {
            $parts = [];
            foreach ($message['content'] as $part) {
                if (isset($part['text'])) {
                    $parts[] = $part['text'];
                }
            }

            return implode("\n", $parts);
        }

        // Reasoning models may only populate the reasoning channel.
        return (string) ($message['reasoning_content'] ?? $body['choices'][0]['text'] ?? '');
    }

    private function decodeOpenAiResponses(array $body): string
    {
        if (isset($body['output_text']) && is_string($body['output_text'])) {
            return $body['output_text'];
        }

        $parts = [];
        foreach ($body['output'] ?? [] as $item) {
            if (($item['type'] ?? null) !== 'message') {
                continue;
            }

            foreach ($item['content'] ?? [] as $content) {
                if (isset($content['text'])) {
                    $parts[] = $content['text'];
                }
            }
        }

        return implode("\n", $parts);
    }

    private function decodeGemini(array $body): string
    {
        $parts = [];
        foreach ($body['candidates'][0]['content']['parts'] ?? [] as $part) {
            if (isset($part['text'])) {
                $parts[] = $part['text'];
            }
        }

        return implode("\n", $parts);
    }

    private function decodeOllama(array $body): string
    {
        return (string) ($body['message']['content'] ?? $body['response'] ?? '');
    }

    /**
     * Pull a text fragment out of one streamed event.
     */
    private function extractStreamDelta(ChatFormat $format, array $event): ?string
    {
        return match ($format) {
            ChatFormat::ANTHROPIC_MESSAGES => ($event['type'] ?? null) === 'content_block_delta'
                ? ($event['delta']['text'] ?? null)
                : null,
            ChatFormat::OPENAI_CHAT => $event['choices'][0]['delta']['content'] ?? null,
            ChatFormat::OPENAI_RESPONSES => ($event['type'] ?? null) === 'response.output_text.delta'
                ? ($event['delta'] ?? null)
                : null,
            ChatFormat::GEMINI_GENERATE_CONTENT => $event['candidates'][0]['content']['parts'][0]['text'] ?? null,
            ChatFormat::OLLAMA_CHAT => $event['message']['content'] ?? null,
        };
    }

    /**
     * Open a raw stream for SSE parsing.
     *
     * @return resource
     */
    private function openStream(string $url, ChatFormat $format, Provider $provider, ?string $apiKey, array $payload)
    {
        $headers = $this->router->headers($provider, $format, $apiKey);

        $headerLines = [];
        foreach ($headers as $name => $value) {
            $headerLines[] = "{$name}: {$value}";
        }
        $headerLines[] = 'Accept: text/event-stream';

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headerLines),
                'content' => json_encode($payload),
                'timeout' => $this->settings->timeout(),
                'ignore_errors' => true,
            ],
        ]);

        $stream = @fopen($url, 'r', false, $context);

        if ($stream === false) {
            throw new DisplayException('Failed to open a streaming connection to the AI provider.');
        }

        return $stream;
    }

    /**
     * Build a configured HTTP client for a provider.
     */
    private function http(string $url, ChatFormat $format, Provider $provider, ?string $apiKey): PendingRequest
    {
        return Http::withHeaders($this->router->headers($provider, $format, $apiKey))
            ->timeout($this->settings->timeout())
            ->connectTimeout(15)
            ->acceptJson();
    }

    /**
     * Turn a failed upstream call into something an administrator can act on.
     */
    private function describeError(RequestException $exception): string
    {
        $response = $exception->response;
        $status = $response->status();
        $body = $response->json();
        $message = $exception->getMessage();

        foreach (['error.message', 'error', 'message', 'detail'] as $path) {
            $value = data_get($body, $path);
            if (is_string($value) && $value !== '') {
                $message = $value;
                break;
            }
        }

        $prefix = $status ? "The AI provider rejected the request (HTTP {$status})" : 'The AI provider request failed';

        return "{$prefix}: {$message}";
    }

    /**
     * @return string[]
     */
    private function extractOpenAiModels(array $body): array
    {
        return collect($body['data'] ?? [])
            ->pluck('id')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @return string[]
     */
    private function extractOllamaModels(array $body): array
    {
        return collect($body['models'] ?? [])
            ->pluck('name')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @return string[]
     */
    private function extractGeminiModels(array $body): array
    {
        return collect($body['models'] ?? [])
            ->pluck('name')
            ->map(fn (string $name) => str_replace('models/', '', $name))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }
}
