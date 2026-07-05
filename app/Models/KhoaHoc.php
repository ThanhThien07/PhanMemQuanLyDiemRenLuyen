<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class KhoaHoc extends Model {
    protected $table = "khoa_hocs";
    protected $fillable = ["ma_khoa_hoc", "nam_bat_dau", "nam_ket_thuc"];
    public function lops() { return $this->hasMany(Lop::class, "khoa_hoc_id"); }
}