<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class HoatDong extends Model {
    protected $table = "hoat_dongs";
    protected $fillable = ["ma_hoat_dong", "ten_hoat_dong", "don_vi_to_chuc_id", "tieu_chi_id", "loai_hoat_dong", "diem_du_kien", "slot_toi_da", "thoi_gian_bat_dau", "thoi_gian_ket_thuc", "hinh_thuc_diem_danh", "yeu_cau_minh_chung", "trang_thai"];
    protected $casts = ["thoi_gian_bat_dau" => "datetime", "thoi_gian_ket_thuc" => "datetime"];
    public function donViToChuc() { return $this->belongsTo(DonViToChuc::class, "don_vi_to_chuc_id"); }
    public function tieuChi() { return $this->belongsTo(TieuChiRenLuyen::class, "tieu_chi_id"); }
    public function dangKyHoatDongs() { return $this->hasMany(DangKyHoatDong::class, "hoat_dong_id"); }
}