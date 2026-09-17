@extends('layouts.admin')

@section('title')
    @lang('admin/server.overview.title') — {{ $server->name }}: @lang('admin/server.details.title')
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">{{ $server->name }}</h1>
    <p class="text-sm text-muted-foreground">@lang('admin/server.details.description')</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">@lang('admin/server.details.breadcrumb_admin')</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.servers') }}">@lang('admin/server.details.breadcrumb_servers')</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.servers.view', $server->id) }}">{{ $server->name }}</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>@lang('admin/server.details.breadcrumb_details')</span>
    </nav>
@endsection

@section('content')
@include('admin.servers.partials.navigation')
<div class="grid gap-6">
    <div class="col-span-full">
        <div class="card">
            <header>
                <h3 class="text-lg font-semibold">@lang('admin/server.details.base_information')</h3>
            </header>
            <section>
                <form action="{{ route('admin.servers.view.details', $server->id) }}" method="POST">
                    <div class="grid gap-6">
                        <div role="group" class="field">
                            <label for="name" >@lang('admin/server.details.server_name') <span class="field-required"></span></label>
                            <input type="text" name="name" value="{{ old('name', $server->name) }}"  />
                            <p class="text-sm text-muted-foreground">@lang('admin/server.details.server_name_help')</p>
                        </div>
                        <div role="group" class="field">
                            <label for="external_id" >@lang('admin/server.details.external_id')</label>
                            <input type="text" name="external_id" value="{{ old('external_id', $server->external_id) }}"  />
                            <p class="text-sm text-muted-foreground">@lang('admin/server.details.external_id_help')</p>
                        </div>
                        <div role="group" class="field">
                            <label for="pUserId" >@lang('admin/server.details.server_owner') <span class="field-required"></span></label>
                            <select name="owner_id" class="select" id="pUserId">
                                <option value="{{ $server->owner_id }}" selected>{{ $server->user->email }}</option>
                            </select>
                            <p class="text-sm text-muted-foreground">@lang('admin/server.details.server_owner_help')</p>
                        </div>
                        <div role="group" class="field">
                            <label for="description" >@lang('admin/server.details.server_description')</label>
                            <textarea name="description" rows="3" >{{ old('description', $server->description) }}</textarea>
                            <p class="text-sm text-muted-foreground">@lang('admin/server.details.server_description_help')</p>
                        </div>
                    </div>
                </form>
            </section>
            <footer>
                {!! csrf_field() !!}
                {!! method_field('PATCH') !!}
                <input type="submit" class="btn" data-size="sm" value="@lang('admin/server.details.update_details')" />
            </footer>
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>
    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }


    </script>
@endsection
