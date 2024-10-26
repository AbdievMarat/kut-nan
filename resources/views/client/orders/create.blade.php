@extends('layouts.client')

@section('content')
    <h1>Форма оформления заказа на {{ date('d.m.Y', strtotime($order->date)) }} от буса № {{ $licensePlate }}</h1>
    <form action="{{ route('orders.store') }}" method="POST">
        @csrf

        <input type="hidden" name="id" value="{{ $order->id }}">

        @foreach ($products as $product)
            <x-client-forms-input
                    type="number"
                    name="item_amounts[{{ $product->id }}]"
                    id="item_{{ $product->id }}"
                    label="{{ $product->name }}"
                    placeholder=""
                    value="{{ $itemAmounts->has($product->id) ? $itemAmounts[$product->id]->amount : old('item_amounts.'.$product->id) }}"
            >
            </x-client-forms-input>

            <input type="hidden" name="item_price[{{ $product->id }}]" value="{{ $product->price }}">
        @endforeach

        <button type="submit" class="btn btn-success w-100 mb-3">Сохранить</button>
    </form>
@endsection