<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            DB::statement('ALTER TABLE orders DROP FOREIGN KEY orders_user_id_foreign');
            DB::statement('ALTER TABLE orders MODIFY user_id bigint unsigned NULL');
            DB::statement('ALTER TABLE orders ADD CONSTRAINT orders_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            DB::statement('ALTER TABLE orders DROP FOREIGN KEY orders_user_id_foreign');
            DB::statement('ALTER TABLE orders MODIFY user_id bigint unsigned NOT NULL');
            DB::statement('ALTER TABLE orders ADD CONSTRAINT orders_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
        });
    }
};
