@extends('layouts.admin')

@section('title')
    Nests &rarr; {{ $nest->name }}
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">{{ $nest->name }}</h1>
    <p class="text-sm text-muted-foreground">{{ str_limit($nest->description, 50) }}</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">Admin</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.nests') }}">Nests</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>{{ $nest->name }}</span>
    </nav>
@endsection

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <form action="{{ route('admin.nests.view', $nest->id) }}" method="POST">
        <div class="card">
            <section>
                <div role="group" class="field">
                    <label >Name <span class="field-required"></span></label>
                    <input type="text" name="name"  value="{{ $nest->name }}" />
                    <p class="text-sm text-muted-foreground">This should be a descriptive category name that encompasses all of the options within the service.</p>
                </div>
                <div role="group" class="field">
                    <label >Description</label>
                    <textarea name="description"  rows="7">{{ $nest->description }}</textarea>
                </div>
            </section>
            <footer>
                {!! csrf_field() !!}
                <button type="submit" name="_method" value="PATCH" class="btn ml-auto" data-size="sm">Save</button>
                <button id="deleteButton" type="submit" name="_method" value="DELETE" class="btn" data-variant="destructive" data-size="sm"><x-icon name="trash-2" class="size-4" /></button>
            </footer>
        </div>
    </form>
    <div>
        <div class="card">
            <section>
                <div role="group" class="field">
                    <label >Nest ID</label>
                        <input type="text" readonly  value="{{ $nest->id }}" />
                        <p class="text-sm text-muted-foreground">A unique ID used for identification of this nest internally and through the API.</p>
                    </div>
                    <div role="group" class="field">
                        <label >Author</label>
                        <input type="text" readonly  value="{{ $nest->author }}" />
                        <p class="text-sm text-muted-foreground">The author of this service option. Please direct questions and issues to them unless this is an official option authored by <code>support@pterodactyl.io</code>.</p>
                    </div>
                    <div role="group" class="field">
                        <label >UUID</label>
                        <input type="text" readonly  value="{{ $nest->uuid }}" />
                        <p class="text-sm text-muted-foreground">A UUID that all servers using this option are assigned for identification purposes.</p>
                </div>
            </section>
        </div>
    </div>
</div>
<div class="grid gap-6">
    <div class="col-span-full">
        <div class="card">
            <header>
                <h3 class="text-lg font-semibold">Nest Eggs</h3>
            </header>
            <section class="table-container no-padding">
                <table class="table table-fixed w-full">
                    <colgroup>
                        <col class="w-[60px]">
                        <col class="w-[180px]">
                        <col>
                        <col class="w-[80px]">
                        <col class="w-[40px]">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th class="text-center">Servers</th>
                            <th class="text-center"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($nest->eggs as $egg)
                        <tr>
                            <td class="align-middle"><code>{{ $egg->id }}</code></td>
                            <td class="align-middle"><a href="{{ route('admin.nests.egg.view', $egg->id) }}" data-tooltip="{{ $egg->author }}" data-side="right">{{ $egg->name }}</a></td>
                            <td class="align-middle break-words"><span class="line-clamp-2" title="{{ $egg->description }}">{{ $egg->description }}</span></td>
                            <td class="text-center align-middle"><code>{{ $egg->servers->count() }}</code></td>
                            <td class="align-middle">
                                <a href="{{ route('admin.nests.egg.export', ['egg' => $egg->id]) }}"><x-icon name="download" class="size-4" /></a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </section>
            <footer>
                <a href="{{ route('admin.nests.egg.new') }}"><button class="btn ml-auto" data-size="sm">New Egg</button></a>
            </footer>
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>
        $('#deleteButton').on('mouseenter', function (event) {
            $(this).find('i').html(' Delete Nest');
        }).on('mouseleave', function (event) {
            $(this).find('i').html('');
        });
    </script>
@endsection
