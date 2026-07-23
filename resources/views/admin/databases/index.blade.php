@extends('layouts.admin')

@section('title')
    Database Hosts
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">Database Hosts</h1>
    <p class="text-sm text-muted-foreground">Database hosts that servers can have databases created on.</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">Admin</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>Database Hosts</span>
    </nav>
@endsection

@section('content')
<div class="grid gap-6">
    <div class="col-span-full">
        <div class="card">
            <header>
                <h3 class="text-lg font-semibold">Host List</h3>
                <div class="card-action">
                    <button class="btn" data-size="sm" onclick="document.getElementById('newHostModal').showModal()">Create New</button>
                </div>
            </header>
            <section>
                <div class="table-container">
                    <table class="table">
                        <tbody>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Host</th>
                                <th>Port</th>
                                <th>Username</th>
                                <th class="text-center">Databases</th>
                                <th class="text-center">Node</th>
                            </tr>
                            @foreach ($hosts as $host)
                                <tr>
                                    <td><code>{{ $host->id }}</code></td>
                                    <td><a href="{{ route('admin.databases.view', $host->id) }}">{{ $host->name }}</a></td>
                                    <td><code>{{ $host->host }}</code></td>
                                    <td><code>{{ $host->port }}</code></td>
                                    <td>{{ $host->username }}</td>
                                    <td class="text-center">{{ $host->databases_count }}</td>
                                    <td class="text-center">
                                        @if(! is_null($host->node))
                                            <a href="{{ route('admin.nodes.view', $host->node->id) }}">{{ $host->node->name }}</a>
                                        @else
                                            <span class="badge">None</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</div>

<dialog class="dialog" id="newHostModal" tabindex="-1">
    <header>
        <button type="button" class="btn" data-variant="ghost" onclick="this.closest('dialog').close()" aria-label="Close"><x-icon name="x" class="size-4" /></button>
        <h4 class="text-lg font-semibold">Create New Database Host</h4>
    </header>
    <form action="{{ route('admin.databases') }}" method="POST" id="databaseHostForm">
        <section>
            <div id="testResult" class="hidden"></div>

            <div role="group" class="field">
                <label for="pName">Name</label>
                <input type="text" name="name" id="pName" value="{{ old('name') }}" />
                <p class="text-sm text-muted-foreground">A short identifier used to distinguish this location from others. Must be between 1 and 60 characters, for example, <code>us.nyc.lvl3</code>.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div role="group" class="field">
                    <label for="pHost">Host</label>
                    <input type="text" name="host" id="pHost" value="{{ old('host') }}" />
                    <p class="text-sm text-muted-foreground">The IP address or FQDN that should be used when attempting to connect to this MySQL host <em>from the panel</em> to add new databases.</p>
                </div>
                <div role="group" class="field">
                    <label for="pPort">Port</label>
                    <input type="text" name="port" id="pPort" value="{{ old('port', '3306') }}" />
                    <p class="text-sm text-muted-foreground">The port that MySQL is running on for this host.</p>
                </div>
                <div role="group" class="field">
                    <label for="pUsername">Username</label>
                    <input type="text" name="username" id="pUsername" value="{{ old('username') }}" />
                    <p class="text-sm text-muted-foreground">The username of an account that has enough permissions to create new users and databases on the system.</p>
                </div>
                <div role="group" class="field">
                    <label for="pPassword">Password</label>
                    <input type="password" name="password" id="pPassword" />
                    <p class="text-sm text-muted-foreground">The password to the account defined.</p>
                </div>
            </div>
            <div role="group" class="field">
                <label for="pNodeId">Linked Node</label>
                <select name="node_id" id="pNodeId" class="select">
                    <option value="">None</option>
                    @foreach($locations as $location)
                        <optgroup label="{{ $location->short }}">
                            @foreach($location->nodes as $node)
                                <option value="{{ $node->id }}" {{ old('node_id') == $node->id ? 'selected' : '' }}>{{ $node->name }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                <p class="text-sm text-muted-foreground">This setting does nothing other than default to this database host when adding a database to a server on the selected node.</p>
            </div>
            {!! csrf_field() !!}
        </section>
    </form>
    <footer>
        <p class="text-sm text-destructive text-left">The account defined for this database host <strong>must</strong> have the <code>WITH GRANT OPTION</code> permission. If the defined account does not have this permission requests to create databases <em>will</em> fail. <strong>Do not use the same account details for MySQL that you have defined for this panel.</strong></p>
        <button type="button" class="btn" data-size="sm" data-variant="outline" onclick="this.closest('dialog').close()">Cancel</button>
        <button type="button" id="testDatabaseBtn" class="btn" data-size="sm">Test Database</button>
        <button type="submit" class="btn" data-size="sm" form="databaseHostForm">Create</button>
    </footer>
</dialog>
@endsection

@section('footer-scripts')
    @parent
    <script>
        // select2 removed in favor of Basecoat native select

        // Test database connection
        $('#testDatabaseBtn').on('click', function() {
            const button = $(this);
            const originalText = button.text();
            const resultDiv = $('#testResult');

            // Show loading state
            button.prop('disabled', true).text('Testing...');
            resultDiv.hide().removeClass('alert').attr('data-variant', '').html('');

            // Get form data
            const formData = {
                host: $('#pHost').val(),
                port: $('#pPort').val(),
                username: $('#pUsername').val(),
                password: $('#pPassword').val(),
                _token: '{{ csrf_token() }}'
            };

            // Validate required fields
            if (!formData.host || !formData.port || !formData.username || !formData.password) {
                resultDiv.html('<strong>Error:</strong> Please fill in all required database connection fields.').addClass('alert').attr('data-variant', 'destructive').show();
                button.prop('disabled', false).text(originalText);
                return;
            }

            // Simple AJAX request
            $.ajax({
                url: '{{ route('admin.databases.test') }}',
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        resultDiv.html('<strong>Success:</strong> ' + response.message).addClass('alert').attr('data-variant', 'success').show();
                    } else {
                        resultDiv.html('<strong>Error:</strong> ' + response.message).addClass('alert').attr('data-variant', 'destructive').show();
                    }
                },
                error: function(xhr) {
                    let message = 'An unexpected error occurred.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    } else if (xhr.statusText) {
                        message = xhr.statusText;
                    }
                    resultDiv.html('<strong>Error:</strong> ' + message).addClass('alert').attr('data-variant', 'destructive').show();
                },
                complete: function() {
                    button.prop('disabled', false).text(originalText);
                }
            });
        });

        // Clear test results when modal is opened
        document.getElementById('newHostModal').addEventListener('show', function() {
            $('#testResult').hide().empty();
        });

        // Re-open modal if there are old inputs (form was submitted but had errors)
        @if($errors->any())
            $(document).ready(function() {
                document.getElementById('newHostModal').showModal();
            });
        @endif
    </script>
@endsection
