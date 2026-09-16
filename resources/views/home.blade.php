@extends('layouts.app') @section('content')
@php
$heroSlides = [
    [$siteContent->text('home','banner_1','field_1'), $siteContent->text('home','banner_1','field_2'), $siteContent->text('home','banner_1','field_3'), $siteContent->text('home','banner_1','field_4'), $siteContent->text('home','banner_1','field_5'), $siteContent->text('home','banner_1','field_6'), $siteContent->text('home','banner_1','field_7'), $siteContent->text('home','banner_1','field_8')],
    [$siteContent->text('home','banner_2','field_1'), $siteContent->text('home','banner_2','field_2'), $siteContent->text('home','banner_2','field_3'), $siteContent->text('home','banner_2','field_4'), $siteContent->text('home','banner_2','field_5'), $siteContent->text('home','banner_2','field_6'), $siteContent->text('home','banner_2','field_7'), $siteContent->text('home','banner_2','field_8')],
    [$siteContent->text('home','banner_3','field_1'), $siteContent->text('home','banner_3','field_2'), $siteContent->text('home','banner_3','field_3'), $siteContent->text('home','banner_3','field_4'), $siteContent->text('home','banner_3','field_5'), $siteContent->text('home','banner_3','field_6'), $siteContent->text('home','banner_3','field_7'), $siteContent->text('home','banner_3','field_8')],
];
@endphp
<section id="heroCarousel" class="hero carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000" data-bs-pause="hover" aria-label="চেয়ারঘরের বিশেষ সংগ্রহ">
    <div class="carousel-indicators hero-dots">
        @foreach($heroSlides as $slide)
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="{{ $loop->index }}" class="{{ $loop->first ? 'active' : '' }}" aria-current="{{ $loop->first ? 'true' : 'false' }}" aria-label="ব্যানার {{ $loop->iteration }}"></button>
        @endforeach
    </div>
    <div class="carousel-inner hero-slides">
        @foreach($heroSlides as $slide)
            <article class="carousel-item hero-slide {{ $loop->first ? 'active' : '' }}" aria-label="{{ $slide[7] }}" style="--hero-image: url('{{ $slide[6] }}')">
                <div class="container"><div class="row align-items-center">
                    <div class="col-lg-8 mx-auto text-center hero-copy">
                        <span class="eyebrow">{{ $slide[0] }}</span><h1>{{ $slide[1] }}<br><em>{{ $slide[2] }}</em></h1><p>{{ $slide[3] }}</p>
                        <div class="d-flex flex-wrap justify-content-center gap-3"><a class="btn btn-brand" href="{{ $slide[5] }}">{{ $slide[4] }} <i class="bi bi-arrow-up-right"></i></a><a class="btn btn-link" href="#guide">{{ $siteContent->text('home','hero','field_1') }}</a></div>
                        <div class="hero-stats justify-content-center"><div><b>{{ $siteContent->text('home','hero','customer_count') }}</b><span>{{ $siteContent->text('home','hero','field_2') }}</span></div><div><b>{{ $siteContent->text('home','hero','field_3') }}</b><span>{{ $siteContent->text('home','hero','field_4') }}</span></div><div><b>{{ $siteContent->text('home','hero','field_5') }}</b><span>{{ $siteContent->text('home','hero','field_6') }}</span></div></div>
                    </div>
                </div></div>
            </article>
        @endforeach
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">{{ $siteContent->text('home','hero','field_7') }}</span></button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">{{ $siteContent->text('home','hero','field_8') }}</span></button>
</section>
<section class="section"><div class="container"><div class="section-head"><div><span class="eyebrow">আপনার প্রয়োজন</span><h2>ধরন অনুযায়ী খুঁজুন</h2></div><a href="{{route('shop')}}">সব সংগ্রহ <i class="bi bi-arrow-right"></i></a></div><div class="category-grid" data-category-slider aria-label="পণ্যের ক্যাটাগরি">@foreach($categories as $category)<a href="{{route('shop',['category'=>$category->slug])}}"><i class="bi bi-{{$category->icon}}"></i><b>{{$category->name}}</b><span>{{$category->products_count}}টি পণ্য</span></a>@endforeach</div></div></section>
<section class="section products-section"><div class="container"><div class="section-head"><div><span class="eyebrow">জনপ্রিয় পছন্দ</span><h2>যে চেয়ারগুলো সবাই ভালোবাসে</h2></div><a href="{{route('shop')}}">সব দেখুন <i class="bi bi-arrow-right"></i></a></div><div class="row g-4">@foreach($featured as $product)<div class="col-6 col-lg-3"><x-product-card :product="$product" /></div>@endforeach</div></div></section>
<section class="section"><div class="container"><div class="section-head"><div><span class="eyebrow">Just For You</span><h2>শুধু আপনার জন্য বাছাই করা</h2></div><a href="{{route('shop',['sort'=>'newest'])}}">সব দেখুন <i class="bi bi-arrow-right"></i></a></div><div class="row g-4">@foreach($justForYou as $product)<div class="col-6 col-lg-3"><x-product-card :product="$product" /></div>@endforeach</div></div></section>
@if($officeProducts->isNotEmpty())<section class="section products-section"><div class="container"><div class="section-head"><div><span class="eyebrow">অফিস এসেনশিয়ালস</span><h2>কাজের সময় আরও স্বচ্ছন্দ</h2></div><a href="{{route('shop',['category'=>'office'])}}">সব অফিস চেয়ার <i class="bi bi-arrow-right"></i></a></div><div class="row g-4">@foreach($officeProducts as $product)<div class="col-6 col-lg-3"><x-product-card :product="$product" /></div>@endforeach</div></div></section>@endif
@if($gamingProducts->isNotEmpty())<section class="section"><div class="container"><div class="section-head"><div><span class="eyebrow">গেমিং পিকস</span><h2>খেলার প্রতিটি মুহূর্তে আরাম</h2></div><a href="{{route('shop',['category'=>'gaming'])}}">সব গেমিং চেয়ার <i class="bi bi-arrow-right"></i></a></div><div class="row g-4">@foreach($gamingProducts as $product)<div class="col-6 col-lg-3"><x-product-card :product="$product" /></div>@endforeach</div></div></section>@endif
<section class="comfort-guide" id="guide" aria-labelledby="comfort-guide-title">
    <div class="container">
        <div class="comfort-guide-layout">
            <div class="comfort-guide-visual">
                <div class="comfort-guide-topline"><span>{{ $siteContent->text('home','guide','field_1') }}</span><span>{{ $siteContent->text('home','guide','field_2') }}</span></div>
                <div class="comfort-guide-photo">
                    <img src="{{ $siteContent->text('home','guide','field_22') }}" alt="{{ $siteContent->text('home','guide','field_23') }}" loading="lazy" width="1000" height="1200">
                    <span class="comfort-guide-stamp"><i class="bi bi-stars" aria-hidden="true"></i>{{ $siteContent->text('home','guide','field_3') }}<br>{{ $siteContent->text('home','guide','field_4') }}</span>
                </div>
                <div class="comfort-guide-caption"><span class="comfort-guide-caption-line"></span><span>{{ $siteContent->text('home','guide','field_5') }}</span></div>
                <div class="comfort-guide-note"><i class="bi bi-heart" aria-hidden="true"></i><div><b>{{ $siteContent->text('home','guide','field_6') }}</b><span>{{ $siteContent->text('home','guide','field_7') }}</span></div></div>
            </div>
            <div class="comfort-guide-copy">
                <span class="comfort-guide-eyebrow"><span></span> {{ $siteContent->text('home','guide','field_8') }}</span>
                <h2 id="comfort-guide-title">{{ $siteContent->text('home','guide','field_9') }}<br>{{ $siteContent->text('home','guide','field_10') }} <em>{{ $siteContent->text('home','guide','field_11') }}</em></h2>
                <p class="comfort-guide-intro">{{ $siteContent->text('home','guide','field_12') }}</p>
                <ol class="comfort-guide-features">
                    <li><span class="comfort-guide-number">০১</span><div><h3>{{ $siteContent->text('home','guide','field_13') }}</h3><p>{{ $siteContent->text('home','guide','field_14') }}</p></div><i class="bi bi-arrows-vertical" aria-hidden="true"></i></li>
                    <li><span class="comfort-guide-number">০২</span><div><h3>{{ $siteContent->text('home','guide','field_15') }}</h3><p>{{ $siteContent->text('home','guide','field_16') }}</p></div><i class="bi bi-person-check" aria-hidden="true"></i></li>
                    <li><span class="comfort-guide-number">০৩</span><div><h3>{{ $siteContent->text('home','guide','field_17') }}</h3><p>{{ $siteContent->text('home','guide','field_18') }}</p></div><i class="bi bi-shield-check" aria-hidden="true"></i></li>
                </ol>
                <div class="comfort-guide-actions">
                    <a class="comfort-guide-button" href="{{ route('shop', ['category' => 'office']) }}">{{ $siteContent->text('home','guide','field_19') }} <span><i class="bi bi-arrow-up-right" aria-hidden="true"></i></span></a>
                    <span class="comfort-guide-footnote">{{ $siteContent->text('home','guide','field_20') }}<br>{{ $siteContent->text('home','guide','field_21') }}</span>
                </div>
            </div>
        </div>
    </div>
</section>
@php
$testimonials = [
    [$siteContent->text('home','review_1','field_1'), $siteContent->text('home','review_1','field_2'), $siteContent->text('home','review_1','field_3'), $siteContent->text('home','review_1','field_4')],
    [$siteContent->text('home','review_2','field_1'), $siteContent->text('home','review_2','field_2'), $siteContent->text('home','review_2','field_3'), $siteContent->text('home','review_2','field_4')],
    [$siteContent->text('home','review_3','field_1'), $siteContent->text('home','review_3','field_2'), $siteContent->text('home','review_3','field_3'), $siteContent->text('home','review_3','field_4')],
    [$siteContent->text('home','review_4','field_1'), $siteContent->text('home','review_4','field_2'), $siteContent->text('home','review_4','field_3'), $siteContent->text('home','review_4','field_4')],
    [$siteContent->text('home','review_5','field_1'), $siteContent->text('home','review_5','field_2'), $siteContent->text('home','review_5','field_3'), $siteContent->text('home','review_5','field_4')],
    [$siteContent->text('home','review_6','field_1'), $siteContent->text('home','review_6','field_2'), $siteContent->text('home','review_6','field_3'), $siteContent->text('home','review_6','field_4')],
];
@endphp
<section class="section testimonials">
    <div class="container">
        <div class="testimonial-heading">
            <span class="eyebrow">{{ $siteContent->text('home','testimonials','field_1') }}</span>
            <h2>{{ $siteContent->text('home','testimonials','field_2') }}</h2>
        </div>
    </div>
    <div class="testimonial-slider">
        <div class="testimonial-track">
            @foreach([false, true] as $isDuplicate)
                @foreach($testimonials as $testimonial)
                    <article class="testimonial-card" @if($isDuplicate) aria-hidden="true" @endif>
                        <div class="testimonial-stars" aria-label="৫-এর মধ্যে ৫ তারকা">★★★★★</div>
                        <p>“{{$testimonial[2]}}”</p>
                        <div class="testimonial-customer">
                            <img src="{{$testimonial[3]}}" alt="{{$isDuplicate ? '' : $testimonial[0]}}" loading="lazy">
                            <span><b>{{$testimonial[0]}}</b><small>{{$testimonial[1]}}</small></span>
                        </div>
                    </article>
                @endforeach
            @endforeach
        </div>
    </div>
</section>
<section class="trust"><div class="container"><div class="row g-4">@foreach([['truck', $siteContent->text('home','services','field_1'), $siteContent->text('home','services','field_2')], ['shield-check', $siteContent->text('home','services','field_3'), $siteContent->text('home','services','field_4')], ['arrow-repeat', $siteContent->text('home','services','field_5'), $siteContent->text('home','services','field_6')], ['headset', $siteContent->text('home','services','field_7'), $siteContent->text('home','services','field_8')]] as $x)<div class="col-6 col-lg-3"><div><i class="bi bi-{{$x[0]}}"></i><span><b>{{$x[1]}}</b><small>{{$x[2]}}</small></span></div></div>@endforeach</div></div></section>
@endsection
