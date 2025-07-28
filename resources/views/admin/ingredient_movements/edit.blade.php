@extends('layouts.admin')

@section('title', 'Редактирование записи движения ингредиентов')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Редактирование записи движения сырья за {{ date('d.m.Y', strtotime($date)) }}</h3>
                </div>
                <form action="{{ route('admin.ingredient-movements.update-by-date', $date) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <input type="hidden" name="date" value="{{ $date }}">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead>
                                            <tr>
                                                <th colspan="4" class="text-center table-secondary">
                                                    <h5 class="mb-0">Произведенная продукция</h5>
                                                </th>
                                            </tr>
                                            <tr>
                                                <th style="width: 40%;" class="text-center">Продукт</th>
                                                <th style="width: 20%;" class="text-center">Тележки</th>
                                                <th style="width: 20%;" class="text-center">Шт. на тележку</th>
                                                <th style="width: 20%;" class="text-center">Всего шт.</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($products as $product)
                                            <tr>
                                                <td class="align-middle">{{ $product->name }}</td>
                                                <td>
                                                    <input type="number"
                                                           class="form-control form-control-sm product-carts @error('products.'.$product->id.'.quantity_cart') is-invalid @enderror"
                                                           name="products[{{ $product->id }}][quantity_cart]"
                                                           data-product-id="{{ $product->id }}"
                                                           data-pieces-per-cart="{{ $product->pieces_per_cart }}"
                                                           step="0.5"
                                                           value="{{ old('products.'.$product->id.'.quantity_cart', $existingProductBatches->get($product->id)?->quantity_cart ?? '') }}">
                                                    <input type="hidden"
                                                           name="products[{{ $product->id }}][pieces_per_cart]"
                                                           value="{{ $product->pieces_per_cart }}">
                                                    @error('products.'.$product->id.'.quantity_cart')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td class="align-middle text-center">{{ $product->pieces_per_cart }}</td>
                                                <td class="align-middle text-center">
                                                    <span class="total-pieces" data-product-id="{{ $product->id }}">
                                                        {{ ($existingProductBatches->get($product->id)?->quantity_cart ?? 0) * $product->pieces_per_cart }}
                                                    </span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead>
                                            <tr>
                                                <th colspan="5" class="text-center table-secondary">
                                                    <h5 class="mb-0">Приход и расход сырья</h5>
                                                </th>
                                            </tr>
                                            <tr>
                                                <th rowspan="3" style="width: 35%;" class="text-center">Ингредиент</th>
                                                <th rowspan="3" style="width: 20%;" class="text-center">Приход</th>
                                                <th colspan="3" style="width: 30%;" class="text-center">Прочие расходы</th>
                                            </tr>
                                            <tr>
                                                <th style="width: 15%;" class="text-center">Не хватает</th>
                                                <th style="width: 15%;" class="text-center">Забрали со склада</th>
                                                <th style="width: 15%;" class="text-center">Кухня</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($ingredients as $ingredient)
                                            <tr>
                                                <td class="align-middle">{{ $ingredient->name }} ({{ $ingredient->unit }})</td>
                                                <td>
                                                    <input type="number"
                                                           class="form-control form-control-sm @error('ingredients.'.$ingredient->id.'.income') is-invalid @enderror"
                                                           name="ingredients[{{ $ingredient->id }}][income]"
                                                           step="0.01"
                                                           value="{{ old('ingredients.'.$ingredient->id.'.income', $existingIngredientUsages->get($ingredient->id)?->income ?? '') }}">
                                                    @error('ingredients.'.$ingredient->id.'.income')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td>
                                                    <input type="number"
                                                           class="form-control form-control-sm @error('ingredients.'.$ingredient->id.'.usage_missing') is-invalid @enderror"
                                                           name="ingredients[{{ $ingredient->id }}][usage_missing]"
                                                           step="0.01"
                                                           value="{{ old('ingredients.'.$ingredient->id.'.usage_missing', $existingIngredientUsages->get($ingredient->id)?->usage_missing ?? '') }}">
                                                    @error('ingredients.'.$ingredient->id.'.usage_missing')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td>
                                                    <input type="number"
                                                           class="form-control form-control-sm @error('ingredients.'.$ingredient->id.'.usage_taken_from_stock') is-invalid @enderror"
                                                           name="ingredients[{{ $ingredient->id }}][usage_taken_from_stock]"
                                                           step="0.01"
                                                           value="{{ old('ingredients.'.$ingredient->id.'.usage_taken_from_stock', $existingIngredientUsages->get($ingredient->id)?->usage_taken_from_stock ?? '') }}">
                                                    @error('ingredients.'.$ingredient->id.'.usage_taken_from_stock')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td>
                                                    <input type="number"
                                                           class="form-control form-control-sm @error('ingredients.'.$ingredient->id.'.usage_kitchen') is-invalid @enderror"
                                                           name="ingredients[{{ $ingredient->id }}][usage_kitchen]"
                                                           step="0.01"
                                                           value="{{ old('ingredients.'.$ingredient->id.'.usage_kitchen', $existingIngredientUsages->get($ingredient->id)?->usage_kitchen ?? '') }}">
                                                    @error('ingredients.'.$ingredient->id.'.usage_kitchen')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-block" style="width: 100%">Обновить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    @vite(['resources/js/admin/ingredient_movements/create.js'])
@endpush

@endsection
