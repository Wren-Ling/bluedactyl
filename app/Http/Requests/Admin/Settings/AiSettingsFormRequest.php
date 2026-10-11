<?php

namespace Pterodactyl\Http\Requests\Admin\Settings;

use Illuminate\Validation\Rule;
use Pterodactyl\Enums\Ai\Provider;
use Pterodactyl\Http\Requests\Admin\AdminFormRequest;

class AiSettingsFormRequest extends AdminFormRequest
{
    public function rules(): array
    {
        return [
            'pterodactyl:ai:enabled' => ['nullable', 'string'],
            'pterodactyl:ai:provider' => ['required', 'string', Rule::in(Provider::values())],
            'pterodactyl:ai:api_key' => ['nullable', 'string', 'max:1024'],
            'pterodactyl:ai:base_url' => ['nullable', 'string', 'max:512'],
            'pterodactyl:ai:endpoint_override' => ['nullable', 'string', 'max:512'],
            'pterodactyl:ai:model' => ['nullable', 'string', 'max:255'],
            'pterodactyl:ai:model_small' => ['nullable', 'string', 'max:255'],
            'pterodactyl:ai:system_prompt' => ['nullable', 'string', 'max:20000'],
            'pterodactyl:ai:temperature' => ['nullable', 'numeric', 'between:0,2'],
            'pterodactyl:ai:max_tokens' => ['nullable', 'integer', 'between:64,32000'],
            'pterodactyl:ai:timeout' => ['nullable', 'integer', 'between:10,600'],
            'pterodactyl:ai:allow_console' => ['nullable', 'string'],
            'pterodactyl:ai:allow_crash_analysis' => ['nullable', 'string'],
            'pterodactyl:ai:console_buffer_lines' => ['nullable', 'integer', 'between:20,1000'],
            'pterodactyl:ai:file_listing_limit' => ['nullable', 'integer', 'between:10,1000'],
            'pterodactyl:ai:rate_limit' => ['nullable', 'string', 'max:32'],
            'pterodactyl:ai:daily_limit_per_user' => ['nullable', 'integer', 'between:0,100000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'pterodactyl:ai:enabled' => 'Enable AI Assistant',
            'pterodactyl:ai:provider' => 'AI Provider',
            'pterodactyl:ai:api_key' => 'API Key',
            'pterodactyl:ai:base_url' => 'Base URL',
            'pterodactyl:ai:endpoint_override' => 'Endpoint Override',
            'pterodactyl:ai:model' => 'Model',
            'pterodactyl:ai:model_small' => 'Fallback Model',
            'pterodactyl:ai:system_prompt' => 'System Prompt',
            'pterodactyl:ai:temperature' => 'Temperature',
            'pterodactyl:ai:max_tokens' => 'Maximum Tokens',
            'pterodactyl:ai:timeout' => 'Request Timeout',
            'pterodactyl:ai:allow_console' => 'Console Assistant',
            'pterodactyl:ai:allow_crash_analysis' => 'Crash Analysis',
            'pterodactyl:ai:console_buffer_lines' => 'Console Context Lines',
            'pterodactyl:ai:file_listing_limit' => 'File Listing Limit',
            'pterodactyl:ai:rate_limit' => 'Rate Limit',
            'pterodactyl:ai:daily_limit_per_user' => 'Daily Limit Per User',
        ];
    }

    /**
     * Checkboxes are absent from the payload when unticked, so normalize them to
     * an explicit 0/1 before validation consumers read them.
     */
    public function normalize(?array $only = null): array
    {
        $data = $this->validated();

        foreach (['enabled', 'allow_console', 'allow_crash_analysis'] as $toggle) {
            $key = 'pterodactyl:ai:' . $toggle;
            $data[$key] = filter_var($data[$key] ?? false, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
        }

        $only = $only ?? array_keys($this->rules());

        return array_intersect_key($data, array_flip($only));
    }
}
