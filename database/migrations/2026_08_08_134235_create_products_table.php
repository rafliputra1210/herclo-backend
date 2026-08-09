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
        $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
        $table->string('name');
        $table->string('slug')->unique();
        $table->text('description');
        $table->decimal('price', 15, 2); // Harga reguler
        $table->decimal('reseller_price', 15, 2)->nullable(); // Harga khusus reseller
        $table->integer('stock_quantity');
        $table->boolean('is_flash_sale')->default(false);
        $table->timestamps();
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
