<table class="table">
    <thead>
    <tr>
        <th scope="col" colspan="4" class="text-center">
            Список уценки буса № {{ $markdown->bus->license_plate }}, добавлено {{ date('d.m.Y H:i', strtotime($markdown->created_at)) }}
            @if($markdown->created_at != $markdown->updated_at)
                <br> Обновлено: {{ date('d.m.Y H:i', strtotime($markdown->updated_at)) }}
            @endif
        </th>
    </tr>
    <tr>
        <th scope="col" style="width: 55%">Продукт</th>
        <th scope="col" style="width: 15%">Сумма</th>
    </tr>
    </thead>
    <tbody>
    @php
        $totalAmount = 0;
    @endphp
    @foreach($markdown->items as $item)
        @if($item->amount > 0)
            @php
                $totalAmount += $item->amount;
            @endphp
            <tr>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->amount }}</td>
            </tr>
        @endif
    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <th>Итого:</th>
        <th>{{ $totalAmount }}</th>
    </tr>
    </tfoot>
</table>