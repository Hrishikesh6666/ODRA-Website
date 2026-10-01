<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ModelConfiguration;
use App\Models\SweeperModel;

class ConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $mt4h = SweeperModel::where('code', 'MT4H')->first();
        $ms5h = SweeperModel::where('code', 'MS5H')->first();

        if ($mt4h) {
            ModelConfiguration::updateOrCreate(
                ['label' => 'MT4H - Isuzu NRR cabover, 19,500 GVWR, Class 5, non-CDL - LEFT side dumping hopper'],
                ['sweeper_model_id' => $mt4h->id, 'short_label' => 'MT4H Isuzu cabover, Class 5, non-CDL - LHD']
            );
            ModelConfiguration::updateOrCreate(
                ['label' => 'MT4H - Isuzu NRR cabover, 19,500 GVWR, Class 5, non-CDL - RIGHT side dumping hopper'],
                ['sweeper_model_id' => $mt4h->id, 'short_label' => 'MT4H Isuzu cabover, Class 5, non-CDL - RHD']
            );
            ModelConfiguration::updateOrCreate(
                ['label' => 'MT4H - Ford F-550XL conventional, 19,000 GVWR, non-CDL - LEFT side dumping hopper'],
                ['sweeper_model_id' => $mt4h->id, 'short_label' => 'MT4H Ford F-550XL, non-CDL - LHD']
            );
        }

        if ($ms5h) {
            ModelConfiguration::updateOrCreate(
                ['label' => 'MS5H - Conventional Class 6, 25,500 GVWR, 148" WB, 300HP Cummins, non-CDL'],
                ['sweeper_model_id' => $ms5h->id, 'short_label' => 'MS5H Conventional Class 6, non-CDL']
            );
            ModelConfiguration::updateOrCreate(
                ['label' => 'MS5H - Conventional Class 7, CDL-required configuration, 300HP Cummins - no price difference'],
                ['sweeper_model_id' => $ms5h->id, 'short_label' => 'MS5H Conventional Class 7, CDL-required']
            );
        }
    }
}
