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
                    <i class="bi bi-truck-front-fill me-1"></i>{{ $licensePlate }}
                    <span class="inv-hero__sep">·</span>
                    <i class="bi bi-calendar3 me-1"></i>{{ date('d.m.Y', strtotime($invoice->date)) }}
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
            <span class="inv-bar__label">Итого</span>
            <span class="inv-bar__amount" id="total-amount">0</span>
            <span class="inv-bar__currency">сум</span>
        </div>
        <button type="submit" form="invoice-form" class="inv-bar__save">
            <i class="bi bi-check2-circle me-2"></i>Сохранить
        </button>
    </div>

    @push('scripts')
        @vite(['resources/js/client/invoices/create.js'])
        <style>
            /* ── Reset ── */
            body { background: #f0f4f8; padding-bottom: 130px; }
            .container { padding-left: 0; padding-right: 0; }

            /* ── Hero ── */
            .inv-hero {
                background: linear-gradient(135deg, #1a56db 0%, #3b82f6 60%, #60a5fa 100%);
                padding: 20px 20px 28px;
                color: #fff;
                margin-bottom: -12px;
            }
            .inv-hero__inner { display: flex; align-items: center; gap: 14px; }
            .inv-hero__icon {
                width: 52px; height: 52px; border-radius: 14px;
                background: rgba(255,255,255,.2);
                display: flex; align-items: center; justify-content: center;
                font-size: 24px; flex-shrink: 0;
                backdrop-filter: blur(4px);
            }
            .inv-hero__title { font-size: 1.3rem; font-weight: 700; line-height: 1.2; }
            .inv-hero__meta  { font-size: .88rem; opacity: .85; margin-top: 3px; }
            .inv-hero__sep   { margin: 0 6px; opacity: .6; }

            /* ── Page body ── */
            .inv-page {
                background: #f0f4f8;
                padding: 20px 14px 8px;
                max-width: 600px; margin: 0 auto;
            }

            /* ── Add button ── */
            .inv-add-btn {
                width: 100%; border: 2px dashed #93c5fd; border-radius: 14px;
                background: #eff6ff; color: #1a56db;
                padding: 14px; margin-bottom: 8px;
                font-size: 1rem; font-weight: 600;
                display: flex; align-items: center; justify-content: center; gap: 10px;
                cursor: pointer; transition: background .15s, border-color .15s;
            }
            .inv-add-btn:active { background: #dbeafe; }
            .inv-add-btn__icon {
                width: 28px; height: 28px; border-radius: 50%;
                background: #1a56db; color: #fff;
                display: flex; align-items: center; justify-content: center;
                font-size: 14px; flex-shrink: 0;
            }

            /* ── Bottom bar ── */
            .inv-bar {
                position: fixed; bottom: 0; left: 0; right: 0; z-index: 1000;
                background: rgba(255,255,255,.92);
                backdrop-filter: blur(16px);
                -webkit-backdrop-filter: blur(16px);
                border-top: 1px solid rgba(0,0,0,.08);
                box-shadow: 0 -8px 24px rgba(0,0,0,.09);
                padding: 12px 16px 16px;
            }
            .inv-bar__total {
                display: flex; align-items: baseline; gap: 6px;
                margin-bottom: 10px;
            }
            .inv-bar__label   { font-size: .8rem; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; }
            .inv-bar__amount  { font-size: 1.8rem; font-weight: 800; color: #1a56db; line-height: 1; }
            .inv-bar__currency{ font-size: .85rem; color: #6b7280; align-self: flex-end; padding-bottom: 2px; }
            .inv-bar__save {
                width: 100%; border: none; border-radius: 14px;
                background: linear-gradient(135deg, #059669, #10b981);
                color: #fff; font-size: 1.05rem; font-weight: 700;
                padding: 15px; cursor: pointer;
                box-shadow: 0 4px 14px rgba(16,185,129,.35);
                transition: transform .1s, box-shadow .1s;
            }
            .inv-bar__save:active {
                transform: scale(.98);
                box-shadow: 0 2px 8px rgba(16,185,129,.25);
            }
        </style>
    @endpush
@endsection
