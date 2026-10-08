<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    public function test_unauthenticated_user_cannot_access_sales_dashboard(): void
    {
        $response = $this->get('/sales/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_unauthenticated_user_cannot_access_admin_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_sales_rep_cannot_access_admin_dashboard(): void
    {
        $salesRep = User::where('email', 'john.wells@odrasweeper.com')->first();

        $response = $this->actingAs($salesRep)->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::where('email', 'it.support@challengermfg.net')->first();

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Headquarters Overview');
    }

    public function test_sales_rep_can_access_sales_dashboard(): void
    {
        $salesRep = User::where('email', 'john.wells@odrasweeper.com')->first();

        $response = $this->actingAs($salesRep)->get('/sales/quotes');
        $response->assertStatus(200);
        $response->assertSee('My Quotations');
    }

    public function test_manager_can_access_manager_dashboard(): void
    {
        $manager = User::where('email', 'alex.byczynski@odrasweeper.com')->first();

        $response = $this->actingAs($manager)->get('/manager/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Commercial Overview');
    }

    public function test_sales_rep_cannot_access_manager_dashboard(): void
    {
        $salesRep = User::where('email', 'john.wells@odrasweeper.com')->first();

        $response = $this->actingAs($salesRep)->get('/manager/dashboard');
        $response->assertStatus(403);
    }

    public function test_technical_admin_cannot_access_manager_dashboard(): void
    {
        $admin = User::where('email', 'cc@odrasweeper.com')->first();

        $response = $this->actingAs($admin)->get('/manager/dashboard');
        $response->assertStatus(403);
    }

    public function test_manager_cannot_access_technical_admin_dashboard(): void
    {
        $manager = User::where('email', 'alex.byczynski@odrasweeper.com')->first();

        $response = $this->actingAs($manager)->get('/admin/dashboard');
        $response->assertStatus(403);
    }
}
