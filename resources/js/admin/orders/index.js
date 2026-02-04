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
                    // Обновляем инпуты итогового количества тележек
                    const $totalCartsInputs = $('.total-carts-input');
                    successResponse.totalCartsValues.forEach(function (value, index) {
                        if ($totalCartsInputs.eq(index).length) {
                            const $input = $totalCartsInputs.eq(index);
                            // Сохраняем точное значение в data-атрибуте
                            $input.data('exact-value', value || '');
                            // Показываем округленное значение в инпуте для удобства
                            const roundedValue = value ? Math.round(parseFloat(value)) : '';
                            $input.val(roundedValue);
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

    // Обработчик события input для итогового количества тележек
    let totalCartsDebounceTimer;
    $(document).on('input', '.total-carts-input', function () {
        const $input = $(this);
        let inputValue = $input.val();
        
        // Проверяем, что введено целое число
        if (inputValue !== '' && inputValue !== null) {
            // Удаляем все нецифровые символы, кроме минуса в начале
            inputValue = inputValue.replace(/[^\d-]/g, '');
            // Убираем минус, если он не в начале, или если значение отрицательное
            if (inputValue.startsWith('-')) {
                inputValue = inputValue.replace('-', '');
            }
            // Округляем до целого числа
            const intValue = Math.floor(parseFloat(inputValue) || 0);
            if (intValue < 0) {
                inputValue = '0';
            } else {
                inputValue = intValue.toString();
            }
            $input.val(inputValue);
        }
        
        const productId = $input.data('product-id');
        const date = $input.data('date');
        const totalCartsValue = parseInt(inputValue) || null;
        
        // Сохраняем точное значение в data-атрибуте
        $input.data('exact-value', totalCartsValue);

        // Получаем рассчитанное значение из заказов (находим в той же ячейке)
        const $cell = $input.closest('.total-cart-cell');
        const $calculatedCartsValue = $cell.find('.calculated-carts-value');
        const calculatedCarts = parseFloat($calculatedCartsValue.text()) || 0;

        // Вычисляем carts = итого - рассчитанное
        let carts = null;
        if (totalCartsValue !== null && !isNaN(totalCartsValue)) {
            carts = totalCartsValue - calculatedCarts;
        }

        // Обновляем инпут carts (только для отображения)
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
                    
                    // Обновляем точное значение в data-атрибуте и в инпуте из ответа сервера
                    if (successResponse.total_carts_value !== null && successResponse.total_carts_value !== undefined) {
                        $input.data('exact-value', successResponse.total_carts_value);
                        // Всегда обновляем значение в инпуте полученным с сервера (округляем до целого)
                        const roundedValue = Math.round(successResponse.total_carts_value);
                        $input.val(roundedValue);
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
        
        // Обновляем значения для печати итого тележек (используем точные значения из data-атрибута)
        $('.total-carts-input').each(function() {
            const $input = $(this);
            // Используем точное значение из data-атрибута, если оно есть
            let exactValue = parseFloat($input.data('exact-value'));
            if (isNaN(exactValue)) {
                // Если точного значения нет, пересчитываем из текущих значений
                const $cell = $input.closest('.total-cart-cell');
                const $calculatedCartsValue = $cell.find('.calculated-carts-value');
                const calculatedCarts = parseFloat($calculatedCartsValue.text()) || 0;
                const $cartCountInput = $cell.find('.cart-count-input');
                const savedCarts = parseFloat($cartCountInput.val()) || 0;
                exactValue = calculatedCarts + savedCarts;
            }
            const $printValue = $input.closest('.total-cart-cell').find('.print-total-carts-value');
            if ($printValue.length) {
                $printValue.text(Math.round(exactValue));
            }
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