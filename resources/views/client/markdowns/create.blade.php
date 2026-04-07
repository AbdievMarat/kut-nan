@extends('layouts.client')

@section('content')

    <div class="inv-hero inv-hero--plum">
        <div class="inv-hero__inner">
            <div class="inv-hero__icon">
                <i class="bi bi-tag-fill"></i>
            </div>
            <div>
                <div class="inv-hero__title">Уценка</div>
                <div class="inv-hero__meta">
                    <span class="inv-hero__badge">
                        <i class="bi bi-truck-front-fill"></i>{{ $licensePlate }}
                    </span>
                    <span class="inv-hero__sep">·</span>
                    <span class="inv-hero__badge">
                        <i class="bi bi-calendar3"></i>{{ date('d.m.Y', strtotime($markdown->date)) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="inv-page">
        <form action="{{ route('markdowns.store') }}" method="POST" id="markdown-form">
            @csrf
            <input type="hidden" name="id" value="{{ $markdown->id }}">

            <div class="pf-card pf-card--plum">
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
                    </div>
                    @error('item_amounts.'.$product->id)
                        <p class="pf-err" style="display:block">{{ $message }}</p>
                    @enderror
                @endforeach
            </div>
        </form>
    </div>

    <div class="inv-bar">
        <div class="inv-bar__total">
            <div class="inv-bar__total-left">
                <div class="inv-bar__total-icon inv-bar__total-icon--plum">
                    <i class="bi bi-calculator-fill"></i>
                </div>
                <span class="inv-bar__label">Итого</span>
            </div>
            <div class="inv-bar__total-right">
                <span class="inv-bar__amount inv-bar__amount--plum" id="total-amount">0</span>
            </div>
        </div>
        <button type="submit" form="markdown-form" class="inv-bar__save">
            <i class="bi bi-check2-circle"></i>Сохранить
        </button>
    </div>

    @push('scripts')
        @vite(['resources/css/client-invoices.css', 'resources/js/client/markdowns/create.js'])
    @endpush

@endsection
