@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header">Бусы</div>
        <div class="card-body overflow-auto">
            <div class="d-flex justify-content-between mb-3">
                <div></div>
                <div>
                    <a href="{{ route('admin.products.create') }}" class="btn btn-success float-end">Создать</a>
                </div>
            </div>

            <table id="products-table" class="table table-bordered table-hover">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Название</th>
                    <th>Сортировка</th>
                    <th>Количество штук на одну тележку</th>
                    <th>Статус</th>
                    <th>Отображение в отчёте</th>
                    <th style="width: 160px"></th>
                </tr>
                </thead>
                <tbody class="table-group-divider">
                @foreach($products as $product)
                    <tr>
                        <td>{{ $product->id }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->sort }} </td>
                        <td>{{ $product->pieces_per_cart }}</td>
                        <td>
                            <div class="badge bg-{{ $product->is_active ? 'success' : 'danger' }}">
                                {{ $product->is_active ? 'Активен' : 'Не активен' }}
                            </div>
                        </td>
                        <td>
                            <div class="badge bg-{{ $product->is_in_report ? 'success' : 'danger' }}">
                                {{ $product->is_in_report ? 'Отображать' : 'Не отображать' }}
                            </div>
                        </td>
                        <td>
                            <div class="d-flex justify-content-end align-items-center">
                                <div class="mx-1">
                                    <a href="{{ route('admin.products.ingredients', ['product' => $product]) }}"
                                       class="btn btn-info btn-sm d-flex align-items-center"
                                       title="Калькуляция ингредиентов">
                                       <i class="bi bi-calculator me-1"></i>
                                       <span>Калькуляция</span>
                                    </a>
                                </div>
                                <div class="mx-1">
                                    <a href="{{ route('admin.products.edit', ['product' => $product]) }}"
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
                        {{ $products->links() }}
                    </th>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
