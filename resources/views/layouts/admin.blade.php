<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{csrf_token()}}">
    <title>@yield('title', 'Admin — ChairGhor')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    @vite(['resources/css/admin.css', 'resources/css/tracking.css', 'resources/js/admin.js'])
</head>
<body class="admin-body">
    <aside class="admin-sidebar d-none d-xl-flex">
        @include('admin.partials.sidebar')
    </aside>

    <div class="offcanvas offcanvas-start admin-offcanvas" tabindex="-1" id="adminSidebar" aria-labelledby="adminSidebarLabel">
        <div class="offcanvas-header"><span id="adminSidebarLabel" class="visually-hidden">Admin menu</span><button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="offcanvas" aria-label="Close"></button></div>
        <div class="offcanvas-body p-0 d-flex flex-column">@include('admin.partials.sidebar')</div>
    </div>

    <main class="admin-main">
        <header class="admin-topbar">
            <button class="btn admin-menu-button d-xl-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar"><i class="bi bi-list"></i></button>
            <div class="admin-search d-none d-md-flex"><i class="bi bi-search"></i><input type="search" placeholder="Search orders, products or customers"></div>
            <div class="ms-auto d-flex align-items-center gap-3">
                <a class="admin-top-icon" href="{{route('home')}}" target="_blank" aria-label="View store" title="View store"><i class="bi bi-box-arrow-up-right"></i></a>
                <button class="admin-top-icon" type="button" aria-label="Notifications"><i class="bi bi-bell"></i><span></span></button>
                <div class="dropdown admin-profile-dropdown">
                    <button class="admin-profile" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="admin-avatar">@if (auth()->user()->avatar_path)<img src="{{asset('storage/'.auth()->user()->avatar_path)}}" alt="{{auth()->user()->name}}">@else{{mb_substr(auth()->user()->name, 0, 1)}}@endif</div>
                        <span><b>{{auth()->user()->name}}</b><small>{{ auth()->user()->roleLabel() }}</small></span>
                        <i class="bi bi-caret-down-fill admin-profile-caret"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end admin-profile-menu">
                        <div class="admin-profile-menu-head"><b>{{auth()->user()->name}}</b><small>{{auth()->user()->email}}</small></div>
                        <div class="admin-profile-menu-links">
                            <a href="{{route('admin.settings.profile')}}#account-details"><i class="bi bi-person-circle"></i> Edit profile</a>
                            <a href="{{route('admin.settings.profile')}}#profileEmail"><i class="bi bi-envelope"></i> Change email</a>
                            <a href="{{route('admin.settings.profile')}}#change-password"><i class="bi bi-key"></i> Change password</a>
                        </div>
                        <form method="post" action="{{route('logout')}}">@csrf<button type="submit"><i class="bi bi-box-arrow-right"></i> Logout</button></form>
                    </div>
                </div>
            </div>
        </header>

        <div class="admin-content">
            @if(session('success'))<div class="alert alert-success border-0 shadow-sm">{{session('success')}}</div>@endif
            @if(session('error'))<div class="alert alert-danger border-0 shadow-sm">{{session('error')}}</div>@endif
            @yield('content')
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
