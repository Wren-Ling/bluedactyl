@extends('layouts.admin')

@section('title')
    Server — {{ $server->name }}: Databases
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">{{ $server->name }}</h1>
    <p class="text-sm text-muted-foreground">Manage server databases.</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">Admin</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.servers') }}">Servers</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.servers.view', $server->id) }}">{{ $server->name }}</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>Databases</span>
    </nav>
@endsection

@section('content')
@include('admin.servers.partials.navigation')
<div class="grid grid-cols-1 md:grid-cols-12 gap-6">
    <div class="md:col-span-7">
        <div class="alert" data-variant="info" role="alert">
            Database passwords can be viewed when <a href="/server/{{ $server->uuidShort }}/databases">visiting this server</a> on the front-end.
        </div>
        <div class="card">
            <header>
                <h3 class="text-lg font-semibold">Active Databases</h3>
            </header>
            <section class="table-container no-padding">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Database</th>
                            <th>Username</th>
                            <th>Connections From</th>
                            <th>Host</th>
                            <th>Max Connections</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($server->databases as $database)
                            <tr>
                                <td>{{ $database->database }}</td>
                                <td>{{ $database->username }}</td>
                                <td>{{ $database->remote }}</td>
                                <td><code>{{ $database->host->host }}:{{ $database->host->port }}</code></td>
                                @if($database->max_connections != null)
                                    <td>{{ $database->max_connections }}</td>
                                @else
                                    <td>Unlimited</td>
                                @endif
                                <td class="text-center">
                                    <button data-action="reset-password" data-id="{{ $database->id }}" class="btn" data-size="sm"><x-icon name="refresh-cw" class="size-4" /></button>
                                    <button data-action="remove" data-id="{{ $database->id }}" class="btn" data-size="sm" data-variant="destructive"><x-icon name="trash-2" class="size-4" /></button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        </div>
    </div>
    <div class="md:col-span-5">
        <div class="card" data-variant="success">
            <header>
                <h3 class="text-lg font-semibold">Create New Database</h3>
            </header>
            <section>
                <form action="{{ route('admin.servers.view.database', $server->id) }}" method="POST">
                    <div class="grid gap-6">
                        <div role="group" class="field">
                            <label for="pDatabaseHostId" >Database Host</label>
                            <select id="pDatabaseHostId" name="database_host_id" class="select">
                                @foreach($hosts as $host)
                                    <option value="{{ $host->id }}">{{ $host->name }}</option>
                                @endforeach
                            </select>
                            <p class="text-sm text-muted-foreground">Select the host database server that this database should be created on.</p>
                        </div>
                        <div role="group" class="field">
                            <label for="pDatabaseName" >Database</label>
                            <span class="inline-flex items-center px-3 py-2 bg-muted border border-input rounded-l text-sm text-muted-foreground">s{{ $server->id }}_</span>
                            <input id="pDatabaseName" type="text" name="database"  placeholder="database" />
                        </div>
                        <div role="group" class="field">
                            <label for="pRemote" >Connections</label>
                            <input id="pRemote" type="text" name="remote"  value="%" />
                            <p class="text-sm text-muted-foreground">This should reflect the IP address that connections are allowed from. Uses standard MySQL notation. If unsure leave as <code>%</code>.</p>
                        </div>
                        <div role="group" class="field">
                            <label for="pmax_connections" >Concurrent Connections</label>
                            <input id="pmax_connections" type="text" name="max_connections" />
                            <p class="text-sm text-muted-foreground">This should reflect the max number of concurrent connections from this user to the database. Leave empty for unlimited.</p>
                        </div>
                    </div>
                </form>
            </section>
            <footer>
                {!! csrf_field() !!}
                <p class="text-sm text-muted-foreground">A username and password for this database will be randomly generated after form submission.</p>
                <input type="submit" class="btn ml-auto" data-size="sm" value="Create Database" />
            </footer>
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>
    $('[data-action="remove"]').click(function (event) {
        event.preventDefault();
        var self = $(this);
        if (confirm('Are you sure that you want to delete this database? There is no going back, all data will immediately be removed.')) {
            $.ajax({
                method: 'DELETE',
                url: '/admin/servers/view/{{ $server->id }}/database/' + self.data('id') + '/delete',
                headers: { 'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content') },
            }).done(function () {
                self.parent().parent().slideUp();
            }).fail(function (jqXHR) {
                console.error(jqXHR);
                alert((typeof jqXHR.responseJSON.error !== 'undefined') ? jqXHR.responseJSON.error : 'An error occurred while processing this request.');
            });
        }
    });
    $('[data-action="reset-password"]').click(function (e) {
        e.preventDefault();
        var block = $(this);
        $(this).addClass('disabled').find('svg').addClass('animate-spin');
        $.ajax({
            type: 'PATCH',
            url: '/admin/servers/view/{{ $server->id }}/database',
            headers: { 'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content') },
            data: { database: $(this).data('id') },
        }).done(function (data) {
            alert('The password for this database has been reset.');
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.error(jqXHR);
            var error = 'An error occurred while trying to process this request.';
            if (typeof jqXHR.responseJSON !== 'undefined' && typeof jqXHR.responseJSON.error !== 'undefined') {
                error = jqXHR.responseJSON.error;
            }
            alert('Whoops! ' + error);
        }).always(function () {
            block.removeClass('disabled').find('svg').removeClass('animate-spin');
        });
    });
    </script>
@endsection
