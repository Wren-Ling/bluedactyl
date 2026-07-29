@extends('layouts.admin')

@section('title')
    @lang('admin/server.overview.title') — {{ $server->name }}: @lang('admin/server.delete.title')
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">{{ $server->name }}</h1>
    <p class="text-sm text-muted-foreground">@lang('admin/server.delete.description')</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">@lang('admin/server.delete.breadcrumb_admin')</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.servers') }}">@lang('admin/server.delete.breadcrumb_servers')</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.servers.view', $server->id) }}">{{ $server->name }}</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>@lang('admin/server.delete.breadcrumb_delete')</span>
    </nav>
@endsection

@section('content')
@include('admin.servers.partials.navigation')
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <div class="card">
            <header>
                <h3 class="text-lg font-semibold">@lang('admin/server.delete.safely_delete')</h3>
            </header>
            <section>
                <p>@lang('admin/server.delete.safely_delete_desc')</p>
                <p class="text-sm text-destructive">@lang('admin/server.delete.safely_delete_warning')</p>
            </section>
            <footer>
                <form id="deleteform" action="{{ route('admin.servers.view.delete', $server->id) }}" method="POST">
                    {!! csrf_field() !!}
                    <button id="deletebtn" class="btn" data-variant="destructive">@lang('admin/server.delete.safely_delete_button')</button>
                </form>
            </footer>
        </div>
    </div>
    <div>
        <div class="card" data-variant="destructive">
            <header>
                <h3 class="text-lg font-semibold">@lang('admin/server.delete.force_delete')</h3>
            </header>
            <section>
                <p>@lang('admin/server.delete.force_delete_desc')</p>
                <p class="text-sm text-destructive">@lang('admin/server.delete.force_delete_warning')</p>
            </section>
            <footer>
                <form id="forcedeleteform" action="{{ route('admin.servers.view.delete', $server->id) }}" method="POST">
                    {!! csrf_field() !!}
                    <input type="hidden" name="force_delete" value="1" />
                    <button id="forcedeletebtn" class="btn" data-variant="destructive">@lang('admin/server.delete.force_delete_button')</button>
                </form>
            </footer>
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>
    $('#deletebtn').click(function (event) {
        event.preventDefault();
        if (confirm('{{ trans('admin/server.delete.confirm_delete') }}')) {
            $('#deleteform').submit();
        }
    });

    $('#forcedeletebtn').click(function (event) {
        event.preventDefault();
        if (confirm('{{ trans('admin/server.delete.confirm_delete') }}')) {
            $('#forcedeleteform').submit();
        }
    });
    </script>
@endsection
