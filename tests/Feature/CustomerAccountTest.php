<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\CustomerAccount;
use App\Models\Product;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_dashboard_sections_render_for_a_customer(): void
    {
        $this->actingAs(User::factory()->create());
        foreach (['profile', 'orders', 'wishlist', 'wallet', 'loyalty', 'inbox', 'addresses', 'support', 'referrals', 'coupons', 'track'] as $section) {
            $this->get(route('dashboard', ['section' => $section]))->assertOk()->assertViewHas('section', $section);
        }
        $this->get(route('dashboard', ['section' => 'invalid']))->assertNotFound();
    }

    public function test_profile_saves_name_and_phone_without_changing_login_email_or_balances(): void
    {
        $user = User::factory()->create(['email' => 'owner@example.com']);
        $this->actingAs($user)->put(route('customer.profile'), [
            'name' => 'Updated Customer', 'phone' => '01800000000', 'email' => 'someone@example.com',
            'wallet_entries' => [['amount' => 100000]], 'user_id' => 999,
        ])->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated Customer', 'email' => 'owner@example.com']);
        $this->assertDatabaseHas('customer_accounts', ['user_id' => $user->id, 'phone' => '01800000000', 'wallet_entries' => null]);
    }

    public function test_password_change_requires_the_current_password(): void
    {
        $user = User::factory()->create(['password' => 'old-password']);
        $this->actingAs($user)->put(route('customer.password'), ['current_password' => 'wrong', 'password' => 'new-password', 'password_confirmation' => 'new-password'])->assertSessionHasErrors('current_password');
        $this->put(route('customer.password'), ['current_password' => 'old-password', 'password' => 'new-password', 'password_confirmation' => 'new-password'])->assertRedirect();
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_guest_wishlist_persists_without_duplicate_products_and_can_be_removed(): void
    {
        $product = Product::factory()->create();
        $this->post(route('customer.wishlist.add', $product))->assertRedirect();
        $this->post(route('customer.wishlist.add', $product))->assertRedirect();
        $account = CustomerAccount::firstOrFail();
        $this->assertSame([$product->id], $account->wishlist);
        $this->get(route('dashboard', ['section' => 'wishlist']))->assertSee($product->name);
        $this->delete(route('customer.wishlist.remove', $product))->assertRedirect();
        $this->assertSame([], $account->fresh()->wishlist);
        $this->flushSession();
        $this->get(route('dashboard'))->assertRedirectToRoute('login');
    }

    public function test_saved_address_can_be_edited_used_at_checkout_and_deleted(): void
    {
        $user = User::factory()->create();
        $address = $this->address();
        $this->actingAs($user)->post(route('customer.addresses.save'), $address)->assertRedirect();
        $account = CustomerAccount::firstOrFail();
        $id = $account->addresses[0]['id'];
        $this->post(route('customer.addresses.save'), [...$address, 'id' => $id, 'label' => 'Office'])->assertRedirect();
        $this->assertSame('Office', $account->fresh()->addresses[0]['label']);

        $product = Product::factory()->create();
        $this->withSession(['cart' => [$product->id => ['id' => $product->id, 'name' => $product->name, 'image' => $product->image, 'price' => 100, 'quantity' => 1]]])
            ->get(route('checkout'))->assertSee('Office')->assertSee('savedAddress');
        $this->delete(route('customer.addresses.remove', $id))->assertRedirect();
        $this->assertSame([], $account->fresh()->addresses);
    }

    public function test_address_changes_cannot_target_another_account(): void
    {
        $id = (string) Str::uuid();
        $other = CustomerAccount::factory()->create(['addresses' => [[...$this->address(), 'id' => $id]]]);
        $this->actingAs(User::factory()->create());
        $this->post(route('customer.addresses.save'), [...$this->address(), 'id' => $id])->assertNotFound();
        $this->delete(route('customer.addresses.remove', $id))->assertNotFound();
        $this->assertSame($id, $other->fresh()->addresses[0]['id']);
    }

    public function test_invalid_address_and_ticket_are_rejected(): void
    {
        $this->actingAs(User::factory()->create());
        $this->post(route('customer.addresses.save'), ['phone' => 'invalid'])->assertSessionHasErrors(['name', 'phone', 'address']);
        $this->post(route('customer.tickets.store'), [])->assertSessionHasErrors(['subject', 'message']);
        $this->assertDatabaseCount('support_tickets', 0);
    }

    public function test_ticket_reply_reaches_inbox_and_can_be_marked_read(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('customer.tickets.store'), ['subject' => 'Delivery question', 'message' => 'Where is my order?'])->assertRedirect();
        $ticket = SupportTicket::firstOrFail();
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get(route('admin.support.index'))->assertSee('Delivery question');
        $this->patch(route('admin.support.update', $ticket), ['message' => 'Your order ships tomorrow.', 'status' => 'answered'])->assertRedirect();
        $this->actingAs($user)->get(route('dashboard', ['section' => 'inbox']))->assertSee('Your order ships tomorrow.');
        $this->post(route('customer.inbox.read'))->assertRedirect();
        $this->assertNotNull(CustomerAccount::firstOrFail()->inbox_read_at);
        $this->post(route('customer.tickets.reply', $ticket->id), ['message' => 'Thank you'])->assertRedirect();
        $this->assertSame('open', $ticket->fresh()->status);
        $this->assertCount(3, $ticket->fresh()->messages);
    }

    public function test_customers_cannot_read_or_reply_to_other_tickets_or_access_admin_support(): void
    {
        $ticket = SupportTicket::factory()->create(['subject' => 'Private subject']);
        $this->actingAs(User::factory()->create());
        $this->get(route('dashboard', ['section' => 'support']))->assertDontSee('Private subject');
        $this->post(route('customer.tickets.reply', $ticket->id), ['message' => 'Unauthorized'])->assertNotFound();
        $this->get(route('admin.support.index'))->assertForbidden();
        $this->patch(route('admin.support.update', $ticket), ['message' => 'Unauthorized', 'status' => 'closed'])->assertForbidden();
        $this->assertCount(1, $ticket->fresh()->messages);
    }

    public function test_coupons_hide_expired_and_disabled_codes(): void
    {
        Coupon::query()->create(['code' => 'ACTIVE10', 'discount_type' => 'percent', 'value' => 10, 'minimum_order' => 0, 'is_active' => true]);
        Coupon::query()->create(['code' => 'EXPIRED10', 'discount_type' => 'percent', 'value' => 10, 'minimum_order' => 0, 'is_active' => true, 'expires_at' => now()->subDay()]);
        Coupon::query()->create(['code' => 'DISABLED10', 'discount_type' => 'percent', 'value' => 10, 'minimum_order' => 0, 'is_active' => false]);
        $this->actingAs(User::factory()->create())->get(route('dashboard', ['section' => 'coupons']))
            ->assertSee('ACTIVE10')->assertDontSee('EXPIRED10')->assertDontSee('DISABLED10');
    }

    public function test_referral_link_records_a_visit_and_attributes_checkout_without_rewarding_money(): void
    {
        $referrer = CustomerAccount::factory()->create();
        $this->get(route('customer.referral', $referrer->referral_code))->assertRedirectToRoute('shop');
        $this->get(route('customer.referral', $referrer->referral_code))->assertRedirectToRoute('shop');
        $this->assertSame(1, $referrer->fresh()->referral_visits);
        $product = Product::factory()->create(['stock' => 2]);
        $this->withSession(['cart' => [$product->id => ['id' => $product->id, 'name' => $product->name, 'image' => $product->image, 'price' => 100, 'quantity' => 1]]])
            ->post(route('checkout.store'), ['name' => 'Buyer', 'phone' => '01800000000', 'district' => 'Dhaka', 'area' => 'Dhanmondi', 'address' => 'House 10, Road 5', 'payment_method' => 'cod'])->assertRedirectToRoute('dashboard');
        $this->assertDatabaseHas('orders', ['referrer_account_id' => $referrer->id]);
        $this->assertNull($referrer->fresh()->wallet_entries);
    }

    public function test_wallet_and_loyalty_show_only_the_current_accounts_entries(): void
    {
        $user = User::factory()->create();
        CustomerAccount::factory()->create(['user_id' => $user->id, 'wallet_entries' => [['date' => '2026-09-07', 'description' => 'Refund credit', 'amount' => 125]], 'loyalty_entries' => [['date' => '2026-09-07', 'description' => 'Purchase points', 'amount' => 20]]]);
        CustomerAccount::factory()->create(['wallet_entries' => [['date' => '2026-09-07', 'description' => 'Private credit', 'amount' => 900]]]);
        $this->actingAs($user)->get(route('dashboard', ['section' => 'wallet']))->assertSee('125.00')->assertSee('Refund credit')->assertDontSee('Private credit');
        $this->get(route('dashboard', ['section' => 'loyalty']))->assertSee('Purchase points')->assertSee('20');
    }

    private function address(): array
    {
        return ['label' => 'Home', 'name' => 'Test Customer', 'phone' => '01700000000', 'district' => 'Dhaka', 'area' => 'Dhanmondi', 'address' => 'House 10, Road 5'];
    }
}
