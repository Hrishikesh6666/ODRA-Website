<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    public function test_guest_is_redirected_to_login_page(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Representative Sign In');
        $response->assertSee('Sign in with Microsoft 365');
    }

    public function test_user_can_authenticate_using_email_and_password(): void
    {
        $admin = User::where('email', 'it.support@challengermfg.net')->first();

        $response = $this->post('/login', [
            'login' => 'it.support@challengermfg.net',
            'password' => 'OdraAdmin2026!',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_user_can_authenticate_using_company_id(): void
    {
        $admin = User::where('company_id', 'ADMIN001')->first();

        $response = $this->post('/login', [
            'login' => 'ADMIN001',
            'password' => 'OdraAdmin2026!',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_sales_rep_is_redirected_to_sales_dashboard(): void
    {
        $salesRep = User::where('email', 'john.wells@odrasweeper.com')->first();

        $response = $this->post('/login', [
            'login' => 'john.wells@odrasweeper.com',
            'password' => 'OdraSales2026!',
        ]);

        $this->assertAuthenticatedAs($salesRep);
        $response->assertRedirect(route('sales.dashboard'));
    }

    public function test_user_cannot_authenticate_with_invalid_password(): void
    {
        $response = $this->post('/login', [
            'login' => 'it.support@challengermfg.net',
            'password' => 'WrongPassword123!',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('login');
    }

    public function test_deactivated_user_cannot_login(): void
    {
        $user = User::where('email', 'carlos.silva@odrasweeper.com')->first();
        $user->update(['is_active' => false]);

        $response = $this->post('/login', [
            'login' => 'carlos.silva@odrasweeper.com',
            'password' => 'OdraSales2026!',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('login');

        // Restore active status
        $user->update(['is_active' => true]);
    }
}
