@php
    /** @var \Pterodactyl\Models\Server $server */
    $router = app('router');
@endphp
<div class="grid gap-6">
    <div>
        <div class="tabs" data-variant="line">
            <div role="tablist">
                <a role="tab" href="{{ route('admin.servers.view', $server->id) }}" @if($router->currentRouteNamed('admin.servers.view')) data-active="true" @endif>@lang('admin/server.navigation.about')</a>
                @if($server->isInstalled())
                    <a role="tab" href="{{ route('admin.servers.view.details', $server->id) }}" @if($router->currentRouteNamed('admin.servers.view.details')) data-active="true" @endif>@lang('admin/server.navigation.details')</a>
                    <a role="tab" href="{{ route('admin.servers.view.build', $server->id) }}" @if($router->currentRouteNamed('admin.servers.view.build')) data-active="true" @endif>@lang('admin/server.navigation.build_config')</a>
                    <a role="tab" href="{{ route('admin.servers.view.startup', $server->id) }}" @if($router->currentRouteNamed('admin.servers.view.startup')) data-active="true" @endif>@lang('admin/server.navigation.startup')</a>
                    <a role="tab" href="{{ route('admin.servers.view.database', $server->id) }}" @if($router->currentRouteNamed('admin.servers.view.database')) data-active="true" @endif>@lang('admin/server.navigation.database')</a>
                    <a role="tab" href="{{ route('admin.servers.view.mounts', $server->id) }}" @if($router->currentRouteNamed('admin.servers.view.mounts')) data-active="true" @endif>@lang('admin/server.navigation.mounts')</a>
                @endif
                <a role="tab" href="{{ route('admin.servers.view.manage', $server->id) }}" @if($router->currentRouteNamed('admin.servers.view.manage')) data-active="true" @endif>@lang('admin/server.navigation.manage')</a>
                <a role="tab" href="{{ route('admin.servers.view.delete', $server->id) }}" @if($router->currentRouteNamed('admin.servers.view.delete')) data-active="true" @endif>@lang('admin/server.navigation.delete')</a>
                <a role="tab" href="/server/{{ $server->uuidShort }}" target="_blank"><x-icon name="external-link" class="size-4" /></a>
            </div>
        </div>
    </div>
</div>
