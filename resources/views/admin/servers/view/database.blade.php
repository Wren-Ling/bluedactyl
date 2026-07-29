@extends('layouts.admin')

@section('title')
    @lang('admin/server.overview.title') — {{ $server->name }}: @lang('admin/server.database.title')
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">{{ $server->name }}</h1>
    <p class="text-sm text-muted-foreground">@lang('admin/server.database.description')</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">@lang('admin/server.database.breadcrumb_admin')</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.servers') }}">@lang('admin/server.database.breadcrumb_servers')</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.servers.view', $server->id) }}">{{ $server->name }}</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>@lang('admin/server.database.breadcrumb_databases')</span>
    </nav>
@endsection

@section('content')
@include('admin.servers.partials.navigation')
<div class="grid grid-cols-1 md:grid-cols-12 gap-6">
    <div class="md:col-span-7">
        <div class="alert" data-variant="info" role="alert">
            @lang('admin/server.database.info_password_view', ['uuid' => $server->uuidShort])
        </div>
        <div class="card">
            <header>
                <h3 class="text-lg font-semibold">@lang('admin/server.database.active_databases')</h3>
            </header>
            <section class="table-container no-padding">
                <table class="table">
                    <thead>
                        <tr>
                            <th>@lang('admin/server.database.database')</th>
                            <th>@lang('admin/server.database.username')</th>
                            <th>@lang('admin/server.database.connections_from')</th>
                            <th>@lang('admin/server.database.host')</th>
                            <th>@lang('admin/server.database.max_connections')</th>
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
                                    <td>@lang('admin/server.database.unlimited')</td>
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
                <h3 class="text-lg font-semibold">@lang('admin/server.database.create_new_database')</h3>
            </header>
            <section>
                <form action="{{ route('admin.servers.view.database', $server->id) }}" method="POST">
                    <div class="grid gap-6">
                        <div role="group" class="field">
                            <label for="pDatabaseHostId" >@lang('admin/server.database.database_host')</label>
                            <select id="pDatabaseHostId" name="database_host_id" class="select">
                                @foreach($hosts as $host)
                                    <option value="{{ $host->id }}">{{ $host->name }}</option>
                                @endforeach
                            </select>
                            <p class="text-sm text-muted-foreground">@lang('admin/server.database.database_host_help')</p>
                        </div>
                        <div role="group" class="field">
                            <label for="pDatabaseName" >@lang('admin/server.database.database_name')</label>
                            <span class="inline-flex items-center px-3 py-2 bg-muted border border-input rounded-l text-sm text-muted-foreground">s{{ $server->id }}_</span>
                            <input id="pDatabaseName" type="text" name="database"  placeholder="{{ trans('admin/server.database.database_placeholder') }}" />
                        </div>
                        <div role="group" class="field">
                            <label for="pRemote" >@lang('admin/server.database.connections')</label>
                            <input id="pRemote" type="text" name="remote"  value="%" />
                            <p class="text-sm text-muted-foreground">@lang('admin/server.database.connections_help')</p>
                        </div>
                        <div role="group" class="field">
                            <label for="pmax_connections" >@lang('admin/server.database.concurrent_connections')</label>
                            <input id="pmax_connections" type="text" name="max_connections" />
                            <p class="text-sm text-muted-foreground">@lang('admin/server.database.concurrent_connections_help')</p>
                        </div>
                    </div>
                </form>
            </section>
            <footer>
                {!! csrf_field() !!}
                <p class="text-sm text-muted-foreground">@lang('admin/server.database.auto_generate_info')</p>
                <input type="submit" class="btn ml-auto" data-size="sm" value="@lang('admin/server.database.create_database')" />
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
        if (confirm('{{ trans('admin/server.database.confirm_delete') }}')) {
            $.ajax({
                method: 'DELETE',
                url: '/admin/servers/view/{{ $server->id }}/database/' + self.data('id') + '/delete',
                headers: { 'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content') },
            }).done(function () {
                self.parent().parent().slideUp();
            }).fail(function (jqXHR) {
                console.error(jqXHR);
                alert((typeof jqXHR.responseJSON.error !== 'undefined') ? jqXHR.responseJSON.error : '{{ trans('admin/server.database.error_request') }}');
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
            alert('{{ trans('admin/server.database.password_reset') }}');
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.error(jqXHR);
            var error = '{{ trans('admin/server.database.error_generic') }}';
            if (typeof jqXHR.responseJSON !== 'undefined' && typeof jqXHR.responseJSON.error !== 'undefined') {
                error = jqXHR.responseJSON.error;
            }
            alert('{{ trans('admin/server.database.whoops') }} ' + error);
        }).always(function () {
            block.removeClass('disabled').find('svg').removeClass('animate-spin');
        });
    });
    </script>
@endsection
