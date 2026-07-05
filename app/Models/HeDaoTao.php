<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class HeDaoTao extends Model {
    protected $table = "he_dao_taos";
    protected $fillable = ["ten_he"];
    public function sinhViens() { return $this->hasMany(SinhVien::class, "he_dao_tao_id"); }
}