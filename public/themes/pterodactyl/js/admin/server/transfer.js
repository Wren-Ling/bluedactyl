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

        additionalAllocationsSelect.replaceChildren();
        if (!node) {
            return;
        }

        appendOptions(
            additionalAllocationsSelect,
            node.allocations.filter(function (allocation) {
                return String(allocation.id) !== allocationSelect.value;
            })
        );
    }

    nodeSelect.addEventListener('change', function () {
        const node = getCurrentNode();

        allocationSelect.replaceChildren();
        additionalAllocationsSelect.replaceChildren();
        if (!node) {
            return;
        }

        appendOptions(allocationSelect, node.allocations);
        updateAdditionalAllocations();
    });

    allocationSelect.addEventListener('change', updateAdditionalAllocations);
    nodeSelect.dispatchEvent(new Event('change'));
});
