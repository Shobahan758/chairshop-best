@extends('layouts.admin')

@php
    $integrations = [
        'meta' => ['Meta Pixel', 'Track Facebook and Instagram advertising conversions.', 'meta', 'meta_pixel_id', 'Meta Pixel ID', 'Numeric pixel ID'],
        'google' => ['Google Tracking', 'Connect Google Analytics, Google Ads or Tag Manager.', 'google', 'google_tag_id', 'Google Tag ID', 'G-XXXXXXXXXX or GTM-XXXXXXX'],
        'tiktok' => ['TikTok Pixel', 'Measure TikTok advertising traffic and conversions.', 'tiktok', 'tiktok_pixel_id', 'TikTok Pixel ID', 'CXXXXXXXXXXXXXXXXX'],
    ];
    $pageTitle = $section === 'site' ? 'Site Tracking' : ($section === 'other' ? 'Other Tracking' : $integrations[$section][0]);
@endphp

@section('title', $pageTitle.' — ChairGhor')

@section('content')
<div class="tracking-page-heading">
    <div>
        <h1>{{ $pageTitle }}</h1>
        <p>
            @if ($section === 'site')
                Track page views and important customer events across the storefront.
            @elseif ($section === 'other')
                Configure Pinterest and Snapchat tracking integrations.
            @else
                {{ $integrations[$section][1] }}
            @endif
        </p>
    </div>
    @if ($section === 'site')
        <span class="tracking-event-total"><i class="bi bi-activity"></i> {{ $eventCounts->sum() }} total events</span>
    @endif
</div>

@if ($section === 'site')
    @php
        $cards = [
            ['page_view', 'Page View', 'Total page views across the site', 'globe2', 'amber'],
            ['landing_page_view', 'Landing Page View', 'Views of the landing homepage', 'house-door-fill', 'lime'],
            ['product_view', 'Product View', 'Views of individual product pages', 'box-seam-fill', 'mint'],
            ['button_click', 'Button Click', 'All tracked button and link clicks', 'cursor-fill', 'amber'],
            ['add_to_cart', 'Add to Cart', 'Items added to the shopping cart', 'cart-plus-fill', 'lime'],
            ['checkout_open', 'Checkout / Order Form Open', 'Checkout or order form opened', 'receipt', 'mint'],
            ['form_submit', 'Form Submit', 'Contact, search and newsletter submissions', 'send-fill', 'amber'],
            ['purchase_complete', 'Purchase / Order Complete', 'Completed purchases or orders', 'check-circle-fill', 'lime'],
            ['phone_whatsapp_click', 'Phone / WhatsApp Click', 'Phone or WhatsApp click-to-call events', 'telephone-fill', 'mint'],
            ['scroll_engagement', 'Scroll / Engagement', '75% scroll depth engagement events', 'arrow-down-up', 'amber'],
        ];
    @endphp
    <div class="row g-3 tracking-card-grid">
        @foreach ($cards as $card)
            <div class="col-xl-6">
                <article class="tracking-metric">
                    <i class="bi bi-{{ $card[3] }} {{ $card[4] }}"></i>
                    <div><small>{{ $card[2] }}</small><b>{{ $card[1] }}</b><strong>{{ $eventCounts->get($card[0], 0) }}</strong></div>
                </article>
            </div>
        @endforeach
    </div>
@elseif ($section === 'other')
    <section class="tracking-integration-card">
        <div class="tracking-integration-head">
            <span><i class="bi bi-bezier2"></i></span>
            <div><h2>Other tracking integrations</h2><p>Leave a field empty to disable that tracker.</p></div>
        </div>
        <form method="post" action="{{ route('admin.tracking.integration.update', $section) }}">
            @csrf
            @method('PUT')
            <div class="tracking-integration-body d-grid gap-3">
                <div>
                    <label class="form-label" for="pinterestTag">Pinterest Tag ID</label>
                    <input class="form-control @error('pinterest_tag_id') is-invalid @enderror" id="pinterestTag" name="pinterest_tag_id" value="{{ old('pinterest_tag_id', $settings->pinterest_tag_id) }}" placeholder="Numeric tag ID">
                    @error('pinterest_tag_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label" for="snapchatPixel">Snapchat Pixel ID</label>
                    <input class="form-control @error('snapchat_pixel_id') is-invalid @enderror" id="snapchatPixel" name="snapchat_pixel_id" value="{{ old('snapchat_pixel_id', $settings->snapchat_pixel_id) }}" placeholder="Pixel ID">
                    @error('snapchat_pixel_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="tracking-integration-footer"><small><i class="bi bi-shield-check"></i> IDs are stored securely in site settings.</small><button class="btn btn-admin-primary" type="submit"><i class="bi bi-check2"></i> Save Integration</button></div>
        </form>
    </section>
@else
    @php($integration = $integrations[$section])
    <section class="tracking-integration-card">
        <div class="tracking-integration-head">
            <span><i class="bi bi-{{ $integration[2] }}"></i></span>
            <div><h2>{{ $integration[0] }}</h2><p>Leave the field empty to disable that tracker.</p></div>
        </div>
        <form method="post" action="{{ route('admin.tracking.integration.update', $section) }}">
            @csrf
            @method('PUT')
            <div class="tracking-integration-body">
                <label class="form-label" for="integrationId">{{ $integration[4] }}</label>
                <input class="form-control @error($integration[3]) is-invalid @enderror" id="integrationId" name="{{ $integration[3] }}" value="{{ old($integration[3], $settings->{$integration[3]}) }}" placeholder="{{ $integration[5] }}">
                @error($integration[3])<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="tracking-integration-footer"><small><i class="bi bi-shield-check"></i> IDs are stored securely in site settings.</small><button class="btn btn-admin-primary" type="submit"><i class="bi bi-check2"></i> Save Integration</button></div>
        </form>
    </section>
@endif
@endsection
