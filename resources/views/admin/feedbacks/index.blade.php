@extends('layouts.admin')

@section('content')
    <style>
        #feedbacks-table tbody tr:hover {
            background-color: #f8f9fa;
        }
    </style>
    <div class="card">
        <div class="card-header">Обратная связь</div>
        <div class="card-body overflow-auto">
            <table id="feedbacks-table" class="table table-bordered table-hover">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Дата</th>
                    <th>Анонимно</th>
                    <th>ФИО</th>
                    <th>Телефон</th>
                    <th>Сообщение</th>
                    <th>Отправлено на почту</th>
                </tr>
                </thead>
                <tbody class="table-group-divider">
                @foreach($feedbacks as $feedback)
                    <tr style="cursor: pointer;" onclick="window.location='{{ route('admin.feedbacks.show', $feedback) }}'">
                        <td>{{ $feedback->id }}</td>
                        <td>{{ $feedback->created_at->format('d.m.Y H:i') }}</td>
                        <td>
                            <div class="badge bg-{{ $feedback->is_anonymous ? 'success' : 'secondary' }}">
                                {{ $feedback->is_anonymous ? 'Да' : 'Нет' }}
                            </div>
                        </td>
                        <td>{{ $feedback->is_anonymous ? '-' : ($feedback->full_name ?? '-') }}</td>
                        <td>{{ $feedback->is_anonymous ? '-' : ($feedback->phone ?? '-') }}</td>
                        <td>{{ Str::limit($feedback->message, 100) }}</td>
                        <td>
                            <div class="badge bg-{{ $feedback->is_send ? 'success' : 'warning' }}">
                                {{ $feedback->is_send ? 'Да' : 'Нет' }}
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="7">
                        {{ $feedbacks->links() }}
                    </th>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection

