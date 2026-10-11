<?php

namespace Pterodactyl\Services\Ai;

use Pterodactyl\Enums\Ai\Provider;

/**
 * Works out the concrete endpoint and headers for a request.
 *
 * Most providers have a fixed path. A handful (Command Code, OpenCode Zen/Go,
 * GitHub Copilot) expose more than one endpoint and expect the client to pick
 * one from the model name, so the mapping lives here rather than in the client.
 */
class ProviderRouter
{
    /**
     * Model prefixes that live behind a different route on the same provider.
     *
     * @var array<string, array<string, string>>
     */
    private const MODEL_ROUTES = [
        Provider::COMMAND_CODE->value => [
            'claude' => '/v1/messages',
            'gpt' => '/v1/chat/completions',
            'o1' => '/v1/chat/completions',
            'o3' => '/v1/chat/completions',
            'o4' => '/v1/chat/completions',
            'gemini' => '/v1beta/models',
        ],
        Provider::OPENCODE_ZEN->value => [
            'claude' => '/v1/messages',
            'gpt' => '/v1/responses',
            'o1' => '/v1/responses',
            'o3' => '/v1/responses',
            'gemini' => '/v1beta/models',
        ],
        Provider::OPENCODE_GO->value => [
            'claude' => '/v1/messages',
            'gpt' => '/v1/responses',
            'o1' => '/v1/responses',
            'o3' => '/v1/responses',
            'gemini' => '/v1beta/models',
        ],
        Provider::GITHUB_COPILOT->value => [
            'claude' => '/chat/completions',
            'gpt' => '/responses',
            'o1' => '/responses',
            'o3' => '/responses',
        ],
    ];

    /**
     * Build the request target for a provider/model pair.
     *
     * @return array{url: string, format: \Pterodactyl\Enums\Ai\ChatFormat, path: string}
     */
    public function resolve(Provider $provider, string $baseUrl, ?string $model, ?string $endpointOverride = null, ?string $apiKey = null): array
    {
        $path = $endpointOverride ?: $this->pathFor($provider, $model);
        $format = $this->formatFor($provider, $path);

        // Gemini keeps the model inside the path and the action after it.
        if ($format === \Pterodactyl\Enums\Ai\ChatFormat::GEMINI_GENERATE_CONTENT) {
            $path = rtrim($path, '/') . '/' . rawurlencode((string) $model) . ':generateContent';
        }

        $base = rtrim($baseUrl, '/');

        // A path that already starts with the version segment, e.g. /v1beta/models.
        $url = $base . '/' . ltrim($path, '/');

        if ($format === \Pterodactyl\Enums\Ai\ChatFormat::GEMINI_GENERATE_CONTENT && $apiKey) {
            $url .= '?key=' . rawurlencode($apiKey);
        }

        return ['url' => $url, 'format' => $format, 'path' => $path];
    }

    public function pathFor(Provider $provider, ?string $model): string
    {
        $model = strtolower((string) $model);

        if ($provider->routesPerModel() && $model !== '') {
            foreach (self::MODEL_ROUTES[$provider->value] ?? [] as $prefix => $path) {
                if (str_starts_with($model, $prefix) || str_contains($model, $prefix)) {
                    return $path;
                }
            }
        }

        $default = $provider->defaultPath();

        return $default;
    }

    /**
     * The dialect implied by the resolved path.
     *
     * A model-routed provider can legitimately resolve to a different dialect
     * than its configured default (Claude behind an OpenAI-shaped gateway), so
     * the path wins over the provider default.
     */
    public function formatFor(Provider $provider, string $path): \Pterodactyl\Enums\Ai\ChatFormat
    {
        $normalized = '/' . ltrim($path, '/');

        if (str_contains($normalized, ':generatecontent') || str_contains($normalized, '/v1beta/models') || str_contains($normalized, '/v1beta1/models') || str_contains($normalized, 'v1internal')) {
            return \Pterodactyl\Enums\Ai\ChatFormat::GEMINI_GENERATE_CONTENT;
        }

        if (str_contains($normalized, '/messages')) {
            return \Pterodactyl\Enums\Ai\ChatFormat::ANTHROPIC_MESSAGES;
        }

        if (str_contains($normalized, '/responses')) {
            return \Pterodactyl\Enums\Ai\ChatFormat::OPENAI_RESPONSES;
        }

        if (str_contains($normalized, '/api/chat')) {
            return \Pterodactyl\Enums\Ai\ChatFormat::OLLAMA_CHAT;
        }

        if (str_contains($normalized, '/chat/completions') || str_contains($normalized, '/completions')) {
            return \Pterodactyl\Enums\Ai\ChatFormat::OPENAI_CHAT;
        }

        return $provider->format();
    }

    /**
     * Authentication headers for the resolved dialect.
     *
     * Gemini carries the key in the query string, so nothing is returned here.
     *
     * @return array<string, string>
     */
    public function headers(Provider $provider, \Pterodactyl\Enums\Ai\ChatFormat $format, ?string $apiKey): array
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        if ($provider === Provider::ANTHROPIC || $provider === Provider::ANTHROPIC_CLAUDE_CODE) {
            if ($apiKey) {
                $headers['x-api-key'] = $apiKey;
            }
            $headers['anthropic-version'] = '2023-06-01';

            return $headers;
        }

        if ($format === \Pterodactyl\Enums\Ai\ChatFormat::GEMINI_GENERATE_CONTENT) {
            if ($apiKey) {
                $headers['x-goog-api-key'] = $apiKey;
            }

            return $headers;
        }

        if ($apiKey) {
            $headers['Authorization'] = 'Bearer ' . $apiKey;
        }

        // OpenRouter attributes traffic using these, and ignores them when absent.
        if ($provider === Provider::OPENROUTER) {
            $headers['HTTP-Referer'] = config('app.url');
            $headers['X-Title'] = config('app.name');
        }

        if ($format === \Pterodactyl\Enums\Ai\ChatFormat::ANTHROPIC_MESSAGES) {
            $headers['anthropic-version'] = '2023-06-01';
        }

        return $headers;
    }

    /**
     * Whether the key belongs in the query string rather than a header.
     */
    public function keyInQuery(\Pterodactyl\Enums\Ai\ChatFormat $format): bool
    {
        return $format === \Pterodactyl\Enums\Ai\ChatFormat::GEMINI_GENERATE_CONTENT;
    }
}
