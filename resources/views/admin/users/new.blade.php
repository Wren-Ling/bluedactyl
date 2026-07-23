@extends('layouts.admin')

@section('title')
    Create User
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">Create User</h1>
    <p class="text-sm text-muted-foreground">Add a new user to the system.</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">Admin</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.users') }}">Users</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>Create</span>
    </nav>
@endsection

@section('content')
<form method="post" class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <div class="card">
            <header>
                <h3 class="text-lg font-semibold">Identity</h3>
            </header>
            <section>
                <div class="grid gap-6">
                    <div role="group" class="field">
                        <label for="email">Email</label>
                        <input type="text" autocomplete="off" name="email" value="{{ old('email') }}"  />
                    </div>
                    <div role="group" class="field">
                        <label for="username">Username</label>
                        <input type="text" autocomplete="off" name="username" value="{{ old('username') }}"  />
                    </div>
                    <div role="group" class="field">
                        <label for="name_first">Client First Name</label>
                        <input type="text" autocomplete="off" name="name_first" value="{{ old('name_first') }}"  />
                    </div>
                    <div role="group" class="field">
                        <label for="name_last">Client Last Name</label>
                        <input type="text" autocomplete="off" name="name_last" value="{{ old('name_last') }}"  />
                    </div>
                    <div role="group" class="field">
                        <label>Default Language</label>
                        <select name="language" class="select">
                                @foreach($languages as $key => $value)
                                    <option value="{{ $key }}" @if(config('app.locale') === $key) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            <p class="text-sm text-muted-foreground">The default language to use when rendering the Panel for this user.</p>
                        </div>
                    </div>
            </section>
            <footer>
                {!! csrf_field() !!}
                <input type="submit" value="Create User" class="btn" data-size="sm">
            </footer>
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
                                <option value="1">@lang('strings.yes')</option>
                            </select>
                            <p class="text-sm text-muted-foreground">Setting this to 'Yes' gives a user full administrative access.</p>
                        </div>
                    </div>
            </section>
        </div>
    </div>
    <div>
        <div class="card">
            <header>
                <h3 class="text-lg font-semibold">Password</h3>
            </header>
            <section>
                <div class="grid gap-6">
                    <div class="alert" data-variant="info" role="alert">
                        <p>Providing a user password is optional. New user emails prompt users to create a password the first time they login. If a password is provided here you will need to find a different method of providing it to the user.</p>
                    </div>
                    <div id="gen_pass" class="alert hidden mb-2.5" data-variant="success" role="alert"></div>
                    <div role="group" class="field">
                        <label for="pass">Password</label>
                        <input type="password" name="password"  />
                    </div>
                </div>
            </section>
        </div>
    </div>
</form>
@endsection

@section('footer-scripts')
    @parent
    <script>$("#gen_pass_bttn").click(function (event) {
            event.preventDefault();
            $.ajax({
                type: "GET",
                url: "/password-gen/12",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
               },
                success: function(data) {
                    $("#gen_pass").html('<strong>Generated Password:</strong> ' + data).slideDown();
                    $('input[name="password"], input[name="password_confirmation"]').val(data);
                    return false;
                }
            });
            return false;
        });
    </script>
@endsection
