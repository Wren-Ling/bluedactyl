@extends('layouts.admin')

@section('title')
    {{ $node->name }}: @lang('admin/nodes.common.servers')
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">{{ $node->name }}</h1>
    <p class="text-sm text-muted-foreground">@lang('admin/nodes.servers.header_subtitle')</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">@lang('admin/nodes.common.admin')</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.nodes') }}">@lang('admin/nodes.common.nodes')</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.nodes.view', $node->id) }}">{{ $node->name }}</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>@lang('admin/nodes.common.servers')</span>
    </nav>
@endsection

@section('content')
<div class="grid gap-6">
    <div class="col-span-full">
        <div class="tabs">
            <nav role="tablist" aria-orientation="horizontal" data-variant="line">
                <a href="{{ route('admin.nodes.view', $node->id) }}" role="tab" aria-selected="false" tabindex="-1">@lang('admin/nodes.common.about')</a>
                <a href="{{ route('admin.nodes.view.settings', $node->id) }}" role="tab" aria-selected="false" tabindex="-1">@lang('admin/nodes.common.settings')</a>
                <a href="{{ route('admin.nodes.view.configuration', $node->id) }}" role="tab" aria-selected="false" tabindex="-1">@lang('admin/nodes.common.configuration')</a>
                <a href="{{ route('admin.nodes.view.allocation', $node->id) }}" role="tab" aria-selected="false" tabindex="-1">@lang('admin/nodes.common.allocation')</a>
                <a href="{{ route('admin.nodes.view.servers', $node->id) }}" role="tab" aria-selected="true" tabindex="0">@lang('admin/nodes.common.servers')</a>
            </nav>
        </div>
    </div>
</div>
<div class="grid gap-6">
    <div class="col-span-full">
        <div class="card">
            <header>
                <h3 class="text-lg font-semibold">@lang('admin/nodes.servers.process_manager')</h3>
            </header>
            <section class="table-container no-padding">
                <table class="table">
                    <tr>
                        <th>@lang('admin/nodes.servers.id')</th>
                        <th>@lang('admin/nodes.servers.server_name')</th>
                        <th>@lang('admin/nodes.servers.owner')</th>
                        <th>@lang('admin/nodes.servers.service')</th>
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
                        @include('admin.partials.pagination', ['paginator' => $servers])
                    </footer>
                @endif
            </section>
        </div>
    </div>
</div>
@endsection
