@extends('layouts.admin')
@section('title', ($staff->exists ? 'Edit User' : 'Add User').' — ChairGhor')
@section('content')
<div class="settings-page-heading"><h1>{{ $staff->exists ? 'Edit User' : 'Add User' }}</h1><p>Create staff accounts and choose their access.</p></div>
<section class="settings-card">
    <div class="settings-card-head"><i class="bi bi-person-plus"></i><h2>Staff details</h2></div>
    <form method="post" action="{{ $staff->exists ? route('admin.settings.users.update', $staff) : route('admin.settings.users.store') }}">
        @csrf
        @if($staff->exists) @method('PUT') @endif
        <div class="settings-card-body">
            @if($errors->any())<div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <div class="row g-4">
                <div class="col-md-6"><label class="form-label" for="staffName">Name *</label><input class="form-control" id="staffName" name="name" value="{{ old('name', $staff->name) }}" maxlength="255" required></div>
                <div class="col-md-6"><label class="form-label" for="staffEmail">Email *</label><input class="form-control" id="staffEmail" type="email" name="email" value="{{ old('email', $staff->email) }}" maxlength="255" required></div>
                <div class="col-md-6"><label class="form-label" for="staffPhone">Phone (optional)</label><input class="form-control" id="staffPhone" name="phone" value="{{ old('phone', $staff->phone) }}" maxlength="30"></div>
                <div class="col-md-6"><label class="form-label" for="staffRole">Role *</label><select class="form-select" id="staffRole" name="role" required>
                    @foreach(['manager' => 'Manager', 'admin' => 'Admin', 'super_admin' => 'Super Admin'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('role', $staff->role ?? 'manager') === $value)>{{ $label }}</option>
                    @endforeach
                </select></div>
                <div class="col-md-6"><label class="form-label" for="staffPassword">{{ $staff->exists ? 'New password (leave blank to keep current)' : 'Password *' }}</label><input class="form-control" id="staffPassword" name="password" type="password" autocomplete="new-password" minlength="8" maxlength="255" @required(! $staff->exists)></div>
                <div class="col-md-6"><label class="form-label" for="staffPasswordConfirm">Confirm password</label><input class="form-control" id="staffPasswordConfirm" name="password_confirmation" type="password" autocomplete="new-password" @required(! $staff->exists)></div>
            </div>
            <section class="border rounded p-3 mt-4" aria-labelledby="accessHeading">
                <h2 class="h5" id="accessHeading">Select access</h2>
                <p class="text-muted">Choose the sections this staff member can view and manage. Profile and password access are always available. Role management is only available to Super Admin.</p>
                <div id="superAdminAccess" class="alert alert-info" @if(old('role', $staff->role) !== 'super_admin') hidden @endif>Super Admin has full access to every section, including roles.</div>
                <fieldset id="staffPermissions" @disabled(old('role', $staff->role) === 'super_admin')>
                    <legend class="visually-hidden">Section permissions</legend>
                    <div class="d-flex gap-2 mb-3">
                        <button class="btn btn-admin-light btn-sm" type="button" data-permissions-select="all">Select all</button>
                        <button class="btn btn-admin-light btn-sm" type="button" data-permissions-select="none">Clear all</button>
                    </div>
                    <div class="row g-3">
                        @foreach(config('staff_permissions') as $key => $details)
                            <div class="col-md-6 col-xl-4">
                                <div class="form-check">
                                    <input class="form-check-input" id="permission-{{ $key }}" name="permissions[]" type="checkbox" value="{{ $key }}" @checked(in_array($key, (array) old('permissions', $staff->staffPermissions()), true))>
                                    <label class="form-check-label" for="permission-{{ $key }}">{{ $details['label'] }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </fieldset>
            </section>
        </div>
        <div class="settings-card-footer d-flex gap-2"><a class="btn btn-admin-light" href="{{ route('admin.settings.users') }}">Cancel</a><button class="btn btn-admin-primary" type="submit">{{ $staff->exists ? 'Save changes' : 'Add user' }}</button></div>
    </form>
</section>
@endsection

@push('scripts')
<script>
(() => {
    const role = document.getElementById('staffRole');
    const permissions = document.getElementById('staffPermissions');
    const notice = document.getElementById('superAdminAccess');
    const syncRole = () => {
        const isSuperAdmin = role.value === 'super_admin';
        permissions.disabled = isSuperAdmin;
        notice.hidden = !isSuperAdmin;
    };
    role.addEventListener('change', syncRole);
    window.addEventListener('pageshow', syncRole);
    document.querySelectorAll('[data-permissions-select]').forEach(button => {
        button.addEventListener('click', () => {
            permissions.querySelectorAll('input[type="checkbox"]').forEach(input => {
                input.checked = button.dataset.permissionsSelect === 'all';
            });
        });
    });
    syncRole();
})();
</script>
@endpush
