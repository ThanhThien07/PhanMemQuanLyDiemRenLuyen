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
        Schema::table('hoc_kys', function (Blueprint $table) {
            $table->decimal('ti_le_ren_luyen', 5, 2)->default(80.00)->after('trang_thai');
            $table->decimal('ti_le_hoc_tap', 5, 2)->default(20.00)->after('ti_le_ren_luyen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hoc_kys', function (Blueprint $table) {
            $table->dropColumn(['ti_le_ren_luyen', 'ti_le_hoc_tap']);
        });
    }
};
