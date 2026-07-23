@extends('layouts.admin')

@section('title')
    Locations &rarr; View &rarr; {{ $location->short }}
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">{{ $location->short }}</h1>
    <p class="text-sm text-muted-foreground">{{ str_limit($location->long, 75) }}</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">Admin</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.locations') }}">Locations</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>{{ $location->short }}</span>
    </nav>
@endsection

@section('content')
@php
    $totalMemory = 0;
    $allocatedMemory = 0;
    $totalDisk = 0;
    $allocatedDisk = 0;

    foreach ($location->nodes as $node) {
        $memoryLimit = $node->memory * (1 + ($node->memory_overallocate / 100));
        $diskLimit = $node->disk * (1 + ($node->disk_overallocate / 100));

        $totalMemory += $memoryLimit;
        $totalDisk += $diskLimit;

        $nodeAllocatedMemory = $node->servers->where('exclude_from_resource_calculation', false)->sum('memory');
        $nodeAllocatedDisk = $node->servers->where('exclude_from_resource_calculation', false)->sum('disk');

        $allocatedMemory += $nodeAllocatedMemory;
        $allocatedDisk += $nodeAllocatedDisk;
    }

    $memoryPercent = $totalMemory > 0 ? ($allocatedMemory / $totalMemory) * 100 : 0;
    $diskPercent = $totalDisk > 0 ? ($allocatedDisk / $totalDisk) * 100 : 0;

    $memoryColor = $memoryPercent < 50 ? '#50af51' : ($memoryPercent < 70 ? '#e0a800' : '#d9534f');
    $diskColor = $diskPercent < 50 ? '#50af51' : ($diskPercent < 70 ? '#e0a800' : '#d9534f');
@endphp
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <div class="card">
            <header>
                <h3 class="text-lg font-semibold">Location Details</h3>
            </header>
            <section>
                <form action="{{ route('admin.locations.view', $location->id) }}" method="POST">
                    <div class="grid gap-6">
                        <div role="group" class="field">
                            <label for="pShort">Short Code</label>
                            <input type="text" id="pShort" name="short"  value="{{ $location->short }}" />
                        </div>
                        <div role="group" class="field">
                            <label for="pLong">Description</label>
                            <textarea id="pLong" name="long"  rows="4">{{ $location->long }}</textarea>
                        </div>
                    </div>
                </form>
            </section>
            <footer>
                {!! csrf_field() !!}
                {!! method_field('PATCH') !!}
                <button name="action" value="edit" class="btn ml-auto" data-size="sm">Save</button>
                <button name="action" value="delete" class="btn mr-auto" data-size="sm" data-variant="destructive"><x-icon name="trash-2" class="size-4" /></button>
            </footer>
        </div>
    </div>
    <div>
        <div class="card" data-variant="outline">
            <header>
                <h3 class="text-lg font-semibold">Resource Allocation</h3>
            </header>
            <section>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4>Memory</h4>
                        <div class="progress h-5">
                            <span style="width: {{ min($memoryPercent, 100) }}%; background-color: {{ $memoryColor }};"></span>
                        </div>
                        <p>
                            <strong>Allocated:</strong> {{ humanizeSize($allocatedMemory * 1024 * 1024) }}<br>
                            <strong>Total:</strong> {{ humanizeSize($totalMemory * 1024 * 1024) }}
                        </p>
                    </div>
                    <div>
                        <h4>Disk</h4>
                        <div class="progress h-5">
                            <span style="width: {{ min($diskPercent, 100) }}%; background-color: {{ $diskColor }};"></span>
                        </div>
                        <p>
                            <strong>Allocated:</strong> {{ humanizeSize($allocatedDisk * 1024 * 1024) }}<br>
                            <strong>Total:</strong> {{ humanizeSize($totalDisk * 1024 * 1024) }}
                        </p>
                    </div>
                </div>
            </section>
        </div>
        <div class="card">
            <header>
                <h3 class="text-lg font-semibold">Nodes</h3>
            </header>
            <section>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>FQDN</th>
                                <th>Memory</th>
                                <th>Disk</th>
                                <th>Servers</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($location->nodes as $node)
                            <tr>
                                <td><code>{{ $node->id }}</code></td>
                                <td><a href="{{ route('admin.nodes.view', $node->id) }}">{{ $node->name }}</a></td>
                                <td><code>{{ $node->fqdn }}</code></td>
                                @php
                                    $nodeMemoryLimit = $node->memory * (1 + ($node->memory_overallocate / 100));
                                    $nodeAllocatedMemory = $node->servers->where('exclude_from_resource_calculation', false)->sum('memory');
                                    $nodeMemoryPercent = $nodeMemoryLimit > 0 ? ($nodeAllocatedMemory / $nodeMemoryLimit) * 100 : 0;

                                    $nodeDiskLimit = $node->disk * (1 + ($node->disk_overallocate / 100));
                                    $nodeAllocatedDisk = $node->servers->where('exclude_from_resource_calculation', false)->sum('disk');
                                    $nodeDiskPercent = $nodeDiskLimit > 0 ? ($nodeAllocatedDisk / $nodeDiskLimit) * 100 : 0;

                                    $nodeMemoryColor = $nodeMemoryPercent < 50 ? '#50af51' : ($nodeMemoryPercent < 70 ? '#e0a800' : '#d9534f');
                                    $nodeDiskColor = $nodeDiskPercent < 50 ? '#50af51' : ($nodeDiskPercent < 70 ? '#e0a800' : '#d9534f');
                                @endphp
                                <td style="color: {{ $nodeMemoryColor }}">{{ round($nodeMemoryPercent) }}%</td>
                                <td style="color: {{ $nodeDiskColor }}">{{ round($nodeDiskPercent) }}%</td>
                                <td>{{ $node->servers->count() }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
