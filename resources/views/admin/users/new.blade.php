@extends('layouts.admin')

@section('title')
    @lang('admin/users.new.title')
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">@lang('admin/users.new.header')</h1>
    <p class="text-sm text-muted-foreground">@lang('admin/users.new.header_desc')</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">@lang('admin/users.new.breadcrumb_admin')</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.users') }}">@lang('admin/users.new.breadcrumb_users')</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>@lang('admin/users.new.breadcrumb_create')</span>
    </nav>
@endsection

@section('content')
<form method="post" class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <div class="card">
            <header>
                <h3 class="text-lg font-semibold">@lang('admin/users.new.identity_title')</h3>
            </header>
            <section>
                <div class="grid gap-6">
                    <div role="group" class="field">
                        <label for="email">@lang('admin/users.new.label_email')</label>
                        <input type="text" autocomplete="off" name="email" value="{{ old('email') }}"  />
                    </div>
                    <div role="group" class="field">
                        <label for="username">@lang('admin/users.new.label_username')</label>
                        <input type="text" autocomplete="off" name="username" value="{{ old('username') }}"  />
                    </div>
                    <div role="group" class="field">
                        <label for="name_first">@lang('admin/users.new.label_first_name')</label>
                        <input type="text" autocomplete="off" name="name_first" value="{{ old('name_first') }}"  />
                    </div>
                    <div role="group" class="field">
                        <label for="name_last">@lang('admin/users.new.label_last_name')</label>
                        <input type="text" autocomplete="off" name="name_last" value="{{ old('name_last') }}"  />
                    </div>
                    <div role="group" class="field">
                        <label>@lang('admin/users.new.label_default_language')</label>
                        <select name="language" class="select">
                                @foreach($languages as $key => $value)
                                    <option value="{{ $key }}" @if(config('app.locale') === $key) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            <p class="text-sm text-muted-foreground">@lang('admin/users.new.language_desc')</p>
                        </div>
                    </div>
            </section>
            <footer>
                {!! csrf_field() !!}
                <input type="submit" value="{{ trans('admin/users.new.submit') }}" class="btn" data-size="sm">
            </footer>
        </div>
    </div>
    <div>
        <div class="card">
            <header>
                <h3 class="text-lg font-semibold">@lang('admin/users.new.permissions_title')</h3>
            </header>
            <section>
                <div class="grid gap-6">
                    <div role="group" class="field">
                        <label for="root_admin">@lang('admin/users.new.label_administrator')</label>
                        <select name="root_admin" class="select">
                                <option value="0">@lang('strings.no')</option>
                                <option value="1">@lang('strings.yes')</option>
                            </select>
                            <p class="text-sm text-muted-foreground">@lang('admin/users.new.administrator_desc')</p>
                        </div>
                    </div>
            </section>
        </div>
    </div>
    <div>
        <div class="card">
            <header>
                <h3 class="text-lg font-semibold">@lang('admin/users.new.password_title')</h3>
            </header>
            <section>
                <div class="grid gap-6">
                    <div class="alert" data-variant="info" role="alert">
                        <p>@lang('admin/users.new.password_info')</p>
                    </div>
                    <div id="gen_pass" class="alert hidden mb-2.5" data-variant="success" role="alert"></div>
                    <div role="group" class="field">
                        <label for="pass">@lang('admin/users.new.label_password')</label>
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
