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
        Schema::table('territories', function (Blueprint $table) {
            if (!Schema::hasColumn('territories', 'channel')) {
                $table->string('channel', 50)->nullable()->after('cities');
            }
            if (!Schema::hasColumn('territories', 'dealer_name')) {
                $table->string('dealer_name', 150)->nullable()->after('channel');
            }
            if (!Schema::hasColumn('territories', 'region')) {
                $table->string('region', 50)->nullable()->after('dealer_name');
            }
            if (!Schema::hasColumn('territories', 'country')) {
                $table->string('country', 50)->nullable()->after('region');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('territories', function (Blueprint $table) {
            $table->dropColumn(['channel', 'dealer_name', 'region', 'country']);
        });
    }
};
