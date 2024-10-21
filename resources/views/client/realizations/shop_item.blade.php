<div class="toast show mb-3 shop_item">
    <div class="toast-header @error("shop.{$index}") is-invalid @enderror">
        <input type="hidden" name="realization_shop_id[]" value="{{ $id ?? 0 }}">

        <input
            type="text"
            class="form-control mb-2 @error("shop.{$index}") is-invalid @enderror"
            name="shop[]"
            value="{{ $shop ?? '' }}"
            placeholder="Название магазина"
            autocomplete="off"
        >
        <button type="button" class="btn-close me-0 mb-1 remove_shop" aria-label="Удалить"></button>
    </div>

    @error(("shop.{$index}"))
    <span class="invalid-feedback ms-3" role="alert"><strong>{{ $message }}</strong></span>
    @enderror

    <div class="toast-body pt-0 pb-0">
        <div class="input-group mb-3">
            <span class="input-group-text" id="amount">Сумма</span>
            <input
                    class="form-control @error("amount.{$index}") is-invalid @enderror"
                    type="number"
                    name="amount[]"
                    aria-describedby="amount"
                    value="{{ $amount ?? '' }}"
                    placeholder="Сумма реализации"
                    autocomplete="off"
            >

            @error("amount.{$index}")
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>
    </div>
</div>