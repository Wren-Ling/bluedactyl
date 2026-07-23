@extends('layouts.admin')
@include('partials/admin.settings.nav', ['activeTab' => 'advanced'])

@section('title')
  Advanced Settings
@endsection

@section('content-header')
  <h1 class="text-xl font-bold">Advanced Settings</h1>
  <p class="text-sm text-muted-foreground">Configure advanced settings for Pterodactyl.</p>
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
    <form action="" method="POST" class="flex flex-col gap-6">
      <div class="card">
      <header>
        <h3 class="text-lg font-semibold">HTTP Connections</h3>
      </header>
      <section>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div role="group" class="field">
          <label>Connection Timeout</label>
          <input type="number" required  name="pterodactyl:guzzle:connect_timeout"
            value="{{ old('pterodactyl:guzzle:connect_timeout', config('pterodactyl.guzzle.connect_timeout')) }}">
          <p class="text-sm text-muted-foreground">The amount of time in seconds to wait for a connection to be opened before
            throwing an error.</p>
        </div>
        <div role="group" class="field">
          <label>Request Timeout</label>
          <input type="number" required  name="pterodactyl:guzzle:timeout"
            value="{{ old('pterodactyl:guzzle:timeout', config('pterodactyl.guzzle.timeout')) }}">
          <p class="text-sm text-muted-foreground">The amount of time in seconds to wait for a request to be completed before
            throwing an error.</p>
        </div>
        </div>
      </section>
      </div>
      <div class="card">
      <header>
        <h3 class="text-lg font-semibold">Automatic Allocation Creation</h3>
      </header>
      <section>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div role="group" class="field">
          <label>Status</label>
          <select class="select" name="pterodactyl:client_features:allocations:enabled">
            <option value="false">Disabled</option>
            <option value="true" @if(old('pterodactyl:client_features:allocations:enabled', config('pterodactyl.client_features.allocations.enabled'))) selected @endif>Enabled</option>
          </select>
          <p class="text-sm text-muted-foreground">If enabled users will have the option to automatically create new
            allocations for their server via the frontend.</p>
        </div>
        <div role="group" class="field">
          <label>Starting Port</label>
          <input type="number"  name="pterodactyl:client_features:allocations:range_start"
            value="{{ old('pterodactyl:client_features:allocations:range_start', config('pterodactyl.client_features.allocations.range_start')) }}">
          <p class="text-sm text-muted-foreground">The starting port in the range that can be automatically allocated.</p>
        </div>
        <div role="group" class="field">
          <label>Ending Port</label>
          <input type="number"  name="pterodactyl:client_features:allocations:range_end"
            value="{{ old('pterodactyl:client_features:allocations:range_end', config('pterodactyl.client_features.allocations.range_end')) }}">
          <p class="text-sm text-muted-foreground">The ending port in the range that can be automatically allocated.</p>
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
@endsection
