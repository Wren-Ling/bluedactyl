// Copyright (c) 2015 - 2017 Dane Everitt <dane@daneeveritt.com>
//
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in all
// copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
// SOFTWARE.

var allAllocations = [];
var allocPage = 0;
var allocPageSize = 10;
var selectedAllocs = {};

function populateOptions($select, items, placeholder) {
    $select.html('');
    if (placeholder) {
        $select.append($('<option>', { value: '', text: placeholder }));
    }
    $.each(items, function(i, item) {
        $select.append($('<option>', { value: item.id, text: item.text }));
    });
}

function updateAllocSummary() {
    var ids = Object.keys(selectedAllocs);
    var $summary = $('#pAllocSummary');
    var $hidden = $('#pAllocation');
    var $additional = $('#pAllocationAdditional');
    var count = ids.length;

    if (count === 0) {
        $summary.text('No allocations selected');
        $hidden.val('');
        $additional.html('');
        return;
    }
    var firstId = ids[0];
    var firstText = selectedAllocs[firstId];
    $hidden.val(firstId);
    $summary.html(count + ' selected (Default: <strong>' + firstText + '</strong>)');
    $additional.html('');
    for (var i = 1; i < ids.length; i++) {
        $additional.append($('<option>', { value: ids[i], selected: true }));
    }
}

function renderAllocPage() {
    var start = allocPage * allocPageSize;
    var pageItems = allAllocations.slice(start, start + allocPageSize);
    var totalPages = Math.ceil(allAllocations.length / allocPageSize);
    var $list = $('#pAllocationsList').empty();
    var $empty = $('#pAllocEmpty');
    var $loader = $('#pAllocLoader');

    $loader.addClass('hidden');

    if (allAllocations.length === 0) {
        $empty.removeClass('hidden');
        renderPagination(0);
        return;
    }
    $empty.addClass('hidden');

    $.each(pageItems, function(i, a) {
        var id = String(a.id);
        var checked = selectedAllocs.hasOwnProperty(id);
        var isDefault = checked && id === Object.keys(selectedAllocs)[0];
        var $row = $('<label class="alloc-row">');
        var $cb = $('<input type="checkbox" value="' + id + '"' + (checked ? ' checked' : '') + '>');
        $cb.on('change', function() {
            if (this.checked) {
                selectedAllocs[id] = a.text;
            } else {
                delete selectedAllocs[id];
            }
            renderAllocPage();
            updateAllocSummary();
        });
        $row.append($cb);
        $row.append('<span class="flex-1 text-sm">' + a.text + '</span>');
        if (isDefault) {
            $row.append('<span class="badge" data-variant="primary">Default</span>');
        } else if (checked) {
            $row.append('<span class="badge" data-variant="outline">Additional</span>');
        }
        $list.append($row);
    });

    $('#pAllocSelectedCount').text(Object.keys(selectedAllocs).length + ' selected');
    renderPagination(totalPages);
}

function renderPagination(totalPages) {
    var $nav = $('#pAllocPagination').empty();
    if (totalPages <= 1) return;

    $nav.append(
        '<li><button type="button" class="btn" data-size="sm" data-variant="ghost"' +
        (allocPage === 0 ? ' disabled' : '') +
        ' onclick="goAllocPage(' + (allocPage - 1) + ')"><svg class="size-4 lucide lucide-chevron-left" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg></button></li>'
    );

    for (var p = 0; p < totalPages; p++) {
        $nav.append(
            '<li><button type="button" class="btn" data-size="sm" data-variant="' +
            (p === allocPage ? 'outline' : 'ghost') +
            '" data-size="icon" onclick="goAllocPage(' + p + ')">' + (p + 1) + '</button></li>'
        );
    }

    $nav.append(
        '<li><button type="button" class="btn" data-size="sm" data-variant="ghost"' +
        (allocPage >= totalPages - 1 ? ' disabled' : '') +
        ' onclick="goAllocPage(' + (allocPage + 1) + ')"><svg class="size-4 lucide lucide-chevron-right" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg></button></li>'
    );
}

function goAllocPage(page) {
    allocPage = page;
    renderAllocPage();
}

function confirmAllocations() {
    updateAllocSummary();
    document.getElementById('allocModal').close();
}

function openAllocModal() {
    var nodeId = $('#pNodeId').val();
    var data = window.Pyrodactyl && Pyrodactyl.nodeData ? Pyrodactyl.nodeData : [];
    var node = data.find(function(v) { return v.id == nodeId; });

    if (!node || !node.allocations || node.allocations.length === 0) {
        allAllocations = [];
        renderAllocPage();
        document.getElementById('allocModalNodeName').textContent = node ? node.text : 'N/A';
        document.getElementById('allocModal').showModal();
        return;
    }
    document.getElementById('allocModalNodeName').textContent = node.text;
    allAllocations = node.allocations;
    allocPage = 0;
    renderAllocPage();
    document.getElementById('allocModal').showModal();
}

$(document).ready(function() {
    $('#pNodeId').on('change', function() {
        var nodeId = $(this).val();
        selectedAllocs = {};
        var data = window.Pyrodactyl && Pyrodactyl.nodeData ? Pyrodactyl.nodeData : [];
        var node = data.find(function(v) { return v.id == nodeId; });
        if (!node) {
            $('#pAllocation').val('');
            $('#pAllocationAdditional').html('');
            $('#pAllocSummary').text('Select a node first');
            return;
        }
        $('#pAllocSummary').text('No allocations selected');
    });

    $('#openAllocBtn').on('click', function() {
        var nodeId = $('#pNodeId').val();
        if (!nodeId) {
            alert('Please select a node first.');
            return;
        }
        openAllocModal();
    });

    $('#pNestId').on('change', function() {
        const nestId = $(this).val();
        if (!nestId) {
            populateOptions($('#pEggId'), [], 'Select a nest first');
            $('#pEggId').trigger('change');
            return;
        }
        const nests = window.Pyrodactyl && Pyrodactyl.nests ? Pyrodactyl.nests : {};
        const eggs = _.get(nests, nestId + '.eggs', []);
        const eggOptions = $.map(eggs, function(item) {
            return { id: item.id, text: item.name };
        });
        populateOptions($('#pEggId'), eggOptions, 'Select a Nest Egg');
        $('#pEggId').trigger('change');
    });

    $('#pEggId').on('change', function() {
        const nests = window.Pyrodactyl && Pyrodactyl.nests ? Pyrodactyl.nests : {};
        const parentChain = _.get(nests, $('#pNestId').val(), null);
        const objectChain = _.get(parentChain, 'eggs.' + $(this).val(), null);

        const $container = $('#pDefaultContainer');
        $container.html('');
        if (!objectChain) {
            $container.append($('<option>', { value: '', text: 'Select an egg first' }));
            $('#pStartup').val('');
            $('#appendVariablesTo').html('');
            return;
        }

        const images = _.get(objectChain, 'docker_images', {});
        const keys = Object.keys(images);
        for (let i = 0; i < keys.length; i++) {
            $container.append($('<option>', {
                value: images[keys[i]],
                text: keys[i] + ' (' + images[keys[i]] + ')',
            }));
        }

        if (!_.get(objectChain, 'startup', false)) {
            $('#pStartup').val(_.get(parentChain, 'startup', 'ERROR: Startup Not Defined!'));
        } else {
            $('#pStartup').val(_.get(objectChain, 'startup'));
        }

        const variableIds = {};
        $('#appendVariablesTo').html('');
        $.each(_.get(objectChain, 'variables', []), function(i, item) {
            variableIds[item.env_variable] = 'var_ref_' + item.id;

            let isRequired = (item.required === 1) ? '<span class="badge" data-variant="destructive">Required</span> ' : '';
            let dataAppend = ' \
                <div role="group" class="field"> \
                    <label for="var_ref_' + escapeHtml(item.id) + '">' + isRequired + escapeHtml(item.name) + '</label> \
                    <input type="text" id="var_ref_' + escapeHtml(item.id) + '" autocomplete="off" name="environment[' + escapeHtml(item.env_variable) + ']" value="' + escapeHtml(item.default_value) + '" /> \
                    <p class="text-sm text-muted-foreground">' + escapeHtml(item.description) + '<br /> \
                    <strong>Access in Startup:</strong> <code>{{' + escapeHtml(item.env_variable) + '}}</code><br /> \
                    <strong>Validation Rules:</strong> <code>' + escapeHtml(item.rules) + '</code></p> \
                </div> \
            ';
            $('#appendVariablesTo').append(dataAppend);
        });

        serviceVariablesUpdated($('#pEggId').val(), variableIds);
    });
});

function escapeHtml(str) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}
