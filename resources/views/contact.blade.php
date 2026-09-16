@extends('layouts.app')

@section('title', $siteContent->text('contact','heading','page_title'))

@section('content')
@php
    $contactPhone = $siteContent->text('contact', 'contact_details', 'phone');
    $contactEmail = $siteContent->text('contact', 'contact_details', 'email');
    $contactAddress = $siteContent->text('contact', 'contact_details', 'address');
@endphp
<div class="page-hero contact-banner"><span class="eyebrow">{{ $siteContent->text('contact','heading','field_3') }}</span><h1>{{ $siteContent->text('contact','heading','field_4') }} <em>{{ $siteContent->text('contact','heading','field_5') }}</em></h1><p>{{ $siteContent->text('contact','heading','field_6') }}</p></div>
<section class="contact-page">
    <div class="container">
        <nav class="contact-breadcrumb" aria-label="ব্রেডক্রাম্ব"><a href="{{ route('home') }}">{{ $siteContent->text('contact','heading','field_1') }}</a><i class="bi bi-chevron-right" aria-hidden="true"></i><span aria-current="page">{{ $siteContent->text('contact','heading','field_2') }}</span></nav>

        <div class="contact-layout">
            <aside class="contact-details" aria-labelledby="contact-details-title">
                <span class="contact-details-kicker">{{ $siteContent->text('contact','contact_details','field_1') }}</span>
                <h2 id="contact-details-title">{{ $siteContent->text('contact','contact_details','field_2') }}<br>{{ $siteContent->text('contact','contact_details','field_3') }}</h2>
                <p>{{ $siteContent->text('contact','contact_details','field_4') }}</p>
                <div class="contact-channels">
                    @if($contactPhone)
                        <a href="tel:{{ preg_replace('/[^+0-9]/', '', $contactPhone) }}"><i class="bi bi-telephone" aria-hidden="true"></i><span><small>{{ $siteContent->text('contact','contact_details','field_5') }}</small><b>{{ $contactPhone }}</b></span><i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                    @else
                        <div><i class="bi bi-telephone" aria-hidden="true"></i><span><small>{{ $siteContent->text('contact','contact_details','field_5') }}</small><b>{{ $siteContent->text('contact','contact_details','missing_details') }}</b></span></div>
                    @endif
                    @if($contactEmail)
                        <a href="mailto:{{ $contactEmail }}"><i class="bi bi-envelope" aria-hidden="true"></i><span><small>{{ $siteContent->text('contact','contact_details','field_6') }}</small><b>{{ $contactEmail }}</b></span><i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                    @else
                        <div><i class="bi bi-envelope" aria-hidden="true"></i><span><small>{{ $siteContent->text('contact','contact_details','field_6') }}</small><b>{{ $siteContent->text('contact','contact_details','missing_details') }}</b></span></div>
                    @endif
                    @if($contactAddress)
                        <div><i class="bi bi-geo-alt" aria-hidden="true"></i><span><small>{{ $siteContent->text('contact','contact_details','field_7') }}</small><b>{{ $contactAddress }}</b></span></div>
                    @else
                        <div><i class="bi bi-geo-alt" aria-hidden="true"></i><span><small>{{ $siteContent->text('contact','contact_details','field_7') }}</small><b>{{ $siteContent->text('contact','contact_details','missing_details') }}</b></span></div>
                    @endif
                    <a href="{{ (auth()->user()?->is_admin ? route('admin.support.index') : route('dashboard', ['section' => 'support'])) }}"><i class="bi bi-chat-left-dots" aria-hidden="true"></i><span><small>{{ $siteContent->text('contact','contact_details','field_8') }}</small><b>{{ $siteContent->text('contact','contact_details','field_9') }}</b></span><i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                </div>
                <div class="contact-details-bottom"><i class="bi bi-heart" aria-hidden="true"></i><span>{{ $siteContent->text('contact','contact_details','field_10') }}</span></div>
            </aside>
            <div class="contact-message">
                <div class="contact-form-heading"><div><span class="eyebrow">{{ $siteContent->text('contact','message_form','field_1') }}</span><h2>{{ $siteContent->text('contact','message_form','field_2') }}</h2></div><span class="contact-message-icon"><i class="bi bi-send" aria-hidden="true"></i></span></div>
                <p>{{ $siteContent->text('contact','message_form','field_3') }}</p>
                    <form method="post" action="{{ route('customer.tickets.store') }}" class="contact-form">
                        @csrf
                        <div><label for="contact-name">{{ $siteContent->text('contact','message_form','field_4') }} <span>*</span></label><input class="form-control @error('name') is-invalid @enderror" id="contact-name" name="name" type="text" autocomplete="name" value="{{ old('name', auth()->user()?->name) }}" placeholder="{{ $siteContent->text('contact','message_form','field_10') }}" maxlength="100" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="row g-3">
                            <div class="col-md-6"><label for="contact-phone">{{ $siteContent->text('contact','message_form','field_5') }} <span>*</span></label><input class="form-control @error('phone') is-invalid @enderror" id="contact-phone" name="phone" type="tel" autocomplete="tel" value="{{ old('phone', auth()->user()?->phone) }}" placeholder="{{ $siteContent->text('contact','message_form','field_11') }}" pattern="01[3-9][0-9]{8}" maxlength="11" required>@error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                            <div class="col-md-6"><label for="contact-email">{{ $siteContent->text('contact','message_form','field_6') }} <span>*</span></label><input class="form-control @error('email') is-invalid @enderror" id="contact-email" name="email" type="email" autocomplete="email" value="{{ old('email', auth()->user()?->email) }}" placeholder="{{ $siteContent->text('contact','message_form','field_12') }}" maxlength="255" required>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        </div>
                        <div><label for="contact-subject">{{ $siteContent->text('contact','message_form','field_7') }} <span>*</span></label><input class="form-control @error('subject') is-invalid @enderror" id="contact-subject" name="subject" value="{{ old('subject') }}" placeholder="{{ $siteContent->text('contact','message_form','field_13') }}" maxlength="160" required @error('subject') aria-invalid="true" aria-describedby="contact-subject-error" @enderror>@error('subject')<div class="invalid-feedback" id="contact-subject-error">{{ $message }}</div>@enderror</div>
                        <div><label for="contact-message">{{ $siteContent->text('contact','message_form','field_8') }} <span>*</span></label><textarea class="form-control @error('message') is-invalid @enderror" id="contact-message" name="message" rows="6" minlength="5" maxlength="5000" placeholder="{{ $siteContent->text('contact','message_form','field_14') }}" required @error('message') aria-invalid="true" aria-describedby="contact-message-error" @enderror>{{ old('message') }}</textarea>@error('message')<div class="invalid-feedback" id="contact-message-error">{{ $message }}</div>@enderror</div>
                        <div class="contact-form-bottom"><span><i class="bi bi-chat-square-text" aria-hidden="true"></i> {{ auth()->user()?->is_admin ? 'আপনার মেসেজ সাপোর্ট প্যানেলে জমা হবে।' : 'পাঠানোর পর সাপোর্ট ইনবক্স খুলবে।' }}</span><button class="btn btn-brand" type="submit">{{ $siteContent->text('contact','message_form','field_9') }} <i class="bi bi-arrow-up-right" aria-hidden="true"></i></button></div>
                    </form>
            </div>
        </div>
        <div class="contact-help-heading"><span>{{ $siteContent->text('contact','help_links','field_1') }}</span><h2>{{ $siteContent->text('contact','help_links','field_2') }}</h2></div>
        <div class="contact-help-links">
            <a href="{{ route('track') }}"><span class="contact-help-icon"><i class="bi bi-box-seam" aria-hidden="true"></i></span><span><b>{{ $siteContent->text('contact','help_links','field_3') }}</b><small>{{ $siteContent->text('contact','help_links','field_4') }}</small></span><i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
            <a href="{{ route('page', 'delivery') }}"><span class="contact-help-icon"><i class="bi bi-truck" aria-hidden="true"></i></span><span><b>{{ $siteContent->text('contact','help_links','field_5') }}</b><small>{{ $siteContent->text('contact','help_links','field_6') }}</small></span><i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
            <a href="{{ route('page', 'returns') }}"><span class="contact-help-icon"><i class="bi bi-arrow-repeat" aria-hidden="true"></i></span><span><b>{{ $siteContent->text('contact','help_links','field_7') }}</b><small>{{ $siteContent->text('contact','help_links','field_8') }}</small></span><i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
        </div>
    </div>
</section>
@endsection
