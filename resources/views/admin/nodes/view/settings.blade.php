@extends('layouts.admin')

@section('title')
  {{ $node->name }}: Settings
@endsection

@section('content-header')
  <h1 class="text-xl font-bold">{{ $node->name }}</h1>
  <p class="text-sm text-muted-foreground">Configure your node settings.</p>
  <nav class="flex items-center gap-1 text-sm text-muted-foreground">
    <a href="{{ route('admin.index') }}">Admin</a>
    <x-icon name="chevron-right" class="size-3" />
    <a href="{{ route('admin.nodes') }}">Nodes</a>
    <x-icon name="chevron-right" class="size-3" />
    <a href="{{ route('admin.nodes.view', $node->id) }}">{{ $node->name }}</a>
    <x-icon name="chevron-right" class="size-3" />
    <span>Settings</span>
  </nav>
@endsection

@section('content')
  <div class="grid gap-6">
    <div class="col-span-full">
    <div class="tabs">
      <nav role="tablist" aria-orientation="horizontal" data-variant="line">
      <a href="{{ route('admin.nodes.view', $node->id) }}" role="tab" aria-selected="false" tabindex="-1">About</a>
      <a href="{{ route('admin.nodes.view.settings', $node->id) }}" role="tab" aria-selected="true" tabindex="0">Settings</a>
      <a href="{{ route('admin.nodes.view.configuration', $node->id) }}" role="tab" aria-selected="false" tabindex="-1">Configuration</a>
      <a href="{{ route('admin.nodes.view.allocation', $node->id) }}" role="tab" aria-selected="false" tabindex="-1">Allocation</a>
      <a href="{{ route('admin.nodes.view.servers', $node->id) }}" role="tab" aria-selected="false" tabindex="-1">Servers</a>
      </nav>
    </div>
    </div>
  </div>
  <form action="{{ route('admin.nodes.view.settings', $node->id) }}" method="POST">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
      <div class="card">
      <header>
        <h3 class="text-lg font-semibold">Settings</h3>
      </header>
      <section>
        <div class="grid gap-6">
        <div role="group" class="field">
        <label for="name" >Node Name</label>
        <input type="text" autocomplete="off" name="name" 
          value="{{ old('name', $node->name) }}" />
        <p class="text-sm text-muted-foreground">Character limits: <code>a-zA-Z0-9_.-</code> and <code>[Space]</code> (min 1,
            max 100 characters).</p>
        </div>
        <div role="group" class="field">
        <label for="description" >Description</label>
        <textarea name="description" id="description" rows="4"
          >{{ $node->description }}</textarea>
        </div>
        <div role="group" class="field">
        <label for="name" >Location</label>
        <select name="location_id" class="select">
          @foreach($locations as $location)
                <option value="{{ $location->id }}" {{ (old('location_id', $node->location_id) === $location->id) ? 'selected' : '' }}>{{ $location->long }} ({{ $location->short }})</option>
          @endforeach
        </select>
        </div>
        <div role="group" class="field">
        <label for="pDaemonType" >Daemon</label>
        <select name="daemonType" id="pDaemonType" class="select">
            @foreach($daemonTypes as $daemon)
                <option value="{{ $daemon }}" {{ $daemon == old('daemon_type', $node->daemonType) ? 'selected' : '' }}>{{ $daemon }}</option>
            @endforeach
        </select>
        </div>
        <p class="text-sm font-semibold mb-2">Allow Automatic Allocation <sup><a data-tooltip="Allow automatic allocation to this Node?" data-side="top">?</a></sup></p>
        <div role="group" class="field" data-orientation="horizontal">
          <input type="radio" name="public" value="1"  {{ (old('public', $node->public)) ? 'checked' : '' }}
          id="public_1" checked>
          <label for="public_1" class="font-normal">Yes</label>
        </div>
        <div role="group" class="field" data-orientation="horizontal">
          <input type="radio" name="public" value="0"  {{ (old('public', $node->public)) ? '' : 'checked' }}
          id="public_0">
          <label for="public_0" class="font-normal">No</label>
        </div>
        <p class="text-sm font-semibold mb-2">Domain by Allocation Alias <sup><a data-tooltip="Allow Ip Aliases to be used instead of allocation ip for domain management" data-side="top">?</a></sup></p>
        <div role="group" class="field" data-orientation="horizontal">
          <input type="radio" name="trust_alias" value="1"  {{ (old('trustalias', $node->trust_alias)) ? 'checked' : '' }}
          id="trust_alias_1" checked>
          <label for="trust_alias_1" class="font-normal">Yes</label>
        </div>
        <div role="group" class="field" data-orientation="horizontal">
          <input type="radio" name="trust_alias" value="0"  {{ (old('trustalias', $node->trust_alias)) ? '' : 'checked' }}
          id="trust_alias_0">
          <label for="trust_alias_0" class="font-normal">No</label>
        </div>
        <div role="group" class="field">
        <label for="fqdn" >Public Fully Qualified Domain Name</label>
        <input type="text" autocomplete="off" name="fqdn" 
          value="{{ old('fqdn', $node->fqdn) }}" />
        <p class="text-sm text-muted-foreground">
          <small>
          Domain name that browsers will use to connect to {{ $node->daemonType }} (e.g <code>{{ $node->daemonType }}.example.com</code>).
          An IP address may be used <em>only</em> if you are not using SSL for this node.
           <a tabindex="0" title="Why do I need a FQDN?"
            data-content="In order to secure communications between your server and this node we use SSL. We cannot generate a SSL certificate for IP Addresses, and as such you will need to provide a FQDN.">Why?</a>
          </small>
        </p>
        </div>
        <div role="group" class="field">
        <label for="internal_fqdn" >
          Internal FQDN
          <strong>(Optional)</strong>
        </label>
        <input type="text" autocomplete="off" name="internal_fqdn" 
          value="{{ old('internal_fqdn', $node->internal_fqdn) }}" />
        <p class="text-sm text-muted-foreground">
          <small>
          <strong>Optional:</strong>
          Leave blank to use the Public FQDN for panel-to-{{ $node->daemonType }} communication.
          If specified, this internal domain name will be used for panel-to-{{ $node->daemonType }} communication instead
          (e.g <code>{{ $node->daemonType }}-internal.example.com</code> or <code>10.0.0.5</code>).
          Useful for internal networks where the panel needs to communicate with {{ $node->daemonType }} using a
          different address than what browsers use.
          </small>
        </p>
        </div>
        <div role="group" class="field" data-orientation="horizontal">
          <input type="radio" id="pSSLTrue" value="https" name="scheme"  {{ (old('scheme', $node->scheme) === 'https') ? 'checked' : '' }}>
          <label for="pSSLTrue" class="font-normal">Use SSL Connection</label>
        </div>
        <div role="group" class="field" data-orientation="horizontal">
          <input type="radio" id="pSSLFalse" value="http" name="scheme"  {{ (old('scheme', $node->scheme) !== 'https') ? 'checked' : '' }}>
          <label for="pSSLFalse" class="font-normal">Use HTTP Connection</label>
        </div>
        <p class="text-sm text-muted-foreground">In most cases you should select to use a SSL connection. If using an IP Address
          or you do not wish to use SSL at all, select a HTTP connection.</p>
        <div role="group" class="field" data-orientation="horizontal">
          <input type="radio" id="pProxyFalse" value="0" name="behind_proxy"  {{ (old('behind_proxy', $node->behind_proxy) == false) ? 'checked' : '' }}>
          <label for="pProxyFalse" class="font-normal">Not Behind Proxy</label>
        </div>
        <div role="group" class="field" data-orientation="horizontal">
          <input type="radio" id="pProxyTrue" value="1" name="behind_proxy"  {{ (old('behind_proxy', $node->behind_proxy) == true) ? 'checked' : '' }}>
          <label for="pProxyTrue" class="font-normal">Behind Proxy</label>
        </div>
        <p class="text-sm text-muted-foreground">If you are running the daemon behind a proxy such as Cloudflare, select this to
          have the daemon skip looking for certificates on boot.</p>
        <div role="group" class="field" data-orientation="horizontal">
          <input type="radio" id="pMaintenanceFalse" value="0" name="maintenance_mode"  {{ (old('maintenance_mode', $node->maintenance_mode) == false) ? 'checked' : '' }}>
          <label for="pMaintenanceFalse" class="font-normal">Disabled</label>
        </div>
        <div role="group" class="field" data-orientation="horizontal">
          <input type="radio" id="pMaintenanceTrue" value="1" name="maintenance_mode"  {{ (old('maintenance_mode', $node->maintenance_mode) == true) ? 'checked' : '' }}>
          <label for="pMaintenanceTrue" class="font-normal">Enabled</label>
        </div>
        <p class="text-sm text-muted-foreground">If the node is marked as 'Under Maintenance' users won't be able to access
          servers that are on this node.</p>
        </div>
      </section>
      </div>
    </div>
    <div>
      <div class="card">
      <header>
        <h3 class="text-lg font-semibold">Allocation Limits</h3>
      </header>
      <section>
        <div class="grid gap-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div role="group" class="field">
          <label for="memory" >Total Memory</label>
          <input type="text" name="memory" data-multiplicator="true"
            value="{{ old('memory', $node->memory) }}" />
          <span class="px-2 text-muted-foreground">MiB</span>
          </div>
          <div role="group" class="field">
          <label for="memory_overallocate" >Overallocate</label>
          <input type="text" name="memory_overallocate"
            value="{{ old('memory_overallocate', $node->memory_overallocate) }}" />
          <span class="px-2 text-muted-foreground">%</span>
          </div>
        </div>
        <p class="text-sm text-muted-foreground">Enter the total amount of memory available on this node for allocation to
          servers. You may also provide a percentage that can allow allocation of more than the defined memory.</p>
        </div>
        <div class="grid gap-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div role="group" class="field">
          <label for="disk" >Disk Space</label>
          <input type="text" name="disk" data-multiplicator="true"
            value="{{ old('disk', $node->disk) }}" />
          <span class="px-2 text-muted-foreground">MiB</span>
          </div>
          <div role="group" class="field">
          <label for="disk_overallocate" >Overallocate</label>
          <input type="text" name="disk_overallocate"
            value="{{ old('disk_overallocate', $node->disk_overallocate) }}" />
          <span class="px-2 text-muted-foreground">%</span>
          </div>
        </div>
        <p class="text-sm text-muted-foreground">Enter the total amount of disk space available on this node for server
          allocation. You may also provide a percentage that will determine the amount of disk space over the set
          limit to allow.</p>
        </div>
      </section>
      </div>
    </div>
    <div>
      <div class="card">
      <header>
        <h3 class="text-lg font-semibold">General Configuration</h3>
      </header>
      <section>
        <div class="grid gap-6">
        <div role="group" class="field">
        <label for="disk_overallocate" >Maximum Web Upload Filesize</label>
        <input type="text" name="upload_size"
          value="{{ old('upload_size', $node->upload_size) }}" />
        <span class="px-2 text-muted-foreground">MiB</span>
        <p class="text-sm text-muted-foreground">Enter the maximum size of files that can be uploaded through the web-based file (1-1024)
          manager. Cloudflare only supports 100mib on free plan if behind cloudflare tunnels</p>
        </div>
        <div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div role="group" class="field">
          <label for="daemonListen" ><span class="badge" data-variant="warning"><x-icon name="power" class="size-4" /></span> Daemon Port</label>
          <input type="text" name="daemonListen"
            value="{{ old('daemonListen', $node->daemonListen) }}" />
          </div>
          <div role="group" class="field">
          <label for="daemonSFTP" ><span class="badge" data-variant="warning"><x-icon name="power" class="size-4" /></span> Daemon SFTP Port</label>
          <input type="text" name="daemonSFTP"
            value="{{ old('daemonSFTP', $node->daemonSFTP) }}" />
          </div>
        </div>
        <div class="grid gap-6">
          <div class="col-span-full">
          <p class="text-sm text-muted-foreground">The daemon runs its own SFTP management container and does not use the SSHd
            process on the main physical server. <Strong>Do not use the same port that you have assigned for
              your physical server's SSH process.</strong></p>
          </div>
        </div>
        </div>
      </section>
      </div>
    </div>

    <div>
      <div class="card">
      <header>
        <h3 class="text-lg font-semibold">Backup Config</h3>
      </header>
      <section>
        <div class="grid gap-6">
        <div role="group" class="field">
        <label for="pBackupDisk" >Backup Disk</label>
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
        <h3 class="text-lg font-semibold">Save Settings</h3>
      </header>
      <section>
        <div class="grid gap-6">
        <div role="group" class="field" data-orientation="horizontal">
          <input type="checkbox" name="reset_secret" id="reset_secret" />
          <label for="reset_secret" class="font-normal">Reset Daemon Master Key</label>
        </div>
        <p class="text-sm text-muted-foreground">Resetting the daemon master key will void any request coming from the old key.
          This key is used for all sensitive operations on the daemon including server creation and deletion. We
          suggest changing this key regularly for security.</p>
        </div>
      </section>
      <footer>
        {!! method_field('PATCH') !!}
        {!! csrf_field() !!}
        <button type="submit" class="btn ml-auto">Save Changes</button>
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
