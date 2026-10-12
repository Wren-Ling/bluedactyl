<?php

namespace Pterodactyl\Enums\Ai;

/**
 * Upstream providers that can back the panel AI assistant.
 *
 * `baseUrl` is the default origin used when the administrator does not override it.
 * An empty base URL means the endpoint differs per deployment (Copilot, Codex, the
 * Gemini CLI shims) and the administrator must supply one.
 */
enum Provider: string
{
    case ANTHROPIC = 'anthropic';
    case ANTHROPIC_CLAUDE_CODE = 'anthropic_claude_code';
    case OPENAI = 'openai';
    case OPENAI_RESPONSES = 'openai_responses';
    case OPENAI_CODEX = 'openai_codex';
    case OPENROUTER = 'openrouter';
    case COMMAND_CODE = 'command_code';
    case OPENCODE_ZEN = 'opencode_zen';
    case OPENCODE_GO = 'opencode_go';
    case GOOGLE_AI_STUDIO = 'google_ai_studio';
    case GOOGLE_VERTEX = 'google_vertex';
    case GOOGLE_ANTIGRAVITY = 'google_antigravity';
    case GOOGLE_GEMINI_CLI = 'google_gemini_cli';
    case GITHUB_COPILOT = 'github_copilot';
    case ZED = 'zed';
    case XAI_GROK = 'xai_grok';
    case OLLAMA = 'ollama';
    case CUSTOM = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::ANTHROPIC => 'Anthropic Messages API',
            self::ANTHROPIC_CLAUDE_CODE => 'Anthropic Claude Code',
            self::OPENAI => 'OpenAI Chat Completion API',
            self::OPENAI_RESPONSES => 'OpenAI Responses API',
            self::OPENAI_CODEX => 'OpenAI Codex',
            self::OPENROUTER => 'OpenRouter',
            self::COMMAND_CODE => 'Command Code',
            self::OPENCODE_ZEN => 'OpenCode Zen',
            self::OPENCODE_GO => 'OpenCode Go',
            self::GOOGLE_AI_STUDIO => 'Google AI Studio (Gemini API)',
            self::GOOGLE_VERTEX => 'Google Vertex AI',
            self::GOOGLE_ANTIGRAVITY => 'Google Antigravity',
            self::GOOGLE_GEMINI_CLI => 'Google Gemini CLI',
            self::GITHUB_COPILOT => 'GitHub Copilot',
            self::ZED => 'Zed',
            self::XAI_GROK => 'xAI Grok Build',
            self::OLLAMA => 'Ollama Chat API',
            self::CUSTOM => 'Custom (OpenAI-compatible)',
        };
    }

    /**
     * Human readable description shown next to the option in the admin panel.
     */
    public function description(): string
    {
        return match ($this) {
            self::ANTHROPIC => 'Official Anthropic Messages API. Use /v1/messages.',
            self::ANTHROPIC_CLAUDE_CODE => 'Anthropic endpoint used by Claude Code. Same wire format as the Messages API.',
            self::OPENAI => 'Classic OpenAI chat completions endpoint.',
            self::OPENAI_RESPONSES => 'Newer OpenAI responses endpoint with reasoning output.',
            self::OPENAI_CODEX => 'Codex backend route. Requires a base URL for your Codex deployment.',
            self::OPENROUTER => 'OpenRouter gateway. Serves many vendors behind one OpenAI-compatible endpoint.',
            self::COMMAND_CODE => 'Command Code. The endpoint is picked automatically from the model name.',
            self::OPENCODE_ZEN => 'OpenCode Zen. The endpoint is picked automatically from the model name.',
            self::OPENCODE_GO => 'OpenCode Go. The endpoint is picked automatically from the model name.',
            self::GOOGLE_AI_STUDIO => 'Google AI Studio Gemini API using generateContent.',
            self::GOOGLE_VERTEX => 'Google Vertex AI generateContent endpoint.',
            self::GOOGLE_ANTIGRAVITY => 'Antigravity internal endpoint. Requires a base URL and mirrors the Gemini wire format.',
            self::GOOGLE_GEMINI_CLI => 'Gemini CLI internal endpoint. Requires a base URL and mirrors the Gemini wire format.',
            self::GITHUB_COPILOT => 'GitHub Copilot. The endpoint is picked automatically from the model name.',
            self::ZED => 'Zed completions endpoint.',
            self::XAI_GROK => 'xAI Grok using the OpenAI responses format.',
            self::OLLAMA => 'Local or remote Ollama daemon.',
            self::CUSTOM => 'Any other OpenAI-compatible service. Supply the base URL.',
        };
    }

    /**
     * Default dialect for this provider when no model-specific rule applies.
     */
    public function format(): ChatFormat
    {
        return match ($this) {
            self::ANTHROPIC, self::ANTHROPIC_CLAUDE_CODE => ChatFormat::ANTHROPIC_MESSAGES,
            self::OPENAI_RESPONSES,
            self::OPENAI_CODEX,
            self::XAI_GROK => ChatFormat::OPENAI_RESPONSES,
            self::GOOGLE_AI_STUDIO,
            self::GOOGLE_VERTEX,
            self::GOOGLE_ANTIGRAVITY,
            self::GOOGLE_GEMINI_CLI => ChatFormat::GEMINI_GENERATE_CONTENT,
            self::OLLAMA => ChatFormat::OLLAMA_CHAT,
            default => ChatFormat::OPENAI_CHAT,
        };
    }

    /**
     * Default origin. Null means the administrator has to provide one.
     */
    public function baseUrl(): ?string
    {
        return match ($this) {
            self::ANTHROPIC, self::ANTHROPIC_CLAUDE_CODE => 'https://api.anthropic.com',
            self::OPENAI,
            self::OPENAI_RESPONSES,
            self::OPENAI_CODEX => 'https://api.openai.com',
            self::OPENROUTER => 'https://openrouter.ai/api',
            self::COMMAND_CODE => 'https://api.commandcode.ai',
            self::OPENCODE_ZEN => 'https://opencode.ai/zen',
            self::OPENCODE_GO => 'https://opencode.ai/go',
            self::GOOGLE_AI_STUDIO => 'https://generativelanguage.googleapis.com',
            self::XAI_GROK => 'https://api.x.ai',
            self::OLLAMA => 'http://127.0.0.1:11434',
            default => null,
        };
    }

    /**
     * Default path appended to the base URL when the administrator has not supplied one.
     */
    public function defaultPath(): string
    {
        return match ($this) {
            self::ANTHROPIC, self::ANTHROPIC_CLAUDE_CODE => '/v1/messages',
            self::OPENAI,
            self::OPENROUTER,
            self::COMMAND_CODE,
            self::OPENCODE_ZEN,
            self::OPENCODE_GO,
            self::CUSTOM => '/v1/chat/completions',
            self::OPENAI_RESPONSES, self::XAI_GROK => '/v1/responses',
            self::OPENAI_CODEX => '/backend-api/codex/responses',
            self::GOOGLE_AI_STUDIO => '/v1beta/models',
            self::GOOGLE_VERTEX => '/v1beta1/models',
            self::GOOGLE_ANTIGRAVITY, self::GOOGLE_GEMINI_CLI => '/v1internal',
            self::GITHUB_COPILOT => '/chat/completions',
            self::ZED => '/completions',
            self::OLLAMA => '/api/chat',
        };
    }

    /**
     * Whether this provider needs an API key before it can be used.
     */
    public function requiresApiKey(): bool
    {
        return match ($this) {
            self::OLLAMA, self::CUSTOM => false,
            default => true,
        };
    }

    /**
     * Whether this provider picks its endpoint based on the selected model.
     */
    public function routesPerModel(): bool
    {
        return match ($this) {
            self::COMMAND_CODE,
            self::OPENCODE_ZEN,
            self::OPENCODE_GO,
            self::GITHUB_COPILOT => true,
            default => false,
        };
    }

    /**
     * Whether the base URL has to be supplied by the administrator.
     */
    public function requiresBaseUrl(): bool
    {
        return $this->baseUrl() === null;
    }

    /**
     * Provider used when the administrator picks a model-capability route that
     * needs a different dialect. Used to build the settings summary.
     */
    public function supportsModelListing(): bool
    {
        return match ($this) {
            self::OLLAMA,
            self::OPENAI,
            self::OPENROUTER,
            self::COMMAND_CODE,
            self::OPENCODE_ZEN,
            self::OPENCODE_GO,
            self::GOOGLE_AI_STUDIO,
            self::GITHUB_COPILOT,
            self::CUSTOM => true,
            default => false,
        };
    }

    /**
     * Model listing path, when the provider exposes one.
     */
    public function modelsPath(): ?string
    {
        return match ($this) {
            self::OLLAMA => '/api/tags',
            self::GOOGLE_AI_STUDIO => '/v1beta/models',
            self::GITHUB_COPILOT => '/models',
            self::OPENAI,
            self::OPENROUTER,
            self::COMMAND_CODE,
            self::OPENCODE_ZEN,
            self::OPENCODE_GO,
            self::CUSTOM => '/v1/models',
            default => null,
        };
    }

    /**
     * @return array<string, array{label: string, description: string, format: string, base_url: ?string, path: ?string, requires_api_key: bool, requires_base_url: bool, routes_per_model: bool, supports_model_listing: bool}>
     */
    public static function toSchema(): array
    {
        $schema = [];
        foreach (self::cases() as $case) {
            $schema[$case->value] = [
                'label' => $case->label(),
                'description' => $case->description(),
                'format' => $case->format()->value,
                'format_label' => $case->format()->label(),
                'base_url' => $case->baseUrl(),
                'path' => $case->defaultPath(),
                'requires_api_key' => $case->requiresApiKey(),
                'requires_base_url' => $case->requiresBaseUrl(),
                'routes_per_model' => $case->routesPerModel(),
                'supports_model_listing' => $case->supportsModelListing(),
            ];
        }

        return $schema;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
