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

function setOptions(select, items, placeholder) {
    select.replaceChildren();

    if (placeholder) {
        select.add(new Option(placeholder, ''));
    }

    items.forEach(function (item) {
        select.add(new Option(item.text, item.id));
    });
}

function updateAllocSummary() {
    var ids = Object.keys(selectedAllocs);
    var summary = document.getElementById('pAllocSummary');
    var primary = document.getElementById('pAllocation');
    var additional = document.getElementById('pAllocationAdditional');

    additional.replaceChildren();
    if (ids.length === 0) {
        summary.textContent = 'No allocations selected';
        primary.value = '';
        return;
    }

    primary.value = ids[0];
    summary.replaceChildren(document.createTextNode(ids.length + ' selected (Default: '));
    var defaultAllocation = document.createElement('strong');
    defaultAllocation.textContent = selectedAllocs[ids[0]];
    summary.append(defaultAllocation, document.createTextNode(')'));

    ids.slice(1).forEach(function (id) {
        additional.add(new Option(id, id, true, true));
    });
}

function renderAllocPage() {
    var start = allocPage * allocPageSize;
    var pageItems = allAllocations.slice(start, start + allocPageSize);
    var totalPages = Math.ceil(allAllocations.length / allocPageSize);
    var list = document.getElementById('pAllocationsList');
    var empty = document.getElementById('pAllocEmpty');

    list.replaceChildren();
    document.getElementById('pAllocLoader').classList.add('hidden');
    empty.classList.toggle('hidden', allAllocations.length !== 0);

    pageItems.forEach(function (allocation) {
        var id = String(allocation.id);
        var checked = Object.prototype.hasOwnProperty.call(selectedAllocs, id);
        var isDefault = checked && id === Object.keys(selectedAllocs)[0];
        var row = document.createElement('label');
        var checkbox = document.createElement('input');
        var name = document.createElement('span');

        row.className = 'alloc-row';
        checkbox.type = 'checkbox';
        checkbox.value = id;
        checkbox.checked = checked;
        checkbox.addEventListener('change', function () {
            if (checkbox.checked) {
                selectedAllocs[id] = allocation.text;
            } else {
                delete selectedAllocs[id];
            }
            renderAllocPage();
            updateAllocSummary();
        });
        name.className = 'flex-1 text-sm';
        name.textContent = allocation.text;
        row.append(checkbox, name);

        if (checked) {
            var badge = document.createElement('span');
            badge.className = 'badge';
            badge.dataset.variant = isDefault ? 'primary' : 'outline';
            badge.textContent = isDefault ? 'Default' : 'Additional';
            row.append(badge);
        }

        list.append(row);
    });

    document.getElementById('pAllocSelectedCount').textContent = Object.keys(selectedAllocs).length + ' selected';
    renderPagination(totalPages);
}

function renderPagination(totalPages) {
    var pagination = document.getElementById('pAllocPagination');
    pagination.replaceChildren();

    if (totalPages <= 1) {
        return;
    }

    function addButton(label, page, disabled, active) {
        var item = document.createElement('li');
        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn';
        button.dataset.size = 'sm';
        button.dataset.variant = active ? 'outline' : 'ghost';
        button.disabled = disabled;
        button.textContent = label;
        button.addEventListener('click', function () {
            goAllocPage(page);
        });
        item.append(button);
        pagination.append(item);
    }

    addButton('‹', allocPage - 1, allocPage === 0, false);
    for (var page = 0; page < totalPages; page++) {
        addButton(String(page + 1), page, false, page === allocPage);
    }
    addButton('›', allocPage + 1, allocPage >= totalPages - 1, false);
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
    var nodeId = document.getElementById('pNodeId').value;
    var nodes = window.Pyrodactyl && window.Pyrodactyl.nodeData ? window.Pyrodactyl.nodeData : [];
    var node = nodes.find(function (item) {
        return String(item.id) === String(nodeId);
    });

    document.getElementById('allocModalNodeName').textContent = node ? node.text : 'N/A';
    allAllocations = node && node.allocations ? node.allocations : [];
    allocPage = 0;
    renderAllocPage();
    document.getElementById('allocModal').showModal();
}

function createVariableField(item) {
    var field = document.createElement('div');
    var label = document.createElement('label');
    var input = document.createElement('input');
    var help = document.createElement('p');
    var variableCode = document.createElement('code');
    var rulesCode = document.createElement('code');

    field.className = 'field';
    field.setAttribute('role', 'group');
    label.htmlFor = 'var_ref_' + item.id;
    if (Number(item.required) === 1) {
        var required = document.createElement('span');
        required.className = 'badge';
        required.dataset.variant = 'destructive';
        required.textContent = 'Required';
        label.append(required, document.createTextNode(' '));
    }
    label.append(document.createTextNode(item.name));

    input.type = 'text';
    input.id = 'var_ref_' + item.id;
    input.name = 'environment[' + item.env_variable + ']';
    input.autocomplete = 'off';
    input.value = item.default_value || '';

    help.className = 'text-sm text-muted-foreground';
    help.append(document.createTextNode(item.description || ''), document.createElement('br'));
    help.append(document.createTextNode('Access in Startup: '));
    variableCode.textContent = '{{' + item.env_variable + '}}';
    help.append(variableCode, document.createElement('br'), document.createTextNode('Validation Rules: '));
    rulesCode.textContent = item.rules || '';
    help.append(rulesCode);
    field.append(label, input, help);

    return field;
}

function updateEggConfiguration() {
    var nests = window.Pyrodactyl && window.Pyrodactyl.nests ? window.Pyrodactyl.nests : {};
    var nest = nests[document.getElementById('pNestId').value];
    var eggId = document.getElementById('pEggId').value;
    var egg = nest && nest.eggs ? nest.eggs[eggId] : null;
    var imageSelect = document.getElementById('pDefaultContainer');
    var variables = document.getElementById('appendVariablesTo');

    imageSelect.replaceChildren();
    variables.replaceChildren();
    if (!egg) {
        imageSelect.add(new Option('Select an egg first', ''));
        document.getElementById('pStartup').value = '';
        return;
    }

    Object.entries(egg.docker_images || {}).forEach(function (entry) {
        imageSelect.add(new Option(entry[0] + ' (' + entry[1] + ')', entry[1]));
    });
    document.getElementById('pStartup').value = egg.startup || nest.startup || 'ERROR: Startup Not Defined!';

    var variableIds = {};
    (egg.variables || []).forEach(function (item) {
        variableIds[item.env_variable] = 'var_ref_' + item.id;
        variables.append(createVariableField(item));
    });
    serviceVariablesUpdated(eggId, variableIds);
}

function updateEggOptions() {
    var nests = window.Pyrodactyl && window.Pyrodactyl.nests ? window.Pyrodactyl.nests : {};
    var nest = nests[document.getElementById('pNestId').value];
    var eggs = nest && nest.eggs ? Object.values(nest.eggs) : [];
    var options = eggs.map(function (egg) {
        return { id: egg.id, text: egg.name };
    });

    setOptions(document.getElementById('pEggId'), options, eggs.length ? null : 'Select a nest first');
    updateEggConfiguration();
}

document.getElementById('pNodeId').addEventListener('change', function (event) {
    var nodes = window.Pyrodactyl && window.Pyrodactyl.nodeData ? window.Pyrodactyl.nodeData : [];
    var node = nodes.find(function (item) {
        return String(item.id) === String(event.target.value);
    });
    selectedAllocs = {};
    document.getElementById('pAllocation').value = '';
    document.getElementById('pAllocationAdditional').replaceChildren();
    document.getElementById('pAllocSummary').textContent = node ? 'No allocations selected' : 'Select a node first';
});

document.getElementById('openAllocBtn').addEventListener('click', function () {
    if (!document.getElementById('pNodeId').value) {
        alert('Please select a node first.');
        return;
    }
    openAllocModal();
});

document.getElementById('pNestId').addEventListener('change', updateEggOptions);
document.getElementById('pEggId').addEventListener('change', updateEggConfiguration);
document.getElementById('pDefaultContainer').addEventListener('change', function () {
    document.getElementById('pDefaultContainerCustom').value = '';
});

document.getElementById('pNodeId').dispatchEvent(new Event('change'));
updateEggOptions();
