@extends('layouts.admin')

@section('content')
    <div class="container-sm">
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-primary mb-3">&lang; Назад</a>
        <div class="card">
            <div class="card-header">
                Калькуляция ингредиентов на одну тележку ({{ $product->pieces_per_cart }} шт.) у продукта: {{ $product->name }}
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="mb-3">
                        <small class="text-muted">
                            <strong>Доступные переменные:</strong><br>
                            <code>$quantity</code> - количество штук продукта<br>
                            <code>$portion</code> - количество штук на одну тележку ({{ $product->pieces_per_cart }} шт.) <br>
                            <strong>Пример:</strong> <code>$quantity * 33 / $portion</code>
                        </small>
                    </div>

                    <form action="{{ route('admin.products.ingredients.store', ['product' => $product]) }}" method="post">
                        @csrf

                        @foreach($ingredients as $ingredient)
                            <div class="mb-3">
                                <div class="input-group">
                                    <span class="input-group-text" id="ingredient_{{ $ingredient->id }}">{{ $ingredient->name }}</span>
                                    <input
                                        class="form-control @error('ingredient_formula.'.$ingredient->id) is-invalid @enderror"
                                        type="text"
                                        name="ingredient_formula[{{ $ingredient->id }}]"
                                        id="ingredient_{{ $ingredient->id }}"
                                        value="{{ old('ingredient_formula.'.$ingredient->id, $productIngredients->get($ingredient->id)?->pivot?->formula) }}"
                                        placeholder="Введите формулу (например: $quantity * 33 / $portion)"
                                        autocomplete="off"
                                    >
                                    <span class="input-group-text">{{ $ingredient->unit }}</span>
                                </div>
                                <!-- Отображение ошибок для конкретного ингредиента -->
                                @error('ingredient_formula.'.$ingredient->id)
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        @endforeach

                        <button type="submit" class="btn btn-success w-100 mb-3">Сохранить</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
