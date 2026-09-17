@extends('layouts.admin')

@section('title')
    @lang('admin/server.index.title')
@endsection

@section('contentWidth', 'max-w-none')

@section('content-header')
    <h1 class="text-xl font-bold">@lang('admin/server.index.header')</h1>
    <p class="text-sm text-muted-foreground">@lang('admin/server.index.description')</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">@lang('admin/server.index.breadcrumb_admin')</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>@lang('admin/server.index.breadcrumb_servers')</span>
    </nav>
@endsection

@section('content')
<div class="grid min-w-0 gap-6">
    <div class="col-span-full min-w-0">
        <div class="server-list-card card min-w-0 w-full">
            <header>
                <h3 class="text-lg font-semibold">@lang('admin/server.index.card_title')</h3>
                <div class="card-action">
                    <div class="search01 min-w-0">
                        <form action="{{ route('admin.servers') }}" method="GET" class="flex items-center gap-1">
                            <div role="group" class="field min-w-0">
                                <input type="text" name="filter[*]" value="{{ request()->input()['filter']['*'] ?? '' }}" placeholder="{{ trans('admin/server.index.search_placeholder') }}">
                            </div>
                            <button type="submit" class="btn" data-variant="outline" data-size="sm"><x-icon name="search" class="size-4" /></button>
                            <a href="{{ route('admin.servers.new') }}"><button type="button" class="btn rounded-r-md -ml-px" data-size="sm">@lang('admin/server.index.create')</button></a>
                        </form>
                    </div>
                </div>
            </header>
            <section class="min-w-0">
                <div class="table-container w-full max-w-full">
                    <table class="table w-full min-w-[760px] table-fixed">
                        <thead>
                            <tr>
                                <th class="w-[17%]">@lang('admin/server.index.server_name')</th>
                                <th class="w-[11%]">@lang('admin/server.index.uuid')</th>
                                <th class="w-[18%]">@lang('admin/server.index.owner')</th>
                                <th class="w-[15%]">@lang('admin/server.index.node')</th>
                                <th class="w-[18%]">@lang('admin/server.index.connection')</th>
                                <!-- <th>Domain</th> -->
                                <th class="w-[13%]"></th>
                                <th class="w-[8%]"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($servers as $server)
                                <tr data-server="{{ $server->uuidShort }}">
                                    <td class="truncate" title="{{ $server->name }}"><a href="{{ route('admin.servers.view', $server->id) }}">{{ $server->name }}</a></td>
                                    <td><code title="{{ $server->uuid }}">{{ $server->uuidShort }}</code></td>
                                    <td class="truncate" title="{{ $server->user->username }} ({{ $server->user->email }})"><a href="{{ route('admin.users.view', $server->user->id) }}">{{ $server->user->username }}</a></td>
                                    <td class="truncate" title="{{ $server->node->name }}"><a href="{{ route('admin.nodes.view', $server->node->id) }}">{{ $server->node->name }}</a></td>
                                    <td class="truncate" title="{{ $server->allocation->alias }}:{{ $server->allocation->port }}">
                                        <code>{{ $server->allocation->alias }}:{{ $server->allocation->port }}</code>
                                    </td>
                                    <!-- <td>{{ $server->domain }}</td> -->
                                    <td class="text-center">
                                        @if($server->isSuspended())
                                            <span class="badge" data-variant="destructive">@lang('admin/server.index.suspended')</span>
                                        @elseif(! $server->isInstalled())
                                            <span class="badge" data-variant="warning">@lang('admin/server.index.installing')</span>
                                        @else
                                            <span class="badge" data-variant="success">@lang('admin/server.index.active')</span>
                                        @endif

                                        @if($server->exclude_from_resource_calculation)
                                            <br><small><span class="badge" data-variant="info" title="@lang('admin/server.index.excluded_title')">@lang('admin/server.index.excluded')</span></small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a class="btn" data-size="xs" data-variant="outline" href="/server/{{ $server->uuidShort }}"><x-icon name="wrench" class="size-4" /></a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
            @if($servers->hasPages())
                <footer class="flex items-center justify-center">
                    @include('admin.partials.pagination', ['paginator' => $servers->appends(['filter' => Request::input('filter')])])
                </footer>
            @endif
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>
        $('.console-popout').on('click', function (event) {
            event.preventDefault();
            window.open($(this).attr('href'), 'Pterodactyl Console', 'width=800,height=400');
        });
    </script>
@endsection
