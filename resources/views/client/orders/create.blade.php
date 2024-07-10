@extends('layouts.client')

@section('content')
    <h1>Форма оформления заказа на {{ date('d.m.Y', strtotime($order->date)) }} от буса № {{ $licensePlate }}</h1>
    <form action="{{ route('orders.store') }}" method="POST">
        @csrf

        <input type="hidden" name="license_plate" value="{{ $licensePlate }}">
        <input type="hidden" name="id" value="{{ $order->id }}">

        <x-client-forms-input type="number" name="product_1" id="product_1" label="Москва"
                       placeholder="" value="{{ old('product_1') ?? $order->product_1 }}">
        </x-client-forms-input>

        <x-client-forms-input type="number" name="product_2" id="product_2" label="Москва уп"
                       placeholder="" value="{{ old('product_2') ?? $order->product_2 }}">
        </x-client-forms-input>

        <x-client-forms-input type="number" name="product_3" id="product_3" label="Солдат"
                       placeholder="" value="{{ old('product_3') ?? $order->product_3 }}">
        </x-client-forms-input>

        <x-client-forms-input type="number" name="product_4" id="product_4" label="Отруб"
                       placeholder="" value="{{ old('product_4') ?? $order->product_4 }}">
        </x-client-forms-input>

        <x-client-forms-input type="number" name="product_5" id="product_5" label="Налив"
                       placeholder="" value="{{ old('product_5') ?? $order->product_5 }}">
        </x-client-forms-input>

        <x-client-forms-input type="number" name="product_6" id="product_6" label="Тостер"
                       placeholder="" value="{{ old('product_6') ?? $order->product_6 }}">
        </x-client-forms-input>

        <x-client-forms-input type="number" name="product_7" id="product_7" label="Тостер кара"
                       placeholder="" value="{{ old('product_7') ?? $order->product_7 }}">
        </x-client-forms-input>

        <x-client-forms-input type="number" name="product_8" id="product_8" label="Мини тостер"
                       placeholder="" value="{{ old('product_8') ?? $order->product_8 }}">
        </x-client-forms-input>

        <x-client-forms-input type="number" name="product_9" id="product_9" label="Гречневый"
                       placeholder="" value="{{ old('product_9') ?? $order->product_9 }}">
        </x-client-forms-input>

        <x-client-forms-input type="number" name="product_10" id="product_10" label="Зерновой"
                       placeholder="" value="{{ old('product_10') ?? $order->product_10 }}">
        </x-client-forms-input>

        <x-client-forms-input type="number" name="product_11" id="product_11" label="Багет"
                       placeholder="" value="{{ old('product_11') ?? $order->product_11 }}">
        </x-client-forms-input>

        <x-client-forms-input type="number" name="product_12" id="product_12" label="Без дрожж"
                       placeholder="" value="{{ old('product_12') ?? $order->product_12 }}">
        </x-client-forms-input>

        <x-client-forms-input type="number" name="product_13" id="product_13" label="Чемпион"
                       placeholder="" value="{{ old('product_13') ?? $order->product_13 }}">
        </x-client-forms-input>

        <x-client-forms-input type="number" name="product_14" id="product_14" label="Абсолют"
                       placeholder="" value="{{ old('product_14') ?? $order->product_14 }}">
        </x-client-forms-input>

        <x-client-forms-input type="number" name="product_15" id="product_15" label="Кукурузный"
                       placeholder="" value="{{ old('product_15') ?? $order->product_15 }}">
        </x-client-forms-input>

        <x-client-forms-input type="number" name="product_16" id="product_16" label="Уп. Бород"
                       placeholder="" value="{{ old('product_16') ?? $order->product_16 }}">
        </x-client-forms-input>

        <x-client-forms-input type="number" name="product_17" id="product_17" label="Уп. Батон отруб"
                       placeholder="" value="{{ old('product_17') ?? $order->product_17 }}">
        </x-client-forms-input>

        <x-client-forms-input type="number" name="product_18" id="product_18" label="Уп. Батон серый"
                       placeholder="" value="{{ old('product_18') ?? $order->product_18 }}">
        </x-client-forms-input>

        <x-client-forms-input type="number" name="product_19" id="product_19" label="Уп. Батон белый"
                       placeholder="" value="{{ old('product_19') ?? $order->product_19 }}">
        </x-client-forms-input>

        <x-client-forms-input type="number" name="product_20" id="product_20" label="Баатыр"
                       placeholder="" value="{{ old('product_20') ?? $order->product_20 }}">
        </x-client-forms-input>

        <x-client-forms-input type="number" name="product_21" id="product_21" label="Обама отруб"
                       placeholder="" value="{{ old('product_21') ?? $order->product_21 }}">
        </x-client-forms-input>

        <x-client-forms-input type="number" name="product_22" id="product_22" label="Обама ржан"
                       placeholder="" value="{{ old('product_22') ?? $order->product_22 }}">
        </x-client-forms-input>

        <x-client-forms-input type="number" name="product_23" id="product_23" label="Обама серый"
                       placeholder="" value="{{ old('product_23') ?? $order->product_23 }}">
        </x-client-forms-input>

        <x-client-forms-input type="number" name="product_24" id="product_24" label="Уп. Моск"
                       placeholder="" value="{{ old('product_24') ?? $order->product_24 }}">
        </x-client-forms-input>

        <x-client-forms-input type="number" name="product_25" id="product_25" label="Гамбургер"
                       placeholder="" value="{{ old('product_25') ?? $order->product_25 }}">
        </x-client-forms-input>

        <x-client-forms-input type="number" name="product_26" id="product_26" label="Тартин"
                       placeholder="" value="{{ old('product_26') ?? $order->product_26 }}">
        </x-client-forms-input>

        <x-client-forms-input type="number" name="product_27" id="product_27" label="Тартин зерновой"
                       placeholder="" value="{{ old('product_27') ?? $order->product_27 }}">
        </x-client-forms-input>

        <x-client-forms-input type="number" name="product_28" id="product_28" label="Тартин ржаной"
                       placeholder="" value="{{ old('product_28') ?? $order->product_28 }}">
        </x-client-forms-input>

        <x-client-forms-input type="number" name="product_29" id="product_29" label="Тартин с луком"
                       placeholder="" value="{{ old('product_29') ?? $order->product_29 }}">
        </x-client-forms-input>

        <button type="submit" class="btn btn-success w-100 mb-3">Сохранить</button>
    </form>
@endsection