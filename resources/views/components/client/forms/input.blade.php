<div class="input-group mb-3">
    <span class="input-group-text" id="{{ $id }}">{{ $label }}</span>
    <input
        {{ $attributes->merge(['class' => 'form-control' .($errors->has(str_replace(['[', ']'], ['.', ''], $name)) ? ' is-invalid' : '')]) }}
        type="{{ $type }}"
        name="{{ $name }}"
        aria-describedby="{{ $id }}"
        value="{{ $value }}"
        autocomplete="off"
    >

    @error(str_replace(['[', ']'], ['.', ''], $name))
    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
    @enderror
</div>