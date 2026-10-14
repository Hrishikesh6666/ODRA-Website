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
        // 1. Add discount_percent and description_template to pricing_bases
        if (Schema::hasTable('pricing_bases')) {
            Schema::table('pricing_bases', function (Blueprint $table) {
                if (! Schema::hasColumn('pricing_bases', 'discount_percent')) {
                    $table->decimal('discount_percent', 5, 2)->default(0.00)->after('price_field');
                }
                if (! Schema::hasColumn('pricing_bases', 'description_template')) {
                    $table->text('description_template')->nullable()->after('subtotal_label');
                }
            });
        }

        // 2. Add price to model_configurations
        if (Schema::hasTable('model_configurations')) {
            Schema::table('model_configurations', function (Blueprint $table) {
                if (! Schema::hasColumn('model_configurations', 'price')) {
                    $table->decimal('price', 10, 2)->default(0.00)->after('short_label');
                }
            });
        }

        // 3. Add discount_percent to enhancements
        if (Schema::hasTable('enhancements')) {
            Schema::table('enhancements', function (Blueprint $table) {
                if (! Schema::hasColumn('enhancements', 'discount_percent')) {
                    $table->decimal('discount_percent', 5, 2)->nullable()->after('msrp');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('pricing_bases')) {
            Schema::table('pricing_bases', function (Blueprint $table) {
                $columns = [];
                if (Schema::hasColumn('pricing_bases', 'discount_percent')) {
                    $columns[] = 'discount_percent';
                }
                if (Schema::hasColumn('pricing_bases', 'description_template')) {
                    $columns[] = 'description_template';
                }
                if (! empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }

        if (Schema::hasTable('model_configurations')) {
            Schema::table('model_configurations', function (Blueprint $table) {
                if (Schema::hasColumn('model_configurations', 'price')) {
                    $table->dropColumn('price');
                }
            });
        }

        if (Schema::hasTable('enhancements')) {
            Schema::table('enhancements', function (Blueprint $table) {
                if (Schema::hasColumn('enhancements', 'discount_percent')) {
                    $table->dropColumn('discount_percent');
                }
            });
        }
    }
};
