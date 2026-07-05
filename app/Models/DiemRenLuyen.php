<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DiemRenLuyen extends Model {
    protected $table = "diem_ren_luyens";
    protected $fillable = ["sinh_vien_id", "hoc_ky_id", "lop_id", "tong_diem_tieu_chi", "diem_hoc_tap_quy_doi", "diem_tong_hop", "xep_loai", "minh_chung_gdrive", "trang_thai_duyet"];
    public function sinhVien() { return $this->belongsTo(SinhVien::class, "sinh_vien_id"); }
    public function hocKy() { return $this->belongsTo(HocKy::class, "hoc_ky_id"); }
    public function lop() { return $this->belongsTo(Lop::class, "lop_id"); }
    public function chiTietDiemRenLuyens() { return $this->hasMany(ChiTietDiemRenLuyen::class, "diem_ren_luyen_id"); }
}