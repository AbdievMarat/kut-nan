@php use App\Models\Order; @endphp
@extends('layouts.client')

@section('content')
    <div class="header">
        <img class="logo" src="{{ asset('logo.png') }}" alt="kut-nan"/>
    </div>

    <div class="content mb-4">
        <div class="content-text">Для оформления заказа или реализации <br/> введите номер машины</div>

        <form action="{{ route('orders.process_license_plate') }}" id="order_form" method="POST">
            @csrf
            <div class="field mb-3">
                <input
                    type="number"
                    name="license_plate"
                    class="form-control @error('license_plate') is-invalid @enderror"
                    id="license_plate"
                    placeholder="Введите номер машины"
                    value="{{ old('license_plate') }}"
                    autofocus
                    autocomplete="off"
                >
                @error('license_plate')
                    <span class="invalid-feedback fw-bold text-danger" role="alert">
                        {{ $message }}
                    </span>
                @enderror
            </div>

            <input type="hidden" name="type_operation" id="type_operation" value="">

            <button class="btn btn-success w-100" type="button" onclick="submitForm({{ Order::TYPE_OPERATION_ORDER }})">
                Оформить заказ
            </button>

            <button class="btn btn-primary w-100 mt-4" type="button" onclick="submitForm({{ Order::TYPE_OPERATION_REALIZATION }})">
                Оформить реализации
            </button>

            <button class="btn btn-info w-100 mt-4" type="button" onclick="submitForm({{ Order::TYPE_OPERATION_REMAINDER }})">
                Ввести остатки
            </button>

            <button class="btn btn-dark w-100 mt-4" type="button" onclick="submitForm({{ Order::TYPE_OPERATION_MARKDOWN }})">
                Ввести уценку
            </button>
        </form>
    </div>
@endsection

@push('scripts')
    @vite(['resources/css/client.css'])
@endpush

<script>
    function submitForm(typeOperation) {
        document.getElementById('type_operation').value = typeOperation;
        document.getElementById('order_form').submit();
    }
</script>