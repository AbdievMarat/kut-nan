// Обработчик изменения количества тележек
$('.product-carts').on('input', function() {
    const productId = $(this).data('product-id');
    const piecesPerCart = $(this).data('pieces-per-cart');
    const quantityCart = parseFloat($(this).val()) || 0;
    const totalPieces = quantityCart * piecesPerCart;

    $(`.total-pieces[data-product-id="${productId}"]`).text(totalPieces);
});
