@extends('layouts.admin')
@include('partials/admin.settings.nav', ['activeTab' => 'advanced'])

@section('title')
  @lang('admin/settings.advanced.title')
@endsection

@section('content-header')
  <h1 class="text-xl font-bold">@lang('admin/settings.advanced.title')</h1>
  <p class="text-sm text-muted-foreground">@lang('admin/settings.advanced.desc')</p>
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
    <form action="" method="POST" class="flex flex-col gap-6">
      <div class="card">
      <header>
        <h3 class="text-lg font-semibold">@lang('admin/settings.advanced.http_connections')</h3>
      </header>
      <section>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div role="group" class="field">
          <label>@lang('admin/settings.advanced.connection_timeout')</label>
          <input type="number" required  name="pterodactyl:guzzle:connect_timeout"
            value="{{ old('pterodactyl:guzzle:connect_timeout', config('pterodactyl.guzzle.connect_timeout')) }}">
          <p class="text-sm text-muted-foreground">@lang('admin/settings.advanced.connection_timeout_help')</p>
        </div>
        <div role="group" class="field">
          <label>@lang('admin/settings.advanced.request_timeout')</label>
          <input type="number" required  name="pterodactyl:guzzle:timeout"
            value="{{ old('pterodactyl:guzzle:timeout', config('pterodactyl.guzzle.timeout')) }}">
          <p class="text-sm text-muted-foreground">@lang('admin/settings.advanced.request_timeout_help')</p>
        </div>
        </div>
      </section>
      </div>
      <div class="card">
      <header>
        <h3 class="text-lg font-semibold">@lang('admin/settings.advanced.auto_allocation')</h3>
      </header>
      <section>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div role="group" class="field">
          <label>@lang('admin/settings.advanced.status')</label>
          <select class="select" name="pterodactyl:client_features:allocations:enabled">
            <option value="false">@lang('admin/settings.advanced.disabled')</option>
            <option value="true" @if(old('pterodactyl:client_features:allocations:enabled', config('pterodactyl.client_features.allocations.enabled'))) selected @endif>@lang('admin/settings.advanced.enabled')</option>
          </select>
          <p class="text-sm text-muted-foreground">@lang('admin/settings.advanced.status_help')</p>
        </div>
        <div role="group" class="field">
          <label>@lang('admin/settings.advanced.starting_port')</label>
          <input type="number"  name="pterodactyl:client_features:allocations:range_start"
            value="{{ old('pterodactyl:client_features:allocations:range_start', config('pterodactyl.client_features.allocations.range_start')) }}">
          <p class="text-sm text-muted-foreground">@lang('admin/settings.advanced.starting_port_help')</p>
        </div>
        <div role="group" class="field">
          <label>@lang('admin/settings.advanced.ending_port')</label>
          <input type="number"  name="pterodactyl:client_features:allocations:range_end"
            value="{{ old('pterodactyl:client_features:allocations:range_end', config('pterodactyl.client_features.allocations.range_end')) }}">
          <p class="text-sm text-muted-foreground">@lang('admin/settings.advanced.ending_port_help')</p>
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
@endsection
