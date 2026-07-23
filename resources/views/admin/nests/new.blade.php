@extends('layouts.admin')

@section('title')
    New Nest
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">New Nest</h1>
    <p class="text-sm text-muted-foreground">Configure a new nest to deploy to all nodes.</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">Admin</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.nests') }}">Nests</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>New</span>
    </nav>
@endsection

@section('content')
<form action="{{ route('admin.nests.new') }}" method="POST">
    <div class="grid gap-6">
        <div class="col-span-full">
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">New Nest</h3>
                </header>
                <section>
                    <div role="group" class="field">
                        <label class="font-medium">Name</label>
                        <input type="text" name="name"  value="{{ old('name') }}" />
                        <p class="text-muted-foreground"><small>This should be a descriptive category name that encompasses all of the eggs within the nest.</small></p>
                    </div>
                    <div role="group" class="field">
                        <label class="font-medium">Description</label>
                        <textarea name="description"  rows="6">{{ old('description') }}</textarea>
                    </div>
                </section>
                <footer class="flex">
                    {!! csrf_field() !!}
                    <button type="submit" class="btn ml-auto">Save</button>
                </footer>
            </div>
        </div>
    </div>
</form>
@endsection
