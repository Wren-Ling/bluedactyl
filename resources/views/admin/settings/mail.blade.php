@extends('layouts.admin')
@include('partials/admin.settings.nav', ['activeTab' => 'mail'])

@section('title')
  @lang('admin/settings.mail.title')
@endsection

@section('content-header')
  <h1 class="text-xl font-bold">@lang('admin/settings.mail.title')</h1>
  <p class="text-sm text-muted-foreground">@lang('admin/settings.mail.desc')</p>
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
    <div class="card">
      <header>
      <h3 class="text-lg font-semibold">@lang('admin/settings.mail.email_settings')</h3>
      </header>
      @if($disabled)
      <section>
      <div class="grid gap-6">
      <div class="col-span-full">
      <div class="alert" data-variant="info" role="alert">
        {!! trans('admin/settings.mail.disabled_alert') !!}
      </div>
      </div>
      </div>
      </section>
    @else
      <section>
        <form action="{{ route('admin.settings.mail') }}" method="POST">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-6">
        <div role="group" class="field md:col-span-3">
          <label>@lang('admin/settings.mail.smtp_host')</label>
          <input required type="text"  name="mail:mailers:smtp:host"
          value="{{ old('mail:mailers:smtp:host', config('mail.mailers.smtp.host')) }}" />
          <p class="text-sm text-muted-foreground">@lang('admin/settings.mail.smtp_host_help')</p>
        </div>
        <div role="group" class="field md:col-span-1">
          <label>@lang('admin/settings.mail.smtp_port')</label>
          <input required type="number"  name="mail:mailers:smtp:port"
          value="{{ old('mail:mailers:smtp:port', config('mail.mailers.smtp.port')) }}" />
          <p class="text-sm text-muted-foreground">@lang('admin/settings.mail.smtp_port_help')</p>
        </div>
        <div role="group" class="field md:col-span-2">
          <label>@lang('admin/settings.mail.encryption')</label>
          @php
        $encryption = old('mail:mailers:smtp:encryption', config('mail.mailers.smtp.encryption'));
        @endphp
          <select name="mail:mailers:smtp:encryption" class="select">
          <option value="" @if($encryption === '') selected @endif>@lang('admin/settings.mail.encryption_none')</option>
          <option value="tls" @if($encryption === 'tls') selected @endif>@lang('admin/settings.mail.encryption_tls')</option>
          <option value="ssl" @if($encryption === 'ssl') selected @endif>@lang('admin/settings.mail.encryption_ssl')</option>
          </select>
          <p class="text-sm text-muted-foreground">@lang('admin/settings.mail.encryption_help')</p>
        </div>
        <div role="group" class="field md:col-span-3">
          <label>@lang('admin/settings.mail.username')</label>
          <input type="text"  name="mail:mailers:smtp:username"
          value="{{ old('mail:mailers:smtp:username', config('mail.mailers.smtp.username')) }}" />
          <p class="text-sm text-muted-foreground">@lang('admin/settings.mail.username_help')</p>
        </div>
        <div role="group" class="field md:col-span-3">
          <label>@lang('admin/settings.mail.password')</label>
          <input type="password"  name="mail:mailers:smtp:password" />
          <p class="text-sm text-muted-foreground">{!! trans('admin/settings.mail.password_help') !!}</p>
        </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <hr class="col-span-full" />
        <div role="group" class="field">
          <label>@lang('admin/settings.mail.mail_from')</label>
          <input required type="email"  name="mail:from:address"
          value="{{ old('mail:from:address', config('mail.from.address')) }}" />
          <p class="text-sm text-muted-foreground">@lang('admin/settings.mail.mail_from_help')</p>
        </div>
        <div role="group" class="field">
          <label>@lang('admin/settings.mail.mail_from_name')</label>
          <input type="text"  name="mail:from:name"
          value="{{ old('mail:from:name', config('mail.from.name')) }}" />
          <p class="text-sm text-muted-foreground">@lang('admin/settings.mail.mail_from_name_help')</p>
        </div>
        </div>
        </form>
      </section>
      <footer>
        {{ csrf_field() }}
        <div class="ml-auto">
        <button type="button" id="testButton" class="btn" data-size="sm">@lang('admin/settings.test')</button>
        <button type="button" id="saveButton" class="btn" data-size="sm">@lang('admin/settings.save')</button>
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
    if (confirm('{{ trans("admin.settings.mail.test_confirm") }}')) {
      $.ajax({
      method: 'POST',
      url: '/admin/settings/mail/test',
      headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() }
      }).fail(function (jqXHR) {
      showErrorDialog(jqXHR, 'test');
      }).done(function () {
      alert('{{ trans("admin.settings.mail.test_success") }}');
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

    alert('{{ trans("admin.settings.mail.error_occurred") }} ' + verb + ' {{ trans("admin.settings.mail.error_suffix") }} ' + errorText);
    }

    $(document).ready(function () {
    $('#testButton').on('click', saveAndTestSettings);
    $('#saveButton').on('click', function () {
      saveSettings().done(function () {
      alert('{{ trans("admin.settings.mail.save_success") }}');
      });
    });
    });
  </script>
@endsection
