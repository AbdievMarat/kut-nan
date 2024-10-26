$(() => {
    function calculateTotal() {
        let total = 0;

        $('input[name^="item_amounts["]').each(function () {
            let amount = parseInt($(this).val());

            if (amount > 0) {
                total += amount;
            }
        });

        let formattedTotal = total.toLocaleString('ru-RU');

        $('#total-amount').text(formattedTotal);
    }

    $(document).on('input', 'input[name^="item_amounts["]', function () {
        calculateTotal();
    });

    calculateTotal();
});