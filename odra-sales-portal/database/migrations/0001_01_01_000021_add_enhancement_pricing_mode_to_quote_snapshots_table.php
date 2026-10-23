<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quote_snapshots', function (Blueprint $table) {
            $table->string('enhancement_pricing_mode', 30)->default('itemized')->after('pricing_basis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quote_snapshots', function (Blueprint $table) {
            $table->dropColumn('enhancement_pricing_mode');
        });
    }
};
