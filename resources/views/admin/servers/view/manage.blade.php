@extends('layouts.admin')

@section('title')
    @lang('admin/server.overview.title') — {{ $server->name }}: @lang('admin/server.manage.title')
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">{{ $server->name }}</h1>
    <p class="text-sm text-muted-foreground">@lang('admin/server.manage.description')</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">@lang('admin/server.manage.breadcrumb_admin')</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.servers') }}">@lang('admin/server.manage.breadcrumb_servers')</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.servers.view', $server->id) }}">{{ $server->name }}</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>@lang('admin/server.manage.breadcrumb_manage')</span>
    </nav>
@endsection

@section('content')
    @include('admin.servers.partials.navigation')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <div class="card" data-variant="destructive">
                <header>
                    <h3 class="text-lg font-semibold">@lang('admin/server.manage.reinstall_server')</h3>
                </header>
                <section>
                    <p>@lang('admin/server.manage.reinstall_desc')</p>
                </section>
                <footer>
                    @if($server->isInstalled())
                        <form action="{{ route('admin.servers.view.manage.reinstall', $server->id) }}" method="POST">
                            {!! csrf_field() !!}
                            <button type="submit" class="btn" data-variant="destructive">@lang('admin/server.manage.reinstall_button')</button>
                        </form>
                    @else
                        <button class="btn" data-variant="destructive" disabled>@lang('admin/server.manage.reinstall_disabled')</button>
                    @endif
                </footer>
            </div>
        </div>
        <div>
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">@lang('admin/server.manage.install_status')</h3>
                </header>
                <section>
                    <p>@lang('admin/server.manage.install_status_desc')</p>
                </section>
                <footer>
                    <form action="{{ route('admin.servers.view.manage.toggle', $server->id) }}" method="POST">
                        {!! csrf_field() !!}
                        <button type="submit" class="btn">@lang('admin/server.manage.toggle_install')</button>
                    </form>
                </footer>
            </div>
        </div>

        @if(! $server->isSuspended())
            <div>
                <div class="card" data-variant="warning">
                    <header>
                        <h3 class="text-lg font-semibold">@lang('admin/server.manage.suspend_server')</h3>
                    </header>
                    <section>
                        <p>@lang('admin/server.manage.suspend_desc')</p>
                    </section>
                    <footer>
                        <form action="{{ route('admin.servers.view.manage.suspension', $server->id) }}" method="POST">
                            {!! csrf_field() !!}
                            <input type="hidden" name="action" value="suspend" />
                            <button type="submit" class="btn" data-variant="secondary" @if(! is_null($server->transfer)) disabled @endif>@lang('admin/server.manage.suspend_button')</button>
                        </form>
                    </footer>
                </div>
            </div>
        @else
            <div>
                <div class="card" data-variant="success">
                    <header>
                        <h3 class="text-lg font-semibold">@lang('admin/server.manage.unsuspend_server')</h3>
                    </header>
                    <section>
                        <p>@lang('admin/server.manage.unsuspend_desc')</p>
                    </section>
                    <footer>
                        <form action="{{ route('admin.servers.view.manage.suspension', $server->id) }}" method="POST">
                            {!! csrf_field() !!}
                            <input type="hidden" name="action" value="unsuspend" />
                            <button type="submit" class="btn">@lang('admin/server.manage.unsuspend_button')</button>
                        </form>
                    </footer>
                </div>
            </div>
        @endif

        @if(is_null($server->transfer))
            <div>
                <div class="card" data-variant="success">
                    <header>
                        <h3 class="text-lg font-semibold">@lang('admin/server.manage.transfer_server')</h3>
                    </header>
                    <section>
                        <p>
                            @lang('admin/server.manage.transfer_desc')
                        </p>
                    </section>

                    <footer>
                        @if($canTransfer)
                            <button class="btn" onclick="document.getElementById('transferServerModal').showModal()">@lang('admin/server.manage.transfer_button')</button>
                        @else
                            <button class="btn" disabled>@lang('admin/server.manage.transfer_button')</button>
                            <p class="pt-4">@lang('admin/server.manage.transfer_need_nodes')</p>
                        @endif
                    </footer>
                </div>
            </div>
        @else
            <div>
                <div class="card" data-variant="success">
                    <header>
                        <h3 class="text-lg font-semibold">@lang('admin/server.manage.transfer_server')</h3>
                    </header>
                    <section>
                        <p>
                            @lang('admin/server.manage.transfer_in_progress')
                            @lang('admin/server.manage.transfer_initiated_at') <strong>{{ $server->transfer->created_at }}</strong>
                        </p>
                    </section>

                    <footer>
                        <button class="btn" disabled>@lang('admin/server.manage.transfer_button')</button>
                    </footer>
                </div>
            </div>
        @endif
    </div>

    <dialog class="dialog" id="transferServerModal">
        <form action="{{ route('admin.servers.view.manage.transfer', $server->id) }}" method="POST">
            <header>
                <button type="button" class="btn" data-variant="outline" data-size="sm" onclick="this.closest('dialog').close()" aria-label="@lang('admin/server.manage.close')"><x-icon name="x" class="size-4" /></button>
                <h3 class="text-lg font-semibold">@lang('admin/server.manage.transfer_dialog_title')</h3>
            </header>

            <section>
                <div class="grid gap-6">
                    <div role="group" class="field">
                        <label for="pNodeId">@lang('admin/server.manage.node')</label>
                        <select name="node_id" id="pNodeId" class="select">
                            @foreach($locations as $location)
                                <optgroup label="{{ $location->long }} ({{ $location->short }})">
                                    @foreach($location->nodes as $node)

                                        @if($node->id != $server->node_id)
                                            <option value="{{ $node->id }}"
                                                    @if($location->id === old('location_id')) selected @endif
                                            >{{ $node->name }}</option>
                                        @endif

                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <p class="text-sm text-muted-foreground">@lang('admin/server.manage.node_help')</p>
                    </div>

                    <div role="group" class="field">
                        <label for="pAllocation">@lang('admin/server.manage.default_allocation')</label>
                        <select name="allocation_id" id="pAllocation" class="select"></select>
                        <p class="text-sm text-muted-foreground">@lang('admin/server.manage.default_allocation_help')</p>
                    </div>

                    <div role="group" class="field">
                        <label for="pAllocationAdditional">@lang('admin/server.manage.additional_allocations')</label>
                        <select name="allocation_additional[]" id="pAllocationAdditional" class="select" multiple></select>
                        <p class="text-sm text-muted-foreground">@lang('admin/server.manage.additional_allocations_help')</p>
                    </div>
                </div>
            </section>

            <footer>
                {!! csrf_field() !!}
                <button type="button" class="btn mr-auto" data-variant="outline" data-size="sm" onclick="this.closest('dialog').close()">@lang('admin/server.manage.cancel')</button>
                <button type="submit" class="btn" data-size="sm">@lang('admin/server.manage.confirm')</button>
            </footer>
        </form>
    </dialog>
@endsection

@section('footer-scripts')
    @parent
    {!! Theme::js('vendor/lodash/lodash.js') !!}

    @if($canTransfer)
        {!! Theme::js('js/admin/server/transfer.js') !!}
    @endif
@endsection
