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
        Schema::create('quote_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_version_id')->constrained('quote_versions')->onDelete('cascade');
            $table->string('item_type', 20);
            $table->foreignId('source_enhancement_id')->nullable()->constrained('enhancements')->onDelete('set null');
            $table->text('description');
            $table->string('category', 50);
            $table->integer('qty')->default(1);
            $table->decimal('msrp_at_time', 10, 2);
            $table->decimal('list_price_at_time', 10, 2);
            $table->decimal('unit_price_at_time', 10, 2);
            $table->decimal('extended_price', 12, 2);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['quote_version_id', 'item_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_items');
    }
};
