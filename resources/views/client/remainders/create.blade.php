@extends('layouts.client')

@section('content')
    <h1>Форма для ввода остатков на {{ date('d.m.Y', strtotime($remainder->date)) }} от буса № {{ $licensePlate }}</h1>
    <form action="{{ route('remainders.store') }}" method="POST">
        @csrf

        <input type="hidden" name="id" value="{{ $remainder->id }}">

        @foreach ($remainder->items as $item)
            <input type="hidden" name="item_ids[{{ $item->id }}]" value="{{ $item->id }}">

            <x-client-forms-input
                    type="number"
                    name="item_amounts[{{ $item->id }}]"
                    id="item_{{ $item->id }}"
                    label="{{ $item->product->name }}"
                    placeholder=""
                    value="{{ old('item_amounts.'.$item->id) ?? $item->amount }}"
                    data-product-price="{{ $item->product->price }}"
            >
            </x-client-forms-input>
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