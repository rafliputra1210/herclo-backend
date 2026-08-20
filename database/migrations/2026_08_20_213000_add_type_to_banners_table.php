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
        if (!Schema::hasColumn('banners', 'type')) {
            Schema::table('banners', function (Blueprint $table) {
                $table->string('type')->default('hero')->after('is_active'); // 'hero' atau 'sub'
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('banners', 'type')) {
            Schema::table('banners', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }
};
