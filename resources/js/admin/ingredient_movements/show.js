$('.cost-detail-btn').on('click', function() {
    const productId = $(this).data('product-id');
    const date = $(this).data('date');
    const $content = $('#costDetailContent');

    // Показываем индикатор загрузки
    $content.html(`
    <div class="text-center">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">Загрузка...</span>
        </div>
        <p class="mt-2">Загрузка данных о себестоимости...</p>
    </div>`);

    $.get('/admin/ingredient-movements/cost-details', {
        product_id: productId,
        date: date
    })
    .done(function(response) {
        $content.html(response.costDetails);
    })
    .fail(function() {
        $content.html(`
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                Ошибка при загрузке данных о себестоимости. Попробуйте еще раз.
            </div>`);
    });
});
