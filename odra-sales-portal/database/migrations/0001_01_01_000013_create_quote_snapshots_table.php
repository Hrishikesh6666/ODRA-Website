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
        Schema::create('quote_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_version_id')->unique()->constrained('quote_versions')->onDelete('cascade');
            $table->string('customer_company', 255);
            $table->string('customer_contact', 255)->nullable();
            $table->string('customer_address_1', 255)->nullable();
            $table->string('customer_address_2', 255)->nullable();
            $table->string('model_code', 24);
            $table->text('model_description');
            $table->string('chassis_configuration', 500);
            $table->string('pricing_basis', 100);
            $table->string('sales_rep_name', 255);
            $table->string('rep_territory', 100)->nullable();
            $table->string('po_number', 100)->nullable();
            $table->string('customer_account', 100)->nullable();
            $table->string('fob_terms', 255)->nullable();
            $table->integer('base_qty')->default(1);
            $table->decimal('base_msrp', 12, 2);
            $table->decimal('base_list_price', 12, 2);
            $table->decimal('base_unit_price', 12, 2);
            $table->decimal('show_discount_amount', 10, 2)->default(0);
            $table->decimal('freight_amount', 10, 2)->default(0);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('total', 12, 2);
            $table->string('quote_code', 50)->nullable();
            $table->json('enhancements_snapshot')->nullable();
            $table->longText('standard_features_html')->nullable();
            $table->longText('legal_terms_html')->nullable();
            $table->json('full_state')->nullable();
            $table->string('pdf_path', 500)->nullable();
            $table->timestamp('pdf_generated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_snapshots');
    }
};
