@extends('layouts.admin')

@section('content')
    <div class="container-sm">
        <a href="{{ route('admin.ingredients.index') }}" class="btn btn-outline-primary mb-3">&lang; Назад</a>
        <div class="row">
            <form
                    action="{{ route('admin.ingredients.store') }}"
                    method="post"
            >
                @csrf

                <h4>Создание ингредиента</h4>
                <hr>
                <div class="mb-3">
                    <x-admin-forms-input
                            type="text"
                            id="name"
                            name="name"
                            label="Название"
                            placeholder="Введите название"
                            value="{{ old('name') }}"
                    />
                </div>
                <div class="mb-3">
                    <x-admin-forms-input
                            type="text"
                            id="unit"
                            name="unit"
                            label="Единица измерения"
                            placeholder="Введите единицу измерения (кг, л, шт.)"
                            value="{{ old('unit') }}"
                    />
                </div>
                <div class="mb-3">
                    <x-admin-forms-input
                            type="number"
                            id="sort"
                            name="sort"
                            label="Сортировка"
                            placeholder="Введите сортировку"
                            value="{{ old('sort') }}"
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
                                @checked(old('is_active', 1) == 1)
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
                                @checked(old('is_active') == 0)
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
