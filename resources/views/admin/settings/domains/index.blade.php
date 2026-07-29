@extends('layouts.admin')
@include('partials/admin.settings.nav', ['activeTab' => 'domains'])

@section('title')
  @lang('admin/settings.domains.title')
@endsection

@section('content-header')
  <h1 class="text-xl font-bold">@lang('admin/settings.domains.title')</h1>
  <p class="text-sm text-muted-foreground">@lang('admin/settings.domains.desc')</p>
  <nav class="flex items-center gap-1 text-sm text-muted-foreground">
    <a href="{{ route('admin.index') }}">@lang('admin/settings.admin')</a>
    <x-icon name="chevron-right" class="size-3" />
    <a href="{{ route('admin.settings') }}">@lang('admin/settings.nav')</a>
    <x-icon name="chevron-right" class="size-3" />
    <span>@lang('admin/settings.domains.nav')</span>
  </nav>
@endsection

@section('content')
  @yield('settings::nav')
  <div class="grid gap-6">
    <div class="col-span-full">
      <div class="card">
        <header>
          <h3 class="text-lg font-semibold">@lang('admin/settings.domains.configured_domains')</h3>
          <div class="card-action">
            <a href="{{ route('admin.settings.domains.create') }}" class="btn" data-size="sm">@lang('admin/settings.domains.create_new')</a>
          </div>
        </header>
        <section>
          @if(count($domains) > 0)
            <div class="table-container">
              <table class="table">
                <thead>
                  <tr>
                    <th>@lang('admin/settings.domains.th_domain_name')</th>
                    <th>@lang('admin/settings.domains.th_dns_provider')</th>
                    <th>@lang('admin/settings.domains.th_status')</th>
                    <th>@lang('admin/settings.domains.th_default')</th>
                    <th>@lang('admin/settings.domains.th_subdomains')</th>
                    <th>@lang('admin/settings.domains.th_created')</th>
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
                          <span class="badge" data-variant="success">@lang('admin/settings.domains.status_active')</span>
                        @else
                          <span class="badge" data-variant="destructive">@lang('admin/settings.domains.status_inactive')</span>
                        @endif
                      </td>
                      <td>
                        @if($domain->is_default)
                          <span class="badge" data-variant="info">@lang('admin/settings.domains.default_badge')</span>
                        @endif
                      </td>
                      <td>
                        <span class="badge">{{ $domain->server_subdomains_count ?? 0 }}</span>
                      </td>
                      <td>{{ $domain->created_at->diffForHumans() }}</td>
                      <td class="text-center">
                        <a href="{{ route('admin.settings.domains.edit', $domain) }}" class="btn" data-size="xs">@lang('admin/settings.domains.action_edit')</a>
                        @if($domain->server_subdomains_count == 0)
                          <form action="{{ route('admin.settings.domains.destroy', $domain) }}" method="POST" class="inline" onsubmit="return confirm('{{ trans("admin.settings.domains.delete_confirm") }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn" data-size="xs" data-variant="destructive">@lang('admin/settings.domains.action_delete')</button>
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
              <h4 class="text-muted-foreground">@lang('admin/settings.domains.no_domains')</h4>
              <p class="text-muted-foreground">
                @lang('admin/settings.domains.no_domains_desc')<br>
                <a href="{{ route('admin.settings.domains.create') }}" class="btn mt-2.5" data-size="sm">@lang('admin/settings.domains.create_first')</a>
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
        if (!confirm('{{ trans("admin.settings.domains.delete_confirm_js") }}')) {
          e.preventDefault();
          return false;
        }
      });
    });
  </script>
@endsection
