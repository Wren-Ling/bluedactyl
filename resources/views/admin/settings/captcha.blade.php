@extends('layouts.admin')
@include('partials/admin.settings.nav', ['activeTab' => 'captcha'])

@section('title')
  Captcha Settings
@endsection

@section('content-header')
  <h1 class="text-xl font-bold">Captcha Settings</h1>
  <p class="text-sm text-muted-foreground">Configure captcha protection for authentication forms.</p>
  <nav class="flex items-center gap-1 text-sm text-muted-foreground">
    <a href="{{ route('admin.index') }}">Admin</a>
    <x-icon name="chevron-right" class="size-3" />
    <span>Settings</span>
  </nav>
@endsection

@section('content')
  @yield('settings::nav')
  <div class="grid gap-6">
    <div class="col-span-full">
      <form action="{{ route('admin.settings.captcha') }}" method="POST">
        <div class="card">
          <header>
            <h3 class="text-lg font-semibold">Captcha Provider</h3>
          </header>
          <section>
            <div class="grid gap-6">
              <div role="group" class="field">
                <label>Provider</label>
                <select name="pterodactyl:captcha:provider" class="select" id="captcha-provider">
                  @foreach($providers as $key => $name)
                    <option value="{{ $key }}" @if(old('pterodactyl:captcha:provider', config('pterodactyl.captcha.provider', 'none')) === $key) selected @endif>{{ $name }}</option>
                  @endforeach
                </select>
                <p class="text-sm text-muted-foreground">Select the captcha provider to use for authentication forms.</p>
              </div>
            </div>
          </section>
        </div>

        <div class="card hidden" id="turnstile-settings">
          <header>
            <h3 class="text-lg font-semibold">Cloudflare Turnstile Configuration</h3>
          </header>
          <section>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div role="group" class="field">
                <label>Site Key</label>
                <input type="text"  name="pterodactyl:captcha:turnstile:site_key"
                  value="{{ old('pterodactyl:captcha:turnstile:site_key', config('pterodactyl.captcha.turnstile.site_key', '')) }}" />
                <p class="text-sm text-muted-foreground">The site key provided by Cloudflare Turnstile. This is used in the frontend widget.</p>
              </div>
              <div role="group" class="field">
                <label>Secret Key</label>
                <input type="password"  name="pterodactyl:captcha:turnstile:secret_key"
                  value="{{ old('pterodactyl:captcha:turnstile:secret_key', config('pterodactyl.captcha.turnstile.secret_key', '')) }}" />
                <p class="text-sm text-muted-foreground">The secret key provided by Cloudflare Turnstile. This is used for server-side verification.</p>
              </div>
            </div>
            <div class="grid gap-6">
              <div class="col-span-full">
                <div class="alert" data-variant="info" role="alert">
                  <strong>Setup Instructions:</strong>
                  <ol>
                    <li>Visit the <a href="https://dash.cloudflare.com/?to=/:account/turnstile" target="_blank">Cloudflare Turnstile dashboard</a></li>
                    <li>Create a new site or select an existing one</li>
                    <li>Add your domain to the site configuration</li>
                    <li>Copy the Site Key and Secret Key from the dashboard</li>
                    <li>Paste them into the fields above</li>
                  </ol>
                </div>
              </div>
            </div>
          </section>
        </div>

        <div class="card hidden" id="hcaptcha-settings">
          <header>
            <h3 class="text-lg font-semibold">hCaptcha Configuration</h3>
          </header>
          <section>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div role="group" class="field">
                <label>Site Key</label>
                <input type="text"  name="pterodactyl:captcha:hcaptcha:site_key"
                  value="{{ old('pterodactyl:captcha:hcaptcha:site_key', config('pterodactyl.captcha.hcaptcha.site_key', '')) }}" />
                <p class="text-sm text-muted-foreground">The site key provided by hCaptcha. This is used in the frontend widget.</p>
              </div>
              <div role="group" class="field">
                <label>Secret Key</label>
                <input type="password"  name="pterodactyl:captcha:hcaptcha:secret_key"
                  value="{{ old('pterodactyl:captcha:hcaptcha:secret_key', config('pterodactyl.captcha.hcaptcha.secret_key', '')) }}" />
                <p class="text-sm text-muted-foreground">The secret key provided by hCaptcha. This is used for server-side verification.</p>
              </div>
            </div>
            <div class="grid gap-6">
              <div class="col-span-full">
                <div class="alert" data-variant="info" role="alert">
                  <strong>Setup Instructions:</strong>
                  <ol>
                    <li>Visit the <a href="https://dashboard.hcaptcha.com/sites" target="_blank">hCaptcha dashboard</a></li>
                    <li>Create a new site or select an existing one</li>
                    <li>Add your domain to the site configuration</li>
                    <li>Copy the Site Key and Secret Key from the dashboard</li>
                    <li>Paste them into the fields above</li>
                  </ol>
                </div>
              </div>
            </div>
          </section>
        </div>

        <div class="card hidden" id="recaptcha-settings">
          <header>
            <h3 class="text-lg font-semibold">Google reCAPTCHA v3 Configuration</h3>
          </header>
          <section>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div role="group" class="field">
                <label>Site Key</label>
                <input type="text"  name="pterodactyl:captcha:recaptcha:site_key"
                  value="{{ old('pterodactyl:captcha:recaptcha:site_key', config('pterodactyl.captcha.recaptcha.site_key', '')) }}" />
                <p class="text-sm text-muted-foreground">The site key provided by Google reCAPTCHA v3. This is used in the frontend integration.</p>
              </div>
              <div role="group" class="field">
                <label>Secret Key</label>
                <input type="password"  name="pterodactyl:captcha:recaptcha:secret_key"
                  value="{{ old('pterodactyl:captcha:recaptcha:secret_key', config('pterodactyl.captcha.recaptcha.secret_key', '')) }}" />
                <p class="text-sm text-muted-foreground">The secret key provided by Google reCAPTCHA v3. This is used for server-side verification.</p>
              </div>
            </div>
            <div class="grid gap-6">
              <div class="col-span-full">
                <div class="alert" data-variant="info" role="alert">
                  <strong>reCAPTCHA v3 Setup Instructions:</strong>
                  <ol>
                    <li>Visit the <a href="https://www.google.com/recaptcha/admin" target="_blank">Google reCAPTCHA admin console</a></li>
                    <li>Create a new site and select <strong>reCAPTCHA v3</strong></li>
                    <li>Add your domain(s) to the site configuration</li>
                    <li>Copy the Site Key and Secret Key from the dashboard</li>
                    <li>Paste them into the fields above</li>
                  </ol>
                  <p><strong>Note:</strong> reCAPTCHA v3 runs invisibly in the background and returns a score (0.0-1.0) based on user interactions. A threshold of 0.5 is used by default.</p>
                </div>
              </div>
            </div>
          </section>
        </div>

        <div class="card">
          <footer>
            {{ csrf_field() }}
            <button type="submit" name="_method" value="PATCH" class="btn ml-auto" data-size="sm">Save</button>
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
