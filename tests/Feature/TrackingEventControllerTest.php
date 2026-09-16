<?php

namespace Tests\Feature;

use App\Models\TrackingEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingEventControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_storefront_event_is_recorded(): void
    {
        $response = $this->postJson(route('tracking.events'), [
            'event' => 'page_view',
            'path' => '/shop',
        ]);

        $response->assertCreated()->assertJson(['tracked' => true]);
        $this->assertSame('page_view', TrackingEvent::first()->event);
    }

    public function test_unknown_event_is_rejected(): void
    {
        $this->postJson(route('tracking.events'), [
            'event' => 'unknown_event',
            'path' => '/',
        ])->assertUnprocessable()->assertJsonValidationErrors('event');
    }
}
