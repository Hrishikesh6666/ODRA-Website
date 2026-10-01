<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ModelPrice;
use App\Models\SweeperModel;
use Carbon\Carbon;

class ModelPriceSeeder extends Seeder
{
    public function run(): void
    {
        $mt4h = SweeperModel::where('code', 'MT4H')->first();
        $ms5h = SweeperModel::where('code', 'MS5H')->first();

        if ($mt4h) {
            ModelPrice::updateOrCreate(
                ['sweeper_model_id' => $mt4h->id],
                ['msrp' => 257493, 'sw_list' => 307378, 'sw_net' => 270493, 'effective_from' => Carbon::now()]
            );
        }
        if ($ms5h) {
            ModelPrice::updateOrCreate(
                ['sweeper_model_id' => $ms5h->id],
                ['msrp' => 349149, 'sw_list' => 411533, 'sw_net' => 362149, 'effective_from' => Carbon::now()]
            );
        }
    }
}
