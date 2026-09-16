@extends('layouts.admin')
@php($pageTitle = match ($section) {'general' => 'General Settings', 'users' => 'Role', 'profile' => 'Profile'})
@section('title', $pageTitle.' — ChairGhor')

@section('content')
<div class="settings-page-heading">
    <h1>{{ $pageTitle }}</h1>
    @if ($section === 'general')<a class="btn btn-admin-light" href="{{ route('admin.settings.site') }}">Site Settings — পেজের কনটেন্ট পরিবর্তন</a>@endif
    @if ($section === 'general')<p>Manage your store identity, contact information and regional preferences.</p>@endif
    @if ($section === 'profile')<p>Manage your account information and security.</p>@endif
</div>

@if ($section === 'general')
    <section class="settings-card">
        <div class="settings-card-head"><i class="bi bi-sliders2"></i><h2>Store configuration</h2></div>
        <form method="post" action="{{ route('admin.settings.general.update') }}">
            @csrf
            @method('PUT')
            <div class="settings-card-body row g-4">
                <div class="col-md-6"><label class="form-label" for="storeName">Store name</label><input class="form-control @error('store_name') is-invalid @enderror" id="storeName" name="store_name" value="{{ old('store_name', $settings->store_name) }}" required>@error('store_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-6"><label class="form-label" for="supportEmail">Support email</label><input class="form-control @error('support_email') is-invalid @enderror" id="supportEmail" name="support_email" type="email" value="{{ old('support_email', $settings->support_email) }}">@error('support_email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-6"><label class="form-label" for="supportPhone">Support phone</label><input class="form-control @error('support_phone') is-invalid @enderror" id="supportPhone" name="support_phone" value="{{ old('support_phone', $settings->support_phone) }}">@error('support_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-6"><label class="form-label" for="bkashNumber">bKash payment number</label><input class="form-control @error('bkash_number') is-invalid @enderror" id="bkashNumber" name="bkash_number" value="{{ old('bkash_number', $settings->bkash_number) }}" placeholder="01XXXXXXXXX">@error('bkash_number')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-6"><label class="form-label" for="nagadNumber">Nagad payment number</label><input class="form-control @error('nagad_number') is-invalid @enderror" id="nagadNumber" name="nagad_number" value="{{ old('nagad_number', $settings->nagad_number) }}" placeholder="01XXXXXXXXX">@error('nagad_number')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-6"><label class="form-label" for="currencyCode">Currency code</label><input class="form-control @error('currency_code') is-invalid @enderror" id="currencyCode" name="currency_code" value="{{ old('currency_code', $settings->currency_code) }}" maxlength="3" required>@error('currency_code')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-6"><label class="form-label" for="currencySymbol">Currency symbol</label><input class="form-control @error('currency_symbol') is-invalid @enderror" id="currencySymbol" name="currency_symbol" value="{{ old('currency_symbol', $settings->currency_symbol) }}" required>@error('currency_symbol')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-6"><label class="form-label" for="timezone">Timezone</label><select class="form-select @error('timezone') is-invalid @enderror" id="timezone" name="timezone" required>@foreach (['Asia/Dhaka', 'UTC', 'Asia/Kolkata', 'Asia/Dubai', 'Europe/London', 'America/New_York'] as $timezone)<option value="{{ $timezone }}" @selected(old('timezone', $settings->timezone) === $timezone)>{{ $timezone }}</option>@endforeach</select>@error('timezone')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-12"><label class="form-label" for="businessAddress">Business address</label><textarea class="form-control @error('business_address') is-invalid @enderror" id="businessAddress" name="business_address" rows="3">{{ old('business_address', $settings->business_address) }}</textarea>@error('business_address')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-12"><label class="form-label" for="maintenanceMessage">Maintenance message</label><textarea class="form-control @error('maintenance_message') is-invalid @enderror" id="maintenanceMessage" name="maintenance_message" rows="3">{{ old('maintenance_message', $settings->maintenance_message) }}</textarea>@error('maintenance_message')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            </div>
            <div class="settings-card-footer"><button class="btn btn-admin-primary" type="submit"><i class="bi bi-check2-circle"></i> Save settings</button></div>
        </form>
    </section>
@elseif ($section === 'users')
    <a class="btn btn-admin-primary mb-3" href="{{ route('admin.settings.users.create') }}"><i class="bi bi-person-plus"></i> Add user</a>
    <form class="settings-user-search" method="get" action="{{ route('admin.settings.users') }}"><i class="bi bi-search"></i><input name="search" value="{{ $search }}" placeholder="Search user" aria-label="Search user"></form>
    <section class="settings-card settings-users-card">
        <div class="table-responsive">
            <table class="table settings-users-table mb-0">
                <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Action</th></tr></thead>
                <tbody>@forelse ($users as $listedUser)<tr><td>{{ $listedUser->name }}</td><td>{{ $listedUser->email }}</td><td><span class="settings-role {{ $listedUser->is_admin ? 'admin' : '' }}">{{ $listedUser->roleLabel() }}</span></td><td>{{ $listedUser->created_at->format('d M Y') }}</td><td>@if($listedUser->is_admin)<a class="btn btn-admin-light btn-sm" href="{{ route('admin.settings.users.edit', $listedUser) }}">Edit</a>@endif
                    @if($listedUser->role !== 'super_admin')
                        <form class="d-inline-block" method="post" action="{{ route('admin.settings.users.destroy', $listedUser) }}" onsubmit="return confirm('Delete this user? This action cannot be undone.')">
                            @csrf @method('DELETE')
                            <button class="btn btn-outline-danger btn-sm" type="submit" aria-label="Delete {{ $listedUser->name }}">Delete</button>
                        </form>
                    @endif
                </td></tr>@empty<tr><td class="text-center text-muted py-5" colspan="5">No users found.</td></tr>@endforelse</tbody>
            </table>
        </div>
        <div class="settings-users-footer"><span>Showing {{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }} of {{ $users->total() }}</span>{{ $users->links('pagination::bootstrap-5') }}</div>
    </section>
@else
    <div class="row g-4">
        <div class="col-xl-6">
            <section class="settings-card settings-profile-card" id="account-details">
                <div class="settings-card-body"><h2>Account details</h2><form method="post" action="{{ route('admin.settings.profile.update') }}" class="d-grid gap-2" enctype="multipart/form-data">@csrf @method('PUT')<div class="profile-photo-field"><div class="profile-photo-preview" data-avatar-preview>@if ($user->avatar_path)<img src="{{asset('storage/'.$user->avatar_path)}}" alt="Profile photo">@else<span>{{mb_substr($user->name, 0, 1)}}</span><img src="" alt="Profile photo" hidden>@endif</div><div><label class="btn btn-admin-light" for="profileAvatar"><i class="bi bi-camera"></i> Change photo</label><input class="visually-hidden @error('avatar') is-invalid @enderror" id="profileAvatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp" data-avatar-source><small>JPG, PNG or WebP. Maximum 2 MB.</small>@error('avatar')<div class="text-danger small mt-1">{{ $message }}</div>@enderror</div></div><div><label class="form-label" for="profileName">Name</label><input class="form-control @error('name') is-invalid @enderror" id="profileName" name="name" value="{{ old('name', $user->name) }}" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div><label class="form-label" for="profileEmail">Email</label><input class="form-control @error('email') is-invalid @enderror" id="profileEmail" name="email" type="email" value="{{ old('email', $user->email) }}" required>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div><label class="form-label" for="profilePhone">Phone</label><input class="form-control @error('phone') is-invalid @enderror" id="profilePhone" name="phone" value="{{ old('phone', $user->phone) }}">@error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div><button class="btn btn-admin-primary mt-2" type="submit"><i class="bi bi-check2-circle"></i> Update profile</button></div></form></div>
            </section>
        </div>
        <div class="col-xl-6">
            <section class="settings-card settings-profile-card" id="change-password">
                <div class="settings-card-body"><h2>Change password</h2><form method="post" action="{{ route('admin.settings.password.update') }}" class="d-grid gap-2">@csrf @method('PUT')<div><label class="form-label" for="newPassword">New password</label><input class="form-control @error('password') is-invalid @enderror" id="newPassword" name="password" type="password" required>@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div><label class="form-label" for="passwordConfirmation">Confirm new password</label><input class="form-control" id="passwordConfirmation" name="password_confirmation" type="password" required></div><div><button class="btn btn-admin-primary mt-2" type="submit"><i class="bi bi-key"></i> Change password</button></div></form></div>
            </section>
        </div>
    </div>
@endif
@endsection
