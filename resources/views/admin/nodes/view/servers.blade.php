@extends('layouts.admin')

@section('title')
    {{ $node->name }}: Servers
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">{{ $node->name }}</h1>
    <p class="text-sm text-muted-foreground">All servers currently assigned to this node.</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">Admin</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.nodes') }}">Nodes</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.nodes.view', $node->id) }}">{{ $node->name }}</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>Servers</span>
    </nav>
@endsection

@section('content')
<div class="grid gap-6">
    <div class="col-span-full">
        <div class="tabs">
            <nav role="tablist" aria-orientation="horizontal" data-variant="line">
                <a href="{{ route('admin.nodes.view', $node->id) }}" role="tab" aria-selected="false" tabindex="-1">About</a>
                <a href="{{ route('admin.nodes.view.settings', $node->id) }}" role="tab" aria-selected="false" tabindex="-1">Settings</a>
                <a href="{{ route('admin.nodes.view.configuration', $node->id) }}" role="tab" aria-selected="false" tabindex="-1">Configuration</a>
                <a href="{{ route('admin.nodes.view.allocation', $node->id) }}" role="tab" aria-selected="false" tabindex="-1">Allocation</a>
                <a href="{{ route('admin.nodes.view.servers', $node->id) }}" role="tab" aria-selected="true" tabindex="0">Servers</a>
            </nav>
        </div>
    </div>
</div>
<div class="grid gap-6">
    <div class="col-span-full">
        <div class="card">
            <header>
                <h3 class="text-lg font-semibold">Process Manager</h3>
            </header>
            <section class="table-container no-padding">
                <table class="table">
                    <tr>
                        <th>ID</th>
                        <th>Server Name</th>
                        <th>Owner</th>
                        <th>Service</th>
                    </tr>
                    @foreach($servers as $server)
                        <tr data-server="{{ $server->uuid }}">
                            <td><code>{{ $server->uuidShort }}</code></td>
                            <td><a href="{{ route('admin.servers.view', $server->id) }}">{{ $server->name }}</a></td>
                            <td><a href="{{ route('admin.users.view', $server->owner_id) }}">{{ $server->user->username }} ({{ $server->user->email }})</a></td>
                            <td>{{ $server->nest->name }} ({{ $server->egg->name }})</td>
                        </tr>
                    @endforeach
                </table>
                @if($servers->hasPages())
                    <footer class="with-border">
                        <div class="text-center">{!! $servers->render() !!}</div>
                    </footer>
                @endif
            </section>
        </div>
    </div>
</div>
@endsection
