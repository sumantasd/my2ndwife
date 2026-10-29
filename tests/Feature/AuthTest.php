<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register_successfully(): void
    {
        $response = $this->post(route('register.post'), [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'phone' => '9876543210',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'email' => 'johndoe@example.com',
            'role' => User::ROLE_CUSTOMER,
        ]);
    }

    public function test_customer_registration_requires_valid_data(): void
    {
        $response = $this->post(route('register.post'), [
            'name' => '',
            'email' => 'invalid-email',
            'phone' => '',
            'password' => '123',
            'password_confirmation' => 'mismatch',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertGuest();
    }

    public function test_customer_can_login_successfully(): void
    {
        $user = User::factory()->create([
            'email' => 'customer@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $response = $this->post(route('login.post'), [
            'email' => 'customer@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_user_is_redirected_to_admin_dashboard_on_login(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this->post(route('login.post'), [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_customer_is_forbidden_from_accessing_admin_routes(): void
    {
        $customer = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
        ]);

        $response = $this->actingAs($customer)->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    public function test_super_admin_can_access_admin_dashboard(): void
    {
        $superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $response = $this->actingAs($superAdmin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Live Dashboard');
    }

    public function test_customer_can_login_using_registered_email_address(): void
    {
        $user = User::factory()->create([
            'email' => 'customer.email@example.com',
            'phone' => '+91 9830012345',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $response = $this->post(route('login.post'), [
            'login' => 'customer.email@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_customer_can_login_using_registered_mobile_number_in_various_formats(): void
    {
        $user = User::factory()->create([
            'email' => 'phone.customer@example.com',
            'phone' => '+91 9876543210',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        // 1. Raw 10 digits
        $response1 = $this->post(route('login.post'), [
            'login' => '9876543210',
            'password' => 'password123',
        ]);
        $response1->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);

        Auth::logout();

        // 2. Formatted with +91 and spaces
        $response2 = $this->post(route('login.post'), [
            'login' => '+91 9876543210',
            'password' => 'password123',
        ]);
        $response2->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);

        Auth::logout();

        // 3. Compact with +91
        $response3 = $this->post(route('login.post'), [
            'login' => '+919876543210',
            'password' => 'password123',
        ]);
        $response3->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_unregistered_email_or_mobile_number(): void
    {
        // Unregistered email
        $responseEmail = $this->post(route('login.post'), [
            'login' => 'unknown@example.com',
            'password' => 'somePassword123',
        ]);
        $responseEmail->assertSessionHasErrors(['login']);
        $this->assertGuest();

        // Unregistered mobile number
        $responsePhone = $this->post(route('login.post'), [
            'login' => '9899999999',
            'password' => 'somePassword123',
        ]);
        $responsePhone->assertSessionHasErrors(['login']);
        $this->assertGuest();
    }

    public function test_login_fails_with_invalid_mobile_format(): void
    {
        $response = $this->post(route('login.post'), [
            'login' => '12345',
            'password' => 'password123',
        ]);
        $response->assertSessionHasErrors(['login']);
        $this->assertGuest();
    }

    public function test_inactive_customer_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'inactive@example.com',
            'phone' => '+91 9876500000',
            'password' => Hash::make('password123'),
            'is_active' => false,
        ]);

        $response = $this->post(route('login.post'), [
            'login' => 'inactive@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['login']);
        $this->assertGuest();
    }

    public function test_registration_normalizes_default_91_country_code(): void
    {
        $response = $this->post(route('register.post'), [
            'name' => 'Kunal Roy',
            'email' => 'kunal@example.com',
            'phone' => '9830099887',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'email' => 'kunal@example.com',
            'phone' => '+91 9830099887',
            'role' => User::ROLE_CUSTOMER,
        ]);
    }

    public function test_registration_rejects_duplicate_mobile_number_across_different_formats(): void
    {
        User::factory()->create([
            'email' => 'existing@example.com',
            'phone' => '+91 9830012345',
        ]);

        // Attempt registering with raw 10-digits
        $response = $this->post(route('register.post'), [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'phone' => '9830012345',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertSessionHasErrors(['phone']);
        $this->assertGuest();
    }

    public function test_registration_rejects_invalid_indian_mobile_number(): void
    {
        // Less than 10 digits
        $responseShort = $this->post(route('register.post'), [
            'name' => 'Short Num',
            'email' => 'short@example.com',
            'phone' => '9830012',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);
        $responseShort->assertSessionHasErrors(['phone']);

        // Invalid starting digit (Indian mobiles start with 6, 7, 8, or 9)
        $responseInvalidPrefix = $this->post(route('register.post'), [
            'name' => 'Invalid Prefix',
            'email' => 'prefix@example.com',
            'phone' => '2234567890',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);
        $responseInvalidPrefix->assertSessionHasErrors(['phone']);
    }

    public function test_login_and_registration_pages_render_default_plus_91_country_code(): void
    {
        // Login page has single input labelled "Email Address or Mobile Number" and default +91 prefix
        $loginRes = $this->get(route('login'));
        $loginRes->assertStatus(200);
        $loginRes->assertSee('Email Address or Mobile Number');
        $loginRes->assertSee('+91');

        // Registration page has +91 prefix and Indian mobile format placeholder
        $regRes = $this->get(route('register'));
        $regRes->assertStatus(200);
        $regRes->assertSee('+91');
        $regRes->assertSee('Enter 10-digit mobile number');
        $regRes->assertSee('placeholder="Your Name"', false);
        $regRes->assertDontSee('placeholder="Sumanta Das"', false);
    }

    public function test_login_page_does_not_contain_quick_demo_login_section_or_demo_credentials(): void
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);

        // Entire demo login section and credentials must be completely absent
        $response->assertDontSee('Quick Demo Login', false);
        $response->assertDontSee('QUICK DEMO LOGIN', false);
        $response->assertDontSee('Super Admin (Email)');
        $response->assertDontSee('Customer (Mobile)');
        $response->assertDontSee('superadmin@my2ndwife.com');
        $response->assertDontSee('password123');
    }

    public function test_login_and_registration_pages_render_accessible_show_hide_password_toggles(): void
    {
        // Login page password toggle
        $loginRes = $this->get(route('login'));
        $loginRes->assertStatus(200);
        $loginRes->assertSee(':type="show ? \'text\' : \'password\'"', false);
        $loginRes->assertSee('Show password');
        $loginRes->assertSee('Hide password');

        // Registration page password and confirmation toggles
        $regRes = $this->get(route('register'));
        $regRes->assertStatus(200);
        $regRes->assertSee('id="register_password"', false);
        $regRes->assertSee('id="register_password_confirmation"', false);
        $regRes->assertSee('Show password');
        $regRes->assertSee('Hide password');
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('home'));
        $this->assertGuest();
    }
}
