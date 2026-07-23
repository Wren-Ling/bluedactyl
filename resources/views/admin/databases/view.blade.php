@extends('layouts.admin')

@section('title')
    Database Hosts &rarr; View &rarr; {{ $host->name }}
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">{{ $host->name }}</h1>
    <p class="text-sm text-muted-foreground">Viewing associated databases and details for this database host.</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">Admin</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.databases') }}">Database Hosts</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>{{ $host->name }}</span>
    </nav>
@endsection

@section('content')
<form action="{{ route('admin.databases.view', $host->id) }}" method="POST">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">Host Details</h3>
                </header>
                <section>
                    <div class="grid gap-6">
                        <div role="group" class="field">
                            <label for="pName">Name</label>
                            <input type="text" id="pName" name="name"  value="{{ old('name', $host->name) }}" />
                        </div>
                        <div role="group" class="field">
                            <label for="pHost">Host</label>
                            <input type="text" id="pHost" name="host"  value="{{ old('host', $host->host) }}" />
                            <p class="text-sm text-muted-foreground">The IP address or FQDN that should be used when attempting to connect to this MySQL host <em>from the panel</em> to add new databases.</p>
                        </div>
                        <div role="group" class="field">
                            <label for="pPort">Port</label>
                            <input type="text" id="pPort" name="port"  value="{{ old('port', $host->port) }}" />
                            <p class="text-sm text-muted-foreground">The port that MySQL is running on for this host.</p>
                        </div>
                        <div role="group" class="field">
                            <label for="pNodeId">Linked Node</label>
                            <select name="node_id" id="pNodeId" class="select">
                                <option value="">None</option>
                                @foreach($locations as $location)
                                    <optgroup label="{{ $location->short }}">
                                        @foreach($location->nodes as $node)
                                            <option value="{{ $node->id }}" {{ $host->node_id !== $node->id ?: 'selected' }}>{{ $node->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            <p class="text-sm text-muted-foreground">This setting does nothing other than default to this database host when adding a database to a server on the selected node.</p>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <div>
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">User Details</h3>
                </header>
                <section>
                    <div class="grid gap-6">
                        <div role="group" class="field">
                            <label for="pUsername">Username</label>
                            <input type="text" name="username" id="pUsername"  value="{{ old('username', $host->username) }}" />
                            <p class="text-sm text-muted-foreground">The username of an account that has enough permissions to create new users and databases on the system.</p>
                        </div>
                        <div role="group" class="field">
                            <label for="pPassword">Password</label>
                            <input type="password" name="password" id="pPassword"  />
                            <p class="text-sm text-muted-foreground">The password to the account defined. Leave blank to continue using the assigned password.</p>
                        </div>
                        <hr />
                        <p class="text-sm text-destructive text-left">The account defined for this database host <strong>must</strong> have the <code>WITH GRANT OPTION</code> permission. If the defined account does not have this permission requests to create databases <em>will</em> fail. <strong>Do not use the same account details for MySQL that you have defined for this panel.</strong></p>
                    </div>
                </section>
                <footer>
                    {!! csrf_field() !!}
                    <button name="_method" value="PATCH" class="btn ml-auto" data-size="sm">Save</button>
                    <button name="_method" value="DELETE" class="btn mr-auto" data-size="sm" data-variant="destructive"><x-icon name="trash-2" class="size-4" /></button>
                </footer>
            </div>
        </div>
    </div>
</form>
<div class="grid gap-6">
    <div class="col-span-full">
        <div class="card">
            <header>
                <h3 class="text-lg font-semibold">Databases</h3>
            </header>
            <section>
                <div class="table-container">
                    <table class="table">
                        <tr>
                            <th>Server</th>
                            <th>Database Name</th>
                            <th>Username</th>
                            <th>Connections From</th>
                            <th>Max Connections</th>
                            <th></th>
                        </tr>
                        @foreach($databases as $database)
                            <tr>
                                <td class="middle"><a href="{{ route('admin.servers.view', $database->getRelation('server')->id) }}">{{ $database->getRelation('server')->name }}</a></td>
                                <td class="middle">{{ $database->database }}</td>
                                <td class="middle">{{ $database->username }}</td>
                                <td class="middle">{{ $database->remote }}</td>
                                @if($database->max_connections != null)
                                    <td class="middle">{{ $database->max_connections }}</td>
                                @else
                                    <td class="middle">Unlimited</td>
                                @endif
                                <td class="text-center">
                                    <a href="{{ route('admin.servers.view.database', $database->getRelation('server')->id) }}">
                                        <button class="btn" data-size="xs">Manage</button>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </section>
            @if($databases->hasPages())
                <footer class="flex justify-center">
                    {!! $databases->render() !!}
                </footer>
            @endif
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>
        // select2 removed in favor of Basecoat native select
    </script>
@endsection
