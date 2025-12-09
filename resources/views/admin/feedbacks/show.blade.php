@extends('layouts.admin')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">
            <i class="bi bi-chat-left-text me-2"></i>
            Обратная связь #{{ $feedback->id }}
        </h3>
        <a href="{{ route('admin.feedbacks.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Назад к списку
        </a>
    </div>

    <div class="row">
        <!-- Основная информация -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-chat-dots me-2 text-primary"></i>
                        Сообщение
                    </h5>
                </div>
                <div class="card-body">
                    <div class="message-content p-4 bg-light rounded border-start border-primary border-4" style="white-space: pre-wrap; word-wrap: break-word; min-height: 150px; font-size: 1.05rem; line-height: 1.6;">
                        {{ $feedback->message }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Дополнительная информация -->
        <div class="col-lg-4">
            <!-- Статусы -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-2 text-info"></i>
                        Статус
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">
                                <i class="bi bi-person-check me-2"></i>Анонимно:
                            </span>
                            <span class="badge bg-{{ $feedback->is_anonymous ? 'success' : 'secondary' }} fs-6">
                                {{ $feedback->is_anonymous ? 'Да' : 'Нет' }}
                            </span>
                        </div>
                    </div>
                    <div class="mb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">
                                <i class="bi bi-envelope-check me-2"></i>Отправлено:
                            </span>
                            <span class="badge bg-{{ $feedback->is_send ? 'success' : 'warning' }} fs-6">
                                {{ $feedback->is_send ? 'Да' : 'Нет' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Контактная информация -->
            @if(!$feedback->is_anonymous)
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">
                            <i class="bi bi-person-lines-fill me-2 text-success"></i>
                            Контакты
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex align-items-start mb-2">
                                <i class="bi bi-person me-2 text-muted mt-1"></i>
                                <div class="flex-grow-1">
                                    <div class="text-muted small">ФИО</div>
                                    <div class="fw-semibold">{{ $feedback->full_name ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-0">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-telephone me-2 text-muted mt-1"></i>
                                <div class="flex-grow-1">
                                    <div class="text-muted small">Телефон</div>
                                    <div class="fw-semibold">{{ $feedback->phone ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Метаданные -->
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history me-2 text-secondary"></i>
                        Информация
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex align-items-start mb-2">
                            <i class="bi bi-hash me-2 text-muted mt-1"></i>
                            <div class="flex-grow-1">
                                <div class="text-muted small">ID</div>
                                <div class="fw-semibold">#{{ $feedback->id }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-0">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-calendar3 me-2 text-muted mt-1"></i>
                            <div class="flex-grow-1">
                                <div class="text-muted small">Дата создания</div>
                                <div class="fw-semibold">{{ $feedback->created_at->format('d.m.Y') }}</div>
                                <div class="text-muted small">{{ $feedback->created_at->format('H:i:s') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

