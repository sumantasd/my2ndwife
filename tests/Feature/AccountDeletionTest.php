<?php

namespace Tests\Feature;

use App\Models\AccountDeletionRequest;
use App\Models\Address;
use App\Models\AuditLog;
use App\Models\DeviceToken;
use App\Models\NewsletterSubscriber;
use App\Models\Order;
use App\Models\RestaurantSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountDeletionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        RestaurantSetting::set('restaurant_name', 'My 2nd Wife');

        $this->admin = User::factory()->create([
            'name' => 'Admin Manager',
            'email' => 'admin@my2ndwife.com',
            'phone' => '+91 98000 00001',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->customer = User::factory()->create([
            'name' => 'Sourav Ganguly',
            'email' => 'sourav@example.com',
            'phone' => '+91 98300 12345',
            'role' => User::ROLE_CUSTOMER,
            'is_active' => true,
        ]);
    }

    public function test_public_account_deletion_page_loads_with_required_google_play_disclosures(): void
    {
        $response = $this->get('/page/play-store-app-delete');

        $response->assertStatus(200);
        $response->assertSee('Account Deletion Request — My 2nd Wife');
        $response->assertSee('Users of My 2nd Wife can request deletion of their account and associated personal data through this page.');
        $response->assertSee('After receiving a request, our team will verify the requester\'s identity and process the request in accordance with our', false);
        $response->assertSee('Some records may need to be retained where required by law, for security, fraud prevention, or legitimate business obligations.');
        $response->assertSee(route('page.privacy'));
        $response->assertSee('Submit Deletion Request');
    }

    public function test_form_validation_requires_all_mandatory_fields(): void
    {
        $response = $this->post('/page/play-store-app-delete', []);

        $response->assertSessionHasErrors(['full_name', 'mobile_number', 'reason', 'confirmation']);
        $this->assertEquals(0, AccountDeletionRequest::count());
    }

    public function test_form_validation_rejects_invalid_mobile_number(): void
    {
        $response = $this->post('/page/play-store-app-delete', [
            'full_name' => 'Test User',
            'mobile_number' => '12345',
            'reason' => 'privacy_concerns',
            'confirmation' => '1',
        ]);

        $response->assertSessionHasErrors(['mobile_number']);
        $this->assertEquals(0, AccountDeletionRequest::count());
    }

    public function test_form_validation_rejects_unapproved_reason(): void
    {
        $response = $this->post('/page/play-store-app-delete', [
            'full_name' => 'Test User',
            'mobile_number' => '9830012345',
            'reason' => 'invalid_unrecognized_reason',
            'confirmation' => '1',
        ]);

        $response->assertSessionHasErrors(['reason']);
        $this->assertEquals(0, AccountDeletionRequest::count());
    }

    public function test_user_can_submit_deletion_request_successfully(): void
    {
        $response = $this->post('/page/play-store-app-delete', [
            'full_name' => 'Rohan Sen',
            'mobile_number' => '+91 98765 43210',
            'email' => 'rohan@example.com',
            'reason' => 'no_longer_using',
            'message' => 'Moving to another city.',
            'confirmation' => '1',
        ]);

        $response->assertRedirect(route('account-deletion.show'));
        $response->assertSessionHas('reference_number');

        $this->assertDatabaseHas('account_deletion_requests', [
            'full_name' => 'Rohan Sen',
            'mobile_number' => '+91 9876543210',
            'email' => 'rohan@example.com',
            'reason' => 'no_longer_using',
            'status' => AccountDeletionRequest::STATUS_PENDING,
        ]);

        $req = AccountDeletionRequest::first();
        $this->assertMatchesRegularExpression('/^MWD-\d{4}-[A-Z0-9]{8}$/', $req->reference_number);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'account_deletion_requested',
            'target_type' => 'AccountDeletionRequest',
            'target_id' => $req->id,
        ]);
    }

    public function test_duplicate_request_within_24_hours_returns_existing_reference_without_duplicate(): void
    {
        $firstResponse = $this->post('/page/play-store-app-delete', [
            'full_name' => 'Amit Kumar',
            'mobile_number' => '9830099887',
            'reason' => 'privacy_concerns',
            'confirmation' => '1',
        ]);

        $firstResponse->assertRedirect(route('account-deletion.show'));
        $firstRef = session('reference_number');
        $this->assertEquals(1, AccountDeletionRequest::count());

        // Submitting duplicate request 5 minutes later
        $secondResponse = $this->post('/page/play-store-app-delete', [
            'full_name' => 'Amit Kumar',
            'mobile_number' => '+91 98300 99887',
            'reason' => 'privacy_concerns',
            'confirmation' => '1',
        ]);

        $secondResponse->assertRedirect(route('account-deletion.show'));
        $secondResponse->assertSessionHas('reference_number', $firstRef);
        $secondResponse->assertSessionHas('status_type', 'info');

        // Verify no duplicate row was created
        $this->assertEquals(1, AccountDeletionRequest::count());
    }

    public function test_authenticated_user_prefills_form_and_links_user_id(): void
    {
        $response = $this->actingAs($this->customer)->get('/page/play-store-app-delete');
        $response->assertStatus(200);
        $response->assertSee($this->customer->name);
        $response->assertSee($this->customer->phone);

        $submitResponse = $this->actingAs($this->customer)->post('/page/play-store-app-delete', [
            'full_name' => $this->customer->name,
            'mobile_number' => $this->customer->phone,
            'email' => $this->customer->email,
            'reason' => 'technical_issues',
            'confirmation' => '1',
        ]);

        $submitResponse->assertRedirect(route('account-deletion.show'));

        $this->assertDatabaseHas('account_deletion_requests', [
            'user_id' => $this->customer->id,
            'full_name' => $this->customer->name,
            'status' => AccountDeletionRequest::STATUS_PENDING,
        ]);
    }

    public function test_unauthenticated_request_matches_existing_user_silently(): void
    {
        $response = $this->post('/page/play-store-app-delete', [
            'full_name' => $this->customer->name,
            'mobile_number' => '9830012345',
            'reason' => 'duplicate_account',
            'confirmation' => '1',
        ]);

        $response->assertRedirect(route('account-deletion.show'));

        $this->assertDatabaseHas('account_deletion_requests', [
            'user_id' => $this->customer->id,
            'mobile_number' => '+91 9830012345',
        ]);
    }

    public function test_guest_cannot_spoof_victim_account_with_unmatched_email(): void
    {
        // Victim has email sourav@example.com and mobile +91 98300 12345
        // Attacker submits victim's email with attacker's different burner phone
        $response = $this->post('/page/play-store-app-delete', [
            'full_name' => 'Mallory Hacker',
            'mobile_number' => '9876543210',
            'email' => $this->customer->email,
            'reason' => 'privacy_concerns',
            'confirmation' => '1',
        ]);

        $response->assertRedirect(route('account-deletion.show'));

        // user_id MUST NOT be linked to victim!
        $req = AccountDeletionRequest::where('mobile_number', '+91 9876543210')->first();
        $this->assertNotNull($req);
        $this->assertNull($req->user_id);
    }

    public function test_guest_and_non_admin_cannot_access_admin_routes(): void
    {
        $req = AccountDeletionRequest::create([
            'reference_number' => 'MWD-2026-TEST1234',
            'full_name' => 'Test User',
            'mobile_number' => '+91 9830011111',
            'reason' => 'other',
            'status' => AccountDeletionRequest::STATUS_PENDING,
        ]);

        // Guest redirected
        $this->get(route('admin.account-deletions.index'))->assertRedirect(route('login'));
        $this->get(route('admin.account-deletions.show', $req))->assertRedirect(route('login'));

        // Customer forbidden
        $this->actingAs($this->customer)->get(route('admin.account-deletions.index'))->assertForbidden();
    }

    public function test_staff_roles_cannot_access_or_process_deletion_management(): void
    {
        $req = AccountDeletionRequest::create([
            'reference_number' => 'MWD-2026-STAFF001',
            'full_name' => 'Staff Test',
            'mobile_number' => '+91 9830011111',
            'reason' => 'other',
            'status' => AccountDeletionRequest::STATUS_PENDING,
        ]);

        $roles = [
            User::ROLE_KITCHEN_STAFF,
            User::ROLE_ORDER_MANAGER,
            User::ROLE_DELIVERY_MANAGER,
            User::ROLE_DELIVERY_PERSON,
        ];

        foreach ($roles as $role) {
            $staffUser = User::factory()->create(['role' => $role]);

            $this->actingAs($staffUser)
                ->get(route('admin.account-deletions.index'))
                ->assertForbidden();

            $this->actingAs($staffUser)
                ->get(route('admin.account-deletions.show', $req))
                ->assertForbidden();

            $this->actingAs($staffUser)
                ->post(route('admin.account-deletions.process', $req))
                ->assertForbidden();
        }
    }

    public function test_admin_can_view_listing_and_filter_requests(): void
    {
        AccountDeletionRequest::create([
            'reference_number' => 'MWD-2026-PEND0001',
            'full_name' => 'Pending Person',
            'mobile_number' => '+91 9830011111',
            'reason' => 'privacy_concerns',
            'status' => AccountDeletionRequest::STATUS_PENDING,
        ]);

        AccountDeletionRequest::create([
            'reference_number' => 'MWD-2026-COMP0002',
            'full_name' => 'Completed Person',
            'mobile_number' => '+91 9830022222',
            'reason' => 'no_longer_using',
            'status' => AccountDeletionRequest::STATUS_COMPLETED,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.account-deletions.index'));
        $response->assertStatus(200);
        $response->assertSee('MWD-2026-PEND0001');
        $response->assertSee('MWD-2026-COMP0002');

        // Filter by status=pending
        $filteredResponse = $this->actingAs($this->admin)->get(route('admin.account-deletions.index', ['status' => 'pending']));
        $filteredResponse->assertStatus(200);
        $filteredResponse->assertSee('MWD-2026-PEND0001');
        $filteredResponse->assertDontSee('MWD-2026-COMP0002');
    }

    public function test_admin_can_verify_and_document_trustworthy_identity(): void
    {
        $req = AccountDeletionRequest::create([
            'reference_number' => 'MWD-2026-VERIFY01',
            'full_name' => 'Verify User',
            'mobile_number' => '+91 9830033333',
            'reason' => 'other',
            'status' => AccountDeletionRequest::STATUS_PENDING,
        ]);

        $this->assertFalse($req->isIdentityVerified());

        // Attempt verification with missing notes fails validation
        $invalidResponse = $this->actingAs($this->admin)->post(route('admin.account-deletions.verify', $req), [
            'verification_method' => 'phone_call',
            'verification_notes' => '',
        ]);
        $invalidResponse->assertSessionHasErrors(['verification_notes']);

        // Proper verification with documented evidence
        $response = $this->actingAs($this->admin)->post(route('admin.account-deletions.verify', $req), [
            'verification_method' => 'phone_call',
            'verification_notes' => 'Spoke with customer on +91 9830033333 and confirmed recent order details.',
        ]);
        $response->assertSessionHas('success');

        $req->refresh();
        $this->assertTrue($req->isIdentityVerified());
        $this->assertNotNull($req->identity_verified_at);
        $this->assertEquals('phone_call', $req->identity_verification_method);
        $this->assertStringContainsString('Spoke with customer on +91 9830033333', $req->admin_notes);
        $this->assertEquals($this->admin->id, $req->reviewed_by);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'account_deletion_identity_verified',
            'target_type' => 'AccountDeletionRequest',
            'target_id' => $req->id,
        ]);

        // Revoke verification
        $clearResponse = $this->actingAs($this->admin)->post(route('admin.account-deletions.clear-verification', $req));
        $clearResponse->assertSessionHas('info');

        $req->refresh();
        $this->assertFalse($req->isIdentityVerified());
        $this->assertNull($req->identity_verified_at);
    }

    public function test_admin_can_update_status_and_admin_notes(): void
    {
        $req = AccountDeletionRequest::create([
            'reference_number' => 'MWD-2026-STAT0001',
            'full_name' => 'Status User',
            'mobile_number' => '+91 9830044444',
            'reason' => 'technical_issues',
            'status' => AccountDeletionRequest::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.account-deletions.update-status', $req), [
            'status' => AccountDeletionRequest::STATUS_UNDER_REVIEW,
            'admin_notes' => 'Customer called to clarify grievance on technical bugs.',
        ]);

        $response->assertSessionHas('success');

        $req->refresh();
        $this->assertEquals(AccountDeletionRequest::STATUS_UNDER_REVIEW, $req->status);
        $this->assertEquals('Customer called to clarify grievance on technical bugs.', $req->admin_notes);
        $this->assertEquals($this->admin->id, $req->reviewed_by);
    }

    public function test_admin_cannot_process_deletion_without_identity_verification(): void
    {
        $req = AccountDeletionRequest::create([
            'reference_number' => 'MWD-2026-UNVER001',
            'user_id' => $this->customer->id,
            'full_name' => $this->customer->name,
            'mobile_number' => $this->customer->phone,
            'reason' => 'privacy_concerns',
            'status' => AccountDeletionRequest::STATUS_PENDING,
            'identity_verified_at' => null, // Unverified!
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.account-deletions.process', $req));

        $response->assertSessionHas('error');
        $this->assertStringContainsString('identity has not been verified', session('error'));

        // Customer account MUST remain untouched
        $this->customer->refresh();
        $this->assertTrue($this->customer->is_active);
        $this->assertEquals('sourav@example.com', $this->customer->email);

        $req->refresh();
        $this->assertEquals(AccountDeletionRequest::STATUS_PENDING, $req->status);
    }

    public function test_admin_cannot_process_deletion_if_customer_has_active_orders(): void
    {
        // Create an in-flight order for customer
        Order::create([
            'order_number' => 'ORD-DEL-ACT01',
            'tracking_token' => 'track-active-01',
            'user_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'customer_phone' => $this->customer->phone,
            'order_status' => Order::STATUS_PREPARING,
            'order_type' => Order::TYPE_DELIVERY,
            'subtotal' => 450,
            'total_amount' => 500,
        ]);

        $req = AccountDeletionRequest::create([
            'reference_number' => 'MWD-2026-ACTORD01',
            'user_id' => $this->customer->id,
            'full_name' => $this->customer->name,
            'mobile_number' => $this->customer->phone,
            'reason' => 'privacy_concerns',
            'status' => AccountDeletionRequest::STATUS_APPROVED,
            'identity_verified_at' => now(),
            'identity_verification_method' => 'phone_call',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.account-deletions.process', $req));

        $response->assertSessionHas('error');

        // Customer account should NOT be touched
        $this->customer->refresh();
        $this->assertTrue($this->customer->is_active);
        $this->assertStringNotContainsString('deleted', $this->customer->email);

        $req->refresh();
        $this->assertEquals(AccountDeletionRequest::STATUS_APPROVED, $req->status);
    }

    public function test_admin_can_process_safe_account_deletion_when_no_active_orders(): void
    {
        // Create past delivered order (should remain intact for accounting)
        $pastOrder = Order::create([
            'order_number' => 'ORD-DEL-PAST01',
            'tracking_token' => 'track-past-01',
            'user_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'customer_phone' => $this->customer->phone,
            'order_status' => Order::STATUS_DELIVERED,
            'order_type' => Order::TYPE_DELIVERY,
            'subtotal' => 600,
            'total_amount' => 650,
        ]);

        // Create saved address
        Address::create([
            'user_id' => $this->customer->id,
            'type' => 'home',
            'contact_name' => $this->customer->name,
            'contact_phone' => $this->customer->phone,
            'address_line_1' => 'Flat 4B, Park Street',
            'city' => 'Kolkata',
            'postal_code' => '700016',
        ]);

        // Create device token
        DeviceToken::create([
            'user_id' => $this->customer->id,
            'token' => 'fcm-device-token-1234567890',
            'platform' => 'android',
            'is_active' => true,
        ]);

        // Subscribe to newsletter
        NewsletterSubscriber::create([
            'email' => $this->customer->email,
            'is_active' => true,
        ]);

        // Create Sanctum personal access token
        $this->customer->createToken('android_auth_token');

        $this->assertEquals(1, $this->customer->addresses()->count());
        $this->assertEquals(1, $this->customer->deviceTokens()->count());
        $this->assertEquals(1, $this->customer->tokens()->count());
        $this->assertEquals(1, NewsletterSubscriber::where('email', $this->customer->email)->count());

        $req = AccountDeletionRequest::create([
            'reference_number' => 'MWD-2026-CLEAN001',
            'user_id' => $this->customer->id,
            'full_name' => $this->customer->name,
            'mobile_number' => $this->customer->phone,
            'reason' => 'privacy_concerns',
            'status' => AccountDeletionRequest::STATUS_APPROVED,
            'identity_verified_at' => now(),
            'identity_verification_method' => 'phone_call',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.account-deletions.process', $req));
        $response->assertSessionHas('success');

        // Customer details anonymized
        $this->customer->refresh();
        $this->assertEquals("Deleted Customer #{$this->customer->id}", $this->customer->name);
        $this->assertEquals("deleted_user_{$this->customer->id}@my2ndwife.local", $this->customer->email);
        $this->assertEquals("deleted_{$this->customer->id}", $this->customer->phone);
        $this->assertFalse($this->customer->is_active);

        // Security tokens and device tokens revoked & addresses deleted & newsletter unsubscribed
        $this->assertEquals(0, $this->customer->addresses()->count());
        $this->assertEquals(0, $this->customer->deviceTokens()->count());
        $this->assertEquals(0, $this->customer->tokens()->count());
        $this->assertEquals(0, NewsletterSubscriber::where('email', 'sourav@example.com')->count());

        // Past order and financial integrity preserved!
        $this->assertDatabaseHas('orders', [
            'id' => $pastOrder->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-DEL-PAST01',
            'total_amount' => 650,
        ]);

        // Deletion request completed
        $req->refresh();
        $this->assertEquals(AccountDeletionRequest::STATUS_COMPLETED, $req->status);
        $this->assertNotNull($req->completed_at);
        $this->assertEquals($this->admin->id, $req->reviewed_by);
    }

    public function test_api_v1_account_deletion_flow_for_mobile_apps(): void
    {
        $token = $this->customer->createToken('test_mobile_token')->plainTextToken;

        // 1. Submit deletion request from mobile API
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/account/delete-request', [
                'reason' => 'privacy_concerns',
                'message' => 'Requested via Android application settings.',
                'confirmation' => true,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'status' => 'pending',
            ])
            ->assertJsonStructure([
                'reference_number',
                'web_url',
            ]);

        $ref = $response->json('reference_number');

        // 2. Query deletion request status via API
        $statusResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/account/delete-request');

        $statusResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'has_pending_request' => true,
                'latest_request' => [
                    'reference_number' => $ref,
                    'status' => 'pending',
                    'reason' => 'privacy_concerns',
                ],
            ]);

        // 3. Submitting duplicate from mobile returns 200 with existing reference
        $dupResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/account/delete-request', [
                'reason' => 'privacy_concerns',
            ]);

        $dupResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'reference_number' => $ref,
            ]);
    }
}
