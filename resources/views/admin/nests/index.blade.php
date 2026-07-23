@extends('layouts.admin')

@section('title')
    Nests
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">Nests</h1>
    <p class="text-sm text-muted-foreground">All nests currently available on this system.</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">Admin</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>Nests</span>
    </nav>
@endsection

@section('content')
<div class="grid gap-6">
    <div class="col-span-full">
        <div class="alert" data-variant="destructive" role="alert">
            Eggs are a powerful feature of Pterodactyl Panel that allow for extreme flexibility and configuration. Please note that while powerful, modifying an egg wrongly can very easily brick your servers and cause more problems. Please avoid editing our default eggs — those provided by <code>support@pterodactyl.io</code> — unless you are absolutely sure of what you are doing.
        </div>
    </div>
    <div class="col-span-full">
        <div class="card">
            <header>
                <h3 class="text-lg font-semibold">Configured Nests</h3>
                <div class="card-action">
                    <div class="flex items-center gap-2">
                        <a href="#" class="btn" data-size="sm" onclick="document.getElementById('importServiceOptionModal').showModal()" role="button"><x-icon name="upload" class="size-4" /> Import Egg</a>
                        <a href="#" class="btn" data-size="sm" onclick="document.getElementById('importServiceOptionFromUrlModal').showModal()" role="button"><x-icon name="upload" class="size-4" /> Import Egg from URL</a>
                        <a href="{{ route('admin.nests.new') }}" class="btn" data-size="sm">Create New</a>
                    </div>
                </div>
            </header>
            <section>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th class="text-center">Eggs</th>
                            <th class="text-center">Servers</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($nests as $nest)
                            <tr>
                                <td class="middle"><code>{{ $nest->id }}</code></td>
                                <td class="middle"><a href="{{ route('admin.nests.view', $nest->id) }}" data-tooltip="{{ $nest->author }}" data-side="right">{{ $nest->name }}</a></td>
                                <td class="w-1/2 middle">{{ $nest->description }}</td>
                                <td class="text-center middle">{{ $nest->eggs_count }}</td>
                                <td class="text-center middle">{{ $nest->servers_count }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</div>
<dialog class="dialog" id="importServiceOptionModal" onclick="if (event.target === this) this.close()">
    <div class="sm:max-w-sm">
        <header>
            <h4 class="text-lg font-semibold">Import an Egg</h4>
        </header>
        <form action="{{ route('admin.nests.egg.import') }}" enctype="multipart/form-data" method="POST" id="importEggForm">
            <section>
                <div role="group" class="field">
                    <label class="font-medium" for="pImportFile">Egg File <span class="field-required"></span></label>
                    <input id="pImportFile" type="file" name="import_file" accept="application/json" />
                    <p class="text-sm text-muted-foreground">Select the <code>.json</code> file for the new egg that you wish to import.</p>
                </div>
                <div role="group" class="field">
                    <label class="font-medium" for="pImportToNest">Associated Nest <span class="field-required"></span></label>
                    <select id="pImportToNest" name="import_to_nest" class="select">
                        @foreach($nests as $nest)
                            <option value="{{ $nest->id }}">{{ $nest->name }} &lt;{{ $nest->author }}&gt;</option>
                        @endforeach
                    </select>
                    <p class="text-sm text-muted-foreground">Select the nest that this egg will be associated with from the dropdown. If you wish to associate it with a new nest you will need to create that nest before continuing.</p>
                </div>
                {{ csrf_field() }}
            </section>
        </form>
        <footer>
            <button type="button" class="btn" data-variant="outline" onclick="this.closest('dialog').close()">Cancel</button>
            <button type="submit" class="btn" form="importEggForm">Import</button>
        </footer>
        <button type="button" class="btn" data-variant="ghost" data-size="icon-sm" aria-label="Close" onclick="this.closest('dialog').close()"><x-icon name="x" class="size-4" /></button>
    </div>
</dialog>
<dialog class="dialog" id="importServiceOptionFromUrlModal" onclick="if (event.target === this) this.close()">
    <div class="sm:max-w-sm">
        <header>
            <h4 class="text-lg font-semibold">Import an Egg</h4>
        </header>
        <form action="{{ route('admin.nests.egg.import_url') }}" method="POST" id="importEggUrlForm">
            <section>
                <div role="group" class="field">
                    <label class="font-medium" for="pImportUrl">Egg URL <span class="field-required"></span></label>
                    <input id="pImportUrl" type="url" name="import_file_url" />
                    <p class="text-sm text-muted-foreground">Type the URL of the file for the new egg that you wish to import.</p>
                </div>
                <div role="group" class="field">
                    <label class="font-medium" for="pImportToNestUrl">Associated Nest <span class="field-required"></span></label>
                    <select id="pImportToNestUrl" name="import_to_nest" class="select">
                        @foreach($nests as $nest)
                            <option value="{{ $nest->id }}">{{ $nest->name }} &lt;{{ $nest->author }}&gt;</option>
                        @endforeach
                    </select>
                    <p class="text-sm text-muted-foreground">Select the nest that this egg will be associated with from the dropdown. If you wish to associate it with a new nest you will need to create that nest before continuing.</p>
                </div>
                {{ csrf_field() }}
            </section>
        </form>
        <footer>
            <button type="button" class="btn" data-variant="outline" onclick="this.closest('dialog').close()">Cancel</button>
            <button type="submit" class="btn" form="importEggUrlForm">Import</button>
        </footer>
        <button type="button" class="btn" data-variant="ghost" data-size="icon-sm" aria-label="Close" onclick="this.closest('dialog').close()"><x-icon name="x" class="size-4" /></button>
    </div>
</dialog>
@endsection

