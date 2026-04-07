<div class="sc sc--return shop_item">
    <input type="hidden" name="invoice_return_shop_id[]" value="{{ $id ?? 0 }}">

    {{-- Шапка карточки --}}
    <div class="sc__head sc__head--return">
        <div class="sc__head-left">
            <span class="sc__num-dot sc__num-dot--return"></span>
            <span class="sc__num sc__num--return">Магазин {{ $num ?? '#' }}</span>
        </div>
        <button type="button" class="sc__del remove_shop" aria-label="Удалить">
            <i class="bi bi-trash3"></i>
        </button>
    </div>

    {{-- Поле: магазин --}}
    <div class="sc__field @error("shop.{$index}") sc__field--error @enderror">
        <span class="sc__chip sc__chip--amber">
            <i class="bi bi-shop-window"></i>
        </span>
        <div class="sc__field-inner">
            <label class="sc__field-label">Название магазина</label>
            <input
                type="text"
                class="sc__input"
                name="shop[]"
                value="{{ $shop ?? '' }}"
                placeholder="Введите название..."
                autocomplete="off"
                autocapitalize="words"
            >
        </div>
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
        <div class="sc__field-inner">
            <label class="sc__field-label">Сумма возврата</label>
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
        </div>
    </div>

    @error("amount.{$index}")
        <p class="sc__err"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</p>
    @enderror
</div>
