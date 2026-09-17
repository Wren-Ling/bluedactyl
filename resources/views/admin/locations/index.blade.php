@extends('layouts.admin')

@section('title')
    @lang('admin/locations.title')
@endsection

@section('contentWidth', 'max-w-none')

@section('content-header')
    <h1 class="text-xl font-bold">@lang('admin/locations.header')</h1>
    <p class="text-sm text-muted-foreground">@lang('admin/locations.header_subtitle')</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">@lang('admin/locations.breadcrumb_admin')</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>@lang('admin/locations.breadcrumb_locations')</span>
    </nav>
@endsection

@section('content')
<div class="grid min-w-0 gap-6">
    <div class="col-span-full min-w-0">
        <div class="server-list-card card min-w-0 w-full">
            <header>
                <h3 class="text-lg font-semibold">@lang('admin/locations.location_list')</h3>
                <div class="card-action">
                    <button class="btn" data-size="sm" onclick="document.getElementById('newLocationModal').showModal()">@lang('admin/locations.create_new')</button>
                </div>
            </header>
            <section class="min-w-0">
                <div class="table-container w-full max-w-full">
                    <table class="table w-full min-w-[760px] table-fixed">
                        <thead>
                            <tr>
                                <th class="w-[6%]">@lang('admin/locations.id')</th>
                                <th class="w-[16%]">@lang('admin/locations.short_code')</th>
                                <th class="w-[30%]">@lang('admin/locations.description')</th>
                                <th class="text-center">@lang('admin/locations.memory_alloc')</th>
                                <th class="text-center">@lang('admin/locations.disk_alloc')</th>
                                <th class="text-center">@lang('admin/locations.nodes')</th>
                                <th class="text-center">@lang('admin/locations.servers')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($locations as $location)
                                @php
                                    $memoryColor = $location->memory_percent < 50 ? '#50af51' : ($location->memory_percent < 70 ? '#e0a800' : '#d9534f');
                                    $diskColor = $location->disk_percent < 50 ? '#50af51' : ($location->disk_percent < 70 ? '#e0a800' : '#d9534f');
                                @endphp
                                <tr>
                                    <td><code>{{ $location->id }}</code></td>
                                    <td><a href="{{ route('admin.locations.view', $location->id) }}">{{ $location->short }}</a></td>
                                    <td>{{ $location->long }}</td>
                                    <td class="text-center" style="color: {{ $memoryColor }}" title="@lang('admin/locations.allocated'): {{ humanizeSize($location->allocated_memory * 1024 * 1024) }} / @lang('admin/locations.total'): {{ humanizeSize($location->total_memory * 1024 * 1024) }}">
                                        {{ round($location->memory_percent) }}%
                                    </td>
                                    <td class="text-center" style="color: {{ $diskColor }}" title="@lang('admin/locations.allocated'): {{ humanizeSize($location->allocated_disk * 1024 * 1024) }} / @lang('admin/locations.total'): {{ humanizeSize($location->total_disk * 1024 * 1024) }}">
                                        {{ round($location->disk_percent) }}%
                                    </td>
                                    <td class="text-center">{{ $location->nodes_count }}</td>
                                    <td class="text-center">{{ $location->servers_count }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</div>
<dialog class="dialog" id="newLocationModal" tabindex="-1">
    <header>
        <button type="button" class="btn" data-variant="ghost" onclick="this.closest('dialog').close()" aria-label="@lang('admin/locations.close')"><x-icon name="x" class="size-4" /></button>
        <h4 class="text-lg font-semibold">@lang('admin/locations.create_location')</h4>
    </header>
    <form action="{{ route('admin.locations') }}" method="POST" id="createLocationForm">
        <section>
            <div class="grid gap-6">
                <div role="group" class="field">
                    <label for="pShortModal">@lang('admin/locations.short_code')</label>
                    <input type="text" name="short" id="pShortModal" />
                    <p class="text-sm text-muted-foreground">@lang('admin/locations.short_code_help')</p>
                </div>
                <div role="group" class="field">
                    <label for="pLongModal">@lang('admin/locations.description')</label>
                    <textarea name="long" id="pLongModal" rows="4"></textarea>
                    <p class="text-sm text-muted-foreground">@lang('admin/locations.description_help')</p>
                </div>
            </div>
            {!! csrf_field() !!}
        </section>
    </form>
    <footer>
        <button type="button" class="btn" data-size="sm" data-variant="outline" onclick="this.closest('dialog').close()">@lang('admin/locations.cancel')</button>
        <button type="submit" class="btn" data-size="sm" form="createLocationForm">@lang('admin/locations.create')</button>
    </footer>
</dialog>
@endsection
