<div class="container-fluid p-0">
    <!-- Заголовок с информацией о продукте -->
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold text-primary mb-1">
                        <i class="bi bi-box me-2"></i>{{ $costData['product_name'] }}
                    </h6>
                    <small class="text-muted">
                        <i class="bi bi-calculator me-1"></i>
                        Количество произведенных штук:
                        <span class="badge bg-info rounded-pill">{{ number_format($costData['quantity_total']) }}</span>
                    </small>
                </div>
                <div class="text-end">
                    <div class="badge bg-success px-2 py-1">
                        Себестоимость за шт.: {{ number_format($costData['total_cost'] / $costData['quantity_total'], 2) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Стоимость ингредиентов -->
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header bg-light py-2">
                    <h6 class="fw-bold mb-0 text-dark fs-6">
                        <i class="bi bi-flower1 me-2 text-success"></i>Стоимость ингредиентов
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0 fw-semibold py-2">Ингредиент</th>
                                    <th class="text-center border-0 fw-semibold py-2">Количество</th>
                                    <th class="text-center border-0 fw-semibold py-2" style="width: 100px;">Цена за ед.</th>
                                    <th class="text-center border-0 fw-semibold py-2" style="width: 100px;">Стоимость</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($costData['ingredients'] as $ingredient)
                                    <tr class="align-middle">
                                        <td class="py-2">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px;">
                                                    <i class="bi bi-flower2 text-success" style="font-size: 12px;"></i>
                                                </div>
                                                <span class="fw-medium">{{ $ingredient['name'] }}</span>
                                            </div>
                                        </td>
                                        <td class="text-center py-2">
                                            <span class="badge bg-secondary bg-opacity-10 text-dark px-2 py-1">
                                                {{ number_format($ingredient['amount'], 2) }} {{ $ingredient['unit'] }}
                                            </span>
                                        </td>
                                        <td class="text-center py-2">
                                            <a href="{{ route('admin.ingredients.edit', $ingredient['id']) }}"
                                               class="text-decoration-none btn btn-outline-primary btn-sm py-1 px-2"
                                               >
                                                <i class="bi bi-pencil me-1"></i>
                                                {{ $ingredient['price'] }} сом
                                            </a>
                                        </td>
                                        <td class="text-center py-2">
                                            <span class="fw-bold text-success">
                                                {{ number_format($ingredient['cost'], 2) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer bg-success bg-opacity-10 py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-success">
                                <i class="bi bi-calculator me-2"></i>Итого расходы на ингредиенты:
                            </span>
                            <span class="fw-bold text-success">
                                {{ number_format($costData['total_ingredients_cost'], 2) }} сом
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Производственные расходы и итоговая стоимость -->
        <div class="col-lg-5">
            <!-- Производственные расходы -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-warning bg-opacity-10 py-2">
                    <h6 class="fw-bold mb-0 text-dark fs-6">
                        <i class="bi bi-gear me-2 text-warning"></i>Производственные расходы
                    </h6>
                </div>
                <div class="card-body py-3">
                    <div class="row g-2">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                                <div>
                                    <i class="bi bi-gear-fill text-warning me-2"></i>
                                    <span>Расход на штуку:</span>
                                </div>
                                <a href="{{ route('admin.products.edit', $costData['product_id']) }}"
                                   class="text-decoration-none btn btn-outline-primary btn-sm py-1 px-2">
                                    <i class="bi bi-pencil me-1"></i>
                                    {{ number_format($costData['production_cost_per_unit'], 2) }} сом
                                </a>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                                <div>
                                    <i class="bi bi-boxes text-info me-2"></i>
                                    <span>Количество штук:</span>
                                </div>
                                <span class="badge bg-info rounded-pill px-2 py-1">
                                    {{ number_format($costData['quantity_total']) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <hr class="my-3">
                    <div class="bg-warning bg-opacity-10 p-2 rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-warning">
                                <i class="bi bi-calculator me-2"></i>Итого расходы на производство:
                            </span>
                            <span class="fw-bold text-warning">
                                {{ number_format($costData['total_production_cost'], 2) }} сом
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Общая себестоимость -->
            <div class="card border-success shadow">
                <div class="card-header bg-success text-white py-2">
                    <h6 class="card-title mb-0 fw-bold fs-6">
                        <i class="bi bi-graph-up me-2"></i>Общая себестоимость
                    </h6>
                </div>
                <div class="card-body bg-success bg-opacity-5 py-3">
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="text-center p-2 bg-white rounded shadow-sm">
                                <i class="bi bi-flower1 text-success fs-5 mb-1"></i>
                                <div class="small text-muted">Ингредиенты</div>
                                <div class="fw-bold text-success small">
                                    {{ number_format($costData['total_ingredients_cost'], 2) }} сом
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-2 bg-white rounded shadow-sm">
                                <i class="bi bi-gear text-warning fs-5 mb-1"></i>
                                <div class="small text-muted">Производство</div>
                                <div class="fw-bold text-warning small">
                                    {{ number_format($costData['total_production_cost'], 2) }} сом
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-success text-white p-3 rounded text-center">
                        <div class="mb-2">
                            <i class="bi bi-equals me-2"></i>ИТОГО:
                        </div>
                        <div class="fs-3 fw-bold">
                            {{ number_format($costData['total_cost'], 2) }} сом
                        </div>
                        <small class="opacity-75">
                            ({{ number_format($costData['total_cost'] / $costData['quantity_total'], 2) }} сом за штуку)
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Icons -->
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
@endpush
