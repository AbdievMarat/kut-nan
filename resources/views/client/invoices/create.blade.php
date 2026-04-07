@extends('layouts.client')

@section('content')

    {{-- Hero Header --}}
    <div class="inv-hero">
        <div class="inv-hero__inner">
            <div class="inv-hero__icon">
                <i class="bi bi-file-earmark-text"></i>
            </div>
            <div>
                <div class="inv-hero__title">Накладные</div>
                <div class="inv-hero__meta">
                    <span class="inv-hero__badge">
                        <i class="bi bi-truck-front-fill"></i>{{ $licensePlate }}
                    </span>
                    <span class="inv-hero__sep">·</span>
                    <span class="inv-hero__badge">
                        <i class="bi bi-calendar3"></i>{{ date('d.m.Y', strtotime($invoice->date)) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="inv-page">
        <form action="{{ route('invoices.store') }}" method="POST" id="invoice-form">
            @csrf
            <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">

            <div id="shops_container">
                @if(old('shop'))
                    @foreach(old('shop') as $index => $oldShop)
                        @include('client.invoices.shop_item', [
                            'id'     => old('invoice_shop_id')[$index] ?? 0,
                            'shop'   => $oldShop,
                            'amount' => old('amount')[$index] ?? '',
                            'index'  => $index,
                            'num'    => $index + 1,
                        ])
                    @endforeach
                @else
                    @foreach($invoiceShops as $index => $shop)
                        @include('client.invoices.shop_item', [
                            'id'     => $shop->id,
                            'shop'   => $shop->shop,
                            'amount' => $shop->amount,
                            'index'  => $index,
                            'num'    => $index + 1,
                        ])
                    @endforeach
                @endif
            </div>

            <button type="button" class="inv-add-btn" id="add_shop">
                <span class="inv-add-btn__icon"><i class="bi bi-plus-lg"></i></span>
                <span>Добавить магазин</span>
            </button>

        </form>
    </div>

    {{-- Sticky bottom bar --}}
    <div class="inv-bar">
        <div class="inv-bar__total">
            <div class="inv-bar__total-left">
                <div class="inv-bar__total-icon"><i class="bi bi-receipt"></i></div>
                <span class="inv-bar__label">Итого</span>
            </div>
            <div class="inv-bar__total-right">
                <span class="inv-bar__amount" id="total-amount">0</span>
            </div>
        </div>
        <button type="submit" form="invoice-form" class="inv-bar__save">
            <i class="bi bi-check2-circle"></i>Сохранить
        </button>
    </div>

    @push('scripts')
        @vite(['resources/css/client-invoices.css', 'resources/js/client/invoices/create.js'])
    @endpush
@endsection
