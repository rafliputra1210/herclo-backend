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
    Schema::table('carts', function (Blueprint $table) {
        $table->string('size')->nullable()->after('quantity');
        $table->string('color')->nullable()->after('size');
    });

    Schema::table('order_items', function (Blueprint $table) {
        $table->string('size')->nullable()->after('price');
        $table->string('color')->nullable()->after('size');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts_and_orders', function (Blueprint $table) {
            //
        });
    }
};
