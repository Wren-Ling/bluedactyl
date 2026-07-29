@extends('layouts.admin')

@section('title')
    {{ $node->name }}: @lang('admin/nodes.common.configuration')
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">{{ $node->name }}</h1>
    <p class="text-sm text-muted-foreground">@lang('admin/nodes.config.header_subtitle')</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">@lang('admin/nodes.common.admin')</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.nodes') }}">@lang('admin/nodes.common.nodes')</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.nodes.view', $node->id) }}">{{ $node->name }}</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>@lang('admin/nodes.common.configuration')</span>
    </nav>
@endsection

@section('content')
<div class="grid gap-6">
    <div class="col-span-full">
        <div class="tabs">
            <nav role="tablist" aria-orientation="horizontal" data-variant="line">
                <a href="{{ route('admin.nodes.view', $node->id) }}" role="tab" aria-selected="false" tabindex="-1">@lang('admin/nodes.common.about')</a>
                <a href="{{ route('admin.nodes.view.settings', $node->id) }}" role="tab" aria-selected="false" tabindex="-1">@lang('admin/nodes.common.settings')</a>
                <a href="{{ route('admin.nodes.view.configuration', $node->id) }}" role="tab" aria-selected="true" tabindex="0">@lang('admin/nodes.common.configuration')</a>
                <a href="{{ route('admin.nodes.view.allocation', $node->id) }}" role="tab" aria-selected="false" tabindex="-1">@lang('admin/nodes.common.allocation')</a>
                <a href="{{ route('admin.nodes.view.servers', $node->id) }}" role="tab" aria-selected="false" tabindex="-1">@lang('admin/nodes.common.servers')</a>
            </nav>
        </div>
    </div>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="md:col-span-2">
        <div class="card">
            <header>
                <h3 class="text-lg font-semibold">@lang('admin/nodes.config.configuration_file')</h3>
            </header>
            <section>
                <pre class="no-margin">{{ $node->getYamlConfiguration() }}</pre>
            </section>
            <footer>
                <p class="no-margin">{!! trans('admin/nodes.config.configuration_file_help') !!}</p>
            </footer>
        </div>
    </div>
    <div>
        <div class="card">
            <header>
                <h3 class="text-lg font-semibold">@lang('admin/nodes.config.auto_deploy')</h3>
            </header>
            <section>
                <p class="text-sm text-muted-foreground">
                    @lang('admin/nodes.config.auto_deploy_help')
                </p>
            </section>
            <footer>
                <button type="button" id="configTokenBtn" class="btn w-full" data-variant="outline" data-size="sm">@lang('admin/nodes.config.generate_token')</button>
            </footer>
        </div>
    </div>
</div>
@endsection

<dialog class="dialog" id="configTokenDialog" aria-labelledby="configTokenDialogTitle" onclick="if (event.target === this) this.close()">
    <div class="sm:max-w-lg">
        <header>
            <h2 id="configTokenDialogTitle">@lang('admin/nodes.config.token_created')</h2>
        </header>
        <section>
            <p>@lang('admin/nodes.config.token_help')</p>
            <pre id="configTokenOutput" class="mt-2 block overflow-x-auto whitespace-pre rounded border bg-muted p-3 text-xs leading-relaxed"></pre>
        </section>
        <footer>
            <button type="button" class="btn" onclick="this.closest('dialog').close()">@lang('admin/nodes.config.close')</button>
        </footer>
        <button type="button" class="btn" data-variant="ghost" data-size="icon-sm" aria-label="@lang('admin/nodes.config.close_dialog')" onclick="this.closest('dialog').close()"><x-icon name="x" class="size-4" /></button>
    </div>
</dialog>

@section('footer-scripts')
    @parent
    <script>
    $('#configTokenBtn').on('click', function (event) {
        $.ajax({
            method: 'POST',
            url: '{{ route('admin.nodes.view.configuration.token', $node->id) }}',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        }).done(function (data) {
            var commandTemplate = "{!! addslashes($node->getAutoDeploy("PLACEHOLDER_TOKEN")) !!}";
            var command = commandTemplate.replace('PLACEHOLDER_TOKEN', data.token);
            document.getElementById('configTokenOutput').textContent = command.split(' && ').join(' &&\n').split(' --').join('\n  --');
            document.getElementById('configTokenDialog').showModal();
        }).fail(function () {
            alert('{{ trans('admin/nodes.config.token_error') }}');
        });
    });
    </script>
@endsection
