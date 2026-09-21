<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register_with_valid_details(): void
    {
        Country::firstOrCreate(
            ['code' => 'IN'],
            ['name' => 'India', 'phone_code' => '+91', 'currency_code' => 'INR', 'is_active' => true]
        );

        $response = $this->post(route('account.register'), [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'password123',
            'country_code' => 'IN',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'testuser@example.com',
            'role' => 'customer',
        ]);
    }

    public function test_customer_can_login_with_valid_credentials(): void
    {
        $user = User::create([
            'name' => 'Existing Customer',
            'email' => 'customer@example.com',
            'password' => Hash::make('password123'),
            'role' => 'customer',
        ]);

        $response = $this->post(route('account.login.submit'), [
            'email' => 'customer@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_customer_cannot_login_with_invalid_password(): void
    {
        User::create([
            'name' => 'Existing Customer',
            'email' => 'customer@example.com',
            'password' => Hash::make('password123'),
            'role' => 'customer',
        ]);

        $response = $this->post(route('account.login.submit'), [
            'email' => 'customer@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_customer_can_logout(): void
    {
        $user = User::create([
            'name' => 'Log Out Customer',
            'email' => 'logout@example.com',
            'password' => Hash::make('password123'),
            'role' => 'customer',
        ]);

        $this->actingAs($user);
        $this->assertAuthenticatedAs($user);

        $response = $this->post(route('account.logout'));
        $response->assertRedirect(route('home'));
        $this->assertGuest();
    }
}

