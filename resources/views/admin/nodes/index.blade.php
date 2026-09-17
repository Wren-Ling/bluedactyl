@extends('layouts.admin')

@section('title')
    @lang('admin/nodes.index.title')
@endsection

@section('contentWidth', 'max-w-none')

@section('scripts')
    @parent
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">@lang('admin/nodes.index.header')</h1>
    <p class="text-sm text-muted-foreground">@lang('admin/nodes.index.header_subtitle')</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">@lang('admin/nodes.common.admin')</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>@lang('admin/nodes.common.nodes')</span>
    </nav>
@endsection

@section('content')
<div class="grid min-w-0 gap-6">
    <div class="col-span-full min-w-0">
        <div class="server-list-card card min-w-0 w-full">
            <header>
                <h3 class="text-lg font-semibold">@lang('admin/nodes.index.node_list')</h3>
                <div class="card-action">
                    <div class="search01 min-w-0">
                        <form action="{{ route('admin.nodes') }}" method="GET" class="flex items-center gap-1">
                            <div role="group" class="field min-w-0">
                                <input type="text" name="filter[name]" value="{{ request()->input('filter.name') }}" placeholder="@lang('admin/nodes.index.search_placeholder')">
                            </div>
                            <button type="submit" class="btn" data-variant="outline" data-size="sm"><x-icon name="search" class="size-4" /></button>
                            <a href="{{ route('admin.nodes.new') }}"><button type="button" class="btn rounded-r-md -ml-px" data-size="sm">@lang('admin/nodes.index.create')</button></a>
                        </form>
                    </div>
                </div>
            </header>
            <section class="min-w-0">
                <div class="table-container w-full max-w-full">
                    <table class="table w-full min-w-[760px] table-fixed">
                        <thead>
                            <tr>
                                <th class="w-[4%]"></th>
                                <th class="w-[16%]">@lang('admin/nodes.index.name')</th>
                                <th class="w-[12%]">@lang('admin/nodes.index.location')</th>
                                <th>@lang('admin/nodes.index.memory_percent')</th>
                                <th class="hidden lg:table-cell">@lang('admin/nodes.index.allocated_memory')</th>
                                <th class="hidden lg:table-cell">@lang('admin/nodes.index.total_memory')</th>
                                <th>@lang('admin/nodes.index.disk_percent')</th>
                                <th class="hidden lg:table-cell">@lang('admin/nodes.index.allocated_disk')</th>
                                <th class="hidden lg:table-cell">@lang('admin/nodes.index.total_disk')</th>
                                <th class="text-center">@lang('admin/nodes.index.servers')</th>
                                <th class="text-center hidden md:table-cell">@lang('admin/nodes.index.daemon_type')</th>
                                <th class="text-center hidden md:table-cell">@lang('admin/nodes.index.public')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($nodes as $node)
                                <tr>
                                    <td class="admin-status-icon text-center text-muted-foreground left-icon" data-action="ping" data-location="{{ url('/admin/nodes/view/' . $node->id . '/system-information') }}"><span data-status="loading"><x-icon name="refresh-cw" /></span><span data-status="online" hidden><x-icon name="circle-check" /></span><span data-status="offline" hidden><x-icon name="circle-alert" /></span></td>
                                    <td>
                                        @if($node->maintenance_mode)
                                            <span class="badge" data-variant="warning"><x-icon name="wrench" class="size-4" /></span>
                                        @endif
                                        <a href="{{ route('admin.nodes.view', $node->id) }}">{{ $node->name }}</a>
                                    </td>
                                    <td>{{ $node->location->short }}</td>
                                    <td style="color: {{ $node->memory_color }}">{{ $node->memory_percent }}%</td>
                                    <td class="hidden lg:table-cell">{{ $node->allocated_memory }}</td>
                                    <td class="hidden lg:table-cell">{{ $node->total_memory }}</td>
                                    <td style="color: {{ $node->disk_color }}">{{ $node->disk_percent }}%</td>
                                    <td class="hidden lg:table-cell">{{ $node->allocated_disk }}</td>
                                    <td class="hidden lg:table-cell">{{ $node->total_disk }}</td>
                                    <td class="text-center">{{ $node->servers_count }}</td>
                                    <td class="text-center hidden md:table-cell">{{ $node->daemonType }}</td>
                                    <td class="text-center hidden md:table-cell"><x-icon name="{{ $node->public ? 'eye' : 'eye-off' }}" class="size-4" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
            @if($nodes->hasPages())
                <footer class="flex items-center justify-center">
                    @include('admin.partials.pagination', ['paginator' => $nodes->appends(['filter' => Request::input('filter')])])
                </footer>
            @endif
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>
    (async function pingNodes() {
        await Promise.all(Array.from(document.querySelectorAll('td[data-action="ping"]'), async function (element) {
            let status = 'offline';
            try {
                const response = await fetch(element.dataset.location, {
                    headers: { Accept: 'application/json' },
                    signal: AbortSignal.timeout(5000),
                });
                if (!response.ok) throw new Error('Node probe failed');
                const data = await response.json();
                element.title = 'v' + data.version;
                status = 'online';
            } catch (error) {
                element.title = @json(trans('admin/nodes.index.ping_error'));
            }
            element.querySelectorAll('[data-status]').forEach(function (icon) {
                icon.hidden = icon.dataset.status !== status;
            });
            element.style.color = status === 'online' ? '#50af51' : '#d9534f';
        }));
        setTimeout(pingNodes, 10000);
    })();
    </script>
@endsection
