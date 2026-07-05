<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SinhVien extends Model {
    protected $table = "sinh_viens";
    protected $fillable = ["user_id", "ma_sv", "ho_ten", "lop_id", "he_dao_tao_id"];
    public function user() { return $this->belongsTo(User::class, "user_id"); }
    public function lop() { return $this->belongsTo(Lop::class, "lop_id"); }
    public function heDaoTao() { return $this->belongsTo(HeDaoTao::class, "he_dao_tao_id"); }
    public function cauLacBos() { return $this->belongsToMany(CauLacBo::class, "clb_sinh_vien", "sinh_vien_id", "clb_id"); }
    public function dangKyHoatDongs() { return $this->hasMany(DangKyHoatDong::class, "sinh_vien_id"); }
    public function hoSoMinhChungs() { return $this->hasMany(HoSoMinhChung::class, "sinh_vien_id"); }
    public function kyLuats() { return $this->hasMany(KyLuat::class, "sinh_vien_id"); }
    public function khieuNais() { return $this->hasMany(KhieuNai::class, "sinh_vien_id"); }
    public function diemHocTaps() { return $this->hasMany(DiemHocTap::class, "sinh_vien_id"); }
    public function diemRenLuyens() { return $this->hasMany(DiemRenLuyen::class, "sinh_vien_id"); }
}