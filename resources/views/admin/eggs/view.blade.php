@extends('layouts.admin')

@section('contentWidth', 'max-w-6xl')

@section('title')
    Nests &rarr; Egg: {{ $egg->name }}
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">{{ $egg->name }}</h1>
    <p class="text-sm text-muted-foreground">{{ str_limit($egg->description, 50) }}</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">Admin</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.nests') }}">Nests</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.nests.view', $egg->nest->id) }}">{{ $egg->nest->name }}</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>{{ $egg->name }}</span>
    </nav>
@endsection

@section('content')
<div class="grid gap-6">
    <div class="col-span-full">
        <div class="tabs" data-variant="line">
            <nav role="tablist">
                <a href="{{ route('admin.nests.egg.view', $egg->id) }}" role="tab" @if(Route::currentRouteName() === 'admin.nests.egg.view') data-active="true" @endif>Configuration</a>
                <a href="{{ route('admin.nests.egg.variables', $egg->id) }}" role="tab" @if(Route::currentRouteName() === 'admin.nests.egg.variables') data-active="true" @endif>Variables</a>
                <a href="{{ route('admin.nests.egg.scripts', $egg->id) }}" role="tab" @if(Route::currentRouteName() === 'admin.nests.egg.scripts') data-active="true" @endif>Install Script</a>
            </nav>
        </div>
    </div>
</div>
<form action="{{ route('admin.nests.egg.view', $egg->id) }}" enctype="multipart/form-data" method="POST">
    <div class="grid gap-6">
        <div class="col-span-full">
            <div class="card" data-variant="destructive">
                <section>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-2">
                            <div class="field no-margin-bottom">
                                <label for="pName" >Egg File</label>
                                <div>
                                    <input type="file" name="import_file" class="border-0 -ml-2.5" />
                                    <p class="text-sm text-muted-foreground no-margin-bottom">If you would like to replace settings for this Egg by uploading a new JSON file, simply select it here and press "Update Egg". This will not change any existing startup strings or Docker images for existing servers.</p>
                                </div>
                            </div>
                        </div>
                        <div class="md:col-span-1">
                            {!! csrf_field() !!}
                            <button type="submit" name="_method" value="PUT" class="btn ml-auto" data-variant="destructive" data-size="sm">Update Egg</button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</form>
<form action="{{ route('admin.nests.egg.view', $egg->id) }}" method="POST">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="space-y-8">
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">Configuration</h3>
                </header>
                <section class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-6">
                            <div role="group" class="field">
                                <label for="pName" >Name <span class="field-required"></span></label>
                                <input type="text" id="pName" name="name" value="{{ $egg->name }}"  />
                                <p class="text-sm text-muted-foreground">A simple, human-readable name to use as an identifier for this Egg.</p>
                            </div>
                            <div role="group" class="field">
                                <label for="pUuid" >UUID</label>
                                <input type="text" id="pUuid" readonly value="{{ $egg->uuid }}"  />
                                <p class="text-sm text-muted-foreground">This is the globally unique identifier for this Egg which the Daemon uses as an identifier.</p>
                            </div>
                            <div role="group" class="field">
                                <label for="pAuthor" >Author</label>
                                <input type="text" id="pAuthor" readonly value="{{ $egg->author }}"  />
                                <p class="text-sm text-muted-foreground">The author of this version of the Egg. Uploading a new Egg configuration from a different author will change this.</p>
                            </div>
                            <div role="group" class="field">
                                <label for="pDockerImage" >Docker Images <span class="field-required"></span></label>
                                <textarea id="pDockerImages" name="docker_images"  rows="4">{{ implode(PHP_EOL, $images) }}</textarea>
                                <p class="text-sm text-muted-foreground">
                                    The docker images available to servers using this egg. Enter one per line. Users
                                    will be able to select from this list of images if more than one value is provided.
                                    Optionally, a display name may be provided by prefixing the image with the name
                                    followed by a pipe character, and then the image URL. Example: <code>Display Name|ghcr.io/my/egg</code>
                                </p>
                            </div>
                            <div role="group" class="field space-y-2" data-orientation="horizontal">
                                <div class="flex items-center gap-3">
                                    <input id="pForceOutgoingIp" name="force_outgoing_ip" type="checkbox" value="1"  @if($egg->force_outgoing_ip) checked @endif />
                                    <label for="pForceOutgoingIp">Force Outgoing IP</label>
                                </div>
                                <p class="text-sm text-muted-foreground">
                                    Forces all outgoing network traffic to have its Source IP NATed to the IP of the server's primary allocation IP.
                                    Required for certain games to work properly when the Node has multiple public IP addresses.
                                    <br>
                                    <strong>
                                        Enabling this option will disable internal networking for any servers using this egg,
                                        causing them to be unable to internally access other servers on the same node.
                                    </strong>
                                </p>
                            </div>
                        </div>
                        <div class="space-y-6">
                            <div role="group" class="field">
                                <label for="pDescription" >Description</label>
                                <textarea id="pDescription" name="description"  rows="8">{{ $egg->description }}</textarea>
                                <p class="text-sm text-muted-foreground">A description of this Egg that will be displayed throughout the Panel as needed.</p>
                            </div>
                            <div role="group" class="field">
                                <label for="pStartup" >Startup Command <span class="field-required"></span></label>
                                <textarea id="pStartup" name="startup"  rows="8">{{ $egg->startup }}</textarea>
                                <p class="text-sm text-muted-foreground">The default startup command that should be used for new servers using this Egg.</p>
                            </div>
                            <div role="group" class="field">
                                <label for="pConfigFeatures" >Features</label>
                                <input type="text" id="pConfigFeatures" name="features_input" placeholder="Enter features separated by comma or space" value="{{ implode(', ', $egg->features ?? []) }}" />
                                <p class="text-sm text-muted-foreground">Additional features belonging to the egg. Useful for configuring additional panel modifications.</p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <div class="space-y-8">
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">Process Management</h3>
                </header>
                <section class="space-y-6">
                    <div class="alert" data-variant="warning" role="alert">
                        <p>The following configuration options should not be edited unless you understand how this system works. If wrongly modified it is possible for the daemon to break.</p>
                        <p>All fields are required unless you select a separate option from the 'Copy Settings From' dropdown, in which case fields may be left blank to use the values from that Egg.</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-6">
                            <div role="group" class="field">
                                <label for="pConfigFrom" >Copy Settings From</label>
                                <select name="config_from" id="pConfigFrom" class="select">
                                    <option value="">None</option>
                                    @foreach($egg->nest->eggs as $o)
                                        <option value="{{ $o->id }}" {{ ($egg->config_from !== $o->id) ?: 'selected' }}>{{ $o->name }} &lt;{{ $o->author }}&gt;</option>
                                    @endforeach
                                </select>
                                <p class="text-sm text-muted-foreground">If you would like to default to settings from another Egg select it from the menu above.</p>
                            </div>
                            <div role="group" class="field">
                                <label for="pConfigStop" >Stop Command</label>
                                <input type="text" id="pConfigStop" name="config_stop"  value="{{ $egg->config_stop }}" />
                                <p class="text-sm text-muted-foreground">The command that should be sent to server processes to stop them gracefully. If you need to send a <code>SIGINT</code> you should enter <code>^C</code> here.</p>
                            </div>
                            <div role="group" class="field">
                                <label for="pConfigLogs" >Log Configuration</label>
                                <textarea data-action="handle-tabs" id="pConfigLogs" name="config_logs"  rows="5">{{ ! is_null($egg->config_logs) ? json_encode(json_decode($egg->config_logs), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '' }}</textarea>
                                <p class="text-sm text-muted-foreground">This should be a JSON representation of where log files are stored, and whether or not the daemon should be creating custom logs.</p>
                            </div>
                        </div>
                        <div class="space-y-6">
                            <div role="group" class="field">
                                <label for="pConfigFiles" >Configuration Files</label>
                                <textarea data-action="handle-tabs" id="pConfigFiles" name="config_files"  rows="5">{{ ! is_null($egg->config_files) ? json_encode(json_decode($egg->config_files), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '' }}</textarea>
                                <p class="text-sm text-muted-foreground">This should be a JSON representation of configuration files to modify and what parts should be changed.</p>
                            </div>
                            <div role="group" class="field">
                                <label for="pConfigStartup" >Start Configuration</label>
                                <textarea data-action="handle-tabs" id="pConfigStartup" name="config_startup"  rows="5">{{ ! is_null($egg->config_startup) ? json_encode(json_decode($egg->config_startup), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '' }}</textarea>
                                <p class="text-sm text-muted-foreground">This should be a JSON representation of what values the daemon should be looking for when booting a server to determine completion.</p>
                            </div>
                        </div>
                    </div>
                </section>
                <footer>
                    {!! csrf_field() !!}
                    <button type="submit" name="_method" value="PATCH" class="btn ml-auto" data-size="sm">Save</button>
                    <a href="{{ route('admin.nests.egg.export', $egg->id) }}" class="btn ml-auto mr-2.5" data-size="sm">Export</a>
                    <button id="deleteButton" type="submit" name="_method" value="DELETE" class="btn" data-variant="destructive" data-size="sm">
                        <x-icon name="trash-2" class="size-4" />
                    </button>
                </footer>
            </div>
        </div>
    </div>
</form>
@endsection

@section('footer-scripts')
    @parent
    <script>
    $('#deleteButton').on('mouseenter', function (event) {
        $(this).find('i').html(' Delete Egg');
    }).on('mouseleave', function (event) {
        $(this).find('i').html('');
    });
    $('textarea[data-action="handle-tabs"]').on('keydown', function(event) {
        if (event.keyCode === 9) {
            event.preventDefault();

            var curPos = $(this)[0].selectionStart;
            var prepend = $(this).val().substr(0, curPos);
            var append = $(this).val().substr(curPos);

            $(this).val(prepend + '    ' + append);
        }
    });
    $('form').on('submit', function () {
        var val = $('#pConfigFeatures').val();
        if (val && $(this).find('#pConfigFeatures').length) {
            var items = val.split(/[, ]+/).filter(Boolean);
            var form = this;
            items.forEach(function (item) {
                $('<input>').attr({type: 'hidden', name: 'features[]'}).val(item.trim()).appendTo(form);
            });
        }
    });
    </script>
@endsection
