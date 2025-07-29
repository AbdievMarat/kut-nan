@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Движение ингредиентов</span>
            <a href="{{ route('admin.ingredient-movements.create') }}" class="btn btn-success">
                <i class="bi bi-plus"></i> Добавить запись
            </a>
        </div>

        <!-- Форма фильтрации по датам -->
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('admin.ingredient-movements.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="date_from" class="form-label">Дата с:</label>
                    <input type="date"
                           class="form-control"
                           id="date_from"
                           name="date_from"
                           value="{{ $dateFrom }}">
                </div>
                <div class="col-md-3">
                    <label for="date_to" class="form-label">Дата по:</label>
                    <input type="date"
                           class="form-control"
                           id="date_to"
                           name="date_to"
                           value="{{ $dateTo }}">
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="bi bi-funnel"></i> Фильтровать
                    </button>
                    <a href="{{ route('admin.ingredient-movements.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-arrow-clockwise"></i> Сбросить
                    </a>
                    <a href="{{ route('admin.ingredient-movements.export_to_excel', ['date_from' => $dateFrom, 'date_to' => $dateTo]) }}"
                       class="btn btn-success">
                        <i class="bi bi-file-earmark-spreadsheet"></i> Экспорт в Excel
                    </a>
                </div>
            </form>
        </div>

        <div class="card-body overflow-auto">
            @if($tableData && count($tableData) > 0)
                <table class="table table-bordered table-hover table-sm">
                    <thead>
                        <tr>
                            <th rowspan="2" class="align-middle text-center">*</th>
                            <th rowspan="2" class="align-middle text-center">Дата</th>
                            @foreach ($ingredients as $ingredient)
                                <th colspan="3" class="text-center" title="{{ $ingredient->name }}">{{ $ingredient->short_name }} ({{ $ingredient->unit }})</th>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach ($ingredients as $ingredient)
                                <th class="text-center bg-success text-white vertical-text">Приход</th>
                                <th class="text-center bg-danger text-white vertical-text">Расход</th>
                                <th class="text-center bg-info text-white vertical-text">Остаток</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tableData as $date => $ingredientsData)
                            <tr>
                                <td class="text-center">
                                    @if($editableDates[$date] ?? false)
                                        <a href="{{ route('admin.ingredient-movements.edit-by-date', $date) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Редактировать запись за {{ date('d.m.Y', strtotime($date)) }}">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endif
                                </td>
                                <td class="fw-bold">
                                    <a href="{{ route('admin.ingredient-movements.show-by-date', $date) }}"
                                       class="text-decoration-none fw-bold"
                                       title="Просмотреть детальную информацию по производству">
                                        {{ date('d.m.Y', strtotime($date)) }}
                                    </a>
                                </td>
                                @foreach ($ingredients as $ingredient)
                                    @php
                                        $data = $ingredientsData[$ingredient->id] ?? null;
                                    @endphp
                                    <td class="text-center">
                                        @if($data && isset($data['income']))
                                            <span class="text-success fw-bold">{{ number_format($data['income'], 2) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($data && isset($data['usage']))
                                            <span class="text-danger fw-bold">{{ number_format($data['usage'], 2) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($data && isset($data['stock']))
                                            <span class="fw-bold">{{ number_format($data['stock'], 2) }}</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle"></i>
                    Данные о движении ингредиентов отсутствуют за выбранный период
                </div>
            @endif
        </div>
    </div>
@endsection
