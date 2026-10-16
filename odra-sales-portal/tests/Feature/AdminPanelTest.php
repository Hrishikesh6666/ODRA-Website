<?php

namespace Tests\Feature;

use App\Models\Enhancement;
use App\Models\FreightRule;
use App\Models\SweeperModel;
use App\Models\User;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        User::where('email', 'michael.scott@odrasweeper.com')->forceDelete();
        User::where('email', 'dwight.schrute@odrasweeper.com')->forceDelete();
        Enhancement::where('description', '360 Bird-Eye Camera System')->forceDelete();
    }

    protected function getAdmin(): User
    {
        return User::where('email', 'it.support@challengermfg.net')->first();
    }

    protected function getSalesRep(): User
    {
        return User::where('email', 'john.wells@odrasweeper.com')->first();
    }

    public function test_sales_rep_cannot_access_any_admin_route(): void
    {
        $rep = $this->getSalesRep();

        $routes = [
            '/admin/dashboard',
            '/admin/models',
            '/admin/enhancements',
            '/admin/freight',
            '/admin/users',
            '/admin/audit',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($rep)->get($route);
            $response->assertStatus(403);
        }
    }

    public function test_admin_can_view_dashboard_and_catalog(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Headquarters Overview');

        $response = $this->actingAs($admin)->get('/admin/models');
        $response->assertStatus(200);
        $response->assertSee('MT4H');
        $response->assertSee('MS5H');
    }

    public function test_admin_can_update_model_pricing(): void
    {
        $admin = $this->getAdmin();
        $model = SweeperModel::where('code', 'MT4H')->first();

        $response = $this->actingAs($admin)->put("/admin/models/{$model->id}", [
            'category' => 'MECHANICAL BROOM SWEEPER',
            'description' => 'Updated Description Test',
            'msrp' => 260000,
            'sw_list' => 310000,
            'sw_net' => 275000,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.models.index'));
        $this->assertDatabaseHas('model_prices', [
            'sweeper_model_id' => $model->id,
            'msrp' => 260000,
            'sw_net' => 275000,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'MODEL_UPDATED',
        ]);

        // Restore original MT4H pricing so subsequent feature tests remain isolated
        $this->actingAs($admin)->put("/admin/models/{$model->id}", [
            'category' => 'MECHANICAL BROOM SWEEPER',
            'description' => $model->description,
            'msrp' => 257493,
            'sw_list' => 307378,
            'sw_net' => 270493,
            'is_active' => 1,
        ]);
    }

    public function test_admin_can_add_and_toggle_enhancement(): void
    {
        $admin = $this->getAdmin();
        $model = SweeperModel::where('code', 'MT4H')->first();

        $response = $this->actingAs($admin)->post('/admin/enhancements', [
            'sweeper_model_id' => $model->id,
            'category' => 'SAFETY',
            'description' => '360 Bird-Eye Camera System',
            'msrp' => 3500,
            'sw_list' => 3800,
            'sw_net' => 3600,
        ]);

        $response->assertRedirect(route('admin.enhancements.index'));
        $this->assertDatabaseHas('enhancements', [
            'description' => '360 Bird-Eye Camera System',
            'category' => 'SAFETY',
        ]);

        $enh = Enhancement::where('description', '360 Bird-Eye Camera System')->first();
        $this->actingAs($admin)->post("/admin/enhancements/{$enh->id}/toggle");
        $enh->refresh();
        $this->assertFalse($enh->is_active);
    }

    public function test_admin_can_update_freight_rates(): void
    {
        $admin = $this->getAdmin();
        $rule = FreightRule::first();

        $response = $this->actingAs($admin)->put("/admin/freight/{$rule->id}", [
            'customer_rate' => 7500,
            'municipality_rate' => 7900,
        ]);

        $response->assertRedirect(route('admin.freight.index'));
        $this->assertDatabaseHas('freight_rules', [
            'id' => $rule->id,
            'customer_rate' => 7500,
            'municipality_rate' => 7900,
        ]);
    }

    public function test_admin_can_register_new_sales_representative(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Michael Scott',
            'email' => 'michael.scott@odrasweeper.com',
            'company_id' => 'REP099',
            'rep_code' => 'MS',
            'territory' => 'Scranton, PA',
            'password' => 'OdraSecure2026!',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'michael.scott@odrasweeper.com',
            'rep_code' => 'MS',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_register_new_administrator(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->post('/admin/users', [
            'role_type' => 'admin',
            'name' => 'Dwight Schrute',
            'email' => 'dwight.schrute@odrasweeper.com',
            'company_id' => 'ADMIN002',
            'password' => 'BeetsBears2026!',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'dwight.schrute@odrasweeper.com',
            'role_id' => 1,
            'is_active' => true,
        ]);
    }

    public function test_admin_cannot_deactivate_or_remove_themselves(): void
    {
        $admin = $this->getAdmin();

        // Attempt to deactivate self
        $toggleResponse = $this->actingAs($admin)->post("/admin/users/{$admin->id}/toggle");
        $toggleResponse->assertRedirect(route('admin.users.index'));
        $toggleResponse->assertSessionHas('error');
        $admin->refresh();
        $this->assertTrue($admin->is_active);

        // Attempt to remove self
        $deleteResponse = $this->actingAs($admin)->delete("/admin/users/{$admin->id}");
        $deleteResponse->assertRedirect(route('admin.users.index'));
        $deleteResponse->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_can_remove_another_administrator(): void
    {
        $admin = $this->getAdmin();

        // Create second admin
        $secondAdmin = User::create([
            'name' => 'Dwight Schrute',
            'email' => 'dwight.schrute@odrasweeper.com',
            'role_id' => 1,
            'is_active' => true,
            'password' => bcrypt('password123'),
        ]);

        $deleteResponse = $this->actingAs($admin)->delete("/admin/users/{$secondAdmin->id}");
        $deleteResponse->assertRedirect(route('admin.users.index'));
        $deleteResponse->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $secondAdmin->id]);
    }

    public function test_enhancements_page_renders_clean_user_friendly_pagination(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->get('/admin/enhancements');
        $response->assertStatus(200);
        $response->assertSee('portal-pagination');
        $response->assertSee('pagination-btn');
        $response->assertDontSee('w-5 h-5');
    }
}
