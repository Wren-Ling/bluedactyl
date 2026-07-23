@extends('layouts.admin')

@section('title')
    Nodes &rarr; New
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">New Node</h1>
    <p class="text-sm text-muted-foreground">Create a new local or remote node for servers to be installed to.</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">Admin</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.nodes') }}">Nodes</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>New</span>
    </nav>
@endsection

@section('content')
<form action="{{ route('admin.nodes.new') }}" method="POST">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">Basic Details</h3>
                </header>
                <section>
                    <div class="grid gap-6">
                    <div role="group" class="field">
                        <label for="pName" >Name</label>
                        <input type="text" name="name" id="pName" value="{{ old('name') }}"/>
                        <p class="text-sm text-muted-foreground">Character limits: <code>a-zA-Z0-9_.-</code> and <code>[Space]</code> (min 1, max 100 characters).</p>
                    </div>
                    <div role="group" class="field">
                        <label for="pDescription" >Description</label>
                        <textarea name="description" id="pDescription" rows="4">{{ old('description') }}</textarea>
                    </div>
                    <div role="group" class="field">
                        <label for="pLocationId" >Location</label>
                        <select name="location_id" id="pLocationId" class="select">
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}" {{ $location->id != old('location_id') ?: 'selected' }}>{{ $location->short }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div role="group" class="field">
                        <label for="pDaemonType" >Daemon</label>
                        <select name="daemonType" id="pDaemonType" class="select">
                            @foreach($daemonTypes as $daemon => $label)
                                <option value="{{ $daemon }}" {{ $daemon == old('daemon_type', 'wings') ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div role="group" class="field">
                        <label for="pBackupDisk" >Backup Disk</label>
                        <select name="backupDisk" id="pBackupDisk" class="select">
                            <!-- Populated via Script-->
                        </select>
                    </div>
                    <div role="group" class="field" data-orientation="horizontal">
                        <input type="radio" id="pPublicTrue" value="1" name="public" checked>
                        <label for="pPublicTrue" class="font-normal">Public</label>
                    </div>
                    <div role="group" class="field" data-orientation="horizontal">
                        <input type="radio" id="pPublicFalse" value="0" name="public">
                        <label for="pPublicFalse" class="font-normal">Private</label>
                    </div>
                    <p class="text-sm text-muted-foreground">By setting a node to <code>private</code> you will be denying the ability to auto-deploy to this node.
                    <div role="group" class="field">
                        <label for="pFQDN" >Public FQDN</label>
                        <input type="text" name="fqdn" id="pFQDN" value="{{ old('fqdn') }}" />
                        <p class="text-sm text-muted-foreground">
                            Domain name that browsers will use to connect to your Node (e.g <code>node.example.com</code>).
                            An IP address may be used <em>only</em> if you are not using SSL for this node.
                        </p>
                    </div>
                    <div role="group" class="field">
                        <label for="pInternalFQDN" >
                            Internal FQDN
                            <strong>(Optional)</strong>
                        </label>
                        <input type="text" name="internal_fqdn" id="pInternalFQDN"
                            value="{{ old('internal_fqdn') }}" />
                        <p class="text-sm text-muted-foreground">
                            <strong>Optional:</strong>
                            Leave blank to use the Public FQDN for panel-to-node communication.
                            If specified, this internal domain name will be used for panel-to-node communication instead
                            (e.g <code>node-internal.example.com</code> or <code>10.0.0.5</code>).
                            Useful for internal networks where the panel needs to communicate with your node using a
                            different address than what browsers use.
                        </p>
                    </div>
                    <div role="group" class="field" data-orientation="horizontal">
                        <input type="radio" id="pSSLTrue" value="https" name="scheme" checked>
                        <label for="pSSLTrue" class="font-normal">Use SSL Connection</label>
                    </div>
                    <div role="group" class="field" data-orientation="horizontal">
                        <input type="radio" id="pSSLFalse" value="http" name="scheme" @if(request()->isSecure()) disabled @endif>
                        <label for="pSSLFalse" class="font-normal">Use HTTP Connection</label>
                    </div>
                    @if(request()->isSecure())
                        <p class="text-destructive small">Your Panel is currently configured to use a secure connection. In order for browsers to connect to your node it <strong>must</strong> use a SSL connection.</p>
                    @else
                        <p class="text-sm text-muted-foreground">In most cases you should select to use a SSL connection. If using an IP Address or you do not wish to use SSL at all, select a HTTP connection.</p>
                    @endif
                    <div role="group" class="field" data-orientation="horizontal">
                        <input type="radio" id="pProxyFalse" value="0" name="behind_proxy" checked>
                        <label for="pProxyFalse" class="font-normal">Not Behind Proxy</label>
                    </div>
                    <div role="group" class="field" data-orientation="horizontal">
                        <input type="radio" id="pProxyTrue" value="1" name="behind_proxy">
                        <label for="pProxyTrue" class="font-normal">Behind Proxy</label>
                    </div>
                    <p class="text-sm text-muted-foreground">If you are running the daemon behind a proxy such as Cloudflare, select this to have the daemon skip looking for certificates on boot.</p>
                    </div>
                </section>
            </div>
        </div>
        <div>
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">Configuration</h3>
                </header>
                <section>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div role="group" class="field">
                            <label for="pDaemonBase" >Daemon Server File Directory</label>
                            <input type="text" name="daemonBase" id="pDaemonBase" value="/var/lib/elytra/volumes" />
                            <p class="text-sm text-muted-foreground">Enter the directory where server files should be stored. <strong>If you use OVH you should check your partition scheme. You may need to use <code>/home/daemon-data</code> to have enough space.</strong></p>
                        </div>
                        <div role="group" class="field">
                            <label for="pMemory" >Total Memory</label>
                            <input type="text" name="memory" data-multiplicator="true"  id="pMemory" value="{{ old('memory') }}"/>
                            <span class="px-2 text-muted-foreground">MiB</span>
                        </div>
                        <div role="group" class="field">
                            <label for="pMemoryOverallocate" >Memory Over-Allocation</label>
                            <input type="text" name="memory_overallocate"  id="pMemoryOverallocate" value="{{ old('memory_overallocate') }}"/>
                            <span class="px-2 text-muted-foreground">%</span>
                        </div>
                        <div class="col-span-full">
                            <p class="text-sm text-muted-foreground">Enter the total amount of memory available for new servers. If you would like to allow overallocation of memory enter the percentage that you want to allow. To disable checking for overallocation enter <code>-1</code> into the field. Entering <code>0</code> will prevent creating new servers if it would put the node over the limit.</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div role="group" class="field">
                            <label for="pDisk" >Total Disk Space</label>
                            <input type="text" name="disk" data-multiplicator="true"  id="pDisk" value="{{ old('disk') }}"/>
                            <span class="px-2 text-muted-foreground">MiB</span>
                        </div>
                        <div role="group" class="field">
                            <label for="pDiskOverallocate" >Disk Over-Allocation</label>
                            <input type="text" name="disk_overallocate"  id="pDiskOverallocate" value="{{ old('disk_overallocate') }}"/>
                            <span class="px-2 text-muted-foreground">%</span>
                        </div>
                        <div class="col-span-full">
                            <p class="text-sm text-muted-foreground">Enter the total amount of disk space available for new servers. If you would like to allow overallocation of disk space enter the percentage that you want to allow. To disable checking for overallocation enter <code>-1</code> into the field. Entering <code>0</code> will prevent creating new servers if it would put the node over the limit.</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div role="group" class="field">
                            <label for="pDaemonListen" >Daemon Port</label>
                            <input type="text" name="daemonListen" id="pDaemonListen" value="8080" />
                        </div>
                        <div role="group" class="field">
                            <label for="pDaemonSFTP" >Daemon SFTP Port</label>
                            <input type="text" name="daemonSFTP" id="pDaemonSFTP" value="2022" />
                        </div>
                        <div class="col-span-full">
                            <p class="text-sm text-muted-foreground">The daemon runs its own SFTP management container and does not use the SSHd process on the main physical server. <Strong>Do not use the same port that you have assigned for your physical server's SSH process.</strong> If you will be running the daemon behind CloudFlare&reg; you should set the daemon port to <code>8443</code> to allow websocket proxying over SSL.</p>
                        </div>
                    </div>
                </section>
                <footer>
                    {!! csrf_field() !!}
                    <button type="submit" class="btn ml-auto">Create Node</button>
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
