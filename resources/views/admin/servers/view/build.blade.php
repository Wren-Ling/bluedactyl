@extends('layouts.admin')

@section('contentWidth', 'max-w-6xl')

@section('title')
    @lang('admin/server.overview.title') — {{ $server->name }}: @lang('admin/server.build.title')
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">{{ $server->name }}</h1>
    <p class="text-sm text-muted-foreground">@lang('admin/server.build.description')</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">@lang('admin/server.build.breadcrumb_admin')</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.servers') }}">@lang('admin/server.build.breadcrumb_servers')</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.servers.view', $server->id) }}">{{ $server->name }}</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>@lang('admin/server.build.breadcrumb_build')</span>
    </nav>
@endsection

@section('content')
@include('admin.servers.partials.navigation')
<div class="admin-responsive-detail grid min-w-0 grid-cols-1 gap-6 lg:grid-cols-2">
    <form action="{{ route('admin.servers.view.build', $server->id) }}" method="POST" class="contents">
        <div class="min-w-0">
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">@lang('admin/server.build.resource_management')</h3>
                </header>
                <section>
                    <div class="grid gap-6">
                        <div role="group" class="field">
                            <label for="cpu" >@lang('admin/server.build.cpu_limit')</label>
                            <input type="text" name="cpu" value="{{ old('cpu', $server->cpu) }}"/>
                            <span class="px-2 text-muted-foreground">%</span>
                            <p class="text-sm text-muted-foreground">@lang('admin/server.build.cpu_limit_help')</p>
                        </div>
                        <div role="group" class="field">
                            <label for="threads" >@lang('admin/server.build.cpu_pinning')</label>
                            <input type="text" name="threads"  value="{{ old('threads', $server->threads) }}"/>
                            <p class="text-sm text-muted-foreground">@lang('admin/server.build.cpu_pinning_help')</p>
                        </div>
                        <div role="group" class="field">
                            <label for="memory" >@lang('admin/server.build.allocated_memory')</label>
                            <input type="text" name="memory" data-multiplicator="true" value="{{ old('memory', $server->memory) }}"/>
                            <span class="px-2 text-muted-foreground">MiB</span>
                            <p class="text-sm text-muted-foreground">@lang('admin/server.build.memory_help')</p>
                        </div>
                        <div role="group" class="field">
                            <label for="overhead_memory" >@lang('admin/server.build.overhead_memory')</label>
                            <input type="text" name="overhead_memory" data-multiplicator="true" value="{{ old('overhead_memory', $server->overhead_memory) }}"/>
                            <span class="px-2 text-muted-foreground">MiB</span>
                            <p class="text-sm text-muted-foreground">@lang('admin/server.build.overhead_memory_help')</p>
                        </div>
                        <div role="group" class="field">
                            <label for="swap" >@lang('admin/server.build.allocated_swap')</label>
                            <input type="text" name="swap" data-multiplicator="true" value="{{ old('swap', $server->swap) }}"/>
                            <span class="px-2 text-muted-foreground">MiB</span>
                            <p class="text-sm text-muted-foreground">@lang('admin/server.build.swap_help')</p>
                        </div>
                        <div role="group" class="field">
                            <label for="disk" >@lang('admin/server.build.disk_space')</label>
                            <input type="text" name="disk" value="{{ old('disk', $server->disk) }}"/>
                            <span class="px-2 text-muted-foreground">MiB</span>
                            <p class="text-sm text-muted-foreground">@lang('admin/server.build.disk_space_help')</p>
                        </div>
                        <div role="group" class="field">
                            <label for="io" >@lang('admin/server.build.block_io')</label>
                            <input type="text" name="io"  value="{{ old('io', $server->io) }}"/>
                            <p class="text-sm text-muted-foreground">@lang('admin/server.build.block_io_help')</p>
                        </div>
                        <p class="text-sm font-semibold mb-2">@lang('admin/server.build.oom_killer')</p>
                        <div role="group" class="field" data-orientation="horizontal">
                            <input type="radio" id="pOomKillerEnabled" value="0" name="oom_disabled"  @if(!$server->oom_disabled)checked @endif>
                            <label for="pOomKillerEnabled" class="font-normal">@lang('admin/server.build.oom_enabled')</label>
                        </div>
                        <div role="group" class="field" data-orientation="horizontal">
                            <input type="radio" id="pOomKillerDisabled" value="1" name="oom_disabled"  @if($server->oom_disabled)checked @endif>
                            <label for="pOomKillerDisabled" class="font-normal">@lang('admin/server.build.oom_disabled')</label>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            @lang('admin/server.build.oom_killer_help')
                        </p>
                        <p class="text-sm font-semibold mb-2">@lang('admin/server.build.resource_calculation')</p>
                        <div role="group" class="field" data-orientation="horizontal">
                            <input type="radio" id="pResourceCalcIncluded" value="0" name="exclude_from_resource_calculation"  @if(!$server->exclude_from_resource_calculation)checked @endif>
                            <label for="pResourceCalcIncluded" class="font-normal">@lang('admin/server.build.resource_included')</label>
                        </div>
                        <div role="group" class="field" data-orientation="horizontal">
                            <input type="radio" id="pResourceCalcExcluded" value="1" name="exclude_from_resource_calculation"  @if($server->exclude_from_resource_calculation)checked @endif>
                            <label for="pResourceCalcExcluded" class="font-normal">@lang('admin/server.build.resource_excluded')</label>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            @lang('admin/server.build.resource_calculation_help')
                        </p>
                    </div>
                </section>
            </div>
        </div>
        <div class="min-w-0">
            <div class="grid min-w-0 gap-6">
                <div class="min-w-0">
                    <div class="card">
                        <header>
                            <h3 class="text-lg font-semibold">@lang('admin/server.build.feature_limits')</h3>
                        </header>
                        <section>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div role="group" class="field">
                                    <label for="database_limit" >@lang('admin/server.build.database_limit')</label>
                                    <input type="text" name="database_limit"  value="{{ old('database_limit', $server->database_limit) }}"/>
                                    <p class="text-sm text-muted-foreground">@lang('admin/server.build.database_limit_help')</p>
                                </div>
                                <div role="group" class="field">
                                    <label for="allocation_limit" >@lang('admin/server.build.allocation_limit')</label>
                                    <input type="text" name="allocation_limit"  value="{{ old('allocation_limit', $server->allocation_limit) }}"/>
                                    <p class="text-sm text-muted-foreground">@lang('admin/server.build.allocation_limit_help')</p>
                                </div>
                                <div role="group" class="field">
                                    <label for="backup_limit" >@lang('admin/server.build.backup_limit')</label>
                                    <input type="text" name="backup_limit"  value="{{ old('backup_limit', $server->backup_limit) }}"/>
                                    <p class="text-sm text-muted-foreground">@lang('admin/server.build.backup_limit_help')</p>
                                </div>
                                <div role="group" class="field">
                                    <label for="backup_storage_limit" >@lang('admin/server.build.backup_storage_limit')</label>
                                    <input type="text" name="backup_storage_limit" data-multiplicator="true" value="{{ old('backup_storage_limit', $server->backup_storage_limit) }}"/>
                                    <span class="px-2 text-muted-foreground">MiB</span>
                                    <p class="text-sm text-muted-foreground">@lang('admin/server.build.backup_storage_limit_help')</p>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
                <div class="min-w-0">
                    <div class="card">
                        <header>
                            <h3 class="text-lg font-semibold">@lang('admin/server.build.allocation_management')</h3>
                        </header>
                        <section>
                            <div role="group" class="field">
                                <label for="pAllocation" >@lang('admin/server.build.game_port')</label>
                                <select id="pAllocation" name="allocation_id" class="select w-full">
                                    @foreach ($assigned as $assignment)
                                        <option value="{{ $assignment->id }}"
                                            @if($assignment->id === $server->allocation_id)
                                                selected
                                            @endif
                                        >{{ $assignment->alias }}:{{ $assignment->port }}</option>
                                    @endforeach
                                </select>
                                <p class="text-sm text-muted-foreground">@lang('admin/server.build.game_port_help')</p>
                            </div>
                            <div role="group" class="field">
                                <label for="pAddAllocations" >@lang('admin/server.build.assign_additional_ports')</label>
                                <select name="add_allocations[]" class="select w-full" multiple id="pAddAllocations">
                                    @foreach ($unassigned as $assignment)
                                        <option value="{{ $assignment->id }}">{{ $assignment->alias }}:{{ $assignment->port }}</option>
                                    @endforeach
                                </select>
                                <p class="text-sm text-muted-foreground">@lang('admin/server.build.assign_additional_ports_help')</p>
                            </div>
                            <div role="group" class="field">
                                <label for="pRemoveAllocations" >@lang('admin/server.build.remove_additional_ports')</label>
                                <select name="remove_allocations[]" class="select w-full" multiple id="pRemoveAllocations">
                                    @foreach ($assigned as $assignment)
                                        <option value="{{ $assignment->id }}">{{ $assignment->alias }}:{{ $assignment->port }}</option>
                                    @endforeach
                                </select>
                                <p class="text-sm text-muted-foreground">@lang('admin/server.build.remove_additional_ports_help')</p>
                            </div>
                        </section>
                        <footer>
                            {!! csrf_field() !!}
                            <button type="submit" class="btn ml-auto">@lang('admin/server.build.update_build')</button>
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
