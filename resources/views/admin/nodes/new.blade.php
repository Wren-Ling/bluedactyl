@extends('layouts.admin')

@section('title')
    @lang('admin/nodes.new.title')
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">@lang('admin/nodes.new.header')</h1>
    <p class="text-sm text-muted-foreground">@lang('admin/nodes.new.header_subtitle')</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">@lang('admin/nodes.common.admin')</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.nodes') }}">@lang('admin/nodes.common.nodes')</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>@lang('admin/nodes.new.new')</span>
    </nav>
@endsection

@section('content')
<form action="{{ route('admin.nodes.new') }}" method="POST">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">@lang('admin/nodes.new.basic_details')</h3>
                </header>
                <section>
                    <div class="grid gap-6">
                    <div role="group" class="field">
                        <label for="pName" >@lang('admin/nodes.new.name')</label>
                        <input type="text" name="name" id="pName" value="{{ old('name') }}"/>
                        <p class="text-sm text-muted-foreground">{!! trans('admin/nodes.new.name_help') !!}</p>
                    </div>
                    <div role="group" class="field">
                        <label for="pDescription" >@lang('admin/nodes.new.description')</label>
                        <textarea name="description" id="pDescription" rows="4">{{ old('description') }}</textarea>
                    </div>
                    <div role="group" class="field">
                        <label for="pLocationId" >@lang('admin/nodes.new.location')</label>
                        <select name="location_id" id="pLocationId" class="select">
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}" {{ $location->id != old('location_id') ?: 'selected' }}>{{ $location->short }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div role="group" class="field">
                        <label for="pDaemonType" >@lang('admin/nodes.new.daemon')</label>
                        <select name="daemonType" id="pDaemonType" class="select">
                            @foreach($daemonTypes as $daemon => $label)
                                <option value="{{ $daemon }}" {{ $daemon == old('daemon_type', 'wings') ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div role="group" class="field">
                        <label for="pBackupDisk" >@lang('admin/nodes.new.backup_disk')</label>
                        <select name="backupDisk" id="pBackupDisk" class="select">
                            <!-- Populated via Script-->
                        </select>
                    </div>
                    <div role="group" class="field" data-orientation="horizontal">
                        <input type="radio" id="pPublicTrue" value="1" name="public" checked>
                        <label for="pPublicTrue" class="font-normal">@lang('admin/nodes.new.public')</label>
                    </div>
                    <div role="group" class="field" data-orientation="horizontal">
                        <input type="radio" id="pPublicFalse" value="0" name="public">
                        <label for="pPublicFalse" class="font-normal">@lang('admin/nodes.new.private')</label>
                    </div>
                    <p class="text-sm text-muted-foreground">{!! trans('admin/nodes.new.public_private_help') !!}
                    <div role="group" class="field">
                        <label for="pFQDN" >@lang('admin/nodes.new.public_fqdn')</label>
                        <input type="text" name="fqdn" id="pFQDN" value="{{ old('fqdn') }}" />
                        <p class="text-sm text-muted-foreground">{!! trans('admin/nodes.new.public_fqdn_help') !!}</p>
                    </div>
                    <div role="group" class="field">
                        <label for="pInternalFQDN" >
                            @lang('admin/nodes.new.internal_fqdn')
                            <strong>@lang('admin/nodes.new.optional')</strong>
                        </label>
                        <input type="text" name="internal_fqdn" id="pInternalFQDN"
                            value="{{ old('internal_fqdn') }}" />
                        <p class="text-sm text-muted-foreground">{!! trans('admin/nodes.new.internal_fqdn_help') !!}</p>
                    </div>
                    <div role="group" class="field" data-orientation="horizontal">
                        <input type="radio" id="pSSLTrue" value="https" name="scheme" checked>
                        <label for="pSSLTrue" class="font-normal">@lang('admin/nodes.new.use_ssl')</label>
                    </div>
                    <div role="group" class="field" data-orientation="horizontal">
                        <input type="radio" id="pSSLFalse" value="http" name="scheme" @if(request()->isSecure()) disabled @endif>
                        <label for="pSSLFalse" class="font-normal">@lang('admin/nodes.new.use_http')</label>
                    </div>
                    @if(request()->isSecure())
                        <p class="text-destructive small">{!! trans('admin/nodes.new.ssl_warning') !!}</p>
                    @else
                        <p class="text-sm text-muted-foreground">@lang('admin/nodes.new.ssl_help')</p>
                    @endif
                    <div role="group" class="field" data-orientation="horizontal">
                        <input type="radio" id="pProxyFalse" value="0" name="behind_proxy" checked>
                        <label for="pProxyFalse" class="font-normal">@lang('admin/nodes.new.not_behind_proxy')</label>
                    </div>
                    <div role="group" class="field" data-orientation="horizontal">
                        <input type="radio" id="pProxyTrue" value="1" name="behind_proxy">
                        <label for="pProxyTrue" class="font-normal">@lang('admin/nodes.new.behind_proxy')</label>
                    </div>
                    <p class="text-sm text-muted-foreground">@lang('admin/nodes.new.behind_proxy_help')</p>
                    </div>
                </section>
            </div>
        </div>
        <div>
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">@lang('admin/nodes.new.configuration')</h3>
                </header>
                <section>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div role="group" class="field">
                            <label for="pDaemonBase" >@lang('admin/nodes.new.daemon_base')</label>
                            <input type="text" name="daemonBase" id="pDaemonBase" value="/var/lib/elytra/volumes" />
                            <p class="text-sm text-muted-foreground">{!! trans('admin/nodes.new.daemon_base_help') !!}</p>
                        </div>
                        <div role="group" class="field">
                            <label for="pMemory" >@lang('admin/nodes.new.total_memory')</label>
                            <input type="text" name="memory" data-multiplicator="true"  id="pMemory" value="{{ old('memory') }}"/>
                            <span class="px-2 text-muted-foreground">MiB</span>
                        </div>
                        <div role="group" class="field">
                            <label for="pMemoryOverallocate" >@lang('admin/nodes.new.memory_overallocate')</label>
                            <input type="text" name="memory_overallocate"  id="pMemoryOverallocate" value="{{ old('memory_overallocate') }}"/>
                            <span class="px-2 text-muted-foreground">%</span>
                        </div>
                        <div class="col-span-full">
                            <p class="text-sm text-muted-foreground">{!! trans('admin/nodes.new.memory_help') !!}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div role="group" class="field">
                            <label for="pDisk" >@lang('admin/nodes.new.total_disk')</label>
                            <input type="text" name="disk" data-multiplicator="true"  id="pDisk" value="{{ old('disk') }}"/>
                            <span class="px-2 text-muted-foreground">MiB</span>
                        </div>
                        <div role="group" class="field">
                            <label for="pDiskOverallocate" >@lang('admin/nodes.new.disk_overallocate')</label>
                            <input type="text" name="disk_overallocate"  id="pDiskOverallocate" value="{{ old('disk_overallocate') }}"/>
                            <span class="px-2 text-muted-foreground">%</span>
                        </div>
                        <div class="col-span-full">
                            <p class="text-sm text-muted-foreground">{!! trans('admin/nodes.new.disk_help') !!}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div role="group" class="field">
                            <label for="pDaemonListen" >@lang('admin/nodes.new.daemon_port')</label>
                            <input type="text" name="daemonListen" id="pDaemonListen" value="8080" />
                        </div>
                        <div role="group" class="field">
                            <label for="pDaemonSFTP" >@lang('admin/nodes.new.daemon_sftp_port')</label>
                            <input type="text" name="daemonSFTP" id="pDaemonSFTP" value="2022" />
                        </div>
                        <div class="col-span-full">
                            <p class="text-sm text-muted-foreground">{!! trans('admin/nodes.new.daemon_port_help') !!}</p>
                        </div>
                    </div>
                </section>
                <footer>
                    {!! csrf_field() !!}
                    <button type="submit" class="btn ml-auto">@lang('admin/nodes.new.create_node')</button>
                </footer>
            </div>
        </div>
    </div>
</form>
@endsection

@section('footer-scripts')
    @parent
    <script>
        $(document).ready(function() {
            const daemonSelect = document.getElementById('pDaemonType');
            const backupDiskSelect = document.getElementById('pBackupDisk');

            function updateBackupDisks() {
                const daemonValue = daemonSelect.value;
                const disks = {!! json_encode($backupDisks ?? []) !!}[daemonValue] || [];

                backupDiskSelect.innerHTML = '';

                disks.forEach(disk => {
                    const option = document.createElement('option');
                    option.value = disk;
                    option.textContent = disk;

                    backupDiskSelect.appendChild(option);
                });
            }

            updateBackupDisks();

            daemonSelect.addEventListener('change', updateBackupDisks);
        });

    </script>
@endsection
