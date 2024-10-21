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
                    <th>Цена</th>
                    <th>Сортировка</th>
                    <th>Статус</th>
                    <th style="width: 160px"></th>
                </tr>
                </thead>
                <tbody class="table-group-divider">
                @foreach($products as $product)
                    <tr>
                        <td>{{ $product->id }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->price }}</td>
                        <td>{{ $product->sort }} </td>
                        <td>
                            <div class="badge bg-{{ $product->is_active ? 'success' : 'danger' }}">
                                {{ $product->is_active ? 'Активен' : 'Не активен' }}
                            </div>
                        </td>
                        <td>
                            <div class="d-flex justify-content-end">
                                <div class="mx-2">
                                    <a href="{{ route('admin.products.edit', ['product' => $product]) }}" type="button"
                                       class="btn btn-warning"><i class="bi bi-pencil-square"></i></a>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="6">
                        {{ $products->links() }}
                    </th>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
