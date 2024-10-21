@extends('layouts.admin')

@section('content')
    <div class="container-sm">
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-primary mb-3">&lang; Назад</a>
        <div class="row">
            <form
                    action="{{ route('admin.products.update', ['product' => $product]) }}"
                    method="post"
            >
                @csrf
                @method('put')

                <h4>Редактирование продукта</h4>
                <hr>
                <div class="mb-3">
                    <x-admin-forms-input
                            type="text"
                            id="name"
                            name="name"
                            label="Название"
                            placeholder="Введите название"
                            value="{{ old('name') ?? $product->name }}"
                    />
                </div>
                <div class="mb-3">
                    <x-admin-forms-input
                            type="number"
                            id="price"
                            name="price"
                            label="Цена"
                            placeholder="Введите цену"
                            value="{{ old('price') ?? $product->price }}"
                    />
                </div>
                <div class="mb-3">
                    <x-admin-forms-input
                            type="number"
                            id="sort"
                            name="sort"
                            label="Сортировка"
                            placeholder="Введите сортировку"
                            value="{{ old('sort') ?? $product->sort }}"
                    />
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input
                                class="form-check-input"
                                type="radio"
                                name="is_active"
                                id="flexRadioDefault1"
                                value="1"
                                @checked($product->is_active == \App\Models\Product::IS_ACTIVE)
                        />
                        <label class="form-check-label" for="flexRadioDefault1">
                            Активный
                        </label>
                    </div>
                    <div class="form-check">
                        <input
                                class="form-check-input"
                                type="radio"
                                name="is_active"
                                id="flexRadioDefault2"
                                value="0"
                                @checked($product->is_active == \App\Models\Product::IS_NOT_ACTIVE)
                        />
                        <label class="form-check-label" for="flexRadioDefault2">
                            Не активный
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-success float-end">Создать</button>
            </form>
        </div>
    </div>
@endsection
