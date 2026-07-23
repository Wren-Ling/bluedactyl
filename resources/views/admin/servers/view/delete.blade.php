@extends('layouts.admin')

@section('title')
    Server — {{ $server->name }}: Delete
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">{{ $server->name }}</h1>
    <p class="text-sm text-muted-foreground">Delete this server from the panel.</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">Admin</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.servers') }}">Servers</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.servers.view', $server->id) }}">{{ $server->name }}</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>Delete</span>
    </nav>
@endsection

@section('content')
@include('admin.servers.partials.navigation')
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <div class="card">
            <header>
                <h3 class="text-lg font-semibold">Safely Delete Server</h3>
            </header>
            <section>
                <p>This action will attempt to delete the server from both the panel and daemon. If either one reports an error the action will be cancelled.</p>
                <p class="text-sm text-destructive">Deleting a server is an irreversible action. <strong>All server data</strong> (including files and users) will be removed from the system.</p>
            </section>
            <footer>
                <form id="deleteform" action="{{ route('admin.servers.view.delete', $server->id) }}" method="POST">
                    {!! csrf_field() !!}
                    <button id="deletebtn" class="btn" data-variant="destructive">Safely Delete This Server</button>
                </form>
            </footer>
        </div>
    </div>
    <div>
        <div class="card" data-variant="destructive">
            <header>
                <h3 class="text-lg font-semibold">Force Delete Server</h3>
            </header>
            <section>
                <p>This action will attempt to delete the server from both the panel and daemon. If the daemon does not respond, or reports an error the deletion will continue.</p>
                <p class="text-sm text-destructive">Deleting a server is an irreversible action. <strong>All server data</strong> (including files and users) will be removed from the system. This method may leave dangling files on your daemon if it reports an error.</p>
            </section>
            <footer>
                <form id="forcedeleteform" action="{{ route('admin.servers.view.delete', $server->id) }}" method="POST">
                    {!! csrf_field() !!}
                    <input type="hidden" name="force_delete" value="1" />
                    <button id="forcedeletebtn" class="btn" data-variant="destructive">Forcibly Delete This Server</button>
                </form>
            </footer>
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>
    $('#deletebtn').click(function (event) {
        event.preventDefault();
        if (confirm('Are you sure that you want to delete this server? There is no going back, all data will immediately be removed.')) {
            $('#deleteform').submit();
        }
    });

    $('#forcedeletebtn').click(function (event) {
        event.preventDefault();
        if (confirm('Are you sure that you want to delete this server? There is no going back, all data will immediately be removed.')) {
            $('#forcedeleteform').submit();
        }
    });
    </script>
@endsection
