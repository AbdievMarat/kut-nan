@extends('layouts.admin')

@section('content')
    <div class="container-sm">
        <a href="{{ route('admin.ingredients.index') }}" class="btn btn-outline-primary mb-3">&lang; Назад</a>
        <div class="row">
            <form
                    action="{{ route('admin.ingredients.update', ['ingredient' => $ingredient]) }}"
                    method="post"
            >
                @csrf
                @method('put')

                <h4>Редактирование ингредиента</h4>
                <hr>
                <div class="mb-3">
                    <x-admin-forms-input
                            type="text"
                            id="name"
                            name="name"
                            label="Название"
                            placeholder="Введите название"
                            value="{{ old('name') ?? $ingredient->name }}"
                    />
                </div>
                <div class="mb-3">
                    <x-admin-forms-input
                            type="text"
                            id="short_name"
                            name="short_name"
                            label="Сокращенное название"
                            placeholder="Введите сокращенное название"
                            value="{{ old('short_name') ?? $ingredient->short_name }}"
                    />
                </div>
                <div class="mb-3">
                    <x-admin-forms-input
                            type="text"
                            id="unit"
                            name="unit"
                            label="Единица измерения"
                            placeholder="Введите единицу измерения (кг, л, шт.)"
                            value="{{ old('unit') ?? $ingredient->unit }}"
                    />
                </div>
                <div class="mb-3">
                    <x-admin-forms-input
                            type="number"
                            id="sort"
                            name="sort"
                            label="Сортировка"
                            placeholder="Введите сортировку"
                            value="{{ old('sort') ?? $ingredient->sort }}"
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
                                @checked($ingredient->is_active == \App\Models\Ingredient::IS_ACTIVE)
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
                                @checked($ingredient->is_active == \App\Models\Ingredient::IS_NOT_ACTIVE)
                        />
                        <label class="form-check-label" for="flexRadioDefault2">
                            Не активный
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-success float-end">Обновить</button>
            </form>
        </div>
    </div>
@endsection
