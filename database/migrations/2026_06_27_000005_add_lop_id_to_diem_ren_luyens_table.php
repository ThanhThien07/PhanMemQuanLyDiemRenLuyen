<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('diem_ren_luyens', function (Blueprint $table) {
            $table->foreignId('lop_id')->nullable()->after('hoc_ky_id')->constrained('lops')->onDelete('set null');
        });

        // Backfill existing diem_ren_luyens with their student's current class
        $drls = DB::table('diem_ren_luyens')->get();
        foreach ($drls as $drl) {
            $sv = DB::table('sinh_viens')->where('id', $drl->sinh_vien_id)->first();
            if ($sv) {
                DB::table('diem_ren_luyens')
                    ->where('id', $drl->id)
                    ->update(['lop_id' => $sv->lop_id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diem_ren_luyens', function (Blueprint $table) {
            $table->dropForeign(['lop_id']);
            $table->dropColumn('lop_id');
        });
    }
};
