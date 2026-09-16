@extends('layouts.app')
@section('title', $siteContent->text('account','heading','page_title'))
@section('content')
@php
    $menu = [
        'profile' => [$siteContent->text('account','navigation','profile'), 'person-circle'],
        'orders' => [$siteContent->text('account','navigation','orders'), 'receipt'],
        'wishlist' => [$siteContent->text('account','navigation','wishlist'), 'heart-fill'],
        'wallet' => [$siteContent->text('account','navigation','wallet'), 'wallet2'],
        'loyalty' => [$siteContent->text('account','navigation','loyalty'), 'award-fill'],
        'inbox' => [$siteContent->text('account','navigation','inbox'), 'envelope'],
        'addresses' => [$siteContent->text('account','navigation','addresses'), 'geo-alt'],
        'support' => [$siteContent->text('account','navigation','support'), 'headset'],
        'referrals' => [$siteContent->text('account','navigation','referrals'), 'share'],
        'coupons' => [$siteContent->text('account','navigation','coupons'), 'ticket-perforated'],
        'track' => [$siteContent->text('account','navigation','track'), 'truck'],
    ];
    $unread = $messages->filter(fn ($message) => ! $account->inbox_read_at || $message['at']->gt($account->inbox_read_at))->count();
@endphp
<section class="customer-dashboard">
    <div class="container">
        <div class="customer-layout">
            <aside class="customer-sidebar">
                <button class="customer-menu-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#customerMenu" aria-expanded="false" aria-controls="customerMenu"><i class="bi bi-list"></i> {{ $menu[$section][0] }} <i class="bi bi-chevron-down"></i></button>
                <nav class="collapse" id="customerMenu" aria-label="Account navigation">
                    @foreach($menu as $key => [$label, $icon])
                        <a href="{{ route('dashboard', ['section' => $key]) }}" class="{{ $section === $key ? 'active' : '' }}" @if($section === $key) aria-current="page" @endif><i class="bi bi-{{ $icon }}"></i><span>{{ $label }}</span>@if($key === 'inbox' && $unread)<small>{{ $unread }}</small>@endif</a>
                    @endforeach
                    @auth
                        <form method="post" action="{{ route('logout') }}">@csrf<button type="submit"><i class="bi bi-box-arrow-right"></i> {{ $siteContent->text('account','navigation','field_1') }}</button></form>
                    @else
                        <a href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right"></i> {{ $siteContent->text('account','navigation','field_2') }}</a>
                    @endauth
                </nav>
            </aside>
            <div class="customer-content">
                <div class="customer-section-heading"><h2>{{ $menu[$section][0] }}</h2>@if($section === 'orders' && ! $selectedOrder)<span>{{ $orders->count() }} {{ $siteContent->text('account','overview','field_1') }}</span>@endif</div>
                @if($errors->any())<div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

                @if($section === 'profile')
                    <form method="post" action="{{ route('customer.profile') }}" class="customer-form">
                        @csrf @method('PUT')
                        <label for="accountName">{{ $siteContent->text('account','profile','field_2') }}</label><input id="accountName" class="form-control" name="name" value="{{ old('name', $account->name) }}" required maxlength="100">
                        <label for="accountPhone">{{ $siteContent->text('account','profile','field_3') }}</label><input id="accountPhone" class="form-control" name="phone" value="{{ old('phone', $account->phone) }}" inputmode="tel">
                        <label for="accountEmail">{{ $siteContent->text('account','profile','field_4') }}</label><input id="accountEmail" class="form-control" name="email" type="email" value="{{ old('email', $account->email) }}" @readonly(auth()->check() && ! auth()->user()->is_admin)>
                        <button class="btn btn-brand" type="submit"><i class="bi bi-check2"></i> {{ $siteContent->text('account','profile','field_5') }}</button>
                    </form>
                    @if(auth()->check() && ! auth()->user()->is_admin)
                        <h3 class="customer-subheading">{{ $siteContent->text('account','profile','field_6') }}</h3>
                        <form method="post" action="{{ route('customer.password') }}" class="customer-form">
                            @csrf @method('PUT')
                            <label for="currentPassword">{{ $siteContent->text('account','profile','field_7') }}</label><input id="currentPassword" class="form-control" name="current_password" type="password" autocomplete="current-password" required>
                            <label for="newPassword">{{ $siteContent->text('account','profile','field_8') }}</label><input id="newPassword" class="form-control" name="password" type="password" autocomplete="new-password" minlength="8" required>
                            <label for="confirmPassword">{{ $siteContent->text('account','profile','field_9') }}</label><input id="confirmPassword" class="form-control" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required>
                            <button class="btn btn-brand" type="submit"><i class="bi bi-lock"></i> {{ $siteContent->text('account','profile','field_10') }}</button>
                        </form>
                    @endif
                @elseif($section === 'orders' && $selectedOrder)
                    @include('partials.customer-order-details')
                @elseif($section === 'orders')
                    <div class="customer-stats">
                        <div><small>{{ $siteContent->text('account','orders','field_11') }}</small><strong>{{ $orders->count() }}</strong></div>
                        <div><small>{{ $siteContent->text('account','orders','field_12') }}</small><strong>{{ $orders->whereNotIn('status', ['ডেলিভারি সম্পন্ন', 'বাতিল'])->count() }}</strong></div>
                        <div><small>{{ $siteContent->text('account','orders','field_13') }}</small><strong>{{ $siteContent->text('account','orders','field_14') }}{{ number_format($orders->where('status', '!=', 'বাতিল')->sum('total')) }}</strong></div>
                    </div>
                    <h3 class="customer-subheading">{{ $siteContent->text('account','orders','field_15') }}</h3>
                    <div class="table-responsive"><table class="table align-middle customer-table"><thead><tr><th>{{ $siteContent->text('account','orders','field_16') }}</th><th>{{ $siteContent->text('account','orders','field_17') }}</th><th>{{ $siteContent->text('account','orders','field_18') }}</th><th>{{ $siteContent->text('account','orders','field_19') }}</th><th></th></tr></thead><tbody>
                    @forelse($orders as $order)
                        <tr><td><b>{{ $order->order_number }}</b><small>{{ $order->name }}</small><small>{{ $order->district }}, {{ $order->area }}</small></td><td>{{ $order->created_at->format('d M, Y') }}</td><td>{{ $siteContent->text('account','orders','field_20') }}{{ number_format($order->total) }}</td><td><span class="customer-status">{{ $order->status }}</span></td><td><a href="{{ route('dashboard', ['section' => 'orders', 'order' => $order->id, 'tab' => 'track']) }}" class="customer-text-link">{{ $siteContent->text('account','orders','field_21') }} <i class="bi bi-arrow-right"></i></a></td></tr>
                    @empty
                        <tr><td colspan="5"><div class="customer-empty"><i class="bi bi-bag"></i><p>{{ $siteContent->text('account','orders','field_22') }}</p><a href="{{ route('shop') }}" class="btn btn-brand">{{ $siteContent->text('account','orders','field_23') }}</a></div></td></tr>
                    @endforelse
                    </tbody></table></div>
                @elseif($section === 'wishlist')
                    <div class="customer-wishlist">
                        @forelse($wishlist as $product)
                            <article>
                                <a href="{{ route('product', $product) }}"><img src="{{ $product->image }}" alt="{{ $product->name }}"><h3>{{ $product->name }}</h3></a><strong>{{ $siteContent->text('account','wishlist','field_24') }}{{ number_format($product->current_price) }}</strong>
                                <div class="customer-item-actions"><form method="post" action="{{ route('cart.add', $product) }}">@csrf<button class="btn btn-brand" @disabled($product->stock < 1)><i class="bi bi-bag-plus"></i> {{ $product->stock < 1 ? 'স্টক নেই' : 'কার্টে যোগ করুন' }}</button></form><form method="post" action="{{ route('customer.wishlist.remove', $product) }}">@csrf @method('DELETE')<button class="customer-icon-button" title="সরান" aria-label="উইশলিস্ট থেকে সরান"><i class="bi bi-trash"></i></button></form></div>
                            </article>
                        @empty
                            <div class="customer-empty"><i class="bi bi-heart"></i><p>{{ $siteContent->text('account','wishlist','field_25') }}</p><a href="{{ route('shop') }}" class="btn btn-brand">{{ $siteContent->text('account','wishlist','field_26') }}</a></div>
                        @endforelse
                    </div>
                @elseif(in_array($section, ['wallet', 'loyalty']))
                    @php($entries = collect($section === 'wallet' ? $account->wallet_entries : $account->loyalty_entries))
                    <div class="customer-balance"><i class="bi bi-{{ $menu[$section][1] }}"></i><div><small>{{ $section === 'wallet' ? 'বর্তমান ব্যালেন্স' : 'মোট পয়েন্ট' }}</small><strong>{{ $section === 'wallet' ? '৳' : '' }}{{ number_format($entries->sum('amount'), $section === 'wallet' ? 2 : 0) }}</strong></div></div>
                    <h3 class="customer-subheading">{{ $siteContent->text('account','wallet','field_27') }}</h3>
                    <div class="table-responsive"><table class="table customer-table"><thead><tr><th>{{ $siteContent->text('account','wallet','field_28') }}</th><th>{{ $siteContent->text('account','wallet','field_29') }}</th><th>{{ $section === 'wallet' ? 'টাকা' : 'পয়েন্ট' }}</th></tr></thead><tbody>@forelse($entries->reverse() as $entry)<tr><td>{{ $entry['date'] }}</td><td>{{ $entry['description'] }}</td><td>{{ number_format($entry['amount'], $section === 'wallet' ? 2 : 0) }}</td></tr>@empty<tr><td colspan="3" class="py-4 text-center text-muted">{{ $siteContent->text('account','wallet','field_30') }}</td></tr>@endforelse</tbody></table></div>
                @elseif($section === 'inbox')
                    @if($unread)<form method="post" action="{{ route('customer.inbox.read') }}">@csrf<button class="btn btn-outline-dark btn-sm"><i class="bi bi-check2-all"></i> {{ $siteContent->text('account','inbox','field_31') }}</button></form>@endif
                    @forelse($messages as $message)
                        <a class="customer-message {{ ! $account->inbox_read_at || $message['at']->gt($account->inbox_read_at) ? 'unread' : '' }}" href="{{ $message['url'] }}"><i class="bi bi-envelope"></i><div><b>{{ $message['title'] }}</b><p>{{ $message['body'] }}</p><small>{{ $message['at']->format('d M Y, h:i A') }}</small></div><i class="bi bi-chevron-right"></i></a>
                    @empty
                        <div class="customer-empty"><i class="bi bi-envelope-open"></i><p>{{ $siteContent->text('account','inbox','field_32') }}</p></div>
                    @endforelse
                @elseif($section === 'addresses')
                    @foreach($account->addresses ?? [] as $address)
                        <article class="customer-address"><div><b>{{ $address['label'] }}</b><p>{{ $address['name'] }} · {{ $address['phone'] }}</p><p>{{ $address['address'] }}, {{ $address['area'] }}, {{ $address['district'] }}</p></div><div class="customer-item-actions"><a class="customer-icon-button" href="{{ route('dashboard', ['section' => 'addresses', 'edit' => $address['id']]) }}#addressForm" title="সম্পাদনা" aria-label="ঠিকানা সম্পাদনা"><i class="bi bi-pencil"></i></a><form method="post" action="{{ route('customer.addresses.remove', $address['id']) }}">@csrf @method('DELETE')<button class="customer-icon-button" title="মুছুন" aria-label="ঠিকানা মুছুন"><i class="bi bi-trash"></i></button></form></div></article>
                    @endforeach
                    @php($editingAddress = collect($account->addresses ?? [])->firstWhere('id', request('edit')) ?? [])
                    <h3 class="customer-subheading">{{ $editingAddress ? 'ঠিকানা সম্পাদনা' : 'নতুন ঠিকানা' }}</h3>
                    <form id="addressForm" class="customer-form" method="post" action="{{ route('customer.addresses.save') }}">
                        @csrf<input type="hidden" name="id" value="{{ old('id', $editingAddress['id'] ?? '') }}">
                        @foreach(['label' => 'ঠিকানার নাম (বাসা / অফিস)', 'name' => 'নাম', 'phone' => 'মোবাইল নম্বর', 'district' => 'জেলা', 'area' => 'থানা / এলাকা', 'address' => 'সম্পূর্ণ ঠিকানা'] as $field => $label)
                            <label for="address-{{ $field }}">{{ $label }}</label><input class="form-control" id="address-{{ $field }}" name="{{ $field }}" value="{{ old($field, $editingAddress[$field] ?? '') }}" required>
                        @endforeach
                        <button class="btn btn-brand"><i class="bi bi-check2"></i> {{ $siteContent->text('account','addresses','field_33') }}</button>
                    </form>
                @elseif($section === 'support')
                    <form method="post" action="{{ route('customer.tickets.store') }}" class="customer-form">
                        @csrf<label for="ticketSubject">{{ $siteContent->text('account','support','field_34') }}</label><input class="form-control" id="ticketSubject" name="subject" value="{{ old('subject') }}" maxlength="160" required>
                        <label for="ticketMessage">{{ $siteContent->text('account','support','field_35') }}</label><textarea class="form-control" id="ticketMessage" name="message" rows="3" maxlength="5000" required>{{ old('message') }}</textarea>
                        <button class="btn btn-brand"><i class="bi bi-send"></i> {{ $siteContent->text('account','support','field_36') }}</button>
                    </form>
                    <h3 class="customer-subheading">{{ $siteContent->text('account','support','field_37') }}</h3>
                    @forelse($tickets as $ticket)
                        <details class="customer-ticket" id="ticket-{{ $ticket->id }}"><summary><span>#{{ $ticket->id }} · {{ $ticket->subject }}</span><b class="customer-status">{{ $ticket->status }}</b></summary>
                        @foreach($ticket->messages as $message)<div class="customer-ticket-message"><small>{{ $message['author'] === 'support' ? 'ChairGhor Support' : 'আপনি' }} · {{ \Illuminate\Support\Carbon::parse($message['at'])->format('d M, h:i A') }}</small><p>{{ $message['body'] }}</p></div>@endforeach
                        <form class="customer-form" method="post" action="{{ route('customer.tickets.reply', $ticket->id) }}">@csrf<label for="reply-{{ $ticket->id }}">{{ $siteContent->text('account','support','field_38') }}</label><textarea class="form-control" id="reply-{{ $ticket->id }}" name="message" rows="2" maxlength="5000" required></textarea><button class="btn btn-brand"><i class="bi bi-send"></i> {{ $siteContent->text('account','support','field_39') }}</button></form></details>
                    @empty
                        <p class="text-muted">{{ $siteContent->text('account','support','field_40') }}</p>
                    @endforelse
                @elseif($section === 'referrals')
                    <div class="customer-stats"><div><small>{{ $siteContent->text('account','referrals','field_41') }}</small><strong>{{ $account->referral_visits }}</strong></div><div><small>{{ $siteContent->text('account','referrals','field_42') }}</small><strong>{{ $referralOrders }}</strong></div></div>
                    <h3 class="customer-subheading">{{ $siteContent->text('account','referrals','field_43') }}</h3><div class="customer-copy"><input class="form-control" aria-label="রেফারেল লিংক" readonly value="{{ route('customer.referral', $account->referral_code) }}"><button type="button" class="customer-icon-button" data-account-copy="{{ route('customer.referral', $account->referral_code) }}" title="লিংক কপি করুন" aria-label="লিংক কপি করুন"><i class="bi bi-copy"></i></button></div><span data-copy-status role="status"></span>
                @elseif($section === 'coupons')
                    @forelse($coupons as $coupon)
                        <article class="customer-coupon"><i class="bi bi-ticket-perforated"></i><div><h3>{{ $coupon->code }}</h3><p>{{ $coupon->discount_type === 'percent' ? $coupon->value.'%' : '৳'.number_format($coupon->value) }} {{ $siteContent->text('account','coupons','field_44') }}{{ number_format($coupon->minimum_order) }}</p><small>{{ $coupon->expires_at ? $coupon->expires_at->format('d M Y').' পর্যন্ত' : 'মেয়াদ সীমাহীন' }}</small></div><button type="button" class="customer-icon-button" data-account-copy="{{ $coupon->code }}" title="কুপন কপি করুন" aria-label="কুপন কপি করুন"><i class="bi bi-copy"></i></button></article>
                    @empty
                        <div class="customer-empty"><i class="bi bi-ticket-perforated"></i><p>{{ $siteContent->text('account','coupons','field_45') }}</p></div>
                    @endforelse
                    <span data-copy-status role="status"></span>
                @elseif($section === 'track')
                    @if($orders->isNotEmpty())
                        <form method="get" action="{{ route('dashboard') }}" class="customer-form">
                            <input type="hidden" name="section" value="orders"><input type="hidden" name="tab" value="track">
                            <label for="trackOrder">{{ $siteContent->text('account','track','field_46') }}</label>
                            <select class="form-select" id="trackOrder" name="order" required>@foreach($orders as $order)<option value="{{ $order->id }}">{{ $order->order_number }} — {{ $order->status }}</option>@endforeach</select>
                            <button class="btn btn-brand"><i class="bi bi-search"></i> {{ $siteContent->text('account','track','field_48') }}</button>
                        </form>
                    @else<div class="customer-empty"><i class="bi bi-bag"></i><p>{{ $siteContent->text('account','orders','field_22') }}</p></div>@endif
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
