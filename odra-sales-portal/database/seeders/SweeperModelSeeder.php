<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SweeperModel;

class SweeperModelSeeder extends Seeder
{
    public function run(): void
    {
        SweeperModel::updateOrCreate(
            ['code' => 'MT4H'],
            [
                'description' => 'MT4H Mechanical Broom Sweeper is a dual engine Tier 4-Final, 65HP auxiliary engine sweeper with a 4-cubic yard hopper, mounted on a non-CDL, 109" WB Class 6 cabover commercial truck chassis with 4-wheel disc brakes, and full LED chassis lighting. The water spray system features a 220-gallon tank and 4 spray bars. The hopper features a variable height discharge up to 10-ft. Standard paint color is white with ODRA branding and fully built with standard features noted below. Left-side dump sweeper. Chassis is a brand new 2027MY Isuzu NRR, 19,500 GVWR.',
                'category' => 'MECHANICAL BROOM SWEEPER',
                'is_active' => true
            ]
        );

        SweeperModel::updateOrCreate(
            ['code' => 'MS5H'],
            [
                'description' => 'MS5H Mechanical Broom Sweeper is a single engine sweeper with a 5-cubic yard hopper, mounted on a non-CDL, 148" WB Class 6 commercial truck chassis with a 300HP Cummins engine with air brakes and full LED chassis lighting. The water spray system features a 355-gallon tank and 4 spray bars. The hopper features a variable height discharge up to 10-ft. Standard paint color is white and red with ODRA branding and fully built with standard features noted below. Left-side dump sweeper. Chassis is a brand new 2027MY Freightliner M2 Plus, 26,000 GVWR.',
                'category' => 'MECHANICAL BROOM SWEEPER',
                'is_active' => true
            ]
        );
    }
}
