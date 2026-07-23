@extends('layouts.admin')
@include('partials/admin.settings.nav', ['activeTab' => 'domains'])

@section('title')
  Domain Management
@endsection

@section('content-header')
  <h1 class="text-xl font-bold">Domain Management</h1>
  <p class="text-sm text-muted-foreground">Configure DNS domains for subdomain management.</p>
  <nav class="flex items-center gap-1 text-sm text-muted-foreground">
    <a href="{{ route('admin.index') }}">Admin</a>
    <x-icon name="chevron-right" class="size-3" />
    <a href="{{ route('admin.settings') }}">Settings</a>
    <x-icon name="chevron-right" class="size-3" />
    <span>Domains</span>
  </nav>
@endsection

@section('content')
  @yield('settings::nav')
  <div class="grid gap-6">
    <div class="col-span-full">
      <div class="card">
        <header>
          <h3 class="text-lg font-semibold">Configured Domains</h3>
          <div class="card-action">
            <a href="{{ route('admin.settings.domains.create') }}" class="btn" data-size="sm">Create New Domain</a>
          </div>
        </header>
        <section>
          @if(count($domains) > 0)
            <div class="table-container">
              <table class="table">
                <thead>
                  <tr>
                    <th>Domain Name</th>
                    <th>DNS Provider</th>
                    <th>Status</th>
                    <th>Default</th>
                    <th>Subdomains</th>
                    <th>Created</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($domains as $domain)
                    <tr>
                      <td><code>{{ $domain->name }}</code></td>
                      <td>
                        <span class="badge" data-variant="primary">{{ ucfirst($domain->dns_provider) }}</span>
                      </td>
                      <td>
                        @if($domain->is_active)
                          <span class="badge" data-variant="success">Active</span>
                        @else
                          <span class="badge" data-variant="destructive">Inactive</span>
                        @endif
                      </td>
                      <td>
                        @if($domain->is_default)
                          <span class="badge" data-variant="info">Default</span>
                        @endif
                      </td>
                      <td>
                        <span class="badge">{{ $domain->server_subdomains_count ?? 0 }}</span>
                      </td>
                      <td>{{ $domain->created_at->diffForHumans() }}</td>
                      <td class="text-center">
                        <a href="{{ route('admin.settings.domains.edit', $domain) }}" class="btn" data-size="xs">Edit</a>
                        @if($domain->server_subdomains_count == 0)
                          <form action="{{ route('admin.settings.domains.destroy', $domain) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this domain?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn" data-size="xs" data-variant="destructive">Delete</button>
                          </form>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="text-center p-12">
              <h4 class="text-muted-foreground">No domains configured</h4>
              <p class="text-muted-foreground">
                Configure DNS domains to enable subdomain management for servers.<br>
                <a href="{{ route('admin.settings.domains.create') }}" class="btn mt-2.5" data-size="sm">Create Your First Domain</a>
              </p>
            </div>
          @endif
        </section>
      </div>
    </div>
  </div>
@endsection

@section('footer-scripts')
  @parent
  <script>
    $(document).ready(function() {
      $('[data-variant="destructive"]').click(function(e) {
        if (!confirm('Are you sure you want to delete this domain? This action cannot be undone.')) {
          e.preventDefault();
          return false;
        }
      });
    });
  </script>
@endsection
