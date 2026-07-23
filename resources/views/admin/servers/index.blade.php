@extends('layouts.admin')

@section('title')
    List Servers
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">Servers</h1>
    <p class="text-sm text-muted-foreground">All servers available on the system.</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">Admin</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>Servers</span>
    </nav>
@endsection

@section('content')
<div class="grid gap-6">
    <div class="col-span-full">
        <div class="card">
            <header>
                <h3 class="text-lg font-semibold">Server List</h3>
                <div class="card-action">
                    <div class="search01">
                        <form action="{{ route('admin.servers') }}" method="GET" class="flex items-center gap-1">
                            <div role="group" class="field">
                                <input type="text" name="filter[*]" value="{{ request()->input()['filter']['*'] ?? '' }}" placeholder="Search Servers">
                            </div>
                            <button type="submit" class="btn" data-variant="outline" data-size="sm"><x-icon name="search" class="size-4" /></button>
                            <a href="{{ route('admin.servers.new') }}"><button type="button" class="btn rounded-r-md -ml-px" data-size="sm">Create New</button></a>
                        </form>
                    </div>
                </div>
            </header>
            <section>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Server Name</th>
                                <th>UUID</th>
                                <th>Owner</th>
                                <th>Node</th>
                                <th>Connection</th>
                                <!-- <th>Domain</th> -->
                                <th></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($servers as $server)
                                <tr data-server="{{ $server->uuidShort }}">
                                    <td><a href="{{ route('admin.servers.view', $server->id) }}">{{ $server->name }}</a></td>
                                    <td><code title="{{ $server->uuid }}">{{ $server->uuid }}</code></td>
                                    <td><a href="{{ route('admin.users.view', $server->user->id) }}">{{ $server->user->username }} ({{ $server->user->email }})</a></td>
                                    <td><a href="{{ route('admin.nodes.view', $server->node->id) }}">{{ $server->node->name }}</a></td>
                                    <td>
                                        <code>{{ $server->allocation->alias }}:{{ $server->allocation->port }}</code>
                                    </td>
                                    <!-- <td>{{ $server->domain }}</td> -->
                                    <td class="text-center">
                                        @if($server->isSuspended())
                                            <span class="badge" data-variant="destructive">Suspended</span>
                                        @elseif(! $server->isInstalled())
                                            <span class="badge" data-variant="warning">Installing</span>
                                        @else
                                            <span class="badge" data-variant="success">Active</span>
                                        @endif

                                        @if($server->exclude_from_resource_calculation)
                                            <br><small><span class="badge" data-variant="info" title="Excluded from resource calculations">Excluded</span></small>
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
                    <div class="text-center">{!! $servers->appends(['filter' => Request::input('filter')])->render() !!}</div>
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
