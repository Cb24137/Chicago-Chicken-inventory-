// Purchase order form: live total cost and inventory unit guidance.
// The server recalculates the total before saving, so this is only a preview.
(function () {
    'use strict';

    var form = document.querySelector('[data-purchase-form]');

    if (!form) {
        return;
    }

    var quantity = form.querySelector('#quantity');
    var unitPrice = form.querySelector('#unit_price');
    var total = form.querySelector('[data-total]');
    var item = form.querySelector('#inventory_item_id');
    var productName = form.querySelector('#item_name');
    var unit = form.querySelector('#unit');
    var unitHint = form.querySelector('[data-unit-hint]');
    var status = form.querySelector('#status');
    var receiveHint = form.querySelector('[data-receive-hint]');

    var unitFactors = JSON.parse(form.getAttribute('data-unit-factors') || '{}');
    var unitLabels = JSON.parse(form.getAttribute('data-unit-labels') || '{}');
    var money = new Intl.NumberFormat('en-MY', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    function selectedItem() {
        if (!item || item.disabled) {
            return null;
        }

        var option = item.options[item.selectedIndex];
        return option && option.value ? option : null;
    }

    function allowedUnits(option) {
        return Object.keys(unitFactors[option.getAttribute('data-unit')] || {});
    }

    // Total Cost = Quantity x Unit Price
    function updateTotal() {
        var q = parseFloat(quantity.value);
        var p = parseFloat(unitPrice.value);
        var value = isFinite(q) && isFinite(p) ? Math.round(q * p * 100) / 100 : 0;

        total.textContent = 'RM ' + money.format(value);
    }

    function updateUnitHint() {
        if (!unitHint) {
            return;
        }

        var option = selectedItem();

        if (!option) {
            unitHint.textContent = unitHint.getAttribute('data-default');
            unitHint.classList.remove('is-warning');
            return;
        }

        var allowed = allowedUnits(option);
        var names = allowed.map(function (key) { return unitLabels[key] || key; });
        var fits = allowed.indexOf(unit.value) !== -1;

        unitHint.textContent = names.length
            ? '“' + option.getAttribute('data-name') + '” is stocked in ' + option.getAttribute('data-unit') +
              '. Order in ' + names.join(' or ') + ' so received stock can be added.'
            : '“' + option.getAttribute('data-name') + '” uses a unit that cannot be received automatically.';
        unitHint.classList.toggle('is-warning', !fits);
    }

    function updateReceiveHint() {
        if (!receiveHint || !status) {
            return;
        }

        var option = selectedItem();

        receiveHint.hidden = status.value !== 'received';
        receiveHint.textContent = option
            ? 'Saving as Received adds this quantity to “' + option.getAttribute('data-name') +
              '” stock. Stock is added only once per order.'
            : 'No inventory item is linked, so saving as Received will not change stock levels.';
    }

    var autoName = selectedItem() ? selectedItem().getAttribute('data-name') : null;

    if (item) {
        item.addEventListener('change', function () {
            var option = selectedItem();

            if (option) {
                // Fill the product name unless the user typed their own
                if (productName.value.trim() === '' || productName.value === autoName) {
                    productName.value = option.getAttribute('data-name');
                }
                autoName = option.getAttribute('data-name');

                // Switch to a unit the inventory item can receive
                var allowed = allowedUnits(option);
                if (allowed.length && allowed.indexOf(unit.value) === -1) {
                    unit.value = allowed[0];
                }

                if (unitPrice.value === '' && unit.value === allowed[0]) {
                    unitPrice.value = option.getAttribute('data-price');
                }
            }

            updateUnitHint();
            updateReceiveHint();
            updateTotal();
        });
    }

    quantity.addEventListener('input', updateTotal);
    unitPrice.addEventListener('input', updateTotal);

    if (unit) {
        unit.addEventListener('change', updateUnitHint);
    }

    if (status) {
        status.addEventListener('change', updateReceiveHint);
    }

    updateTotal();
    updateUnitHint();
    updateReceiveHint();
})();
