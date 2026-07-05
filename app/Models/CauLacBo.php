<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CauLacBo extends Model {
    protected $table = "cau_lac_bos";
    protected $fillable = ["ma_clb", "ten_clb", "mo_ta"];
    public function sinhViens() { return $this->belongsToMany(SinhVien::class, "clb_sinh_vien", "clb_id", "sinh_vien_id"); }
}