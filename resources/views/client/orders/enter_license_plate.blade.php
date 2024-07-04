@extends('layouts.client')

@section('content')
    <div class="header">
        <img class="logo" src="{{ asset('logo.png') }}" alt="kut-nan" />
    </div>

    <div class="content mb-4">
        <div class="content-text">Для оформления заказа <br/> введите номер машины</div>

        <form action="{{ route('orders.process_license_plate') }}" method="POST">
            @csrf
            <div class="field mb-3">
                <input type="number" name="license_plate" class="form-control @error('license_plate') is-invalid @enderror" id="license_plate" placeholder="Введите номер машины" value="{{ old('license_plate') }}" autofocus autocomplete="off">
                @error('license_plate')
                <span class="invalid-feedback fw-bold text-danger" role="alert">
                {{ $message }}
            </span>
                @enderror
            </div>

            <button class="btn btn-success" type="submit">Далее</button>
        </form>
    </div>
@endsection

@push('scripts')
    @vite(['resources/css/client.css'])
@endpush