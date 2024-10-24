<table class="table">
    <thead>
    <tr>
        <th scope="col" colspan="3" class="text-center">
            Список реализаций на {{ date('d.m.Y', strtotime($realization->date)) }} от буса № {{ $realization->bus->license_plate }} <br>
            Создано: {{ date('d.m.Y H:i', strtotime($realization->created_at)) }} <br>
            @if($realization->created_at != $realization->updated_at)
                Обновлено: {{ date('d.m.Y H:i', strtotime($realization->updated_at)) }}
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
    @foreach($realization->shops as $shop)
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