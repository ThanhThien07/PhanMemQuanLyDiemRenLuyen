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
        Schema::create('chi_tiet_diem_ren_luyens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diem_ren_luyen_id')->constrained('diem_ren_luyens')->onDelete('cascade');
            $table->string('ma_tieu_chi'); // e.g. 'I.1', 'I.2', 'II.1', etc.
            
            // Student inputs
            $table->decimal('diem_sv', 5, 2)->default(0.00);
            $table->text('ghi_chu_sv')->nullable();
            
            // Class monitor inputs
            $table->decimal('diem_bcs', 5, 2)->default(0.00);
            $table->text('ghi_chu_bcs')->nullable();
            
            // Academic advisor inputs
            $table->decimal('diem_cvht', 5, 2)->default(0.00);
            $table->text('ghi_chu_cvht')->nullable();
            
            $table->timestamps();
            
            // Add composite unique key to avoid duplicate entries for the same criterion on a record
            $table->unique(['diem_ren_luyen_id', 'ma_tieu_chi'], 'drl_tieu_chi_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chi_tiet_diem_ren_luyens');
    }
};
