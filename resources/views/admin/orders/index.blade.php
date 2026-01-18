@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header">Отчёт по заказам</div>
        <div class="card-body overflow-auto">
            <form method="GET" action="{{ route('admin.orders.index') }}" class="mb-4">
                <div class="row">
                    <div class="col-md-3 mb-3">
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
                    @foreach ($products as $product)
                        <th class="vertical-text">{{ $product->name }}</th>
                    @endforeach
                    <th class="vertical-text">Уценка</th>
                    <th class="vertical-text">Реализации</th>
                    <th class="vertical-text">Остаток</th>
                </tr>
                </thead>
                <tbody>
                    <tr class="table-info fw-bold" id="total-carts-row">
                        <td>Тележки</td>
                        @foreach ($totalCarts as $cartsCount)
                            <td class="total-cart-cell">{{ $cartsCount }}</td>
                        @endforeach
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @foreach ($busesData as $bus)
                    <tr>
                        <td>{{ $bus['license_plate'] }}</td>
                        @foreach ($bus['products'] as $productData)
                            <td>
                                <input 
                                    type="number" 
                                    class="form-control form-control-sm order-amount-input" 
                                    value="{{ $productData['order_amount'] }}" 
                                    data-bus-id="{{ $bus['id'] }}" 
                                    data-product-id="{{ $productData['product_id'] }}" 
                                    data-date="{{ $date }}"
                                    min="0"
                                    step="1"
                                    style="min-width: 60px; width: 60px;"
                                >
                            </td>
                        @endforeach
                        <td>
                            @if($bus['total_markdown_sum'])
                                <a href="#" class="get-markdown-items" data-date="{{ $date }}" data-bus_id="{{ $bus['id'] }}">{{ $bus['total_markdown_sum'] }}</a>
                            @endif
                        </td>
                        <td>
                            @if($bus['total_realization_sum'])
                                <a href="#" class="get-realization-shops" data-date="{{ $date }}" data-bus_id="{{ $bus['id'] }}">{{ $bus['total_realization_sum'] }}</a>
                            @endif
                        </td>
                        <td>
                            @if($bus['total_remainder_sum'])
                                <a href="#" class="get-remainder-items" data-date="{{ $date }}" data-bus_id="{{ $bus['id'] }}">{{ $bus['total_remainder_sum'] }}</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="orderModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Детализация</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    <div id="orderContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        @vite(['resources/js/admin/orders/index.js'])
    @endpush
@endsection