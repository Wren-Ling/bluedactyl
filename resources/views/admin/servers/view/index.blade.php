@extends('layouts.admin')

@section('title')
    @lang('admin/server.overview.title') — {{ $server->name }}
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">{{ $server->name }}</h1>
    <p class="text-sm text-muted-foreground">{{ str_limit($server->description) }}</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">@lang('admin/server.overview.breadcrumb_admin')</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.servers') }}">@lang('admin/server.overview.breadcrumb_servers')</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>{{ $server->name }}</span>
    </nav>
@endsection

@section('content')
@include('admin.servers.partials.navigation')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="md:col-span-2">
        <div class="grid gap-6">
            <div class="col-span-full">
                <div class="card">
                    <header>
                        <h3 class="text-lg font-semibold">@lang('admin/server.overview.information')</h3>
                    </header>
                    <section class="table-container no-padding">
                        <table class="table">
                            <tr>
                                <td>@lang('admin/server.overview.internal_id')</td>
                                <td><code>{{ $server->id }}</code></td>
                            </tr>
                            <tr>
                                <td>@lang('admin/server.overview.external_id')</td>
                                @if(is_null($server->external_id))
                                    <td><span class="badge">@lang('admin/server.overview.not_set')</span></td>
                                @else
                                    <td><code>{{ $server->external_id }}</code></td>
                                @endif
                            </tr>
                            <tr>
                                <td>@lang('admin/server.overview.uuid_docker_id')</td>
                                <td><code>{{ $server->uuid }}</code></td>
                            </tr>
                            <tr>
                                <td>@lang('admin/server.overview.current_egg')</td>
                                <td>
                                    <a href="{{ route('admin.nests.view', $server->nest_id) }}">{{ $server->nest->name }}</a> ::
                                    <a href="{{ route('admin.nests.egg.view', $server->egg_id) }}">{{ $server->egg->name }}</a>
                                </td>
                            </tr>
                            <tr>
                                <td>@lang('admin/server.overview.server_name')</td>
                                <td>{{ $server->name }}</td>
                            </tr>
                            <tr>
                                <td>@lang('admin/server.overview.cpu_limit')</td>
                                <td>
                                    @if($server->cpu === 0)
                                        <code>@lang('admin/server.overview.unlimited')</code>
                                    @else
                                        <code>{{ $server->cpu }}%</code>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>@lang('admin/server.overview.cpu_pinning')</td>
                                <td>
                                    @if($server->threads != null)
                                        <code>{{ $server->threads }}</code>
                                    @else
                                        <span class="badge">@lang('admin/server.overview.not_set')</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>@lang('admin/server.overview.memory')</td>
                                <td>
                                    @if($server->memory === 0)
                                        <code>@lang('admin/server.overview.unlimited')</code>
                                    @else
                                        <code>{{ $server->memory }}MiB</code>
                                    @endif
                                    /
                                    @if($server->swap === 0)
                                        <code data-tooltip="@lang('admin/server.overview.swap_space')" data-side="top">@lang('admin/server.overview.not_set')</code>
                                    @elseif($server->swap === -1)
                                        <code data-tooltip="@lang('admin/server.overview.swap_space')" data-side="top">@lang('admin/server.overview.unlimited')</code>
                                    @else
                                        <code data-tooltip="@lang('admin/server.overview.swap_space')" data-side="top"> {{ $server->swap }}MiB</code>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>@lang('admin/server.overview.disk_space')</td>
                                <td>
                                    @if($server->disk === 0)
                                        <code>@lang('admin/server.overview.unlimited')</code>
                                    @else
                                        <code>{{ $server->disk }}MiB</code>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>@lang('admin/server.overview.block_io_weight')</td>
                                <td><code>{{ $server->io }}</code></td>
                            </tr>
                            <tr>
                                <td>@lang('admin/server.overview.default_connection')</td>
                                <td><code>{{ $server->allocation->ip }}:{{ $server->allocation->port }}</code></td>
                            </tr>
                            <tr>
                                <td>@lang('admin/server.overview.connection_alias')</td>
                                <td>
                                    @if($server->allocation->alias !== $server->allocation->ip)
                                        <code>{{ $server->allocation->alias }}:{{ $server->allocation->port }}</code>
                                    @else
                                        <span class="badge">@lang('admin/server.overview.no_alias')</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </section>
                </div>
            </div>
        </div>
    </div>
    <div>
        <div class="card">
            <section class="pb-0">
                <div class="grid gap-6">
                    @if($server->isSuspended())
                        <div>
                            <div class="card" data-variant="warning">
                                <section>
                                    <h3 class="text-lg font-semibold no-margin">@lang('admin/server.overview.suspended')</h3>
                                </section>
                            </div>
                        </div>
                    @endif
                    @if(!$server->isInstalled())
                        <div>
                            <div class="card" data-variant="info">
                                <section>
                                    <h3 class="text-lg font-semibold no-margin">{{ (! $server->isInstalled()) ? trans('admin/server.overview.installing') : trans('admin/server.overview.install_failed') }}</h3>
                                </section>
                            </div>
                        </div>
                    @endif
                    <div>
                        <div class="card">
                            <section>
                                <h3>{{ str_limit($server->user->username, 16) }}</h3>
                                <p>{{ $server->user->email }}</p>
                                <p>@lang('admin/server.overview.server_owner')</p>
                            </section>
                            <x-icon name="user" class="size-4" />
                            <a href="{{ route('admin.users.view', $server->user->id) }}" class="flex items-center gap-1 px-4 py-2 text-sm">
                                @lang('admin/server.overview.more_info') <x-icon name="arrow-right" class="size-4" />
                            </a>
                        </div>
                    </div>
                    <div>
                        <div class="card">
                            <section>
                                <h3>{{ str_limit($server->node->name, 16) }}</h3>
                                <p>@lang('admin/server.overview.server_node')</p>
                            </section>
                            <x-icon name="box" class="size-4" />
                            <a href="{{ route('admin.nodes.view', $server->node->id) }}" class="flex items-center gap-1 px-4 py-2 text-sm">
                                @lang('admin/server.overview.more_info') <x-icon name="arrow-right" class="size-4" />
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
