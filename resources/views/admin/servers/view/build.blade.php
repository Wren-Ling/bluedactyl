@extends('layouts.admin')

@section('title')
    Server — {{ $server->name }}: Build Details
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">{{ $server->name }}</h1>
    <p class="text-sm text-muted-foreground">Control allocations and system resources for this server.</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">Admin</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.servers') }}">Servers</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.servers.view', $server->id) }}">{{ $server->name }}</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>Build Configuration</span>
    </nav>
@endsection

@section('content')
@include('admin.servers.partials.navigation')
<div class="grid grid-cols-1 md:grid-cols-12 gap-6">
    <form action="{{ route('admin.servers.view.build', $server->id) }}" method="POST" class="contents">
        <div class="md:col-span-5">
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">Resource Management</h3>
                </header>
                <section>
                    <div class="grid gap-6">
                        <div role="group" class="field">
                            <label for="cpu" >CPU Limit</label>
                            <input type="text" name="cpu" value="{{ old('cpu', $server->cpu) }}"/>
                            <span class="px-2 text-muted-foreground">%</span>
                            <p class="text-sm text-muted-foreground">Each <em>virtual</em> core (thread) on the system is considered to be <code>100%</code>. Setting this value to <code>0</code> will allow a server to use CPU time without restrictions.</p>
                        </div>
                        <div role="group" class="field">
                            <label for="threads" >CPU Pinning</label>
                            <input type="text" name="threads"  value="{{ old('threads', $server->threads) }}"/>
                            <p class="text-sm text-muted-foreground"><strong>Advanced:</strong> Enter the specific CPU cores that this process can run on, or leave blank to allow all cores. This can be a single number, or a comma seperated list. Example: <code>0</code>, <code>0-1,3</code>, or <code>0,1,3,4</code>.</p>
                        </div>
                        <div role="group" class="field">
                            <label for="memory" >Allocated Memory</label>
                            <input type="text" name="memory" data-multiplicator="true" value="{{ old('memory', $server->memory) }}"/>
                            <span class="px-2 text-muted-foreground">MiB</span>
                            <p class="text-sm text-muted-foreground">The maximum amount of memory allowed for this container. Setting this to <code>0</code> will allow unlimited memory in a container.</p>
                        </div>
                        <div role="group" class="field">
                            <label for="overhead_memory" >Overhead Memory</label>
                            <input type="text" name="overhead_memory" data-multiplicator="true" value="{{ old('overhead_memory', $server->overhead_memory) }}"/>
                            <span class="px-2 text-muted-foreground">MiB</span>
                            <p class="text-sm text-muted-foreground">Additional memory allocated to the container that doesn't go to the SERVER_MEMORY variable. Setting to <code>0</code> disables overhead memory.</p>
                        </div>
                        <div role="group" class="field">
                            <label for="swap" >Allocated Swap</label>
                            <input type="text" name="swap" data-multiplicator="true" value="{{ old('swap', $server->swap) }}"/>
                            <span class="px-2 text-muted-foreground">MiB</span>
                            <p class="text-sm text-muted-foreground">Setting this to <code>0</code> will disable swap space on this server. Setting to <code>-1</code> will allow unlimited swap.</p>
                        </div>
                        <div role="group" class="field">
                            <label for="disk" >Disk Space Limit</label>
                            <input type="text" name="disk" value="{{ old('disk', $server->disk) }}"/>
                            <span class="px-2 text-muted-foreground">MiB</span>
                            <p class="text-sm text-muted-foreground">This server will not be allowed to boot if it is using more than this amount of space. If a server goes over this limit while running it will be safely stopped and locked until enough space is available. Set to <code>0</code> to allow unlimited disk usage.</p>
                        </div>
                        <div role="group" class="field">
                            <label for="io" >Block IO Proportion</label>
                            <input type="text" name="io"  value="{{ old('io', $server->io) }}"/>
                            <p class="text-sm text-muted-foreground"><strong>Advanced</strong>: The IO performance of this server relative to other <em>running</em> containers on the system. Value should be between <code>10</code> and <code>1000</code>.</p>
                        </div>
                        <p class="text-sm font-semibold mb-2">OOM Killer</p>
                        <div role="group" class="field" data-orientation="horizontal">
                            <input type="radio" id="pOomKillerEnabled" value="0" name="oom_disabled"  @if(!$server->oom_disabled)checked @endif>
                            <label for="pOomKillerEnabled" class="font-normal">Enabled</label>
                        </div>
                        <div role="group" class="field" data-orientation="horizontal">
                            <input type="radio" id="pOomKillerDisabled" value="1" name="oom_disabled"  @if($server->oom_disabled)checked @endif>
                            <label for="pOomKillerDisabled" class="font-normal">Disabled</label>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            Enabling OOM killer may cause server processes to exit unexpectedly.
                        </p>
                        <p class="text-sm font-semibold mb-2">Resource Calculation</p>
                        <div role="group" class="field" data-orientation="horizontal">
                            <input type="radio" id="pResourceCalcIncluded" value="0" name="exclude_from_resource_calculation"  @if(!$server->exclude_from_resource_calculation)checked @endif>
                            <label for="pResourceCalcIncluded" class="font-normal">Included</label>
                        </div>
                        <div role="group" class="field" data-orientation="horizontal">
                            <input type="radio" id="pResourceCalcExcluded" value="1" name="exclude_from_resource_calculation"  @if($server->exclude_from_resource_calculation)checked @endif>
                            <label for="pResourceCalcExcluded" class="font-normal">Excluded</label>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            When enabled, this server will not be included in resource calculations when provisioning new servers onto this node. Useful for testing or development servers.
                        </p>
                    </div>
                </section>
            </div>
        </div>
        <div class="md:col-span-7">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="card">
                        <header>
                            <h3 class="text-lg font-semibold">Application Feature Limits</h3>
                        </header>
                        <section>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div role="group" class="field">
                                    <label for="database_limit" >Database Limit</label>
                                    <input type="text" name="database_limit"  value="{{ old('database_limit', $server->database_limit) }}"/>
                                    <p class="text-sm text-muted-foreground">The total number of databases a user is allowed to create for this server. Leave blank for unlimited, set to 0 to disable.</p>
                                </div>
                                <div role="group" class="field">
                                    <label for="allocation_limit" >Allocation Limit</label>
                                    <input type="text" name="allocation_limit"  value="{{ old('allocation_limit', $server->allocation_limit) }}"/>
                                    <p class="text-sm text-muted-foreground">The total number of allocations a user is allowed to create for this server. Leave blank for unlimited, set to 0 to disable.</p>
                                </div>
                                <div role="group" class="field">
                                    <label for="backup_limit" >Backup Limit</label>
                                    <input type="text" name="backup_limit"  value="{{ old('backup_limit', $server->backup_limit) }}"/>
                                    <p class="text-sm text-muted-foreground">The total number of backups that can be created for this server. Leave blank for unlimited, set to 0 to disable.</p>
                                </div>
                                <div role="group" class="field">
                                    <label for="backup_storage_limit" >Backup Storage Limit</label>
                                    <input type="text" name="backup_storage_limit" data-multiplicator="true" value="{{ old('backup_storage_limit', $server->backup_storage_limit) }}"/>
                                    <span class="px-2 text-muted-foreground">MiB</span>
                                    <p class="text-sm text-muted-foreground">The total storage space that can be used for backups. Leave blank for unlimited storage.</p>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
                <div>
                    <div class="card">
                        <header>
                            <h3 class="text-lg font-semibold">Allocation Management</h3>
                        </header>
                        <section>
                            <div role="group" class="field">
                                <label for="pAllocation" >Game Port</label>
                                <select id="pAllocation" name="allocation_id" class="select">
                                    @foreach ($assigned as $assignment)
                                        <option value="{{ $assignment->id }}"
                                            @if($assignment->id === $server->allocation_id)
                                                selected
                                            @endif
                                        >{{ $assignment->alias }}:{{ $assignment->port }}</option>
                                    @endforeach
                                </select>
                                <p class="text-sm text-muted-foreground">The default connection address that will be used for this game server.</p>
                            </div>
                            <div role="group" class="field">
                                <label for="pAddAllocations" >Assign Additional Ports</label>
                                <select name="add_allocations[]" class="select" multiple id="pAddAllocations">
                                    @foreach ($unassigned as $assignment)
                                        <option value="{{ $assignment->id }}">{{ $assignment->alias }}:{{ $assignment->port }}</option>
                                    @endforeach
                                </select>
                                <p class="text-sm text-muted-foreground">Please note that due to software limitations you cannot assign identical ports on different IPs to the same server.</p>
                            </div>
                            <div role="group" class="field">
                                <label for="pRemoveAllocations" >Remove Additional Ports</label>
                                <select name="remove_allocations[]" class="select" multiple id="pRemoveAllocations">
                                    @foreach ($assigned as $assignment)
                                        <option value="{{ $assignment->id }}">{{ $assignment->alias }}:{{ $assignment->port }}</option>
                                    @endforeach
                                </select>
                                <p class="text-sm text-muted-foreground">Simply select which ports you would like to remove from the list above. If you want to assign a port on a different IP that is already in use you can select it from the left and delete it here.</p>
                            </div>
                        </section>
                        <footer>
                            {!! csrf_field() !!}
                            <button type="submit" class="btn ml-auto">Update Build Configuration</button>
                        </footer>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>

    </script>
@endsection
