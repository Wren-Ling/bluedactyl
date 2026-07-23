@extends('layouts.admin')

@section('title')
    List Nodes
@endsection

@section('scripts')
    @parent
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">Nodes</h1>
    <p class="text-sm text-muted-foreground">All nodes available on the system.</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">Admin</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>Nodes</span>
    </nav>
@endsection

@section('content')
<div class="grid gap-6">
    <div class="col-span-full">
        <div class="card">
            <header>
                <h3 class="text-lg font-semibold">Node List</h3>
                <div class="card-action">
                    <div class="search01">
                        <form action="{{ route('admin.nodes') }}" method="GET" class="flex items-center gap-1">
                            <div role="group" class="field">
                                <input type="text" name="filter[name]" value="{{ request()->input('filter.name') }}" placeholder="Search Nodes">
                            </div>
                            <button type="submit" class="btn" data-variant="outline" data-size="sm"><x-icon name="search" class="size-4" /></button>
                            <a href="{{ route('admin.nodes.new') }}"><button type="button" class="btn rounded-r-md -ml-px" data-size="sm">Create New</button></a>
                        </form>
                    </div>
                </div>
            </header>
            <section>
                <div class="table-container">
                    <table class="table">
                        <tbody>
                            <tr>
                                <th></th>
                                <th>Name</th>
                                <th>Location</th>
                                <th>Memory%</th>
                                <th class="hidden lg:table-cell">Allocated Memory</th>
                                <th class="hidden lg:table-cell">Total Memory</th>
                                <th>Disk%</th>
                                <th class="hidden lg:table-cell">Allocated Disk</th>
                                <th class="hidden lg:table-cell">Total Disk</th>
                                <th class="text-center">Servers</th>
                                <th class="text-center hidden md:table-cell">Daemon Type</th>
                                <th class="text-center hidden md:table-cell">Public</th>
                            </tr>
                            @foreach ($nodes as $node)
                                <tr>
                                    <td class="text-center text-muted-foreground left-icon" data-action="ping" data-secret="{{ $node->getDecryptedKey() }}" data-location="{{ $node->scheme }}://{{ $node->fqdn }}:{{ $node->daemonListen }}/api/system"><x-icon name="refresh-cw" class="size-4" /></td>
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
                    <div class="text-center">{!! $nodes->appends(['query' => Request::input('query')])->render() !!}</div>
                </footer>
            @endif
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>
    (function pingNodes() {
        $('td[data-action="ping"]').each(function(i, element) {
            $.ajax({
                type: 'GET',
                url: $(element).data('location'),
                headers: {
                    'Authorization': 'Bearer ' + $(element).data('secret'),
                },
                timeout: 5000
            }).done(function (data) {
                $(element).attr('title', 'v' + data.version);
                $(element).removeClass('text-muted-foreground').find('svg').removeClass().addClass('lucide lucide-check-circle').css('color', '#50af51');
            }).fail(function (error) {
                var errorText = 'Error connecting to node! Check browser console for details.';
                try {
                    errorText = error.responseJSON.errors[0].detail || errorText;
                } catch (ex) {}

                $(element).removeClass('text-muted-foreground').find('svg').removeClass().addClass('lucide lucide-alert-circle').css('color', '#d9534f');
                $(element).attr('title', errorText);
            });
        }).promise().done(function () {
            setTimeout(pingNodes, 10000);
        });
    })();
    </script>
@endsection
