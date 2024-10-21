$(() => {
    function calculateTotal() {
        let total = 0;

        $('input[data-product-price]').each(function () {
            let quantity = parseInt($(this).val());
            let price = parseInt($(this).data('product-price'));

            if (quantity > 0) {
                total += price * quantity;
            }
        });

        $('#total-price').text(total);
    }

    $(document).on('input', 'input[data-product-price]', function () {
        calculateTotal();
    });

    calculateTotal();
});