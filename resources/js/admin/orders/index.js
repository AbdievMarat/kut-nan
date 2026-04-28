$(() => {
    const URL_ORDER_ITEMS = '/admin/orders/update-order-items';
    const URL_BREAD_REMAINS = '/admin/orders/update-bread-remains';
    const URL_CART_COUNTS = '/admin/orders/update-cart-counts';

    /** @type {'none' | 'orders' | 'bread' | 'carts'} */
    let activeEditDomain = 'none';
    let domainDirty = false;

    function csrfToken() {
        return $('meta[name="csrf-token"]').attr('content');
    }

    function setFormsDisabled(disabled) {
        $('.orders-filter-control, #print-btn').prop('disabled', disabled);
    }

    function markDirty() {
        if (activeEditDomain !== 'none') {
            domainDirty = true;
        }
    }

    function setEditDomain(domain) {
        activeEditDomain = domain;
        const $table = $('#orders-table');

        if (domain === 'none') {
            $table.removeClass('orders-edit-mode').addClass('orders-view-mode');
        } else {
            $table.removeClass('orders-view-mode').addClass('orders-edit-mode');
        }

        $('tr.bus-data-row, tr.multiplied-amount-row, tr.bread-remain-row, tr.carts-calculated-row, tr.carts-reserve-row, tr.carts-total-row').removeClass(
            'table-warning',
        );

        $('.orders-domain-enter-btn, .bread-domain-enter-btn, .carts-domain-enter-btn').removeClass('d-none');
        $('.orders-domain-save-btn, .orders-domain-cancel-btn').addClass('d-none');
        $('.bread-domain-save-btn, .bread-domain-cancel-btn').addClass('d-none');
        $('.carts-domain-save-btn, .carts-domain-cancel-btn').addClass('d-none');

        $('.order-amount-input, .bread-remain-input, .total-carts-input').addClass('d-none');
        $('.order-amount-view, .bread-remain-view, .total-carts-view').removeClass('d-none');
        $('.clear-column-btn').addClass('d-none');

        if (domain === 'none') {
            setFormsDisabled(false);
            domainDirty = false;
            return;
        }

        setFormsDisabled(true);
        domainDirty = false;

        $('.orders-domain-enter-btn, .bread-domain-enter-btn, .carts-domain-enter-btn').addClass('d-none');

        if (domain === 'orders') {
            $('.orders-domain-save-btn, .orders-domain-cancel-btn').removeClass('d-none');
            $('.order-amount-view').addClass('d-none');
            $('.order-amount-input').removeClass('d-none');
            $('.clear-column-btn').removeClass('d-none');
            $('tr.bus-data-row, tr.multiplied-amount-row').addClass('table-warning');
        } else if (domain === 'bread') {
            $('.bread-domain-save-btn, .bread-domain-cancel-btn').removeClass('d-none');
            $('.bread-remain-view').addClass('d-none');
            $('.bread-remain-input').removeClass('d-none');
            $('tr.bread-remain-row').addClass('table-warning');
        } else if (domain === 'carts') {
            $('.carts-domain-save-btn, .carts-domain-cancel-btn').removeClass('d-none');
            $('.total-carts-view').addClass('d-none');
            $('.total-carts-input').removeClass('d-none');
            $('tr.carts-calculated-row, tr.carts-reserve-row, tr.carts-total-row').addClass('table-warning');
        }
    }

    function normalizeTotalCartsInput($input) {
        let inputValue = $input.val();
        if (inputValue !== '' && inputValue !== null) {
            // Allow digits and decimal point only
            inputValue = String(inputValue).replace(/[^\d.]/g, '');
            // Keep only first decimal point
            const dotIndex = inputValue.indexOf('.');
            if (dotIndex !== -1) {
                inputValue = inputValue.slice(0, dotIndex + 1) + inputValue.slice(dotIndex + 1).replace(/\./g, '');
            }
            $input.val(inputValue);
        }
        const totalCartsValue = inputValue === '' || inputValue === null ? null : parseFloat(inputValue);
        $input.data('exact-value', totalCartsValue !== null && !isNaN(totalCartsValue) ? totalCartsValue : '');
        return totalCartsValue;
    }

    function collectOrderItemsPayload() {
        const date = $('#orders-table').data('date');
        const order_items = [];
        $('.order-amount-input').each(function () {
            const $i = $(this);
            const v = $i.val();
            order_items.push({
                bus_id: $i.data('bus-id'),
                product_id: $i.data('product-id'),
                amount: v === '' ? null : v,
            });
        });
        return { date, order_items };
    }

    function collectBreadRemainsPayload() {
        const date = $('#orders-table').data('date');
        const bread_remains = [];
        $('.bread-remain-input').each(function () {
            const $i = $(this);
            const v = $i.val();
            bread_remains.push({
                product_id: $i.data('product-id'),
                amount: v === '' ? null : v,
            });
        });
        return { date, bread_remains };
    }

    function collectCartCountsPayload() {
        const date = $('#orders-table').data('date');
        const cart_counts = [];
        $('.total-carts-input').each(function () {
            const $i = $(this);
            const productId = $i.data('product-id');
            normalizeTotalCartsInput($i);
            const rawExact = $i.data('exact-value');
            const parsedTotal =
                rawExact === '' || rawExact === undefined || rawExact === null ? null : parseFloat(String(rawExact));
            cart_counts.push({
                product_id: productId,
                carts: parsedTotal === null || isNaN(parsedTotal) ? null : parsedTotal.toFixed(2),
            });
        });
        return { date, cart_counts };
    }

    function ajaxSave(url, payload, $btn) {
        $btn.prop('disabled', true);
        $.ajax({
            type: 'POST',
            url,
            contentType: 'application/json; charset=UTF-8',
            headers: { 'X-CSRF-TOKEN': csrfToken() },
            data: JSON.stringify(payload),
        })
            .done((res) => {
                if (res.success) {
                    domainDirty = false;
                    window.location.reload();
                } else {
                    alert('Не удалось сохранить изменения.');
                    $btn.prop('disabled', false);
                }
            })
            .fail((xhr) => {
                let msg = 'Ошибка при сохранении данных.';
                const json = xhr.responseJSON;
                if (json) {
                    if (json.message) {
                        msg = json.message;
                    }
                    if (json.errors) {
                        const first = Object.values(json.errors)[0];
                        if (Array.isArray(first) && first[0]) {
                            msg = first[0];
                        }
                    }
                }
                alert(msg);
                $btn.prop('disabled', false);
            });
    }

    $(window).on('beforeunload', function (e) {
        if (domainDirty && activeEditDomain !== 'none') {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    $(document).on('click', '.get-cashbox-breakdown', function (event) {
        event.preventDefault();

        const d = $(this).data();

        const rows = [
            { label: `Сумма заказа за ${d.date}`,   sign: '+', value: d.orderSum },
            { label: 'Уценка',                        sign: '-', value: d.markdown },
            { label: 'Реализация',                    sign: '-', value: d.realization },
            { label: 'Накладные',                     sign: '-', value: d.invoice },
            { label: 'Возврат накладных',             sign: '+', value: d.invoiceReturn },
            { label: 'Остаток',                       sign: '-', value: d.remainder },
            { label: `Остаток за ${d.prevDate}`,      sign: '+', value: d.prevRemainder },
            { label: `Реализация за ${d.prevDate}`,   sign: '+', value: d.prevRealization },
        ];

        let html = '<table class="table table-sm mb-0">';
        rows.forEach(({ label, sign: s, value }) => {
            const num = Number(value);
            const colored = num !== 0
                ? `<span class="${s === '+' ? 'text-success' : 'text-danger'}">${s === '+' ? '+' : '−'} ${abs(num)}</span>`
                : `<span class="text-muted">${s === '+' ? '+' : '−'} 0</span>`;
            html += `<tr><td>${label}</td><td class="text-end">${colored}</td></tr>`;
        });
        html += `<tr class="fw-bold table-active"><td>Итого в кассу</td><td class="text-end">${fmt(d.total)}</td></tr>`;
        html += '</table>';

        $('#orderContent').html(html);
        $('#orderModal .modal-title').text(`Бус ${d.bus} должен сдать в кассу ${d.date}`);
        $('#orderModal').modal('show');
    });

    $(document).on('click', '.get-order-breakdown', function (event) {
        event.preventDefault();

        const date = $('#orders-table').data('date');
        const busId = $(this).data('bus_id');

        $.ajax({
            type: 'GET',
            url: '/admin/get-order-items',
            headers: { 'X-CSRF-TOKEN': csrfToken() },
            data: { date, bus_id: busId },
        })
            .done((res) => {
                let total = 0;
                let html = '<table class="table table-sm mb-0">';
                res.items.forEach(({ name, amount, price }) => {
                    const subtotal = amount * price;
                    total += subtotal;
                    html += `<tr><td>${name}</td><td class="text-end text-nowrap">${amount} шт × ${price.toLocaleString('ru-RU')} = ${subtotal.toLocaleString('ru-RU')}</td></tr>`;
                });
                html += `<tr class="fw-bold table-active"><td>Итого</td><td class="text-end">${total.toLocaleString('ru-RU')}</td></tr>`;
                html += '</table>';
                $('#orderContent').html(html);
                $('#orderModal .modal-title').text('Детализация заказа');
                $('#orderModal').modal('show');
            })
            .fail(() => {
                alert('Ошибка при загрузке детализации заказа!');
            });
    });

    $(document).on('click', '.get-realization-shops', function (event) {
        event.preventDefault();

        const date = $('#orders-table').data('date');
        const busId = $(this).data('bus_id');

        $.ajax({
            type: 'GET',
            url: '/admin/get-realization-shops',
            headers: { 'X-CSRF-TOKEN': csrfToken() },
            data: {
                date: date,
                bus_id: busId,
            },
        })
            .done((successResponse) => {
                $('#orderContent').html(successResponse.realizationDetails);
                $('#orderModal').modal('show');
            })
            .fail(() => {
                alert('Ошибка при загрузке данных реализации!');
            });
    });

    $(document).on('click', '.get-remainder-items', function (event) {
        event.preventDefault();

        const date = $('#orders-table').data('date');
        const busId = $(this).data('bus_id');

        $.ajax({
            type: 'GET',
            url: '/admin/get-remainder-items',
            headers: { 'X-CSRF-TOKEN': csrfToken() },
            data: {
                date: date,
                bus_id: busId,
            },
        })
            .done((successResponse) => {
                $('#orderContent').html(successResponse.remainderDetails);
                $('#orderModal').modal('show');
            })
            .fail(() => {
                alert('Ошибка при загрузке данных по остаткам!');
            });
    });

    $(document).on('click', '.get-invoice-shops', function (event) {
        event.preventDefault();

        const date = $('#orders-table').data('date');
        const busId = $(this).data('bus_id');

        $.ajax({
            type: 'GET',
            url: '/admin/get-invoice-shops',
            headers: { 'X-CSRF-TOKEN': csrfToken() },
            data: {
                date: date,
                bus_id: busId,
            },
        })
            .done((successResponse) => {
                $('#orderContent').html(successResponse.invoiceDetails);
                $('#orderModal').modal('show');
            })
            .fail(() => {
                alert('Ошибка при загрузке данных накладной!');
            });
    });

    $(document).on('click', '.get-invoice-return-shops', function (event) {
        event.preventDefault();

        const date = $('#orders-table').data('date');
        const busId = $(this).data('bus_id');

        $.ajax({
            type: 'GET',
            url: '/admin/get-invoice-return-shops',
            headers: { 'X-CSRF-TOKEN': csrfToken() },
            data: {
                date: date,
                bus_id: busId,
            },
        })
            .done((successResponse) => {
                $('#orderContent').html(successResponse.invoiceReturnDetails);
                $('#orderModal').modal('show');
            })
            .fail(() => {
                alert('Ошибка при загрузке данных возврата накладной!');
            });
    });

    $(document).on('click', '.get-markdown-items', function (event) {
        event.preventDefault();

        const date = $('#orders-table').data('date');
        const busId = $(this).data('bus_id');

        $.ajax({
            type: 'GET',
            url: '/admin/get-markdown-items',
            headers: { 'X-CSRF-TOKEN': csrfToken() },
            data: {
                date: date,
                bus_id: busId,
            },
        })
            .done((successResponse) => {
                $('#orderContent').html(successResponse.markdownDetails);
                $('#orderModal').modal('show');
            })
            .fail(() => {
                alert('Ошибка при загрузке данных по уценке!');
            });
    });

    $('.orders-domain-enter-btn').on('click', function () {
        setEditDomain('orders');
    });
    $('.bread-domain-enter-btn').on('click', function () {
        setEditDomain('bread');
    });
    $('.carts-domain-enter-btn').on('click', function () {
        setEditDomain('carts');
    });

    $('.orders-domain-cancel-btn, .bread-domain-cancel-btn, .carts-domain-cancel-btn').on('click', function () {
        domainDirty = false;
        window.location.reload();
    });

    $('.orders-domain-save-btn').on('click', function () {
        ajaxSave(URL_ORDER_ITEMS, collectOrderItemsPayload(), $(this));
    });
    $('.bread-domain-save-btn').on('click', function () {
        ajaxSave(URL_BREAD_REMAINS, collectBreadRemainsPayload(), $(this));
    });
    $('.carts-domain-save-btn').on('click', function () {
        ajaxSave(URL_CART_COUNTS, collectCartCountsPayload(), $(this));
    });

    $(document).on('click', '.clear-column-btn', function () {
        const productId = $(this).data('product-id');
        $(`.order-amount-input[data-product-id="${productId}"]`).val('').trigger('input');
    });

    $(document).on('input', '.order-amount-input', function () {
        if (activeEditDomain === 'orders') {
            markDirty();
        }

        const productId = $(this).data('product-id');
        const $cell = $(`.multiplied-amount-cell[data-product-id="${productId}"]`);
        const multiplier = parseFloat($cell.data('multiplier')) || 1;

        let total = 0;
        $(`.order-amount-input[data-product-id="${productId}"]`).each(function () {
            const v = parseFloat($(this).val());
            if (!isNaN(v)) total += v;
        });

        const multiplied = total * multiplier;
        $cell.find('.multiplied-amount-value').text(multiplied > 0 ? multiplied : '');
    });

    $(document).on('input', '.bread-remain-input', function () {
        if (activeEditDomain === 'bread') {
            markDirty();
        }
    });

    $(document).on('input', '.total-carts-input', function () {
        if (activeEditDomain === 'carts') {
            markDirty();
        }
        const $input = $(this);
        const productId = $input.data('product-id');
        const totalVal = parseFloat($input.val());
        const calculatedCarts =
            parseFloat($(`.calculated-carts-cell[data-product-id="${productId}"]`).find('.calculated-carts-value').text()) || 0;
        const $reserveCell = $(`.cart-count-cell[data-product-id="${productId}"]`).find('.cart-count-view');
        const $viewSpan = $(`.total-carts-summary-cell[data-product-id="${productId}"]`).find('.total-carts-view');
        if (!isNaN(totalVal)) {
            const reserve = Math.round((totalVal - calculatedCarts) * 100) / 100;
            $reserveCell.text(reserve !== 0 ? reserve : 0);
            $viewSpan.toggleClass('carts-warning-blink', totalVal < calculatedCarts);
        } else {
            $reserveCell.text('');
            $viewSpan.removeClass('carts-warning-blink');
        }
    });

    $(document).on('click', '#print-btn', function () {
        if ($('#orders-table').hasClass('orders-edit-mode')) {
            return;
        }

        $('.total-carts-input').each(function () {
            const $input = $(this);
            let exactValue = parseFloat($input.data('exact-value'));
            if (isNaN(exactValue)) {
                const productId = $input.data('product-id');
                const calculatedCarts =
                    parseFloat($(`.calculated-carts-cell[data-product-id="${productId}"]`).find('.calculated-carts-value').text()) || 0;
                const reserveText = $(`.cart-count-cell[data-product-id="${productId}"]`).find('.cart-count-view').text().trim();
                const savedCarts = parseFloat(reserveText) || 0;
                exactValue = calculatedCarts + savedCarts;
            }
            const $printValue = $input.closest('.total-carts-summary-cell').find('.print-total-carts-value');
            if ($printValue.length) {
                $printValue.text(exactValue !== '' && !isNaN(exactValue) ? exactValue : '');
            }
        });

        window.print();
    });

    $(document).on('dblclick', '#orders-table.orders-view-mode td.order-cell', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const $td = $(this);
        const busId = $td.data('bus-id');
        const productId = $td.data('product-id');
        const date = $('#orders-table').data('date');

        $.ajax({
            type: 'POST',
            url: '/admin/orders/toggle-order-item-mark',
            contentType: 'application/json; charset=UTF-8',
            headers: { 'X-CSRF-TOKEN': csrfToken() },
            data: JSON.stringify({ bus_id: busId, product_id: productId, date: date }),
        })
            .done((res) => {
                if (res.success) {
                    $td.toggleClass('order-cell-marked', res.is_marked);
                }
            });
    });

    $(document).on('click', 'tr.bus-data-row', function (e) {
        if (!$('#orders-table').hasClass('orders-view-mode')) return;
        if ($(e.target).closest('a').length) return;

        const $row = $(this);
        const isSelected = $row.hasClass('row-selected');
        $('tr.bus-data-row').removeClass('row-selected');
        if (!isSelected) {
            $row.addClass('row-selected');
        }
    });

    const $toggleBtn = $('#toggle-summary-rows-btn');
    const $table = $('#orders-table');
    const $summaryRows = $table.find(
        '.carts-calculated-row, .carts-reserve-row, .carts-total-row, ' +
        '.pieces-per-cart-row, #cart-totals-row, .bread-remain-row, .multiplied-amount-row'
    );

    function showSummaryRows() {
        $table.removeClass('summary-rows-hidden');
        $summaryRows.hide().fadeIn(300);
        $toggleBtn.html('<i class="bi bi-eye-slash"></i> Сводные строки');
    }

    function hideSummaryRows() {
        $summaryRows.fadeOut(300, function () {
            $table.addClass('summary-rows-hidden');
        });
        $toggleBtn.html('<i class="bi bi-eye"></i> Сводные строки');
    }

    showSummaryRows();

    $toggleBtn.on('click', function () {
        if ($table.hasClass('summary-rows-hidden')) {
            showSummaryRows();
        } else {
            hideSummaryRows();
        }
    });

    // Фиксируем строку "Итого шт. из заказов" под заголовком таблицы
    function updateStickyRowTop() {
        const theadHeight = $('#orders-table thead').outerHeight();
        $('#orders-table .multiplied-amount-row td').css('top', theadHeight + 'px');
    }

    updateStickyRowTop();
    $(window).on('resize', updateStickyRowTop);
});
