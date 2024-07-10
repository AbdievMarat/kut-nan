@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header">Бусы</div>
        <div class="card-body overflow-auto">
            <div class="d-flex justify-content-between mb-3">
                <div></div>
                <div>
                    <a href="{{ route('admin.buses.create') }}" class="btn btn-success float-end">Создать</a>
                </div>
            </div>

            <table id="buses-table" class="table table-bordered table-hover">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Гос. номер</th>
                    <th>Порядковый номер</th>
                    <th>Сортировка</th>
                    <th>Статус</th>
                    <th style="width: 160px"></th>
                </tr>
                </thead>
                <tbody class="table-group-divider">
                @foreach($buses as $bus)
                    <tr>
                        <td>{{ $bus->id }}</td>
                        <td>{{ $bus->license_plate }}</td>
                        <td>{{ $bus->serial_number }}</td>
                        <td>{{ $bus->sort }} </td>
                        <td>
                            <div class="badge bg-{{ $bus->is_active ? 'success' : 'danger' }}">
                                {{ $bus->is_active ? 'Активен' : 'Не активен' }}
                            </div>
                        </td>
                        <td>
                            <div class="d-flex justify-content-end">
                                <div class="mx-2">
                                    <a href="{{ route('admin.buses.edit', ['bus' => $bus]) }}" type="button"
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
                        {{ $buses->links() }}
                    </th>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
