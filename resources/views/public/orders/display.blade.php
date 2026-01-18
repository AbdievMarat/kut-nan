@extends('layouts.public')

@section('content')
    <div class="public-orders-table-wrapper">
        <div class="public-orders-header">
            <h1 class="public-orders-title">Отчёт по заказам</h1>
            <div class="public-orders-date-wrapper">
                <div class="public-orders-date" data-date="{{ $date }}">{{ date('d.m.Y', strtotime($date)) }}</div>
                <div class="public-orders-update-time" id="update-time">Обновлено: {{ date('H:i') }}</div>
            </div>
        </div>

        <table id="public-orders-table" class="table table-bordered public-orders-table">
            <thead>
            <tr>
                <th class="public-table-header">Автобус</th>
                @foreach ($products as $product)
                    <th class="public-table-header vertical-text">{{ $product->name }}</th>
                @endforeach
            </tr>
            </thead>
            <tbody>
                <tr class="table-info fw-bold" id="total-carts-row">
                    <td class="public-table-cell public-table-cell-bus">Тележки</td>
                    @foreach ($totalCarts as $cartsCount)
                        <td class="public-table-cell total-cart-cell">{{ $cartsCount }}</td>
                    @endforeach
                </tr>
            @foreach ($busesData as $bus)
                <tr>
                    <td class="public-table-cell public-table-cell-bus">{{ $bus['license_plate'] }}</td>
                    @foreach ($bus['products'] as $productData)
                        <td class="public-table-cell">
                            {{ $productData['order_amount'] ?: '' }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    @push('scripts')
        @vite(['resources/js/public/orders/display.js'])
    @endpush
@endsection
