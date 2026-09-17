@extends('layouts.admin')

@section('title')
    @lang('admin/nests.nest_new.title')
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">@lang('admin/nests.nest_new.title')</h1>
    <p class="text-sm text-muted-foreground">@lang('admin/nests.nest_new.header_description')</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">@lang('admin/nests.admin')</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.nests') }}">@lang('admin/nests.nests_breadcrumb')</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>@lang('admin/nests.nest_new.title')</span>
    </nav>
@endsection

@section('content')
<form action="{{ route('admin.nests.new') }}" method="POST">
    <div class="grid gap-6">
        <div class="col-span-full">
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">@lang('admin/nests.nest_new.card_title')</h3>
                </header>
                <section>
                    <div role="group" class="field">
                        <label class="font-medium">@lang('admin/nests.nest_new.name_label')</label>
                        <input type="text" name="name"  value="{{ old('name') }}" />
                        <p class="text-muted-foreground"><small>{!! trans('admin/nests.nest_new.name_hint') !!}</small></p>
                    </div>
                    <div role="group" class="field">
                        <label class="font-medium">@lang('admin/nests.nest_new.description_label')</label>
                        <textarea name="description"  rows="6">{{ old('description') }}</textarea>
                    </div>
                </section>
                <footer class="flex">
                    {!! csrf_field() !!}
                    <button type="submit" class="btn ml-auto">@lang('admin/nests.nest_new.save')</button>
                </footer>
            </div>
        </div>
    </div>
</form>
@endsection
