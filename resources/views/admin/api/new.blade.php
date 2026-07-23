@extends('layouts.admin')

@section('title')
    Application API
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">Application API</h1>
    <p class="text-sm text-muted-foreground">Create a new application API key.</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">Admin</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.api.index') }}">Application API</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>New Credentials</span>
    </nav>
@endsection

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <form method="POST" action="{{ route('admin.api.new') }}" class="md:col-span-2">
            <div>
                <div class="card">
                    <header>
                        <h3 class="text-lg font-semibold">Select Permissions</h3>
                    </header>
                    <section>
                        <div class="table-container">
                            <table class="table">
                                @foreach($resources as $resource)
                                    <tr>
                                        <td class="sm:w-1/4 font-bold">{{ str_replace('_', ' ', title_case($resource)) }}</td>
                                        <td class="sm:w-1/4 text-center">
                                            <input type="radio" id="r_{{ $resource }}" name="r_{{ $resource }}" value="{{ $permissions['r'] }}">
                                            <label for="r_{{ $resource }}">Read</label>
                                        </td>
                                        <td class="sm:w-1/4 text-center">
                                            <input type="radio" id="rw_{{ $resource }}" name="r_{{ $resource }}" value="{{ $permissions['rw'] }}">
                                            <label for="rw_{{ $resource }}">Read &amp; Write</label>
                                        </td>
                                        <td class="sm:w-1/4 text-center">
                                            <input type="radio" id="n_{{ $resource }}" name="r_{{ $resource }}" value="{{ $permissions['n'] }}" checked>
                                            <label for="n_{{ $resource }}">None</label>
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
                        <label for="memoField">Description <span class="field-required"></span></label>
                        <input id="memoField" type="text" name="memo">
                    </div>
                    <p class="text-sm text-muted-foreground">Once you have assigned permissions and created this set of credentials you will be unable to come back and edit it. If you need to make changes down the road you will need to create a new set of credentials.</p>
                </section>
                <footer>
                    {{ csrf_field() }}
                    <button type="submit" class="btn ml-auto" data-size="sm">Create Credentials</button>
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
