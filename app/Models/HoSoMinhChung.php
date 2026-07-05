<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class HoSoMinhChung extends Model {
    protected $table = "ho_so_minh_chungs";
    protected $fillable = ["sinh_vien_id", "hoat_dong_id", "ten_hoat_dong_ngoai", "minh_chung_id", "tieu_chi_id", "diem_de_xuat", "trang_thai_duyet", "ly_do_tu_choi", "nguoi_duyet_id"];
    public function sinhVien() { return $this->belongsTo(SinhVien::class, "sinh_vien_id"); }
    public function hoatDong() { return $this->belongsTo(HoatDong::class, "hoat_dong_id"); }
    public function minhChung() { return $this->belongsTo(MinhChung::class, "minh_chung_id"); }
    public function tieuChi() { return $this->belongsTo(TieuChiRenLuyen::class, "tieu_chi_id"); }
    public function nguoiDuyet() { return $this->belongsTo(User::class, "nguoi_duyet_id"); }
}