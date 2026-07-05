<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Nganh extends Model {
    protected $table = "nganhs";
    protected $fillable = ["ma_nganh", "ten_nganh", "khoa_id"];
    public function khoa() { return $this->belongsTo(Khoa::class, "khoa_id"); }
    public function lops() { return $this->hasMany(Lop::class, "nganh_id"); }
}