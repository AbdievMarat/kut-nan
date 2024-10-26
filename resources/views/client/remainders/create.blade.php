@extends('layouts.client')

@section('content')
    <h1>Форма для ввода остатков на {{ date('d.m.Y', strtotime($remainder->date)) }} от буса № {{ $licensePlate }}</h1>
    <form action="{{ route('remainders.store') }}" method="POST">
        @csrf

        <input type="hidden" name="id" value="{{ $remainder->id }}">

        @foreach ($products as $product)
            <x-client-forms-input
                    type="number"
                    name="item_amounts[{{ $product->id }}]"
                    id="item_{{ $product->id }}"
                    label="{{ $product->name }}"
                    placeholder=""
                    value="{{ $itemAmounts->has($product->id) ? $itemAmounts[$product->id]->amount : old('item_amounts.'.$product->id) }}"
                    data-product-price="{{ $product->price }}"
            >
            </x-client-forms-input>

            <input type="hidden" name="item_price[{{ $product->id }}]" value="{{ $product->price }}">
        @endforeach

        <div id="price-label" class="alert alert-success" role="alert">
            <i class="bi bi-calculator-fill me-2"></i>
            <strong>Итого: <span id="total-price">0</span></strong>
        </div>

        <button type="submit" class="btn btn-success w-100 mb-3">Сохранить</button>
    </form>

    @push('scripts')
        @vite(['resources/js/client/remainders/create.js'])
    @endpush
@endsection