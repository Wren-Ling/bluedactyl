
@extends('layouts.admin')

@section('title')
    @lang('admin/mounts.title')
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">@lang('admin/mounts.header')</h1>
    <p class="text-sm text-muted-foreground">@lang('admin/mounts.header_subtitle')</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">@lang('admin/mounts.breadcrumb_admin')</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>@lang('admin/mounts.breadcrumb_mounts')</span>
    </nav>
@endsection

@section('content')
    <div class="grid gap-6">
        <div class="col-span-full">
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">@lang('admin/mounts.mount_list')</h3>
                    <div class="card-action">
                        <button class="btn" data-size="sm" onclick="document.getElementById('newMountModal').showModal()">@lang('admin/mounts.create_new')</button>
                    </div>
                </header>

                <section>
                    <div class="table-container">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th>@lang('admin/mounts.id')</th>
                                    <th>@lang('admin/mounts.name')</th>
                                    <th>@lang('admin/mounts.source')</th>
                                    <th>@lang('admin/mounts.target')</th>
                                    <th class="text-center">@lang('admin/mounts.eggs')</th>
                                    <th class="text-center">@lang('admin/mounts.nodes')</th>
                                    <th class="text-center">@lang('admin/mounts.servers')</th>
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
            <button type="button" class="btn" data-variant="ghost" onclick="this.closest('dialog').close()" aria-label="@lang('admin/mounts.close')">
                <x-icon name="x" class="size-4" />
            </button>
            <h4 class="text-lg font-semibold">@lang('admin/mounts.create_mount')</h4>
        </header>

        <form action="{{ route('admin.mounts') }}" method="POST" id="newMountForm">
            <section>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div role="group" class="field col-span-full">
                        <label for="pName">@lang('admin/mounts.name')</label>
                        <input type="text" id="pName" name="name" />
                        <p class="text-sm text-muted-foreground">@lang('admin/mounts.name_help')</p>
                    </div>

                    <div role="group" class="field col-span-full">
                        <label for="pDescription">@lang('admin/mounts.description')</label>
                        <textarea id="pDescription" name="description" rows="4"></textarea>
                        <p class="text-sm text-muted-foreground">@lang('admin/mounts.description_help')</p>
                    </div>

                    <div role="group" class="field">
                        <label for="pSource">@lang('admin/mounts.source')</label>
                        <input type="text" id="pSource" name="source" />
                        <p class="text-sm text-muted-foreground">@lang('admin/mounts.source_help')</p>
                    </div>

                    <div role="group" class="field">
                        <label for="pTarget">@lang('admin/mounts.target')</label>
                        <input type="text" id="pTarget" name="target" />
                        <p class="text-sm text-muted-foreground">@lang('admin/mounts.target_help')</p>
                    </div>

                    <div role="group" class="field" data-orientation="horizontal">
                        <label>@lang('admin/mounts.read_only')</label>
                        <input type="radio" id="pReadOnlyFalse" name="read_only" value="0" checked>
                        <label for="pReadOnlyFalse" class="font-normal">@lang('admin/mounts.false')</label>
                        <input type="radio" id="pReadOnly" name="read_only" value="1">
                        <label for="pReadOnly" class="font-normal">@lang('admin/mounts.true')</label>
                        <p class="text-sm text-muted-foreground">@lang('admin/mounts.read_only_help')</p>
                    </div>

                    <div role="group" class="field" data-orientation="horizontal">
                        <label>@lang('admin/mounts.user_mountable')</label>
                        <input type="radio" id="pUserMountableFalse" name="user_mountable" value="0" checked>
                        <label for="pUserMountableFalse" class="font-normal">@lang('admin/mounts.false')</label>
                        <input type="radio" id="pUserMountable" name="user_mountable" value="1">
                        <label for="pUserMountable" class="font-normal">@lang('admin/mounts.true')</label>
                        <p class="text-sm text-muted-foreground">@lang('admin/mounts.user_mountable_help')</p>
                    </div>
                </div>
            </section>
            {!! csrf_field() !!}
        </form>
        <footer>
            <button type="button" class="btn" data-size="sm" data-variant="outline" onclick="this.closest('dialog').close()">@lang('admin/mounts.cancel')</button>
            <button type="submit" class="btn" data-size="sm" form="newMountForm">@lang('admin/mounts.create')</button>
        </footer>
    </dialog>
@endsection