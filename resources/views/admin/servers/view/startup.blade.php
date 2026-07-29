@extends('layouts.admin')

@section('title')
    @lang('admin/server.overview.title') — {{ $server->name }}: @lang('admin/server.startup.title')
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">{{ $server->name }}</h1>
    <p class="text-sm text-muted-foreground">@lang('admin/server.startup.description')</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">@lang('admin/server.startup.breadcrumb_admin')</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.servers') }}">@lang('admin/server.startup.breadcrumb_servers')</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.servers.view', $server->id) }}">{{ $server->name }}</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>@lang('admin/server.startup.breadcrumb_startup')</span>
    </nav>
@endsection

@section('content')
@include('admin.servers.partials.navigation')
<form action="{{ route('admin.servers.view.startup', $server->id) }}" method="POST">
    <div class="grid gap-6">
        <div class="col-span-full">
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">@lang('admin/server.startup.startup_command_modification')</h3>
                </header>
                <section>
                    <div class="grid gap-6">
                        <div role="group" class="field">
                            <label for="pStartup" class="font-semibold">@lang('admin/server.startup.startup_command')</label>
                            <input id="pStartup" name="startup" type="text" value="{{ old('startup', $server->startup) }}" />
                            <p class="text-sm text-muted-foreground">@lang('admin/server.startup.startup_command_help')</p>
                        </div>
                    </div>
                </section>
                <section>
                    <div class="grid gap-6">
                        <div role="group" class="field">
                            <label for="pDefaultStartupCommand" class="font-semibold">@lang('admin/server.startup.default_start_command')</label>
                            <input id="pDefaultStartupCommand" type="text" readonly />
                        </div>
                    </div>
                </section>
                <footer>
                    {!! csrf_field() !!}
                    <button type="submit" class="btn ml-auto" data-size="sm">@lang('admin/server.startup.save_modifications')</button>
                </footer>
            </div>
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">@lang('admin/server.startup.service_config')</h3>
                </header>
                <section>
                    <div class="grid gap-6">
                        <div>
                            <p class="text-sm text-destructive">
                                @lang('admin/server.startup.service_config_warning')
                            </p>
                            <p class="text-sm text-destructive">
                                @lang('admin/server.startup.service_config_danger')
                            </p>
                        </div>
                        <div role="group" class="field">
                            <label for="pNestId">@lang('admin/server.startup.nest')</label>
                            <select name="nest_id" id="pNestId" class="select">
                                @foreach($nests as $nest)
                                    <option value="{{ $nest->id }}"
                                        @if($nest->id === $server->nest_id)
                                            selected
                                        @endif
                                    >{{ $nest->name }}</option>
                                @endforeach
                            </select>
                            <p class="text-sm text-muted-foreground">@lang('admin/server.startup.nest_help')</p>
                        </div>
                        <div role="group" class="field">
                            <label for="pEggId">@lang('admin/server.startup.egg')</label>
                            <select name="egg_id" id="pEggId" class="select"></select>
                            <p class="text-sm text-muted-foreground">@lang('admin/server.startup.egg_help')</p>
                        </div>
                        <div role="group" class="field" data-orientation="horizontal">
                            <input id="pSkipScripting" name="skip_scripts" type="checkbox" value="1" @if($server->skip_scripts) checked @endif />
                            <label for="pSkipScripting" class="font-normal">@lang('admin/server.startup.skip_egg_install_script')</label>
                        </div>
                        <p class="text-sm text-muted-foreground">@lang('admin/server.startup.skip_egg_install_script_help')</p>
                    </div>
                </section>
            </div>
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">@lang('admin/server.startup.docker_image_config')</h3>
                </header>
                <section>
                    <div class="grid gap-6">
                        <div role="group" class="field">
                            <label for="pDockerImage">@lang('admin/server.startup.image')</label>
                            <select id="pDockerImage" name="docker_image" class="select"></select>
                            <input id="pDockerImageCustom" name="custom_docker_image" value="{{ old('custom_docker_image') }}" placeholder="{{ trans('admin/server.startup.custom_image_placeholder') }}"/>
                            <p class="text-sm text-muted-foreground">@lang('admin/server.startup.docker_image_help')</p>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <div>
            <div class="grid gap-6" id="appendVariablesTo"></div>
        </div>
    </div>
</form>
@endsection

@section('footer-scripts')
    @parent
    {!! Theme::js('vendor/lodash/lodash.js') !!}
    <script>
    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    $(document).ready(function () {
        $('#pEggId').on('change', function () {
            var selectedEgg = _.isNull($(this).val()) ? $(this).find('option').first().val() : $(this).val();
            var parentChain = _.get(Pyrodactyl.nests, $("#pNestId").val());
            var objectChain = _.get(parentChain, 'eggs.' + selectedEgg);

            const images = _.get(objectChain, 'docker_images', [])
            $('#pDockerImage').html('');
            const keys = Object.keys(images);
            for (let i = 0; i < keys.length; i++) {
                let opt = document.createElement('option');
                opt.value = images[keys[i]];
                opt.innerText = keys[i] + " (" + images[keys[i]] + ")";
                if (objectChain.id === parseInt(Pyrodactyl.server.egg_id) && Pyrodactyl.server.image == opt.value) {
                    opt.selected = true
                }
                $('#pDockerImage').append(opt);
            }
            $('#pDockerImage').on('change', function () {
                $('#pDockerImageCustom').val('');
            })

            if (objectChain.id === parseInt(Pyrodactyl.server.egg_id)) {
                if ($('#pDockerImage').val() != Pyrodactyl.server.image) {
                    $('#pDockerImageCustom').val(Pyrodactyl.server.image);
                }
            }

            if (!_.get(objectChain, 'startup', false)) {
                $('#pDefaultStartupCommand').val(_.get(parentChain, 'startup', '{{ trans('admin/server.startup.error_startup_not_defined') }}'));
            } else {
                $('#pDefaultStartupCommand').val(_.get(objectChain, 'startup'));
            }

            $('#appendVariablesTo').html('');
            $.each(_.get(objectChain, 'variables', []), function (i, item) {
                var setValue = _.get(Pyrodactyl.server_variables, item.env_variable, item.default_value);
                var isRequired = (item.required === 1) ? '<span class="badge" data-variant="destructive">{{ trans('admin/server.startup.required') }}</span> ' : '';
                var dataAppend = ' \
                    <div> \
                        <div class="card"> \
                            <header> \
                                <h3 class="text-lg font-semibold">' + isRequired + escapeHtml(item.name) + '</h3> \
                            </header> \
                            <section> \
                                <div role="group" class="field"> \
                                    <input name="environment[' + escapeHtml(item.env_variable) + ']" type="text" id="egg_variable_' + escapeHtml(item.env_variable) + '" /> \
                                    <p class="text-sm text-muted-foreground">' + escapeHtml(item.description) + '</p> \
                                </div> \
                            </section> \
                            <footer> \
                                <p class="text-sm text-muted-foreground"><strong>{{ trans('admin/server.startup.startup_command_variable') }}</strong> <code>' + escapeHtml(item.env_variable) + '</code></p> \
                                <p class="text-sm text-muted-foreground"><strong>{{ trans('admin/server.startup.input_rules') }}</strong> <code>' + escapeHtml(item.rules) + '</code></p> \
                            </footer> \
                        </div> \
                    </div>';
                $('#appendVariablesTo').append(dataAppend).find('#egg_variable_' + item.env_variable).val(setValue);
            });
        });

        $('#pNestId').on('change', function () {
            $('#pEggId').html('');
            $.each(_.get(Pyrodactyl.nests, $(this).val() + '.eggs', []), function (i, item) {
                $('#pEggId').append($('<option>', {
                    value: item.id,
                    text: item.name,
                }));
            });

            if (_.isObject(_.get(Pyrodactyl.nests, $(this).val() + '.eggs.' + Pyrodactyl.server.egg_id))) {
                $('#pEggId').val(Pyrodactyl.server.egg_id);
            }

            $('#pEggId').change();
        }).change();
    });
    </script>
@endsection
