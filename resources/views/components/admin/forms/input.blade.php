<div>
    @if($label)
        <label for="{{ $id }}" class="form-label fw-bold mb-0">{{ $label }}</label>
    @endif
    <input
            type="{{ $type }}"
            id="{{ $id }}"
            name="{{ $name }}"
            value="{{ $value }}"
            placeholder="{{ $placeholder }}"
            @if($form) form="{{ $form }}" @endif
            @if($required) required="{{ $required }}" @endif
            class="form-control {{ $class }} @error($name) is-invalid @enderror"
    >
    @error($name)
    <small class="text-danger">{{ $message }}</small>
    @enderror
</div>
