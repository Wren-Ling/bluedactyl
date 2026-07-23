
@extends('layouts.admin')

@section('title')
    Mounts
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">Mounts</h1>
    <p class="text-sm text-muted-foreground">Configure and manage additional mount points for servers.</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">Admin</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>Mounts</span>
    </nav>
@endsection

@section('content')
    <div class="grid gap-6">
        <div class="col-span-full">
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">Mount List</h3>
                    <div class="card-action">
                        <button class="btn" data-size="sm" onclick="document.getElementById('newMountModal').showModal()">Create New</button>
                    </div>
                </header>

                <section>
                    <div class="table-container">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Source</th>
                                    <th>Target</th>
                                    <th class="text-center">Eggs</th>
                                    <th class="text-center">Nodes</th>
                                    <th class="text-center">Servers</th>
                                </tr>

                                @foreach ($mounts as $mount)
                                    <tr>
                                        <td><code>{{ $mount->id }}</code></td>
                                        <td><a href="{{ route('admin.mounts.view', $mount->id) }}">{{ $mount->name }}</a></td>
                                        <td><code>{{ $mount->source }}</code></td>
                                        <td><code>{{ $mount->target }}</code></td>
                                        <td class="text-center">{{ $mount->eggs_count }}</td>
                                        <td class="text-center">{{ $mount->nodes_count }}</td>
                                        <td class="text-center">{{ $mount->servers_count }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <dialog class="dialog" id="newMountModal" tabindex="-1">
        <header>
            <button type="button" class="btn" data-variant="ghost" onclick="this.closest('dialog').close()" aria-label="Close">
                <x-icon name="x" class="size-4" />
            </button>
            <h4 class="text-lg font-semibold">Create Mount</h4>
        </header>

        <form action="{{ route('admin.mounts') }}" method="POST" id="newMountForm">
            <section>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div role="group" class="field col-span-full">
                        <label for="pName">Name</label>
                        <input type="text" id="pName" name="name" />
                        <p class="text-sm text-muted-foreground">Unique name used to separate this mount from another.</p>
                    </div>

                    <div role="group" class="field col-span-full">
                        <label for="pDescription">Description</label>
                        <textarea id="pDescription" name="description" rows="4"></textarea>
                        <p class="text-sm text-muted-foreground">A longer description for this mount, must be less than 191 characters.</p>
                    </div>

                    <div role="group" class="field">
                        <label for="pSource">Source</label>
                        <input type="text" id="pSource" name="source" />
                        <p class="text-sm text-muted-foreground">File path on the host system to mount to a container.</p>
                    </div>

                    <div role="group" class="field">
                        <label for="pTarget">Target</label>
                        <input type="text" id="pTarget" name="target" />
                        <p class="text-sm text-muted-foreground">Where the mount will be accessible inside a container.</p>
                    </div>

                    <div role="group" class="field" data-orientation="horizontal">
                        <label>Read Only</label>
                        <input type="radio" id="pReadOnlyFalse" name="read_only" value="0" checked>
                        <label for="pReadOnlyFalse" class="font-normal">False</label>
                        <input type="radio" id="pReadOnly" name="read_only" value="1">
                        <label for="pReadOnly" class="font-normal">True</label>
                        <p class="text-sm text-muted-foreground">Is the mount read only inside the container?</p>
                    </div>

                    <div role="group" class="field" data-orientation="horizontal">
                        <label>User Mountable</label>
                        <input type="radio" id="pUserMountableFalse" name="user_mountable" value="0" checked>
                        <label for="pUserMountableFalse" class="font-normal">False</label>
                        <input type="radio" id="pUserMountable" name="user_mountable" value="1">
                        <label for="pUserMountable" class="font-normal">True</label>
                        <p class="text-sm text-muted-foreground">Should users be able to mount this themselves?</p>
                    </div>
                </div>
            </section>
            {!! csrf_field() !!}
        </form>
        <footer>
            <button type="button" class="btn" data-size="sm" data-variant="outline" onclick="this.closest('dialog').close()">Cancel</button>
            <button type="submit" class="btn" data-size="sm" form="newMountForm">Create</button>
        </footer>
    </dialog>
@endsection