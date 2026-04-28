@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header">Отчёт по заказам {{ date('d.m.Y', strtotime($date)) }}</div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.orders.index') }}" class="mb-4 no-print" id="orders-filter-form">
                <div class="row align-items-end">
                    <div class="col-md-3 mb-3">
                        <input type="date" name="date" class="form-control orders-filter-control" value="{{ $date }}">
                    </div>
                    <div class="col-md-9 mb-3">
                        <div class="d-grid d-md-flex gap-2">
                            <button type="submit" class="btn btn-success orders-filter-control">
                                <i class="bi bi-search"></i> Вывести
                            </button>

                            <button type="submit" class="btn btn-primary orders-filter-control" formaction="{{ route('admin.orders.export_to_excel') }}" id="orders-export-btn">
                                <i class="bi bi-file-earmark-excel"></i> Выгрузить
                            </button>

                            <button type="button" class="btn btn-info no-print" id="print-btn">
                                <i class="bi bi-printer"></i> Распечатать
                            </button>

                            <button type="button" class="btn btn-secondary no-print" id="toggle-summary-rows-btn">
                                <i class="bi bi-eye"></i> Сводные строки
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <div class="orders-table-container">
            <table id="orders-table" class="table table-bordered table-hover orders-view-mode" data-date="{{ $date }}">
                <thead>
                <tr>
                    <th>Автобус</th>
                    @foreach ($products as $product)
                        <th class="vertical-text">{{ $product->name }}</th>
                    @endforeach
                    <th class="vertical-text no-print">Сумма заказа</th>
                    <th class="vertical-text no-print">Уценка</th>
                    <th class="vertical-text no-print">Реализации</th>
                    <th class="vertical-text no-print">Накладные</th>
                    <th class="vertical-text no-print">Возврат накладных</th>
                    <th class="vertical-text">Остаток</th>
                    <th class="vertical-text">Касса</th>
                    <th class="vertical-text print-only">Подпись</th>
                </tr>
                </thead>
                <tbody>
                    <tr class="table-info fw-bold carts-total-row" id="total-carts-summary-row">
                        <td class="d-flex align-items-center gap-2 flex-wrap">
                            <span>Итого тележек</span>
                            <button type="button" class="btn btn-sm btn-warning no-print carts-domain-enter-btn">Изменить</button>
                            <button type="button" class="btn btn-sm btn-success no-print carts-domain-save-btn d-none">Сохранить</button>
                            <button type="button" class="btn btn-sm btn-secondary no-print carts-domain-cancel-btn d-none">Отмена</button>
                        </td>
                        @foreach ($products as $index => $product)
                            @php
                                $totalCartsVal = $totalCartsValues->values()->get($index);
                                $fromOrdersCartsVal = $totalCarts->values()->get($index);
                                $displayCartsVal = ($totalCartsVal !== null && $totalCartsVal !== '') ? $totalCartsVal : ($fromOrdersCartsVal ?: '');
                                $inputDefaultVal = $displayCartsVal;
                                $isUnderOrders = $totalCartsVal !== null && $totalCartsVal !== ''
                                    && $fromOrdersCartsVal !== null && $fromOrdersCartsVal !== ''
                                    && (float)$totalCartsVal < (float)$fromOrdersCartsVal;
                            @endphp
                            <td class="text-center total-carts-summary-cell" data-product-id="{{ $product->id }}">
                                <span class="total-carts-view align-middle px-1 fw-semibold{{ $isUnderOrders ? ' carts-warning-blink' : '' }}">{{ $displayCartsVal }}</span>
                                <input
                                    type="number"
                                    class="form-control form-control-sm total-carts-input d-none mx-auto"
                                    value="{{ $inputDefaultVal }}"
                                    data-exact-value="{{ $inputDefaultVal }}"
                                    data-product-id="{{ $product->id }}"
                                    placeholder="Итого"
                                    min="0"
                                    step="any"
                                >
                                <span class="print-total-carts-value d-none">{{ $displayCartsVal }}</span>
                            </td>
                        @endforeach
                        <td class="no-print"></td>
                        <td class="no-print"></td>
                        <td class="no-print"></td>
                        <td class="no-print"></td>
                        <td class="no-print"></td>
                        <td></td>
                        <td></td>
                        <td class="print-only"></td>
                    </tr>
                    <tr class="table-info fw-bold carts-calculated-row">
                        <td>Тележек из заказов</td>
                        @foreach ($products as $index => $product)
                            <td class="text-center calculated-carts-cell" data-product-id="{{ $product->id }}">
                                <span class="calculated-carts-value">{{ $totalCarts->values()->get($index) ?? '' }}</span>
                            </td>
                        @endforeach
                        <td class="no-print"></td>
                        <td class="no-print"></td>
                        <td class="no-print"></td>
                        <td class="no-print"></td>
                        <td class="no-print"></td>
                        <td></td>
                        <td></td>
                        <td class="print-only"></td>
                    </tr>
                    <tr class="table-info fw-bold carts-reserve-row">
                        <td>Запас тележек</td>
                        @foreach ($products as $index => $product)
                            @php
                                $reserveCartVal = $reserveCarts->values()->get($index);
                            @endphp
                            <td class="text-center cart-count-cell" data-product-id="{{ $product->id }}">
                                <span class="cart-count-view align-middle px-1">{{ $reserveCartVal !== null && $reserveCartVal !== '' ? $reserveCartVal : '' }}</span>
                            </td>
                        @endforeach
                        <td class="no-print"></td>
                        <td class="no-print"></td>
                        <td class="no-print"></td>
                        <td class="no-print"></td>
                        <td class="no-print"></td>
                        <td></td>
                        <td></td>
                        <td class="print-only"></td>
                    </tr>
                    <tr class="table-secondary fw-bold pieces-per-cart-row">
                        <td>Шт. на тележку</td>
                        @foreach ($piecesPerCarts as $piecesPerCart)
                            <td class="text-center pieces-per-cart-cell">{{ $piecesPerCart }}</td>
                        @endforeach
                        <td class="no-print"></td>
                        <td class="no-print"></td>
                        <td class="no-print"></td>
                        <td class="no-print"></td>
                        <td class="no-print"></td>
                        <td></td>
                        <td></td>
                        <td class="print-only"></td>
                    </tr>
                    <tr class="table-warning fw-bold" id="cart-totals-row">
                        <td>Итого шт.</td>
                        @foreach ($products as $index => $product)
                            <td class="text-center cart-total-cell">
                                <span class="calculated-total-value">{{ $finalTotals->values()->get($index) ?? '' }}</span>
                            </td>
                        @endforeach
                        <td class="no-print"></td>
                        <td class="no-print"></td>
                        <td class="no-print"></td>
                        <td class="no-print"></td>
                        <td class="no-print"></td>
                        <td></td>
                        <td></td>
                        <td class="print-only"></td>
                    </tr>
                    <tr class="table-info fw-bold bread-remain-row">
                        <td class="d-flex align-items-center gap-2 flex-wrap">
                            <span>Остатки шт.</span>
                            <button type="button" class="btn btn-sm btn-warning no-print bread-domain-enter-btn">Изменить</button>
                            <button type="button" class="btn btn-sm btn-success no-print bread-domain-save-btn d-none">Сохранить</button>
                            <button type="button" class="btn btn-sm btn-secondary no-print bread-domain-cancel-btn d-none">Отмена</button>
                        </td>
                        @foreach ($products as $index => $product)
                            @php $breadVal = $breadRemains->values()->get($index); @endphp
                            <td class="text-center bread-remain-cell">
                                <span class="bread-remain-view d-inline-block">{{ $breadVal !== null && $breadVal !== '' ? $breadVal : '' }}</span>
                                <input
                                    type="number"
                                    class="form-control form-control-sm bread-remain-input d-none"
                                    value="{{ $breadVal !== null && $breadVal !== '' ? $breadVal : '' }}"
                                    data-product-id="{{ $product->id }}"
                                    min="0"
                                    step="1"
                                >
                            </td>
                        @endforeach
                        <td class="no-print"></td>
                        <td class="no-print"></td>
                        <td class="no-print"></td>
                        <td class="no-print"></td>
                        <td class="no-print"></td>
                        <td></td>
                        <td></td>
                        <td class="print-only"></td>
                    </tr>
                    <tr class="table-secondary fw-bold multiplied-amount-row">
                        <td class="d-flex align-items-center gap-2 flex-wrap">
                            <span>Итого шт. из заказов</span>
                            <button type="button" class="btn btn-sm btn-warning no-print orders-domain-enter-btn">Изменить</button>
                            <button type="button" class="btn btn-sm btn-success no-print orders-domain-save-btn d-none">Сохранить</button>
                            <button type="button" class="btn btn-sm btn-secondary no-print orders-domain-cancel-btn d-none">Отмена</button>
                        </td>
                        @foreach ($products as $index => $product)
                            @php $multipliedAmount = $multipliedAmounts->get($index); @endphp
                            <td class="text-center multiplied-amount-cell" data-product-id="{{ $product->id }}" data-multiplier="{{ $product->order_multiplier ?? 1 }}">
                                <span class="multiplied-amount-value">{{ $multipliedAmount }}</span>
                                <button type="button"
                                    class="btn btn-sm btn-danger no-print clear-column-btn d-none"
                                    data-product-id="{{ $product->id }}"
                                    title="Очистить столбец">×</button>
                            </td>
                        @endforeach
                        <td class="no-print"></td>
                        <td class="no-print"></td>
                        <td class="no-print"></td>
                        <td class="no-print"></td>
                        <td class="no-print"></td>
                        <td></td>
                        <td></td>
                        <td class="print-only"></td>
                    </tr>
                @foreach ($busesData as $bus)
                    <tr class="bus-data-row">
                        <td>{{ $bus['license_plate'] }}</td>
                        @foreach ($bus['products'] as $productData)
                            @php $ordAmt = $productData['order_amount']; @endphp
                            <td class="text-center order-cell{{ $productData['is_marked'] ? ' order-cell-marked' : '' }}"
                                data-bus-id="{{ $bus['id'] }}"
                                data-product-id="{{ $productData['product_id'] }}">
                                <span class="order-amount-view d-inline-block">{{ $ordAmt === '' || $ordAmt === null || $ordAmt == 0 ? '' : $ordAmt }}</span>
                                <input
                                    type="number"
                                    class="form-control form-control-sm order-amount-input d-none"
                                    value="{{ $ordAmt === '' || $ordAmt === null || $ordAmt == 0 ? '' : $ordAmt }}"
                                    data-bus-id="{{ $bus['id'] }}"
                                    data-product-id="{{ $productData['product_id'] }}"
                                    min="0"
                                    step="1"
                                >
                            </td>
                        @endforeach
                        <td class="no-print">
                            @if($bus['current_order_sum'])
                                <a href="#" class="get-order-breakdown" data-bus_id="{{ $bus['id'] }}">{{ (int) $bus['current_order_sum'] }}</a>
                            @endif
                        </td>
                        <td class="no-print">
                            @if($bus['total_markdown_sum'])
                                <a href="#" class="get-markdown-items" data-bus_id="{{ $bus['id'] }}">{{ $bus['total_markdown_sum'] }}</a>
                            @endif
                        </td>
                        <td class="no-print">
                            @if($bus['total_realization_sum'])
                                <a href="#" class="get-realization-shops" data-bus_id="{{ $bus['id'] }}">{{ $bus['total_realization_sum'] }}</a>
                            @endif
                        </td>
                        <td class="no-print">
                            @if($bus['total_invoice_sum'])
                                <a href="#" class="get-invoice-shops" data-bus_id="{{ $bus['id'] }}">{{ $bus['total_invoice_sum'] }}</a>
                            @endif
                        </td>
                        <td class="no-print">
                            @if($bus['total_invoice_return_sum'])
                                <a href="#" class="get-invoice-return-shops" data-bus_id="{{ $bus['id'] }}">{{ $bus['total_invoice_return_sum'] }}</a>
                            @endif
                        </td>
                        <td>
                            @if($bus['total_remainder_sum'])
                                <a href="#" class="get-remainder-items" data-bus_id="{{ $bus['id'] }}">{{ (int) $bus['total_remainder_sum'] }}</a>
                            @endif
                        </td>
                        <td>
                            @if($bus['total_cashbox'] !== '')
                                <a href="#" class="get-cashbox-breakdown"
                                   data-order-sum="{{ $bus['order_sum'] }}"
                                   data-markdown="{{ $bus['total_markdown_sum'] ?: 0 }}"
                                   data-realization="{{ $bus['total_realization_sum'] ?: 0 }}"
                                   data-invoice="{{ $bus['total_invoice_sum'] ?: 0 }}"
                                   data-invoice-return="{{ $bus['total_invoice_return_sum'] ?: 0 }}"
                                   data-remainder="{{ $bus['total_remainder_sum'] ?: 0 }}"
                                   data-prev-remainder="{{ $bus['prev_remainder_sum'] }}"
                                   data-prev-realization="{{ $bus['prev_realization_sum'] }}"
                                   data-bus="{{ $bus['license_plate'] }}"
                                   data-date="{{ date('d.m.Y', strtotime($date . ' -1 day')) }}"
                                   data-prev-date="{{ date('d.m.Y', strtotime($date . ' -2 day')) }}"
                                   data-total="{{ $bus['total_cashbox'] }}">{{ $bus['total_cashbox'] }}</a>
                            @endif
                        </td>
                        <td class="print-only"></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            </div>
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
        @vite(['resources/js/admin/orders/index.js', 'resources/css/admin-orders.css'])
    @endpush
@endsection
