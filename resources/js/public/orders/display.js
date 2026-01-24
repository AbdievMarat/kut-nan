$(document).ready(function() {
    const UPDATE_INTERVAL = 5 * 60 * 1000; // 5 минут в миллисекундах
    let updateTimer;
    
    // Инициализация фиксированного заголовка для смарт ТВ
    initStickyHeaderForTV();

    /**
     * Обновление данных через AJAX
     */
    function updateData() {
        let date = $('[data-date]').data('date');
        if (!date) {
            date = getDateFromDisplay();
        }
        
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
                // Обновляем время последнего обновления
                updateLastUpdateTime();
            }
        })
        .fail(function(xhr, status, error) {
            // В случае ошибки оставляем старые данные
            console.log('Ошибка обновления данных:', status, error);
        })
        .always(function() {
            // Планируем следующее обновление
            scheduleNextUpdate();
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
            $('[data-date]').text(formattedDate);
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

        // Удаляем старые строки автобусов (кроме строки с тележками)
        const $tbody = $('#public-orders-table tbody');
        $tbody.find('tr:not(#total-carts-row)').remove();

        // Добавляем новые строки автобусов
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
                
                $totalCartsRow.after($row);
            });
        }
    }

    /**
     * Обновление времени последнего обновления
     */
    function updateLastUpdateTime() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const timeString = hours + ':' + minutes;
        $('#update-time').text('Обновлено: ' + timeString);
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
     * Получение даты из отображения
     */
    function getDateFromDisplay() {
        const dateText = $('[data-date]').text();
        if (dateText) {
            // Преобразуем формат dd.mm.yyyy в yyyy-mm-dd
            const parts = dateText.split('.');
            if (parts.length === 3) {
                return parts[2] + '-' + parts[1] + '-' + parts[0];
            }
        }
        // По умолчанию текущий день
        const today = new Date();
        return today.toISOString().split('T')[0];
    }

    /**
     * Планирование следующего обновления
     */
    function scheduleNextUpdate() {
        clearTimeout(updateTimer);
        updateTimer = setTimeout(function() {
            updateData();
        }, UPDATE_INTERVAL);
    }

    // Сохраняем дату в data-атрибут при загрузке страницы
    const initialDate = getDateFromDisplay();
    $('[data-date]').data('date', initialDate);

    // Запускаем первое обновление через 5 минут
    scheduleNextUpdate();
    
    /**
     * Инициализация фиксированного заголовка для смарт ТВ
     */
    function initStickyHeaderForTV() {
        // Проверяем поддержку CSS sticky
        const testElement = $('<div style="position: sticky; position: -webkit-sticky;"></div>');
        const supportsSticky = testElement.css('position').includes('sticky');
        
        if (supportsSticky) {
            console.log('CSS Sticky поддерживается');
            return; // CSS sticky работает, ничего не делаем
        }
        
        console.log('CSS Sticky НЕ поддерживается, используем JavaScript fallback');
        
        // JavaScript fallback для браузеров без sticky
        const table = $('#public-orders-table');
        const thead = table.find('thead');
        const thElements = thead.find('th');
        
        // Создаем клон заголовка
        let stickyHeader = null;
        
        function createStickyHeader() {
            if (stickyHeader) {
                stickyHeader.remove();
            }
            
            stickyHeader = $('<div class="sticky-header-container"></div>');
            const headerTable = $('<table class="table table-bordered mb-0"></table>');
            const headerThead = thead.clone();
            
            headerTable.append(headerThead);
            stickyHeader.append(headerTable);
            
            // Стили для фиксированного заголовка
            stickyHeader.css({
                position: 'fixed',
                top: '0',
                left: '0',
                right: '0',
                zIndex: 1000,
                backgroundColor: '#212529',
                display: 'none'
            });
            
            // Синхронизируем ширину колонок
            setTimeout(() => {
                headerThead.find('th').each(function(index) {
                    const originalTh = thElements.eq(index);
                    if (originalTh.length > 0) {
                        $(this).width(originalTh.outerWidth());
                    }
                });
            }, 100);
            
            $('body').append(stickyHeader);
        }
        
        function updateStickyHeader() {
            if (!stickyHeader) return;
            
            const tableOffset = table.offset();
            if (!tableOffset) return;
            
            const scrollTop = $(window).scrollTop();
            const tableTop = tableOffset.top;
            const tableHeight = table.outerHeight();
            const headerHeight = thead.outerHeight();
            
            // Показываем/скрываем фиксированный заголовок
            if (scrollTop > tableTop && scrollTop < (tableTop + tableHeight - headerHeight)) {
                stickyHeader.show();
                // Обновляем позицию left для горизонтального скролла
                stickyHeader.css('left', -$(window).scrollLeft() + 'px');
            } else {
                stickyHeader.hide();
            }
        }
        
        // Создаем фиксированный заголовок
        createStickyHeader();
        
        // Отслеживаем события
        $(window).on('scroll', updateStickyHeader);
        $(window).on('resize', () => {
            createStickyHeader();
            updateStickyHeader();
        });
        
        // Обновляем при изменении данных таблицы
        const observer = new MutationObserver(() => {
            setTimeout(() => {
                createStickyHeader();
                updateStickyHeader();
            }, 100);
        });
        
        observer.observe(table[0], { 
            childList: true, 
            subtree: true 
        });
    }
});
