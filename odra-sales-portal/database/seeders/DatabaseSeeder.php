<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            SweeperModelSeeder::class,
            ConfigurationSeeder::class,
            PricingBasisSeeder::class,
            ModelPriceSeeder::class,
            EnhancementSeeder::class,
            FreightSeeder::class,
            TerritorySeeder::class,
        ]);
    }
}
