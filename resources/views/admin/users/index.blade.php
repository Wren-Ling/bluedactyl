@extends('layouts.admin')

@section('title')
    List Users
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">Users</h1>
    <p class="text-sm text-muted-foreground">All registered users on the system.</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">Admin</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>Users</span>
    </nav>
@endsection

@section('content')
<div class="grid gap-6">
    <div class="col-span-full">
        <div class="card">
            <header>
                <h3 class="text-lg font-semibold">User List</h3>
                <div class="card-action">
                    <div class="search01">
                        <form action="{{ route('admin.users') }}" method="GET" class="flex items-center gap-1">
                            <div role="group" class="field">
                                <input type="text" name="filter[email]" value="{{ request()->input('filter.email') }}" placeholder="Search">
                            </div>
                            <button type="submit" class="btn" data-variant="outline" data-size="sm"><x-icon name="search" class="size-4" /></button>
                            <a href="{{ route('admin.users.new') }}"><button type="button" class="btn rounded-r-md -ml-px" data-size="sm">Create New</button></a>
                        </form>
                    </div>
                </div>
            </header>
            <section>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Email</th>
                                <th>Username</th>
                                <th class="text-center">2FA</th>
                                <th class="text-center"><span data-tooltip="Servers that this user is marked as the owner of." data-side="top">Servers Owned</span></th>
                                <th class="text-center"><span data-tooltip="Servers that this user can access because they are marked as a subuser." data-side="top">Can Access</span></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr class="align-middle">
                                    <td><code>{{ $user->id }}</code></td>
                                    <td><a href="{{ route('admin.users.view', $user->id) }}">{{ $user->email }}</a> @if($user->root_admin)<x-icon name="star" class="size-4" />@endif</td>
                                    <td>{{ $user->username }}</td>
                                    <td class="text-center">
                                        @if($user->use_totp)
                                            <x-icon name="lock" class="size-4" />
                                        @else
                                            <x-icon name="unlock" class="size-4" />
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
                    <div class="text-center">{!! $users->appends(['query' => Request::input('query')])->render() !!}</div>
                </footer>
            @endif
        </div>
    </div>
</div>
@endsection
