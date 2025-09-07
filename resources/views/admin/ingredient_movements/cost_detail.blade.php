<div class="mb-3">
    <h6 class="fw-bold text-primary">Продукт: {{ $costData['product_name'] }}</h6>
    <small class="text-muted">Количество произведенных штук: {{ $costData['quantity_total'] }}</small>
</div>

<div class="row">
    <div class="col-md-8">
        <h6 class="fw-bold mb-3">Стоимость ингредиентов:</h6>
        <div class="table-responsive">
            <table class="table table-sm table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Ингредиент</th>
                        <th class="text-center">Количество</th>
                        <th class="text-center">Цена за ед.</th>
                        <th class="text-center">Стоимость</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($costData['ingredients'] as $ingredient)
                        <tr>
                            <td>{{ $ingredient['name'] }}</td>
                            <td class="text-center">{{ number_format($ingredient['amount'], 2) }} {{ $ingredient['unit'] }}</td>
                            <td class="text-center">{{ number_format($ingredient['price'], 2) }}</td>
                            <td class="text-center"><strong>{{ number_format($ingredient['cost'], 2) }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="3">Итого ингредиенты:</th>
                        <th class="text-center">{{ number_format($costData['total_ingredients_cost'], 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="col-md-4">
        <h6 class="fw-bold mb-3">Производственные расходы:</h6>
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Расход на штуку:</span>
                    <strong>{{ number_format($costData['production_cost_per_unit'], 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Количество штук:</span>
                    <strong>{{ $costData['quantity_total'] }}</strong>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <span class="fw-bold">Итого производство:</span>
                    <strong class="text-primary">{{ number_format($costData['total_production_cost'], 2) }}</strong>
                </div>
            </div>
        </div>

        <div class="card mt-3 border-success">
            <div class="card-body">
                <h6 class="card-title text-success mb-3">Общая себестоимость</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span>Ингредиенты:</span>
                    <span>{{ number_format($costData['total_ingredients_cost'], 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Производство:</span>
                    <span>{{ number_format($costData['total_production_cost'], 2) }}</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <span class="fw-bold fs-5">ИТОГО:</span>
                    <strong class="text-success fs-5">{{ number_format($costData['total_cost'], 2) }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>
