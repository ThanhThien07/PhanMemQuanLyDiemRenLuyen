<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Lop extends Model {
    protected $table = "lops";
    protected $fillable = ["ma_lop", "ten_lop", "nganh_id", "khoa_hoc_id"];
    public function nganh() { return $this->belongsTo(Nganh::class, "nganh_id"); }
    public function khoaHoc() { return $this->belongsTo(KhoaHoc::class, "khoa_hoc_id"); }
    public function sinhViens() { return $this->hasMany(SinhVien::class, "lop_id"); }
    public function coVan() {
        return $this->hasOneThrough(
            User::class,
            PhanCongCoVan::class,
            'lop_id',
            'id',
            'id',
            'user_id'
        );
    }
}