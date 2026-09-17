@extends('layouts.admin')

@section('contentWidth', 'max-w-6xl')

@section('title')
    @lang('admin/nests.egg_view.page_title', ['name' => $egg->name])
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">{{ $egg->name }}</h1>
    <p class="text-sm text-muted-foreground">{{ str_limit($egg->description, 50) }}</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">@lang('admin/nests.admin')</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.nests') }}">@lang('admin/nests.nests_breadcrumb')</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.nests.view', $egg->nest->id) }}">{{ $egg->nest->name }}</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>{{ $egg->name }}</span>
    </nav>
@endsection

@section('content')
<div class="admin-responsive-detail">
<div class="grid gap-6">
    <div class="col-span-full">
        <div class="tabs" data-variant="line">
            <nav role="tablist">
                <a href="{{ route('admin.nests.egg.view', $egg->id) }}" role="tab" @if(Route::currentRouteName() === 'admin.nests.egg.view') data-active="true" @endif>@lang('admin/nests.egg_view.tab_configuration')</a>
                <a href="{{ route('admin.nests.egg.variables', $egg->id) }}" role="tab" @if(Route::currentRouteName() === 'admin.nests.egg.variables') data-active="true" @endif>@lang('admin/nests.egg_view.tab_variables')</a>
                <a href="{{ route('admin.nests.egg.scripts', $egg->id) }}" role="tab" @if(Route::currentRouteName() === 'admin.nests.egg.scripts') data-active="true" @endif>@lang('admin/nests.egg_view.tab_install_script')</a>
            </nav>
        </div>
    </div>
</div>
<form action="{{ route('admin.nests.egg.view', $egg->id) }}" enctype="multipart/form-data" method="POST">
    <div class="grid gap-6">
        <div class="col-span-full">
            <div class="card" data-variant="destructive">
                <section>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-2">
                            <div class="field no-margin-bottom">
                                <label for="pName" >@lang('admin/nests.egg_view.egg_file_label')</label>
                                <div>
                                    <input type="file" name="import_file" class="border-0 max-w-full" />
                                    <p class="text-sm text-muted-foreground no-margin-bottom">{!! trans('admin/nests.egg_view.egg_file_hint') !!}</p>
                                </div>
                            </div>
                        </div>
                        <div class="md:col-span-1">
                            {!! csrf_field() !!}
                            <button type="submit" name="_method" value="PUT" class="btn ml-auto" data-variant="destructive" data-size="sm">@lang('admin/nests.egg_view.update_egg')</button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</form>
<form action="{{ route('admin.nests.egg.view', $egg->id) }}" method="POST">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="space-y-8">
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">@lang('admin/nests.egg_view.configuration_card_title')</h3>
                </header>
                <section class="space-y-6">
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                        <div class="space-y-6">
                            <div role="group" class="field">
                                <label for="pName" >@lang('admin/nests.egg_view.name_label') <span class="field-required"></span></label>
                                <input type="text" id="pName" name="name" value="{{ $egg->name }}"  />
                                <p class="text-sm text-muted-foreground">{!! trans('admin/nests.egg_view.name_hint') !!}</p>
                            </div>
                            <div role="group" class="field">
                                <label for="pUuid" >@lang('admin/nests.egg_view.uuid_label')</label>
                                <input type="text" id="pUuid" readonly value="{{ $egg->uuid }}"  />
                                <p class="text-sm text-muted-foreground">{!! trans('admin/nests.egg_view.uuid_hint') !!}</p>
                            </div>
                            <div role="group" class="field">
                                <label for="pAuthor" >@lang('admin/nests.egg_view.author_label')</label>
                                <input type="text" id="pAuthor" readonly value="{{ $egg->author }}"  />
                                <p class="text-sm text-muted-foreground">{!! trans('admin/nests.egg_view.author_hint') !!}</p>
                            </div>
                            <div role="group" class="field">
                                <label for="pDockerImage" >@lang('admin/nests.egg_view.docker_images_label') <span class="field-required"></span></label>
                                <textarea id="pDockerImages" name="docker_images"  rows="4">{{ implode(PHP_EOL, $images) }}</textarea>
                                <p class="text-sm text-muted-foreground">
                                    {!! trans('admin/nests.egg_view.docker_images_hint') !!}
                                </p>
                            </div>
                            <div role="group" class="field space-y-2" data-orientation="horizontal">
                                <div class="flex items-center gap-3">
                                    <input id="pForceOutgoingIp" name="force_outgoing_ip" type="checkbox" value="1"  @if($egg->force_outgoing_ip) checked @endif />
                                    <label for="pForceOutgoingIp">@lang('admin/nests.egg_view.force_outgoing_ip_label')</label>
                                </div>
                                <p class="text-sm text-muted-foreground">
                                    {!! trans('admin/nests.egg_view.force_outgoing_ip_hint') !!}
                                    <br>
                                    <strong>
                                        {!! trans('admin/nests.egg_view.force_outgoing_ip_warning') !!}
                                    </strong>
                                </p>
                            </div>
                        </div>
                        <div class="space-y-6">
                            <div role="group" class="field">
                                <label for="pDescription" >@lang('admin/nests.egg_view.description_label')</label>
                                <textarea id="pDescription" name="description"  rows="8">{{ $egg->description }}</textarea>
                                <p class="text-sm text-muted-foreground">{!! trans('admin/nests.egg_view.description_hint') !!}</p>
                            </div>
                            <div role="group" class="field">
                                <label for="pStartup" >@lang('admin/nests.egg_view.startup_label') <span class="field-required"></span></label>
                                <textarea id="pStartup" name="startup"  rows="8">{{ $egg->startup }}</textarea>
                                <p class="text-sm text-muted-foreground">{!! trans('admin/nests.egg_view.startup_hint') !!}</p>
                            </div>
                            <div role="group" class="field">
                                <label for="pConfigFeatures" >@lang('admin/nests.egg_view.features_label')</label>
                                <input type="text" id="pConfigFeatures" name="features_input" placeholder="@lang('admin/nests.egg_view.features_placeholder')" value="{{ implode(', ', $egg->features ?? []) }}" />
                                <p class="text-sm text-muted-foreground">{!! trans('admin/nests.egg_view.features_hint') !!}</p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <div class="space-y-8">
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">@lang('admin/nests.egg_view.process_management_card_title')</h3>
                </header>
                <section class="space-y-6">
                    <div class="alert" data-variant="warning" role="alert">
                        <p>{!! trans('admin/nests.egg_view.process_management_alert1') !!}</p>
                        <p>{!! trans('admin/nests.egg_view.process_management_alert2') !!}</p>
                    </div>
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                        <div class="space-y-6">
                            <div role="group" class="field">
                                <label for="pConfigFrom" >@lang('admin/nests.egg_view.copy_from_label')</label>
                                <select name="config_from" id="pConfigFrom" class="select">
                                    <option value="">@lang('admin/nests.egg_view.copy_from_none')</option>
                                    @foreach($egg->nest->eggs as $o)
                                        <option value="{{ $o->id }}" {{ ($egg->config_from !== $o->id) ?: 'selected' }}>{{ $o->name }} &lt;{{ $o->author }}&gt;</option>
                                    @endforeach
                                </select>
                                <p class="text-sm text-muted-foreground">{!! trans('admin/nests.egg_view.copy_from_hint') !!}</p>
                            </div>
                            <div role="group" class="field">
                                <label for="pConfigStop" >@lang('admin/nests.egg_view.stop_command_label')</label>
                                <input type="text" id="pConfigStop" name="config_stop"  value="{{ $egg->config_stop }}" />
                                <p class="text-sm text-muted-foreground">{!! trans('admin/nests.egg_view.stop_command_hint') !!}</p>
                            </div>
                            <div role="group" class="field">
                                <label for="pConfigLogs" >@lang('admin/nests.egg_view.log_config_label')</label>
                                <textarea data-action="handle-tabs" id="pConfigLogs" name="config_logs"  rows="5">{{ ! is_null($egg->config_logs) ? json_encode(json_decode($egg->config_logs), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '' }}</textarea>
                                <p class="text-sm text-muted-foreground">{!! trans('admin/nests.egg_view.log_config_hint') !!}</p>
                            </div>
                        </div>
                        <div class="space-y-6">
                            <div role="group" class="field">
                                <label for="pConfigFiles" >@lang('admin/nests.egg_view.config_files_label')</label>
                                <textarea data-action="handle-tabs" id="pConfigFiles" name="config_files"  rows="5">{{ ! is_null($egg->config_files) ? json_encode(json_decode($egg->config_files), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '' }}</textarea>
                                <p class="text-sm text-muted-foreground">{!! trans('admin/nests.egg_view.config_files_hint') !!}</p>
                            </div>
                            <div role="group" class="field">
                                <label for="pConfigStartup" >@lang('admin/nests.egg_view.start_config_label')</label>
                                <textarea data-action="handle-tabs" id="pConfigStartup" name="config_startup"  rows="5">{{ ! is_null($egg->config_startup) ? json_encode(json_decode($egg->config_startup), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '' }}</textarea>
                                <p class="text-sm text-muted-foreground">{!! trans('admin/nests.egg_view.start_config_hint') !!}</p>
                            </div>
                        </div>
                    </div>
                </section>
                <footer>
                    {!! csrf_field() !!}
                    <button type="submit" name="_method" value="PATCH" class="btn ml-auto" data-size="sm">@lang('admin/nests.egg_view.save')</button>
                    <a href="{{ route('admin.nests.egg.export', $egg->id) }}" class="btn ml-auto mr-2.5" data-size="sm">@lang('admin/nests.egg_view.export')</a>
                    <button id="deleteButton" type="submit" name="_method" value="DELETE" class="btn" data-variant="destructive" data-size="sm">
                        <x-icon name="trash-2" class="size-4" />
                    </button>
                </footer>
            </div>
        </div>
    </div>
</form>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>
    $('#deleteButton').on('mouseenter', function (event) {
        $(this).find('i').html('{{ trans("admin.nests.egg_view.delete_egg") }}');
    }).on('mouseleave', function (event) {
        $(this).find('i').html('');
    });
    $('textarea[data-action="handle-tabs"]').on('keydown', function(event) {
        if (event.keyCode === 9) {
            event.preventDefault();

            var curPos = $(this)[0].selectionStart;
            var prepend = $(this).val().substr(0, curPos);
            var append = $(this).val().substr(curPos);

            $(this).val(prepend + '    ' + append);
        }
    });
    $('form').on('submit', function () {
        var val = $('#pConfigFeatures').val();
        if (val && $(this).find('#pConfigFeatures').length) {
            var items = val.split(/[, ]+/).filter(Boolean);
            var form = this;
            items.forEach(function (item) {
                $('<input>').attr({type: 'hidden', name: 'features[]'}).val(item.trim()).appendTo(form);
            });
        }
    });
    </script>
@endsection
