@extends('layouts.admin')

@section('title')
    @lang('admin/users.view.title', ['name' => $user->username])
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">{{ $user->name_first }} {{ $user->name_last}}</h1>
    <p class="text-sm text-muted-foreground">{{ $user->username }}</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">@lang('admin/users.view.breadcrumb_admin')</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.users') }}">@lang('admin/users.view.breadcrumb_users')</a>
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
                    <h3 class="text-lg font-semibold">@lang('admin/users.view.identity_title')</h3>
                </header>
                <section>
                    <div class="grid gap-6">
                        <div role="group" class="field">
                            <label for="email">@lang('admin/users.view.label_email')</label>
                            <input type="email" name="email" value="{{ $user->email }}" >
                        </div>
                        <div role="group" class="field">
                            <label for="registered">@lang('admin/users.view.label_username')</label>
                            <input type="text" name="username" value="{{ $user->username }}" >
                        </div>
                        <div role="group" class="field">
                            <label for="registered">@lang('admin/users.view.label_first_name')</label>
                            <input type="text" name="name_first" value="{{ $user->name_first }}" >
                        </div>
                        <div role="group" class="field">
                            <label for="registered">@lang('admin/users.view.label_last_name')</label>
                            <input type="text" name="name_last" value="{{ $user->name_last }}" >
                        </div>
                        <div role="group" class="field">
                            <label>@lang('admin/users.view.label_default_language')</label>
                            <select name="language" class="select">
                                    @foreach($languages as $key => $value)
                                        <option value="{{ $key }}" @if($user->language === $key) selected @endif>{{ $value }}</option>
                                    @endforeach
                                </select>
                                <p class="text-sm text-muted-foreground">@lang('admin/users.view.language_desc')</p>
                            </div>
                        </div>
                </section>
                <footer>
                    {!! csrf_field() !!}
                    {!! method_field('PATCH') !!}
                    <input type="submit" value="{{ trans('admin/users.view.submit') }}" class="btn" data-size="sm">
                </footer>
            </div>
        </div>
        <div>
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">@lang('admin/users.view.password_title')</h3>
                </header>
                <section>
                    <div class="grid gap-6">
                        <div class="alert hidden mb-2.5" data-variant="success" role="alert" id="gen_pass"></div>
                        <div role="group" class="field">
                            <label for="password">@lang('admin/users.view.label_password') <span class="field-optional"></span></label>
                            <input type="password" id="password" name="password" >
                            <p class="text-sm text-muted-foreground">@lang('admin/users.view.password_desc')</p>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <div>
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">@lang('admin/users.view.permissions_title')</h3>
                </header>
                <section>
                    <div class="grid gap-6">
                        <div role="group" class="field">
                            <label for="root_admin">@lang('admin/users.view.label_administrator')</label>
                            <select name="root_admin" class="select">
                                    <option value="0">@lang('strings.no')</option>
                                    <option value="1" {{ $user->root_admin ? 'selected' : '' }}>@lang('strings.yes')</option>
                                </select>
                                <p class="text-sm text-muted-foreground">@lang('admin/users.view.administrator_desc')</p>
                            </div>
                        </div>
                </section>
            </div>
        </div>
    </form>
    <div class="md:col-span-2">
        <div class="card" data-variant="destructive">
            <header>
                <h3 class="text-lg font-semibold">@lang('admin/users.view.delete_title')</h3>
            </header>
            <section>
                <p>@lang('admin/users.view.delete_desc')</p>
            </section>
            <footer>
                <form action="{{ route('admin.users.view', $user->id) }}" method="POST">
                    {!! csrf_field() !!}
                    {!! method_field('DELETE') !!}
                    <input id="delete" type="submit" class="btn ml-auto" data-size="sm" data-variant="destructive" {{ $user->servers->count() < 1 ?: 'disabled' }} value="{{ trans('admin/users.view.delete_submit') }}" />
                </form>
            </footer>
        </div>
    </div>
</div>
@endsection
