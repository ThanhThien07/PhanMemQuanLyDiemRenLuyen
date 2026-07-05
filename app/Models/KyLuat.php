<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class KyLuat extends Model {
    protected $table = "ky_luats";
    protected $fillable = ["sinh_vien_id", "tieu_chi_id", "nguoi_tao_id", "diem_tru", "noi_dung", "minh_chung_id"];
    public function sinhVien() { return $this->belongsTo(SinhVien::class, "sinh_vien_id"); }
    public function tieuChi() { return $this->belongsTo(TieuChiRenLuyen::class, "tieu_chi_id"); }
    public function nguoiTao() { return $this->belongsTo(User::class, "nguoi_tao_id"); }
    public function minhChung() { return $this->belongsTo(MinhChung::class, "minh_chung_id"); }
}