@extends('layouts.admin')

@section('title')
    Locations
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">Locations</h1>
    <p class="text-sm text-muted-foreground">All locations that nodes can be assigned to for easier categorization.</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">Admin</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>Locations</span>
    </nav>
@endsection

@section('content')
<div class="grid gap-6">
    <div class="col-span-full">
        <div class="card">
            <header>
                <h3 class="text-lg font-semibold">Location List</h3>
                <div class="card-action">
                    <button class="btn" data-size="sm" onclick="document.getElementById('newLocationModal').showModal()">Create New</button>
                </div>
            </header>
            <section>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Short Code</th>
                                <th>Description</th>
                                <th class="text-center">Memory Alloc%</th>
                                <th class="text-center">Disk Alloc%</th>
                                <th class="text-center">Nodes</th>
                                <th class="text-center">Servers</th>
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
                                    <td class="text-center" style="color: {{ $memoryColor }}" title="Allocated: {{ humanizeSize($location->allocated_memory * 1024 * 1024) }} / Total: {{ humanizeSize($location->total_memory * 1024 * 1024) }}">
                                        {{ round($location->memory_percent) }}%
                                    </td>
                                    <td class="text-center" style="color: {{ $diskColor }}" title="Allocated: {{ humanizeSize($location->allocated_disk * 1024 * 1024) }} / Total: {{ humanizeSize($location->total_disk * 1024 * 1024) }}">
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
        <button type="button" class="btn" data-variant="ghost" onclick="this.closest('dialog').close()" aria-label="Close"><x-icon name="x" class="size-4" /></button>
        <h4 class="text-lg font-semibold">Create Location</h4>
    </header>
    <form action="{{ route('admin.locations') }}" method="POST" id="createLocationForm">
        <section>
            <div class="grid gap-6">
                <div role="group" class="field">
                    <label for="pShortModal">Short Code</label>
                    <input type="text" name="short" id="pShortModal" />
                    <p class="text-sm text-muted-foreground">A short identifier used to distinguish this location from others. Must be between 1 and 60 characters, for example, <code>us.nyc.lvl3</code>.</p>
                </div>
                <div role="group" class="field">
                    <label for="pLongModal">Description</label>
                    <textarea name="long" id="pLongModal" rows="4"></textarea>
                    <p class="text-sm text-muted-foreground">A longer description of this location. Must be less than 191 characters.</p>
                </div>
            </div>
            {!! csrf_field() !!}
        </section>
    </form>
    <footer>
        <button type="button" class="btn" data-size="sm" data-variant="outline" onclick="this.closest('dialog').close()">Cancel</button>
        <button type="submit" class="btn" data-size="sm" form="createLocationForm">Create</button>
    </footer>
</dialog>
@endsection
