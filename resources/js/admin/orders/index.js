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
});