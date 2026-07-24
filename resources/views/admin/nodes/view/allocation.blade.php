@extends('layouts.admin')

@section('title')
    {{ $node->name }}: Allocations
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">{{ $node->name }}</h1>
    <p class="text-sm text-muted-foreground">Control allocations available for servers on this node.</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">Admin</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.nodes') }}">Nodes</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.nodes.view', $node->id) }}">{{ $node->name }}</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>Allocations</span>
    </nav>
@endsection

@section('content')
<div class="grid gap-6">
    <div class="col-span-full">
        <div class="tabs">
            <nav role="tablist" aria-orientation="horizontal" data-variant="line">
                <a href="{{ route('admin.nodes.view', $node->id) }}" role="tab" aria-selected="false" tabindex="-1">About</a>
                <a href="{{ route('admin.nodes.view.settings', $node->id) }}" role="tab" aria-selected="false" tabindex="-1">Settings</a>
                <a href="{{ route('admin.nodes.view.configuration', $node->id) }}" role="tab" aria-selected="false" tabindex="-1">Configuration</a>
                <a href="{{ route('admin.nodes.view.allocation', $node->id) }}" role="tab" aria-selected="true" tabindex="0">Allocation</a>
                <a href="{{ route('admin.nodes.view.servers', $node->id) }}" role="tab" aria-selected="false" tabindex="-1">Servers</a>
            </nav>
        </div>
    </div>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="md:col-span-2">
        <div class="card">
            <header>
                <h3 class="text-lg font-semibold">Existing Allocations</h3>
            </header>
            <section class="table-container no-padding">
                <table class="table">
                    <thead>
                    <tr>
                        <th>
                            <input type="checkbox" class="input select-all-files max-sm:hidden" data-action="selectAll">
                        </th>
                        <th>IP Address <x-icon name="minus-square" class="size-4" onclick="document.getElementById('allocationModal').showModal()" /></th>
                        <th>IP Alias</th>
                        <th>Port</th>
                        <th>Assigned To</th>
                        <th>
                            <div class="flex items-center gap-2">
                                <button type="button" id="mass_actions" class="btn" data-variant="outline" data-size="sm" disabled
                                        onclick="deleteSelected()">Delete Selected <x-icon name="trash-2" class="size-4" />
                                </button>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($node->allocations as $allocation)
                        <tr>
                            <td class="middle min-size" data-identifier="type">
                                @if(is_null($allocation->server_id))
                                <input type="checkbox" class="input select-file max-sm:hidden" data-action="addSelection">
                                @else
                                <input disabled type="checkbox" class="input select-file max-sm:hidden" data-action="addSelection">
                                @endif
                            </td>
                            <td class="sm:w-1/4 middle" data-identifier="ip">{{ $allocation->ip }}</td>
                            <td class="sm:w-1/4 middle">
                                <input  data-size="sm" type="text" value="{{ $allocation->ip_alias }}" data-action="set-alias" data-id="{{ $allocation->id }}" placeholder="none" />
                                <span class="input-loader"><x-icon name="refresh-cw" class="size-4 animate-spin" /></span>
                            </td>
                            <td class="sm:w-1/6 middle" data-identifier="port">{{ $allocation->port }}</td>
                            <td class="sm:w-1/4 middle">
                                @if(! is_null($allocation->server))
                                    <a href="{{ route('admin.servers.view', $allocation->server_id) }}">{{ $allocation->server->name }}</a>
                                @endif
                            </td>
                            <td class="sm:w-1/12 middle">
                                @if(is_null($allocation->server_id))
                                    <button data-action="deallocate" data-id="{{ $allocation->id }}" class="btn" data-variant="destructive" data-size="sm"><x-icon name="trash-2" class="size-4" /></button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                </table>
            </section>
            @if($node->allocations->hasPages())
                <footer class="text-center">
                    <nav role="navigation" aria-label="pagination" class="mx-auto flex w-full justify-center">
                        <ul class="flex flex-row items-center gap-1">
                            @if($node->allocations->onFirstPage())
                                <li><span class="btn" data-variant="ghost" data-size="sm" aria-disabled="true"><x-icon name="chevron-left" class="size-4" /> <span>Previous</span></span></li>
                            @else
                                <li><a href="{{ $node->allocations->previousPageUrl() }}" class="btn" data-variant="ghost" data-size="sm"><x-icon name="chevron-left" class="size-4" /> <span>Previous</span></a></li>
                            @endif

                            @for($page = 1; $page <= $node->allocations->lastPage(); $page++)
                                @if($page == $node->allocations->currentPage())
                                    <li><a href="{{ $node->allocations->url($page) }}" class="btn" data-variant="outline" data-size="icon" aria-current="page">{{ $page }}</a></li>
                                @else
                                    <li><a href="{{ $node->allocations->url($page) }}" class="btn" data-variant="ghost" data-size="icon">{{ $page }}</a></li>
                                @endif
                            @endfor

                            @if($node->allocations->hasMorePages())
                                <li><a href="{{ $node->allocations->nextPageUrl() }}" class="btn" data-variant="ghost" data-size="sm"><span>Next</span> <x-icon name="chevron-right" class="size-4" /></a></li>
                            @else
                                <li><span class="btn" data-variant="ghost" data-size="sm" aria-disabled="true"><span>Next</span> <x-icon name="chevron-right" class="size-4" /></span></li>
                            @endif
                        </ul>
                    </nav>
                </footer>
            @endif
        </div>
    </div>
    <div>
        <form action="{{ route('admin.nodes.view.allocation', $node->id) }}" method="POST">
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">Assign New Allocations</h3>
                </header>
                <section>
                    <div role="group" class="field">
                        <label for="pAllocationIP" >IP Address</label>
                        <input type="text" class="input" name="allocation_ip" id="pAllocationIP" list="pAllocationIPList" placeholder="Select or type an IP address" />
                        <datalist id="pAllocationIPList">
                            @foreach($allocations as $allocation)
                                <option value="{{ $allocation->ip }}">
                            @endforeach
                        </datalist>
                        <p class="text-sm text-muted-foreground">Select an IP address to assign ports to.</p>
                    </div>
                    <div role="group" class="field">
                        <label for="pAllocationAlias" >IP Alias</label>
                        <input type="text" id="pAllocationAlias"  name="allocation_alias" placeholder="alias" />
                        <p class="text-sm text-muted-foreground">If you would like to assign a default alias to these allocations enter it here.</p>
                    </div>
                    <div role="group" class="field">
                        <label for="pAllocationPorts" >Ports</label>
                        <input type="text" id="pAllocationPorts" placeholder="e.g. 25565, 25566, 27000-27100" />
                        <p class="text-sm text-muted-foreground">Enter individual ports or port ranges separated by commas or spaces.</p>
                    </div>
                </section>
                <footer>
                    {!! csrf_field() !!}
                    <button type="submit" class="btn ml-auto" data-size="sm">Submit</button>
                </footer>
            </div>
        </form>
    </div>
</div>
<dialog class="dialog" id="allocationModal" onclick="if (event.target === this) this.close()">
    <div class="sm:max-w-sm">
        <header>
            <h4>Delete Allocations for IP Block</h4>
        </header>
        <form action="{{ route('admin.nodes.view.allocation.removeBlock', $node->id) }}" method="POST">
            <section>
                <div class="grid gap-4">
                    <div role="group" class="field">
                        <label for="pIP">IP Address</label>
                        <select class="select" name="ip" id="pIP">
                            @foreach($allocations as $allocation)
                                <option value="{{ $allocation->ip }}">{{ $allocation->ip }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </section>
            <footer>
                @csrf
                <button type="button" class="btn" data-variant="outline" onclick="this.closest('dialog').close()">Close</button>
                <button type="submit" class="btn" data-variant="destructive">Delete Allocations</button>
            </footer>
        </form>
        <button type="button" class="btn" data-variant="ghost" data-size="icon-sm" aria-label="Close dialog" onclick="this.closest('dialog').close()"><x-icon name="x" class="size-4" /></button>
    </div>
</dialog>
@endsection

@section('footer-scripts')
    @parent
    <script>
    $('[data-action="addSelection"]').on('click', function () {
        updateMassActions();
    });

    $('[data-action="selectAll"]').on('click', function () {
        $('input.select-file').not(':disabled').prop('checked', function (i, val) {
            return !val;
        });

        updateMassActions();
    });

    $('[data-action="selective-deletion"]').on('mousedown', function () {
        deleteSelected();
    });

    $('form').on('submit', function () {
        var ports = $('#pAllocationPorts').val();
        if (ports) {
            var parts = ports.split(/[\s,]+/).filter(Boolean);
            var form = this;
            parts.forEach(function (port) {
                var input = $('<input>').attr('type', 'hidden').attr('name', 'allocation_ports[]').val(port);
                form.appendChild(input[0]);
            });
        }
    });

    $('button[data-action="deallocate"]').click(function (event) {
        event.preventDefault();
        var element = $(this);
        var allocation = $(this).data('id');
        if (confirm('Are you sure you want to delete this allocation?')) {
            $.ajax({
                method: 'DELETE',
                url: '/admin/nodes/view/' + {{ $node->id }} + '/allocation/remove/' + allocation,
                headers: { 'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content') },
            }).done(function (data) {
                element.parent().parent().addClass('warning').delay(100).fadeOut();
                alert('Port Deleted!');
            }).fail(function (jqXHR) {
                console.error(jqXHR);
                alert('Whoops! ' + jqXHR.responseJSON.error);
            });
        }
    });

    var typingTimer;
    $('input[data-action="set-alias"]').keyup(function () {
        clearTimeout(typingTimer);
        $(this).parent().removeClass('has-error has-success');
        typingTimer = setTimeout(sendAlias, 250, $(this));
    });

    var fadeTimers = [];
    function sendAlias(element) {
        element.parent().find('.input-loader').show();
        clearTimeout(fadeTimers[element.data('id')]);
        $.ajax({
            method: 'POST',
            url: '/admin/nodes/view/' + {{ $node->id }} + '/allocation/alias',
            headers: { 'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content') },
            data: {
                alias: element.val(),
                allocation_id: element.data('id'),
            }
        }).done(function () {
            element.parent().addClass('has-success');
        }).fail(function (jqXHR) {
            console.error(jqXHR);
            element.parent().addClass('has-error');
        }).always(function () {
            element.parent().find('.input-loader').hide();
            fadeTimers[element.data('id')] = setTimeout(clearHighlight, 2500, element);
        });
    }

    function clearHighlight(element) {
        element.parent().removeClass('has-error has-success');
    }

    function updateMassActions() {
        $('#mass_actions').prop('disabled', $('input.select-file:checked').length === 0);
    }

    function deleteSelected() {
        var selectedIds = [];
        var selectedItems = [];
        var selectedItemsElements = [];

        $('input.select-file:checked').each(function () {
            var $parent = $($(this).closest('tr'));
            var id = $parent.find('[data-action="deallocate"]').data('id');
            var $ip = $parent.find('td[data-identifier="ip"]');
            var $port = $parent.find('td[data-identifier="port"]');
            var block = `${$ip.text()}:${$port.text()}`;

            selectedIds.push({
                id: id
            });
            selectedItems.push(block);
            selectedItemsElements.push($parent);
        });

        if (selectedItems.length !== 0) {
            var formattedItems = "";
            var i = 0;
            $.each(selectedItems, function (key, value) {
                formattedItems += (value + ", ");
                i++;
                return i < 5;
            });

            formattedItems = formattedItems.slice(0, -2);
            if (selectedItems.length > 5) {
                formattedItems += ', and ' + (selectedItems.length - 5) + ' other(s)';
            }

            if (confirm('Are you sure you want to delete the following allocations: ' + formattedItems + '?')) {
                $.ajax({
                    method: 'DELETE',
                    url: '/admin/nodes/view/' + {{ $node->id }} + '/allocations',
                    headers: {'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')},
                    data: JSON.stringify({
                        allocations: selectedIds
                    }),
                    contentType: 'application/json',
                    processData: false
                }).done(function () {
                    $('#file_listing input:checked').each(function () {
                        $(this).prop('checked', false);
                    });

                    $.each(selectedItemsElements, function () {
                        $(this).addClass('warning').delay(200).fadeOut();
                    });

                    alert('Allocations Deleted');
                }).fail(function (jqXHR) {
                    console.error(jqXHR);
                    alert('Whoops! An error occurred while attempting to delete these allocations. Please try again.');
                });
            }
        } else {
            alert('Please select allocation(s) to delete.');
        }
    }
    </script>
@endsection
