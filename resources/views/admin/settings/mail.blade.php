@extends('layouts.admin')
@include('partials/admin.settings.nav', ['activeTab' => 'mail'])

@section('title')
  Mail Settings
@endsection

@section('content-header')
  <h1 class="text-xl font-bold">Mail Settings</h1>
  <p class="text-sm text-muted-foreground">Configure how Pterodactyl should handle sending emails.</p>
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
    <div class="card">
      <header>
      <h3 class="text-lg font-semibold">Email Settings</h3>
      </header>
      @if($disabled)
      <section>
      <div class="grid gap-6">
      <div class="col-span-full">
      <div class="alert" data-variant="info" role="alert">
        This interface is limited to instances using SMTP as the mail driver. Please either use
        <code>php artisan p:environment:mail</code> command to update your email settings, or set
        <code>MAIL_DRIVER=smtp</code> in your environment file.
      </div>
      </div>
      </div>
      </section>
    @else
      <section>
        <form action="{{ route('admin.settings.mail') }}" method="POST">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-6">
        <div role="group" class="field md:col-span-3">
          <label>SMTP Host</label>
          <input required type="text"  name="mail:mailers:smtp:host"
          value="{{ old('mail:mailers:smtp:host', config('mail.mailers.smtp.host')) }}" />
          <p class="text-sm text-muted-foreground">Enter the SMTP server address that mail should be sent through.</p>
        </div>
        <div role="group" class="field md:col-span-1">
          <label>SMTP Port</label>
          <input required type="number"  name="mail:mailers:smtp:port"
          value="{{ old('mail:mailers:smtp:port', config('mail.mailers.smtp.port')) }}" />
          <p class="text-sm text-muted-foreground">Enter the SMTP server port that mail should be sent through.</p>
        </div>
        <div role="group" class="field md:col-span-2">
          <label>Encryption</label>
          @php
        $encryption = old('mail:mailers:smtp:encryption', config('mail.mailers.smtp.encryption'));
        @endphp
          <select name="mail:mailers:smtp:encryption" class="select">
          <option value="" @if($encryption === '') selected @endif>None</option>
          <option value="tls" @if($encryption === 'tls') selected @endif>Transport Layer Security (TLS)</option>
          <option value="ssl" @if($encryption === 'ssl') selected @endif>Secure Sockets Layer (SSL)</option>
          </select>
          <p class="text-sm text-muted-foreground">Select the type of encryption to use when sending mail.</p>
        </div>
        <div role="group" class="field md:col-span-3">
          <label>Username</label>
          <input type="text"  name="mail:mailers:smtp:username"
          value="{{ old('mail:mailers:smtp:username', config('mail.mailers.smtp.username')) }}" />
          <p class="text-sm text-muted-foreground">The username to use when connecting to the SMTP server.</p>
        </div>
        <div role="group" class="field md:col-span-3">
          <label>Password</label>
          <input type="password"  name="mail:mailers:smtp:password" />
          <p class="text-sm text-muted-foreground">The password to use in conjunction with the SMTP username. Leave blank to
          continue using the existing password. To set the password to an empty value enter <code>!e</code> into
          the field.</p>
        </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <hr class="col-span-full" />
        <div role="group" class="field">
          <label>Mail From</label>
          <input required type="email"  name="mail:from:address"
          value="{{ old('mail:from:address', config('mail.from.address')) }}" />
          <p class="text-sm text-muted-foreground">Enter an email address that all outgoing emails will originate from.</p>
        </div>
        <div role="group" class="field">
          <label>Mail From Name</label>
          <input type="text"  name="mail:from:name"
          value="{{ old('mail:from:name', config('mail.from.name')) }}" />
          <p class="text-sm text-muted-foreground">The name that emails should appear to come from.</p>
        </div>
        </div>
        </form>
      </section>
      <footer>
        {{ csrf_field() }}
        <div class="ml-auto">
        <button type="button" id="testButton" class="btn" data-size="sm">Test</button>
        <button type="button" id="saveButton" class="btn" data-size="sm">Save</button>
        </div>
      </footer>
    @endif
    </div>
    </div>
  </div>
@endsection

@section('footer-scripts')
  @parent

  <script>
    function saveSettings() {
    return $.ajax({
      method: 'PATCH',
      url: '/admin/settings/mail',
      contentType: 'application/json',
      data: JSON.stringify({
      'mail:mailers:smtp:host': $('input[name="mail:mailers:smtp:host"]').val(),
      'mail:mailers:smtp:port': $('input[name="mail:mailers:smtp:port"]').val(),
      'mail:mailers:smtp:encryption': $('select[name="mail:mailers:smtp:encryption"]').val(),
      'mail:mailers:smtp:username': $('input[name="mail:mailers:smtp:username"]').val(),
      'mail:mailers:smtp:password': $('input[name="mail:mailers:smtp:password"]').val(),
      'mail:from:address': $('input[name="mail:from:address"]').val(),
      'mail:from:name': $('input[name="mail:from:name"]').val()
      }),
      headers: { 'X-CSRF-Token': $('input[name="_token"]').val() }
    }).fail(function (jqXHR) {
      showErrorDialog(jqXHR, 'save');
    });
    }

    function testSettings() {
    if (confirm('Click OK to begin the mail test.')) {
      $.ajax({
      method: 'POST',
      url: '/admin/settings/mail/test',
      headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() }
      }).fail(function (jqXHR) {
      showErrorDialog(jqXHR, 'test');
      }).done(function () {
      alert('The test message was sent successfully.');
      });
    }
    }

    function saveAndTestSettings() {
    saveSettings().done(testSettings);
    }

    function showErrorDialog(jqXHR, verb) {
    console.error(jqXHR);
    var errorText = '';
    if (!jqXHR.responseJSON) {
      errorText = jqXHR.responseText;
    } else if (jqXHR.responseJSON.error) {
      errorText = jqXHR.responseJSON.error;
    } else if (jqXHR.responseJSON.errors) {
      $.each(jqXHR.responseJSON.errors, function (i, v) {
      if (v.detail) {
        errorText += v.detail + ' ';
      }
      });
    }

    alert('An error occurred while attempting to ' + verb + ' mail settings: ' + errorText);
    }

    $(document).ready(function () {
    $('#testButton').on('click', saveAndTestSettings);
    $('#saveButton').on('click', function () {
      saveSettings().done(function () {
      alert('Mail settings have been updated successfully and the queue worker was restarted to apply these changes.');
      });
    });
    });
  </script>
@endsection
