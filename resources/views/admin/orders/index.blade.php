@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header">Отчёт по заказам</div>
        <div class="card-body overflow-auto">
            <form method="GET" action="{{ route('admin.orders.index') }}" class="mb-4">
                <div class="row">
                    <div class="col-md-3">
                        <input type="date" name="date" class="form-control" value="{{ $date }}">
                    </div>
                    <div class="col-md-9">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-search"></i> Вывести
                        </button>

                        <button type="submit" class="btn btn-primary" formaction="{{ route('admin.orders.export_to_excel') }}">
                            <i class="bi bi-file-earmark-excel"></i> Выгрузить
                        </button>
                    </div>
                </div>
            </form>

            <table id="orders-table" class="table table-bordered table-hover">
                <thead>
                <tr>
                    <th>Автобус</th>
                    <th class="vertical-text">Москва</th>
                    <th class="vertical-text">Москва уп</th>
                    <th class="vertical-text">Солдат</th>
                    <th class="vertical-text">Отруб</th>
                    <th class="vertical-text">Налив</th>
                    <th class="vertical-text">Тостер</th>
                    <th class="vertical-text">Тостер кара</th>
                    <th class="vertical-text">Мини тостер</th>
                    <th class="vertical-text">Гречневый</th>
                    <th class="vertical-text">Зерновой</th>
                    <th class="vertical-text">Багет</th>
                    <th class="vertical-text">Без дрожж</th>
                    <th class="vertical-text">Чемпион</th>
                    <th class="vertical-text">Абсолют</th>
                    <th class="vertical-text">Кукурузный</th>
                    <th class="vertical-text">Уп. Бород</th>
                    <th class="vertical-text">Уп. Батон отруб</th>
                    <th class="vertical-text">Уп. Батон серый</th>
                    <th class="vertical-text">Уп. Батон белый</th>
                    <th class="vertical-text">Баатыр</th>
                    <th class="vertical-text">Обама отруб</th>
                    <th class="vertical-text">Обама ржан</th>
                    <th class="vertical-text">Обама серый</th>
                    <th class="vertical-text">Уп. Моск</th>
                    <th class="vertical-text">Гамбургер</th>
                    <th class="vertical-text">Тартин</th>
                    <th class="vertical-text">Тартин зерновой</th>
                    <th class="vertical-text">Тартин ржаной</th>
                    <th class="vertical-text">Тартин с луком</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($busOrders as $busOrder)
                    @forelse ($busOrder->orders as $order)
                        <tr>
                            <td>{{ $busOrder->license_plate . ' ' . $busOrder->serial_number }}</td>
                            <td>{{ $order->product_1 }}</td>
                            <td>{{ $order->product_2 }}</td>
                            <td>{{ $order->product_3 }}</td>
                            <td>{{ $order->product_4 }}</td>
                            <td>{{ $order->product_5 }}</td>
                            <td>{{ $order->product_6 }}</td>
                            <td>{{ $order->product_7 }}</td>
                            <td>{{ $order->product_8 }}</td>
                            <td>{{ $order->product_9 }}</td>
                            <td>{{ $order->product_10 }}</td>
                            <td>{{ $order->product_11 }}</td>
                            <td>{{ $order->product_12 }}</td>
                            <td>{{ $order->product_13 }}</td>
                            <td>{{ $order->product_14 }}</td>
                            <td>{{ $order->product_15 }}</td>
                            <td>{{ $order->product_16 }}</td>
                            <td>{{ $order->product_17 }}</td>
                            <td>{{ $order->product_18 }}</td>
                            <td>{{ $order->product_19 }}</td>
                            <td>{{ $order->product_20 }}</td>
                            <td>{{ $order->product_21 }}</td>
                            <td>{{ $order->product_22 }}</td>
                            <td>{{ $order->product_23 }}</td>
                            <td>{{ $order->product_24 }}</td>
                            <td>{{ $order->product_25 }}</td>
                            <td>{{ $order->product_26 }}</td>
                            <td>{{ $order->product_27 }}</td>
                            <td>{{ $order->product_28 }}</td>
                            <td>{{ $order->product_29 }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td>{{ $busOrder->license_plate . ' ' . $busOrder->serial_number }}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endforelse
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
