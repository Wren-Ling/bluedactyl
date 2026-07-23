@extends('layouts.admin')

@section('title')
    Server — {{ $server->name }}: Startup
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">{{ $server->name }}</h1>
    <p class="text-sm text-muted-foreground">Control startup command as well as variables.</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">Admin</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.servers') }}">Servers</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.servers.view', $server->id) }}">{{ $server->name }}</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>Startup</span>
    </nav>
@endsection

@section('content')
@include('admin.servers.partials.navigation')
<form action="{{ route('admin.servers.view.startup', $server->id) }}" method="POST">
    <div class="grid gap-6">
        <div class="col-span-full">
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">Startup Command Modification</h3>
                </header>
                <section>
                    <div class="grid gap-6">
                        <div role="group" class="field">
                            <label for="pStartup" class="font-semibold">Startup Command</label>
                            <input id="pStartup" name="startup" type="text" value="{{ old('startup', $server->startup) }}" />
                            <p class="text-sm text-muted-foreground">Edit your server's startup command here. The following variables are available by default: <code>@{{SERVER_MEMORY}}</code>, <code>@{{SERVER_IP}}</code>, and <code>@{{SERVER_PORT}}</code>.</p>
                        </div>
                    </div>
                </section>
                <section>
                    <div class="grid gap-6">
                        <div role="group" class="field">
                            <label for="pDefaultStartupCommand" class="font-semibold">Default Service Start Command</label>
                            <input id="pDefaultStartupCommand" type="text" readonly />
                        </div>
                    </div>
                </section>
                <footer>
                    {!! csrf_field() !!}
                    <button type="submit" class="btn ml-auto" data-size="sm">Save Modifications</button>
                </footer>
            </div>
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">Service Configuration</h3>
                </header>
                <section>
                    <div class="grid gap-6">
                        <div>
                            <p class="text-sm text-destructive">
                                Changing any of the below values will result in the server processing a re-install command. The server will be stopped and will then proceed.
                                If you would like the service scripts to not run, ensure the box is checked at the bottom.
                            </p>
                            <p class="text-sm text-destructive">
                                <strong>This is a destructive operation in many cases. This server will be stopped immediately in order for this action to proceed.</strong>
                            </p>
                        </div>
                        <div role="group" class="field">
                            <label for="pNestId">Nest</label>
                            <select name="nest_id" id="pNestId" class="select">
                                @foreach($nests as $nest)
                                    <option value="{{ $nest->id }}"
                                        @if($nest->id === $server->nest_id)
                                            selected
                                        @endif
                                    >{{ $nest->name }}</option>
                                @endforeach
                            </select>
                            <p class="text-sm text-muted-foreground">Select the Nest that this server will be grouped into.</p>
                        </div>
                        <div role="group" class="field">
                            <label for="pEggId">Egg</label>
                            <select name="egg_id" id="pEggId" class="select"></select>
                            <p class="text-sm text-muted-foreground">Select the Egg that will provide processing data for this server.</p>
                        </div>
                        <div role="group" class="field" data-orientation="horizontal">
                            <input id="pSkipScripting" name="skip_scripts" type="checkbox" value="1" @if($server->skip_scripts) checked @endif />
                            <label for="pSkipScripting" class="font-normal">Skip Egg Install Script</label>
                        </div>
                        <p class="text-sm text-muted-foreground">If the selected Egg has an install script attached to it, the script will run during install. If you would like to skip this step, check this box.</p>
                    </div>
                </section>
            </div>
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">Docker Image Configuration</h3>
                </header>
                <section>
                    <div class="grid gap-6">
                        <div role="group" class="field">
                            <label for="pDockerImage">Image</label>
                            <select id="pDockerImage" name="docker_image" class="select"></select>
                            <input id="pDockerImageCustom" name="custom_docker_image" value="{{ old('custom_docker_image') }}" placeholder="Or enter a custom image..."/>
                            <p class="text-sm text-muted-foreground">This is the Docker image that will be used to run this server. Select an image from the dropdown or enter a custom image in the text field above.</p>
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
                $('#pDefaultStartupCommand').val(_.get(parentChain, 'startup', 'ERROR: Startup Not Defined!'));
            } else {
                $('#pDefaultStartupCommand').val(_.get(objectChain, 'startup'));
            }

            $('#appendVariablesTo').html('');
            $.each(_.get(objectChain, 'variables', []), function (i, item) {
                var setValue = _.get(Pyrodactyl.server_variables, item.env_variable, item.default_value);
                var isRequired = (item.required === 1) ? '<span class="badge" data-variant="destructive">Required</span> ' : '';
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
                                <p class="text-sm text-muted-foreground"><strong>Startup Command Variable:</strong> <code>' + escapeHtml(item.env_variable) + '</code></p> \
                                <p class="text-sm text-muted-foreground"><strong>Input Rules:</strong> <code>' + escapeHtml(item.rules) + '</code></p> \
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
