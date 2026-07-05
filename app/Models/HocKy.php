<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class HocKy extends Model {
    protected $table = "hoc_kys";
    protected $fillable = ["ma_hoc_ky", "ten_hoc_ky", "trang_thai", "ti_le_ren_luyen", "ti_le_hoc_tap"];
    public function cauHinhDotDuyet() { return $this->hasOne(CauHinhDotDuyet::class, "hoc_ky_id"); }
}