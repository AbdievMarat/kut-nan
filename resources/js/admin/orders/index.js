$(() => {
    $(document).on('click', '.get-realization-shops', function (event) {
        event.preventDefault();

        const csrf_token = $('meta[name="csrf-token"]').attr('content');
        const date = $(this).data('date');
        const busId = $(this).data('bus_id');

        $.ajax({
            type: 'GET',
            url: '/admin/get-realization-shops',
            headers: {'X-CSRF-TOKEN': csrf_token},
            data: {
                date: date,
                bus_id: busId
            },
        }).done(successResponse => {
            $('#orderContent').html(successResponse.realizationDetails);
            $('#orderModal').modal('show');
        }).fail(errorResponse => {
            alert('Ошибка при загрузке данных реализации!');
        });
    });

    $(document).on('click', '.get-remainder-items', function (event) {
        event.preventDefault();

        const csrf_token = $('meta[name="csrf-token"]').attr('content');
        const date = $(this).data('date');
        const busId = $(this).data('bus_id');

        $.ajax({
            type: 'GET',
            url: '/admin/get-remainder-items',
            headers: {'X-CSRF-TOKEN': csrf_token},
            data: {
                date: date,
                bus_id: busId
            },
        }).done(successResponse => {
            $('#orderContent').html(successResponse.remainderDetails);
            $('#orderModal').modal('show');
        }).fail(errorResponse => {
            alert('Ошибка при загрузке данных по остаткам!');
        });
    });

    $(document).on('click', '.get-markdown-items', function (event) {
        event.preventDefault();

        const csrf_token = $('meta[name="csrf-token"]').attr('content');
        const date = $(this).data('date');
        const busId = $(this).data('bus_id');

        $.ajax({
            type: 'GET',
            url: '/admin/get-markdown-items',
            headers: {'X-CSRF-TOKEN': csrf_token},
            data: {
                date: date,
                bus_id: busId
            },
        }).done(successResponse => {
            $('#orderContent').html(successResponse.markdownDetails);
            $('#orderModal').modal('show');
        }).fail(errorResponse => {
            alert('Ошибка при загрузке данных по уценке!');
        });
    });

    // Обработчик события input для сохранения order_amount
    let debounceTimer;
    $(document).on('input', '.order-amount-input', function () {
        const $input = $(this);
        const busId = $input.data('bus-id');
        const productId = $input.data('product-id');
        const date = $input.data('date');
        const amount = $input.val();

        // Очищаем предыдущий таймер
        clearTimeout(debounceTimer);

        // Устанавливаем новый таймер для debounce (задержка 500мс)
        debounceTimer = setTimeout(function () {
            const csrf_token = $('meta[name="csrf-token"]').attr('content');

            $.ajax({
                type: 'POST',
                url: '/admin/update-order-amount',
                headers: {'X-CSRF-TOKEN': csrf_token},
                data: {
                    bus_id: busId,
                    product_id: productId,
                    date: date,
                    amount: amount
                },
            }).done(successResponse => {
                if (successResponse.success && successResponse.totalCarts) {
                    // Обновляем значения в строке "Тележки" (рассчитанные значения)
                    const $calculatedCartsValues = $('.calculated-carts-value');
                    successResponse.totalCarts.forEach(function (value, index) {
                        if ($calculatedCartsValues.eq(index).length) {
                            $calculatedCartsValues.eq(index).text(value || '');
                        }
                    });
                }
                if (successResponse.multipliedAmounts) {
                    // Обновляем значения в строке "Итого × Множитель"
                    const $multipliedAmountCells = $('.multiplied-amount-cell');
                    successResponse.multipliedAmounts.forEach(function (value, index) {
                        if ($multipliedAmountCells.eq(index).length) {
                            $multipliedAmountCells.eq(index).text(value || '');
                        }
                    });
                }
                if (successResponse.finalTotals) {
                    // Обновляем итого в строке "Итого" (с учетом сохраненных carts, округляем до целых)
                    const $calculatedTotalValues = $('.calculated-total-value');
                    successResponse.finalTotals.forEach(function (value, index) {
                        if ($calculatedTotalValues.eq(index).length) {
                            const roundedValue = value ? Math.round(parseFloat(value)) : '';
                            $calculatedTotalValues.eq(index).text(roundedValue);
                        }
                    });
                }
                if (successResponse.totalCartsValues) {
                    // Обновляем инпуты итогового количества тележек (округляем до целых)
                    const $totalCartsInputs = $('.total-carts-input');
                    successResponse.totalCartsValues.forEach(function (value, index) {
                        if ($totalCartsInputs.eq(index).length) {
                            const roundedValue = value ? Math.round(parseFloat(value)) : '';
                            $totalCartsInputs.eq(index).val(roundedValue);
                        }
                    });
                }
                if (successResponse.piecesPerCarts) {
                    // Обновляем значения в строке "Штук на тележку"
                    const $piecesPerCartCells = $('.pieces-per-cart-cell');
                    successResponse.piecesPerCarts.forEach(function (value, index) {
                        if ($piecesPerCartCells.eq(index).length) {
                            $piecesPerCartCells.eq(index).text(value);
                        }
                    });
                }
            }).fail(errorResponse => {
                alert('Ошибка при сохранении данных!');
            });
        }, 500);
    });

    // Обработчик события input для сохранения cart_count (количество тележек)
    let cartCountDebounceTimer;
    $(document).on('input', '.cart-count-input', function () {
        const $input = $(this);
        const productId = $input.data('product-id');
        const date = $input.data('date');
        const carts = parseFloat($input.val()) || null;

        // Очищаем предыдущий таймер
        clearTimeout(cartCountDebounceTimer);

        // Устанавливаем новый таймер для debounce (задержка 500мс)
        cartCountDebounceTimer = setTimeout(function () {
            const csrf_token = $('meta[name="csrf-token"]').attr('content');

            $.ajax({
                type: 'POST',
                url: '/admin/update-cart-count',
                headers: {'X-CSRF-TOKEN': csrf_token},
                data: {
                    product_id: productId,
                    date: date,
                    carts: carts
                },
            }).done(successResponse => {
                if (successResponse.success) {
                    // Обновляем значение в инпуте после сохранения
                    if (successResponse.carts !== null && successResponse.carts !== undefined) {
                        $input.val(parseFloat(successResponse.carts).toFixed(2));
                    } else {
                        $input.val('');
                    }
                    
                    // Обновляем инпут итогового количества тележек
                    const $totalCartsInput = $('.total-carts-input').filter(function() {
                        return $(this).data('product-id') == productId;
                    });
                    if ($totalCartsInput.length && successResponse.total_carts_value !== null && successResponse.total_carts_value !== undefined) {
                        $totalCartsInput.val(parseFloat(successResponse.total_carts_value).toFixed(2));
                    }
                    
                    // Обновляем итого в строке "Итого"
                    const $totalCells = $('#cart-totals-row .cart-total-cell');
                    const $allCartInputs = $('.cart-count-input');
                    const productIndex = $allCartInputs.index($input);
                    
                    if (productIndex >= 0 && successResponse.calculated_total !== null && successResponse.calculated_total !== undefined) {
                        const $totalCell = $totalCells.eq(productIndex);
                        if ($totalCell.length) {
                            // Округляем до целых чисел
                            $totalCell.find('.calculated-total-value').text(Math.round(successResponse.calculated_total));
                        }
                    }
                }
            }).fail(errorResponse => {
                alert('Ошибка при сохранении данных тележек!');
            });
        }, 500);
    });

    // Обработчик события input для итогового количества тележек
    let totalCartsDebounceTimer;
    $(document).on('input', '.total-carts-input', function () {
        const $input = $(this);
        const productId = $input.data('product-id');
        const date = $input.data('date');
        const totalCartsValue = parseFloat($input.val()) || null;

        // Получаем рассчитанное значение из заказов (находим в той же ячейке)
        const $cell = $input.closest('.total-cart-cell');
        const $calculatedCartsValue = $cell.find('.calculated-carts-value');
        const calculatedCarts = parseFloat($calculatedCartsValue.text()) || 0;

        // Вычисляем carts = итого - рассчитанное
        let carts = null;
        if (totalCartsValue !== null && totalCartsValue !== '' && !isNaN(totalCartsValue)) {
            carts = totalCartsValue - calculatedCarts;
        }

        // Обновляем инпут carts
        const $cartCountInput = $('.cart-count-input').filter(function() {
            return $(this).data('product-id') == productId;
        });
        if ($cartCountInput.length && carts !== null && !isNaN(carts)) {
            $cartCountInput.val(carts.toFixed(2));
        } else if ($cartCountInput.length && (carts === null || isNaN(carts))) {
            $cartCountInput.val('');
        }

        // Очищаем предыдущий таймер
        clearTimeout(totalCartsDebounceTimer);

        // Устанавливаем новый таймер для debounce (задержка 500мс)
        totalCartsDebounceTimer = setTimeout(function () {
            const csrf_token = $('meta[name="csrf-token"]').attr('content');

            $.ajax({
                type: 'POST',
                url: '/admin/update-cart-count',
                headers: {'X-CSRF-TOKEN': csrf_token},
                data: {
                    product_id: productId,
                    date: date,
                    carts: carts
                },
            }).done(successResponse => {
                if (successResponse.success) {
                    // Обновляем значение в инпуте carts после сохранения
                    if (successResponse.carts !== null && successResponse.carts !== undefined) {
                        $cartCountInput.val(parseFloat(successResponse.carts).toFixed(2));
                    } else {
                        $cartCountInput.val('');
                    }
                    
                    // НЕ перезаписываем введенное значение итого тележек - оставляем как ввел пользователь
                    // Обновляем только если значение пустое (округляем до целых)
                    if (!$input.val() && successResponse.total_carts_value !== null && successResponse.total_carts_value !== undefined) {
                        $input.val(Math.round(successResponse.total_carts_value));
                    }
                    
                    // Обновляем итого в строке "Итого" (округляем до целых)
                    const $totalCells = $('#cart-totals-row .cart-total-cell');
                    const $allTotalCartsInputs = $('.total-carts-input');
                    const productIndex = $allTotalCartsInputs.index($input);
                    
                    if (productIndex >= 0 && successResponse.calculated_total !== null && successResponse.calculated_total !== undefined) {
                        const $totalCell = $totalCells.eq(productIndex);
                        if ($totalCell.length) {
                            $totalCell.find('.calculated-total-value').text(Math.round(successResponse.calculated_total));
                        }
                    }
                }
            }).fail(errorResponse => {
                alert('Ошибка при сохранении данных итогового количества тележек!');
            });
        }, 500);
    });

    // Обработчик кнопки печати
    $(document).on('click', '#print-btn', function () {
        // Заменяем input на текст перед печатью
        $('.order-amount-input').each(function() {
            const $input = $(this);
            const value = $input.val() || '0';
            const $span = $('<span class="print-value">' + value + '</span>');
            $input.after($span);
            $input.hide();
        });
        
        // Печать
        window.print();
        
        // Возвращаем input после печати
        setTimeout(function() {
            $('.print-value').remove();
            $('.order-amount-input').show();
        }, 100);
    });
});