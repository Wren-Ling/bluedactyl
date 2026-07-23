@extends('layouts.admin')

@section('title')
    Server — {{ $server->name }}
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">{{ $server->name }}</h1>
    <p class="text-sm text-muted-foreground">{{ str_limit($server->description) }}</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">Admin</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.servers') }}">Servers</a>
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
                        <h3 class="text-lg font-semibold">Information</h3>
                    </header>
                    <section class="table-container no-padding">
                        <table class="table">
                            <tr>
                                <td>Internal Identifier</td>
                                <td><code>{{ $server->id }}</code></td>
                            </tr>
                            <tr>
                                <td>External Identifier</td>
                                @if(is_null($server->external_id))
                                    <td><span class="badge">Not Set</span></td>
                                @else
                                    <td><code>{{ $server->external_id }}</code></td>
                                @endif
                            </tr>
                            <tr>
                                <td>UUID / Docker Container ID</td>
                                <td><code>{{ $server->uuid }}</code></td>
                            </tr>
                            <tr>
                                <td>Current Egg</td>
                                <td>
                                    <a href="{{ route('admin.nests.view', $server->nest_id) }}">{{ $server->nest->name }}</a> ::
                                    <a href="{{ route('admin.nests.egg.view', $server->egg_id) }}">{{ $server->egg->name }}</a>
                                </td>
                            </tr>
                            <tr>
                                <td>Server Name</td>
                                <td>{{ $server->name }}</td>
                            </tr>
                            <tr>
                                <td>CPU Limit</td>
                                <td>
                                    @if($server->cpu === 0)
                                        <code>Unlimited</code>
                                    @else
                                        <code>{{ $server->cpu }}%</code>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>CPU Pinning</td>
                                <td>
                                    @if($server->threads != null)
                                        <code>{{ $server->threads }}</code>
                                    @else
                                        <span class="badge">Not Set</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>Memory</td>
                                <td>
                                    @if($server->memory === 0)
                                        <code>Unlimited</code>
                                    @else
                                        <code>{{ $server->memory }}MiB</code>
                                    @endif
                                    /
                                    @if($server->swap === 0)
                                        <code data-tooltip="Swap Space" data-side="top">Not Set</code>
                                    @elseif($server->swap === -1)
                                        <code data-tooltip="Swap Space" data-side="top">Unlimited</code>
                                    @else
                                        <code data-tooltip="Swap Space" data-side="top"> {{ $server->swap }}MiB</code>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>Disk Space</td>
                                <td>
                                    @if($server->disk === 0)
                                        <code>Unlimited</code>
                                    @else
                                        <code>{{ $server->disk }}MiB</code>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>Block IO Weight</td>
                                <td><code>{{ $server->io }}</code></td>
                            </tr>
                            <tr>
                                <td>Default Connection</td>
                                <td><code>{{ $server->allocation->ip }}:{{ $server->allocation->port }}</code></td>
                            </tr>
                            <tr>
                                <td>Connection Alias</td>
                                <td>
                                    @if($server->allocation->alias !== $server->allocation->ip)
                                        <code>{{ $server->allocation->alias }}:{{ $server->allocation->port }}</code>
                                    @else
                                        <span class="badge">No Alias Assigned</span>
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
                                    <h3 class="text-lg font-semibold no-margin">Suspended</h3>
                                </section>
                            </div>
                        </div>
                    @endif
                    @if(!$server->isInstalled())
                        <div>
                            <div class="card" data-variant="info">
                                <section>
                                    <h3 class="text-lg font-semibold no-margin">{{ (! $server->isInstalled()) ? 'Installing' : 'Install Failed' }}</h3>
                                </section>
                            </div>
                        </div>
                    @endif
                    <div>
                        <div class="card">
                            <section>
                                <h3>{{ str_limit($server->user->username, 16) }}</h3>
                                <p>{{ $server->user->email }}</p>
                                <p>Server Owner</p>
                            </section>
                            <x-icon name="user" class="size-4" />
                            <a href="{{ route('admin.users.view', $server->user->id) }}" class="flex items-center gap-1 px-4 py-2 text-sm">
                                More info <x-icon name="arrow-right" class="size-4" />
                            </a>
                        </div>
                    </div>
                    <div>
                        <div class="card">
                            <section>
                                <h3>{{ str_limit($server->node->name, 16) }}</h3>
                                <p>Server Node</p>
                            </section>
                            <x-icon name="box" class="size-4" />
                            <a href="{{ route('admin.nodes.view', $server->node->id) }}" class="flex items-center gap-1 px-4 py-2 text-sm">
                                More info <x-icon name="arrow-right" class="size-4" />
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
