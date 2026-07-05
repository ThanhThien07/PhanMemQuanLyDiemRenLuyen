<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DiemDanh extends Model {
    protected $table = "diem_danhs";
    protected $fillable = ["dang_ky_hoat_dong_id", "check_in_time", "check_out_time", "trang_thai"];
    public function dangKyHoatDong() { return $this->belongsTo(DangKyHoatDong::class, "dang_ky_hoat_dong_id"); }
}