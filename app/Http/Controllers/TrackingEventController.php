<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTrackingEventRequest;
use App\Models\TrackingEvent;
use App\Models\TrackingSetting;
use Illuminate\Http\JsonResponse;

class TrackingEventController extends Controller
{
    public function __invoke(StoreTrackingEventRequest $request): JsonResponse
    {
        if (TrackingSetting::query()->where('tracking_enabled', false)->exists()) {
            return response()->json(['tracked' => false]);
        }

        TrackingEvent::create([
            ...$request->safe()->only(['event', 'path', 'metadata']),
            'session_hash' => hash('sha256', $request->session()->getId()),
        ]);

        return response()->json(['tracked' => true], 201);
    }
}
