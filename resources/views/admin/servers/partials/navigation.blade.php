@php
    /** @var \Pterodactyl\Models\Server $server */
    $router = app('router');
@endphp
<div class="grid min-w-0 gap-6">
    <div class="min-w-0 overflow-x-auto pb-1">
        <div class="tabs min-w-max">
            <div role="tablist" data-variant="line" aria-orientation="horizontal">
                <a role="tab" href="{{ route('admin.servers.view', $server->id) }}" aria-selected="{{ $router->currentRouteNamed('admin.servers.view') ? 'true' : 'false' }}">@lang('admin/server.navigation.about')</a>
                @if($server->isInstalled())
                    <a role="tab" href="{{ route('admin.servers.view.details', $server->id) }}" aria-selected="{{ $router->currentRouteNamed('admin.servers.view.details') ? 'true' : 'false' }}">@lang('admin/server.navigation.details')</a>
                    <a role="tab" href="{{ route('admin.servers.view.build', $server->id) }}" aria-selected="{{ $router->currentRouteNamed('admin.servers.view.build') ? 'true' : 'false' }}">@lang('admin/server.navigation.build_config')</a>
                    <a role="tab" href="{{ route('admin.servers.view.startup', $server->id) }}" aria-selected="{{ $router->currentRouteNamed('admin.servers.view.startup') ? 'true' : 'false' }}">@lang('admin/server.navigation.startup')</a>
                    <a role="tab" href="{{ route('admin.servers.view.database', $server->id) }}" aria-selected="{{ $router->currentRouteNamed('admin.servers.view.database') ? 'true' : 'false' }}">@lang('admin/server.navigation.database')</a>
                    <a role="tab" href="{{ route('admin.servers.view.mounts', $server->id) }}" aria-selected="{{ $router->currentRouteNamed('admin.servers.view.mounts') ? 'true' : 'false' }}">@lang('admin/server.navigation.mounts')</a>
                @endif
                <a role="tab" href="{{ route('admin.servers.view.manage', $server->id) }}" aria-selected="{{ $router->currentRouteNamed('admin.servers.view.manage') ? 'true' : 'false' }}">@lang('admin/server.navigation.manage')</a>
                <a role="tab" href="{{ route('admin.servers.view.delete', $server->id) }}" aria-selected="{{ $router->currentRouteNamed('admin.servers.view.delete') ? 'true' : 'false' }}">@lang('admin/server.navigation.delete')</a>
                <a role="tab" href="/server/{{ $server->uuidShort }}" aria-selected="false" target="_blank" aria-label="@lang('admin/server.navigation.about')"><x-icon name="external-link" class="size-4" /></a>
            </div>
        </div>
    </div>
</div>
