<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveStaffUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function create(): View
    {
        return view('admin.staff-form', ['staff' => new User]);
    }

    public function store(SaveStaffUserRequest $request): RedirectResponse
    {
        User::create([...$request->validated(), 'is_admin' => true]);

        return to_route('admin.settings.users')->with('success', 'Staff user added successfully.');
    }

    public function edit(User $user): View
    {
        abort_unless($user->is_admin, 404);

        return view('admin.staff-form', ['staff' => $user]);
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        DB::transaction(function () use ($user): void {
            $target = User::query()->lockForUpdate()->findOrFail($user->id);
            abort_if($target->role === 'super_admin', 403, 'Super Admin accounts cannot be deleted.');
            $target->delete();
        });

        return to_route('admin.settings.users')->with('success', 'User deleted successfully.');
    }

    public function update(SaveStaffUserRequest $request, User $user): RedirectResponse
    {
        abort_unless($user->is_admin, 404);
        DB::transaction(function () use ($request, $user): void {
            $staff = User::query()->where('is_admin', true)->orderBy('id')->lockForUpdate()->get();
            $target = $staff->firstWhere('id', $user->id);
            abort_unless($target, 404);
            if ($target->isSuperAdmin() && $request->validated('role') !== 'super_admin'
                && ($target->id === $request->user()->id || $staff->filter->isSuperAdmin()->count() <= 1)) {
                throw ValidationException::withMessages(['role' => 'You cannot remove your own Super Admin access or the last Super Admin.']);
            }
            $data = $request->safe()->except('password');
            if ($request->filled('password')) {
                $data['password'] = $request->validated('password');
            }
            $target->update($data);
        }, 3);

        return to_route('admin.settings.users')->with('success', 'Staff user updated successfully.');
    }
}
