@extends('layouts.admin')

@section('contentWidth', 'max-w-6xl')

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
<form action="{{ route('admin.servers.view.startup', $server->id) }}" method="POST" class="admin-responsive-detail min-w-0">
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
                            <textarea id="pStartup" name="startup" rows="3">{{ old('startup', $server->startup) }}</textarea>
                            <p class="text-sm text-muted-foreground">@lang('admin/server.startup.startup_command_help')</p>
                        </div>
                    </div>
                </section>
                <section>
                    <div class="grid gap-6">
                        <div role="group" class="field">
                            <label for="pDefaultStartupCommand" class="font-semibold">@lang('admin/server.startup.default_start_command')</label>
                            <textarea id="pDefaultStartupCommand" rows="3" readonly></textarea>
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
    <div class="grid min-w-0 grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="min-w-0 space-y-6">
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
                            <select name="nest_id" id="pNestId" class="select w-full">
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
                            <select name="egg_id" id="pEggId" class="select w-full"></select>
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
                            <select id="pDockerImage" name="docker_image" class="select w-full"></select>
                            <input id="pDockerImageCustom" name="custom_docker_image" value="{{ old('custom_docker_image') }}" placeholder="{{ trans('admin/server.startup.custom_image_placeholder') }}"/>
                            <p class="text-sm text-muted-foreground">@lang('admin/server.startup.docker_image_help')</p>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <div class="min-w-0">
            <div class="grid min-w-0 gap-6" id="appendVariablesTo"></div>
        </div>
    </div>
</form>
@endsection

@section('footer-scripts')
    @parent
    <script>
    const nests = window.Pyrodactyl?.nests || {};
    const server = window.Pyrodactyl?.server || {};
    const serverVariables = window.Pyrodactyl?.server_variables || {};
    const previous = {
        nest: String(@json(old('nest_id', $server->nest_id))),
        egg: String(@json(old('egg_id', $server->egg_id))),
        image: @json(old('docker_image', $server->image)),
        environment: @json(old('environment', [])),
    };
    const nestSelect = document.getElementById('pNestId');
    const eggSelect = document.getElementById('pEggId');
    const imageSelect = document.getElementById('pDockerImage');
    const customImage = document.getElementById('pDockerImageCustom');
    const variableContainer = document.getElementById('appendVariablesTo');

    function updateEgg(initial = false) {
        const nest = nests[nestSelect.value];
        const egg = nest?.eggs?.[eggSelect.value];
        imageSelect.replaceChildren();
        variableContainer.replaceChildren();
        document.getElementById('pDefaultStartupCommand').value = egg?.startup || nest?.startup || @json(trans('admin/server.startup.error_startup_not_defined'));

        if (!egg) return;

        Object.entries(egg.docker_images || {}).forEach(([name, image]) => {
            imageSelect.add(new Option(name + ' (' + image + ')', image));
        });
        if (initial && previous.image) {
            imageSelect.value = previous.image;
            if (imageSelect.selectedIndex === -1) {
                imageSelect.selectedIndex = 0;
                if (!customImage.value) customImage.value = previous.image;
            }
        } else {
            customImage.value = '';
        }

        (egg.variables || []).forEach((variable) => {
            const card = document.createElement('div');
            const header = document.createElement('header');
            const heading = document.createElement('h3');
            const section = document.createElement('section');
            const field = document.createElement('div');
            const input = document.createElement('input');
            const description = document.createElement('p');
            const footer = document.createElement('footer');

            card.className = 'card min-w-0';
            heading.className = 'text-lg font-semibold';
            if (variable.rules?.split('|').includes('required')) {
                const required = document.createElement('span');
                required.className = 'badge';
                required.dataset.variant = 'destructive';
                required.textContent = @json(trans('admin/server.startup.required'));
                heading.append(required, document.createTextNode(' '));
            }
            heading.append(document.createTextNode(variable.name));
            header.append(heading);
            field.className = 'field';
            field.setAttribute('role', 'group');
            input.type = 'text';
            input.name = 'environment[' + variable.env_variable + ']';
            input.id = 'egg_variable_' + variable.id;
            input.value = initial && Object.hasOwn(previous.environment, variable.env_variable)
                ? previous.environment[variable.env_variable]
                : String(egg.id) === String(server.egg_id) && Object.hasOwn(serverVariables, variable.env_variable)
                    ? serverVariables[variable.env_variable] : variable.default_value || '';
            description.className = 'text-sm text-muted-foreground';
            description.textContent = variable.description || '';
            field.append(input, description);
            section.append(field);
            [
                [@json(trans('admin/server.startup.startup_command_variable')), variable.env_variable],
                [@json(trans('admin/server.startup.input_rules')), variable.rules],
            ].forEach(([label, value]) => {
                const row = document.createElement('p');
                const title = document.createElement('strong');
                const code = document.createElement('code');
                row.className = 'text-sm text-muted-foreground';
                title.textContent = label + ' ';
                code.textContent = value || '';
                row.append(title, code);
                footer.append(row);
            });
            card.append(header, section, footer);
            variableContainer.append(card);
        });
    }

    function updateNest(initial = false) {
        const eggs = Object.values(nests[nestSelect.value]?.eggs || {});
        eggSelect.replaceChildren();
        eggs.forEach((egg) => eggSelect.add(new Option(egg.name, egg.id)));
        if (initial && eggs.some((egg) => String(egg.id) === previous.egg)) eggSelect.value = previous.egg;
        updateEgg(initial);
    }

    nestSelect.addEventListener('change', () => updateNest());
    eggSelect.addEventListener('change', () => updateEgg());
    imageSelect.addEventListener('change', () => { customImage.value = ''; });
    nestSelect.value = previous.nest;
    updateNest(true);
    </script>
@endsection
