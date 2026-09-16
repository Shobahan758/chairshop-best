@extends('layouts.app')
@section('title', $siteContent->text('cart','heading','page_title'))

@section('content')
<div class="page-hero compact cart-hero"><div class="container"><span class="eyebrow">{{ $siteContent->text('cart','heading','field_1') }}</span><h1>{{ $siteContent->text('cart','heading','field_2') }}</h1><p>{{ $siteContent->text('cart','heading','field_3') }}</p></div></div>
<section class="section cart-section">
    <div class="container">
        @if (count($cart))
            <div class="row g-4 g-xl-5">
                <div class="col-lg-8">
                    <div class="cart-list">
                        @foreach ($cart as $row)
                            <article class="cart-row">
                                <a class="cart-product-image" href="{{ route('product', $row['slug']) }}"><img src="{{ $row['image'] }}" alt="{{ $row['name'] }}"></a>
                                <div class="cart-info"><small>{{ $siteContent->text('cart','cart','field_1') }}</small><a href="{{ route('product', $row['slug']) }}"><h3>{{ $row['name'] }}</h3></a><b>{{ $siteContent->text('cart','cart','field_2') }}{{ number_format($row['price']) }}</b></div>
                                <form class="quantity-control" method="post" action="{{ route('cart.update', $row['id']) }}">
                                    @csrf @method('PATCH')
                                    <button name="quantity" value="{{ max(1, $row['quantity'] - 1) }}" type="submit" aria-label="পরিমাণ কমান" @disabled($row['quantity'] <= 1)><i class="bi bi-dash"></i></button>
                                    <span>{{ $row['quantity'] }}</span>
                                    <button name="quantity" value="{{ $row['quantity'] + 1 }}" type="submit" aria-label="পরিমাণ বাড়ান"><i class="bi bi-plus"></i></button>
                                </form>
                                <strong class="cart-line-total">{{ $siteContent->text('cart','cart','field_3') }}{{ number_format($row['price'] * $row['quantity']) }}</strong>
                                <form method="post" action="{{ route('cart.remove', $row['id']) }}">@csrf @method('DELETE')<button class="icon-delete" type="submit" aria-label="পণ্য সরান"><i class="bi bi-trash"></i></button></form>
                            </article>
                        @endforeach
                    </div>
                    <a class="continue-shopping" href="{{ route('shop') }}"><i class="bi bi-arrow-left"></i> {{ $siteContent->text('cart','cart','field_4') }}</a>
                </div>
                <div class="col-lg-4">
                    <aside class="summary cart-summary">
                        <span class="summary-kicker">{{ $siteContent->text('cart','summary','field_1') }}</span><h3>{{ $siteContent->text('cart','summary','field_2') }}</h3>
                        <div><span>{{ $siteContent->text('cart','summary','field_3') }}</span><b>{{ $siteContent->text('cart','summary','field_4') }}{{ number_format($subtotal) }}</b></div>
                        @if ($discount > 0)<div class="discount-row"><span>{{ $siteContent->text('cart','summary','field_5') }}</span><b>{{ $siteContent->text('cart','summary','field_6') }}{{ number_format($discount) }}</b></div>@endif
                        <div><span>{{ $siteContent->text('cart','summary','field_7') }}</span><span>{{ $siteContent->text('cart','summary','field_8') }}</span></div>
                        <div class="cart-coupon">
                            <label for="cartCoupon"><i class="bi bi-ticket-perforated"></i> {{ $siteContent->text('cart','summary','field_9') }}</label>
                            @if ($coupon)
                                <div class="applied-coupon"><span><i class="bi bi-check-circle-fill"></i> <b>{{ $coupon->code }}</b></span><form method="post" action="{{ route('checkout.coupon.remove') }}">@csrf @method('DELETE')<button type="submit">{{ $siteContent->text('cart','summary','field_10') }}</button></form></div>
                            @else
                                <form class="coupon-input" method="post" action="{{ route('checkout.coupon.apply') }}">@csrf<input id="cartCoupon" name="coupon_code" value="{{ old('coupon_code') }}" placeholder="{{ $siteContent->text('cart','summary','field_17') }}"><button type="submit">{{ $siteContent->text('cart','summary','field_11') }}</button></form>
                                @error('coupon_code')<small class="coupon-error">{{ $message }}</small>@enderror
                            @endif
                        </div>
                        <div class="cart-payable"><span>{{ $siteContent->text('cart','summary','field_12') }}</span><strong>{{ $siteContent->text('cart','summary','field_13') }}{{ number_format($subtotal - $discount) }}</strong></div>
                        <hr><p><i class="bi bi-truck"></i> {{ $siteContent->text('cart','summary','field_14') }}</p><a class="btn btn-brand w-100" href="{{ route('checkout') }}">{{ $siteContent->text('cart','summary','field_15') }} <i class="bi bi-arrow-right"></i></a><small class="secure"><i class="bi bi-shield-check"></i> {{ $siteContent->text('cart','summary','field_16') }}</small>
                    </aside>
                </div>
            </div>
        @else
            <div class="empty"><i class="bi bi-bag"></i><h2>{{ $siteContent->text('cart','empty_cart','field_1') }}</h2><p>{{ $siteContent->text('cart','empty_cart','field_2') }}</p><a class="btn btn-brand" href="{{ route('shop') }}">{{ $siteContent->text('cart','empty_cart','field_3') }}</a></div>
        @endif
    </div>
</section>
@endsection
