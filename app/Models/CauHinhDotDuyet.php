<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CauHinhDotDuyet extends Model {
    protected $table = "cau_hinh_dot_duyets";
    protected $fillable = ["hoc_ky_id", "gd1_bat_dau", "gd1_ket_thuc", "gd2_bat_dau", "gd2_ket_thuc", "gd3_bat_dau", "gd3_ket_thuc", "gd4_bat_dau", "gd4_ket_thuc", "gd5_bat_dau", "gd5_ket_thuc"];
    public function hocKy() { return $this->belongsTo(HocKy::class, "hoc_ky_id"); }
}