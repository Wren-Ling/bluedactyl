@extends('layouts.admin')

@section('title')
    Manage User: {{ $user->username }}
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">{{ $user->name_first }} {{ $user->name_last}}</h1>
    <p class="text-sm text-muted-foreground">{{ $user->username }}</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">Admin</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.users') }}">Users</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>{{ $user->username }}</span>
    </nav>
@endsection

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <form action="{{ route('admin.users.view', $user->id) }}" method="post" class="contents">
        <div>
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">Identity</h3>
                </header>
                <section>
                    <div class="grid gap-6">
                        <div role="group" class="field">
                            <label for="email">Email</label>
                            <input type="email" name="email" value="{{ $user->email }}" >
                        </div>
                        <div role="group" class="field">
                            <label for="registered">Username</label>
                            <input type="text" name="username" value="{{ $user->username }}" >
                        </div>
                        <div role="group" class="field">
                            <label for="registered">Client First Name</label>
                            <input type="text" name="name_first" value="{{ $user->name_first }}" >
                        </div>
                        <div role="group" class="field">
                            <label for="registered">Client Last Name</label>
                            <input type="text" name="name_last" value="{{ $user->name_last }}" >
                        </div>
                        <div role="group" class="field">
                            <label>Default Language</label>
                            <select name="language" class="select">
                                    @foreach($languages as $key => $value)
                                        <option value="{{ $key }}" @if($user->language === $key) selected @endif>{{ $value }}</option>
                                    @endforeach
                                </select>
                                <p class="text-sm text-muted-foreground">The default language to use when rendering the Panel for this user.</p>
                            </div>
                        </div>
                </section>
                <footer>
                    {!! csrf_field() !!}
                    {!! method_field('PATCH') !!}
                    <input type="submit" value="Update User" class="btn" data-size="sm">
                </footer>
            </div>
        </div>
        <div>
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">Password</h3>
                </header>
                <section>
                    <div class="grid gap-6">
                        <div class="alert hidden mb-2.5" data-variant="success" role="alert" id="gen_pass"></div>
                        <div role="group" class="field">
                            <label for="password">Password <span class="field-optional"></span></label>
                            <input type="password" id="password" name="password" >
                            <p class="text-sm text-muted-foreground">Leave blank to keep this user's password the same. User will not receive any notification if password is changed.</p>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <div>
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">Permissions</h3>
                </header>
                <section>
                    <div class="grid gap-6">
                        <div role="group" class="field">
                            <label for="root_admin">Administrator</label>
                            <select name="root_admin" class="select">
                                    <option value="0">@lang('strings.no')</option>
                                    <option value="1" {{ $user->root_admin ? 'selected' : '' }}>@lang('strings.yes')</option>
                                </select>
                                <p class="text-sm text-muted-foreground">Setting this to 'Yes' gives a user full administrative access.</p>
                            </div>
                        </div>
                </section>
            </div>
        </div>
    </form>
    <div class="md:col-span-2">
        <div class="card" data-variant="destructive">
            <header>
                <h3 class="text-lg font-semibold">Delete User</h3>
            </header>
            <section>
                <p>There must be no servers associated with this account in order for it to be deleted.</p>
            </section>
            <footer>
                <form action="{{ route('admin.users.view', $user->id) }}" method="POST">
                    {!! csrf_field() !!}
                    {!! method_field('DELETE') !!}
                    <input id="delete" type="submit" class="btn ml-auto" data-size="sm" data-variant="destructive" {{ $user->servers->count() < 1 ?: 'disabled' }} value="Delete User" />
                </form>
            </footer>
        </div>
    </div>
</div>
@endsection
