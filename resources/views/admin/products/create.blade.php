@extends('layouts.admin')

@section('content')
    <div class="container-sm">
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-primary mb-3">&lang; Назад</a>
        <div class="row">
            <form
                    action="{{ route('admin.products.store') }}"
                    method="post"
            >
                @csrf

                <h4>Создание продукта</h4>
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
                                name="is_in_report"
                                id="isInReportRadio"
                                value="1"
                                @checked(old('is_in_report') == \App\Models\Product::IS_IN_REPORT)
                        />
                        <label class="form-check-label" for="isInReportRadio">
                            Отображать в отчёте
                        </label>
                    </div>
                    <div class="form-check">
                        <input
                                class="form-check-input"
                                type="radio"
                                name="is_in_report"
                                id="isNotInReportRadio"
                                value="0"
                                @checked(old('is_in_report') == \App\Models\Product::IS_NOT_IN_REPORT)
                        />
                        <label class="form-check-label" for="isNotInReportRadio">
                            Не отображать в отчёте
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-success float-end">Создать</button>
            </form>
        </div>
    </div>
@endsection
