
@extends('layouts.admin')

@section('title')
    @lang('admin/mounts.breadcrumb_view', ['name' => $mount->id])
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">{{ $mount->name }}</h1>
    <p class="text-sm text-muted-foreground">{{ str_limit($mount->description, 75) }}</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">@lang('admin/mounts.breadcrumb_admin')</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.mounts') }}">@lang('admin/mounts.breadcrumb_mounts')</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>{{ $mount->name }}</span>
    </nav>
@endsection

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">@lang('admin/mounts.mount_details')</h3>
                </header>

                    <section>
                        <form action="{{ route('admin.mounts.view', $mount->id) }}" method="POST">
                            <div class="grid gap-6">
                                <div role="group" class="field">
                                    <label for="PUniqueID">@lang('admin/mounts.unique_id')</label>
                                    <input type="text" id="PUniqueID"  value="{{ $mount->uuid }}" disabled />
                                </div>

                                <div role="group" class="field">
                                    <label for="pName">@lang('admin/mounts.name')</label>
                                    <input type="text" id="pName" name="name"  value="{{ $mount->name }}" />
                                </div>

                                <div role="group" class="field">
                                    <label for="pDescription">@lang('admin/mounts.description')</label>
                                    <textarea id="pDescription" name="description"  rows="4">{{ $mount->description }}</textarea>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div role="group" class="field">
                                    <label for="pSource">@lang('admin/mounts.source')</label>
                                    <input type="text" id="pSource" name="source"  value="{{ $mount->source }}" />
                                </div>

                                <div role="group" class="field">
                                    <label for="pTarget">@lang('admin/mounts.target')</label>
                                    <input type="text" id="pTarget" name="target"  value="{{ $mount->target }}" />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div role="group" class="field">
                                    <p class="text-sm font-semibold mb-2">@lang('admin/mounts.read_only')</p>
                                    <div role="group" class="field" data-orientation="horizontal">
                                        <input type="radio" id="pReadOnlyFalse" name="read_only" value="0" @if(!$mount->read_only) checked @endif>
                                        <label for="pReadOnlyFalse" class="font-normal">@lang('admin/mounts.false')</label>
                                    </div>
                                    <div role="group" class="field" data-orientation="horizontal">
                                        <input type="radio" id="pReadOnly" name="read_only" value="1" @if($mount->read_only) checked @endif>
                                        <label for="pReadOnly" class="font-normal">@lang('admin/mounts.true')</label>
                                    </div>
                                </div>

                                <div role="group" class="field">
                                    <p class="text-sm font-semibold mb-2">@lang('admin/mounts.user_mountable')</p>
                                    <div role="group" class="field" data-orientation="horizontal">
                                        <input type="radio" id="pUserMountableFalse" name="user_mountable" value="0" @if(!$mount->user_mountable) checked @endif>
                                        <label for="pUserMountableFalse" class="font-normal">@lang('admin/mounts.false')</label>
                                    </div>
                                    <div role="group" class="field" data-orientation="horizontal">
                                        <input type="radio" id="pUserMountable" name="user_mountable" value="1" @if($mount->user_mountable) checked @endif>
                                        <label for="pUserMountable" class="font-normal">@lang('admin/mounts.true')</label>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </section>

                    <footer>
                        {!! csrf_field() !!}
                        {!! method_field('PATCH') !!}

                        <button name="action" value="edit" class="btn ml-auto" data-size="sm">@lang('admin/mounts.save')</button>
                        <button name="action" value="delete" class="btn mr-auto" data-size="sm" data-variant="destructive"><x-icon name="trash-2" class="size-4" /></button>
                    </footer>
            </div>
        </div>

        <div>
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">@lang('admin/mounts.eggs_section')</h3>
                    <div class="card-action">
                        <button class="btn" data-size="sm" onclick="document.getElementById('addEggsModal').showModal()">@lang('admin/mounts.add_eggs')</button>
                    </div>
                </header>

                <section>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>@lang('admin/mounts.id')</th>
                                    <th>@lang('admin/mounts.name')</th>
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
                    <h3 class="text-lg font-semibold">@lang('admin/mounts.nodes_section')</h3>
                    <div class="card-action">
                        <button class="btn" data-size="sm" onclick="document.getElementById('addNodesModal').showModal()">@lang('admin/mounts.add_nodes')</button>
                    </div>
                </header>

                <section>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>@lang('admin/mounts.id')</th>
                                    <th>@lang('admin/mounts.name')</th>
                                    <th>@lang('admin/mounts.fqdn')</th>
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

    <dialog class="dialog" id="addEggsModal" onclick="if (event.target === this) this.close()">
        <div class="admin-form-dialog sm:max-w-lg">
            <header>
                <h3 class="text-lg font-semibold">@lang('admin/mounts.add_eggs_title')</h3>
            </header>
            <section>
                <form action="{{ route('admin.mounts.eggs', $mount->id) }}" method="POST" id="addEggsForm">
                    <div class="grid gap-6">
                        <div role="group" class="field">
                            <label for="pEggs">@lang('admin/mounts.add_eggs_label')</label>
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
                </form>
            </section>
            <footer>
                <button type="button" class="btn" data-size="sm" data-variant="outline" onclick="this.closest('dialog').close()">@lang('admin/mounts.cancel')</button>
                <button type="submit" class="btn" data-size="sm" form="addEggsForm">@lang('admin/mounts.add')</button>
            </footer>
            <button type="button" class="btn" data-variant="ghost" data-size="icon-sm" onclick="this.closest('dialog').close()" aria-label="@lang('admin/mounts.close')"><x-icon name="x" class="size-4" /></button>
        </div>
    </dialog>

    <dialog class="dialog" id="addNodesModal" onclick="if (event.target === this) this.close()">
        <div class="admin-form-dialog sm:max-w-lg">
            <header>
                <h3 class="text-lg font-semibold">@lang('admin/mounts.add_nodes_title')</h3>
            </header>
            <section>
                <form action="{{ route('admin.mounts.nodes', $mount->id) }}" method="POST" id="addNodesForm">
                    <div class="grid gap-6">
                        <div role="group" class="field">
                            <label for="pNodes">@lang('admin/mounts.add_nodes_label')</label>
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
                </form>
            </section>
            <footer>
                <button type="button" class="btn" data-size="sm" data-variant="outline" onclick="this.closest('dialog').close()">@lang('admin/mounts.cancel')</button>
                <button type="submit" class="btn" data-size="sm" form="addNodesForm">@lang('admin/mounts.add')</button>
            </footer>
            <button type="button" class="btn" data-variant="ghost" data-size="icon-sm" onclick="this.closest('dialog').close()" aria-label="@lang('admin/mounts.close')"><x-icon name="x" class="size-4" /></button>
        </div>
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
                    alert('{{ trans('admin/mounts.egg_detached') }}');
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
                    alert('{{ trans('admin/mounts.node_detached') }}');
                }).fail(function (jqXHR) {
                    console.error(jqXHR);
                    alert(jqXHR.responseJSON.error);
                });
            });
        });
    </script>
@endsection
