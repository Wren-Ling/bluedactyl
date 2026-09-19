
@extends('layouts.admin')

@section('title')
    @lang('admin/mounts.title')
@endsection

@section('contentWidth', 'max-w-none')

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
    <div class="grid min-w-0 gap-6">
        <div class="col-span-full min-w-0">
            <div class="server-list-card card min-w-0 w-full">
                <header>
                    <h3 class="text-lg font-semibold">@lang('admin/mounts.mount_list')</h3>
                    <div class="card-action">
                        <button class="btn" data-size="sm" onclick="document.getElementById('newMountModal').showModal()">@lang('admin/mounts.create_new')</button>
                    </div>
                </header>

                <section class="min-w-0">
                    <div class="table-container w-full max-w-full">
                        <table class="table w-full min-w-[760px] table-fixed">
                            <tbody>
                                <tr>
                                    <th class="w-[8%]">@lang('admin/mounts.id')</th>
                                    <th class="w-[20%]">@lang('admin/mounts.name')</th>
                                    <th class="w-[24%]">@lang('admin/mounts.source')</th>
                                    <th class="w-[24%]">@lang('admin/mounts.target')</th>
                                    <th class="w-[8%] text-center">@lang('admin/mounts.eggs')</th>
                                    <th class="w-[8%] text-center">@lang('admin/mounts.nodes')</th>
                                    <th class="w-[8%] text-center">@lang('admin/mounts.servers')</th>
                                </tr>

                                @foreach ($mounts as $mount)
                                    <tr>
                                        <td><code>{{ $mount->id }}</code></td>
                                        <td class="truncate" title="{{ $mount->name }}"><a href="{{ route('admin.mounts.view', $mount->id) }}">{{ $mount->name }}</a></td>
                                        <td class="truncate" title="{{ $mount->source }}"><code>{{ $mount->source }}</code></td>
                                        <td class="truncate" title="{{ $mount->target }}"><code>{{ $mount->target }}</code></td>
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

    <dialog class="dialog" id="newMountModal" tabindex="-1" onclick="if (event.target === this) this.close()">
        <div class="admin-form-dialog sm:max-w-2xl">
            <header>
                <h2 class="text-lg font-semibold">@lang('admin/mounts.create_mount')</h2>
            </header>
            <section>
                <form action="{{ route('admin.mounts') }}" method="POST" id="newMountForm">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
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

                        <div role="group" class="field">
                            <span class="font-medium">@lang('admin/mounts.read_only')</span>
                            <div role="radiogroup" class="flex flex-wrap gap-4">
                                <label for="pReadOnlyFalse" class="flex items-center gap-2 font-normal">
                                    <input type="radio" id="pReadOnlyFalse" name="read_only" value="0" checked>
                                    <span>@lang('admin/mounts.false')</span>
                                </label>
                                <label for="pReadOnly" class="flex items-center gap-2 font-normal">
                                    <input type="radio" id="pReadOnly" name="read_only" value="1">
                                    <span>@lang('admin/mounts.true')</span>
                                </label>
                            </div>
                            <p class="text-sm text-muted-foreground">@lang('admin/mounts.read_only_help')</p>
                        </div>

                        <div role="group" class="field">
                            <span class="font-medium">@lang('admin/mounts.user_mountable')</span>
                            <div role="radiogroup" class="flex flex-wrap gap-4">
                                <label for="pUserMountableFalse" class="flex items-center gap-2 font-normal">
                                    <input type="radio" id="pUserMountableFalse" name="user_mountable" value="0" checked>
                                    <span>@lang('admin/mounts.false')</span>
                                </label>
                                <label for="pUserMountable" class="flex items-center gap-2 font-normal">
                                    <input type="radio" id="pUserMountable" name="user_mountable" value="1">
                                    <span>@lang('admin/mounts.true')</span>
                                </label>
                            </div>
                            <p class="text-sm text-muted-foreground">@lang('admin/mounts.user_mountable_help')</p>
                        </div>
                    </div>
                    {!! csrf_field() !!}
                </form>
            </section>
            <footer>
                <button type="button" class="btn" data-size="sm" data-variant="outline" onclick="this.closest('dialog').close()">@lang('admin/mounts.cancel')</button>
                <button type="submit" class="btn" data-size="sm" form="newMountForm">@lang('admin/mounts.create')</button>
            </footer>
            <button type="button" class="btn" data-variant="ghost" data-size="icon-sm" onclick="this.closest('dialog').close()" aria-label="@lang('admin/mounts.close')"><x-icon name="x" class="size-4" /></button>
        </div>
    </dialog>
@endsection
