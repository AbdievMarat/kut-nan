@extends('layouts.client')

@section('content')

    <div class="inv-hero inv-hero--green">
        <div class="inv-hero__inner">
            <div class="inv-hero__icon">
                <i class="bi bi-cart-check-fill"></i>
            </div>
            <div>
                <div class="inv-hero__title">Заказ</div>
                <div class="inv-hero__meta">
                    <span class="inv-hero__badge">
                        <i class="bi bi-truck-front-fill"></i>{{ $licensePlate }}
                    </span>
                    <span class="inv-hero__sep">·</span>
                    <span class="inv-hero__badge">
                        <i class="bi bi-calendar3"></i>{{ date('d.m.Y', strtotime($order->date)) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="inv-page">
        <form action="{{ route('orders.store') }}" method="POST" id="order-form">
            @csrf
            <input type="hidden" name="id" value="{{ $order->id }}">

            <div class="pf-card pf-card--green">
                @foreach ($products as $product)
                    <div class="pf-row">
                        <label class="pf-label" for="item_{{ $product->id }}">{{ $product->name }}</label>
                        <div class="pf-input-wrap">
                            <input
                                type="number"
                                class="pf-input @error('item_amounts.'.$product->id) is-invalid @enderror"
                                id="item_{{ $product->id }}"
                                name="item_amounts[{{ $product->id }}]"
                                placeholder="0"
                                value="{{ $itemAmounts->has($product->id) ? $itemAmounts[$product->id]->amount : old('item_amounts.'.$product->id) }}"
                                autocomplete="off"
                            >
                        </div>
                        <input type="hidden" name="item_price[{{ $product->id }}]" value="{{ $product->price }}">
                    </div>
                    @error('item_amounts.'.$product->id)
                        <p class="pf-err" style="display:block">{{ $message }}</p>
                    @enderror
                @endforeach
            </div>
        </form>
    </div>

    @if(now()->hour < 13)
        <div class="inv-bar">
            <button type="submit" form="order-form" class="inv-bar__save">
                <i class="bi bi-check2-circle"></i>Сохранить
            </button>
        </div>
    @endif

    @push('scripts')
        @vite(['resources/css/client-invoices.css'])
    @endpush

@endsection
