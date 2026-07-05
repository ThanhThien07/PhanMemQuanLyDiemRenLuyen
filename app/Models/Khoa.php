<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Khoa extends Model {
    protected $table = "khoas";
    protected $fillable = ["ma_khoa", "ten_khoa"];
    public function nganhs() { return $this->hasMany(Nganh::class, "khoa_id"); }
}