<?php

namespace Pterodactyl\Services\Ai;

use Pterodactyl\Enums\Ai\ChatFormat;
use Pterodactyl\Enums\Ai\Provider;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;

/**
 * Reads the AI assistant configuration.
 *
 * The API key is stored encrypted in the settings table and is never handed to
 * the browser: only the values exposed by {@see toPublicArray()} leave the server.
 */
class AiSettingsService
{
    public const PREFIX = 'settings::pterodactyl:ai:';

    /**
     * Decrypted key cache for the current request. The settings repository
     * caches the ciphertext, so decrypting once per request avoids repeating
     * the work for every prompt the assistant assembles.
     */
    private ?string $decryptedApiKey = null;

    private bool $apiKeyResolved = false;

    public function __construct(
        private SettingsRepositoryInterface $settings,
        private ConfigRepository $config,
        private Encrypter $encrypter,
    ) {}

    public function isEnabled(): bool
    {
        if (!$this->provider() instanceof Provider) {
            return false;
        }

        return $this->bool($this->get('enabled', $this->config->get('ai.enabled', false)));
    }

    public function provider(): ?Provider
    {
        return Provider::tryFrom((string) $this->get('provider', $this->config->get('ai.provider', 'anthropic')));
    }

    public function format(): ChatFormat
    {
        return $this->provider()?->format() ?? ChatFormat::OPENAI_CHAT;
    }

    /**
     * The API key in plaintext, or null when unset.
     *
     * Values saved through the admin screen are encrypted; a key supplied only
     * through the environment is used verbatim.
     */
    public function apiKey(): ?string
    {
        if ($this->apiKeyResolved) {
            return $this->decryptedApiKey;
        }

        $this->apiKeyResolved = true;

        $value = $this->get('api_key', $this->config->get('ai.api_key'));

        if (!is_string($value) || trim($value) === '') {
            return $this->decryptedApiKey = null;
        }

        try {
            // encrypt() serializes by default, so decrypt() must unserialize.
            // This matches how SettingsServiceProvider reads the same value.
            $decrypted = $this->encrypter->decrypt($value);
        } catch (DecryptException) {
            // Not ciphertext: an environment-supplied key.
            $decrypted = $value;
        }

        return $this->decryptedApiKey = (is_string($decrypted) && $decrypted !== '' ? $decrypted : null);
    }

    public function hasApiKey(): bool
    {
        return $this->apiKey() !== null;
    }

    public function baseUrl(): ?string
    {
        $value = $this->get('base_url', $this->config->get('ai.base_url'));

        return is_string($value) && trim($value) !== '' ? rtrim(trim($value), '/') : null;
    }

    /**
     * Explicit endpoint override. When set this replaces the provider default path.
     */
    public function endpointOverride(): ?string
    {
        $value = $this->get('endpoint_override', $this->config->get('ai.endpoint_override'));

        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }

    public function model(): ?string
    {
        $value = $this->get('model', $this->config->get('ai.model'));

        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }

    public function modelSmall(): ?string
    {
        $value = $this->get('model_small', $this->config->get('ai.model_small'));

        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }

    public function systemPrompt(): ?string
    {
        $value = $this->get('system_prompt', $this->config->get('ai.system_prompt'));

        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }

    public function temperature(): float
    {
        $value = $this->get('temperature', $this->config->get('ai.temperature', 0.3));

        return is_numeric($value) ? (float) $value : 0.3;
    }

    public function maxTokens(): int
    {
        $value = $this->get('max_tokens', $this->config->get('ai.max_tokens', 2048));

        return is_numeric($value) ? max(64, (int) $value) : 2048;
    }

    public function timeout(): int
    {
        $value = $this->get('timeout', $this->config->get('ai.timeout', 120));

        return is_numeric($value) ? max(10, (int) $value) : 120;
    }

    public function allowConsole(): bool
    {
        return $this->bool($this->get('allow_console', $this->config->get('ai.allow_console', true)));
    }

    public function allowCrashAnalysis(): bool
    {
        return $this->bool($this->get('allow_crash_analysis', $this->config->get('ai.allow_crash_analysis', true)));
    }

    public function consoleBufferLines(): int
    {
        $value = $this->get('console_buffer_lines', $this->config->get('ai.console_buffer_lines', 200));

        return is_numeric($value) ? min(1000, max(20, (int) $value)) : 200;
    }

    public function fileListingLimit(): int
    {
        $value = $this->get('file_listing_limit', $this->config->get('ai.file_listing_limit', 300));

        return is_numeric($value) ? min(1000, max(10, (int) $value)) : 300;
    }

    public function rateLimit(): string
    {
        $value = $this->get('rate_limit', $this->config->get('ai.rate_limit', '20,1'));

        return is_string($value) && $value !== '' ? $value : '20,1';
    }

    /**
     * True when the assistant is switched on and has everything it needs to run.
     */
    public function isUsable(): bool
    {
        $provider = $this->provider();

        if (!$this->isEnabled() || !$provider instanceof Provider) {
            return false;
        }

        if ($provider->requiresApiKey() && !$this->hasApiKey()) {
            return false;
        }

        if (!$this->resolvedBaseUrl() || !$this->model()) {
            return false;
        }

        return true;
    }

    /**
     * Effective origin: the administrator value when present, otherwise the
     * provider default.
     */
    public function resolvedBaseUrl(): ?string
    {
        $base = $this->baseUrl() ?? $this->provider()?->baseUrl();

        return $base ? rtrim($base, '/') : null;
    }

    /**
     * Safe subset of the configuration for the browser. Never contains the key.
     */
    public function toPublicArray(): array
    {
        $provider = $this->provider();

        return [
            'enabled' => $this->isUsable(),
            'configured' => $this->isUsable(),
            'provider' => $provider?->value,
            'provider_label' => $provider?->label(),
            'format' => $this->format()->value,
            'allow_console' => $this->allowConsole(),
            'allow_crash_analysis' => $this->allowCrashAnalysis(),
            'console_buffer_lines' => $this->consoleBufferLines(),
            'file_listing_limit' => $this->fileListingLimit(),
        ];
    }

    /**
     * Everything the admin screen needs, with the key reduced to a presence flag.
     */
    public function toAdminArray(): array
    {
        $provider = $this->provider();

        return [
            'enabled' => $this->isEnabled(),
            'usable' => $this->isUsable(),
            'provider' => $provider?->value,
            'base_url' => $this->baseUrl(),
            'resolved_base_url' => $this->resolvedBaseUrl(),
            'endpoint_override' => $this->endpointOverride(),
            'model' => $this->model(),
            'model_small' => $this->modelSmall(),
            'has_api_key' => $this->hasApiKey(),
            'api_key_hint' => $this->apiKeyHint(),
            'temperature' => $this->temperature(),
            'max_tokens' => $this->maxTokens(),
            'timeout' => $this->timeout(),
            'allow_console' => $this->allowConsole(),
            'allow_crash_analysis' => $this->allowCrashAnalysis(),
            'console_buffer_lines' => $this->consoleBufferLines(),
            'file_listing_limit' => $this->fileListingLimit(),
            'system_prompt' => $this->systemPrompt(),
            'missing' => $this->missingRequirements(),
        ];
    }

    /**
     * Human readable list of what still blocks the assistant from running.
     *
     * @return string[]
     */
    public function missingRequirements(): array
    {
        $missing = [];
        $provider = $this->provider();

        if (!$provider instanceof Provider) {
            return ['No provider selected.'];
        }

        if ($provider->requiresApiKey() && !$this->hasApiKey()) {
            $missing[] = 'An API key is required.';
        }

        if (!$this->resolvedBaseUrl()) {
            $missing[] = 'A base URL is required for the selected provider.';
        }

        if (!$this->model()) {
            $missing[] = 'A model name is required.';
        }

        return $missing;
    }

    /**
     * Last four characters of the stored key, for display only.
     */
    public function apiKeyHint(): ?string
    {
        $key = $this->apiKey();

        if ($key === null) {
            return null;
        }

        return strlen($key) <= 4 ? str_repeat('*', strlen($key)) : '****' . substr($key, -4);
    }

    private function get(string $key, mixed $default = null): mixed
    {
        $value = $this->settings->get(self::PREFIX . $key, $default);

        return $value === '' || $value === null ? $default : $value;
    }

    private function bool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }
}
