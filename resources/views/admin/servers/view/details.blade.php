@extends('layouts.admin')

@section('title')
    Server — {{ $server->name }}: Details
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">{{ $server->name }}</h1>
    <p class="text-sm text-muted-foreground">Edit details for this server including owner and container.</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">Admin</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.servers') }}">Servers</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.servers.view', $server->id) }}">{{ $server->name }}</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>Details</span>
    </nav>
@endsection

@section('content')
@include('admin.servers.partials.navigation')
<div class="grid gap-6">
    <div class="col-span-full">
        <div class="card">
            <header>
                <h3 class="text-lg font-semibold">Base Information</h3>
            </header>
            <section>
                <form action="{{ route('admin.servers.view.details', $server->id) }}" method="POST">
                    <div class="grid gap-6">
                        <div role="group" class="field">
                            <label for="name" >Server Name <span class="field-required"></span></label>
                            <input type="text" name="name" value="{{ old('name', $server->name) }}"  />
                            <p class="text-sm text-muted-foreground">Character limits: <code>a-zA-Z0-9_-</code> and <code>[Space]</code>.</p>
                        </div>
                        <div role="group" class="field">
                            <label for="external_id" >External Identifier</label>
                            <input type="text" name="external_id" value="{{ old('external_id', $server->external_id) }}"  />
                            <p class="text-sm text-muted-foreground">Leave empty to not assign an external identifier for this server. The external ID should be unique to this server and not be in use by any other servers.</p>
                        </div>
                        <div role="group" class="field">
                            <label for="pUserId" >Server Owner <span class="field-required"></span></label>
                            <select name="owner_id" class="select" id="pUserId">
                                <option value="{{ $server->owner_id }}" selected>{{ $server->user->email }}</option>
                            </select>
                            <p class="text-sm text-muted-foreground">You can change the owner of this server by changing this field to an email matching another use on this system. If you do this a new daemon security token will be generated automatically.</p>
                        </div>
                        <div role="group" class="field">
                            <label for="description" >Server Description</label>
                            <textarea name="description" rows="3" >{{ old('description', $server->description) }}</textarea>
                            <p class="text-sm text-muted-foreground">A brief description of this server.</p>
                        </div>
                    </div>
                </form>
            </section>
            <footer>
                {!! csrf_field() !!}
                {!! method_field('PATCH') !!}
                <input type="submit" class="btn" data-size="sm" value="Update Details" />
            </footer>
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>
    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }


    </script>
@endsection
