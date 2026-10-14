<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Calculate and backfill discount_percent for all enhancements
        $enhancements = DB::table('enhancements')->get();
        foreach ($enhancements as $enh) {
            $swList = (float) $enh->sw_list;
            $swNet = (float) $enh->sw_net;
            if ($enh->discount_percent === null && $swList > 0 && $swNet >= 0 && $swList >= $swNet) {
                $disc = round((1 - ($swNet / $swList)) * 100, 1);
                DB::table('enhancements')->where('id', $enh->id)->update([
                    'discount_percent' => $disc,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
