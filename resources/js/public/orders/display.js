$(document).ready(function() {
    const UPDATE_INTERVAL = 1 * 60 * 1000; // 1 минута в миллисекундах

    /**
     * Обновление данных через AJAX
     */
    function updateData() {
        let date = $('[data-date]').data('date');

        $.ajax({
            type: 'GET',
            url: window.location.pathname,
            data: { date: date },
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

    /**
     * Обновление таблицы новыми данными
     */
    function updateTable(data) {
        // Обновляем дату
        if (data.date) {
            const dateObj = new Date(data.date + 'T00:00:00');
            const formattedDate = dateObj.toLocaleDateString('ru-RU', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            $('[data-date]').text('Обновлено: ' + data.dateFormatted);
            $('[data-date]').data('date', data.date);
        }

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
                        $row.append('<td class="text-center align-middle">' + amount + '</td>');
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
        // Получаем высоту заголовка таблицы
        const $thead = $('#public-orders-table thead');
        const headerHeight = $thead.length ? $thead.outerHeight() : 120;
        
        // Вычисляем максимальную высоту с учетом видимости шапки
        const maxHeight = Math.max(
            document.body.scrollHeight,
            document.documentElement.scrollHeight
        );
        
        // Вычисляем высоту окна браузера
        const windowHeight = window.innerHeight;
        
        // Скроллим так, чтобы шапка всегда была видна
        // Оставляем отступ равный высоте заголовка плюс небольшой буфер
        const scrollTarget = Math.max(0, maxHeight - windowHeight + headerHeight + 20);

        window.scrollTo({
            top: scrollTarget,
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


    // Начинаем автоскроллинг сразу после загрузки (сначала 10 сек наверху)
    setTimeout(autoScroll, 1000);
});
