@extends('layouts.admin')
@include('partials/admin.settings.nav', ['activeTab' => 'domains'])

@section('title')
  @lang('admin/settings.domains.edit.title')
@endsection

@section('content-header')
  <h1 class="text-xl font-bold">@lang('admin/settings.domains.edit.title')</h1>
  <p class="text-sm text-muted-foreground">@lang('admin/settings.domains.edit.desc')</p>
  <nav class="flex items-center gap-1 text-sm text-muted-foreground">
    <a href="{{ route('admin.index') }}">@lang('admin/settings.admin')</a>
    <x-icon name="chevron-right" class="size-3" />
    <a href="{{ route('admin.settings') }}">@lang('admin/settings.nav')</a>
    <x-icon name="chevron-right" class="size-3" />
    <a href="{{ route('admin.settings.domains.index') }}">@lang('admin/settings.domains.nav')</a>
    <x-icon name="chevron-right" class="size-3" />
    <span>{{ $domain->name }}</span>
  </nav>
@endsection

@section('content')
  @yield('settings::nav')
  <div class="grid gap-6">
    <div class="col-span-full">
      <form action="{{ route('admin.settings.domains.update', $domain) }}" method="POST" id="domain-form">
        <div class="card">
          <header>
            <h3 class="text-lg font-semibold">@lang('admin/settings.domains.edit.domain_info')</h3>
          </header>
          <section>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div role="group" class="field">
                <label for="name">@lang('admin/settings.domains.edit.domain_name')</label>
                <input type="text" name="name" id="name" 
                       value="{{ old('name', $domain->name) }}" placeholder="@lang('admin/settings.domains.edit.domain_name_placeholder')" required />
                <p class="text-sm text-muted-foreground">@lang('admin/settings.domains.edit.domain_name_help')</p>
              </div>
              <div role="group" class="field">
                <label for="dns_provider">@lang('admin/settings.domains.edit.dns_provider') </label>
                <select name="dns_provider" id="dns_provider" class="select" required>
                  <option value="">@lang('admin/settings.domains.edit.dns_provider_placeholder')</option>
                  @foreach($providers as $key => $provider)
                    <option value="{{ $key }}" 
                            @if(old('dns_provider', $domain->dns_provider) === $key) selected @endif>
                      {{ $provider['name'] }}
                    </option>
                  @endforeach
                </select>
                <p class="text-sm text-muted-foreground">@lang('admin/settings.domains.edit.dns_provider_help')</p>
              </div>
            </div>
          </section>
        </div>

        <div class="card hidden" id="dns-config-box">
          <header>
            <h3 class="text-lg font-semibold">@lang('admin/settings.domains.edit.dns_config')</h3>
          </header>
          <section id="dns-config-content">
          </section>
        </div>

        <div class="card">
          <header>
            <h3 class="text-lg font-semibold">@lang('admin/settings.domains.edit.additional_settings')</h3>
          </header>
          <section>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div role="group" class="field col-span-full" data-orientation="responsive">
                <section>
                  <label>@lang('admin/settings.domains.edit.status_label')</label>
                  <p>@lang('admin/settings.domains.edit.status_help')</p>
                </section>
                <div role="radiogroup" aria-label="{{ trans('admin/settings.domains.edit.aria_status') }}">
                  <div role="group" class="field" data-orientation="horizontal">
                    <input type="radio" name="is_active" id="edit-active" value="1" @if(old('is_active', $domain->is_active)) checked @endif />
                    <label for="edit-active" class="font-normal">@lang('admin/settings.domains.edit.status_active')</label>
                  </div>
                  <div role="group" class="field" data-orientation="horizontal">
                    <input type="radio" name="is_active" id="edit-inactive" value="0" @if(!old('is_active', $domain->is_active)) checked @endif />
                    <label for="edit-inactive" class="font-normal">@lang('admin/settings.domains.edit.status_inactive')</label>
                  </div>
                </div>
              </div>
              <div role="group" class="field col-span-full" data-orientation="responsive">
                <section>
                  <label>@lang('admin/settings.domains.edit.default_label')</label>
                  <p>@lang('admin/settings.domains.edit.default_help')</p>
                </section>
                <div role="radiogroup" aria-label="{{ trans('admin/settings.domains.edit.aria_default') }}">
                  <div role="group" class="field" data-orientation="horizontal">
                    <input type="radio" name="is_default" id="edit-default-yes" value="1" @if(old('is_default', $domain->is_default)) checked @endif />
                    <label for="edit-default-yes" class="font-normal">@lang('admin/settings.domains.edit.default_yes')</label>
                  </div>
                  <div role="group" class="field" data-orientation="horizontal">
                    <input type="radio" name="is_default" id="edit-default-no" value="0" @if(!old('is_default', $domain->is_default)) checked @endif />
                    <label for="edit-default-no" class="font-normal">@lang('admin/settings.domains.edit.default_no')</label>
                  </div>
                </div>
              </div>
              <div role="group" class="field">
                <label>@lang('admin/settings.domains.edit.active_subdomains')</label>
                <p>
                  <span class="badge">{{ $domain->serverSubdomains->where('is_active', true)->count() }}</span>
                  @lang('admin/settings.domains.edit.subdomains_count')
                </p>
              </div>
            </div>
          </section>
        </div>

        <div class="card">
          <footer>
            {{ csrf_field() }}
            @method('PATCH')
            <button type="button" id="test-connection" class="btn" data-size="sm" data-variant="secondary" disabled>
              <x-icon name="refresh-cw" class="size-4 hidden" /> @lang('admin/settings.domains.edit.test_connection')
            </button>
            <a href="{{ route('admin.settings.domains.index') }}" class="btn" data-size="sm" data-variant="outline">@lang('admin/settings.domains.edit.cancel')</a>
            <button type="submit" class="btn ml-auto" data-size="sm">@lang('admin/settings.domains.edit.update_domain')</button>
          </footer>
        </div>
      </form>
    </div>
  </div>
@endsection

@section('footer-scripts')
  @parent
  <script>
    $(document).ready(function() {
      const $providerSelect = $('#dns_provider');
      const $configBox = $('#dns-config-box');
      const $configContent = $('#dns-config-content');
      const $testButton = $('#test-connection');
      const $form = $('#domain-form');
      const existingConfig = @json(old('dns_config', $domain->dns_config));

      $providerSelect.change(function() {
        const provider = $(this).val();
        
        if (provider) {
          loadProviderConfig(provider);
          $testButton.prop('disabled', false);
        } else {
          $configBox.hide();
          $testButton.prop('disabled', true);
        }
      });

      $testButton.click(function() {
        const $button = $(this);
        const $spinner = $button.find('.size-4');
        
        const formData = {
          dns_provider: $providerSelect.val(),
          dns_config: {}
        };

        $configContent.find('input').each(function() {
          const name = $(this).attr('name');
          if (name && name.startsWith('dns_config[')) {
            const key = name.replace('dns_config[', '').replace(']', '');
            formData.dns_config[key] = $(this).val();
          }
        });

        $button.prop('disabled', true);
        $spinner.show();

        $.post('{{ route('admin.settings.domains.test-connection') }}', {
          _token: '{{ csrf_token() }}',
          ...formData
        })
        .done(function(response) {
          if (response.success) {
            alert('{{ trans("admin.settings.domains.connection_successful") }} ' + response.message);
          } else {
            alert('{{ trans("admin.settings.domains.connection_failed") }} ' + response.message);
          }
        })
        .fail(function(xhr) {
          const response = xhr.responseJSON || {};
          alert('{{ trans("admin.settings.domains.connection_failed") }} ' + (response.message || '{{ trans("admin.settings.domains.unexpected_error") }}'));
        })
        .always(function() {
          $button.prop('disabled', false);
          $spinner.hide();
        });
      });

      function loadProviderConfig(provider) {
        $.get(`{{ route('admin.settings.domains.provider-schema', ':provider') }}`.replace(':provider', provider))
          .done(function(response) {
            if (response.success) {
              renderConfigForm(response.schema);
              $configBox.show();
            }
          })
          .fail(function() {
            $configBox.hide();
          });
      }

      function renderConfigForm(schema) {
        let html = '<div class="flex flex-wrap gap-4">';
        
        Object.keys(schema).forEach(function(key) {
          const field = schema[key];
          const oldValue = existingConfig[key] || '';
          
          html += `
            <div role="group" class="field md:w-1/2">
              <label for="dns_config_${key}">
                ${field.description || key} 
                ${field.required ? '' : ''}
              </label>
              <input type="${field.sensitive ? 'password' : 'text'}" 
                     name="dns_config[${key}]" 
                     id="dns_config_${key}" 
                     value="${oldValue}"
                     ${field.required ? 'required' : ''} />
            </div>
          `;
        });
        
        html += '</div>';
        $configContent.html(html);
      }

      if ($providerSelect.val()) {
        $providerSelect.trigger('change');
      }
    });
  </script>
@endsection
