@extends('layouts.admin')
@include('partials/admin.settings.nav', ['activeTab' => 'basic'])

@section('title')
  Settings
@endsection

@section('content-header')
  <h1 class="text-xl font-bold">Panel Settings</h1>
  <p class="text-sm text-muted-foreground">Configure Pterodactyl to your liking.</p>
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
          <h3 class="text-lg font-semibold">Panel Settings</h3>
        </header>
        <section>
          <form action="{{ route('admin.settings') }}" method="POST">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div role="group" class="field">
                <label>Company Name</label>
                <input type="text"  name="app:name"
                  value="{{ old('app:name', config('app.name')) }}" />
                <p class="text-sm text-muted-foreground">This is the name that is used throughout the panel and in emails sent to clients.</p>
              </div>
              <div role="group" class="field col-span-full" data-orientation="responsive">
                <section>
                  <label>Require 2-Factor Authentication</label>
                  <p>If enabled, any account falling into the selected grouping will be required to have 2-Factor authentication enabled to use the Panel.</p>
                </section>
                <div role="radiogroup" aria-label="2FA requirement">
                  @php
                    $level = old('pterodactyl:auth:2fa_required', config('pterodactyl.auth.2fa_required'));
                  @endphp
                  <div role="group" class="field" data-orientation="horizontal">
                    <input type="radio" name="pterodactyl:auth:2fa_required" id="2fa-none" value="0" @if ($level == 0) checked @endif />
                    <label for="2fa-none" class="font-normal">Not Required</label>
                  </div>
                  <div role="group" class="field" data-orientation="horizontal">
                    <input type="radio" name="pterodactyl:auth:2fa_required" id="2fa-admin" value="1" @if ($level == 1) checked @endif />
                    <label for="2fa-admin" class="font-normal">Admin Only</label>
                  </div>
                  <div role="group" class="field" data-orientation="horizontal">
                    <input type="radio" name="pterodactyl:auth:2fa_required" id="2fa-all" value="2" @if ($level == 2) checked @endif />
                    <label for="2fa-all" class="font-normal">All Users</label>
                  </div>
                </div>
              </div>
              <div role="group" class="field">
                <label>Default Language</label>
                <select name="app:locale" class="select">
                  @foreach($languages as $key => $value)
                    <option value="{{ $key }}" @if(config('app.locale') === $key) selected @endif>{{ $value }}</option>
                  @endforeach
                </select>
                <p class="text-sm text-muted-foreground">The default language to use when rendering UI components.</p>
              </div>
            </div>
          </form>
        </section>
        <footer>
          {!! csrf_field() !!}
          <button type="submit" name="_method" value="PATCH" class="btn ml-auto" data-size="sm">Save</button>
        </footer>
      </div>
    </div>
  </div>
@endsection
