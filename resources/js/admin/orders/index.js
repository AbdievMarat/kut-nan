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
                    // Обновляем значения в строке "Тележки"
                    const $totalCartCells = $('#total-carts-row .total-cart-cell');
                    successResponse.totalCarts.forEach(function (value, index) {
                        if ($totalCartCells.eq(index).length) {
                            $totalCartCells.eq(index).text(value);
                        }
                    });
                }
                if (successResponse.multipliedAmounts) {
                    // Обновляем значения в строке "Итого × Множитель"
                    const $multipliedAmountCells = $('.multiplied-amount-cell');
                    successResponse.multipliedAmounts.forEach(function (value, index) {
                        if ($multipliedAmountCells.eq(index).length) {
                            $multipliedAmountCells.eq(index).text(value);
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
});