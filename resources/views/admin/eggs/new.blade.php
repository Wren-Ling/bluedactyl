@extends('layouts.admin')

@section('title')
    @lang('admin/nests.egg_new.page_title')
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">@lang('admin/nests.egg_new.title')</h1>
    <p class="text-sm text-muted-foreground">@lang('admin/nests.egg_new.header_description')</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">@lang('admin/nests.admin')</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.nests') }}">@lang('admin/nests.nests_breadcrumb')</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>@lang('admin/nests.egg_new.title')</span>
    </nav>
@endsection

@section('content')
<form action="{{ route('admin.nests.egg.new') }}" method="POST">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div>
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">@lang('admin/nests.egg_new.configuration_card_title')</h3>
                </header>
                <section>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div role="group" class="field">
                                <label for="pNestId" >@lang('admin/nests.egg_new.nest_label')</label>
                                <select name="nest_id" id="pNestId" class="select">
                                    @foreach($nests as $nest)
                                        <option value="{{ $nest->id }}" {{ old('nest_id') != $nest->id ?: 'selected' }}>{{ $nest->name }} &lt;{{ $nest->author }}&gt;</option>
                                    @endforeach
                                </select>
                                <p class="text-sm text-muted-foreground">{!! trans('admin/nests.egg_new.nest_hint') !!}</p>
                            </div>
                            <div role="group" class="field">
                                <label for="pName" >@lang('admin/nests.egg_new.name_label')</label>
                                <input type="text" id="pName" name="name" value="{{ old('name') }}"  />
                                <p class="text-sm text-muted-foreground">{!! trans('admin/nests.egg_new.name_hint') !!}</p>
                            </div>
                            <div role="group" class="field">
                                <label for="pDescription" >@lang('admin/nests.egg_new.description_label')</label>
                                <textarea id="pDescription" name="description"  rows="8">{{ old('description') }}</textarea>
                                <p class="text-sm text-muted-foreground">{!! trans('admin/nests.egg_new.description_hint') !!}</p>
                            </div>
                            <div role="group" class="field" data-orientation="horizontal">
                                <input id="pForceOutgoingIp" name="force_outgoing_ip" type="checkbox" value="1"  {{ \Pterodactyl\Helpers\Utilities::checked('force_outgoing_ip', 0) }} />
                                <label for="pForceOutgoingIp">@lang('admin/nests.egg_new.force_outgoing_ip_label')</label>
                                <p class="text-sm text-muted-foreground">
                                    {!! trans('admin/nests.egg_new.force_outgoing_ip_hint') !!}
                                    <br>
                                    <strong>
                                        {!! trans('admin/nests.egg_new.force_outgoing_ip_warning') !!}
                                    </strong>
                                </p>
                            </div>
                        </div>
                        <div>
                            <div role="group" class="field">
                                <label for="pDockerImage" >@lang('admin/nests.egg_new.docker_images_label')</label>
                                <textarea id="pDockerImages" name="docker_images" rows="4" placeholder="@lang('admin/nests.egg_new.docker_images_placeholder')" >{{ old('docker_images') }}</textarea>
                                <p class="text-sm text-muted-foreground">{!! trans('admin/nests.egg_new.docker_images_hint') !!}</p>
                            </div>
                            <div role="group" class="field">
                                <label for="pStartup" >@lang('admin/nests.egg_new.startup_label')</label>
                                <textarea id="pStartup" name="startup"  rows="10">{{ old('startup') }}</textarea>
                                <p class="text-sm text-muted-foreground">{!! trans('admin/nests.egg_new.startup_hint') !!}</p>
                            </div>
                            <div role="group" class="field">
                                <label for="pConfigFeatures" >@lang('admin/nests.egg_new.features_label')</label>
                                <input type="text" id="pConfigFeatures" name="features_input" placeholder="@lang('admin/nests.egg_new.features_placeholder')" />
                                <p class="text-sm text-muted-foreground">{!! trans('admin/nests.egg_new.features_hint') !!}</p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <div>
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">@lang('admin/nests.egg_new.process_management_card_title')</h3>
                </header>
                <section>
                    <div class="grid gap-6">
                        <div class="col-span-full">
                            <div class="alert" data-variant="warning" role="alert">
                                <p>{!! trans('admin/nests.egg_new.process_management_alert') !!}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 col-span-full">
                            <div>
                                <div role="group" class="field">
                                    <label for="pConfigFrom" >@lang('admin/nests.egg_new.copy_from_label')</label>
                                    <select name="config_from" id="pConfigFrom" class="select">
                                        <option value="">@lang('admin/nests.egg_new.copy_from_none')</option>
                                    </select>
                                    <p class="text-sm text-muted-foreground">{!! trans('admin/nests.egg_new.copy_from_hint') !!}</p>
                                </div>
                                <div role="group" class="field">
                                    <label for="pConfigStop" >@lang('admin/nests.egg_new.stop_command_label')</label>
                                    <input type="text" id="pConfigStop" name="config_stop"  value="{{ old('config_stop') }}" />
                                    <p class="text-sm text-muted-foreground">{!! trans('admin/nests.egg_new.stop_command_hint') !!}</p>
                                </div>
                                <div role="group" class="field">
                                    <label for="pConfigLogs" >@lang('admin/nests.egg_new.log_config_label')</label>
                                    <textarea data-action="handle-tabs" id="pConfigLogs" name="config_logs"  rows="6">{{ old('config_logs') }}</textarea>
                                    <p class="text-sm text-muted-foreground">{!! trans('admin/nests.egg_new.log_config_hint') !!}</p>
                                </div>
                            </div>
                            <div>
                                <div role="group" class="field">
                                    <label for="pConfigFiles" >@lang('admin/nests.egg_new.config_files_label')</label>
                                    <textarea data-action="handle-tabs" id="pConfigFiles" name="config_files"  rows="6">{{ old('config_files') }}</textarea>
                                    <p class="text-sm text-muted-foreground">{!! trans('admin/nests.egg_new.config_files_hint') !!}</p>
                                </div>
                                <div role="group" class="field">
                                    <label for="pConfigStartup" >@lang('admin/nests.egg_new.start_config_label')</label>
                                    <textarea data-action="handle-tabs" id="pConfigStartup" name="config_startup"  rows="6">{{ old('config_startup') }}</textarea>
                                    <p class="text-sm text-muted-foreground">{!! trans('admin/nests.egg_new.start_config_hint') !!}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <footer>
                    {!! csrf_field() !!}
                    <button type="submit" class="btn ml-auto" data-size="sm">@lang('admin/nests.egg_new.create')</button>
                </footer>
            </div>
        </div>
    </div>
</form>
@endsection

@section('footer-scripts')
    @parent
    {!! Theme::js('vendor/lodash/lodash.js') !!}
    <script>
    $(document).ready(function() {
        $('#pNestId').change();
    });
    $('#pNestId').on('change', function (event) {
        $('#pConfigFrom').html('<option value="">@lang('admin/nests.egg_new.copy_from_none')</option>');
        var eggs = _.get(Pyrodactyl.nests, $(this).val() + '.eggs', []);
        eggs.forEach(function (item) {
            $('#pConfigFrom').append($('<option>', {
                value: item.id,
                text: item.name + ' <' + item.author + '>',
            }));
        });
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
        if (val) {
            var items = val.split(/[, ]+/).filter(Boolean);
            var form = this;
            items.forEach(function (item) {
                $('<input>').attr({type: 'hidden', name: 'features[]'}).val(item.trim()).appendTo(form);
            });
        }
    });
    </script>
@endsection
