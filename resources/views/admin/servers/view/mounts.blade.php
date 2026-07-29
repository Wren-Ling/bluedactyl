@extends('layouts.admin')

@section('title')
    @lang('admin/server.overview.title') — {{ $server->name }}: @lang('admin/server.mounts.title')
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">{{ $server->name }}</h1>
    <p class="text-sm text-muted-foreground">@lang('admin/server.mounts.description')</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">@lang('admin/server.mounts.breadcrumb_admin')</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.servers') }}">@lang('admin/server.mounts.breadcrumb_servers')</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.servers.view', $server->id) }}">{{ $server->name }}</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>@lang('admin/server.mounts.breadcrumb_mounts')</span>
    </nav>
@endsection

@section('content')
    @include('admin.servers.partials.navigation')

    <div class="grid gap-6">
        <div class="col-span-full">
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">@lang('admin/server.mounts.available_mounts')</h3>
                </header>

                <section class="table-container no-padding">
                    <table class="table">
                        <tr>
                            <th>@lang('admin/server.mounts.id')</th>
                            <th>@lang('admin/server.mounts.name')</th>
                            <th>@lang('admin/server.mounts.source')</th>
                            <th>@lang('admin/server.mounts.target')</th>
                            <th>@lang('admin/server.mounts.status')</th>
                            <th></th>
                        </tr>

                        @foreach ($mounts as $mount)
                            <tr>
                                <td><code>{{ $mount->id }}</code></td>
                                <td><a href="{{ route('admin.mounts.view', $mount->id) }}">{{ $mount->name }}</a></td>
                                <td><code>{{ $mount->source }}</code></td>
                                <td><code>{{ $mount->target }}</code></td>

                                @if (! in_array($mount->id, $server->mounts->pluck('id')->toArray()))
                                    <td>
                                        <span class="badge" data-variant="primary">@lang('admin/server.mounts.unmounted')</span>
                                    </td>

                                    <td>
                                        <form action="{{ route('admin.servers.view.mounts.store', [ 'server' => $server->id ]) }}" method="POST">
                                            {!! csrf_field() !!}
                                            <input type="hidden" value="{{ $mount->id }}" name="mount_id" />
                                            <button type="submit" class="btn" data-size="sm"><x-icon name="plus" class="size-4" /></button>
                                        </form>
                                    </td>
                                @else
                                    <td>
                                        <span class="badge" data-variant="success">@lang('admin/server.mounts.mounted')</span>
                                    </td>

                                    <td>
                                        <form action="{{ route('admin.servers.view.mounts.delete', [ 'server' => $server->id, 'mount' => $mount->id ]) }}" method="POST">
                                            @method('DELETE')
                                            {!! csrf_field() !!}

                                            <button type="submit" class="btn" data-size="sm" data-variant="destructive"><x-icon name="x" class="size-4" /></button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </table>
                </section>
            </div>
        </div>
    </div>
@endsection
