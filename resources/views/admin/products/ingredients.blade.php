@extends('layouts.admin')

@section('content')
    <div class="container-sm">
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-primary mb-3">&lang; Назад</a>
        <div class="card">
            <div class="card-header">
                Калькуляция ингредиентов продукта: {{ $product->name }}
            </div>
            <div class="card-body">
                <div class="row">
                    <form action="{{ route('admin.products.ingredients.store', ['product' => $product]) }}" method="post">
                        @csrf

                        @foreach($ingredients as $ingredient)
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="ingredient_{{ $ingredient->id }}">{{ $ingredient->name }}</span>
                                <input
                                    class="form-control"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="ingredient_amounts[{{ $ingredient->id }}]"
                                    id="ingredient_{{ $ingredient->id }}"
                                    value="{{ $productIngredients->get($ingredient->id)?->pivot?->amount ?? old('ingredients.'.$ingredient->id.'.amount') }}"
                                    placeholder="Количество"
                                    autocomplete="off"
                                >
                                <span class="input-group-text">{{ $ingredient->unit }}</span>
                            </div>
                        @endforeach

                        <button type="submit" class="btn btn-success w-100 mb-3">Сохранить</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
