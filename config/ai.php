<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Assistant Defaults
    |--------------------------------------------------------------------------
    |
    | Values here are only used when the corresponding key has never been saved
    | through the admin settings screen. Anything stored in the `settings` table
    | takes precedence via the SettingsServiceProvider.
    |
    */

    'enabled' => env('AI_ASSISTANT_ENABLED', false),

    'provider' => env('AI_ASSISTANT_PROVIDER', 'anthropic'),

    'api_key' => env('AI_ASSISTANT_API_KEY'),

    'base_url' => env('AI_ASSISTANT_BASE_URL'),

    'model' => env('AI_ASSISTANT_MODEL'),

    'model_small' => env('AI_ASSISTANT_MODEL_SMALL'),

    'endpoint_override' => env('AI_ASSISTANT_ENDPOINT'),

    'system_prompt' => env('AI_ASSISTANT_SYSTEM_PROMPT'),

    'temperature' => env('AI_ASSISTANT_TEMPERATURE', 0.3),

    'max_tokens' => env('AI_ASSISTANT_MAX_TOKENS', 2048),

    'timeout' => env('AI_ASSISTANT_TIMEOUT', 120),

    // Whether the terminal panel is available on the server console page.
    'allow_console' => env('AI_ASSISTANT_ALLOW_CONSOLE', true),

    // Whether the crash analysis action is offered when the server stops unexpectedly.
    'allow_crash_analysis' => env('AI_ASSISTANT_ALLOW_CRASH_ANALYSIS', true),

    // Maximum number of console lines handed to the model as context.
    'console_buffer_lines' => env('AI_ASSISTANT_CONSOLE_BUFFER', 200),

    // Maximum number of directory entries sent when the model asks for a file listing.
    'file_listing_limit' => env('AI_ASSISTANT_FILE_LISTING_LIMIT', 300),

    // Rate limiting applied to the client-facing assistant endpoints.
    'rate_limit' => env('AI_ASSISTANT_RATE_LIMIT', '20,1'),

    // Hard ceiling on assistant requests per user per calendar day, applied on
    // top of the per-minute throttle. This is the backstop against a user
    // trickling requests slowly to stay under the burst limit while still
    // spending the panel's upstream budget around the clock. 0 disables it.
    'daily_limit_per_user' => env('AI_ASSISTANT_DAILY_LIMIT', 200),
];
