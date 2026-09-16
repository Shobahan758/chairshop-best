<?php

namespace Tests\Feature;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMessageListsTest extends TestCase
{
    use RefreshDatabase;

    public function test_today_list_only_contains_messages_created_today(): void
    {
        $this->travelTo(now()->startOfDay()->addHours(12));
        $today = SupportTicket::factory()->create(['created_at' => now()->startOfDay()]);
        SupportTicket::factory()->create(['created_at' => now()->startOfDay()->subSecond(), 'updated_at' => now()]);
        $this->actingAs(User::factory()->admin()->create());

        $response = $this->get(route('admin.support.today'))->assertOk()->assertSee('Today SMS');

        $this->assertSame([$today->id], $response->viewData('tickets')->pluck('id')->all());
        $all = $this->get(route('admin.support.index'))->assertOk()->assertSee('All SMS');
        $this->assertSame(2, $all->viewData('tickets')->total());
        $all->assertSee(route('admin.support.today'), false)->assertSee('Message');
    }

    public function test_today_messages_paginate_on_the_today_route(): void
    {
        $this->travelTo(now()->startOfDay()->addHours(12));
        SupportTicket::factory()->count(21)->create();
        SupportTicket::factory()->create(['created_at' => now()->subDay()]);

        $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.support.today'));

        $response->assertOk()->assertSee(route('admin.support.today', ['page' => 2]), false);
        $this->assertSame(21, $response->viewData('tickets')->total());
        $this->assertCount(20, $response->viewData('tickets'));
        $secondPage = $this->get(route('admin.support.today', ['page' => 2]))->assertOk();
        $this->assertCount(1, $secondPage->viewData('tickets'));
    }

    public function test_today_messages_require_admin_access(): void
    {
        $this->get(route('admin.support.today'))->assertRedirectToRoute('login');
        $this->actingAs(User::factory()->create())->get(route('admin.support.today'))->assertForbidden();
    }

    public function test_admin_can_delete_a_message_and_returns_to_the_selected_list(): void
    {
        $ticket = SupportTicket::factory()->create();
        $other = SupportTicket::factory()->create();
        $account = $ticket->customerAccount;
        $this->actingAs(User::factory()->admin()->create());
        $this->get(route('admin.support.today'))->assertSee(route('admin.support.destroy', $ticket), false);
        $this->get(route('admin.support.index'))->assertSee(route('admin.support.destroy', $ticket), false);

        $this->from(route('admin.support.today'))->delete(route('admin.support.destroy', $ticket))
            ->assertRedirectToRoute('admin.support.today')->assertSessionHas('success', 'Message deleted successfully.');

        $this->assertModelMissing($ticket);
        $this->assertModelExists($other);
        $this->assertModelExists($account);
        $this->delete(route('admin.support.destroy', $ticket))->assertNotFound();
    }

    public function test_guests_and_customers_cannot_delete_messages(): void
    {
        $ticket = SupportTicket::factory()->create();

        $this->delete(route('admin.support.destroy', $ticket))->assertRedirectToRoute('login');
        $this->actingAs(User::factory()->create())->delete(route('admin.support.destroy', $ticket))->assertForbidden();

        $this->assertModelExists($ticket);
    }
}
