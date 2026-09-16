@extends('layouts.app')
@section('title', $siteContent->text('checkout','heading','page_title'))

@section('content')
<div class="checkout-hero"><div class="container"><span class="eyebrow">{{ $siteContent->text('checkout','heading','field_1') }}</span><h1>{{ $siteContent->text('checkout','heading','field_2') }}</h1><div class="checkout-steps"><span class="done"><i class="bi bi-check"></i> {{ $siteContent->text('checkout','heading','field_3') }}</span><b></b><span class="active"><i>২</i> {{ $siteContent->text('checkout','heading','field_4') }}</span><b></b><span><i>৩</i> {{ $siteContent->text('checkout','heading','field_5') }}</span></div></div></div>

@foreach ($cart as $row)
    <form id="checkout-quantity-{{ $row['id'] }}" method="post" action="{{ route('cart.update', $row['id']) }}">@csrf @method('PATCH')</form>
@endforeach
<form id="coupon-apply-form" method="post" action="{{ route('checkout.coupon.apply') }}">@csrf</form>
@if ($coupon)<form id="coupon-remove-form" method="post" action="{{ route('checkout.coupon.remove') }}">@csrf @method('DELETE')</form>@endif

<section class="section checkout-section">
    <div class="container">
        <form method="post" action="{{ route('checkout.store') }}" class="row g-4 g-xl-5" data-checkout-form data-incomplete-url="{{ route('checkout.incomplete.store') }}">
            @if(count($savedAddresses))
                <div class="col-12"><label for="savedAddress" class="form-label">{{ $siteContent->text('checkout','heading','field_6') }}</label><select id="savedAddress" class="form-select"><option value="">{{ $siteContent->text('checkout','heading','field_7') }}</option>@foreach($savedAddresses as $savedAddress)<option value="{{ $savedAddress['id'] }}" data-address="{{ json_encode($savedAddress) }}">{{ $savedAddress['label'] }} — {{ $savedAddress['address'] }}</option>@endforeach</select></div>
            @endif
            @csrf
            <div class="col-lg-7">
                @if ($errors->any() && ! $errors->has('coupon_code'))<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
                <section class="checkout-card">
                    <div class="checkout-card-title"><span>১</span><div><h2>{{ $siteContent->text('checkout','delivery','field_1') }}</h2><p>{{ $siteContent->text('checkout','delivery','field_2') }}</p></div></div>
                    <div class="row g-3">
                        <div class="col-md-6"><label for="customerName">{{ $siteContent->text('checkout','delivery','field_3') }}</label><input class="form-control @error('name') is-invalid @enderror" id="customerName" name="name" value="{{ old('name', auth()->user()?->name) }}" placeholder="{{ $siteContent->text('checkout','delivery','field_10') }}" required></div>
                        <div class="col-md-6"><label for="customerPhone">{{ $siteContent->text('checkout','delivery','field_4') }}</label><input class="form-control @error('phone') is-invalid @enderror" id="customerPhone" name="phone" placeholder="{{ $siteContent->text('checkout','delivery','field_11') }}" value="{{ old('phone', auth()->user()?->phone) }}" required></div>
                        <div class="col-12"><label for="customerEmail">{{ $siteContent->text('checkout','delivery','field_5') }} <small>{{ $siteContent->text('checkout','delivery','field_6') }}</small></label><input class="form-control" id="customerEmail" type="email" name="email" value="{{ old('email', auth()->user()?->email) }}" placeholder="{{ $siteContent->text('checkout','delivery','field_12') }}" @readonly(auth()->check() && ! auth()->user()->is_admin)></div>
                        <div class="col-md-6"><label for="district">{{ $siteContent->text('checkout','delivery','field_7') }}</label><input class="form-control @error('district') is-invalid @enderror" id="district" name="district" value="{{ old('district') }}" placeholder="{{ $siteContent->text('checkout','delivery','field_13') }}" autocomplete="off" required>@error('district')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="col-md-6"><label for="area">{{ $siteContent->text('checkout','delivery','field_8') }}</label><input class="form-control" id="area" name="area" value="{{ old('area') }}" placeholder="{{ $siteContent->text('checkout','delivery','field_14') }}" required></div>
                        <div class="col-12"><label for="address">{{ $siteContent->text('checkout','delivery','field_9') }}</label><textarea class="form-control" id="address" name="address" rows="3" placeholder="{{ $siteContent->text('checkout','delivery','field_15') }}" required>{{ old('address') }}</textarea></div>
                    </div>
                </section>

                <section class="checkout-card mt-4">
                    <div class="checkout-card-title"><span>২</span><div><h2>{{ $siteContent->text('checkout','payment','field_1') }}</h2><p>{{ $siteContent->text('checkout','payment','field_2') }}</p></div></div>
                    @php
                        $selectedPayment = old('payment_method', 'cod');
                        $bkashLogo = 'https://images.seeklogo.com/logo-png/47/1/bkash-logo-png_seeklogo-471379.png';
                        $nagadLogo = 'https://download.logo.wine/logo/Nagad/Nagad-Logo.wine.png';
                    @endphp
                    <div class="payment-grid">
                        <label class="payment"><input type="radio" name="payment_method" value="cod" @checked($selectedPayment === 'cod') data-payment-choice><i class="bi bi-cash-stack"></i><span><b>{{ $siteContent->text('checkout','payment','field_3') }}</b><small>{{ $siteContent->text('checkout','payment','field_4') }}</small></span><i class="bi bi-check-circle-fill payment-check"></i></label>
                        <label class="payment"><input type="radio" name="payment_method" value="bkash" @checked($selectedPayment === 'bkash') data-payment-choice data-payment-name="বিকাশ" data-payment-number="{{ $paymentSettings->bkash_number }}" data-payment-logo="{{ $bkashLogo }}"><img class="payment-logo" src="{{ $bkashLogo }}" alt="{{ $siteContent->text('checkout','payment','field_14') }}"><span><b>{{ $siteContent->text('checkout','payment','field_5') }}</b><small>{{ $siteContent->text('checkout','payment','field_6') }}</small></span><i class="bi bi-check-circle-fill payment-check"></i></label>
                        <label class="payment"><input type="radio" name="payment_method" value="nagad" @checked($selectedPayment === 'nagad') data-payment-choice data-payment-name="নগদ" data-payment-number="{{ $paymentSettings->nagad_number }}" data-payment-logo="{{ $nagadLogo }}"><img class="payment-logo" src="{{ $nagadLogo }}" alt="{{ $siteContent->text('checkout','payment','field_15') }}"><span><b>{{ $siteContent->text('checkout','payment','field_7') }}</b><small>{{ $siteContent->text('checkout','payment','field_8') }}</small></span><i class="bi bi-check-circle-fill payment-check"></i></label>
                    </div>
                    <div class="mobile-payment-details" data-payment-details @if ($selectedPayment === 'cod') hidden @endif>
                        <div class="payment-send-instruction">
                            <img data-selected-payment-logo src="{{ $selectedPayment === 'nagad' ? $nagadLogo : $bkashLogo }}" alt="{{ $siteContent->text('checkout','payment','field_16') }}">
                            <span><small>{{ $siteContent->text('checkout','payment','field_9') }}</small><b data-selected-payment-number>{{ $selectedPayment === 'nagad' ? $paymentSettings->nagad_number : $paymentSettings->bkash_number }}</b></span>
                            <button type="button" data-copy-payment-number @disabled(! ($selectedPayment === 'nagad' ? $paymentSettings->nagad_number : $paymentSettings->bkash_number))><i class="bi bi-copy"></i> {{ $siteContent->text('checkout','payment','field_10') }}</button>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-6"><label for="paymentPhone">{{ $siteContent->text('checkout','payment','field_11') }}</label><input class="form-control @error('payment_phone') is-invalid @enderror" id="paymentPhone" name="payment_phone" value="{{ old('payment_phone') }}" placeholder="{{ $siteContent->text('checkout','payment','field_17') }}">@error('payment_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                            <div class="col-md-6"><label for="transactionId">{{ $siteContent->text('checkout','payment','field_12') }}</label><input class="form-control @error('transaction_id') is-invalid @enderror" id="transactionId" name="transaction_id" value="{{ old('transaction_id') }}" placeholder="{{ $siteContent->text('checkout','payment','field_18') }}">@error('transaction_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        </div>
                        <p><i class="bi bi-info-circle"></i> {{ $siteContent->text('checkout','payment','field_13') }}</p>
                    </div>
                </section>
            </div>

            <div class="col-lg-5">
                <aside class="summary sticky-summary checkout-summary">
                    <span class="summary-kicker">{{ $siteContent->text('checkout','summary','field_1') }}</span><h3>{{ $siteContent->text('checkout','summary','field_2') }}</h3>
                    <div class="checkout-products">
                        @foreach ($cart as $row)
                            <article class="order-mini"><img src="{{ $row['image'] }}" alt="{{ $row['name'] }}"><span><b>{{ $row['name'] }}</b><small>{{ $siteContent->text('checkout','summary','field_3') }}{{ number_format($row['price']) }}</small></span><div class="quantity-control compact"><button form="checkout-quantity-{{ $row['id'] }}" name="quantity" value="{{ max(1, $row['quantity'] - 1) }}" type="submit" @disabled($row['quantity'] <= 1) aria-label="পরিমাণ কমান"><i class="bi bi-dash"></i></button><span>{{ $row['quantity'] }}</span><button form="checkout-quantity-{{ $row['id'] }}" name="quantity" value="{{ $row['quantity'] + 1 }}" type="submit" aria-label="পরিমাণ বাড়ান"><i class="bi bi-plus"></i></button></div><strong>{{ $siteContent->text('checkout','summary','field_4') }}{{ number_format($row['price'] * $row['quantity']) }}</strong></article>
                        @endforeach
                    </div>

                    <div class="coupon-box">
                        <label for="couponCode"><i class="bi bi-ticket-perforated"></i> {{ $siteContent->text('checkout','summary','field_5') }}</label>
                        @if ($coupon)
                            <div class="applied-coupon"><span><i class="bi bi-check-circle-fill"></i><b>{{ $coupon->code }}</b> {{ $siteContent->text('checkout','summary','field_6') }}</span><button form="coupon-remove-form" type="submit">{{ $siteContent->text('checkout','summary','field_7') }}</button></div>
                        @else
                            <div class="coupon-input"><input form="coupon-apply-form" id="couponCode" name="coupon_code" value="{{ old('coupon_code') }}" placeholder="{{ $siteContent->text('checkout','summary','field_19') }}"><button form="coupon-apply-form" type="submit">{{ $siteContent->text('checkout','summary','field_8') }}</button></div>
                            @error('coupon_code')<small class="coupon-error">{{ $message }}</small>@enderror
                        @endif
                    </div>

                    <div class="checkout-totals"><div><span>{{ $siteContent->text('checkout','summary','field_9') }}</span><b>{{ $siteContent->text('checkout','summary','field_10') }}{{ number_format($subtotal) }}</b></div>@if ($discount > 0)<div class="discount-row"><span>{{ $siteContent->text('checkout','summary','field_11') }}</span><b>{{ $siteContent->text('checkout','summary','field_12') }}{{ number_format($discount) }}</b></div>@endif<div><span>{{ $siteContent->text('checkout','summary','field_13') }}</span><b data-delivery-charge aria-live="polite">—</b></div></div>
                    <div class="checkout-totals"><div><span>{{ $siteContent->text('checkout','summary','total_label') }}</span><b data-checkout-total aria-live="polite">—</b></div></div>
                    <p class="delivery-note"><i class="bi bi-truck"></i> {{ strtr($siteContent->text('checkout','summary','delivery_rates'), [':dhaka' => config('delivery.dhaka'), ':outside' => config('delivery.outside_dhaka')]) }}</p>
                    <button class="btn btn-brand w-100 checkout-button" type="submit">{{ $siteContent->text('checkout','summary','field_16') }} <i class="bi bi-arrow-right"></i></button>
                    <small class="secure"><i class="bi bi-lock"></i> {{ $siteContent->text('checkout','summary','field_17') }}</small>
                </aside>
            </div>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-checkout-form]');
    if (!form) return;
    const deliveryRates = @json(config('delivery'));
    const discountedSubtotal = @json($subtotal - $discount);
    const formatMoney = (amount) => `৳${new Intl.NumberFormat('bn-BD', {maximumFractionDigits: 2}).format(amount)}`;
    const updateDeliveryCharge = () => {
        const district = form.elements.district.value.trim().toLowerCase();
        const charge = deliveryRates.dhaka_names.includes(district) ? deliveryRates.dhaka : deliveryRates.outside_dhaka;
        form.querySelector('[data-delivery-charge]').textContent = district ? formatMoney(charge) : '—';
        form.querySelector('[data-checkout-total]').textContent = district ? formatMoney(discountedSubtotal + charge) : '—';
    };
    form.elements.district.addEventListener('input', updateDeliveryCharge);
    form.elements.district.addEventListener('change', updateDeliveryCharge);
    window.addEventListener('pageshow', updateDeliveryCharge);
    updateDeliveryCharge();
    const fields = ['name', 'phone', 'email', 'district', 'area', 'address'];
    document.querySelector('#savedAddress')?.addEventListener('change', (event) => {
        const selected = event.target.selectedOptions[0]?.dataset.address;
        if (!selected) return;
        const address = JSON.parse(selected);
        ['name', 'phone', 'district', 'area', 'address'].forEach((name) => {
            form.elements[name].value = address[name];
            form.elements[name].dispatchEvent(new Event('input', {bubbles: true}));
        });
    });
    let timer;
    let submitting = false;
    form.addEventListener('submit', (event) => {
        if (submitting) {
            event.preventDefault();
            return;
        }
        submitting = true;
        clearTimeout(timer);
        form.querySelector('.checkout-button').disabled = true;
    });
    window.addEventListener('pageshow', () => {
        submitting = false;
        form.querySelector('.checkout-button').disabled = false;
    });
    let savedDetails = '';
    const pendingDetails = new Set();
    const save = () => {
        clearTimeout(timer);
        if (submitting) return;
        const name = form.elements.name.value.trim();
        const phone = form.elements.phone.value.trim();
        if (!name || !/^01[3-9][0-9]{8}$/.test(phone)) return;

        const values = Object.fromEntries(fields.map((field) => [field, form.elements[field]?.value.trim() || '']));
        if (!form.elements.email.checkValidity()) values.email = '';
        const details = JSON.stringify(values);
        if (details === savedDetails || pendingDetails.has(details)) return;
        const payload = new FormData();
        Object.entries(values).forEach(([field, value]) => payload.append(field, value));
        pendingDetails.add(details);
        fetch(form.dataset.incompleteUrl, {
            method: 'POST', keepalive: true,
            headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json'},
            body: payload,
        }).then((response) => {
            if (response.ok) savedDetails = details;
        }).catch(() => {}).finally(() => pendingDetails.delete(details));
    };
    fields.forEach((name) => {
        form.elements[name]?.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(save, 400); });
        form.elements[name]?.addEventListener('change', save);
        form.elements[name]?.addEventListener('blur', save);
    });
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') save();
    });
    window.addEventListener('pagehide', save);
    window.addEventListener('pageshow', save);
    save();
});
</script>
@endpush
