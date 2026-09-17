document.addEventListener('DOMContentLoaded', function () {
    const nodeSelect = document.getElementById('pNodeId');
    const allocationSelect = document.getElementById('pAllocation');
    const additionalAllocationsSelect = document.getElementById('pAllocationAdditional');

    if (!nodeSelect || !allocationSelect || !additionalAllocationsSelect) {
        return;
    }

    function getCurrentNode() {
        const nodes = window.Pyrodactyl?.nodeData ?? [];

        return nodes.find(function (node) {
            return String(node.id) === nodeSelect.value;
        });
    }

    function appendOptions(select, allocations) {
        allocations.forEach(function (allocation) {
            select.add(new Option(allocation.text, allocation.id));
        });
    }

    function updateAdditionalAllocations() {
        const node = getCurrentNode();
        const selected = Array.from(additionalAllocationsSelect.selectedOptions, option => option.value);

        additionalAllocationsSelect.replaceChildren();
        if (!node) {
            return;
        }

        const allocations = node.allocations.filter(function (allocation) {
            return String(allocation.id) !== allocationSelect.value;
        });
        appendOptions(additionalAllocationsSelect, allocations);
        if (!allocations.length) {
            additionalAllocationsSelect.add(new Option(additionalAllocationsSelect.dataset.empty, ''));
        }
        Array.from(additionalAllocationsSelect.options).forEach(function (option) {
            option.selected = selected.includes(option.value);
        });
        additionalAllocationsSelect.disabled = !allocations.length;
    }

    nodeSelect.addEventListener('change', function () {
        const node = getCurrentNode();

        allocationSelect.replaceChildren();
        additionalAllocationsSelect.replaceChildren();
        if (!node) {
            return;
        }

        appendOptions(allocationSelect, node.allocations);
        if (allocationSelect.dataset.selected) {
            const value = allocationSelect.dataset.selected;
            if (Array.from(allocationSelect.options).some(option => option.value === value)) {
                allocationSelect.value = value;
            }
            delete allocationSelect.dataset.selected;
        }
        if (!node.allocations.length) {
            allocationSelect.add(new Option(allocationSelect.dataset.empty, ''));
        }
        allocationSelect.disabled = !node.allocations.length;
        document.querySelector('#transferServerModal button[type="submit"]').disabled = !node.allocations.length;
        updateAdditionalAllocations();
        if (additionalAllocationsSelect.dataset.selected) {
            const selected = JSON.parse(additionalAllocationsSelect.dataset.selected).map(String);
            Array.from(additionalAllocationsSelect.options).forEach(function (option) {
                option.selected = selected.includes(option.value);
            });
            delete additionalAllocationsSelect.dataset.selected;
        }
    });

    allocationSelect.addEventListener('change', updateAdditionalAllocations);
    nodeSelect.dispatchEvent(new Event('change'));
});
