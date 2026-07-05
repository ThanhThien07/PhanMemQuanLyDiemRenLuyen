<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ThongBao extends Model {
    protected $table = "thong_baos";
    protected $fillable = ["tieu_de", "noi_dung", "loai_thong_bao"];
    public function nguoiNhans() { return $this->belongsToMany(User::class, "nguoi_nhan_thong_baos", "thong_bao_id", "user_id")->withPivot("da_xem"); }
}