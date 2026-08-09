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
    Schema::create('banners', function (Blueprint $table) {
        $table->id();
        $table->string('title')->nullable(); // Judul opsional
        $table->string('image_path'); // Path file gambar
        $table->string('link_url')->nullable(); // Link tujuan saat banner diklik
        $table->boolean('is_active')->default(true); // Status aktif/tidak
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
