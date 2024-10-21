$(() => {
    $(document).on('click', '#add_shop', function () {
        const csrf_token = $('meta[name="csrf-token"]').attr('content');

        $.ajax({
            type: 'POST',
            url: '/realizations/add-shop',
            headers: {'X-CSRF-TOKEN': csrf_token},
        }).done(successResponse => {
            $('#shops_container').append(successResponse.item);
        }).fail(errorResponse => {
            alert('Не удалось добавить магазин!');
        });
    });

    $(document).on('click', '.remove_shop', function () {
        Swal.fire({
            title: 'Удалить данные о магазине?',
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#107ee1',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Да!',
            cancelButtonText: 'Отмена!'
        }).then((result) => {
            if (result.isConfirmed) {
                $(this).closest('.shop_item').remove();
            }
        });
    });
});