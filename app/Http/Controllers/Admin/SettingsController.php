<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateGeneralSettingsRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UpdateSiteContentRequest;
use App\Models\GeneralSetting;
use App\Models\User;
use App\Services\SiteContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function general(): View
    {
        return view('admin.settings', [
            'section' => 'general',
            'settings' => GeneralSetting::first() ?? new GeneralSetting,
        ]);
    }

    public function updateGeneral(UpdateGeneralSettingsRequest $request): RedirectResponse
    {
        GeneralSetting::firstOrNew()->fill($request->validated())->save();

        return back()->with('success', 'General settings updated successfully.');
    }

    public function site(Request $request): View
    {
        $page = $request->string('page', 'home')->toString();
        abort_unless(array_key_exists($page, SiteContent::pages()), 404);

        $settings = GeneralSetting::first();

        return view('admin.site-settings', [
            'page' => $page,
            'pages' => SiteContent::pages(),
            'sections' => config('site_content.'.$page),
            'siteContent' => new SiteContent($settings?->site_content ?? [], $settings),
        ]);
    }

    public function updateSite(UpdateSiteContentRequest $request, string $page, string $section): RedirectResponse
    {
        $content = $request->validated('content');
        foreach ($request->file('uploads', []) as $key => $image) {
            $content[$key] = '/storage/'.$image->store('site-content', 'public');
        }

        DB::transaction(function () use ($page, $section, $content): void {
            $settings = GeneralSetting::query()->lockForUpdate()->first() ?? new GeneralSetting;
            $values = $settings->site_content ?? [];
            $values[$page][$section] = array_map(fn (?string $value): string => $value ?? '', $content);
            $settings->site_content = $values;
            $settings->save();
        });

        return to_route('admin.settings.site', ['page' => $page, 'saved' => $section])
            ->with('success', 'সেকশনের কনটেন্ট সেভ হয়েছে।');
    }

    public function users(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $users = User::query()
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.settings', ['section' => 'users', 'users' => $users, 'search' => $search]);
    }

    public function profile(Request $request): View
    {
        return view('admin.settings', ['section' => 'profile', 'user' => $request->user()]);
    }

    public function updateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $attributes = $request->safe()->except('avatar');

        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');

            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $attributes['avatar_path'] = $avatarPath;
        }

        $user->update($attributes);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $request->user()->update(['password' => $request->validated('password')]);

        return back()->with('success', 'Password changed successfully.');
    }
}
