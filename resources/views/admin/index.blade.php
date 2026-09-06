@extends('layouts.admin')

@section('title')
    @lang('admin/dashboard.title')
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">@lang('admin/dashboard.header')</h1>
    <p class="text-sm text-muted-foreground">@lang('admin/dashboard.header_subtitle')</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">@lang('admin/dashboard.breadcrumb_admin')</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>@lang('admin/dashboard.breadcrumb_index')</span>
    </nav>
@endsection

@section('content')
<div class="grid gap-6 md:grid-cols-2">
    <div class="card">
        <header>
            <h3 class="text-lg font-semibold">@lang('admin/dashboard.system_info')</h3>
        </header>
        <section class="space-y-4">
            <p class="text-sm text-muted-foreground">
                @lang('admin/dashboard.system_info_text', ['version' => config('app.version')])
            </p>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.settings') }}" class="btn" data-size="sm">
                    <x-icon name="settings" class="size-4" />
                    @lang('admin/dashboard.sidebar_settings')
                </a>
                <a href="{{ route('admin.api.index') }}" class="btn" data-variant="outline" data-size="sm">
                    <x-icon name="globe" class="size-4" />
                    @lang('admin/dashboard.sidebar_api')
                </a>
            </div>
        </section>
    </div>

    <div class="card">
        <header>
            <h3 class="text-lg font-semibold">@lang('admin/dashboard.sidebar_management')</h3>
        </header>
        <section class="grid grid-cols-2 gap-2">
            <a href="{{ route('admin.nodes') }}" class="btn justify-start" data-variant="outline">
                <x-icon name="hard-drive" class="size-4" />
                @lang('admin/dashboard.sidebar_nodes')
            </a>
            <a href="{{ route('admin.servers') }}" class="btn justify-start" data-variant="outline">
                <x-icon name="server" class="size-4" />
                @lang('admin/dashboard.sidebar_servers')
            </a>
            <a href="{{ route('admin.users') }}" class="btn justify-start" data-variant="outline">
                <x-icon name="users" class="size-4" />
                @lang('admin/dashboard.sidebar_users')
            </a>
            <a href="{{ route('admin.databases') }}" class="btn justify-start" data-variant="outline">
                <x-icon name="database" class="size-4" />
                @lang('admin/dashboard.sidebar_databases')
            </a>
        </section>
    </div>

    <div class="card md:col-span-2">
        <header>
            <h3 class="text-lg font-semibold">@lang('admin/dashboard.get_help')</h3>
        </header>
        <section class="flex flex-wrap gap-2">
            <a href="https://pyrodactyl.dev" class="btn" data-variant="outline" target="_blank" rel="noopener noreferrer">
                <x-icon name="book-open" class="size-4" />
                @lang('admin/dashboard.documentation')
            </a>
            <a href="https://github.com/pyrodactyl-oss/pyrodactyl" class="btn" data-variant="outline" target="_blank" rel="noopener noreferrer">
                <x-icon name="github" class="size-4" />
                @lang('admin/dashboard.github')
            </a>
            <a href="{{ $version->getDiscord() }}" class="btn" data-variant="outline" target="_blank" rel="noopener noreferrer">
                <x-icon name="messages-square" class="size-4" />
                @lang('admin/dashboard.get_help') @lang('admin/dashboard.via_discord')
            </a>
        </section>
    </div>
</div>
@endsection
