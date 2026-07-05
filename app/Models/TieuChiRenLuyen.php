<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TieuChiRenLuyen extends Model {
    protected $table = "tieu_chi_ren_luyens";
    protected $fillable = ["ma_tieu_chi", "ten_tieu_chi", "diem_toi_da"];
}