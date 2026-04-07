@php use App\Models\Order; @endphp
@extends('layouts.client')

@section('content')
    <div class="lp-wrapper">
        <div class="lp-logo-wrap">
            <img class="logo" src="{{ asset('logo.png') }}" alt="kut-nan"/>
        </div>

        <div class="lp-card">
            <p class="lp-title">
                <i class="bi bi-truck-front-fill lp-title-icon"></i>
                Введите номер машины
            </p>

            <form action="{{ route('orders.process_license_plate') }}" id="order_form" method="POST">
                @csrf

                <div class="lp-input-wrap mb-4">
                    <input
                        type="number"
                        name="license_plate"
                        class="lp-input @error('license_plate') is-invalid @enderror"
                        id="license_plate"
                        placeholder="Номер машины"
                        value="{{ old('li1cense_plate') }}"
                        autofocus
                        autocomplete="off"
                    >
                    @error('license_plate')
                        <span class="invalid-feedback fw-bold" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <input type="hidden" name="type_operation" id="type_operation" value="">

                <div class="lp-btn-grid">
                    <button class="lp-btn lp-btn--green" type="button" onclick="submitForm({{ Order::TYPE_OPERATION_ORDER }})">
                        <span class="lp-btn-icon"><i class="bi bi-cart-check-fill"></i></span>
                        <span class="lp-btn-label">Оформить заказ</span>
                    </button>

                    <button class="lp-btn lp-btn--teal" type="button" onclick="submitForm({{ Order::TYPE_OPERATION_REMAINDER }})">
                        <span class="lp-btn-icon"><i class="bi bi-box-seam-fill"></i></span>
                        <span class="lp-btn-label">Остатки</span>
                    </button>

                    <button class="lp-btn lp-btn--plum" type="button" onclick="submitForm({{ Order::TYPE_OPERATION_MARKDOWN }})">
                        <span class="lp-btn-icon"><i class="bi bi-tag-fill"></i></span>
                        <span class="lp-btn-label">Уценка</span>
                    </button>

                    <button class="lp-btn lp-btn--blue" type="button" onclick="submitForm({{ Order::TYPE_OPERATION_REALIZATION }})">
                        <span class="lp-btn-icon"><i class="bi bi-bag-check-fill"></i></span>
                        <span class="lp-btn-label">Реализации</span>
                    </button>

                    <button class="lp-btn lp-btn--amber" type="button" onclick="submitForm({{ Order::TYPE_OPERATION_INVOICE }})">
                        <span class="lp-btn-icon"><i class="bi bi-receipt-cutoff"></i></span>
                        <span class="lp-btn-label">Накладные</span>
                    </button>

                    <button class="lp-btn lp-btn--slate" type="button" onclick="submitForm({{ Order::TYPE_OPERATION_INVOICE_RETURN }})">
                        <span class="lp-btn-icon"><i class="bi bi-arrow-counterclockwise"></i></span>
                        <span class="lp-btn-label">Возврат накладных</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    @vite(['resources/css/client.css'])
@endpush

<style>
    /* ── Background ── */
    body {
        background: #f5f0ff;
        background-image:
            radial-gradient(ellipse 80% 60% at 20% 0%,   rgba(200, 160, 255, 0.18) 0%, transparent 70%),
            radial-gradient(ellipse 60% 50% at 80% 100%, rgba(165, 211, 50,  0.14) 0%, transparent 70%),
            radial-gradient(ellipse 50% 40% at 90% 10%,  rgba(255, 220, 130, 0.13) 0%, transparent 65%);
        min-height: 100vh;
    }

    /* ── Layout ── */
    .lp-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
        padding-bottom: 2.5rem;
    }

    .lp-logo-wrap {
        padding-top: 1.75rem;
        padding-bottom: 0.75rem;
        animation: lp-fadein 0.5s ease both;
    }

    /* ── Card ── */
    .lp-card {
        background: rgba(255, 255, 255, 0.75);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1.5px solid rgba(255, 255, 255, 0.95);
        border-radius: 28px;
        box-shadow:
            0 1px 0 rgba(255,255,255,1) inset,
            0 16px 48px rgba(130, 0, 166, 0.07),
            0  4px 16px rgba(76,  45, 16,  0.05);
        padding: 2rem 1.75rem 2.25rem;
        width: 100%;
        max-width: 420px;
        animation: lp-slidein 0.5s cubic-bezier(0.22, 1, 0.36, 1) both;
    }

    /* ── Title ── */
    .lp-title {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 1.1rem;
        font-weight: 700;
        color: #4c2d10;
        letter-spacing: -0.01em;
        margin-bottom: 1.4rem;
    }

    .lp-title-icon {
        font-size: 1.35rem;
        color: #a855f7;
    }

    /* ── Input ── */
    .lp-input-wrap { position: relative; }

    .lp-input {
        width: 100%;
        border: 2px solid #e9d8fd;
        border-radius: 16px;
        padding: 16px 20px;
        font-size: 1.35rem;
        font-weight: 700;
        color: #3b1d08;
        background: #fff;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
        box-sizing: border-box;
        -moz-appearance: textfield;
        text-align: center;
        letter-spacing: 0.08em;
        box-shadow: 0 2px 8px rgba(168, 85, 247, 0.07),
                    0 1px 2px rgba(0,0,0,0.04);
    }

    .lp-input::placeholder {
        color: #c4b5d6;
        font-weight: 400;
        letter-spacing: 0;
        font-size: 1rem;
    }

    .lp-input::-webkit-outer-spin-button,
    .lp-input::-webkit-inner-spin-button { -webkit-appearance: none; }

    .lp-input:focus {
        border-color: #a855f7;
        box-shadow: 0 0 0 4px rgba(168, 85, 247, 0.12),
                    0 4px 16px rgba(168, 85, 247, 0.1);
    }

    .lp-input.is-invalid {
        border-color: #f87171;
        box-shadow: 0 0 0 4px rgba(248, 113, 113, 0.12);
    }

    /* ── Button grid ── */
    .lp-btn-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.65rem;
    }

    .lp-btn {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border: 1.5px solid rgba(255,255,255,0.6);
        border-radius: 18px;
        padding: 1.1rem 0.5rem 0.95rem;
        cursor: pointer;
        overflow: hidden;
        transition: transform 0.18s cubic-bezier(0.34, 1.56, 0.64, 1),
                    box-shadow 0.2s ease;
    }

    /* top shine */
    .lp-btn::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 50%;
        background: linear-gradient(180deg, rgba(255,255,255,0.35) 0%, transparent 100%);
        pointer-events: none;
        border-radius: inherit;
    }

    .lp-btn:hover {
        transform: translateY(-3px) scale(1.03);
    }

    .lp-btn:active {
        transform: scale(0.95);
        transition-duration: 0.08s;
    }

    .lp-btn-icon {
        font-size: 1.8rem;
        line-height: 1;
    }

    .lp-btn-label {
        font-size: 0.8rem;
        font-weight: 700;
        letter-spacing: 0.01em;
        line-height: 1.25;
    }

    /* ── Button colors (pastel-light) ── */
    .lp-btn--green {
        background: linear-gradient(145deg, #bbf7d0, #6ee7a0);
        box-shadow: 0 4px 16px rgba(74, 222, 128, 0.3);
        color: #14532d;
    }
    .lp-btn--green .lp-btn-icon  { color: #16a34a; }
    .lp-btn--green .lp-btn-label { color: #166534; }

    .lp-btn--blue {
        background: linear-gradient(145deg, #bfdbfe, #93c5fd);
        box-shadow: 0 4px 16px rgba(96, 165, 250, 0.3);
    }
    .lp-btn--blue .lp-btn-icon  { color: #1d4ed8; }
    .lp-btn--blue .lp-btn-label { color: #1e40af; }

    .lp-btn--teal {
        background: linear-gradient(145deg, #a5f3fc, #67e8f9);
        box-shadow: 0 4px 16px rgba(34, 211, 238, 0.3);
    }
    .lp-btn--teal .lp-btn-icon  { color: #0e7490; }
    .lp-btn--teal .lp-btn-label { color: #155e75; }

    .lp-btn--plum {
        background: linear-gradient(145deg, #f3e8ff, #e9d5ff);
        box-shadow: 0 4px 16px rgba(192, 132, 252, 0.3);
    }
    .lp-btn--plum .lp-btn-icon  { color: #9333ea; }
    .lp-btn--plum .lp-btn-label { color: #7e22ce; }

    .lp-btn--amber {
        background: linear-gradient(145deg, #fef9c3, #fde68a);
        box-shadow: 0 4px 16px rgba(251, 191, 36, 0.3);
    }
    .lp-btn--amber .lp-btn-icon  { color: #d97706; }
    .lp-btn--amber .lp-btn-label { color: #92400e; }

    .lp-btn--slate {
        background: linear-gradient(145deg, #e2e8f0, #cbd5e1);
        box-shadow: 0 4px 16px rgba(148, 163, 184, 0.3);
    }
    .lp-btn--slate .lp-btn-icon  { color: #475569; }
    .lp-btn--slate .lp-btn-label { color: #334155; }

    /* ── Animations ── */
    @keyframes lp-fadein {
        from { opacity: 0; }
        to   { opacity: 1; }
    }

    @keyframes lp-slidein {
        from { opacity: 0; transform: translateY(24px) scale(0.97); }
        to   { opacity: 1; transform: translateY(0)    scale(1); }
    }

    /* ── Responsive ── */
    @media (max-width: 400px) {
        .lp-card    { padding: 1.5rem 1rem; }
        .lp-btn     { padding: 0.9rem 0.4rem 0.75rem; }
        .lp-btn-icon  { font-size: 1.5rem; }
        .lp-btn-label { font-size: 0.74rem; }
    }
</style>

<script>
    function submitForm(typeOperation) {
        document.getElementById('type_operation').value = typeOperation;
        document.getElementById('order_form').submit();
    }
</script>
