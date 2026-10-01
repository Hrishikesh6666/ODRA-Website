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
        Schema::create('enhancements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sweeper_model_id')->constrained('sweeper_models')->onDelete('restrict');
            $table->string('category', 50);
            $table->text('description');
            $table->decimal('msrp', 10, 2);
            $table->decimal('sw_list', 10, 2);
            $table->decimal('sw_net', 10, 2);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->softDeletes();
            $table->timestamps();
            
            $table->index(['sweeper_model_id', 'category', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enhancements');
    }
};
