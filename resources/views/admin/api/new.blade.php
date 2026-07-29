@extends('layouts.admin')

@section('title')
    @lang('admin/api.title')
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">@lang('admin/api.header')</h1>
    <p class="text-sm text-muted-foreground">@lang('admin/api.header_subtitle_new')</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">@lang('admin/api.breadcrumb_admin')</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.api.index') }}">@lang('admin/api.breadcrumb_api')</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>@lang('admin/api.breadcrumb_new')</span>
    </nav>
@endsection

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <form method="POST" action="{{ route('admin.api.new') }}" class="md:col-span-2">
            <div>
                <div class="card">
                    <header>
                        <h3 class="text-lg font-semibold">@lang('admin/api.select_permissions')</h3>
                    </header>
                    <section>
                        <div class="table-container">
                            <table class="table">
                                @foreach($resources as $resource)
                                    <tr>
                                        <td class="sm:w-1/4 font-bold">{{ str_replace('_', ' ', title_case($resource)) }}</td>
                                        <td class="sm:w-1/4 text-center">
                                            <input type="radio" id="r_{{ $resource }}" name="r_{{ $resource }}" value="{{ $permissions['r'] }}">
                                            <label for="r_{{ $resource }}">@lang('admin/api.read')</label>
                                        </td>
                                        <td class="sm:w-1/4 text-center">
                                            <input type="radio" id="rw_{{ $resource }}" name="r_{{ $resource }}" value="{{ $permissions['rw'] }}">
                                            <label for="rw_{{ $resource }}">@lang('admin/api.read_write')</label>
                                        </td>
                                        <td class="sm:w-1/4 text-center">
                                            <input type="radio" id="n_{{ $resource }}" name="r_{{ $resource }}" value="{{ $permissions['n'] }}" checked>
                                            <label for="n_{{ $resource }}">@lang('admin/api.none')</label>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </section>
                </div>
            </div>
        </form>
        <div>
            <div class="card">
                <section>
                    <div role="group" class="field">
                        <label for="memoField">@lang('admin/api.description') <span class="field-required"></span></label>
                        <input id="memoField" type="text" name="memo">
                    </div>
                    <p class="text-sm text-muted-foreground">@lang('admin/api.description_help')</p>
                </section>
                <footer>
                    {{ csrf_field() }}
                    <button type="submit" class="btn ml-auto" data-size="sm">@lang('admin/api.create_credentials')</button>
                </footer>
            </div>
        </div>
    </div>
@endsection

@section('footer-scripts')
    @parent
    <script>
    </script>
@endsection
