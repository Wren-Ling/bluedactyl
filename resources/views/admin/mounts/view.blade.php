
@extends('layouts.admin')

@section('title')
    Mounts &rarr; View &rarr; {{ $mount->id }}
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">{{ $mount->name }}</h1>
    <p class="text-sm text-muted-foreground">{{ str_limit($mount->description, 75) }}</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">Admin</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.mounts') }}">Mounts</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>{{ $mount->name }}</span>
    </nav>
@endsection

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">Mount Details</h3>
                </header>

                    <section>
                        <form action="{{ route('admin.mounts.view', $mount->id) }}" method="POST">
                            <div class="grid gap-6">
                                <div role="group" class="field">
                                    <label for="PUniqueID">Unique ID</label>
                                    <input type="text" id="PUniqueID"  value="{{ $mount->uuid }}" disabled />
                                </div>

                                <div role="group" class="field">
                                    <label for="pName">Name</label>
                                    <input type="text" id="pName" name="name"  value="{{ $mount->name }}" />
                                </div>

                                <div role="group" class="field">
                                    <label for="pDescription">Description</label>
                                    <textarea id="pDescription" name="description"  rows="4">{{ $mount->description }}</textarea>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div role="group" class="field">
                                    <label for="pSource">Source</label>
                                    <input type="text" id="pSource" name="source"  value="{{ $mount->source }}" />
                                </div>

                                <div role="group" class="field">
                                    <label for="pTarget">Target</label>
                                    <input type="text" id="pTarget" name="target"  value="{{ $mount->target }}" />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div role="group" class="field">
                                    <p class="text-sm font-semibold mb-2">Read Only</p>
                                    <div role="group" class="field" data-orientation="horizontal">
                                        <input type="radio" id="pReadOnlyFalse" name="read_only" value="0" @if(!$mount->read_only) checked @endif>
                                        <label for="pReadOnlyFalse" class="font-normal">False</label>
                                    </div>
                                    <div role="group" class="field" data-orientation="horizontal">
                                        <input type="radio" id="pReadOnly" name="read_only" value="1" @if($mount->read_only) checked @endif>
                                        <label for="pReadOnly" class="font-normal">True</label>
                                    </div>
                                </div>

                                <div role="group" class="field">
                                    <p class="text-sm font-semibold mb-2">User Mountable</p>
                                    <div role="group" class="field" data-orientation="horizontal">
                                        <input type="radio" id="pUserMountableFalse" name="user_mountable" value="0" @if(!$mount->user_mountable) checked @endif>
                                        <label for="pUserMountableFalse" class="font-normal">False</label>
                                    </div>
                                    <div role="group" class="field" data-orientation="horizontal">
                                        <input type="radio" id="pUserMountable" name="user_mountable" value="1" @if($mount->user_mountable) checked @endif>
                                        <label for="pUserMountable" class="font-normal">True</label>
                                    </div>
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
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">Eggs</h3>
                    <div class="card-action">
                        <button class="btn" data-size="sm" onclick="document.getElementById('addEggsModal').showModal()">Add Eggs</button>
                    </div>
                </header>

                <section>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ($mount->eggs as $egg)
                                <tr>
                                    <td class="sm:w-1/6 middle"><code>{{ $egg->id }}</code></td>
                                    <td class="middle"><a href="{{ route('admin.nests.egg.view', $egg->id) }}">{{ $egg->name }}</a></td>
                                    <td class="sm:w-1/12 middle">
                                        <button data-action="detach-egg" data-id="{{ $egg->id }}" class="btn" data-size="sm" data-variant="destructive"><x-icon name="trash-2" class="size-4" /></button>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">Nodes</h3>
                    <div class="card-action">
                        <button class="btn" data-size="sm" onclick="document.getElementById('addNodesModal').showModal()">Add Nodes</button>
                    </div>
                </header>

                <section>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>FQDN</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ($mount->nodes as $node)
                                <tr>
                                    <td class="sm:w-1/6 middle"><code>{{ $node->id }}</code></td>
                                    <td class="middle"><a href="{{ route('admin.nodes.view', $node->id) }}">{{ $node->name }}</a></td>
                                    <td class="middle"><code>{{ $node->fqdn }}</code></td>
                                    <td class="sm:w-1/12 middle">
                                        <button data-action="detach-node" data-id="{{ $node->id }}" class="btn" data-size="sm" data-variant="destructive"><x-icon name="trash-2" class="size-4" /></button>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <dialog class="dialog" id="addEggsModal">
        <header>
            <button type="button" class="btn" onclick="this.closest('dialog').close()" aria-label="Close" data-variant="outline">
                <x-icon name="x" class="size-4" />
            </button>

            <h3 class="text-lg font-semibold">Add Eggs</h3>
        </header>

        <form action="{{ route('admin.mounts.eggs', $mount->id) }}" method="POST" id="addEggsForm">
            <section>
                <div class="grid gap-6">
                    <div role="group" class="field">
                        <label for="pEggs">Eggs</label>
                        <select id="pEggs" name="eggs[]" class="select" multiple>
                            @foreach ($nests as $nest)
                                <optgroup label="{{ $nest->name }}">
                                    @foreach ($nest->eggs as $egg)

                                        @if (! in_array($egg->id, $mount->eggs->pluck('id')->toArray()))
                                            <option value="{{ $egg->id }}">{{ $egg->name }}</option>
                                        @endif

                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                </div>
                {!! csrf_field() !!}
            </section>
        </form>

        <footer>
            <button type="button" class="btn mr-auto" data-size="sm" data-variant="outline" onclick="this.closest('dialog').close()">Cancel</button>
            <button type="submit" class="btn" data-size="sm" form="addEggsForm">Add</button>
        </footer>
    </dialog>

    <dialog class="dialog" id="addNodesModal">
        <header>
            <button type="button" class="btn" onclick="this.closest('dialog').close()" aria-label="Close" data-variant="outline">
                <x-icon name="x" class="size-4" />
            </button>

            <h3 class="text-lg font-semibold">Add Nodes</h3>
        </header>

        <form action="{{ route('admin.mounts.nodes', $mount->id) }}" method="POST" id="addNodesForm">
            <section>
                <div class="grid gap-6">
                    <div role="group" class="field">
                        <label for="pNodes">Nodes</label>
                        <select id="pNodes" name="nodes[]" class="select" multiple>
                            @foreach ($locations as $location)
                                <optgroup label="{{ $location->long }} ({{ $location->short }})">
                                    @foreach ($location->nodes as $node)

                                        @if (! in_array($node->id, $mount->nodes->pluck('id')->toArray()))
                                            <option value="{{ $node->id }}">{{ $node->name }}</option>
                                        @endif

                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                </div>
                {!! csrf_field() !!}
            </section>
        </form>

        <footer>
            <button type="button" class="btn mr-auto" data-size="sm" data-variant="outline" onclick="this.closest('dialog').close()">Cancel</button>
            <button type="submit" class="btn" data-size="sm" form="addNodesForm">Add</button>
        </footer>
    </dialog>
@endsection

@section('footer-scripts')
    @parent

    <script>
        $(document).ready(function() {
            $('button[data-action="detach-egg"]').click(function (event) {
                event.preventDefault();

                const element = $(this);
                const eggId = $(this).data('id');

                $.ajax({
                    method: 'DELETE',
                    url: '/admin/mounts/' + {{ $mount->id }} + '/eggs/' + eggId,
                    headers: { 'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content') },
                }).done(function () {
                    element.parent().parent().className = 'warning';
                    setTimeout(function() {
                        element.parent().parent().style.display = 'none';
                    }, 100);
                    alert('Egg detached.');
                }).fail(function (jqXHR) {
                    console.error(jqXHR);
                    alert(jqXHR.responseJSON.error);
                });
            });

            $('button[data-action="detach-node"]').click(function (event) {
                event.preventDefault();

                const element = $(this);
                const nodeId = $(this).data('id');

                $.ajax({
                    method: 'DELETE',
                    url: '/admin/mounts/' + {{ $mount->id }} + '/nodes/' + nodeId,
                    headers: { 'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content') },
                }).done(function () {
                    element.parent().parent().className = 'warning';
                    setTimeout(function() {
                        element.parent().parent().style.display = 'none';
                    }, 100);
                    alert('Node detached.');
                }).fail(function (jqXHR) {
                    console.error(jqXHR);
                    alert(jqXHR.responseJSON.error);
                });
            });
        });
    </script>
@endsection
