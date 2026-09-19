@extends('layouts.admin')

@section('title')
    @lang('admin/nests.nest_view.title') &rarr; {{ $nest->name }}
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">{{ $nest->name }}</h1>
    <p class="text-sm text-muted-foreground">{{ str_limit($nest->description, 50) }}</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">@lang('admin/nests.admin')</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.nests') }}">@lang('admin/nests.nests_breadcrumb')</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>{{ $nest->name }}</span>
    </nav>
@endsection

@section('content')
<div class="admin-responsive-detail">
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <form action="{{ route('admin.nests.view', $nest->id) }}" method="POST">
        <div class="card">
            <section>
                <div role="group" class="field">
                    <label >@lang('admin/nests.nest_view.name_label') <span class="field-required"></span></label>
                    <input type="text" name="name"  value="{{ $nest->name }}" />
                    <p class="text-sm text-muted-foreground">{!! trans('admin/nests.nest_view.name_hint') !!}</p>
                </div>
                <div role="group" class="field">
                    <label >@lang('admin/nests.nest_view.description_label')</label>
                    <textarea name="description"  rows="7">{{ $nest->description }}</textarea>
                </div>
            </section>
            <footer>
                {!! csrf_field() !!}
                <button type="submit" name="_method" value="PATCH" class="btn ml-auto" data-size="sm">@lang('admin/nests.nest_view.save')</button>
                <button id="deleteButton" type="submit" name="_method" value="DELETE" class="btn" data-variant="destructive" data-size="sm"><x-icon name="trash-2" class="size-4" /></button>
            </footer>
        </div>
    </form>
    <div>
        <div class="card">
            <section>
                <div role="group" class="field">
                    <label >@lang('admin/nests.nest_view.nest_id_label')</label>
                        <input type="text" readonly  value="{{ $nest->id }}" />
                        <p class="text-sm text-muted-foreground">{!! trans('admin/nests.nest_view.nest_id_hint') !!}</p>
                    </div>
                    <div role="group" class="field">
                        <label >@lang('admin/nests.nest_view.author_label')</label>
                        <input type="text" readonly  value="{{ $nest->author }}" />
                        <p class="text-sm text-muted-foreground">{!! trans('admin/nests.nest_view.author_hint') !!}</p>
                    </div>
                    <div role="group" class="field">
                        <label >@lang('admin/nests.nest_view.uuid_label')</label>
                        <input type="text" readonly  value="{{ $nest->uuid }}" />
                        <p class="text-sm text-muted-foreground">{!! trans('admin/nests.nest_view.uuid_hint') !!}</p>
                </div>
            </section>
        </div>
    </div>
</div>
<div class="grid min-w-0 gap-6">
    <div class="col-span-full min-w-0">
        <div class="server-list-card card min-w-0 w-full">
            <header>
                <h3 class="text-lg font-semibold">@lang('admin/nests.nest_view.eggs_card_title')</h3>
            </header>
            <section class="table-container no-padding w-full max-w-full">
                <table class="table table-fixed w-full min-w-[760px]">
                    <colgroup>
                        <col class="w-[60px]">
                        <col class="w-[180px]">
                        <col>
                        <col class="w-[80px]">
                        <col class="w-[40px]">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>@lang('admin/nests.nest_view.table_id')</th>
                            <th>@lang('admin/nests.nest_view.table_name')</th>
                            <th>@lang('admin/nests.nest_view.table_description')</th>
                            <th class="text-center">@lang('admin/nests.nest_view.table_servers')</th>
                            <th class="text-center"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($nest->eggs as $egg)
                        <tr>
                            <td class="align-middle"><code>{{ $egg->id }}</code></td>
                            <td class="align-middle truncate"><a href="{{ route('admin.nests.egg.view', $egg->id) }}" title="{{ $egg->name }} ({{ $egg->author }})">{{ $egg->name }}</a></td>
                            <td class="align-middle break-words"><span class="line-clamp-2" title="{{ $egg->description }}">{{ $egg->description }}</span></td>
                            <td class="text-center align-middle"><code>{{ $egg->servers->count() }}</code></td>
                            <td class="align-middle">
                                <a href="{{ route('admin.nests.egg.export', ['egg' => $egg->id]) }}"><x-icon name="download" class="size-4" /></a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </section>
            <footer>
                <a href="{{ route('admin.nests.egg.new') }}"><button class="btn ml-auto" data-size="sm">@lang('admin/nests.nest_view.new_egg')</button></a>
            </footer>
        </div>
    </div>
</div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>
        $('#deleteButton').on('mouseenter', function (event) {
            $(this).find('i').html('{{ trans("admin.nests.nest_view.delete_nest") }}');
        }).on('mouseleave', function (event) {
            $(this).find('i').html('');
        });
    </script>
@endsection
