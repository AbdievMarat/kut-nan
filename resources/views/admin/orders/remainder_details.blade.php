<table class="table">
    <thead>
    <tr>
        <th scope="col" colspan="4" class="text-center">
            Список остатков на {{ date('d.m.Y', strtotime($remainder->date)) }} от буса № {{ $remainder->bus->license_plate }} <br>
            Создано: {{ date('d.m.Y H:i', strtotime($remainder->created_at)) }} <br>
            @if($realization->created_at != $realization->updated_at)
                Обновлено: {{ date('d.m.Y H:i', strtotime($realization->updated_at)) }}
            @endif
        </th>
    </tr>
    <tr>
        <th scope="col" style="width: 55%">Продукт</th>
        <th scope="col" style="width: 15%">Кол-во</th>
        <th scope="col" style="width: 15%">Цена</th>
        <th scope="col" style="width: 15%">Сумма</th>
    </tr>
    </thead>
    <tbody>
    @php
        $totalSum = 0;
        $totalAmount = 0;
    @endphp
    @foreach($remainder->items as $item)
        @if($item->amount > 0)
            @php
                $totalSum = $item->amount * $item->price;
                $totalAmount += $totalSum;
            @endphp
            <tr>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->amount }}</td>
                <td>{{ $item->price }}</td>
                <td>{{ $totalSum }}</td>
            </tr>
        @endif
    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <th colspan="3">Итого:</th>
        <th>{{ $totalAmount }}</th>
    </tr>
    </tfoot>
</table>