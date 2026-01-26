$(document).ready(function() {
    const UPDATE_INTERVAL = 5 * 60 * 1000; // 5 минут в миллисекундах

    /**
     * Применение стилей изменения к ячейке
     * Подсветка остается на весь день
     */
    function applyChangeStyle($cell, changeType) {
        // Удаляем предыдущие классы изменений
        $cell.removeClass('change-increase change-decrease');
        
        if (changeType === 'increase') {
            $cell.addClass('change-increase');
        } else if (changeType === 'decrease') {
            $cell.addClass('change-decrease');
        }
    }

    /**
     * Обновление данных через AJAX
     */
    function updateData() {
        $.ajax({
            type: 'GET',
            url: window.location.pathname,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            timeout: 10000, // 10 секунд таймаут
        })
        .done(function(response) {
            if (response && response.busesData && response.products && response.totalCarts) {
                updateTable(response);
            }
        })
        .fail(function(xhr, status, error) {
            console.log('Ошибка обновления данных:', status, error);
        });
    }

    // Применяем стили изменений при первой загрузке страницы
    $('.order-cell').each(function() {
        const $cell = $(this);
        const changeType = $cell.data('change-type');
        applyChangeStyle($cell, changeType);
    });

    /**
     * Обновление таблицы новыми данными
     */
    function updateTable(data) {
        $('#date-formatted').text('Обновлено: ' + data.dateFormatted);

        // Обновляем строку с тележками
        const $totalCartsRow = $('#total-carts-row');
        const $totalCartCells = $totalCartsRow.find('.total-cart-cell');

        // Обновляем существующие ячейки
        if (data.totalCarts && data.totalCarts.length > 0) {
            data.totalCarts.forEach(function(cartsCount, index) {
                const $cell = $totalCartCells.eq(index);
                if ($cell.length) {
                    $cell.text(cartsCount || '');
                } else {
                    // Если ячейки не хватает, добавляем новую
                    $totalCartsRow.append('<td class="text-center align-middle total-cart-cell">' + (cartsCount || '') + '</td>');
                }
            });

            // Удаляем лишние ячейки, если их стало меньше
            const currentCellsCount = $totalCartsRow.find('.total-cart-cell').length;
            if (currentCellsCount > data.totalCarts.length) {
                $totalCartsRow.find('.total-cart-cell').slice(data.totalCarts.length).remove();
            }
        }

        // Сохраняем предыдущие значения для сравнения
        const previousValues = {};
        $('#public-orders-table tbody .order-cell').each(function() {
            const $cell = $(this);
            const busId = $cell.data('bus-id');
            const productId = $cell.data('product-id');
            const key = busId + '_' + productId;
            previousValues[key] = {
                amount: parseInt($cell.text()) || 0,
                changeType: $cell.data('change-type') || ''
            };
        });

        // Удаляем старые строки автобусов из tbody
        const $tbody = $('#public-orders-table tbody');
        $tbody.empty();

        // Добавляем новые строки автобусов в tbody
        if (data.busesData && data.busesData.length > 0) {
            data.busesData.forEach(function(bus) {
                const $row = $('<tr></tr>');
                $row.append('<td class="text-center align-middle fw-bold bg-light">' + escapeHtml(bus.license_plate) + '</td>');

                if (bus.products && bus.products.length > 0) {
                    bus.products.forEach(function(productData) {
                        const amount = productData.order_amount || '';
                        const changeType = productData.change_type || '';
                        const key = bus.id + '_' + productData.product_id;
                        
                        // Используем тип изменения из данных сервера (изменения за весь день)
                        // Если тип не передан, проверяем изменение относительно предыдущего значения
                        let finalChangeType = changeType;
                        if (!finalChangeType && previousValues[key]) {
                            const prevAmount = previousValues[key].amount;
                            const newAmount = parseInt(amount) || 0;
                            if (newAmount > prevAmount) {
                                finalChangeType = 'increase';
                            } else if (newAmount < prevAmount) {
                                finalChangeType = 'decrease';
                            }
                        }

                        const $cell = $('<td class="text-center align-middle order-cell"></td>')
                            .attr('data-bus-id', bus.id)
                            .attr('data-product-id', productData.product_id)
                            .attr('data-change-type', finalChangeType)
                            .text(amount);
                        
                        applyChangeStyle($cell, finalChangeType);
                        $row.append($cell);
                    });
                }

                $tbody.append($row);
            });
        }
    }

    /**
     * Экранирование HTML для безопасности
     */
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    /**
     * Автоматический скроллинг
     */
    function autoScroll() {
        // Вычисляем максимальную высоту страницы
        const maxHeight = Math.max(
            document.body.scrollHeight,
            document.documentElement.scrollHeight
        );

        window.scrollTo({
            top: maxHeight,
            behavior: 'smooth'
        });

        // Через 10 секунд быстро возвращаемся наверх
        setTimeout(() => {
            window.scrollTo({
                top: 0,
                behavior: 'instant'
            });

            // Через полсекунды повторяем цикл
            setTimeout(autoScroll, 10000);
        }, 10000);
    }


    // Запускаем первое обновление данных через 5 секунд после загрузки
    setTimeout(updateData, 5000);

    // Устанавливаем периодическое обновление данных
    setInterval(updateData, UPDATE_INTERVAL);

    // Начинаем автоскроллинг сразу после загрузки (сначала 10 сек наверху)
    setTimeout(autoScroll, 1000);
});
