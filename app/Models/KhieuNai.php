<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class KhieuNai extends Model {
    protected $table = "khieu_nais";
    protected $fillable = ["sinh_vien_id", "hoat_dong_id", "ly_do", "minh_chung_id", "trang_thai_xu_ly", "phan_hoi", "nguoi_xu_ly_id"];
    public function sinhVien() { return $this->belongsTo(SinhVien::class, "sinh_vien_id"); }
    public function hoatDong() { return $this->belongsTo(HoatDong::class, "hoat_dong_id"); }
    public function minhChung() { return $this->belongsTo(MinhChung::class, "minh_chung_id"); }
    public function nguoiXuLy() { return $this->belongsTo(User::class, "nguoi_xu_ly_id"); }
}