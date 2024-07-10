@extends('layouts.admin')

@section('content')
    <div class="container-sm">
        <a href="{{ route('admin.buses.index') }}" class="btn btn-outline-primary mb-3">&lang; Назад</a>
        <div class="row">
            <form
                    action="{{ route('admin.buses.update', ['bus' => $bus]) }}"
                    method="post"
            >
                @csrf
                @method('put')

                <h4>Создание буса</h4>
                <hr>
                <div class="mb-3">
                    <x-admin-forms-input
                            type="number"
                            id="license_plate"
                            name="license_plate"
                            label="Гос. номер"
                            placeholder="Введите гос. номер"
                            value="{{ old('license_plate') ?? $bus->license_plate }}"
                    />
                </div>
                <div class="mb-3">
                    <x-admin-forms-input
                            type="text"
                            id="serial_number"
                            name="serial_number"
                            label="Порядковый номер"
                            placeholder="Введите порядковый номер"
                            value="{{ old('serial_number') ?? $bus->serial_number }}"
                    />
                </div>
                <div class="mb-3">
                    <x-admin-forms-input
                            type="text"
                            id="sort"
                            name="sort"
                            label="Сортировка"
                            placeholder="Введите сортировку"
                            value="{{ old('sort') ?? $bus->sort }}"
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
                                @checked($bus->is_active == \App\Models\Bus::IS_ACTIVE)
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
                                @checked($bus->is_active == \App\Models\Bus::IS_NOT_ACTIVE)
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
