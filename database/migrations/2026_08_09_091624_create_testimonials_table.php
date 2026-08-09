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
    Schema::create('testimonials', function (Blueprint $table) {
        $table->id();
        $table->string('customer_name');
        $table->text('content');
        $table->integer('rating')->default(5); // Rating bintang 1-5
        $table->boolean('is_featured')->default(true); // Untuk ditampilkan di Landing Page
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
