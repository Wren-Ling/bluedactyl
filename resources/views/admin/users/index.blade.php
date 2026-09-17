@extends('layouts.admin')

@section('title')
    @lang('admin/users.list.title')
@endsection

@section('contentWidth', 'max-w-none')

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
<div class="grid min-w-0 gap-6">
    <div class="col-span-full min-w-0">
        <div class="server-list-card card min-w-0 w-full">
            <header>
                <h3 class="text-lg font-semibold">@lang('admin/users.list.card_title')</h3>
                <div class="card-action">
                    <div class="search01 min-w-0">
                        <form action="{{ route('admin.users') }}" method="GET" class="flex items-center gap-1">
                            <div role="group" class="field min-w-0">
                                <input type="text" name="filter[email]" value="{{ request()->input('filter.email') }}" placeholder="{{ trans('admin/users.list.search_placeholder') }}">
                            </div>
                            <button type="submit" class="btn" data-variant="outline" data-size="sm"><x-icon name="search" class="size-4" /></button>
                            <a href="{{ route('admin.users.new') }}"><button type="button" class="btn rounded-r-md -ml-px" data-size="sm">@lang('admin/users.list.create_new')</button></a>
                        </form>
                    </div>
                </div>
            </header>
            <section class="min-w-0">
                <div class="table-container w-full max-w-full">
                    <table class="table w-full min-w-[760px] table-fixed">
                        <thead>
                            <tr>
                                <th class="w-[6%]">@lang('admin/users.list.id')</th>
                                <th class="w-[32%]">@lang('admin/users.list.email')</th>
                                <th class="w-[18%]">@lang('admin/users.list.username')</th>
                                <th class="text-center">@lang('admin/users.list.2fa')</th>
                                <th class="text-center"><span data-tooltip="@lang('admin/users.list.servers_owned_tooltip')" data-side="top">@lang('admin/users.list.servers_owned')</span></th>
                                <th class="text-center"><span data-tooltip="@lang('admin/users.list.can_access_tooltip')" data-side="top">@lang('admin/users.list.can_access')</span></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr class="align-middle">
                                    <td><code>{{ $user->id }}</code></td>
                                    <td class="break-words"><a href="{{ route('admin.users.view', $user->id) }}">{{ $user->email }}</a> @if($user->root_admin)<x-icon name="star" class="size-4 shrink-0" />@endif</td>
                                    <td class="break-words">{{ $user->username }}</td>
                                    <td class="admin-status-icon text-center">
                                        @if($user->use_totp)
                                            <x-icon name="lock" />
                                        @else
                                            <x-icon name="unlock" />
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.servers', ['filter[owner_id]' => $user->id]) }}">{{ $user->servers_count }}</a>
                                    </td>
                                    <td class="text-center">{{ $user->subuser_of_count }}</td>
                                    <td class="text-center"><img src="https://cravatar.cn/avatar/{{ md5(strtolower($user->email)) }}?s=100" class="rounded-full h-5" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
            @if($users->hasPages())
                <footer class="flex items-center justify-center">
                    @include('admin.partials.pagination', ['paginator' => $users->appends(['filter' => Request::input('filter')])])
                </footer>
            @endif
        </div>
    </div>
</div>
@endsection
