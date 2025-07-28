@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header">Ингредиенты</div>
        <div class="card-body overflow-auto">
            <div class="d-flex justify-content-between mb-3">
                <div></div>
                <div>
                    <a href="{{ route('admin.ingredients.create') }}" class="btn btn-success float-end">Создать</a>
                </div>
            </div>

            <table id="ingredients-table" class="table table-bordered table-hover">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Название</th>
                    <th>Сокращенное название</th>
                    <th>Единица измерения</th>
                    <th>Сортировка</th>
                    <th>Статус</th>
                    <th style="width: 160px"></th>
                </tr>
                </thead>
                <tbody class="table-group-divider">
                @foreach($ingredients as $ingredient)
                    <tr>
                        <td>{{ $ingredient->id }}</td>
                        <td>{{ $ingredient->name }}</td>
                        <td>{{ $ingredient->short_name }}</td>
                        <td>{{ $ingredient->unit }}</td>
                        <td>{{ $ingredient->sort }} </td>
                        <td>
                            <div class="badge bg-{{ $ingredient->is_active ? 'success' : 'danger' }}">
                                {{ $ingredient->is_active ? 'Активен' : 'Не активен' }}
                            </div>
                        </td>
                        <td>
                            <div class="d-flex justify-content-end">
                                <div class="mx-2">
                                    <a href="{{ route('admin.ingredients.edit', ['ingredient' => $ingredient]) }}"
                                       type="button"
                                       class="btn btn-warning btn-sm d-flex align-items-center"
                                       title="Редактировать">
                                        <i class="bi bi-pencil-square me-1"></i>
                                        <span>Редактировать</span>
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="7">
                        {{ $ingredients->links() }}
                    </th>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
