<?php

namespace Tests\Feature;

use App\Models\GeneralSetting;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_details_can_be_changed_from_site_settings(): void
    {
        $this->get(route('contact'))->assertSee('hello@example.com')->assertSee('+880 1700-000000');
        GeneralSetting::create(['support_email' => 'old@example.com']);
        $content = array_map(fn (array $field): string => $field['default'], config('site_content.contact.contact_details'));
        $content['email'] = 'new@example.com';
        $content['phone'] = '+880 1812-345678';
        $content['address'] = 'Updated office, Dhaka';

        $this->actingAs(User::factory()->admin()->create())->put(route('admin.settings.site.update', ['contact', 'contact_details']), ['content' => $content])
            ->assertSessionHasNoErrors()->assertRedirect();

        $this->get(route('contact'))->assertSee('mailto:new@example.com', false)
            ->assertSee('tel:+8801812345678', false)->assertSee('Updated office, Dhaka');
        $this->get(route('shop'))->assertSee('mailto:new@example.com', false)
            ->assertSee('tel:+8801812345678', false)->assertSee('Updated office, Dhaka')
            ->assertDontSee('mailto:old@example.com', false);
        $this->get(route('admin.settings.site', ['page' => 'contact', 'section' => 'contact_details']))->assertSee('new@example.com');
        $this->assertSame('new@example.com', GeneralSetting::first()->site_content['contact']['contact_details']['email']);
    }

    public function test_store_footer_shows_contact_settings_on_other_pages(): void
    {
        GeneralSetting::create(['support_email' => 'office@example.com', 'support_phone' => '+8801712345678', 'business_address' => 'Office 12, Dhaka']);

        $this->get(route('page', 'delivery'))->assertOk()
            ->assertSee('Office Address')
            ->assertSee('Office 12, Dhaka')
            ->assertSee('mailto:office@example.com', false)
            ->assertSee('tel:+8801712345678', false)
            ->assertDontSee('আমাদের খবর নিন');
    }

    public function test_contact_page_displays_configured_contact_details_and_support_form(): void
    {
        GeneralSetting::create(['support_email' => 'support@example.com', 'support_phone' => '+8801712345678', 'business_address' => 'House 12, Dhaka']);

        $this->get(route('contact'))->assertOk()
            ->assertSee('mailto:support@example.com', false)
            ->assertSee('tel:+8801712345678', false)
            ->assertSee('House 12, Dhaka')
            ->assertSee(route('customer.tickets.store'), false);
        $this->get(route('page', 'contact'))->assertOk()->assertSee('mailto:support@example.com', false);
    }

    public function test_contact_page_works_without_settings_and_does_not_show_placeholder_details(): void
    {
        $this->get(route('contact'))->assertOk()
            ->assertSee(route('customer.tickets.store'), false)
            ->assertDontSee('hello@chairghor.bd')
            ->assertDontSee('০১৭০০-০০০০০০');
    }

    public function test_contact_message_is_saved_as_a_guest_support_ticket(): void
    {
        $this->from(route('contact'))->post(route('customer.tickets.store'), [
            'name' => 'Contact Customer', 'phone' => '01712345678', 'email' => 'contact@example.com',
            'subject' => 'Chair selection', 'message' => 'Please help me choose an office chair.',
        ])->assertRedirectToRoute('dashboard', ['section' => 'support']);

        $ticket = SupportTicket::sole();
        $this->assertSame('Chair selection', $ticket->subject);
        $this->assertSame('Please help me choose an office chair.', $ticket->messages[0]['body']);
        $this->assertSame(session('customer_account_id'), $ticket->customer_account_id);
        $this->assertSame('Contact Customer', $ticket->messages[0]['contact']['name']);
        $this->assertSame('01712345678', $ticket->messages[0]['contact']['phone']);
        $this->assertSame('contact@example.com', $ticket->messages[0]['contact']['email']);
        $this->get(route('dashboard', ['section' => 'support']))->assertSee('Chair selection');
        $this->actingAs(User::factory()->admin()->create())->get(route('admin.support.index'))
            ->assertSee('Contact Customer')->assertSee('01712345678')->assertSee('contact@example.com')
            ->assertSee('Chair selection')->assertSee('Please help me choose an office chair.');
        $this->get(route('admin.support.today'))
            ->assertSee('Contact Customer')->assertSee('01712345678')->assertSee('contact@example.com')
            ->assertSee('Chair selection')->assertSee('Please help me choose an office chair.');
    }

    public function test_invalid_contact_message_returns_to_form_with_errors(): void
    {
        $this->from(route('contact'))->post(route('customer.tickets.store'), [
            'name' => '', 'phone' => '123', 'email' => 'invalid',
            'subject' => '', 'message' => 'Hi',
        ])->assertRedirectToRoute('contact')->assertSessionHasErrors(['name', 'phone', 'email', 'subject', 'message']);

        $this->assertDatabaseCount('support_tickets', 0);
    }

    public function test_admin_can_see_and_submit_the_contact_form(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get(route('contact'))
            ->assertOk()->assertSee('action="'.route('customer.tickets.store').'"', false)
            ->assertSee('id="contact-subject"', false)->assertSee('id="contact-message"', false);

        $this->post(route('customer.tickets.store'), [
            'subject' => 'Contact enquiry', 'message' => 'Please share more chair details.',
        ])->assertRedirectToRoute('contact')->assertSessionHas('success');

        $ticket = SupportTicket::sole();
        $this->assertSame($admin->id, $ticket->customerAccount->user_id);
        $this->assertSame('Contact enquiry', $ticket->subject);
        $this->assertSame('Please share more chair details.', $ticket->messages[0]['body']);
    }
}
