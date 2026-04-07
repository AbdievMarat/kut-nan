<div class="sc sc--return shop_item">
    <input type="hidden" name="invoice_return_shop_id[]" value="{{ $id ?? 0 }}">

    {{-- Шапка карточки --}}
    <div class="sc__head sc__head--return">
        <span class="sc__num sc__num--return">{{ $num ?? '#' }}</span>
        <button type="button" class="sc__del remove_shop" aria-label="Удалить">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    {{-- Поле: магазин --}}
    <div class="sc__field @error("shop.{$index}") sc__field--error @enderror">
        <span class="sc__chip sc__chip--amber">
            <i class="bi bi-shop-window"></i>
        </span>
        <input
            type="text"
            class="sc__input"
            name="shop[]"
            value="{{ $shop ?? '' }}"
            placeholder="Название магазина"
            autocomplete="off"
            autocapitalize="words"
        >
    </div>

    @error("shop.{$index}")
        <p class="sc__err"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</p>
    @enderror

    <div class="sc__divider"></div>

    {{-- Поле: сумма --}}
    <div class="sc__field @error("amount.{$index}") sc__field--error @enderror">
        <span class="sc__chip sc__chip--orange">
            <i class="bi bi-cash-coin"></i>
        </span>
        <input
            class="sc__input sc__input--amount"
            type="number"
            inputmode="numeric"
            name="amount[]"
            value="{{ $amount ?? '' }}"
            placeholder="0"
            autocomplete="off"
            min="0"
        >
        <span class="sc__currency">сум</span>
    </div>

    @error("amount.{$index}")
        <p class="sc__err"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</p>
    @enderror
</div>

<style>
    .sc {
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 10px;
        box-shadow: 0 4px 20px rgba(26,86,219,.08), 0 1px 4px rgba(0,0,0,.05);
        animation: scIn .22s cubic-bezier(.34,1.56,.64,1);
    }
    @keyframes scIn {
        from { opacity: 0; transform: scale(.97) translateY(6px); }
        to   { opacity: 1; transform: scale(1) translateY(0); }
    }

    .sc__head {
        display: flex; align-items: center; justify-content: space-between;
        padding: 7px 12px;
        background: linear-gradient(135deg, #eff6ff 0%, #f8faff 100%);
        border-bottom: 1px solid #e8f0fe;
    }
    .sc__head--return {
        background: linear-gradient(135deg, #fffbeb 0%, #fffdf5 100%);
        border-bottom-color: #fde68a;
    }
    .sc__num {
        font-size: .72rem; font-weight: 700; color: #1a56db;
        background: #dbeafe; padding: 2px 9px; border-radius: 20px;
        letter-spacing: .03em;
    }
    .sc__num--return { color: #b45309; background: #fef3c7; }

    .sc__del {
        background: none; border: none; padding: 4px 6px;
        color: #9ca3af; font-size: .85rem; border-radius: 6px;
        cursor: pointer; transition: color .15s, background .15s; line-height: 1;
    }
    .sc__del:active { color: #ef4444; background: #fee2e2; }

    .sc__field {
        display: flex; align-items: center; gap: 10px; padding: 10px 14px;
        transition: background .15s;
    }
    .sc__field:focus-within { background: #fffdf7; }
    .sc__field--error { background: #fff7f7; }

    .sc__chip {
        width: 34px; height: 34px; border-radius: 10px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: 15px; color: #fff;
    }
    .sc__chip--blue   { background: linear-gradient(135deg, #1a56db, #3b82f6); }
    .sc__chip--green  { background: linear-gradient(135deg, #059669, #10b981); }
    .sc__chip--amber  { background: linear-gradient(135deg, #b45309, #d97706); }
    .sc__chip--orange { background: linear-gradient(135deg, #c2410c, #f97316); }

    .sc__input {
        flex: 1; border: none; background: transparent;
        font-size: 1rem; color: #111827; outline: none;
        -webkit-appearance: none; padding: 0;
    }
    .sc__input::placeholder { color: #d1d5db; }
    .sc__input--amount { font-weight: 700; font-size: 1.05rem; }
    .sc__input--amount::-webkit-outer-spin-button,
    .sc__input--amount::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    .sc__input--amount { -moz-appearance: textfield; }

    .sc__currency {
        font-size: .8rem; font-weight: 600; color: #9ca3af;
        background: #f3f4f6; padding: 3px 8px; border-radius: 6px; flex-shrink: 0;
    }

    .sc__divider { height: 1px; background: #f3f4f6; margin: 0 14px; }

    .sc__err {
        font-size: .78rem; color: #ef4444;
        margin: 0 14px 8px; padding: 0;
    }
</style>
