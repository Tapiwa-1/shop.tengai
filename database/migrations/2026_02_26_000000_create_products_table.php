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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('brand')->nullable();
            $table->string('asin')->unique()->nullable();
            $table->string('sku')->nullable();
            $table->string('currency')->default('USD');
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('compare_at_price', 10, 2)->nullable();
            $table->string('category')->nullable();
            $table->decimal('rating', 3, 2)->nullable();
            $table->unsignedInteger('review_count')->default(0);
            $table->string('availability')->nullable();
            $table->text('source_url')->nullable();
            $table->json('attributes')->nullable();
            $table->json('bullet_points')->nullable();
            $table->json('images')->nullable();
            $table->timestamps();

            $table->index(['brand', 'category']);
            $table->index('availability');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
