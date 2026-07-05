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
        Schema::create('phan_cong_co_vans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('lop_id')->constrained('lops')->onDelete('cascade');
            $table->foreignId('hoc_ky_id')->constrained('hoc_kys')->onDelete('cascade');
            $table->timestamps();

            // A class can only have one advisor assigned per semester
            $table->unique(['lop_id', 'hoc_ky_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phan_cong_co_vans');
    }
};
