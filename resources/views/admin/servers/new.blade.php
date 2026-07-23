@extends('layouts.admin')

@section('title')
    New Server
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">Create Server</h1>
    <p class="text-sm text-muted-foreground">Add a new server to the panel.</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">Admin</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.servers') }}">Servers</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>Create Server</span>
    </nav>
@endsection

@section('content')
<form action="{{ route('admin.servers.new') }}" method="POST">
    <div class="grid gap-6">
        <div class="col-span-full">
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">Core Details</h3>
                </header>

                <section>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div role="group" class="field">
                                <label for="pName">Server Name</label>
                                <input type="text"  id="pName" name="name" value="{{ old('name') }}" placeholder="Server Name">
                                <p class="text-sm text-muted-foreground">Character limits: <code>a-z A-Z 0-9 _ - .</code> and <code>[Space]</code>.</p>
                            </div>

                            <div role="group" class="field">
                                <label for="pUserId">Server Owner</label>
                                <select id="pUserId" name="owner_id" class="select pl-0"></select>
                                <p class="text-sm text-muted-foreground">Email address of the Server Owner.</p>
                            </div>
                        </div>

                        <div>
                            <div role="group" class="field">
                                <label for="pDescription">Server Description</label>
                                <textarea id="pDescription" name="description" rows="3" >{{ old('description') }}</textarea>
                                <p class="text-sm text-muted-foreground">A brief description of this server.</p>
                            </div>

                            <div role="group" class="field" data-orientation="horizontal">
                                <input id="pStartOnCreation" name="start_on_completion" type="checkbox"  {{ \Pterodactyl\Helpers\Utilities::checked('start_on_completion', 1) }} />
                                <label for="pStartOnCreation" class="font-normal">Start Server when Installed</label>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <div class="grid gap-6">
        <div class="col-span-full">
            <div class="card">
                <div class="overlay hidden" id="allocationLoader"><x-icon name="refresh-cw" class="size-4 animate-spin" /></div>
                <header>
                    <h3 class="text-lg font-semibold">Allocation Management</h3>
                </header>

                <section>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div role="group" class="field">
                            <label for="pNodeId">Node</label>
                            <select name="node_id" id="pNodeId" class="select">
                                @foreach($locations as $location)
                                    <optgroup label="{{ $location->long }} ({{ $location->short }})">
                                    @foreach($location->nodes as $node)

                                    <option value="{{ $node->id }}"
                                        @if($location->id === old('location_id')) selected @endif
                                    >{{ $node->name }}</option>

                                    @endforeach
                                    </optgroup>
                                @endforeach
                            </select>

                            <p class="text-sm text-muted-foreground">The node which this server will be deployed to.</p>
                        </div>

                        <div role="group" class="field">
                            <label for="pAllocation">Default Allocation</label>
                            <select id="pAllocation" name="allocation_id" class="select"></select>
                            <p class="text-sm text-muted-foreground">The main allocation that will be assigned to this server.</p>
                        </div>

                        <div role="group" class="field">
                            <label for="pAllocationAdditional">Additional Allocation(s)</label>
                            <select id="pAllocationAdditional" name="allocation_additional[]" class="select" multiple></select>
                            <p class="text-sm text-muted-foreground">Additional allocations to assign to this server on creation.</p>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <div class="grid gap-6">
        <div class="col-span-full">
            <div class="card">
                <div class="overlay hidden" id="allocationLoader"><x-icon name="refresh-cw" class="size-4 animate-spin" /></div>
                <header>
                    <h3 class="text-lg font-semibold">Application Feature Limits</h3>
                </header>

                <section>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div role="group" class="field">
                            <label for="pDatabaseLimit">Database Limit</label>
                            <input type="text" id="pDatabaseLimit" name="database_limit"  value="{{ old('database_limit') }}" placeholder="Leave blank for unlimited"/>
                            <p class="text-sm text-muted-foreground">The total number of databases a user is allowed to create for this server. Leave blank for unlimited, set to 0 to disable.</p>
                        </div>
                        <div role="group" class="field">
                            <label for="pAllocationLimit">Allocation Limit</label>
                            <input type="text" id="pAllocationLimit" name="allocation_limit"  value="{{ old('allocation_limit') }}" placeholder="Leave blank for unlimited"/>
                            <p class="text-sm text-muted-foreground">The total number of allocations a user is allowed to create for this server. Leave blank for unlimited, set to 0 to disable.</p>
                        </div>
                        <div role="group" class="field">
                            <label for="pBackupLimit">Backup Limit</label>
                            <input type="text" id="pBackupLimit" name="backup_limit"  value="{{ old('backup_limit') }}" placeholder="Leave blank for unlimited"/>
                            <p class="text-sm text-muted-foreground">The total number of backups that can be created for this server. Leave blank for unlimited, set to 0 to disable.</p>
                        </div>
                        <div role="group" class="field">
                            <label for="pBackupStorageLimit">Backup Storage Limit</label>
                            <input type="text" id="pBackupStorageLimit" name="backup_storage_limit" data-multiplicator="true"  value="{{ old('backup_storage_limit') }}" placeholder="Leave blank for unlimited"/>
                            <span class="px-2 text-muted-foreground">MiB</span>
                            <p class="text-sm text-muted-foreground">The total storage space that can be used for backups. Leave blank for unlimited storage.</p>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <div class="grid gap-6">
        <div class="col-span-full">
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">Resource Management</h3>
                </header>

                <section>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div role="group" class="field">
                            <label for="pCPU">CPU Limit</label>

                            <input type="text" id="pCPU" name="cpu"  value="{{ old('cpu', 0) }}" />
                            <span class="px-2 text-muted-foreground">%</span>

                            <p class="text-sm text-muted-foreground">If you do not want to limit CPU usage, set the value to <code>0</code>. To determine a value, take the number of threads and multiply it by 100. For example, on a quad core system without hyperthreading <code>(4 * 100 = 400)</code> there is <code>400%</code> available. To limit a server to using half of a single thread, you would set the value to <code>50</code>. To allow a server to use up to two threads, set the value to <code>200</code>.</p>
                        </div>

                        <div role="group" class="field">
                            <label for="pThreads">CPU Pinning</label>

                            <input type="text" id="pThreads" name="threads"  value="{{ old('threads') }}" />

                            <p class="text-sm text-muted-foreground"><strong>Advanced:</strong> Enter the specific CPU threads that this process can run on, or leave blank to allow all threads. This can be a single number, or a comma separated list. Example: <code>0</code>, <code>0-1,3</code>, or <code>0,1,3,4</code>.</p>
                        </div>
                    </div>
                </section>

                <section>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div role="group" class="field">
                            <label for="pMemory">Memory</label>

                            <input type="text" id="pMemory" name="memory"  value="{{ old('memory') }}" />
                            <span class="px-2 text-muted-foreground">MiB</span>

                            <p class="text-sm text-muted-foreground">The maximum amount of memory allowed for this container. Setting this to <code>0</code> will allow unlimited memory in a container.</p>
                        </div>

                        <div role="group" class="field">
                            <label for="pOverheadMemory">Overhead Memory</label>

                            <input type="text" id="pOverheadMemory" name="overhead_memory"  value="{{ old('overhead_memory', 0) }}" />
                            <span class="px-2 text-muted-foreground">MiB</span>

                            <p class="text-sm text-muted-foreground">Additional memory allocated to the container that doesn't go to the SERVER_MEMORY variable. Setting to <code>0</code> disables overhead memory.</p>
                        </div>
                    </div>
                </section>

                <section>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div role="group" class="field">
                            <label for="pSwap">Swap</label>

                            <input type="text" id="pSwap" name="swap"  value="{{ old('swap', 0) }}" />
                            <span class="px-2 text-muted-foreground">MiB</span>

                            <p class="text-sm text-muted-foreground">Setting this to <code>0</code> will disable swap space on this server. Setting to <code>-1</code> will allow unlimited swap.</p>
                        </div>
                    </div>
                </section>

                <section>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div role="group" class="field">
                            <label for="pDisk">Disk Space</label>

                            <input type="text" id="pDisk" name="disk"  value="{{ old('disk') }}" />
                            <span class="px-2 text-muted-foreground">MiB</span>

                            <p class="text-sm text-muted-foreground">This server will not be allowed to boot if it is using more than this amount of space. If a server goes over this limit while running it will be safely stopped and locked until enough space is available. Set to <code>0</code> to allow unlimited disk usage.</p>
                        </div>

                        <div role="group" class="field">
                            <label for="pIO">Block IO Weight</label>

                            <input type="text" id="pIO" name="io"  value="{{ old('io', 500) }}" />

                            <p class="text-sm text-muted-foreground"><strong>Advanced</strong>: The IO performance of this server relative to other <em>running</em> containers on the system. Value should be between <code>10</code> and <code>1000</code>. Please see <a href="https://docs.docker.com/engine/reference/run/#block-io-bandwidth-blkio-constraint" target="_blank">this documentation</a> for more information about it.</p>
                        </div>
                        <div role="group" class="field col-span-full" data-orientation="horizontal">
                            <input type="checkbox" id="pOomDisabled" name="oom_disabled" value="0"  {{ \Pterodactyl\Helpers\Utilities::checked('oom_disabled', 0) }} />
                            <label for="pOomDisabled" class="font-normal">Enable OOM Killer</label>
                        </div>
                        <p class="col-span-full text-sm text-muted-foreground">Terminates the server if it breaches the memory limits. Enabling OOM killer may cause server processes to exit unexpectedly.</p>
                        <div role="group" class="field col-span-full" data-orientation="horizontal">
                            <input type="checkbox" id="pExcludeFromResourceCalculation" name="exclude_from_resource_calculation" value="1"  {{ \Pterodactyl\Helpers\Utilities::checked('exclude_from_resource_calculation', 0) }} />
                            <label for="pExcludeFromResourceCalculation" class="font-normal">Exclude from Resource Calculation</label>
                        </div>
                        <p class="col-span-full text-sm text-muted-foreground">When enabled, this server will not be included in resource calculations when provisioning new servers onto this node. Useful for testing or development servers.</p>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">Nest Configuration</h3>
                </header>

                <section>
                    <div class="grid gap-6">
                        <div role="group" class="field">
                            <label for="pNestId">Nest</label>

                            <select id="pNestId" name="nest_id" class="select">
                                @foreach($nests as $nest)
                                    <option value="{{ $nest->id }}"
                                        @if($nest->id === old('nest_id'))
                                            selected
                                        @endif
                                    >{{ $nest->name }}</option>
                                @endforeach
                            </select>

                            <p class="text-sm text-muted-foreground">Select the Nest that this server will be grouped under.</p>
                        </div>

                        <div role="group" class="field">
                            <label for="pEggId">Egg</label>
                            <select id="pEggId" name="egg_id" class="select"></select>
                            <p class="text-sm text-muted-foreground">Select the Egg that will define how this server should operate.</p>
                        </div>
                        <div role="group" class="field" data-orientation="horizontal">
                            <input type="checkbox" id="pSkipScripting" name="skip_scripts" value="1"  {{ \Pterodactyl\Helpers\Utilities::checked('skip_scripts', 0) }} />
                            <label for="pSkipScripting" class="font-normal">Skip Egg Install Script</label>
                        </div>
                        <p class="text-sm text-muted-foreground">If the selected Egg has an install script attached to it, the script will run during the install. If you would like to skip this step, check this box.</p>
                    </div>
                </section>
            </div>
        </div>

        <div>
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">Docker Configuration</h3>
                </header>

                <section>
                    <div class="grid gap-6">
                        <div role="group" class="field">
                            <label for="pDefaultContainer">Docker Image</label>
                            <select id="pDefaultContainer" name="image" class="select"></select>
                            <input id="pDefaultContainerCustom" name="custom_image" value="{{ old('custom_image') }}" class="input mt-4" placeholder="Or enter a custom image..."/>
                            <p class="text-sm text-muted-foreground">This is the default Docker image that will be used to run this server. Select an image from the dropdown above, or enter a custom image in the text field above.</p>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <div class="grid gap-6">
        <div class="col-span-full">
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">Startup Configuration</h3>
                </header>

                <section>
                    <div class="grid gap-6">
                        <div role="group" class="field">
                            <label for="pStartup">Startup Command</label>
                            <input type="text" id="pStartup" name="startup" value="{{ old('startup') }}"  />
                            <p class="text-sm text-muted-foreground">The following data substitutes are available for the startup command: <code>@{{SERVER_MEMORY}}</code>, <code>@{{SERVER_IP}}</code>, and <code>@{{SERVER_PORT}}</code>. They will be replaced with the allocated memory, server IP, and server port respectively.</p>
                        </div>
                    </div>
                </section>

                <header class="-mt-2">
                    <h3 class="text-lg font-semibold">Service Variables</h3>
                </header>

                <section class="grid gap-6" id="appendVariablesTo"></section>

                <footer>
                    {!! csrf_field() !!}
                    <input type="submit" class="btn ml-auto" value="Create Server" />
                </footer>
            </div>
        </div>
    </div>
</form>
@endsection

@section('footer-scripts')
    @parent
    {!! Theme::js('vendor/lodash/lodash.js') !!}

    <script type="application/javascript">
        // Persist 'Service Variables'
        function serviceVariablesUpdated(eggId, ids) {
            @if (old('egg_id'))
                // Check if the egg id matches.
                if (eggId != '{{ old('egg_id') }}') {
                    return;
                }

                @if (old('environment'))
                    @foreach (old('environment') as $key => $value)
                        $('#' + ids['{{ $key }}']).val('{{ $value }}');
                    @endforeach
                @endif
            @endif
            @if(old('image'))
                $('#pDefaultContainer').val('{{ old('image') }}');
            @endif
        }
        // END Persist 'Service Variables'
    </script>

    {!! Theme::js('js/admin/new-server.js?v=20220530') !!}

    <script type="application/javascript">
        $(document).ready(function() {
            // Persist 'Server Owner' select2
            @if (old('owner_id'))
                $.ajax({
                    url: '/admin/users/accounts.json?user_id={{ old('owner_id') }}',
                    dataType: 'json',
                }).then(function (data) {
                    initUserIdSelect([ data ]);
                });
            @else
                initUserIdSelect();
            @endif
            // END Persist 'Server Owner' select2

            // Persist 'Node' select2
            @if (old('node_id'))
                $('#pNodeId').val('{{ old('node_id') }}').change();

                // Persist 'Default Allocation' select2
                @if (old('allocation_id'))
                    $('#pAllocation').val('{{ old('allocation_id') }}').change();
                @endif
                // END Persist 'Default Allocation' select2

                // Persist 'Additional Allocations' select2
                @if (old('allocation_additional'))
                    const additional_allocations = [];

                    @for ($i = 0; $i < count(old('allocation_additional')); $i++)
                        additional_allocations.push('{{ old('allocation_additional.'.$i)}}');
                    @endfor

                    $('#pAllocationAdditional').val(additional_allocations).change();
                @endif
                // END Persist 'Additional Allocations' select2
            @endif
            // END Persist 'Node' select2

            // Persist 'Nest' select2
            @if (old('nest_id'))
                $('#pNestId').val('{{ old('nest_id') }}').change();

                // Persist 'Egg' select2
                @if (old('egg_id'))
                    $('#pEggId').val('{{ old('egg_id') }}').change();
                @endif
                // END Persist 'Egg' select2
            @endif
            // END Persist 'Nest' select2
        });
    </script>


@endsection
