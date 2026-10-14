<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Enhancement;
use App\Models\SweeperModel;

class EnhancementSeeder extends Seeder
{
    public function run(): void
    {
        $mt4h = SweeperModel::where('code', 'MT4H')->first();
        $ms5h = SweeperModel::where('code', 'MS5H')->first();

        $mt4h_enhancements = [
            ['category' => 'LIGHTS', 'description' => 'Front LED Strobe Light (behind cab, visible from front)', 'msrp' => 529, 'sw_list' => 567, 'sw_net' => 556],
            ['category' => 'LIGHTS', 'description' => 'Rear Mounted LED Light Bar', 'msrp' => 2293, 'sw_list' => 2504, 'sw_net' => 2454],
            ['category' => 'LIGHTS', 'description' => "Rear LED Strobe Light, curbside (add'l to std street-side)", 'msrp' => 529, 'sw_list' => 567, 'sw_net' => 556],
            ['category' => 'BROOMS', 'description' => 'Strip Main Broom ILO Standard', 'msrp' => 746, 'sw_list' => 761, 'sw_net' => 746],
            ['category' => 'WATER', 'description' => 'Elevator System Flush', 'msrp' => 1405, 'sw_list' => 1505, 'sw_net' => 1475],
            ['category' => 'HOPPER', 'description' => 'Stainless Steel Hopper ILO Standard', 'msrp' => 13000, 'sw_list' => 13929, 'sw_net' => 13650],
            ['category' => 'MANUALS', 'description' => "Add'l Operator & Service Manual", 'msrp' => 235, 'sw_list' => 240, 'sw_net' => 240],
            ['category' => 'PAINT', 'description' => 'Paint Surcharge - Module (RAL No. required)', 'msrp' => 1913, 'sw_list' => 2347, 'sw_net' => 2300],
            ['category' => 'PAINT', 'description' => 'Paint Surcharge - Chassis (RAL No. required)', 'msrp' => 9375, 'sw_list' => 9694, 'sw_net' => 9500],
            ['category' => 'STEERING', 'description' => 'Dualization for MT4H Chassis', 'msrp' => 20400, 'sw_list' => 20816, 'sw_net' => 20400],
            ['category' => 'WARRANTY', 'description' => '2nd Year Warranty Parts/Labor - Module Only (excl. aux engine)', 'msrp' => 6106, 'sw_list' => 6231, 'sw_net' => 6106],
            ['category' => 'WARRANTY', 'description' => '3rd Year Warranty Parts/Labor - Module Only (excl. aux engine)', 'msrp' => 11554, 'sw_list' => 11790, 'sw_net' => 11554],
            ['category' => 'DRAG SHOES', 'description' => 'Pivot Drag Shoe Arms ILO Standard', 'msrp' => 998, 'sw_list' => 1018, 'sw_net' => 998],
        ];

        $ms5h_enhancements = [
            ['category' => 'LIGHTS', 'description' => 'Front LED Strobe Light (behind cab, visible from front)', 'msrp' => 529, 'sw_list' => 567, 'sw_net' => 556],
            ['category' => 'LIGHTS', 'description' => 'Rear Mounted LED Light Bar', 'msrp' => 2293, 'sw_list' => 2504, 'sw_net' => 2454],
            ['category' => 'LIGHTS', 'description' => "Rear LED Strobe Light, curbside (add'l to std street-side)", 'msrp' => 529, 'sw_list' => 567, 'sw_net' => 556],
            ['category' => 'BROOMS', 'description' => 'Strip Main Broom ILO Standard', 'msrp' => 746, 'sw_list' => 761, 'sw_net' => 746],
            ['category' => 'WATER', 'description' => 'Elevator System Flush', 'msrp' => 1405, 'sw_list' => 1505, 'sw_net' => 1475],
            ['category' => 'MANUALS', 'description' => "Add'l Operator & Service Manual", 'msrp' => 235, 'sw_list' => 240, 'sw_net' => 240],
            ['category' => 'STEERING', 'description' => 'Dualized Conversion on Chassis', 'msrp' => 19519, 'sw_list' => 19918, 'sw_net' => 19519],
            ['category' => 'WARRANTY', 'description' => '2nd Year Warranty Parts/Labor - Module Only (excl. chassis)', 'msrp' => 6106, 'sw_list' => 6231, 'sw_net' => 6106],
            ['category' => 'WARRANTY', 'description' => '3rd Year Warranty Parts/Labor - Module Only (excl. chassis)', 'msrp' => 11554, 'sw_list' => 11790, 'sw_net' => 11554],
            ['category' => 'DRAG SHOES', 'description' => 'Pivot Drag Shoe Arms ILO Standard', 'msrp' => 998, 'sw_list' => 1018, 'sw_net' => 998],
        ];

        if ($mt4h) {
            foreach ($mt4h_enhancements as $enhancement) {
                if (!isset($enhancement['discount_percent'])) {
                    $swList = (float) $enhancement['sw_list'];
                    $swNet = (float) $enhancement['sw_net'];
                    $enhancement['discount_percent'] = ($swList > 0 && $swList >= $swNet)
                        ? round((1 - ($swNet / $swList)) * 100, 1)
                        : 0.0;
                }
                Enhancement::updateOrCreate(
                    ['sweeper_model_id' => $mt4h->id, 'description' => $enhancement['description']],
                    $enhancement
                );
            }
        }

        if ($ms5h) {
            foreach ($ms5h_enhancements as $enhancement) {
                if (!isset($enhancement['discount_percent'])) {
                    $swList = (float) $enhancement['sw_list'];
                    $swNet = (float) $enhancement['sw_net'];
                    $enhancement['discount_percent'] = ($swList > 0 && $swList >= $swNet)
                        ? round((1 - ($swNet / $swList)) * 100, 1)
                        : 0.0;
                }
                Enhancement::updateOrCreate(
                    ['sweeper_model_id' => $ms5h->id, 'description' => $enhancement['description']],
                    $enhancement
                );
            }
        }
    }
}
