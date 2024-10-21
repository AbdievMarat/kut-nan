@extends('layouts.client')

@section('content')
    <h1>Форма оформления заказа на {{ date('d.m.Y', strtotime($order->date)) }} от буса № {{ $licensePlate }}</h1>
    <form action="{{ route('orders.store') }}" method="POST">
        @csrf

        <input type="hidden" name="id" value="{{ $order->id }}">

        @foreach ($order->items as $item)
            <input type="hidden" name="item_ids[{{ $item->id }}]" value="{{ $item->id }}">

            <x-client-forms-input
                    type="number"
                    name="item_amounts[{{ $item->id }}]"
                    id="item_{{ $item->id }}"
                    label="{{ $item->product->name }}"
                    placeholder=""
                    value="{{ old('item_amounts.'.$item->id) ?? $item->amount }}"
            >
            </x-client-forms-input>
        @endforeach

        <button type="submit" class="btn btn-success w-100 mb-3">Сохранить</button>
    </form>
@endsection