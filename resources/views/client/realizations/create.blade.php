@extends('layouts.client')

@section('content')
    <h1>Форма оформления реализаций на {{ date('d.m.Y', strtotime($realization->date)) }} от буса № {{ $licensePlate }}</h1>
    <form action="{{ route('realizations.store') }}" method="POST">
        @csrf

        <input type="hidden" name="realization_id" value="{{ $realization->id }}">

        <div id="shops_container">
            @if(old('shop'))
                @foreach(old('shop') as $index => $oldShop)
                    @include('client.realizations.shop_item', [
                        'id' => old('realization_shop_id')[$index] ?? 0,
                        'shop' => $oldShop,
                        'amount' => old('amount')[$index] ?? '',
                        'index' => $index
                    ])
                @endforeach
            @else
                @foreach($realizationShops as $index => $shop)
                    @include('client.realizations.shop_item', [
                        'id' => $shop->id,
                        'shop' => $shop->shop,
                        'amount' => $shop->amount,
                        'index' => $index
                    ])
                @endforeach
            @endif
        </div>

        <button type="button" class="btn btn-primary w-100 mb-3" id="add_shop">Добавить магазин</button>

        <button type="submit" class="btn btn-success w-100 mb-3">Сохранить</button>
    </form>

    @push('scripts')
        @vite(['resources/js/client/realizations/create.js'])
    @endpush
@endsection