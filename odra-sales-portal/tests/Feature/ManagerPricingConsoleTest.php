<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Enhancement;
use App\Models\FreightRule;
use App\Models\ModelPrice;
use App\Models\SweeperModel;
use App\Models\User;
use Tests\TestCase;

class ManagerPricingConsoleTest extends TestCase
{
    public function test_manager_and_admin_can_access_master_pricing_console_while_sales_rep_is_forbidden(): void
    {
        $manager = User::where('email', 'cc@odrasweeper.com')->first();
        $admin = User::whereHas('role', fn ($q) => $q->where('slug', 'admin'))->first();
        $salesRep = User::where('email', 'john.wells@odrasweeper.com')->first();

        // Manager can view Master Pricing Console
        $this->actingAs($manager)
            ->get(route('manager.pricing.index'))
            ->assertStatus(200)
            ->assertSee('Master Pricing Console')
            ->assertSee('MT4H')
            ->assertSee('MS5H');

        // Admin can view Master Pricing Console directly
        $this->actingAs($admin)
            ->get(route('manager.pricing.index'))
            ->assertStatus(200)
            ->assertSee('Master Pricing Console');

        // Admin /admin/pricing route redirects smoothly to manager.pricing.index
        $this->actingAs($admin)
            ->get('/admin/pricing')
            ->assertRedirect(route('manager.pricing.index'));

        // Sales representative is strictly forbidden from pricing console
        $this->actingAs($salesRep)
            ->get(route('manager.pricing.index'))
            ->assertStatus(403);
    }

    public function test_manager_can_update_base_equipment_pricing_with_audit_trail_and_live_catalog_sync(): void
    {
        $manager = User::where('email', 'cc@odrasweeper.com')->first();
        $salesRep = User::where('email', 'john.wells@odrasweeper.com')->first();
        $model = SweeperModel::where('code', 'MT4H')->first();

        $updateResponse = $this->actingAs($manager)
            ->put(route('manager.pricing.models.update', $model), [
                'msrp' => 260500,
                'sw_list' => 312000,
                'sw_net' => 275000,
            ]);

        $updateResponse->assertRedirect();
        $updateResponse->assertSessionHas('success');

        // Check model price created
        $newPrice = ModelPrice::where('sweeper_model_id', $model->id)
            ->current()
            ->latest('effective_from')
            ->first();

        $this->assertEquals(260500, (float) $newPrice->msrp);
        $this->assertEquals(312000, (float) $newPrice->sw_list);
        $this->assertEquals(275000, (float) $newPrice->sw_net);

        // Check audit log
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $manager->id,
            'action' => 'MANAGER_PRICE_UPDATED',
            'entity_type' => 'sweeper_model',
            'entity_id' => $model->id,
        ]);

        // Quote Builder reflects the updated price immediately
        $builderResp = $this->actingAs($salesRep)->get('/sales/quotes/create');
        $builderResp->assertStatus(200);
        $builderResp->assertSee('260500');
        $builderResp->assertSee('312000');
        $builderResp->assertSee('275000');
    }

    public function test_admin_can_update_enhancement_pricing_and_freight_rates(): void
    {
        $admin = User::whereHas('role', fn ($q) => $q->where('slug', 'admin'))->first();
        $enhancement = Enhancement::first();
        $freight = FreightRule::first();

        // 1. Update enhancement pricing
        $enhResp = $this->actingAs($admin)
            ->put(route('manager.pricing.enhancements.update', $enhancement), [
                'msrp' => 650,
                'sw_list' => 700,
                'sw_net' => 630,
            ]);

        $enhResp->assertRedirect();
        $enhResp->assertSessionHas('success');

        $enhancement->refresh();
        $this->assertEquals(650, (float) $enhancement->msrp);
        $this->assertEquals(700, (float) $enhancement->sw_list);
        $this->assertEquals(630, (float) $enhancement->sw_net);

        // 2. Update freight rates
        $freightResp = $this->actingAs($admin)
            ->put(route('manager.pricing.freight.update', $freight), [
                'customer_rate' => 6250,
                'municipality_rate' => 6550,
            ]);

        $freightResp->assertRedirect();
        $freightResp->assertSessionHas('success');

        $freight->refresh();
        $this->assertEquals(6250, (float) $freight->customer_rate);
        $this->assertEquals(6550, (float) $freight->municipality_rate);

        // Audit log exists
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'FREIGHT_RULE_UPDATED',
            'entity_id' => $freight->id,
        ]);
    }

    public function test_manager_can_update_model_and_enhancement_pricing_with_discount_percentage(): void
    {
        $manager = User::where('email', 'cc@odrasweeper.com')->first();
        $model = SweeperModel::where('code', 'MS5H')->first();
        $enhancement = Enhancement::first();

        // 1. Update model price by providing MSRP, List, and Discount % (15%)
        $modelResp = $this->actingAs($manager)
            ->put(route('manager.pricing.models.update', $model), [
                'msrp' => 350000,
                'sw_list' => 400000,
                'discount_percent' => 15,
            ]);

        $modelResp->assertRedirect();
        $modelResp->assertSessionHas('success');

        $newPrice = ModelPrice::where('sweeper_model_id', $model->id)->current()->first();
        $this->assertEquals(350000, (float) $newPrice->msrp);
        $this->assertEquals(400000, (float) $newPrice->sw_list);
        $this->assertEquals(340000, (float) $newPrice->sw_net); // 400,000 * (1 - 0.15) = 340,000

        // 2. Update enhancement price by providing MSRP, List, and Discount % (20%)
        $enhResp = $this->actingAs($manager)
            ->put(route('manager.pricing.enhancements.update', $enhancement), [
                'msrp' => 1000,
                'sw_list' => 1200,
                'discount_percent' => 20,
            ]);

        $enhResp->assertRedirect();
        $enhResp->assertSessionHas('success');

        $enhancement->refresh();
        $this->assertEquals(1000, (float) $enhancement->msrp);
        $this->assertEquals(1200, (float) $enhancement->sw_list);
        $this->assertEquals(20, (float) $enhancement->discount_percent);
        $this->assertEquals(960, (float) $enhancement->sw_net); // 1,200 * (1 - 0.20) = 960
    }
}
