<table class="table">
    <thead>
    <tr>
        <th scope="col" colspan="2" class="text-center">
            Возврат накладной буса № {{ $invoiceReturn->bus->license_plate }}, добавлено {{ date('d.m.Y H:i', strtotime($invoiceReturn->created_at)) }}
            @if($invoiceReturn->created_at != $invoiceReturn->updated_at)
                <br> Обновлено: {{ date('d.m.Y H:i', strtotime($invoiceReturn->updated_at)) }}
            @endif
        </th>
    </tr>
    <tr>
        <th scope="col" style="width: 70%">Данные магазина</th>
        <th scope="col" style="width: 30%">Сумма</th>
    </tr>
    </thead>
    <tbody>
    @php
        $totalAmount = 0;
    @endphp
    @foreach($invoiceReturn->shops as $shop)
        @php
            $totalAmount += $shop->amount ?? 0;
        @endphp
        <tr>
            <td>{{ $shop->shop }}</td>
            <td>{{ $shop->amount }}</td>
        </tr>
    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <th>Итого:</th>
        <th>{{ $totalAmount }}</th>
    </tr>
    </tfoot>
</table>
