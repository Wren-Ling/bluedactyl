@extends('layouts.admin')

@section('title')
    @lang('admin/nests.nest_list.title')
@endsection

@section('contentWidth', 'max-w-none')

@section('content-header')
    <h1 class="text-xl font-bold">@lang('admin/nests.nest_list.title')</h1>
    <p class="text-sm text-muted-foreground">@lang('admin/nests.nest_list.header_description')</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">@lang('admin/nests.admin')</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>@lang('admin/nests.nests_breadcrumb')</span>
    </nav>
@endsection

@section('content')
<div class="grid min-w-0 gap-6">
    <div class="col-span-full min-w-0">
        <div class="alert" data-variant="destructive" role="alert">
            {!! trans('admin/nests.nest_list.alert') !!}
        </div>
    </div>
    <div class="col-span-full min-w-0">
        <div class="server-list-card card min-w-0 w-full">
            <header>
                <h3 class="text-lg font-semibold">@lang('admin/nests.nest_list.card_title')</h3>
                <div class="card-action">
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="#" class="btn" data-size="sm" onclick="document.getElementById('importServiceOptionModal').showModal()" role="button"><x-icon name="upload" class="size-4" /> @lang('admin/nests.nest_list.import_egg')</a>
                        <a href="#" class="btn" data-size="sm" onclick="document.getElementById('importServiceOptionFromUrlModal').showModal()" role="button"><x-icon name="upload" class="size-4" /> @lang('admin/nests.nest_list.import_egg_from_url')</a>
                        <a href="{{ route('admin.nests.new') }}" class="btn" data-size="sm">@lang('admin/nests.nest_list.create_new')</a>
                    </div>
                </div>
            </header>
            <section class="min-w-0">
                <div class="table-container w-full max-w-full">
                    <table class="table w-full min-w-[760px] table-fixed">
                        <thead>
                            <tr>
                             <th class="w-[8%]">@lang('admin/nests.nest_list.table_id')</th>
                             <th class="w-[22%]">@lang('admin/nests.nest_list.table_name')</th>
                             <th class="w-[50%]">@lang('admin/nests.nest_list.table_description')</th>
                             <th class="w-[10%] text-center">@lang('admin/nests.nest_list.table_eggs')</th>
                             <th class="w-[10%] text-center">@lang('admin/nests.nest_list.table_servers')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($nests as $nest)
                            <tr>
                                <td class="middle"><code>{{ $nest->id }}</code></td>
                                <td class="middle truncate"><a href="{{ route('admin.nests.view', $nest->id) }}" title="{{ $nest->name }} ({{ $nest->author }})">{{ $nest->name }}</a></td>
                                <td class="middle break-words">{{ $nest->description }}</td>
                                <td class="text-center middle">{{ $nest->eggs_count }}</td>
                                <td class="text-center middle">{{ $nest->servers_count }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</div>
<dialog class="dialog" id="importServiceOptionModal" onclick="if (event.target === this) this.close()">
    <div class="sm:max-w-sm">
        <header>
            <h4 class="text-lg font-semibold">@lang('admin/nests.nest_list.import_modal_title')</h4>
        </header>
        <form action="{{ route('admin.nests.egg.import') }}" enctype="multipart/form-data" method="POST" id="importEggForm">
            <section>
                <div role="group" class="field">
                    <label class="font-medium" for="pImportFile">@lang('admin/nests.nest_list.import_file_label') <span class="field-required"></span></label>
                    <input id="pImportFile" type="file" name="import_file" accept="application/json" />
                    <p class="text-sm text-muted-foreground">{!! trans('admin/nests.nest_list.import_file_hint') !!}</p>
                </div>
                <div role="group" class="field">
                    <label class="font-medium" for="pImportToNest">@lang('admin/nests.nest_list.import_nest_label') <span class="field-required"></span></label>
                    <select id="pImportToNest" name="import_to_nest" class="select">
                        @foreach($nests as $nest)
                            <option value="{{ $nest->id }}">{{ $nest->name }} &lt;{{ $nest->author }}&gt;</option>
                        @endforeach
                    </select>
                    <p class="text-sm text-muted-foreground">{!! trans('admin/nests.nest_list.import_nest_hint') !!}</p>
                </div>
                {{ csrf_field() }}
            </section>
        </form>
        <footer>
            <button type="button" class="btn" data-variant="outline" onclick="this.closest('dialog').close()">@lang('admin/nests.nest_list.cancel')</button>
            <button type="submit" class="btn" form="importEggForm">@lang('admin/nests.nest_list.import')</button>
        </footer>
        <button type="button" class="btn" data-variant="ghost" data-size="icon-sm" aria-label="@lang('admin/nests.nest_list.close')" onclick="this.closest('dialog').close()"><x-icon name="x" class="size-4" /></button>
    </div>
</dialog>
<dialog class="dialog" id="importServiceOptionFromUrlModal" onclick="if (event.target === this) this.close()">
    <div class="sm:max-w-sm">
        <header>
            <h4 class="text-lg font-semibold">@lang('admin/nests.nest_list.import_modal_title')</h4>
        </header>
        <form action="{{ route('admin.nests.egg.import_url') }}" method="POST" id="importEggUrlForm">
            <section>
                <div role="group" class="field">
                    <label class="font-medium" for="pImportUrl">@lang('admin/nests.nest_list.import_url_label') <span class="field-required"></span></label>
                    <input id="pImportUrl" type="url" name="import_file_url" />
                    <p class="text-sm text-muted-foreground">{!! trans('admin/nests.nest_list.import_url_hint') !!}</p>
                </div>
                <div role="group" class="field">
                    <label class="font-medium" for="pImportToNestUrl">@lang('admin/nests.nest_list.import_nest_label') <span class="field-required"></span></label>
                    <select id="pImportToNestUrl" name="import_to_nest" class="select">
                        @foreach($nests as $nest)
                            <option value="{{ $nest->id }}">{{ $nest->name }} &lt;{{ $nest->author }}&gt;</option>
                        @endforeach
                    </select>
                    <p class="text-sm text-muted-foreground">{!! trans('admin/nests.nest_list.import_nest_hint') !!}</p>
                </div>
                {{ csrf_field() }}
            </section>
        </form>
        <footer>
            <button type="button" class="btn" data-variant="outline" onclick="this.closest('dialog').close()">@lang('admin/nests.nest_list.cancel')</button>
            <button type="submit" class="btn" form="importEggUrlForm">@lang('admin/nests.nest_list.import')</button>
        </footer>
        <button type="button" class="btn" data-variant="ghost" data-size="icon-sm" aria-label="@lang('admin/nests.nest_list.close')" onclick="this.closest('dialog').close()"><x-icon name="x" class="size-4" /></button>
    </div>
</dialog>
@endsection
