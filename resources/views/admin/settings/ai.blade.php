@extends('layouts.admin')
@include('partials/admin.settings.nav', ['activeTab' => 'ai'])

@section('title')
  @lang('admin/settings.ai.title')
@endsection

@section('content-header')
  <h1 class="text-xl font-bold">@lang('admin/settings.ai.title')</h1>
  <p class="text-sm text-muted-foreground">@lang('admin/settings.ai.desc')</p>
  <nav class="flex items-center gap-1 text-sm text-muted-foreground">
    <a href="{{ route('admin.index') }}">@lang('admin/settings.admin')</a>
    <x-icon name="chevron-right" class="size-3" />
    <span>@lang('admin/settings.nav')</span>
  </nav>
@endsection

@section('content')
  @yield('settings::nav')

  @if(!empty($configuration['missing']))
    <div class="alert mb-6" data-variant="warning" role="alert">
      <strong>@lang('admin/settings.ai.incomplete_title')</strong>
      <ul>
        @foreach($configuration['missing'] as $missing)
          <li>{{ $missing }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('admin.settings.ai') }}" method="POST" id="ai-settings-form">
    {{ csrf_field() }}
    <input type="hidden" name="_method" value="PATCH">

    <div class="card">
      <header>
        <h3 class="text-lg font-semibold">@lang('admin/settings.ai.status_title')</h3>
      </header>
      <section>
        <div class="grid gap-6">
          <label class="field flex-row items-center gap-3">
            <input type="checkbox" name="pterodactyl:ai:enabled" value="1" class="checkbox"
                   @if($configuration['enabled']) checked @endif>
            <span>
              <span class="font-medium">@lang('admin/settings.ai.enabled_label')</span>
              <span class="block text-sm text-muted-foreground">@lang('admin/settings.ai.enabled_help')</span>
            </span>
          </label>
        </div>
      </section>
    </div>

    <div class="card">
      <header>
        <h3 class="text-lg font-semibold">@lang('admin/settings.ai.provider_title')</h3>
      </header>
      <section>
        <div class="grid gap-6">
          <div role="group" class="field">
            <label for="ai-provider">@lang('admin/settings.ai.provider_label')</label>
            <select name="pterodactyl:ai:provider" class="select" id="ai-provider">
              @foreach($providers as $key => $provider)
                <option value="{{ $key }}"
                        data-base-url="{{ $provider['base_url'] }}"
                        data-path="{{ $provider['path'] }}"
                        data-requires-key="{{ $provider['requires_api_key'] ? '1' : '0' }}"
                        data-requires-base="{{ $provider['requires_base_url'] ? '1' : '0' }}"
                        data-routes-per-model="{{ $provider['routes_per_model'] ? '1' : '0' }}"
                        data-supports-models="{{ $provider['supports_model_listing'] ? '1' : '0' }}"
                        data-format="{{ $provider['format_label'] }}"
                        @if($configuration['provider'] === $key) selected @endif>{{ $provider['label'] }}</option>
              @endforeach
            </select>
            <p class="text-sm text-muted-foreground" id="ai-provider-description">@lang('admin/settings.ai.provider_help')</p>
            <p class="text-sm text-muted-foreground">
              @lang('admin/settings.ai.format_label'): <code id="ai-provider-format">—</code>
            </p>
          </div>

          <div role="group" class="field">
            <label for="ai-base-url">@lang('admin/settings.ai.base_url_label')</label>
            <input type="text" name="pterodactyl:ai:base_url" id="ai-base-url"
                   value="{{ old('pterodactyl:ai:base_url', $configuration['base_url'] ?? '') }}"
                   placeholder="https://api.example.com">
            <p class="text-sm text-muted-foreground">@lang('admin/settings.ai.base_url_help')</p>
          </div>

          <div role="group" class="field">
            <label for="ai-endpoint">@lang('admin/settings.ai.endpoint_label')</label>
            <input type="text" name="pterodactyl:ai:endpoint_override" id="ai-endpoint"
                   value="{{ old('pterodactyl:ai:endpoint_override', $configuration['endpoint_override'] ?? '') }}"
                   placeholder="/v1/chat/completions">
            <p class="text-sm text-muted-foreground">@lang('admin/settings.ai.endpoint_help')</p>
          </div>
        </div>
      </section>
    </div>

    <div class="card">
      <header>
        <h3 class="text-lg font-semibold">@lang('admin/settings.ai.key_title')</h3>
      </header>
      <section>
        <div class="grid gap-6">
          <div role="group" class="field">
            <label for="ai-api-key">@lang('admin/settings.ai.key_label')</label>
            <input type="password" name="pterodactyl:ai:api_key" id="ai-api-key" autocomplete="new-password"
                   placeholder="{{ $configuration['has_api_key'] ? ($configuration['api_key_hint'] ?? '••••') : '' }}">
            <p class="text-sm text-muted-foreground">@lang('admin/settings.ai.key_help')</p>
            @if($configuration['has_api_key'])
              <label class="mt-2 flex items-center gap-2 text-sm">
                <input type="checkbox" name="pterodactyl:ai:clear_api_key" value="1" class="checkbox">
                @lang('admin/settings.ai.clear_key')
              </label>
            @endif
          </div>

          <div class="flex flex-wrap gap-2">
            <button type="button" class="btn" data-variant="outline" data-size="sm" id="ai-test-button">
              @lang('admin/settings.ai.test_connection')
            </button>
            <button type="button" class="btn" data-variant="outline" data-size="sm" id="ai-load-models">
              @lang('admin/settings.ai.load_models')
            </button>
          </div>

          <div id="ai-test-result" class="hidden text-sm" role="status"></div>
        </div>
      </section>
    </div>

    <div class="card">
      <header>
        <h3 class="text-lg font-semibold">@lang('admin/settings.ai.model_title')</h3>
      </header>
      <section>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div role="group" class="field">
            <label for="ai-model">@lang('admin/settings.ai.model_label')</label>
            <input type="text" name="pterodactyl:ai:model" id="ai-model" list="ai-model-list"
                   value="{{ old('pterodactyl:ai:model', $configuration['model'] ?? '') }}">
            <datalist id="ai-model-list"></datalist>
            <p class="text-sm text-muted-foreground">@lang('admin/settings.ai.model_help')</p>
          </div>
          <div role="group" class="field">
            <label for="ai-model-small">@lang('admin/settings.ai.model_small_label')</label>
            <input type="text" name="pterodactyl:ai:model_small" id="ai-model-small"
                   value="{{ old('pterodactyl:ai:model_small', $configuration['model_small'] ?? '') }}">
            <p class="text-sm text-muted-foreground">@lang('admin/settings.ai.model_small_help')</p>
          </div>
        </div>
      </section>
    </div>

    <div class="card">
      <header>
        <h3 class="text-lg font-semibold">@lang('admin/settings.ai.features_title')</h3>
      </header>
      <section>
        <div class="grid gap-6">
          <label class="field flex-row items-center gap-3">
            <input type="checkbox" name="pterodactyl:ai:allow_console" value="1" class="checkbox"
                   @if($configuration['allow_console']) checked @endif>
            <span>
              <span class="font-medium">@lang('admin/settings.ai.allow_console_label')</span>
              <span class="block text-sm text-muted-foreground">@lang('admin/settings.ai.allow_console_help')</span>
            </span>
          </label>

          <label class="field flex-row items-center gap-3">
            <input type="checkbox" name="pterodactyl:ai:allow_crash_analysis" value="1" class="checkbox"
                   @if($configuration['allow_crash_analysis']) checked @endif>
            <span>
              <span class="font-medium">@lang('admin/settings.ai.allow_crash_label')</span>
              <span class="block text-sm text-muted-foreground">@lang('admin/settings.ai.allow_crash_help')</span>
            </span>
          </label>
        </div>
      </section>
    </div>

    <div class="card">
      <header>
        <h3 class="text-lg font-semibold">@lang('admin/settings.ai.advanced_title')</h3>
      </header>
      <section>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div role="group" class="field">
            <label for="ai-temperature">@lang('admin/settings.ai.temperature_label')</label>
            <input type="number" step="0.1" min="0" max="2" name="pterodactyl:ai:temperature" id="ai-temperature"
                   value="{{ old('pterodactyl:ai:temperature', $configuration['temperature']) }}">
          </div>
          <div role="group" class="field">
            <label for="ai-max-tokens">@lang('admin/settings.ai.max_tokens_label')</label>
            <input type="number" min="64" max="32000" name="pterodactyl:ai:max_tokens" id="ai-max-tokens"
                   value="{{ old('pterodactyl:ai:max_tokens', $configuration['max_tokens']) }}">
          </div>
          <div role="group" class="field">
            <label for="ai-timeout">@lang('admin/settings.ai.timeout_label')</label>
            <input type="number" min="10" max="600" name="pterodactyl:ai:timeout" id="ai-timeout"
                   value="{{ old('pterodactyl:ai:timeout', $configuration['timeout']) }}">
          </div>
          <div role="group" class="field">
            <label for="ai-rate-limit">@lang('admin/settings.ai.rate_limit_label')</label>
            <input type="text" name="pterodactyl:ai:rate_limit" id="ai-rate-limit"
                   value="{{ old('pterodactyl:ai:rate_limit', $configuration['rate_limit'] ?? '20,1') }}">
            <p class="text-sm text-muted-foreground">@lang('admin/settings.ai.rate_limit_help')</p>
          </div>
          <div role="group" class="field">
            <label for="ai-daily-limit">@lang('admin/settings.ai.daily_limit_label')</label>
            <input type="number" min="0" max="100000" name="pterodactyl:ai:daily_limit_per_user" id="ai-daily-limit"
                   value="{{ old('pterodactyl:ai:daily_limit_per_user', $configuration['daily_limit_per_user'] ?? 200) }}">
            <p class="text-sm text-muted-foreground">@lang('admin/settings.ai.daily_limit_help')</p>
          </div>
          <div role="group" class="field">
            <label for="ai-console-lines">@lang('admin/settings.ai.console_lines_label')</label>
            <input type="number" min="20" max="1000" name="pterodactyl:ai:console_buffer_lines" id="ai-console-lines"
                   value="{{ old('pterodactyl:ai:console_buffer_lines', $configuration['console_buffer_lines']) }}">
            <p class="text-sm text-muted-foreground">@lang('admin/settings.ai.console_lines_help')</p>
          </div>
          <div role="group" class="field">
            <label for="ai-file-limit">@lang('admin/settings.ai.file_limit_label')</label>
            <input type="number" min="10" max="1000" name="pterodactyl:ai:file_listing_limit" id="ai-file-limit"
                   value="{{ old('pterodactyl:ai:file_listing_limit', $configuration['file_listing_limit']) }}">
            <p class="text-sm text-muted-foreground">@lang('admin/settings.ai.file_limit_help')</p>
          </div>
        </div>

        <div class="grid gap-6 mt-6">
          <div role="group" class="field">
            <label for="ai-system-prompt">@lang('admin/settings.ai.system_prompt_label')</label>
            <textarea name="pterodactyl:ai:system_prompt" id="ai-system-prompt" rows="6"
                      placeholder="@lang('admin/settings.ai.system_prompt_placeholder')">{{ old('pterodactyl:ai:system_prompt', $configuration['system_prompt'] ?? '') }}</textarea>
            <p class="text-sm text-muted-foreground">@lang('admin/settings.ai.system_prompt_help')</p>
          </div>
        </div>
      </section>
    </div>

    <div class="card">
      <footer>
        <button type="submit" class="btn ml-auto" data-size="sm">@lang('admin/settings.save')</button>
      </footer>
    </div>
  </form>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const providerSelect = document.getElementById('ai-provider');
      const baseUrlInput = document.getElementById('ai-base-url');
      const formatLabel = document.getElementById('ai-provider-format');
      const description = document.getElementById('ai-provider-description');
      const modelList = document.getElementById('ai-model-list');
      const result = document.getElementById('ai-test-result');

      const providerData = @json($providers);

      function describeResult(success, message) {
        result.classList.remove('hidden');
        result.className = 'text-sm ' + (success ? 'text-green-500' : 'text-red-500');
        result.textContent = message;
      }

      function syncProvider() {
        const key = providerSelect.value;
        const data = providerData[key] || {};

        if (formatLabel) formatLabel.textContent = data.format_label || '—';
        if (description) description.textContent = data.description || '';

        // Only prefill the base URL when the field is empty, so an explicit
        // override is never silently replaced while editing.
        if (baseUrlInput && !baseUrlInput.value && data.base_url) {
          baseUrlInput.placeholder = data.base_url;
        }
      }

      providerSelect.addEventListener('change', syncProvider);
      syncProvider();

      document.getElementById('ai-test-button').addEventListener('click', async function () {
        const button = this;
        button.disabled = true;
        describeResult(true, @lang('admin/settings.ai.testing'));

        try {
          const response = await fetch('{{ route('admin.settings.ai.test') }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
              'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
              provider: providerSelect.value,
              base_url: baseUrlInput.value,
            }),
          });
          const data = await response.json();
          describeResult(data.success, data.message);
        } catch (error) {
          describeResult(false, error.message);
        } finally {
          button.disabled = false;
        }
      });

      document.getElementById('ai-load-models').addEventListener('click', async function () {
        const button = this;
        button.disabled = true;

        try {
          const url = new URL('{{ route('admin.settings.ai.models') }}', window.location.origin);
          url.searchParams.set('provider', providerSelect.value);

          const response = await fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
          });
          const data = await response.json();

          if (!data.success) {
            describeResult(false, data.message);
            return;
          }

          modelList.innerHTML = '';
          (data.models || []).forEach(function (model) {
            const option = document.createElement('option');
            option.value = model;
            modelList.appendChild(option);
          });

          describeResult(true, (data.models || []).length
            ? @lang('admin/settings.ai.models_loaded') + ' (' + data.models.length + ')'
            : data.message);
        } catch (error) {
          describeResult(false, error.message);
        } finally {
          button.disabled = false;
        }
      });
    });
  </script>
@endsection
