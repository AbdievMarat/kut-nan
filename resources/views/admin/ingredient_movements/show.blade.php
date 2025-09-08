@extends('layouts.admin')

@section('title', 'Детальная информация по производству на ' . $date)

@section('content')
    <style>
        .compact-table {
            font-size: 0.85rem;
        }
        .compact-table th,
        .compact-table td {
            padding: 0.3rem 0.5rem !important;
            vertical-align: middle;
            line-height: 1.2;
        }
        .compact-table th {
            font-size: 0.8rem;
            font-weight: 600;
        }
        .compact-table .section-header {
            font-size: 0.9rem;
            font-weight: bold;
            padding: 0.4rem !important;
        }
        .compact-table .section-header h5 {
            font-size: 0.9rem;
            margin: 0;
        }
        .compact-table small {
            font-size: 0.7rem;
        }
    </style>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">
                            Производство на {{ date('d.m.Y', strtotime($date)) }}
                        </h3>
                        <div>
                            <a href="{{ route('admin.ingredient-movements.show-export', $date) }}" class="btn btn-success me-2">
                                <i class="bi bi-file-earmark-spreadsheet"></i> Экспорт в Excel
                            </a>
                            <a href="{{ route('admin.ingredient-movements.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Назад к списку
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(count($tableData) > 0 || count($ingredientUsages) > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped compact-table">
                                    <thead>
                                        <tr>
                                            <th>Продукт</th>
                                            <th>Тележек</th>
                                            <th>Штук</th>
                                            @foreach($ingredients as $ingredient)
                                                <th class="text-center" title="{{ $ingredient->name }}">
                                                    {{ $ingredient->short_name ?? $ingredient->name }}
                                                    <small class="text-muted d-block">({{ $ingredient->unit }})</small>
                                                </th>
                                            @endforeach
                                            <th>Себестоимость</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($tableData as $productId => $data)
                                            <tr>
                                                <td><strong>{{ $data['product']->name }}</strong></td>
                                                <td class="text-center">{{ $data['quantity_cart'] }}</td>
                                                <td class="text-center">{{ $data['quantity_total'] }}</td>
                                                @foreach($ingredients as $ingredient)
                                                    <td class="text-center" title="{{ $ingredient->name }} ({{ $ingredient->unit }})">
                                                        @if(isset($data['ingredients'][$ingredient->id]))
                                                            {{ number_format($data['ingredients'][$ingredient->id], 2) }}
                                                        @endif
                                                    </td>
                                                @endforeach
                                                <td class="text-center">
                                                    @php
                                                        $ingredientsCost = 0;
                                                        foreach($ingredients as $ingredient) {
                                                            if(isset($data['ingredients'][$ingredient->id])) {
                                                                $ingredientsCost += $data['ingredients'][$ingredient->id] * $ingredient->price;
                                                            }
                                                        }
                                                        $productionCost = $data['product']->production_cost * $data['quantity_total'];
                                                        $totalCost = $ingredientsCost + $productionCost;
                                                    @endphp
                                                    @if($totalCost > 0)
                                                        <button type="button"
                                                                class="btn btn-link p-0 cost-detail-btn"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#costDetailModal"
                                                                data-product-id="{{ $data['product']->id }}"
                                                                data-date="{{ $date }}"
                                                                style="text-decoration: underline; color: #0a58ca;">
                                                            <strong>{{ number_format($totalCost, 2) }}</strong>
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <!-- Заголовок для расхода на хлеб -->
                                        <tr class="table-light">
                                            <td colspan="{{ 4 + count($ingredients) }}" class="text-center section-header">
                                                <h5><strong>РАСХОД НА ХЛЕБ</strong></h5>
                                            </td>
                                        </tr>
                                        <tr class="table-info">
                                            <td><strong>Итого:</strong></td>
                                            <td class="text-center">
                                                <strong>{{ array_sum(array_column($tableData, 'quantity_cart')) }}</strong>
                                            </td>
                                            <td class="text-center">
                                                <strong>{{ array_sum(array_column($tableData, 'quantity_total')) }}</strong>
                                            </td>
                                            @foreach($ingredients as $ingredient)
                                                <td class="text-center" title="{{ $ingredient->name }} ({{ $ingredient->unit }})">
                                                    @php
                                                        $totalUsage = 0;
                                                        foreach($tableData as $data) {
                                                            if(isset($data['ingredients'][$ingredient->id])) {
                                                                $totalUsage += $data['ingredients'][$ingredient->id];
                                                            }
                                                        }
                                                    @endphp
                                                    @if($totalUsage > 0)
                                                        <strong>{{ number_format($totalUsage, 2) }}</strong>
                                                    @endif
                                                </td>
                                            @endforeach
                                            <td class="text-center">
                                                @php
                                                    $totalCostAllProducts = 0;
                                                    foreach($tableData as $data) {
                                                        $ingredientsCost = 0;
                                                        foreach($ingredients as $ingredient) {
                                                            if(isset($data['ingredients'][$ingredient->id])) {
                                                                $ingredientsCost += $data['ingredients'][$ingredient->id] * $ingredient->price;
                                                            }
                                                        }
                                                        $productionCost = $data['product']->production_cost * $data['quantity_total'];
                                                        $totalCostAllProducts += $ingredientsCost + $productionCost;
                                                    }
                                                @endphp
                                                @if($totalCostAllProducts > 0)
                                                    <strong>{{ number_format($totalCostAllProducts, 2) }}</strong>
                                                @endif
                                            </td>
                                        </tr>

                                        <!-- Заголовок для прочих расходов -->
                                        <tr class="table-light">
                                            <td colspan="{{ 4 + count($ingredients) }}" class="text-center section-header">
                                                <h5><strong>ПРОЧИЕ РАСХОДЫ</strong></h5>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Не хватает:</strong></td>
                                            <td></td>
                                            <td></td>
                                            @foreach($ingredients as $ingredient)
                                                <td class="text-center" title="{{ $ingredient->name }} ({{ $ingredient->unit }})">
                                                    @if(isset($ingredientUsages[$ingredient->id]) && $ingredientUsages[$ingredient->id]->usage_missing > 0)
                                                        <strong>{{ number_format($ingredientUsages[$ingredient->id]->usage_missing, 2) }}</strong>
                                                    @endif
                                                </td>
                                            @endforeach
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Забрали со склада:</strong></td>
                                            <td></td>
                                            <td></td>
                                            @foreach($ingredients as $ingredient)
                                                <td class="text-center" title="{{ $ingredient->name }} ({{ $ingredient->unit }})">
                                                    @if(isset($ingredientUsages[$ingredient->id]) && $ingredientUsages[$ingredient->id]->usage_taken_from_stock > 0)
                                                        <strong>{{ number_format($ingredientUsages[$ingredient->id]->usage_taken_from_stock, 2) }}</strong>
                                                    @endif
                                                </td>
                                            @endforeach
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Кухня:</strong></td>
                                            <td></td>
                                            <td></td>
                                            @foreach($ingredients as $ingredient)
                                                <td class="text-center" title="{{ $ingredient->name }} ({{ $ingredient->unit }})">
                                                    @if(isset($ingredientUsages[$ingredient->id]) && $ingredientUsages[$ingredient->id]->usage_kitchen > 0)
                                                        <strong>{{ number_format($ingredientUsages[$ingredient->id]->usage_kitchen, 2) }}</strong>
                                                    @endif
                                                </td>
                                            @endforeach
                                            <td></td>
                                        </tr>

                                        <!-- Заголовок для итогового расхода -->
                                        <tr class="table-light">
                                            <td colspan="{{ 4 + count($ingredients) }}" class="text-center section-header">
                                                <h5><strong>ИТОГО РАСХОД</strong></h5>
                                            </td>
                                        </tr>
                                        <tr class="table-primary">
                                            <td><strong>Итого:</strong></td>
                                            <td></td>
                                            <td></td>
                                            @foreach($ingredients as $ingredient)
                                                <td class="text-center" title="{{ $ingredient->name }} ({{ $ingredient->unit }})">
                                                    @php
                                                        $totalUsage = 0;
                                                        foreach($tableData as $data) {
                                                            if(isset($data['ingredients'][$ingredient->id])) {
                                                                $totalUsage += $data['ingredients'][$ingredient->id];
                                                            }
                                                        }

                                                        $totalOtherUsage = 0;
                                                        if(isset($ingredientUsages[$ingredient->id])) {
                                                            $totalOtherUsage = $ingredientUsages[$ingredient->id]->usage_missing +
                                                                              $ingredientUsages[$ingredient->id]->usage_taken_from_stock +
                                                                              $ingredientUsages[$ingredient->id]->usage_kitchen;
                                                        }

                                                        $grandTotal = $totalUsage + $totalOtherUsage;
                                                    @endphp
                                                    @if($grandTotal > 0)
                                                        <strong>{{ number_format($grandTotal, 2) }}</strong>
                                                    @endif
                                                </td>
                                            @endforeach
                                            <td></td>
                                        </tr>

                                        <!-- Заголовок для прихода -->
                                        <tr class="table-light">
                                            <td colspan="{{ 4 + count($ingredients) }}" class="text-center section-header">
                                                <h5><strong>ПРИХОД</strong></h5>
                                            </td>
                                        </tr>
                                        <tr class="table-success">
                                            <td><strong>Итого:</strong></td>
                                            <td></td>
                                            <td></td>
                                            @foreach($ingredients as $ingredient)
                                                <td class="text-center" title="{{ $ingredient->name }} ({{ $ingredient->unit }})">
                                                    @if(isset($ingredientUsages[$ingredient->id]) && $ingredientUsages[$ingredient->id]->income > 0)
                                                        <strong>{{ number_format($ingredientUsages[$ingredient->id]->income, 2) }}</strong>
                                                    @endif
                                                </td>
                                            @endforeach
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle"></i>
                                На {{ date('d.m.Y', strtotime($date)) }} производство не осуществлялось
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="costDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Детализация себестоимости</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    <div id="costDetailContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/admin/ingredient_movements/show.js'])
@endpush
