<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DangKyHoatDong extends Model {
    protected $table = "dang_ky_hoat_dongs";
    protected $fillable = ["sinh_vien_id", "hoat_dong_id", "trang_thai_dang_ky"];
    public function sinhVien() { return $this->belongsTo(SinhVien::class, "sinh_vien_id"); }
    public function hoatDong() { return $this->belongsTo(HoatDong::class, "hoat_dong_id"); }
    public function diemDanh() { return $this->hasOne(DiemDanh::class, "dang_ky_hoat_dong_id"); }
}