@extends('layouts.client')

@section('content')
    <div class="header">
        <img class="logo" src="{{ asset('logo.png') }}" alt="kut-nan"/>
    </div>

    <div class="content mb-4">
        <div class="content-text">Обратная связь<br/>Жалобы и предложения</div>

        <form action="{{ route('feedback.store') }}" method="POST" id="feedback-form">
            @csrf

            <!-- Анонимно поле -->
            <div class="mb-4">
                <div class="d-flex align-items-center justify-content-between p-3 border rounded bg-light">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-person-check-fill me-2 text-primary"></i>
                        <div>
                            <label class="form-label fw-bold mb-0" for="is_anonymous_switch">Указать контакты</label>
                            <small class="text-muted d-block">Оставьте включенным, чтобы мы могли связаться с вами</small>
                        </div>
                    </div>
                    <div class="form-check form-switch form-switch-lg ms-3">
                        <input 
                            class="form-check-input" 
                            type="checkbox" 
                            role="switch" 
                            id="is_anonymous_switch"
                            name="is_anonymous_switch"
                            {{ old('is_anonymous', '0') == '0' ? 'checked' : '' }}
                        >
                    </div>
                </div>
                <input type="hidden" name="is_anonymous" id="is_anonymous" value="{{ old('is_anonymous', '0') }}">
                @error('is_anonymous')
                    <div class="text-danger small mt-2">
                        <strong>{{ $message }}</strong>
                    </div>
                @enderror
            </div>

            <!-- Контактные поля (показываются только если не анонимно) -->
            <div id="contact-fields" class="mb-4" style="display: {{ old('is_anonymous', '0') == '0' ? 'block' : 'none' }};">
                <div class="form-floating mb-3">
                    <input
                        type="text"
                        name="full_name"
                        class="form-control form-control-lg @error('full_name') is-invalid @enderror"
                        id="full_name"
                        placeholder="ФИО"
                        value="{{ old('full_name') }}"
                        autocomplete="off"
                    >
                    <label for="full_name">ФИО</label>
                    <div class="validation-feedback" id="full_name_feedback"></div>
                    @error('full_name')
                        <div class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </div>
                    @enderror
                </div>

                <div class="form-floating mb-3">
                    <input
                        type="tel"
                        name="phone"
                        class="form-control form-control-lg @error('phone') is-invalid @enderror"
                        id="phone"
                        placeholder="Номер телефона (996XXXXXXXXX)"
                        value="{{ old('phone') }}"
                        autocomplete="off"
                        maxlength="12"
                    >
                    <label for="phone">Номер телефона (996XXXXXXXXX)</label>
                    <div class="validation-feedback" id="phone_feedback"></div>
                    @error('phone')
                        <div class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </div>
                    @enderror
                </div>
            </div>

            <!-- Сообщение -->
            <div class="form-floating mb-4">
                <textarea
                    name="message"
                    class="form-control @error('message') is-invalid @enderror"
                    id="message"
                    placeholder="Ваше сообщение"
                    style="height: 150px;"
                    autocomplete="off"
                >{{ old('message') }}</textarea>
                <label for="message">Ваше сообщение</label>
                <div class="validation-feedback" id="message_feedback"></div>
                @error('message')
                    <div class="invalid-feedback">
                        <strong>{{ $message }}</strong>
                    </div>
                @enderror
            </div>

            <button type="submit" class="btn btn-success btn-lg w-100 shadow-sm">
                <i class="bi bi-send-fill me-2"></i>
                Отправить обратную связь
            </button>
        </form>
    </div>
@endsection

@push('scripts')
    @vite(['resources/css/client.css', 'resources/js/client/feedback/create.js'])
@endpush

