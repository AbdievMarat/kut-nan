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
                    // Если пользователь ввел значение в total-carts-input, используем его для пересчета
                    const $calculatedTotalValues = $('.calculated-total-value');
                    const $totalCartsInputs = $('.total-carts-input');
                    const $piecesPerCartCells = $('.pieces-per-cart-cell');
                    
                    successResponse.finalTotals.forEach(function (value, index) {
                        if ($calculatedTotalValues.eq(index).length) {
                            const $input = $totalCartsInputs.eq(index);
                            const $piecesPerCartCell = $piecesPerCartCells.eq(index);
                            
                            // Проверяем, заполнил ли пользователь поле total-carts-input
                            const userInputValue = $input.val();
                            let finalTotal = value;
                            
                            if (userInputValue && userInputValue !== '') {
                                // Пользователь ввел значение - пересчитываем итого на основе его ввода
                                const totalCartsValue = parseFloat(userInputValue) || 0;
                                const piecesPerCart = parseFloat($piecesPerCartCell.text()) || 1;
                                finalTotal = totalCartsValue * piecesPerCart;
                            }
                            
                            const roundedValue = finalTotal ? Math.round(parseFloat(finalTotal)) : '';
                            $calculatedTotalValues.eq(index).text(roundedValue);
                        }
                    });
                }
                if (successResponse.totalCartsValuesExact) {
                    // Обновляем точные значения в data-атрибуте для использования при печати
                    const $totalCartsInputs = $('.total-carts-input');
                    successResponse.totalCartsValuesExact.forEach(function (exactValue, index) {
                        if ($totalCartsInputs.eq(index).length) {
                            const $input = $totalCartsInputs.eq(index);
                            // Обновляем data-exact-value только если поле пустое (пользователь не вводил значение)
                            // Если поле заполнено, сохраняем текущее значение
                            const currentValue = $input.val();
                            if (!currentValue || currentValue === '') {
                                // Поле пустое - обновляем data-exact-value новым рассчитанным значением
                                $input.data('exact-value', exactValue !== '' && exactValue !== null ? exactValue : '');
                            } else {
                                // Поле заполнено пользователем - пересчитываем data-exact-value на основе текущего значения
                                const $cell = $input.closest('.total-cart-cell');
                                const $calculatedCartsValue = $cell.find('.calculated-carts-value');
                                const calculatedCarts = parseFloat($calculatedCartsValue.text()) || 0;
                                const $cartCountInput = $cell.find('.cart-count-input');
                                const savedCarts = parseFloat($cartCountInput.val()) || 0;
                                const newExactValue = calculatedCarts + savedCarts;
                                $input.data('exact-value', newExactValue > 0 ? newExactValue : '');
                            }
                        }
                    });
                }
                if (successResponse.totalCartsValues) {
                    // Обновляем инпуты итогового количества тележек ТОЛЬКО если поле пустое
                    // Если пользователь уже ввел значение, не трогаем его, но пересчитываем запас
                    const $totalCartsInputs = $('.total-carts-input');
                    successResponse.totalCartsValues.forEach(function (value, index) {
                        if ($totalCartsInputs.eq(index).length) {
                            const $input = $totalCartsInputs.eq(index);
                            const currentValue = $input.val();
                            // Обновляем значение только если поле пустое
                            if (!currentValue || currentValue === '') {
                                $input.val(value || '');
                            } else {
                                // Поле заполнено пользователем - пересчитываем запас тележек
                                const $cell = $input.closest('.total-cart-cell');
                                const $calculatedCartsValue = $cell.find('.calculated-carts-value');
                                const calculatedCarts = parseFloat($calculatedCartsValue.text()) || 0;
                                const totalCartsValue = parseFloat(currentValue) || 0;
                                const carts = totalCartsValue - calculatedCarts;
                                
                                const $cartCountInput = $cell.find('.cart-count-input');
                                if ($cartCountInput.length) {
                                    const productId = $input.data('product-id');
                                    const date = $input.data('date');
                                    const cartsValue = carts !== null && !isNaN(carts) ? carts : null;
                                    
                                    // Обновляем значение в поле
                                    $cartCountInput.val(cartsValue !== null ? cartsValue.toFixed(2) : '');
                                    
                                    // Сохраняем в базу данных
                                    const csrf_token = $('meta[name="csrf-token"]').attr('content');
                                    $.ajax({
                                        type: 'POST',
                                        url: '/admin/update-cart-count',
                                        headers: {'X-CSRF-TOKEN': csrf_token},
                                        data: {
                                            product_id: productId,
                                            date: date,
                                            carts: cartsValue
                                        },
                                    }).done(saveResponse => {
                                        if (saveResponse.success) {
                                            // Обновляем значение в инпуте carts после сохранения
                                            if (saveResponse.carts !== null && saveResponse.carts !== undefined) {
                                                $cartCountInput.val(parseFloat(saveResponse.carts).toFixed(2));
                                            } else {
                                                $cartCountInput.val('');
                                            }
                                            
                                            // Обновляем точное значение в data-атрибуте
                                            if (saveResponse.total_carts_value !== null && saveResponse.total_carts_value !== undefined) {
                                                $input.data('exact-value', saveResponse.total_carts_value);
                                                // Обновляем значение в инпуте только если пользователь заполнил поле (есть carts)
                                                if (saveResponse.carts !== null && saveResponse.carts !== undefined) {
                                                    const roundedValue = Math.round(saveResponse.total_carts_value);
                                                    $input.val(roundedValue);
                                                }
                                            }
                                            
                                            // Обновляем итого в строке "Итого" (округляем до целых)
                                            const $totalCells = $('#cart-totals-row .cart-total-cell');
                                            const $allTotalCartsInputs = $('.total-carts-input');
                                            const productIndex = $allTotalCartsInputs.index($input);
                                            
                                            if (productIndex >= 0 && saveResponse.calculated_total !== null && saveResponse.calculated_total !== undefined) {
                                                const $totalCell = $totalCells.eq(productIndex);
                                                if ($totalCell.length) {
                                                    $totalCell.find('.calculated-total-value').text(Math.round(saveResponse.calculated_total));
                                                }
                                            }
                                        }
                                    }).fail(errorResponse => {
                                        console.error('Ошибка при сохранении запаса тележек:', errorResponse);
                                    });
                                }
                            }
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
                    
                    // Обновляем точное значение в data-атрибуте (без округления) для использования при печати
                    if (successResponse.total_carts_value !== null && successResponse.total_carts_value !== undefined) {
                        $input.data('exact-value', successResponse.total_carts_value);
                        // Обновляем значение в инпуте только если пользователь заполнил поле (есть carts)
                        if (successResponse.carts !== null && successResponse.carts !== undefined) {
                            const roundedValue = Math.round(successResponse.total_carts_value);
                            $input.val(roundedValue);
                        } else {
                            // Если пользователь не заполнил поле, очищаем его
                            $input.val('');
                        }
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
                // Выводим точное значение без округления
                $printValue.text(exactValue !== '' && !isNaN(exactValue) ? exactValue : '');
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