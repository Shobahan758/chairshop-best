@if($trackingSettings?->tracking_enabled)
@if($trackingSettings->google_tag_id)<script async src="https://www.googletagmanager.com/gtag/js?id={{$trackingSettings->google_tag_id}}"></script><script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config',@json($trackingSettings->google_tag_id));</script>@endif
@if($trackingSettings->meta_pixel_id)
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=true;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=true;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
fbq('init', @json($trackingSettings->meta_pixel_id));
fbq('track', 'PageView');
@if(request()->routeIs('product') && isset($product))
@php($pixelProduct = ['content_ids' => [(string) $product->id], 'content_type' => 'product', 'value' => (float) $product->current_price, 'currency' => 'BDT'])
fbq('track', 'ViewContent', @json($pixelProduct));
@endif
@if(request()->routeIs('checkout') && isset($cart, $subtotal, $discount))
@php($pixelCart = ['content_ids' => array_map('strval', array_keys($cart)), 'content_type' => 'product', 'num_items' => array_sum(array_column($cart, 'quantity')), 'value' => (float) ($subtotal - $discount), 'currency' => 'BDT'])
fbq('track', 'InitiateCheckout', @json($pixelCart));
@endif
@foreach(session()->pull('meta_pixel_events', []) as $pixelEvent)
fbq('track', @json($pixelEvent['name']), @json($pixelEvent['parameters']), @json(['eventID' => $pixelEvent['id']]));
@endforeach
</script>
@endif
@if($trackingSettings->tiktok_pixel_id)<script>!function(w,d,t){w.TiktokAnalyticsObject=t;var q=w[t]=w[t]||[];q.load=function(e){var s=d.createElement('script');s.async=true;s.src='https://analytics.tiktok.com/i18n/pixel/events.js?sdkid='+e+'&lib='+t;d.head.appendChild(s)};q.load(@json($trackingSettings->tiktok_pixel_id));q.push(['page'])}(window,document,'ttq');</script>@endif
@if($trackingSettings->pinterest_tag_id)<script>!function(e){if(!window.pintrk){window.pintrk=function(){window.pintrk.queue.push(Array.prototype.slice.call(arguments))};var n=window.pintrk;n.queue=[];n.version='3.0';var t=document.createElement('script');t.async=true;t.src=e;document.head.appendChild(t)}}('https://s.pinimg.com/ct/core.js');pintrk('load',@json($trackingSettings->pinterest_tag_id));pintrk('page');</script>@endif
@if($trackingSettings->snapchat_pixel_id)<script>!function(e,t,n){if(e.snaptr)return;var a=e.snaptr=function(){a.handleRequest?a.handleRequest.apply(a,arguments):a.queue.push(arguments)};a.queue=[];var s='script',r=t.createElement(s);r.async=true;r.src=n;var u=t.getElementsByTagName(s)[0];u.parentNode.insertBefore(r,u)}(window,document,'https://sc-static.net/scevent.min.js');snaptr('init',@json($trackingSettings->snapchat_pixel_id));snaptr('track','PAGE_VIEW');</script>@endif
@endif
