@extends('layouts.admin')

@section('title')
  {{ trans('admin/nodes.settings.title', ['name' => $node->name]) }}
@endsection

@section('content-header')
  <h1 class="text-xl font-bold">{{ $node->name }}</h1>
  <p class="text-sm text-muted-foreground">@lang('admin/nodes.settings.header_subtitle')</p>
  <nav class="flex items-center gap-1 text-sm text-muted-foreground">
    <a href="{{ route('admin.index') }}">@lang('admin/nodes.common.admin')</a>
    <x-icon name="chevron-right" class="size-3" />
    <a href="{{ route('admin.nodes') }}">@lang('admin/nodes.common.nodes')</a>
    <x-icon name="chevron-right" class="size-3" />
    <a href="{{ route('admin.nodes.view', $node->id) }}">{{ $node->name }}</a>
    <x-icon name="chevron-right" class="size-3" />
    <span>@lang('admin/nodes.common.settings')</span>
  </nav>
@endsection

@section('content')
  <div class="grid gap-6">
    <div class="col-span-full">
    <div class="tabs">
      <nav role="tablist" aria-orientation="horizontal" data-variant="line">
      <a href="{{ route('admin.nodes.view', $node->id) }}" role="tab" aria-selected="false" tabindex="-1">@lang('admin/nodes.common.about')</a>
      <a href="{{ route('admin.nodes.view.settings', $node->id) }}" role="tab" aria-selected="true" tabindex="0">@lang('admin/nodes.common.settings')</a>
      <a href="{{ route('admin.nodes.view.configuration', $node->id) }}" role="tab" aria-selected="false" tabindex="-1">@lang('admin/nodes.common.configuration')</a>
      <a href="{{ route('admin.nodes.view.allocation', $node->id) }}" role="tab" aria-selected="false" tabindex="-1">@lang('admin/nodes.common.allocation')</a>
      <a href="{{ route('admin.nodes.view.servers', $node->id) }}" role="tab" aria-selected="false" tabindex="-1">@lang('admin/nodes.common.servers')</a>
      </nav>
    </div>
    </div>
  </div>
  <form action="{{ route('admin.nodes.view.settings', $node->id) }}" method="POST">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
      <div class="card">
      <header>
        <h3 class="text-lg font-semibold">@lang('admin/nodes.settings.settings')</h3>
      </header>
      <section>
        <div class="grid gap-6">
        <div role="group" class="field">
        <label for="name" >@lang('admin/nodes.settings.node_name')</label>
        <input type="text" autocomplete="off" name="name" 
          value="{{ old('name', $node->name) }}" />
        <p class="text-sm text-muted-foreground">{!! trans('admin/nodes.settings.name_help') !!}</p>
        </div>
        <div role="group" class="field">
        <label for="description" >@lang('admin/nodes.settings.description')</label>
        <textarea name="description" id="description" rows="4"
          >{{ $node->description }}</textarea>
        </div>
        <div role="group" class="field">
        <label for="name" >@lang('admin/nodes.settings.location')</label>
        <select name="location_id" class="select">
          @foreach($locations as $location)
                <option value="{{ $location->id }}" {{ (old('location_id', $node->location_id) === $location->id) ? 'selected' : '' }}>{{ $location->long }} ({{ $location->short }})</option>
          @endforeach
        </select>
        </div>
        <div role="group" class="field">
        <label for="pDaemonType" >@lang('admin/nodes.settings.daemon')</label>
        <select name="daemonType" id="pDaemonType" class="select">
            @foreach($daemonTypes as $daemon)
                <option value="{{ $daemon }}" {{ $daemon == old('daemon_type', $node->daemonType) ? 'selected' : '' }}>{{ $daemon }}</option>
            @endforeach
        </select>
        </div>
        <p class="text-sm font-semibold mb-2">@lang('admin/nodes.settings.allow_auto_allocation') <sup><a data-tooltip="@lang('admin/nodes.settings.allow_auto_allocation_tooltip')" data-side="top">?</a></sup></p>
        <div role="group" class="field" data-orientation="horizontal">
          <input type="radio" name="public" value="1"  {{ (old('public', $node->public)) ? 'checked' : '' }}
          id="public_1" checked>
          <label for="public_1" class="font-normal">@lang('admin/nodes.common.yes')</label>
        </div>
        <div role="group" class="field" data-orientation="horizontal">
          <input type="radio" name="public" value="0"  {{ (old('public', $node->public)) ? '' : 'checked' }}
          id="public_0">
          <label for="public_0" class="font-normal">@lang('admin/nodes.common.no')</label>
        </div>
        <p class="text-sm font-semibold mb-2">@lang('admin/nodes.settings.domain_by_alias') <sup><a data-tooltip="@lang('admin/nodes.settings.domain_by_alias_tooltip')" data-side="top">?</a></sup></p>
        <div role="group" class="field" data-orientation="horizontal">
          <input type="radio" name="trust_alias" value="1"  {{ (old('trustalias', $node->trust_alias)) ? 'checked' : '' }}
          id="trust_alias_1" checked>
          <label for="trust_alias_1" class="font-normal">@lang('admin/nodes.common.yes')</label>
        </div>
        <div role="group" class="field" data-orientation="horizontal">
          <input type="radio" name="trust_alias" value="0"  {{ (old('trustalias', $node->trust_alias)) ? '' : 'checked' }}
          id="trust_alias_0">
          <label for="trust_alias_0" class="font-normal">@lang('admin/nodes.common.no')</label>
        </div>
        <div role="group" class="field">
        <label for="fqdn" >@lang('admin/nodes.settings.public_fqdn')</label>
        <input type="text" autocomplete="off" name="fqdn" 
          value="{{ old('fqdn', $node->fqdn) }}" />
        <p class="text-sm text-muted-foreground">
          <small>{!! trans('admin/nodes.settings.fqdn_help', [
            'daemon' => $node->daemonType,
            'why_title' => trans('admin/nodes.settings.why_title'),
            'why_content' => trans('admin/nodes.settings.why_content'),
          ]) !!}</small>
        </p>
        </div>
        <div role="group" class="field">
        <label for="internal_fqdn" >
          @lang('admin/nodes.settings.internal_fqdn')
          <strong>@lang('admin/nodes.settings.optional')</strong>
        </label>
        <input type="text" autocomplete="off" name="internal_fqdn" 
          value="{{ old('internal_fqdn', $node->internal_fqdn) }}" />
        <p class="text-sm text-muted-foreground">
          <small>{!! trans('admin/nodes.settings.internal_fqdn_help', ['daemon' => $node->daemonType]) !!}</small>
        </p>
        </div>
        <div role="group" class="field" data-orientation="horizontal">
          <input type="radio" id="pSSLTrue" value="https" name="scheme"  {{ (old('scheme', $node->scheme) === 'https') ? 'checked' : '' }}>
          <label for="pSSLTrue" class="font-normal">@lang('admin/nodes.settings.use_ssl')</label>
        </div>
        <div role="group" class="field" data-orientation="horizontal">
          <input type="radio" id="pSSLFalse" value="http" name="scheme"  {{ (old('scheme', $node->scheme) !== 'https') ? 'checked' : '' }}>
          <label for="pSSLFalse" class="font-normal">@lang('admin/nodes.settings.use_http')</label>
        </div>
        <p class="text-sm text-muted-foreground">@lang('admin/nodes.settings.ssl_help')</p>
        <div role="group" class="field" data-orientation="horizontal">
          <input type="radio" id="pProxyFalse" value="0" name="behind_proxy"  {{ (old('behind_proxy', $node->behind_proxy) == false) ? 'checked' : '' }}>
          <label for="pProxyFalse" class="font-normal">@lang('admin/nodes.settings.not_behind_proxy')</label>
        </div>
        <div role="group" class="field" data-orientation="horizontal">
          <input type="radio" id="pProxyTrue" value="1" name="behind_proxy"  {{ (old('behind_proxy', $node->behind_proxy) == true) ? 'checked' : '' }}>
          <label for="pProxyTrue" class="font-normal">@lang('admin/nodes.settings.behind_proxy')</label>
        </div>
        <p class="text-sm text-muted-foreground">@lang('admin/nodes.settings.behind_proxy_help')</p>
        <div role="group" class="field" data-orientation="horizontal">
          <input type="radio" id="pMaintenanceFalse" value="0" name="maintenance_mode"  {{ (old('maintenance_mode', $node->maintenance_mode) == false) ? 'checked' : '' }}>
          <label for="pMaintenanceFalse" class="font-normal">@lang('admin/nodes.settings.disabled')</label>
        </div>
        <div role="group" class="field" data-orientation="horizontal">
          <input type="radio" id="pMaintenanceTrue" value="1" name="maintenance_mode"  {{ (old('maintenance_mode', $node->maintenance_mode) == true) ? 'checked' : '' }}>
          <label for="pMaintenanceTrue" class="font-normal">@lang('admin/nodes.settings.enabled')</label>
        </div>
        <p class="text-sm text-muted-foreground">@lang('admin/nodes.settings.maintenance_help')</p>
        </div>
      </section>
      </div>
    </div>
    <div>
      <div class="card">
      <header>
        <h3 class="text-lg font-semibold">@lang('admin/nodes.settings.allocation_limits')</h3>
      </header>
      <section>
        <div class="grid gap-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div role="group" class="field">
          <label for="memory" >@lang('admin/nodes.settings.total_memory')</label>
          <input type="text" name="memory" data-multiplicator="true"
            value="{{ old('memory', $node->memory) }}" />
          <span class="px-2 text-muted-foreground">MiB</span>
          </div>
          <div role="group" class="field">
          <label for="memory_overallocate" >@lang('admin/nodes.settings.overallocate')</label>
          <input type="text" name="memory_overallocate"
            value="{{ old('memory_overallocate', $node->memory_overallocate) }}" />
          <span class="px-2 text-muted-foreground">%</span>
          </div>
        </div>
        <p class="text-sm text-muted-foreground">@lang('admin/nodes.settings.memory_help')</p>
        </div>
        <div class="grid gap-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div role="group" class="field">
          <label for="disk" >@lang('admin/nodes.settings.disk_space')</label>
          <input type="text" name="disk" data-multiplicator="true"
            value="{{ old('disk', $node->disk) }}" />
          <span class="px-2 text-muted-foreground">MiB</span>
          </div>
          <div role="group" class="field">
          <label for="disk_overallocate" >@lang('admin/nodes.settings.overallocate')</label>
          <input type="text" name="disk_overallocate"
            value="{{ old('disk_overallocate', $node->disk_overallocate) }}" />
          <span class="px-2 text-muted-foreground">%</span>
          </div>
        </div>
        <p class="text-sm text-muted-foreground">@lang('admin/nodes.settings.disk_help')</p>
        </div>
      </section>
      </div>
    </div>
    <div>
      <div class="card">
      <header>
        <h3 class="text-lg font-semibold">@lang('admin/nodes.settings.general_configuration')</h3>
      </header>
      <section>
        <div class="grid gap-6">
        <div role="group" class="field">
        <label for="disk_overallocate" >@lang('admin/nodes.settings.upload_filesize')</label>
        <input type="text" name="upload_size"
          value="{{ old('upload_size', $node->upload_size) }}" />
        <span class="px-2 text-muted-foreground">MiB</span>
        <p class="text-sm text-muted-foreground">@lang('admin/nodes.settings.upload_help')</p>
        </div>
        <div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div role="group" class="field">
          <label for="daemonListen" ><span class="badge" data-variant="warning"><x-icon name="power" class="size-4" /></span> @lang('admin/nodes.settings.daemon_port')</label>
          <input type="text" name="daemonListen"
            value="{{ old('daemonListen', $node->daemonListen) }}" />
          </div>
          <div role="group" class="field">
          <label for="daemonSFTP" ><span class="badge" data-variant="warning"><x-icon name="power" class="size-4" /></span> @lang('admin/nodes.settings.daemon_sftp_port')</label>
          <input type="text" name="daemonSFTP"
            value="{{ old('daemonSFTP', $node->daemonSFTP) }}" />
          </div>
        </div>
        <div class="grid gap-6">
          <div class="col-span-full">
          <p class="text-sm text-muted-foreground">{!! trans('admin/nodes.settings.daemon_port_help') !!}</p>
          </div>
        </div>
        </div>
      </section>
      </div>
    </div>

    <div>
      <div class="card">
      <header>
        <h3 class="text-lg font-semibold">@lang('admin/nodes.settings.backup_config')</h3>
      </header>
      <section>
        <div class="grid gap-6">
        <div role="group" class="field">
        <label for="pBackupDisk" >@lang('admin/nodes.settings.backup_disk')</label>
        <select name="backupDisk" id="pBackupDisk" class="select">
            <!-- Populated via Script-->
        </select>
        </div>
        </div>
      </section>
      </div>
    </div>

    <div>
      <div class="card">
      <header>
        <h3 class="text-lg font-semibold">@lang('admin/nodes.settings.save_settings')</h3>
      </header>
      <section>
        <div class="grid gap-6">
        <div role="group" class="field" data-orientation="horizontal">
          <input type="checkbox" name="reset_secret" id="reset_secret" />
          <label for="reset_secret" class="font-normal">@lang('admin/nodes.settings.reset_secret')</label>
        </div>
        <p class="text-sm text-muted-foreground">@lang('admin/nodes.settings.reset_secret_help')</p>
        </div>
      </section>
      <footer>
        {!! method_field('PATCH') !!}
        {!! csrf_field() !!}
        <button type="submit" class="btn ml-auto">@lang('admin/nodes.settings.save_changes')</button>
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

                    if (disk === '{{ old("backupDisk", $node->backupDisk) }}') {
                        option.selected = true;
                    }

                    backupDiskSelect.appendChild(option);
                });
            }

            updateBackupDisks();

            daemonSelect.addEventListener('change', updateBackupDisks);

            // $('[data-toggle="popover"]').popover({
            //     placement: 'auto'
            // });

        });
    </script>
@endsection
