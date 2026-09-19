@extends('layouts.admin')

@section('title')
    @lang('admin/server.new.title')
@endsection

@section('scripts')
    @parent
<style>
#pAllocationsList { counter-reset: alloc-page; }
.alloc-row { display: flex; align-items: center; gap: 0.5rem; padding: 0.375rem 0.5rem; border-radius: 0.375rem; cursor: pointer; }
.alloc-row:hover { background: var(--color-accent); }
.alloc-row input[type="checkbox"] { flex-shrink: 0; }
.alloc-row .badge { font-size: 0.625rem; padding: 0.125rem 0.375rem; }
</style>
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">@lang('admin/server.new.header')</h1>
    <p class="text-sm text-muted-foreground">@lang('admin/server.new.description')</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">@lang('admin/server.new.breadcrumb_admin')</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.servers') }}">@lang('admin/server.new.breadcrumb_servers')</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>@lang('admin/server.new.breadcrumb_create')</span>
    </nav>
@endsection

@section('content')
<form action="{{ route('admin.servers.new') }}" method="POST" class="admin-responsive-detail">
    <div class="grid gap-6">
        <div class="col-span-full">
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">@lang('admin/server.new.core_details')</h3>
                </header>

                <section>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div role="group" class="field">
                                <label for="pName">@lang('admin/server.new.server_name')</label>
                                <input type="text"  id="pName" name="name" value="{{ old('name') }}" placeholder="{{ trans('admin/server.new.server_name_placeholder') }}">
                                <p class="text-sm text-muted-foreground">@lang('admin/server.new.server_name_help')</p>
                            </div>

                            <div role="group" class="field">
                                <label for="pUserId">@lang('admin/server.new.server_owner')</label>
                                <input type="hidden" name="owner_id" id="pUserId" value="{{ old('owner_id') }}">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span id="pUserDisplay" class="text-muted-foreground text-sm">
                                        @if (old('owner_id'))
                                            @lang('admin/server.new.loading')
                                        @else
                                            @lang('admin/server.new.no_owner_selected')
                                        @endif
                                    </span>
                                    <button type="button" class="btn" data-size="sm" data-variant="outline" id="openUserSearchBtn">@lang('admin/server.new.select_owner')</button>
                                </div>
                                <p class="text-sm text-muted-foreground">@lang('admin/server.new.server_owner_help')</p>
                            </div>
                        </div>

                        <div>
                            <div role="group" class="field">
                                <label for="pDescription">@lang('admin/server.new.server_description')</label>
                                <textarea id="pDescription" name="description" rows="3" >{{ old('description') }}</textarea>
                                <p class="text-sm text-muted-foreground">@lang('admin/server.new.server_description_help')</p>
                            </div>

                            <div role="group" class="field" data-orientation="horizontal">
                                <input id="pStartOnCreation" name="start_on_completion" type="checkbox"  {{ \Pterodactyl\Helpers\Utilities::checked('start_on_completion', 1) }} />
                                <label for="pStartOnCreation" class="font-normal">@lang('admin/server.new.start_on_creation')</label>
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
                    <h3 class="text-lg font-semibold">@lang('admin/server.new.allocation_management')</h3>
                </header>

                <section>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div role="group" class="field">
                            <label for="pNodeId">@lang('admin/server.new.node')</label>
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

                            <p class="text-sm text-muted-foreground">@lang('admin/server.new.node_help')</p>
                        </div>

                        <div role="group" class="field">
                            <label>@lang('admin/server.new.allocations')</label>
                            <div class="flex flex-wrap items-center gap-2">
                                <span id="pAllocSummary" class="text-sm text-muted-foreground">@lang('admin/server.new.no_allocations_selected')</span>
                                <button type="button" class="btn" data-size="sm" data-variant="outline" id="openAllocBtn">@lang('admin/server.new.select_allocations')</button>
                            </div>
                            <input type="hidden" name="allocation_id" id="pAllocation" value="">
                            <select multiple name="allocation_additional[]" id="pAllocationAdditional" class="hidden"></select>
                            <p class="text-sm text-muted-foreground">@lang('admin/server.new.allocations_help')</p>
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
                    <h3 class="text-lg font-semibold">@lang('admin/server.new.feature_limits')</h3>
                </header>

                <section>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div role="group" class="field">
                            <label for="pDatabaseLimit">@lang('admin/server.new.database_limit')</label>
                            <input type="text" id="pDatabaseLimit" name="database_limit"  value="{{ old('database_limit') }}" placeholder="{{ trans('admin/server.new.unlimited_placeholder') }}"/>
                            <p class="text-sm text-muted-foreground">@lang('admin/server.new.database_limit_help')</p>
                        </div>
                        <div role="group" class="field">
                            <label for="pAllocationLimit">@lang('admin/server.new.allocation_limit')</label>
                            <input type="text" id="pAllocationLimit" name="allocation_limit"  value="{{ old('allocation_limit') }}" placeholder="{{ trans('admin/server.new.unlimited_placeholder') }}"/>
                            <p class="text-sm text-muted-foreground">@lang('admin/server.new.allocation_limit_help')</p>
                        </div>
                        <div role="group" class="field">
                            <label for="pBackupLimit">@lang('admin/server.new.backup_limit')</label>
                            <input type="text" id="pBackupLimit" name="backup_limit"  value="{{ old('backup_limit') }}" placeholder="{{ trans('admin/server.new.unlimited_placeholder') }}"/>
                            <p class="text-sm text-muted-foreground">@lang('admin/server.new.backup_limit_help')</p>
                        </div>
                        <div role="group" class="field">
                            <label for="pBackupStorageLimit">@lang('admin/server.new.backup_storage_limit')</label>
                            <input type="text" id="pBackupStorageLimit" name="backup_storage_limit" data-multiplicator="true"  value="{{ old('backup_storage_limit') }}" placeholder="{{ trans('admin/server.new.unlimited_placeholder') }}"/>
                            <span class="px-2 text-muted-foreground">MiB</span>
                            <p class="text-sm text-muted-foreground">@lang('admin/server.new.backup_storage_limit_help')</p>
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
                    <h3 class="text-lg font-semibold">@lang('admin/server.new.resource_management')</h3>
                </header>

                <section>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div role="group" class="field">
                            <label for="pCPU">@lang('admin/server.new.cpu_limit')</label>

                            <input type="text" id="pCPU" name="cpu"  value="{{ old('cpu', 0) }}" />
                            <span class="px-2 text-muted-foreground">%</span>

                            <p class="text-sm text-muted-foreground">@lang('admin/server.new.cpu_limit_help')</p>
                        </div>

                        <div role="group" class="field">
                            <label for="pThreads">@lang('admin/server.new.cpu_pinning')</label>

                            <input type="text" id="pThreads" name="threads"  value="{{ old('threads') }}" />

                            <p class="text-sm text-muted-foreground">@lang('admin/server.new.cpu_pinning_help')</p>
                        </div>
                    </div>
                </section>

                <section>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div role="group" class="field">
                            <label for="pMemory">@lang('admin/server.new.memory')</label>

                            <input type="text" id="pMemory" name="memory"  value="{{ old('memory') }}" />
                            <span class="px-2 text-muted-foreground">MiB</span>

                            <p class="text-sm text-muted-foreground">@lang('admin/server.new.memory_help')</p>
                        </div>

                        <div role="group" class="field">
                            <label for="pOverheadMemory">@lang('admin/server.new.overhead_memory')</label>

                            <input type="text" id="pOverheadMemory" name="overhead_memory"  value="{{ old('overhead_memory', 0) }}" />
                            <span class="px-2 text-muted-foreground">MiB</span>

                            <p class="text-sm text-muted-foreground">@lang('admin/server.new.overhead_memory_help')</p>
                        </div>
                    </div>
                </section>

                <section>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div role="group" class="field">
                            <label for="pSwap">@lang('admin/server.new.swap')</label>

                            <input type="text" id="pSwap" name="swap"  value="{{ old('swap', 0) }}" />
                            <span class="px-2 text-muted-foreground">MiB</span>

                            <p class="text-sm text-muted-foreground">@lang('admin/server.new.swap_help')</p>
                        </div>
                    </div>
                </section>

                <section>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div role="group" class="field">
                            <label for="pDisk">@lang('admin/server.new.disk_space')</label>

                            <input type="text" id="pDisk" name="disk"  value="{{ old('disk') }}" />
                            <span class="px-2 text-muted-foreground">MiB</span>

                            <p class="text-sm text-muted-foreground">@lang('admin/server.new.disk_space_help')</p>
                        </div>

                        <div role="group" class="field">
                            <label for="pIO">@lang('admin/server.new.block_io_weight')</label>

                            <input type="text" id="pIO" name="io"  value="{{ old('io', 500) }}" />

                            <p class="text-sm text-muted-foreground">@lang('admin/server.new.block_io_weight_help')</p>
                        </div>
                        <div role="group" class="field col-span-full" data-orientation="horizontal">
                            <input type="checkbox" id="pOomDisabled" name="oom_disabled" value="0"  {{ \Pterodactyl\Helpers\Utilities::checked('oom_disabled', 0) }} />
                            <label for="pOomDisabled" class="font-normal">@lang('admin/server.new.enable_oom_killer')</label>
                        </div>
                        <p class="col-span-full text-sm text-muted-foreground">@lang('admin/server.new.oom_killer_help')</p>
                        <div role="group" class="field col-span-full" data-orientation="horizontal">
                            <input type="checkbox" id="pExcludeFromResourceCalculation" name="exclude_from_resource_calculation" value="1"  {{ \Pterodactyl\Helpers\Utilities::checked('exclude_from_resource_calculation', 0) }} />
                            <label for="pExcludeFromResourceCalculation" class="font-normal">@lang('admin/server.new.exclude_resource_calc')</label>
                        </div>
                        <p class="col-span-full text-sm text-muted-foreground">@lang('admin/server.new.exclude_resource_calc_help')</p>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">@lang('admin/server.new.nest_config')</h3>
                </header>

                <section>
                    <div class="grid gap-6">
                        <div role="group" class="field">
                            <label for="pNestId">@lang('admin/server.new.nest')</label>

                            <select id="pNestId" name="nest_id" class="select">
                                @foreach($nests as $nest)
                                    <option value="{{ $nest->id }}"
                                        @if($nest->id === old('nest_id'))
                                            selected
                                        @endif
                                    >{{ $nest->name }}</option>
                                @endforeach
                            </select>

                            <p class="text-sm text-muted-foreground">@lang('admin/server.new.nest_help')</p>
                        </div>

                        <div role="group" class="field">
                            <label for="pEggId">@lang('admin/server.new.egg')</label>
                            <select id="pEggId" name="egg_id" class="select">
                                <option value="">@lang('admin/server.new.select_nest_first')</option>
                            </select>
                            <p class="text-sm text-muted-foreground">@lang('admin/server.new.egg_help')</p>
                        </div>
                        <div role="group" class="field" data-orientation="horizontal">
                            <input type="checkbox" id="pSkipScripting" name="skip_scripts" value="1"  {{ \Pterodactyl\Helpers\Utilities::checked('skip_scripts', 0) }} />
                            <label for="pSkipScripting" class="font-normal">@lang('admin/server.new.skip_egg_install_script')</label>
                        </div>
                        <p class="text-sm text-muted-foreground">@lang('admin/server.new.skip_egg_install_script_help')</p>
                    </div>
                </section>
            </div>
        </div>

        <div>
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">@lang('admin/server.new.docker_config')</h3>
                </header>

                <section>
                    <div class="grid gap-6">
                        <div role="group" class="field">
                            <label for="pDefaultContainer">@lang('admin/server.new.docker_image')</label>
                            <select id="pDefaultContainer" name="image" class="select">
                                <option value="">@lang('admin/server.new.select_egg_first')</option>
                            </select>
                            <input id="pDefaultContainerCustom" name="custom_image" value="{{ old('custom_image') }}" class="input mt-4" placeholder="{{ trans('admin/server.new.custom_image_placeholder') }}"/>
                            <p class="text-sm text-muted-foreground">@lang('admin/server.new.docker_image_help')</p>
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
                    <h3 class="text-lg font-semibold">@lang('admin/server.new.startup_config')</h3>
                </header>

                <section>
                    <div class="grid gap-6">
                        <div role="group" class="field">
                            <label for="pStartup">@lang('admin/server.new.startup_command')</label>
                            <input type="text" id="pStartup" name="startup" value="{{ old('startup') }}"  />
                            <p class="text-sm text-muted-foreground">@lang('admin/server.new.startup_command_help')</p>
                        </div>
                    </div>
                </section>

                <header class="-mt-2">
                    <h3 class="text-lg font-semibold">@lang('admin/server.new.service_variables')</h3>
                </header>

                <section class="grid gap-6" id="appendVariablesTo"></section>

                <footer>
                    {!! csrf_field() !!}
                    <input type="submit" class="btn ml-auto" value="@lang('admin/server.new.create_server_submit')" />
                </footer>
            </div>
        </div>
    </div>
</form>

<dialog class="dialog" id="userSearchModal" aria-labelledby="userSearchModal-title" aria-describedby="userSearchModal-desc" onclick="if (event.target === this) this.close()">
    <div class="sm:max-w-md">
        <header>
            <h2 id="userSearchModal-title">@lang('admin/server.new.select_server_owner_title')</h2>
            <p id="userSearchModal-desc">@lang('admin/server.new.select_server_owner_desc')</p>
        </header>
        <section>
            <div role="group" class="field">
                <label for="pUserSearch">@lang('admin/server.new.search_by_email')</label>
                <input type="text" id="pUserSearch" placeholder="{{ trans('admin/server.new.search_placeholder') }}" autocomplete="off">
            </div>
            <div id="pUserSearchResults" class="mt-2 space-y-1 max-h-64 overflow-y-auto"></div>
            <div id="pUserSearchEmpty" class="hidden text-sm text-muted-foreground text-center py-4">@lang('admin/server.new.no_users_found')</div>
            <div id="pUserSearchLoading" class="hidden text-sm text-muted-foreground text-center py-4 flex items-center justify-center gap-2">
                <svg aria-label="Loading" role="status" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56" /></svg>
                @lang('admin/server.new.searching')
            </div>
        </section>
        <footer>
            <button type="button" class="btn" data-variant="outline" onclick="this.closest('dialog').close()">@lang('admin/server.new.cancel')</button>
        </footer>
        <button type="button" class="btn" data-variant="ghost" data-size="icon-sm" aria-label="@lang('admin/server.new.close_dialog')" onclick="this.closest('dialog').close()"><x-icon name="x" class="size-4" /></button>
    </div>
</dialog>

<dialog class="dialog" id="allocModal" aria-labelledby="allocModal-title" onclick="if (event.target === this) this.close()">
    <div class="sm:max-w-lg">
        <header>
            <h2 id="allocModal-title">@lang('admin/server.new.select_allocations_title')</h2>
            <p class="text-sm text-muted-foreground">@lang('admin/server.new.select_allocations_desc') <strong id="allocModalNodeName"></strong></p>
        </header>
        <section class="min-h-0 overflow-y-auto">
            <div id="pAllocationsList" class="divide-y"></div>
            <div id="pAllocEmpty" class="hidden text-sm text-muted-foreground text-center py-8">@lang('admin/server.new.no_available_allocations')</div>
            <div id="pAllocLoader" class="hidden text-sm text-muted-foreground text-center py-8">@lang('admin/server.new.loading_allocations')</div>
        </section>
        <footer class="flex items-center justify-between">
            <nav role="navigation" aria-label="pagination">
                <ul class="flex flex-row items-center gap-1" id="pAllocPagination"></ul>
            </nav>
            <div class="flex items-center gap-2">
                <span id="pAllocSelectedCount" class="text-xs text-muted-foreground">@lang('admin/server.new.x_selected')</span>
                <button type="button" class="btn" onclick="confirmAllocations()">@lang('admin/server.new.confirm')</button>
            </div>
        </footer>
        <button type="button" class="btn" data-variant="ghost" data-size="icon-sm" aria-label="@lang('admin/server.new.close')" onclick="this.closest('dialog').close()"><x-icon name="x" class="size-4" /></button>
    </div>
</dialog>
@endsection

@section('footer-scripts')
    @parent

    <script>
    window.Pyrodactyl = window.Pyrodactyl || {};
    Pyrodactyl.nodeData = {!! $nodeDataJson !!};
    Pyrodactyl.nests = {!! $nestsDataJson !!};
    </script>

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
                        if (ids[@json($key)]) {
                            document.getElementById(ids[@json($key)]).value = @json($value);
                        }
                    @endforeach
                @endif
            @endif
            @if(old('image'))
                document.getElementById('pDefaultContainer').value = @json(old('image'));
            @endif
        }
        // END Persist 'Service Variables'
    </script>

    {!! Theme::js('js/admin/new-server.js?v=20260919') !!}

    <script type="application/javascript">
        function selectUser(user) {
            var display = document.getElementById('pUserDisplay');
            var selection = document.createElement('span');
            var avatar = document.createElement('img');
            var name = document.createElement('span');
            var email = document.createElement('span');

            document.getElementById('pUserId').value = user.id;
            selection.className = 'inline-flex items-center gap-2 rounded-md border px-3 py-1.5';
            avatar.className = 'size-6 rounded-full';
            avatar.src = 'https://cravatar.cn/avatar/' + encodeURIComponent(user.md5) + '?s=48';
            avatar.alt = '';
            name.textContent = (user.name_first || '') + ' ' + (user.name_last || '');
            email.className = 'text-muted-foreground';
            email.textContent = '(' + user.email + ')';
            selection.append(avatar, name, email);
            display.replaceChildren(selection);
            document.getElementById('userSearchModal').close();
        }

        document.getElementById('openUserSearchBtn').addEventListener('click', function () {
            document.getElementById('userSearchModal').showModal();
            document.getElementById('pUserSearch').focus();
        });

        // Persist 'Server Owner'
        @if (old('owner_id'))
            fetch('/admin/users/accounts.json?user_id={{ old('owner_id') }}', {
                headers: { Accept: 'application/json' },
            }).then(function (response) {
                if (!response.ok) throw new Error('Unable to load owner.');
                return response.json();
            }).then(selectUser);
        @endif

        // Persist 'Node'
        @if (old('node_id'))
            document.getElementById('pNodeId').value = @json(old('node_id'));
            document.getElementById('pNodeId').dispatchEvent(new Event('change'));

            @if (old('allocation_id') || old('allocation_additional'))
                var node = Pyrodactyl.nodeData.find(function (item) {
                    return String(item.id) === String(@json(old('node_id')));
                });
                if (node) {
                    allAllocations = node.allocations;
                    var persistedAllocationIds = @json(array_values(array_filter(array_merge([old('allocation_id')], old('allocation_additional', [])))));
                    persistedAllocationIds.forEach(function (id) {
                        var allocation = node.allocations.find(function (item) {
                            return String(item.id) === String(id);
                        });
                        if (allocation) selectedAllocs[String(id)] = allocation.text;
                    });
                    updateAllocSummary();
                }
            @endif
        @endif

        // Persist 'Nest'
        @if (old('nest_id'))
            document.getElementById('pNestId').value = @json(old('nest_id'));
            document.getElementById('pNestId').dispatchEvent(new Event('change'));

            @if (old('egg_id'))
                document.getElementById('pEggId').value = @json(old('egg_id'));
                document.getElementById('pEggId').dispatchEvent(new Event('change'));
            @endif
        @endif

        // User search in modal
        var searchTimeout;
        document.getElementById('pUserSearch').addEventListener('input', function () {
            clearTimeout(searchTimeout);
            var term = this.value.trim();
            var results = document.getElementById('pUserSearchResults');
            var empty = document.getElementById('pUserSearchEmpty');
            var loading = document.getElementById('pUserSearchLoading');

            if (term.length < 2) {
                results.replaceChildren();
                empty.classList.add('hidden');
                loading.classList.add('hidden');
                return;
            }

            results.replaceChildren();
            empty.classList.add('hidden');
            loading.classList.remove('hidden');
            searchTimeout = setTimeout(function () {
                var query = new URLSearchParams({ 'filter[email]': term });
                fetch('/admin/users/accounts.json?' + query.toString(), {
                    headers: { Accept: 'application/json' },
                }).then(function (response) {
                    if (!response.ok) throw new Error('Unable to search users.');
                    return response.json();
                }).then(function (data) {
                    loading.classList.add('hidden');
                    var users = data && data.data ? data.data : data;
                    if (!users || users.length === 0) {
                        empty.classList.remove('hidden');
                        return;
                    }

                    users.forEach(function (user) {
                        var card = document.createElement('button');
                        var avatar = document.createElement('img');
                        var info = document.createElement('span');
                        var name = document.createElement('span');
                        var details = document.createElement('span');
                        card.type = 'button';
                        card.className = 'flex w-full items-center gap-3 rounded-md border p-3 text-left hover:bg-accent';
                        card.addEventListener('click', function () { selectUser(user); });
                        avatar.className = 'size-10 rounded-full';
                        avatar.src = 'https://cravatar.cn/avatar/' + encodeURIComponent(user.md5) + '?s=80';
                        avatar.alt = '';
                        info.className = 'min-w-0 flex-1';
                        name.className = 'block truncate font-medium';
                        name.textContent = (user.name_first || '') + ' ' + (user.name_last || '');
                        details.className = 'block truncate text-sm text-muted-foreground';
                        details.textContent = user.email + ' - ' + user.username;
                        info.append(name, details);
                        card.append(avatar, info);
                        results.append(card);
                    });
                }).catch(function () {
                    loading.classList.add('hidden');
                    var error = document.createElement('div');
                    error.className = 'py-2 text-center text-sm text-destructive';
                    error.textContent = @json(trans('admin/server.new.search_failed'));
                    results.replaceChildren(error);
                });
            }, 300);
        });
    </script>


@endsection
