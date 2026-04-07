<table class="table">
    <thead>
    <tr>
        <th scope="col" colspan="2" class="text-center">
            Накладная буса № {{ $invoice->bus->license_plate }}, добавлено {{ date('d.m.Y H:i', strtotime($invoice->created_at)) }}
            @if($invoice->created_at != $invoice->updated_at)
                <br> Обновлено: {{ date('d.m.Y H:i', strtotime($invoice->updated_at)) }}
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
    @foreach($invoice->shops as $shop)
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
