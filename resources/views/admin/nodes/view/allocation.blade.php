@extends('layouts.admin')

@section('title')
    {{ $node->name }}: @lang('admin/nodes.common.allocation')
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">{{ $node->name }}</h1>
    <p class="text-sm text-muted-foreground">@lang('admin/nodes.allocation.header_subtitle')</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">@lang('admin/nodes.common.admin')</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.nodes') }}">@lang('admin/nodes.common.nodes')</a>
        <x-icon name="chevron-right" class="size-3" />
        <a href="{{ route('admin.nodes.view', $node->id) }}">{{ $node->name }}</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>@lang('admin/nodes.common.allocation')</span>
    </nav>
@endsection

@section('content')
<div class="grid gap-6">
    <div class="col-span-full">
        <div class="tabs">
            <nav role="tablist" aria-orientation="horizontal" data-variant="line">
                <a href="{{ route('admin.nodes.view', $node->id) }}" role="tab" aria-selected="false" tabindex="-1">@lang('admin/nodes.common.about')</a>
                <a href="{{ route('admin.nodes.view.settings', $node->id) }}" role="tab" aria-selected="false" tabindex="-1">@lang('admin/nodes.common.settings')</a>
                <a href="{{ route('admin.nodes.view.configuration', $node->id) }}" role="tab" aria-selected="false" tabindex="-1">@lang('admin/nodes.common.configuration')</a>
                <a href="{{ route('admin.nodes.view.allocation', $node->id) }}" role="tab" aria-selected="true" tabindex="0">@lang('admin/nodes.common.allocation')</a>
                <a href="{{ route('admin.nodes.view.servers', $node->id) }}" role="tab" aria-selected="false" tabindex="-1">@lang('admin/nodes.common.servers')</a>
            </nav>
        </div>
    </div>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="md:col-span-2">
        <div class="card">
            <header>
                <h3 class="text-lg font-semibold">@lang('admin/nodes.allocation.existing_allocations')</h3>
            </header>
            <section class="table-container no-padding">
                <table class="table">
                    <thead>
                    <tr>
                        <th>
                            <input type="checkbox" class="input select-all-files max-sm:hidden" data-action="selectAll">
                        </th>
                        <th>@lang('admin/nodes.allocation.ip_address') <x-icon name="minus-square" class="size-4" onclick="document.getElementById('allocationModal').showModal()" /></th>
                        <th>@lang('admin/nodes.allocation.ip_alias')</th>
                        <th>@lang('admin/nodes.allocation.port')</th>
                        <th>@lang('admin/nodes.allocation.assigned_to')</th>
                        <th>
                            <div class="flex items-center gap-2">
                                <button type="button" id="mass_actions" class="btn" data-variant="outline" data-size="sm" disabled
                                        onclick="deleteSelected()">@lang('admin/nodes.allocation.delete_selected') <x-icon name="trash-2" class="size-4" />
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
                                <input  data-size="sm" type="text" value="{{ $allocation->ip_alias }}" data-action="set-alias" data-id="{{ $allocation->id }}" placeholder="@lang('admin/nodes.allocation.none_placeholder')" />
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
                                <li><span class="btn" data-variant="ghost" data-size="sm" aria-disabled="true"><x-icon name="chevron-left" class="size-4" /> <span>@lang('admin/nodes.allocation.previous')</span></span></li>
                            @else
                                <li><a href="{{ $node->allocations->previousPageUrl() }}" class="btn" data-variant="ghost" data-size="sm"><x-icon name="chevron-left" class="size-4" /> <span>@lang('admin/nodes.allocation.previous')</span></a></li>
                            @endif

                            @for($page = 1; $page <= $node->allocations->lastPage(); $page++)
                                @if($page == $node->allocations->currentPage())
                                    <li><a href="{{ $node->allocations->url($page) }}" class="btn" data-variant="outline" data-size="icon" aria-current="page">{{ $page }}</a></li>
                                @else
                                    <li><a href="{{ $node->allocations->url($page) }}" class="btn" data-variant="ghost" data-size="icon">{{ $page }}</a></li>
                                @endif
                            @endfor

                            @if($node->allocations->hasMorePages())
                                <li><a href="{{ $node->allocations->nextPageUrl() }}" class="btn" data-variant="ghost" data-size="sm"><span>@lang('admin/nodes.allocation.next')</span> <x-icon name="chevron-right" class="size-4" /></a></li>
                            @else
                                <li><span class="btn" data-variant="ghost" data-size="sm" aria-disabled="true"><span>@lang('admin/nodes.allocation.next')</span> <x-icon name="chevron-right" class="size-4" /></span></li>
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
                    <h3 class="text-lg font-semibold">@lang('admin/nodes.allocation.assign_new')</h3>
                </header>
                <section>
                    <div role="group" class="field">
                        <label for="pAllocationIP" >@lang('admin/nodes.allocation.ip_address_label')</label>
                        <input type="text" class="input" name="allocation_ip" id="pAllocationIP" list="pAllocationIPList" placeholder="@lang('admin/nodes.allocation.ip_placeholder')" />
                        <datalist id="pAllocationIPList">
                            @foreach($allocations as $allocation)
                                <option value="{{ $allocation->ip }}">
                            @endforeach
                        </datalist>
                        <p class="text-sm text-muted-foreground">@lang('admin/nodes.allocation.ip_help')</p>
                    </div>
                    <div role="group" class="field">
                        <label for="pAllocationAlias" >@lang('admin/nodes.allocation.ip_alias_label')</label>
                        <input type="text" id="pAllocationAlias"  name="allocation_alias" placeholder="@lang('admin/nodes.allocation.alias_placeholder')" />
                        <p class="text-sm text-muted-foreground">@lang('admin/nodes.allocation.alias_help')</p>
                    </div>
                    <div role="group" class="field">
                        <label for="pAllocationPorts" >@lang('admin/nodes.allocation.ports')</label>
                        <input type="text" id="pAllocationPorts" placeholder="@lang('admin/nodes.allocation.ports_placeholder')" />
                        <p class="text-sm text-muted-foreground">@lang('admin/nodes.allocation.ports_help')</p>
                    </div>
                </section>
                <footer>
                    {!! csrf_field() !!}
                    <button type="submit" class="btn ml-auto" data-size="sm">@lang('admin/nodes.allocation.submit')</button>
                </footer>
            </div>
        </form>
    </div>
</div>
<dialog class="dialog" id="allocationModal" onclick="if (event.target === this) this.close()">
    <div class="sm:max-w-sm">
        <header>
            <h4>@lang('admin/nodes.allocation.delete_block_title')</h4>
        </header>
        <form action="{{ route('admin.nodes.view.allocation.removeBlock', $node->id) }}" method="POST">
            <section>
                <div class="grid gap-4">
                    <div role="group" class="field">
                        <label for="pIP">@lang('admin/nodes.allocation.ip_address_label')</label>
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
                <button type="button" class="btn" data-variant="outline" onclick="this.closest('dialog').close()">@lang('admin/nodes.allocation.close')</button>
                <button type="submit" class="btn" data-variant="destructive">@lang('admin/nodes.allocation.delete_allocations')</button>
            </footer>
        </form>
        <button type="button" class="btn" data-variant="ghost" data-size="icon-sm" aria-label="@lang('admin/nodes.allocation.close_dialog')" onclick="this.closest('dialog').close()"><x-icon name="x" class="size-4" /></button>
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
        if (confirm('{{ trans('admin/nodes.allocation.delete_confirm_single') }}')) {
            $.ajax({
                method: 'DELETE',
                url: '/admin/nodes/view/' + {{ $node->id }} + '/allocation/remove/' + allocation,
                headers: { 'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content') },
            }).done(function (data) {
                element.parent().parent().addClass('warning').delay(100).fadeOut();
                alert('{{ trans('admin/nodes.allocation.port_deleted') }}');
            }).fail(function (jqXHR) {
                console.error(jqXHR);
                alert('{{ trans('admin/nodes.allocation.delete_error') }} ' + jqXHR.responseJSON.error);
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
                formattedItems += ', {{ trans('admin/nodes.allocation.and_others') }}'.replace(':count', selectedItems.length - 5);
            }

            if (confirm('{{ trans('admin/nodes.allocation.delete_confirm_multiple') }}' + formattedItems + '?')) {
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

                    alert('{{ trans('admin/nodes.allocation.allocations_deleted') }}');
                }).fail(function (jqXHR) {
                    console.error(jqXHR);
                    alert('{{ trans('admin/nodes.allocation.delete_batch_error') }}');
                });
            }
        } else {
            alert('{{ trans('admin/nodes.allocation.select_allocations') }}');
        }
    }
    </script>
@endsection
