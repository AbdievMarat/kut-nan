@extends('layouts.public')

@section('content')
    <div class="container-fluid h-100">
        <div class="row mb-3">
            <div class="text-end">
                <div class="text-muted" data-date="{{ $date }}">{{ date('d.m.Y', strtotime($date)) }} | <span id="update-time">Обновлено: {{ date('H:i') }}</span></div>
            </div>
        </div>

        <div class="row">
            <table id="public-orders-table" class="table table-bordered table-hover">
                <thead>
                <tr>
                    <th class="text-center align-middle" style="width: 40px;">Автобус</th>
                    @foreach ($products as $product)
                        <th class="vertical-text">{{ $product->name }}</th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                <tr class="table-info fw-bold" id="total-carts-row">
                    <td class="text-center align-middle fw-bold">Тележки</td>
                    @foreach ($totalCarts as $cartsCount)
                        <td class="text-center align-middle total-cart-cell">{{ $cartsCount }}</td>
                    @endforeach
                </tr>
                @foreach ($busesData as $bus)
                    <tr>
                        <td class="text-center align-middle fw-bold bg-light">{{ $bus['license_plate'] }}</td>
                        @foreach ($bus['products'] as $productData)
                            <td class="text-center align-middle">
                                {{ $productData['order_amount'] ?: '' }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
        @vite(['resources/js/public/orders/display.js'])
    @endpush
@endsection
