@extends('layouts.admin')

@section('title')
    Nests &rarr; New Egg
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">New Egg</h1>
    <p class="text-sm text-muted-foreground">Create a new Egg to assign to servers.</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">Admin</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.nests') }}">Nests</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>New Egg</span>
    </nav>
@endsection

@section('content')
<form action="{{ route('admin.nests.egg.new') }}" method="POST">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div>
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">Configuration</h3>
                </header>
                <section>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div role="group" class="field">
                                <label for="pNestId" >Associated Nest</label>
                                <select name="nest_id" id="pNestId" class="select">
                                    @foreach($nests as $nest)
                                        <option value="{{ $nest->id }}" {{ old('nest_id') != $nest->id ?: 'selected' }}>{{ $nest->name }} &lt;{{ $nest->author }}&gt;</option>
                                    @endforeach
                                </select>
                                <p class="text-sm text-muted-foreground">Think of a Nest as a category. You can put multiple Eggs in a nest, but consider putting only Eggs that are related to each other in each Nest.</p>
                            </div>
                            <div role="group" class="field">
                                <label for="pName" >Name</label>
                                <input type="text" id="pName" name="name" value="{{ old('name') }}"  />
                                <p class="text-sm text-muted-foreground">A simple, human-readable name to use as an identifier for this Egg. This is what users will see as their game server type.</p>
                            </div>
                            <div role="group" class="field">
                                <label for="pDescription" >Description</label>
                                <textarea id="pDescription" name="description"  rows="8">{{ old('description') }}</textarea>
                                <p class="text-sm text-muted-foreground">A description of this Egg.</p>
                            </div>
                            <div role="group" class="field" data-orientation="horizontal">
                                <input id="pForceOutgoingIp" name="force_outgoing_ip" type="checkbox" value="1"  {{ \Pterodactyl\Helpers\Utilities::checked('force_outgoing_ip', 0) }} />
                                <label for="pForceOutgoingIp">Force Outgoing IP</label>
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
                        <div>
                            <div role="group" class="field">
                                <label for="pDockerImage" >Docker Images</label>
                                <textarea id="pDockerImages" name="docker_images" rows="4" placeholder="quay.io/pterodactyl/service" >{{ old('docker_images') }}</textarea>
                                <p class="text-sm text-muted-foreground">The docker images available to servers using this egg. Enter one per line. Users will be able to select from this list of images if more than one value is provided.</p>
                            </div>
                            <div role="group" class="field">
                                <label for="pStartup" >Startup Command</label>
                                <textarea id="pStartup" name="startup"  rows="10">{{ old('startup') }}</textarea>
                                <p class="text-sm text-muted-foreground">The default startup command that should be used for new servers created with this Egg. You can change this per-server as needed.</p>
                            </div>
                            <div role="group" class="field">
                                <label for="pConfigFeatures" >Features</label>
                                <input type="text" id="pConfigFeatures" name="features_input" placeholder="Enter features separated by comma or space" />
                                <p class="text-sm text-muted-foreground">Additional features belonging to the egg. Useful for configuring additional panel modifications.</p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <div>
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">Process Management</h3>
                </header>
                <section>
                    <div class="grid gap-6">
                        <div class="col-span-full">
                            <div class="alert" data-variant="warning" role="alert">
                                <p>All fields are required unless you select a separate option from the 'Copy Settings From' dropdown, in which case fields may be left blank to use the values from that option.</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 col-span-full">
                            <div>
                                <div role="group" class="field">
                                    <label for="pConfigFrom" >Copy Settings From</label>
                                    <select name="config_from" id="pConfigFrom" class="select">
                                        <option value="">None</option>
                                    </select>
                                    <p class="text-sm text-muted-foreground">If you would like to default to settings from another Egg select it from the dropdown above.</p>
                                </div>
                                <div role="group" class="field">
                                    <label for="pConfigStop" >Stop Command</label>
                                    <input type="text" id="pConfigStop" name="config_stop"  value="{{ old('config_stop') }}" />
                                    <p class="text-sm text-muted-foreground">The command that should be sent to server processes to stop them gracefully. If you need to send a <code>SIGINT</code> you should enter <code>^C</code> here.</p>
                                </div>
                                <div role="group" class="field">
                                    <label for="pConfigLogs" >Log Configuration</label>
                                    <textarea data-action="handle-tabs" id="pConfigLogs" name="config_logs"  rows="6">{{ old('config_logs') }}</textarea>
                                    <p class="text-sm text-muted-foreground">This should be a JSON representation of where log files are stored, and whether or not the daemon should be creating custom logs.</p>
                                </div>
                            </div>
                            <div>
                                <div role="group" class="field">
                                    <label for="pConfigFiles" >Configuration Files</label>
                                    <textarea data-action="handle-tabs" id="pConfigFiles" name="config_files"  rows="6">{{ old('config_files') }}</textarea>
                                    <p class="text-sm text-muted-foreground">This should be a JSON representation of configuration files to modify and what parts should be changed.</p>
                                </div>
                                <div role="group" class="field">
                                    <label for="pConfigStartup" >Start Configuration</label>
                                    <textarea data-action="handle-tabs" id="pConfigStartup" name="config_startup"  rows="6">{{ old('config_startup') }}</textarea>
                                    <p class="text-sm text-muted-foreground">This should be a JSON representation of what values the daemon should be looking for when booting a server to determine completion.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <footer>
                    {!! csrf_field() !!}
                    <button type="submit" class="btn ml-auto" data-size="sm">Create</button>
                </footer>
            </div>
        </div>
    </div>
</form>
@endsection

@section('footer-scripts')
    @parent
    {!! Theme::js('vendor/lodash/lodash.js') !!}
    <script>
    $(document).ready(function() {
        $('#pNestId').change();
    });
    $('#pNestId').on('change', function (event) {
        $('#pConfigFrom').html('<option value="">None</option>');
        var eggs = _.get(Pyrodactyl.nests, $(this).val() + '.eggs', []);
        eggs.forEach(function (item) {
            $('#pConfigFrom').append($('<option>', {
                value: item.id,
                text: item.name + ' <' + item.author + '>',
            }));
        });
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
        if (val) {
            var items = val.split(/[, ]+/).filter(Boolean);
            var form = this;
            items.forEach(function (item) {
                $('<input>').attr({type: 'hidden', name: 'features[]'}).val(item.trim()).appendTo(form);
            });
        }
    });
    </script>
@endsection
