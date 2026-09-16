<div class="page-hero"><div class="container"><span class="eyebrow">{{ $siteContent->text('about','story','eyebrow') }}</span><h1>{{ $siteContent->text('about','content','title') }}</h1><p>{{ $siteContent->text('about','story','intro') }}</p></div></div>
<section class="about-intro">
    <div class="container">
        <nav class="about-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><i class="bi bi-chevron-right" aria-hidden="true"></i><span>About Us</span></nav>
        <div class="about-intro-grid">
            <div class="about-intro-copy">
                <span class="eyebrow">{{ $siteContent->text('about','story','eyebrow') }}</span>
                <h2 class="about-main-heading">{{ $siteContent->text('about','story','headline') }}</h2>
                <p>{{ $siteContent->text('about','story','intro') }}</p>
                <a class="btn btn-brand" href="{{ route('shop') }}">{{ $siteContent->text('about','story','button') }} <i class="bi bi-arrow-up-right"></i></a>
                <div class="about-story"><span class="about-story-line"></span><div><h2>{{ $siteContent->text('about','content','title') }}</h2><p>{{ $siteContent->text('about','content','body') }}</p></div></div>
            </div>
            <figure class="about-photo"><img src="{{ $siteContent->text('about','story','image') }}" alt="{{ $siteContent->text('about','story','image_alt') }}" width="1200" height="1400"><figcaption><i class="bi bi-heart" aria-hidden="true"></i>{{ $siteContent->text('about','story','caption') }}</figcaption></figure>
        </div>
    </div>
</section>
<section class="about-values">
    <div class="container">
        <div class="about-values-heading"><span class="eyebrow">{{ $siteContent->text('about','values','eyebrow') }}</span><h2>{{ $siteContent->text('about','values','title') }}</h2></div>
        <div class="about-value-grid">
            @foreach(['comfort' => 'heart', 'design' => 'palette', 'care' => 'chat-heart'] as $value => $icon)
                <article><div class="about-value-top"><i class="bi bi-{{ $icon }}" aria-hidden="true"></i><span>0{{ $loop->iteration }}</span></div><h3>{{ $siteContent->text('about','values',$value.'_title') }}</h3><p>{{ $siteContent->text('about','values',$value.'_body') }}</p></article>
            @endforeach
        </div>
    </div>
</section>
<section class="about-contact">
    <div class="container"><div class="about-contact-card"><div><span class="eyebrow">CHAIRGHOR / HERE TO HELP</span><h2>{{ $siteContent->text('about','contact','title') }}</h2><p>{{ $siteContent->text('about','content','help') }}</p></div><div class="about-contact-actions"><a class="btn btn-brand" href="{{ route('contact') }}">{{ $siteContent->text('about','contact','button') }} <i class="bi bi-arrow-up-right"></i></a><a href="{{ route('shop') }}">{{ $siteContent->text('about','contact','shop_button') }} <i class="bi bi-arrow-right"></i></a></div></div></div>
</section>
