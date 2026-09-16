<?php

namespace App\Services;

use App\Models\TrackingEvent;
use App\Models\TrackingSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StorefrontTracker
{
    /** @param array<string, mixed> $metadata */
    public function record(Request $request, string $event, array $metadata = []): void
    {
        $settings = TrackingSetting::first();
        if ($settings && ! $settings->tracking_enabled) {
            return;
        }

        if ($settings?->meta_pixel_id) {
            $pixelEvent = match ($event) {
                'add_to_cart' => ($metadata['quantity'] ?? 0) > 0 ? [
                    'name' => 'AddToCart',
                    'parameters' => ['content_ids' => [(string) $metadata['product_id']], 'content_type' => 'product', 'contents' => [['id' => (string) $metadata['product_id'], 'quantity' => $metadata['quantity']]], 'value' => $metadata['value'], 'currency' => 'BDT'],
                ] : null,
                'purchase_complete' => [
                    'name' => 'Purchase',
                    'parameters' => ['value' => $metadata['total'], 'currency' => 'BDT'],
                ],
                default => null,
            };
            if ($pixelEvent) {
                $events = $request->session()->get('meta_pixel_events', []);
                $events[] = [...$pixelEvent, 'id' => (string) Str::uuid()];
                $request->session()->flash('meta_pixel_events', $events);
            }
        }

        TrackingEvent::create([
            'event' => $event,
            'path' => '/'.$request->path(),
            'session_hash' => hash('sha256', $request->session()->getId()),
            'metadata' => $metadata,
        ]);
    }
}
