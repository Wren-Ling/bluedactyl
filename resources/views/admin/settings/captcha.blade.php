@extends('layouts.admin')
@include('partials/admin.settings.nav', ['activeTab' => 'captcha'])

@section('title')
  @lang('admin/settings.captcha.title')
@endsection

@section('content-header')
  <h1 class="text-xl font-bold">@lang('admin/settings.captcha.title')</h1>
  <p class="text-sm text-muted-foreground">@lang('admin/settings.captcha.desc')</p>
  <nav class="flex items-center gap-1 text-sm text-muted-foreground">
    <a href="{{ route('admin.index') }}">@lang('admin/settings.admin')</a>
    <x-icon name="chevron-right" class="size-3" />
    <span>@lang('admin/settings.nav')</span>
  </nav>
@endsection

@section('content')
  @yield('settings::nav')
  <div class="grid gap-6">
    <div class="col-span-full">
      <form action="{{ route('admin.settings.captcha') }}" method="POST">
        <div class="card">
          <header>
            <h3 class="text-lg font-semibold">@lang('admin/settings.captcha.provider_title')</h3>
          </header>
          <section>
            <div class="grid gap-6">
              <div role="group" class="field">
                <label>@lang('admin/settings.captcha.provider_label')</label>
                <select name="pterodactyl:captcha:provider" class="select" id="captcha-provider">
                  @foreach($providers as $key => $name)
                    <option value="{{ $key }}" @if(old('pterodactyl:captcha:provider', config('pterodactyl.captcha.provider', 'none')) === $key) selected @endif>{{ $name }}</option>
                  @endforeach
                </select>
                <p class="text-sm text-muted-foreground">@lang('admin/settings.captcha.provider_help')</p>
              </div>
            </div>
          </section>
        </div>

        <div class="card hidden" id="turnstile-settings">
          <header>
            <h3 class="text-lg font-semibold">@lang('admin/settings.captcha.turnstile_title')</h3>
          </header>
          <section>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div role="group" class="field">
                <label>@lang('admin/settings.captcha.turnstile_site_key')</label>
                <input type="text"  name="pterodactyl:captcha:turnstile:site_key"
                  value="{{ old('pterodactyl:captcha:turnstile:site_key', config('pterodactyl.captcha.turnstile.site_key', '')) }}" />
                <p class="text-sm text-muted-foreground">@lang('admin/settings.captcha.turnstile_site_key_help')</p>
              </div>
              <div role="group" class="field">
                <label>@lang('admin/settings.captcha.turnstile_secret_key')</label>
                <input type="password"  name="pterodactyl:captcha:turnstile:secret_key"
                  value="{{ old('pterodactyl:captcha:turnstile:secret_key', config('pterodactyl.captcha.turnstile.secret_key', '')) }}" />
                <p class="text-sm text-muted-foreground">@lang('admin/settings.captcha.turnstile_secret_key_help')</p>
              </div>
            </div>
            <div class="grid gap-6">
              <div class="col-span-full">
                <div class="alert" data-variant="info" role="alert">
                  <strong>@lang('admin/settings.captcha.turnstile_instructions_title')</strong>
                  <ol>
                    <li>{!! trans('admin/settings.captcha.turnstile_step1') !!}</li>
                    <li>@lang('admin/settings.captcha.turnstile_step2')</li>
                    <li>@lang('admin/settings.captcha.turnstile_step3')</li>
                    <li>@lang('admin/settings.captcha.turnstile_step4')</li>
                    <li>@lang('admin/settings.captcha.turnstile_step5')</li>
                  </ol>
                </div>
              </div>
            </div>
          </section>
        </div>

        <div class="card hidden" id="hcaptcha-settings">
          <header>
            <h3 class="text-lg font-semibold">@lang('admin/settings.captcha.hcaptcha_title')</h3>
          </header>
          <section>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div role="group" class="field">
                <label>@lang('admin/settings.captcha.hcaptcha_site_key')</label>
                <input type="text"  name="pterodactyl:captcha:hcaptcha:site_key"
                  value="{{ old('pterodactyl:captcha:hcaptcha:site_key', config('pterodactyl.captcha.hcaptcha.site_key', '')) }}" />
                <p class="text-sm text-muted-foreground">@lang('admin/settings.captcha.hcaptcha_site_key_help')</p>
              </div>
              <div role="group" class="field">
                <label>@lang('admin/settings.captcha.hcaptcha_secret_key')</label>
                <input type="password"  name="pterodactyl:captcha:hcaptcha:secret_key"
                  value="{{ old('pterodactyl:captcha:hcaptcha:secret_key', config('pterodactyl.captcha.hcaptcha.secret_key', '')) }}" />
                <p class="text-sm text-muted-foreground">@lang('admin/settings.captcha.hcaptcha_secret_key_help')</p>
              </div>
            </div>
            <div class="grid gap-6">
              <div class="col-span-full">
                <div class="alert" data-variant="info" role="alert">
                  <strong>@lang('admin/settings.captcha.hcaptcha_instructions_title')</strong>
                  <ol>
                    <li>{!! trans('admin/settings.captcha.hcaptcha_step1') !!}</li>
                    <li>@lang('admin/settings.captcha.hcaptcha_step2')</li>
                    <li>@lang('admin/settings.captcha.hcaptcha_step3')</li>
                    <li>@lang('admin/settings.captcha.hcaptcha_step4')</li>
                    <li>@lang('admin/settings.captcha.hcaptcha_step5')</li>
                  </ol>
                </div>
              </div>
            </div>
          </section>
        </div>

        <div class="card hidden" id="recaptcha-settings">
          <header>
            <h3 class="text-lg font-semibold">@lang('admin/settings.captcha.recaptcha_title')</h3>
          </header>
          <section>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div role="group" class="field">
                <label>@lang('admin/settings.captcha.recaptcha_site_key')</label>
                <input type="text"  name="pterodactyl:captcha:recaptcha:site_key"
                  value="{{ old('pterodactyl:captcha:recaptcha:site_key', config('pterodactyl.captcha.recaptcha.site_key', '')) }}" />
                <p class="text-sm text-muted-foreground">@lang('admin/settings.captcha.recaptcha_site_key_help')</p>
              </div>
              <div role="group" class="field">
                <label>@lang('admin/settings.captcha.recaptcha_secret_key')</label>
                <input type="password"  name="pterodactyl:captcha:recaptcha:secret_key"
                  value="{{ old('pterodactyl:captcha:recaptcha:secret_key', config('pterodactyl.captcha.recaptcha.secret_key', '')) }}" />
                <p class="text-sm text-muted-foreground">@lang('admin/settings.captcha.recaptcha_secret_key_help')</p>
              </div>
            </div>
            <div class="grid gap-6">
              <div class="col-span-full">
                <div class="alert" data-variant="info" role="alert">
                  <strong>@lang('admin/settings.captcha.recaptcha_instructions_title')</strong>
                  <ol>
                    <li>{!! trans('admin/settings.captcha.recaptcha_step1') !!}</li>
                    <li>{!! trans('admin/settings.captcha.recaptcha_step2') !!}</li>
                    <li>@lang('admin/settings.captcha.recaptcha_step3')</li>
                    <li>@lang('admin/settings.captcha.recaptcha_step4')</li>
                    <li>@lang('admin/settings.captcha.recaptcha_step5')</li>
                  </ol>
                  <p>{!! trans('admin/settings.captcha.recaptcha_note') !!}</p>
                </div>
              </div>
            </div>
          </section>
        </div>

        <div class="card">
          <footer>
            {{ csrf_field() }}
            <button type="submit" name="_method" value="PATCH" class="btn ml-auto" data-size="sm">@lang('admin/settings.save')</button>
          </footer>
        </div>
      </form>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const providerSelect = document.getElementById('captcha-provider');
      const turnstileSettings = document.getElementById('turnstile-settings');
      const hcaptchaSettings = document.getElementById('hcaptcha-settings');
      const recaptchaSettings = document.getElementById('recaptcha-settings');

      function toggleSettings() {
        const provider = providerSelect.value;

        turnstileSettings.style.display = 'none';
        hcaptchaSettings.style.display = 'none';
        recaptchaSettings.style.display = 'none';

        if (provider === 'turnstile') {
          turnstileSettings.style.display = 'block';
        } else if (provider === 'hcaptcha') {
          hcaptchaSettings.style.display = 'block';
        } else if (provider === 'recaptcha') {
          recaptchaSettings.style.display = 'block';
        }
      }

      providerSelect.addEventListener('change', toggleSettings);

      setTimeout(toggleSettings, 100);
    });
  </script>
@endsection
