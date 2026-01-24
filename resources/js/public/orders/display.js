$(document).ready(function() {
    const UPDATE_INTERVAL = 1 * 60 * 1000; // 1 минута в миллисекундах
    const SCROLL_SPEED = 0.5; // Пикселей в миллисекунду (очень медленно)
    const SCROLL_PAUSE = 3000; // Пауза в начале и конце скроллинга (мс)
    
    let isScrolling = false;
    let scrollDirection = 'down'; // 'down' или 'up'

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
     * Функция автоскроллинга страницы
     */
    function autoScroll() {
        if (isScrolling) return; // Предотвращаем множественные запуски
        
        isScrolling = true;
        const startPosition = window.pageYOffset;
        const documentHeight = Math.max(document.body.scrollHeight, document.documentElement.scrollHeight);
        const windowHeight = window.innerHeight;
        const maxScrollPosition = documentHeight - windowHeight;
        
        if (maxScrollPosition <= 0) {
            isScrolling = false;
            return; // Страница помещается в окно, скроллинг не нужен
        }

        function smoothScroll() {
            const currentPosition = window.pageYOffset;
            let targetPosition;
            let newPosition;

            if (scrollDirection === 'down') {
                targetPosition = maxScrollPosition;
                newPosition = Math.min(currentPosition + SCROLL_SPEED, targetPosition);
                
                if (newPosition >= targetPosition) {
                    // Достигли низа, переключаемся на скроллинг вверх после паузы
                    setTimeout(() => {
                        scrollDirection = 'up';
                        requestAnimationFrame(smoothScroll);
                    }, SCROLL_PAUSE);
                    return;
                }
            } else {
                targetPosition = 0;
                newPosition = Math.max(currentPosition - SCROLL_SPEED, targetPosition);
                
                if (newPosition <= targetPosition) {
                    // Достигли верха, переключаемся на скроллинг вниз после паузы
                    setTimeout(() => {
                        scrollDirection = 'down';
                        isScrolling = false; // Завершаем цикл
                    }, SCROLL_PAUSE);
                    return;
                }
            }

            window.scrollTo(0, newPosition);
            requestAnimationFrame(smoothScroll);
        }

        // Начинаем скроллинг после небольшой паузы
        setTimeout(() => {
            requestAnimationFrame(smoothScroll);
        }, SCROLL_PAUSE);
    }

    /**
     * Запуск автоскроллинга с интервалом
     */
    function startAutoScrollCycle() {
        autoScroll();
        // Запускаем новый цикл скроллинга каждые 30 секунд
        setTimeout(startAutoScrollCycle, 30000);
    }

    // Запускаем обновление данных каждую минуту
    setInterval(updateData, UPDATE_INTERVAL);
    
    // Запускаем автоскроллинг через 5 секунд после загрузки страницы
    setTimeout(startAutoScrollCycle, 5000);
});
