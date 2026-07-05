<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DiemHocTap extends Model {
    protected $table = "diem_hoc_taps";
    protected $fillable = ["sinh_vien_id", "hoc_ky_id", "diem_he_4", "diem_he_10", "diem_quy_doi"];
    public function sinhVien() { return $this->belongsTo(SinhVien::class, "sinh_vien_id"); }
    public function hocKy() { return $this->belongsTo(HocKy::class, "hoc_ky_id"); }
}