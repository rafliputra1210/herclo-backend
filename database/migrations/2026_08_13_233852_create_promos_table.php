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
    Schema::create('promos', function (Blueprint $table) {
        $table->id();
        $table->string('code')->unique(); // Contoh: HERCLO50
        $table->enum('type', ['nominal', 'persen']); // Potongan harga tetap atau persentase
        $table->decimal('value', 15, 2); // Nilai diskonnya (misal 50000 atau 10%)
        $table->decimal('min_purchase', 15, 2)->default(0); // Minimal belanja
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promos');
    }
};
