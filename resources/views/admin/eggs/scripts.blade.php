@extends('layouts.admin')

@section('contentWidth', 'max-w-6xl')

@section('title')
    @lang('admin/nests.egg_scripts.page_title', ['name' => $egg->name])
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">{{ $egg->name }}</h1>
    <p class="text-sm text-muted-foreground">@lang('admin/nests.egg_scripts.header_description')</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">@lang('admin/nests.admin')</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.nests') }}">@lang('admin/nests.nests_breadcrumb')</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.nests.view', $egg->nest->id) }}">{{ $egg->nest->name }}</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.nests.egg.view', $egg->id) }}">{{ $egg->name }}</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>{{ $egg->name }}</span>
    </nav>
@endsection

@section('content')
<div class="grid gap-6">
    <div class="col-span-full">
        <div class="tabs" data-variant="line">
            <nav role="tablist">
                <a href="{{ route('admin.nests.egg.view', $egg->id) }}" role="tab">@lang('admin/nests.egg_scripts.tab_configuration')</a>
                <a href="{{ route('admin.nests.egg.variables', $egg->id) }}" role="tab">@lang('admin/nests.egg_scripts.tab_variables')</a>
                <a href="{{ route('admin.nests.egg.scripts', $egg->id) }}" role="tab" data-active="true">@lang('admin/nests.egg_scripts.tab_install_script')</a>
            </nav>
        </div>
    </div>
</div>
<form action="{{ route('admin.nests.egg.scripts', $egg->id) }}" method="POST">
    <div class="grid gap-6">
        <div class="col-span-full">
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">@lang('admin/nests.egg_scripts.card_title')</h3>
                </header>
                @if(! is_null($egg->copyFrom))
                    <section>
                        <div class="alert" data-variant="warning" role="alert">
                            {!! trans('admin/nests.egg_scripts.copy_alert', ['url' => route('admin.nests.egg.view', $egg->copyFrom->id), 'name' => $egg->copyFrom->name]) !!}
                        </div>
                    </section>
                @endif
                <section class="no-padding">
                    <div id="editor_install" class="h-[300px]">{{ $egg->script_install }}</div>
                </section>
                <section>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div role="group" class="field">
                            <label >@lang('admin/nests.egg_scripts.copy_from_label')</label>
                            <select id="pCopyScriptFrom" name="copy_script_from" class="select">
                                <option value="">@lang('admin/nests.egg_scripts.copy_from_none')</option>
                                @foreach($copyFromOptions as $opt)
                                    <option value="{{ $opt->id }}" {{ $egg->copy_script_from !== $opt->id ?: 'selected' }}>{{ $opt->name }}</option>
                                @endforeach
                            </select>
                            <p class="text-sm text-muted-foreground">{!! trans('admin/nests.egg_scripts.copy_from_hint') !!}</p>
                        </div>
                        <div role="group" class="field">
                            <label >@lang('admin/nests.egg_scripts.script_container_label')</label>
                            <input type="text" name="script_container"  value="{{ $egg->script_container }}" />
                            <p class="text-sm text-muted-foreground">{!! trans('admin/nests.egg_scripts.script_container_hint') !!}</p>
                        </div>
                        <div role="group" class="field">
                            <label >@lang('admin/nests.egg_scripts.script_entrypoint_label')</label>
                            <input type="text" name="script_entry"  value="{{ $egg->script_entry }}" />
                            <p class="text-sm text-muted-foreground">{!! trans('admin/nests.egg_scripts.script_entrypoint_hint') !!}</p>
                        </div>
                    </div>
                    <div class="grid gap-6">
                        <div class="text-sm text-muted-foreground col-span-full">
                            @lang('admin/nests.egg_scripts.rely_on_script')
                            @if(count($relyOnScript) > 0)
                                @foreach($relyOnScript as $rely)
                                    <a href="{{ route('admin.nests.egg.view', $rely->id) }}">
                                        <code>{{ $rely->name }}</code>@if(!$loop->last),&nbsp;@endif
                                    </a>
                                @endforeach
                            @else
                                <em>@lang('admin/nests.egg_scripts.none')</em>
                            @endif
                        </div>
                    </div>
                </section>
                <footer>
                    {!! csrf_field() !!}
                    <textarea name="script_install" class="hidden"></textarea>
                    <button type="submit" name="_method" value="PATCH" class="btn ml-auto" data-size="sm">@lang('admin/nests.egg_scripts.save')</button>
                </footer>
            </div>
        </div>
    </div>
</form>
@endsection

@section('footer-scripts')
    @parent
    {!! Theme::js('vendor/ace/ace.js') !!}
    {!! Theme::js('vendor/ace/ext-modelist.js') !!}
    <script>
    $(document).ready(function () {
        const InstallEditor = ace.edit('editor_install');
        const Modelist = ace.require('ace/ext/modelist')

        InstallEditor.setTheme('ace/theme/chrome');
        InstallEditor.getSession().setMode('ace/mode/sh');
        InstallEditor.getSession().setUseWrapMode(true);
        InstallEditor.setShowPrintMargin(false);

        $('form').on('submit', function (e) {
            $('textarea[name="script_install"]').val(InstallEditor.getValue());
        });
    });
    </script>

@endsection
