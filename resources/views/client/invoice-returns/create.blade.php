@extends('layouts.client')

@section('content')

    {{-- Hero Header --}}
    <div class="inv-hero inv-hero--return">
        <div class="inv-hero__inner">
            <div class="inv-hero__icon">
                <i class="bi bi-arrow-return-left"></i>
            </div>
            <div>
                <div class="inv-hero__title">Возврат по накладным</div>
                <div class="inv-hero__meta">
                    <span class="inv-hero__badge">
                        <i class="bi bi-truck-front-fill"></i>{{ $licensePlate }}
                    </span>
                    <span class="inv-hero__sep">·</span>
                    <span class="inv-hero__badge">
                        <i class="bi bi-calendar3"></i>{{ date('d.m.Y', strtotime($invoiceReturn->date)) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="inv-page">
        <form action="{{ route('invoice-returns.store') }}" method="POST" id="invoice-return-form">
            @csrf
            <input type="hidden" name="invoice_return_id" value="{{ $invoiceReturn->id }}">

            <div id="shops_container">
                @if(old('shop'))
                    @foreach(old('shop') as $index => $oldShop)
                        @include('client.invoice-returns.shop_item', [
                            'id'     => old('invoice_return_shop_id')[$index] ?? 0,
                            'shop'   => $oldShop,
                            'amount' => old('amount')[$index] ?? '',
                            'index'  => $index,
                            'num'    => $index + 1,
                        ])
                    @endforeach
                @else
                    @foreach($invoiceReturnShops as $index => $shop)
                        @include('client.invoice-returns.shop_item', [
                            'id'     => $shop->id,
                            'shop'   => $shop->shop,
                            'amount' => $shop->amount,
                            'index'  => $index,
                            'num'    => $index + 1,
                        ])
                    @endforeach
                @endif
            </div>

            <button type="button" class="inv-add-btn inv-add-btn--return" id="add_shop">
                <span class="inv-add-btn__icon inv-add-btn__icon--return"><i class="bi bi-plus-lg"></i></span>
                <span>Добавить магазин</span>
            </button>

        </form>
    </div>

    {{-- Sticky bottom bar --}}
    <div class="inv-bar">
        <div class="inv-bar__total inv-bar__total--return">
            <div class="inv-bar__total-left">
                <div class="inv-bar__total-icon inv-bar__total-icon--return"><i class="bi bi-receipt"></i></div>
                <span class="inv-bar__label">Итого возврат</span>
            </div>
            <div class="inv-bar__total-right">
                <span class="inv-bar__amount inv-bar__amount--return" id="total-amount">0</span>
            </div>
        </div>
        <button type="submit" form="invoice-return-form" class="inv-bar__save inv-bar__save--return">
            <i class="bi bi-check2-circle"></i>Сохранить
        </button>
    </div>

    @push('scripts')
        @vite(['resources/css/client-invoices.css', 'resources/js/client/invoice-returns/create.js'])
    @endpush
@endsection
