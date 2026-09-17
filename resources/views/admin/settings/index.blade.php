@extends('layouts.admin')
@include('partials/admin.settings.nav', ['activeTab' => 'basic'])

@section('title')
  @lang('admin/settings.title')
@endsection

@section('content-header')
  <h1 class="text-xl font-bold">@lang('admin/settings.panel_title')</h1>
  <p class="text-sm text-muted-foreground">@lang('admin/settings.panel_desc')</p>
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
          <h3 class="text-lg font-semibold">@lang('admin/settings.panel_title')</h3>
        </header>
        <section>
          <form action="{{ route('admin.settings') }}" method="POST">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div role="group" class="field">
                <label>@lang('admin/settings.company_name')</label>
                <input type="text"  name="app:name"
                  value="{{ old('app:name', config('app.name')) }}" />
                <p class="text-sm text-muted-foreground">@lang('admin/settings.company_name_help')</p>
              </div>
              <div role="group" class="field col-span-full" data-orientation="responsive">
                <section>
                  <label>@lang('admin/settings.require_2fa')</label>
                  <p>@lang('admin/settings.require_2fa_help')</p>
                </section>
                <div role="radiogroup" aria-label="{{ trans('admin/settings.aria_2fa') }}">
                  @php
                    $level = old('pterodactyl:auth:2fa_required', config('pterodactyl.auth.2fa_required'));
                  @endphp
                  <div role="group" class="field" data-orientation="horizontal">
                    <input type="radio" name="pterodactyl:auth:2fa_required" id="2fa-none" value="0" @if ($level == 0) checked @endif />
                    <label for="2fa-none" class="font-normal">@lang('admin/settings.2fa_not_required')</label>
                  </div>
                  <div role="group" class="field" data-orientation="horizontal">
                    <input type="radio" name="pterodactyl:auth:2fa_required" id="2fa-admin" value="1" @if ($level == 1) checked @endif />
                    <label for="2fa-admin" class="font-normal">@lang('admin/settings.2fa_admin_only')</label>
                  </div>
                  <div role="group" class="field" data-orientation="horizontal">
                    <input type="radio" name="pterodactyl:auth:2fa_required" id="2fa-all" value="2" @if ($level == 2) checked @endif />
                    <label for="2fa-all" class="font-normal">@lang('admin/settings.2fa_all_users')</label>
                  </div>
                </div>
              </div>
              <div role="group" class="field">
                <label>@lang('admin/settings.default_language')</label>
                <select name="app:locale" class="select">
                  @foreach($languages as $key => $value)
                    <option value="{{ $key }}" @if(config('app.locale') === $key) selected @endif>{{ $value }}</option>
                  @endforeach
                </select>
                <p class="text-sm text-muted-foreground">@lang('admin/settings.default_language_help')</p>
              </div>
            </div>
            {!! csrf_field() !!}
            <footer>
              <button type="submit" name="_method" value="PATCH" class="btn ml-auto" data-size="sm">@lang('admin/settings.save')</button>
            </footer>
          </form>
        </section>
      </div>
    </div>
  </div>
@endsection
