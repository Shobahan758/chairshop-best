<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateTrackingSettingsRequest;
use App\Models\TrackingEvent;
use App\Models\TrackingSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TrackingController extends Controller
{
    public function index(): View
    {
        return view('admin.tracking', [
            'section' => 'site',
            'settings' => TrackingSetting::first() ?? new TrackingSetting(['tracking_enabled' => true]),
            'eventCounts' => TrackingEvent::query()
                ->whereNotIn('path', ['/login', '/dashboard'])
                ->where('path', 'not like', '/admin%')
                ->selectRaw('event, count(*) as total')
                ->groupBy('event')
                ->pluck('total', 'event'),
        ]);
    }

    public function show(string $integration): View
    {
        return view('admin.tracking', [
            'section' => $integration,
            'settings' => TrackingSetting::first() ?? new TrackingSetting(['tracking_enabled' => true]),
        ]);
    }

    public function update(UpdateTrackingSettingsRequest $request): RedirectResponse
    {
        $settings = TrackingSetting::firstOrNew();
        $settings->fill($request->safe()->except('tracking_enabled'));
        $settings->tracking_enabled = $request->boolean('tracking_enabled');
        $settings->save();

        return back()->with('success', 'Tracking integrations updated successfully.');
    }

    public function updateIntegration(UpdateTrackingSettingsRequest $request, string $integration): RedirectResponse
    {
        $fields = match ($integration) {
            'meta' => ['meta_pixel_id'],
            'google' => ['google_tag_id'],
            'tiktok' => ['tiktok_pixel_id'],
            'other' => ['pinterest_tag_id', 'snapchat_pixel_id'],
        };

        $settings = TrackingSetting::firstOrNew()->fill($request->safe()->only($fields));
        if ($integration === 'meta' && $request->filled('meta_pixel_id')) {
            $settings->tracking_enabled = true;
        }
        $settings->save();

        return back()->with('success', 'Integration updated successfully.');
    }
}
