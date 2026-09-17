@extends('layouts.admin')

@section('title')
    @lang('admin/databases.breadcrumb_view', ['name' => $host->name])
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">{{ $host->name }}</h1>
    <p class="text-sm text-muted-foreground">@lang('admin/databases.view_subtitle')</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">@lang('admin/databases.breadcrumb_admin')</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.databases') }}">@lang('admin/databases.breadcrumb_databases')</a>
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
                    <h3 class="text-lg font-semibold">@lang('admin/databases.host_details')</h3>
                </header>
                <section>
                    <div class="grid gap-6">
                        <div role="group" class="field">
                            <label for="pName">@lang('admin/databases.name')</label>
                            <input type="text" id="pName" name="name"  value="{{ old('name', $host->name) }}" />
                        </div>
                        <div role="group" class="field">
                            <label for="pHost">@lang('admin/databases.host')</label>
                            <input type="text" id="pHost" name="host"  value="{{ old('host', $host->host) }}" />
                            <p class="text-sm text-muted-foreground">@lang('admin/databases.host_help')</p>
                        </div>
                        <div role="group" class="field">
                            <label for="pPort">@lang('admin/databases.port')</label>
                            <input type="text" id="pPort" name="port"  value="{{ old('port', $host->port) }}" />
                            <p class="text-sm text-muted-foreground">@lang('admin/databases.port_help')</p>
                        </div>
                        <div role="group" class="field">
                            <label for="pNodeId">@lang('admin/databases.linked_node')</label>
                            <select name="node_id" id="pNodeId" class="select">
                                <option value="">@lang('admin/databases.none')</option>
                                @foreach($locations as $location)
                                    <optgroup label="{{ $location->short }}">
                                        @foreach($location->nodes as $node)
                                            <option value="{{ $node->id }}" {{ $host->node_id !== $node->id ?: 'selected' }}>{{ $node->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            <p class="text-sm text-muted-foreground">@lang('admin/databases.linked_node_help')</p>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <div>
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">@lang('admin/databases.user_details')</h3>
                </header>
                <section>
                    <div class="grid gap-6">
                        <div role="group" class="field">
                            <label for="pUsername">@lang('admin/databases.username')</label>
                            <input type="text" name="username" id="pUsername"  value="{{ old('username', $host->username) }}" />
                            <p class="text-sm text-muted-foreground">@lang('admin/databases.username_help')</p>
                        </div>
                        <div role="group" class="field">
                            <label for="pPassword">@lang('admin/databases.password')</label>
                            <input type="password" name="password" id="pPassword"  />
                            <p class="text-sm text-muted-foreground">@lang('admin/databases.password_leave_blank')</p>
                        </div>
                        <hr />
                        <p class="text-sm text-destructive text-left">@lang('admin/databases.grant_warning')</p>
                    </div>
                </section>
                <footer>
                    {!! csrf_field() !!}
                    <button name="_method" value="PATCH" class="btn ml-auto" data-size="sm">@lang('admin/databases.save')</button>
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
                <h3 class="text-lg font-semibold">@lang('admin/databases.databases_list')</h3>
            </header>
            <section>
                <div class="table-container">
                    <table class="table">
                        <tr>
                            <th>@lang('admin/databases.server')</th>
                            <th>@lang('admin/databases.database_name')</th>
                            <th>@lang('admin/databases.username')</th>
                            <th>@lang('admin/databases.connections_from')</th>
                            <th>@lang('admin/databases.max_connections')</th>
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
                                    <td class="middle">@lang('admin/databases.unlimited')</td>
                                @endif
                                <td class="text-center">
                                    <a href="{{ route('admin.servers.view.database', $database->getRelation('server')->id) }}">
                                        <button class="btn" data-size="xs">@lang('admin/databases.manage')</button>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </section>
            @if($databases->hasPages())
                <footer class="flex justify-center">
                    @include('admin.partials.pagination', ['paginator' => $databases])
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
