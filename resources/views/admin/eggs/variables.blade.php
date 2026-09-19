@extends('layouts.admin')

@section('contentWidth', 'max-w-6xl')

@section('title')
    Egg &rarr; {{ $egg->name }} &rarr; @lang('admin/nests.egg_variables.title')
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">{{ $egg->name }}</h1>
    <p class="text-sm text-muted-foreground">@lang('admin/nests.egg_variables.header_description')</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">@lang('admin/nests.admin')</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.nests') }}">@lang('admin/nests.nests_breadcrumb')</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.nests.view', $egg->nest->id) }}">{{ $egg->nest->name }}</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.nests.egg.view', $egg->id) }}">{{ $egg->name }}</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>@lang('admin/nests.egg_variables.title')</span>
    </nav>
@endsection

@section('content')
<div class="grid gap-6">
    <div class="col-span-full">
        <div class="tabs">
            <nav role="tablist" aria-orientation="horizontal" data-variant="line">
                <a href="{{ route('admin.nests.egg.view', $egg->id) }}" role="tab" aria-selected="false">@lang('admin/nests.egg_variables.tab_configuration')</a>
                <a href="{{ route('admin.nests.egg.variables', $egg->id) }}" role="tab" aria-selected="true">@lang('admin/nests.egg_variables.tab_variables')</a>
                <a href="{{ route('admin.nests.egg.scripts', $egg->id) }}" role="tab" aria-selected="false">@lang('admin/nests.egg_variables.tab_install_script')</a>
            </nav>
        </div>
    </div>
</div>
<div class="grid gap-6">
    <div class="col-span-full">
        <div class="card no-border">
            <section>
                <div class="card-action">
                    <a href="#" class="btn ml-auto" data-size="sm" onclick="document.getElementById('newVariableModal').showModal()">@lang('admin/nests.egg_variables.create_new_variable')</a>
                </div>
            </section>
        </div>
    </div>
</div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
    @foreach($egg->variables as $variable)
        <div>
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">{{ $variable->name }}</h3>
                </header>
                <section>
                    <form action="{{ route('admin.nests.egg.variables.edit', ['egg' => $egg->id, 'variable' => $variable->id]) }}" method="POST" id="variableForm{{ $variable->id }}" class="space-y-6">
                        {!! csrf_field() !!}
                    <div role="group" class="field">
                        <label >@lang('admin/nests.egg_variables.name_label')</label>
                        <input type="text" name="name" value="{{ $variable->name }}" form="variableForm{{ $variable->id }}" />
                    </div>
                    <div role="group" class="field">
                        <label >@lang('admin/nests.egg_variables.description_label')</label>
                        <textarea name="description" rows="3" form="variableForm{{ $variable->id }}">{{ $variable->description }}</textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="field">
                            <label >@lang('admin/nests.egg_variables.env_variable_label')</label>
                            <input type="text" name="env_variable" value="{{ $variable->env_variable }}" form="variableForm{{ $variable->id }}" />
                        </div>
                        <div class="field">
                            <label >@lang('admin/nests.egg_variables.default_value_label')</label>
                            <input type="text" name="default_value" value="{{ $variable->default_value }}" form="variableForm{{ $variable->id }}" />
                        </div>
                        <div class="col-span-full">
                            <p class="text-sm text-muted-foreground">{!! trans('admin/nests.egg_variables.env_variable_hint', ['env' => $variable->env_variable]) !!}</p>
                        </div>
                    </div>
                    <div role="group" class="field">
                        <label >@lang('admin/nests.egg_variables.permissions_label')</label>
                        <select name="options[]" class="pOptions select" multiple form="variableForm{{ $variable->id }}">
                            <option value="user_viewable" {{ (! $variable->user_viewable) ?: 'selected' }}>@lang('admin/nests.egg_variables.user_viewable')</option>
                            <option value="user_editable" {{ (! $variable->user_editable) ?: 'selected' }}>@lang('admin/nests.egg_variables.user_editable')</option>
                        </select>
                    </div>
                    <div role="group" class="field">
                        <label >@lang('admin/nests.egg_variables.input_rules_label')</label>
                        <input type="text" name="rules" value="{{ $variable->rules }}" form="variableForm{{ $variable->id }}" />
                        <p class="text-sm text-muted-foreground">{!! trans('admin/nests.egg_variables.input_rules_hint') !!}</p>
                    </div>
                    </form>
                </section>
                <footer>
                    <button class="btn ml-auto" data-size="sm" name="_method" value="PATCH" type="submit" form="variableForm{{ $variable->id }}">@lang('admin/nests.egg_variables.save')</button>
                    <button class="btn" data-variant="destructive" data-size="sm" data-action="delete" name="_method" value="DELETE" type="submit" form="variableForm{{ $variable->id }}"><x-icon name="trash-2" class="size-4" /></button>
                </footer>
            </div>
        </div>
    @endforeach
</div>
<dialog class="dialog" id="newVariableModal" aria-labelledby="newVariableModal-title" onclick="if (event.target === this) this.close()">
    <div class="sm:max-w-sm">
        <header>
            <h2 id="newVariableModal-title">@lang('admin/nests.egg_variables.create_modal_title')</h2>
        </header>
        <section>
            <form action="{{ route('admin.nests.egg.variables', $egg->id) }}" method="POST" id="newVariableForm" class="grid gap-4">
                <div class="grid gap-3">
                    <label class="label" for="pNewName">@lang('admin/nests.egg_variables.name_label') <span class="field-required"></span></label>
                    <input class="input" id="pNewName" type="text" name="name" value="{{ old('name') }}" />
                </div>
                <div class="grid gap-3">
                    <label class="label" for="pNewDescription">@lang('admin/nests.egg_variables.description_label')</label>
                    <textarea class="input" id="pNewDescription" name="description" rows="3">{{ old('description') }}</textarea>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="grid gap-3">
                        <label class="label" for="pNewEnvVariable">@lang('admin/nests.egg_variables.env_variable_label') <span class="field-required"></span></label>
                        <input class="input" id="pNewEnvVariable" type="text" name="env_variable" value="{{ old('env_variable') }}" />
                    </div>
                    <div class="grid gap-3">
                        <label class="label" for="pNewDefaultValue">@lang('admin/nests.egg_variables.default_value_label')</label>
                        <input class="input" id="pNewDefaultValue" type="text" name="default_value" value="{{ old('default_value') }}" />
                    </div>
                </div>
                <div class="grid gap-3">
                    <label class="label">@lang('admin/nests.egg_variables.permissions_label')</label>
                    <select name="options[]" class="pOptions select" multiple>
                        <option value="user_viewable">@lang('admin/nests.egg_variables.user_viewable')</option>
                        <option value="user_editable">@lang('admin/nests.egg_variables.user_editable')</option>
                    </select>
                </div>
                <div class="grid gap-3">
                    <label class="label" for="pNewRules">@lang('admin/nests.egg_variables.input_rules_label') <span class="field-required"></span></label>
                    <input class="input" id="pNewRules" type="text" name="rules" value="{{ old('rules', 'required|string|max:20') }}" placeholder="@lang('admin/nests.egg_variables.input_rules_placeholder')" />
                    <p class="text-sm text-muted-foreground">{!! trans('admin/nests.egg_variables.input_rules_hint') !!}</p>
                </div>
                {!! csrf_field() !!}
            </form>
        </section>
        <footer>
            <button type="button" class="btn" data-variant="outline" onclick="this.closest('dialog').close()">@lang('admin/nests.egg_variables.close')</button>
            <button type="submit" class="btn" form="newVariableForm">@lang('admin/nests.egg_variables.create_variable')</button>
        </footer>
        <button type="button" class="btn" data-variant="ghost" data-size="icon-sm" aria-label="@lang('admin/nests.egg_variables.close_dialog')" onclick="this.closest('dialog').close()"><x-icon name="x" class="size-4" /></button>
    </div>
</dialog>
@endsection

@section('footer-scripts')
    @parent
    <script>
        $('[data-action="delete"]').on('mouseenter', function (event) {
            $(this).find('i').html('{{ trans("admin.nests.egg_variables.delete_variable") }}');
        }).on('mouseleave', function (event) {
            $(this).find('i').html('');
        });
    </script>
@endsection
