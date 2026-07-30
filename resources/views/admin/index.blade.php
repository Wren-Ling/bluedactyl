@extends('layouts.admin')

@section('title')
    @lang('admin/users.list.title')
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">@lang('admin/users.list.header')</h1>
    <p class="text-sm text-muted-foreground">@lang('admin/users.list.header_desc')</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">@lang('admin/users.list.breadcrumb_admin')</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>@lang('admin/users.list.breadcrumb_here')</span>
    </nav>
@endsection

@section('content')
<div class="grid gap-6">
    <div class="col-span-full">
        <div class="card overflow-hidden">
            <header>
                <h3 class="text-lg font-semibold">@lang('admin/users.list.card_title')</h3>
                <div class="card-action">
                    <div class="search01">
                        <form action="{{ route('admin.users') }}" method="GET" class="flex items-center gap-1">
                            <div role="group" class="field">
                                <input type="text" name="filter[email]" value="{{ request()->input('filter.email') }}" placeholder="{{ trans('admin/users.list.search_placeholder') }}">
                            </div>
                            <button type="submit" class="btn" data-variant="outline" data-size="sm"><x-icon name="search" class="size-4" /></button>
                            <a href="{{ route('admin.users.new') }}"><button type="button" class="btn rounded-r-md -ml-px" data-size="sm">@lang('admin/users.list.create_new')</button></a>
                        </form>
                    </div>
                </div>
            </header>
            <section>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="hidden sm:table-cell">@lang('admin/users.list.id')</th>
                                <th>@lang('admin/users.list.email')</th>
                                <th>@lang('admin/users.list.username')</th>
                                <th class="text-center hidden md:table-cell">@lang('admin/users.list.2fa')</th>
                                <th class="text-center hidden lg:table-cell"><span data-tooltip="@lang('admin/users.list.servers_owned_tooltip')" data-side="top">@lang('admin/users.list.servers_owned')</span></th>
                                <th class="text-center hidden lg:table-cell"><span data-tooltip="@lang('admin/users.list.can_access_tooltip')" data-side="top">@lang('admin/users.list.can_access')</span></th>
                                <th class="hidden sm:table-cell"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr class="align-middle">
                                    <td class="hidden sm:table-cell"><code>{{ $user->id }}</code></td>
                                    <td><a href="{{ route('admin.users.view', $user->id) }}">{{ $user->email }}</a> @if($user->root_admin)<x-icon name="star" class="size-4" />@endif</td>
                                    <td>{{ $user->username }}</td>
                                    <td class="text-center hidden md:table-cell">
                                        @if($user->use_totp)
                                            <x-icon name="lock" class="size-4" />
                                        @else
                                            <x-icon name="unlock" class="size-4" />
                                        @endif
                                    </td>
                                    <td class="text-center hidden lg:table-cell">
                                        <a href="{{ route('admin.servers', ['filter[owner_id]' => $user->id]) }}">{{ $user->servers_count }}</a>
                                    </td>
                                    <td class="text-center hidden lg:table-cell">{{ $user->subuser_of_count }}</td>
                                    <td class="text-center hidden sm:table-cell"><img src="https://cravatar.cn/avatar/{{ md5(strtolower($user->email)) }}?s=100" class="rounded-full h-5" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
            @if($users->hasPages())
                <footer class="flex items-center justify-center">
                    <div class="text-center">{!! $users->appends(['query' => Request::input('query')])->render() !!}</div>
                </footer>
            @endif
        </div>
    </div>
</div>
@endsection
