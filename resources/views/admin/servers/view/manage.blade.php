@extends('layouts.admin')

@section('title')
    Server — {{ $server->name }}: Manage
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">{{ $server->name }}</h1>
    <p class="text-sm text-muted-foreground">Additional actions to control this server.</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">Admin</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.servers') }}">Servers</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.servers.view', $server->id) }}">{{ $server->name }}</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>Manage</span>
    </nav>
@endsection

@section('content')
    @include('admin.servers.partials.navigation')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <div class="card" data-variant="destructive">
                <header>
                    <h3 class="text-lg font-semibold">Reinstall Server</h3>
                </header>
                <section>
                    <p>This will reinstall the server with the assigned service scripts. <strong>Danger!</strong> This could overwrite server data.</p>
                </section>
                <footer>
                    @if($server->isInstalled())
                        <form action="{{ route('admin.servers.view.manage.reinstall', $server->id) }}" method="POST">
                            {!! csrf_field() !!}
                            <button type="submit" class="btn" data-variant="destructive">Reinstall Server</button>
                        </form>
                    @else
                        <button class="btn" data-variant="destructive" disabled>Server Must Install Properly to Reinstall</button>
                    @endif
                </footer>
            </div>
        </div>
        <div>
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">Install Status</h3>
                </header>
                <section>
                    <p>If you need to change the install status from uninstalled to installed, or vice versa, you may do so with the button below.</p>
                </section>
                <footer>
                    <form action="{{ route('admin.servers.view.manage.toggle', $server->id) }}" method="POST">
                        {!! csrf_field() !!}
                        <button type="submit" class="btn">Toggle Install Status</button>
                    </form>
                </footer>
            </div>
        </div>

        @if(! $server->isSuspended())
            <div>
                <div class="card" data-variant="warning">
                    <header>
                        <h3 class="text-lg font-semibold">Suspend Server</h3>
                    </header>
                    <section>
                        <p>This will suspend the server, stop any running processes, and immediately block the user from being able to access their files or otherwise manage the server through the panel or API.</p>
                    </section>
                    <footer>
                        <form action="{{ route('admin.servers.view.manage.suspension', $server->id) }}" method="POST">
                            {!! csrf_field() !!}
                            <input type="hidden" name="action" value="suspend" />
                            <button type="submit" class="btn" data-variant="secondary" @if(! is_null($server->transfer)) disabled @endif>Suspend Server</button>
                        </form>
                    </footer>
                </div>
            </div>
        @else
            <div>
                <div class="card" data-variant="success">
                    <header>
                        <h3 class="text-lg font-semibold">Unsuspend Server</h3>
                    </header>
                    <section>
                        <p>This will unsuspend the server and restore normal user access.</p>
                    </section>
                    <footer>
                        <form action="{{ route('admin.servers.view.manage.suspension', $server->id) }}" method="POST">
                            {!! csrf_field() !!}
                            <input type="hidden" name="action" value="unsuspend" />
                            <button type="submit" class="btn">Unsuspend Server</button>
                        </form>
                    </footer>
                </div>
            </div>
        @endif

        @if(is_null($server->transfer))
            <div>
                <div class="card" data-variant="success">
                    <header>
                        <h3 class="text-lg font-semibold">Transfer Server</h3>
                    </header>
                    <section>
                        <p>
                            Transfer this server to another node connected to this panel.
                            <strong>Warning!</strong> This feature has not been fully tested and may have bugs.
                        </p>
                    </section>

                    <footer>
                        @if($canTransfer)
                            <button class="btn" onclick="document.getElementById('transferServerModal').showModal()">Transfer Server</button>
                        @else
                            <button class="btn" disabled>Transfer Server</button>
                            <p class="pt-4">Transferring a server requires more than one node to be configured on your panel.</p>
                        @endif
                    </footer>
                </div>
            </div>
        @else
            <div>
                <div class="card" data-variant="success">
                    <header>
                        <h3 class="text-lg font-semibold">Transfer Server</h3>
                    </header>
                    <section>
                        <p>
                            This server is currently being transferred to another node.
                            Transfer was initiated at <strong>{{ $server->transfer->created_at }}</strong>
                        </p>
                    </section>

                    <footer>
                        <button class="btn" disabled>Transfer Server</button>
                    </footer>
                </div>
            </div>
        @endif
    </div>

    <dialog class="dialog" id="transferServerModal">
        <form action="{{ route('admin.servers.view.manage.transfer', $server->id) }}" method="POST">
            <header>
                <button type="button" class="btn" data-variant="outline" data-size="sm" onclick="this.closest('dialog').close()" aria-label="Close"><x-icon name="x" class="size-4" /></button>
                <h3 class="text-lg font-semibold">Transfer Server</h3>
            </header>

            <section>
                <div class="grid gap-6">
                    <div role="group" class="field">
                        <label for="pNodeId">Node</label>
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
                        <p class="text-sm text-muted-foreground">The node which this server will be transferred to.</p>
                    </div>

                    <div role="group" class="field">
                        <label for="pAllocation">Default Allocation</label>
                        <select name="allocation_id" id="pAllocation" class="select"></select>
                        <p class="text-sm text-muted-foreground">The main allocation that will be assigned to this server.</p>
                    </div>

                    <div role="group" class="field">
                        <label for="pAllocationAdditional">Additional Allocation(s)</label>
                        <select name="allocation_additional[]" id="pAllocationAdditional" class="select" multiple></select>
                        <p class="text-sm text-muted-foreground">Additional allocations to assign to this server on creation.</p>
                    </div>
                </div>
            </section>

            <footer>
                {!! csrf_field() !!}
                <button type="button" class="btn mr-auto" data-variant="outline" data-size="sm" onclick="this.closest('dialog').close()">Cancel</button>
                <button type="submit" class="btn" data-size="sm">Confirm</button>
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
