<!doctype html><html lang="bn"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="csrf-token" content="{{csrf_token()}}"><meta name="tracking-endpoint" content="{{route('tracking.events')}}"><meta name="description" content="{{ $siteContent->text('shared','metadata','description') }}"><title>@yield('title', $siteContent->text('shared','metadata','title'))</title><link rel="preconnect" href="https://fonts.googleapis.com"><link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">@vite(['resources/css/app.css','resources/js/app.js'])@include('partials.tracking-pixels')</head><body>
<header class="sticky-top bg-white"><nav class="navbar navbar-expand-lg"><div class="container"><a class="brand" href="{{route('home')}}">@if($siteContent->text('shared','header','logo'))<img class="store-brand-logo" src="{{ $siteContent->text('shared','header','logo') }}" alt="{{ $siteContent->text('shared','header','field_1').$siteContent->text('shared','header','field_2') }}">@else<span>{{ $siteContent->text('shared','header','field_1') }}</span>{{ $siteContent->text('shared','header','field_2') }}@endif<small>{{ $siteContent->text('shared','header','field_3') }}</small></a><button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button><div class="collapse navbar-collapse" id="nav"><ul class="navbar-nav mx-auto"><li><a class="nav-link" href="{{route('home')}}">{{ $siteContent->text('shared','header','field_4') }}</a></li><li><a class="nav-link" href="{{route('shop')}}">{{ $siteContent->text('shared','header','field_5') }}</a></li><li><a class="nav-link" href="{{route('shop',['category'=>'office'])}}">{{ $siteContent->text('shared','header','field_6') }}</a></li><li><a class="nav-link" href="{{route('shop',['category'=>'gaming'])}}">{{ $siteContent->text('shared','header','field_7') }}</a></li><li><a class="nav-link {{ request()->is('info/about') ? 'active fw-semibold' : '' }}" href="{{ route('page', 'about') }}">{{ $siteContent->text('shared','header','about_label') }}</a></li><li><a class="nav-link {{ request()->routeIs('contact') || request()->is('info/contact') ? 'active fw-semibold' : '' }}" href="{{ route('contact') }}">{{ $siteContent->text('shared','header','field_9') }}</a></li></ul><form action="{{route('shop')}}" class="search" data-track-event="form_submit"><i class="bi bi-search"></i><input name="q" placeholder="{{ $siteContent->text('shared','header','field_10') }}" value="{{request('q')}}"></form><div class="header-actions"><a class="header-icon" href="{{auth()->check() ? (auth()->user()->is_admin && ! session('customer_account_id') ? route('admin') : route('dashboard')) : (session('customer_account_id') || session('customer_order_ids') ? route('dashboard') : route('login'))}}" aria-label="My Account" title="My Account"><i class="bi bi-person"></i></a><a class="header-icon" href="{{ route('dashboard', ['section' => 'wishlist']) }}" aria-label="Wishlist" title="Wishlist"><i class="bi bi-heart"></i></a><a class="cart-link" href="{{route('cart')}}" aria-label="Cart" title="Cart"><i class="bi bi-bag"></i>@if (collect(session('cart', []))->sum('quantity') > 0)<b>{{ collect(session('cart', []))->sum('quantity') > 99 ? '99+' : collect(session('cart', []))->sum('quantity') }}</b>@endif</a></div></div></div></nav></header>
@if(session('success'))
<div class="store-notification" data-store-notification role="status" aria-live="polite" aria-atomic="true">
    <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
    <span>{{ session('success') }}</span>
    <button type="button" data-dismiss-notification aria-label="বন্ধ করুন" title="বন্ধ করুন"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
</div>
@endif
<main style="--page-banner-image: url('{{ $siteContent->text('shared','page_banner','image') }}')">@yield('content')</main>@stack('scripts')
@php
    $footerPhone = $siteContent->text('contact', 'contact_details', 'phone');
    $footerEmail = $siteContent->text('contact', 'contact_details', 'email');
    $footerAddress = $siteContent->text('contact', 'contact_details', 'address');
@endphp
<footer class="store-footer">
    <div class="container">
        <div class="store-footer-grid">
            <div class="store-footer-about">
                <a class="brand brand-light" href="{{ route('home') }}">@if($siteContent->text('shared','footer','logo'))<img class="store-brand-logo" src="{{ $siteContent->text('shared','footer','logo') }}" alt="{{ $siteContent->text('shared','footer','field_1').$siteContent->text('shared','footer','field_2') }}">@else<span>{{ $siteContent->text('shared','footer','field_1') }}</span>{{ $siteContent->text('shared','footer','field_2') }}@endif</a>
                <p>{{ $siteContent->text('shared','footer','field_3') }}</p>
                <span class="store-footer-tagline"><i class="bi bi-heart" aria-hidden="true"></i> {{ $siteContent->text('shared','footer','field_4') }}</span>
            </div>
            <div class="store-footer-links"><h2>{{ $siteContent->text('shared','footer','field_5') }}</h2><a href="{{ route('shop') }}">{{ $siteContent->text('shared','footer','field_6') }}</a><a href="{{ route('track') }}">{{ $siteContent->text('shared','footer','field_7') }}</a><a href="{{ route('cart') }}">{{ $siteContent->text('shared','footer','field_8') }}</a></div>
            <div class="store-footer-links"><h2>{{ $siteContent->text('shared','footer','field_9') }}</h2><a href="{{ route('page', 'about') }}">{{ $siteContent->text('shared','footer','about_label') }}</a><a href="{{ route('page', 'delivery') }}">{{ $siteContent->text('shared','footer','field_10') }}</a><a href="{{ route('page', 'returns') }}">{{ $siteContent->text('shared','footer','field_11') }}</a><a href="{{ route('contact') }}">{{ $siteContent->text('shared','footer','field_12') }}</a></div>
            <div class="store-footer-contact">
                <h2>{{ $siteContent->text('shared','footer','field_13') }}</h2>
                <address>
                    @if($footerAddress)
                        <div class="store-footer-contact-row"><i class="bi bi-geo-alt" aria-hidden="true"></i><div><span>{{ $siteContent->text('shared','footer','field_14') }}</span><p>{{ $footerAddress }}</p></div></div>
                    @endif
                    @if($footerEmail)
                        <a class="store-footer-contact-row" href="mailto:{{ $footerEmail }}"><i class="bi bi-envelope" aria-hidden="true"></i><div><span>{{ $siteContent->text('shared','footer','field_15') }}</span><b>{{ $footerEmail }}</b></div></a>
                    @endif
                    @if($footerPhone)
                        <a class="store-footer-contact-row" href="tel:{{ preg_replace('/[^+0-9]/', '', $footerPhone) }}"><i class="bi bi-telephone" aria-hidden="true"></i><div><span>{{ $siteContent->text('shared','footer','field_16') }}</span><b>{{ $footerPhone }}</b></div></a>
                    @endif
                </address>
                <a class="store-footer-message" href="{{ route('contact') }}">{{ $siteContent->text('shared','footer','field_17') }} <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
            </div>
        </div>
        <div class="store-footer-bottom"><small>© {{ date('Y') }} {{ $siteContent->text('shared','footer','field_18') }}</small><span>{{ $siteContent->text('shared','footer','field_19') }}</span></div>
    </div>
</footer><nav class="mobile-nav"><a href="{{route('home')}}"><i class="bi bi-house"></i>{{ $siteContent->text('shared','mobile_navigation','field_1') }}</a><a href="{{route('shop')}}"><i class="bi bi-grid"></i>{{ $siteContent->text('shared','mobile_navigation','field_2') }}</a><a href="{{route('track')}}"><i class="bi bi-truck"></i>{{ $siteContent->text('shared','mobile_navigation','field_3') }}</a><a href="{{route('cart')}}"><i class="bi bi-bag"></i>{{ $siteContent->text('shared','mobile_navigation','field_4') }}</a></nav><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script></body></html>
