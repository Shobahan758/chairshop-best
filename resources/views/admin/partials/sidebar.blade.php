<div class="admin-brand"><span>Chair</span>Ghor<small>ADMIN CONSOLE</small></div>
<nav class="admin-nav">
    <small class="nav-label">OVERVIEW</small>
    <a class="{{request()->routeIs('admin') ? 'active' : ''}}" href="{{route('admin')}}"><i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span></a>

    <small class="nav-label">MANAGEMENT</small>
    @if(auth()->user()->canAccessAdminRoute('admin.support.index'))
    @php($messagesRouteIsActive = request()->routeIs('admin.support.*'))
    <button class="admin-nav-parent {{ $messagesRouteIsActive ? 'active' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#messagesMenu" aria-controls="messagesMenu" aria-expanded="{{ $messagesRouteIsActive ? 'true' : 'false' }}"><i class="bi bi-chat-left-text"></i><span>Message</span><i class="bi bi-chevron-down ms-auto"></i></button>
    <div class="collapse {{ $messagesRouteIsActive ? 'show' : '' }} admin-subnav" id="messagesMenu">
        <a class="{{ request()->routeIs('admin.support.today') ? 'active text-white fw-semibold' : '' }}" href="{{ route('admin.support.today') }}">Today SMS</a>
        <a class="{{ request()->routeIs('admin.support.index') ? 'active text-white fw-semibold' : '' }}" href="{{ route('admin.support.index') }}">All SMS</a>
    </div>
    @endif

    @if(auth()->user()->canAccessAdminRoute('admin.categories.index'))
    @php($catalogRouteIsActive = request()->routeIs('admin.categories.*', 'admin.subcategories.*', 'admin.brands.*'))
    <button class="admin-nav-parent {{$catalogRouteIsActive ? 'active' : ''}}" data-bs-toggle="collapse" data-bs-target="#categoryMenu" aria-expanded="{{$catalogRouteIsActive ? 'true' : 'false'}}"><i class="bi bi-tags"></i><span>Categories</span><i class="bi bi-chevron-down ms-auto"></i></button>
    <div class="collapse {{$catalogRouteIsActive ? 'show' : ''}} admin-subnav" id="categoryMenu"><a href="{{route('admin.categories.index')}}">All Categories</a><a href="{{route('admin.subcategories.index')}}">Sub Categories</a><a href="{{route('admin.brands.index')}}">Brands</a><a href="{{route('admin.categories.index')}}">Sort Order</a></div>

    @endif

    @if(auth()->user()->canAccessAdminRoute('admin.products.index'))
    <button class="admin-nav-parent {{request()->routeIs('admin.products.*') ? 'active' : ''}}" data-bs-toggle="collapse" data-bs-target="#productMenu" aria-expanded="{{request()->routeIs('admin.products.*') ? 'true' : 'false'}}"><i class="bi bi-box-seam"></i><span>Products</span><i class="bi bi-chevron-down ms-auto"></i></button>
    <div class="collapse {{request()->routeIs('admin.products.*') ? 'show' : ''}} admin-subnav" id="productMenu"><a href="{{route('admin.products.index')}}">All Products</a><a href="{{route('admin.products.create')}}">Add Product</a><a href="{{route('admin.products.low-stock')}}">Low Stock</a><a href="{{route('admin.products.sale')}}">Sale Products</a></div>

    @endif

    @if(auth()->user()->canAccessAdminRoute('admin.orders.index'))
    @php($ordersRouteIsActive = request()->routeIs('admin.orders.*'))
    <button class="admin-nav-parent {{$ordersRouteIsActive ? 'active' : ''}}" data-bs-toggle="collapse" data-bs-target="#ordersMenu" aria-expanded="true"><i class="bi bi-receipt-cutoff"></i><span>Orders</span><i class="bi bi-chevron-down ms-auto"></i></button>
    <div class="collapse show admin-subnav" id="ordersMenu">
        @foreach(['' => 'All Orders', 'new' => 'New Orders', 'processing' => 'Processing', 'shipping' => 'Shipping', 'completed' => 'Completed Orders', 'cancelled' => 'Cancelled Orders'] as $orderFilter => $orderLabel)
            <a class="{{$ordersRouteIsActive && (request()->route('filter') ?? '') === $orderFilter ? 'active text-white fw-semibold' : ''}}" href="{{route('admin.orders.index', $orderFilter ? ['filter' => $orderFilter] : [])}}">{{$orderLabel}}</a>
        @endforeach
    </div>

    @endif

    @if(auth()->user()->canAccessAdminRoute('admin.incomplete-orders.index'))
    @php($incompleteRouteIsActive = request()->routeIs('admin.incomplete-orders.*'))
    <button class="admin-nav-parent {{$incompleteRouteIsActive ? 'active' : ''}}" data-bs-toggle="collapse" data-bs-target="#incompleteOrdersMenu" aria-expanded="{{$incompleteRouteIsActive ? 'true' : 'false'}}"><i class="bi bi-clipboard2-x"></i><span>Incomplete Orders</span><i class="bi bi-chevron-down ms-auto"></i></button>
    <div class="collapse {{$incompleteRouteIsActive ? 'show' : ''}} admin-subnav" id="incompleteOrdersMenu"><a class="{{request()->routeIs('admin.incomplete-orders.index') ? 'active' : ''}}" href="{{route('admin.incomplete-orders.index')}}">All Incomplete Orders</a><a class="{{request()->routeIs('admin.incomplete-orders.today') ? 'active' : ''}}" href="{{route('admin.incomplete-orders.today')}}">Today's Incomplete Orders</a></div>

    @endif

    @if(auth()->user()->canAccessAdminRoute('admin.fake-orders.index'))
    @php($fakeRouteIsActive = request()->routeIs('admin.fake-orders.*'))
    <button class="admin-nav-parent {{$fakeRouteIsActive ? 'active' : ''}}" data-bs-toggle="collapse" data-bs-target="#fakeOrdersMenu" aria-expanded="{{$fakeRouteIsActive ? 'true' : 'false'}}"><i class="bi bi-person-x"></i><span>Fake Orders</span><i class="bi bi-chevron-down ms-auto"></i></button>
    <div class="collapse {{$fakeRouteIsActive ? 'show' : ''}} admin-subnav" id="fakeOrdersMenu"><a class="{{request()->routeIs('admin.fake-orders.index') ? 'active' : ''}}" href="{{route('admin.fake-orders.index')}}">All Fake Orders</a><a class="{{request()->routeIs('admin.fake-orders.today') ? 'active' : ''}}" href="{{route('admin.fake-orders.today')}}">Today's Fake Orders</a></div>

    @endif

    @if(auth()->user()->canAccessAdminRoute('admin.tracking'))
    @php($trackingRouteIsActive = request()->routeIs('admin.tracking*'))
    <button class="admin-nav-parent {{$trackingRouteIsActive ? 'active' : ''}}" data-bs-toggle="collapse" data-bs-target="#trackingMenu" aria-expanded="{{$trackingRouteIsActive ? 'true' : 'false'}}"><i class="bi bi-graph-up-arrow"></i><span>Tracking</span><i class="bi bi-chevron-down ms-auto"></i></button>
    <div class="collapse {{$trackingRouteIsActive ? 'show' : ''}} admin-subnav tracking-subnav" id="trackingMenu">
        <a class="{{request()->routeIs('admin.tracking') ? 'active' : ''}}" href="{{route('admin.tracking')}}"><i class="bi bi-broadcast-pin"></i> Site Tracking</a>
        <a class="{{request()->route('integration') === 'meta' ? 'active' : ''}}" href="{{route('admin.tracking.integration', 'meta')}}"><i class="bi bi-meta"></i> Meta Pixel</a>
        <a class="{{request()->route('integration') === 'google' ? 'active' : ''}}" href="{{route('admin.tracking.integration', 'google')}}"><i class="bi bi-google"></i> Google</a>
        <a class="{{request()->route('integration') === 'tiktok' ? 'active' : ''}}" href="{{route('admin.tracking.integration', 'tiktok')}}"><i class="bi bi-tiktok"></i> TikTok Pixel</a>
        <a class="{{request()->route('integration') === 'other' ? 'active' : ''}}" href="{{route('admin.tracking.integration', 'other')}}"><i class="bi bi-bezier2"></i> Other Tracking</a>
    </div>

    @endif

    @php($siteRouteIsActive = request()->routeIs('admin.settings.site*'))
    @if(auth()->user()->canAccessAdminRoute('admin.settings.site'))
    <button class="admin-nav-parent {{ $siteRouteIsActive ? 'active' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#siteMenu" aria-controls="siteMenu" aria-expanded="{{ $siteRouteIsActive ? 'true' : 'false' }}"><i class="bi bi-window-stack"></i><span>Site Settings</span><i class="bi bi-chevron-down ms-auto"></i></button>
    <div class="collapse {{ $siteRouteIsActive ? 'show' : '' }} admin-subnav" id="siteMenu">
        <a href="{{ route('admin.settings.site') }}">All Pages & Sections</a>
        @foreach([
            ['home', 'banner_1', 'Hero Banner'],
            ['home', 'guide', 'Chair Guide'],
            ['home', 'review_1', 'Customer Reviews'],
            ['home', 'services', 'Service Benefits'],
            ['product', 'assurances', 'Product Benefits'],
            ['shared', 'header', 'Header'],
            ['shared', 'footer', 'Footer'],
        ] as [$sitePage, $siteSection, $siteLabel])
            <a class="{{ $siteRouteIsActive && request('page') === $sitePage && request('section', request('saved')) === $siteSection ? 'active text-white fw-semibold' : '' }}" href="{{ route('admin.settings.site', ['page' => $sitePage, 'section' => $siteSection]).'#section-'.$siteSection }}">{{ $siteLabel }}</a>
        @endforeach
        @if(auth()->user()->canAccessAdminRoute('admin.products.index'))
        <a href="{{ route('admin.products.index') }}">Featured Products</a>
        @endif
    </div>

    @endif

    <small class="nav-label">SYSTEM</small>
    @php($settingsRouteIsActive = request()->routeIs('admin.settings.*') && ! $siteRouteIsActive)
    <button class="admin-nav-parent {{$settingsRouteIsActive ? 'active' : ''}}" data-bs-toggle="collapse" data-bs-target="#settingsMenu" aria-expanded="{{$settingsRouteIsActive ? 'true' : 'false'}}"><i class="bi bi-gear"></i><span>Settings</span><i class="bi bi-chevron-down ms-auto"></i></button>
    <div class="collapse {{$settingsRouteIsActive ? 'show' : ''}} admin-subnav settings-subnav" id="settingsMenu">
        @if(auth()->user()->canAccessAdminRoute('admin.settings.general'))
        <a class="{{request()->routeIs('admin.settings.general*') ? 'active' : ''}}" href="{{route('admin.settings.general')}}"><i class="bi bi-sliders2"></i> General Settings</a>
        @endif
        @if(auth()->user()->canAccessAdminRoute('admin.settings.users'))
        <a class="{{request()->routeIs('admin.settings.users*') ? 'active' : ''}}" href="{{route('admin.settings.users')}}"><i class="bi bi-people"></i> Role</a>
        @endif
        <a class="{{request()->routeIs('admin.settings.profile') || request()->routeIs('admin.settings.password.update') ? 'active' : ''}}" href="{{route('admin.settings.profile')}}"><i class="bi bi-person"></i> Profile</a>
    </div>
</nav>
<form class="admin-logout" method="post" action="{{route('logout')}}">@csrf<button type="submit"><i class="bi bi-box-arrow-left"></i><span>Log Out</span></button></form>
