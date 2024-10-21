<table class="table">
    <thead>
    <tr>
        <th scope="col" colspan="3" class="text-center">Список остатков на {{ date('d.m.Y', strtotime($remainder->date)) }} от буса № {{ $remainder->bus->license_plate }}</th>
    </tr>
    <tr>
        <th scope="col" style="width: 50%">Продукт</th>
        <th scope="col" style="width: 25%">Кол-во</th>
        <th scope="col" style="width: 25%">Цена</th>
    </tr>
    </thead>
    <tbody>
    @php
        $totalAmount = 0;
    @endphp
    @foreach($remainder->items as $item)
        @if($item->amount > 0)
            @php
                $totalAmount += $item->amount * $item->price;
            @endphp
            <tr>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->amount }}</td>
                <td>{{ $item->price }}</td>
            </tr>
        @endif
    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <th colspan="2">Итого:</th>
        <th>{{ $totalAmount }}</th>
    </tr>
    </tfoot>
</table>