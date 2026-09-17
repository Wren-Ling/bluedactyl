@extends('layouts.admin')

@section('title')
    @lang('admin/databases.title')
@endsection

@section('contentWidth', 'max-w-none')

@section('content-header')
    <h1 class="text-xl font-bold">@lang('admin/databases.header')</h1>
    <p class="text-sm text-muted-foreground">@lang('admin/databases.header_subtitle')</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">@lang('admin/databases.breadcrumb_admin')</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>@lang('admin/databases.breadcrumb_databases')</span>
    </nav>
@endsection

@section('content')
<div class="grid min-w-0 gap-6">
    <div class="col-span-full min-w-0">
        <div class="server-list-card card min-w-0 w-full">
            <header>
                <h3 class="text-lg font-semibold">@lang('admin/databases.host_list')</h3>
                <div class="card-action">
                    <button class="btn" data-size="sm" onclick="document.getElementById('newHostModal').showModal()">@lang('admin/databases.create_new')</button>
                </div>
            </header>
            <section class="min-w-0">
                <div class="table-container w-full max-w-full">
                    <table class="table w-full min-w-[760px] table-fixed">
                        <thead>
                            <tr>
                                <th>@lang('admin/databases.id')</th>
                                <th>@lang('admin/databases.name')</th>
                                <th>@lang('admin/databases.host')</th>
                                <th>@lang('admin/databases.port')</th>
                                <th>@lang('admin/databases.username')</th>
                                <th class="text-center">@lang('admin/databases.databases')</th>
                                <th class="text-center">@lang('admin/databases.node')</th>
                            </tr>
                        </thead>
                        <tbody>
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
                                            <span class="badge">@lang('admin/databases.none')</span>
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
        <button type="button" class="btn" data-variant="ghost" onclick="this.closest('dialog').close()" aria-label="@lang('admin/databases.close')"><x-icon name="x" class="size-4" /></button>
        <h4 class="text-lg font-semibold">@lang('admin/databases.create_host')</h4>
    </header>
    <form action="{{ route('admin.databases') }}" method="POST" id="databaseHostForm">
        <section>
            <div id="testResult" class="hidden"></div>

            <div role="group" class="field">
                <label for="pName">@lang('admin/databases.name')</label>
                <input type="text" name="name" id="pName" value="{{ old('name') }}" />
                <p class="text-sm text-muted-foreground">@lang('admin/databases.name_help')</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div role="group" class="field">
                    <label for="pHost">@lang('admin/databases.host')</label>
                    <input type="text" name="host" id="pHost" value="{{ old('host') }}" />
                    <p class="text-sm text-muted-foreground">@lang('admin/databases.host_help')</p>
                </div>
                <div role="group" class="field">
                    <label for="pPort">@lang('admin/databases.port')</label>
                    <input type="text" name="port" id="pPort" value="{{ old('port', '3306') }}" />
                    <p class="text-sm text-muted-foreground">@lang('admin/databases.port_help')</p>
                </div>
                <div role="group" class="field">
                    <label for="pUsername">@lang('admin/databases.username')</label>
                    <input type="text" name="username" id="pUsername" value="{{ old('username') }}" />
                    <p class="text-sm text-muted-foreground">@lang('admin/databases.username_help')</p>
                </div>
                <div role="group" class="field">
                    <label for="pPassword">@lang('admin/databases.password')</label>
                    <input type="password" name="password" id="pPassword" />
                    <p class="text-sm text-muted-foreground">@lang('admin/databases.password_help')</p>
                </div>
            </div>
            <div role="group" class="field">
                <label for="pNodeId">@lang('admin/databases.linked_node')</label>
                <select name="node_id" id="pNodeId" class="select">
                    <option value="">@lang('admin/databases.none')</option>
                    @foreach($locations as $location)
                        <optgroup label="{{ $location->short }}">
                            @foreach($location->nodes as $node)
                                <option value="{{ $node->id }}" {{ old('node_id') == $node->id ? 'selected' : '' }}>{{ $node->name }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                <p class="text-sm text-muted-foreground">@lang('admin/databases.linked_node_help')</p>
            </div>
            {!! csrf_field() !!}
        </section>
    </form>
    <footer>
        <p class="text-sm text-destructive text-left">@lang('admin/databases.grant_warning')</p>
        <button type="button" class="btn" data-size="sm" data-variant="outline" onclick="this.closest('dialog').close()">@lang('admin/databases.cancel')</button>
        <button type="button" id="testDatabaseBtn" class="btn" data-size="sm">@lang('admin/databases.test_database')</button>
        <button type="submit" class="btn" data-size="sm" form="databaseHostForm">@lang('admin/databases.create')</button>
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
            button.prop('disabled', true).text('{{ trans('admin/databases.testing') }}');
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
                resultDiv.html('<strong>@lang('admin/databases.error_label')</strong> {{ trans('admin/databases.fill_required') }}').addClass('alert').attr('data-variant', 'destructive').show();
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
                        resultDiv.html('<strong>@lang('admin/databases.success_label')</strong> ' + response.message).addClass('alert').attr('data-variant', 'success').show();
                    } else {
                        resultDiv.html('<strong>@lang('admin/databases.error_label')</strong> ' + response.message).addClass('alert').attr('data-variant', 'destructive').show();
                    }
                },
                error: function(xhr) {
                    let message = '{{ trans('admin/databases.unexpected_error') }}';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    } else if (xhr.statusText) {
                        message = xhr.statusText;
                    }
                    resultDiv.html('<strong>@lang('admin/databases.error_label')</strong> ' + message).addClass('alert').attr('data-variant', 'destructive').show();
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
