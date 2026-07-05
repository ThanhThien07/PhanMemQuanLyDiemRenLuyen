<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DonViToChuc extends Model {
    protected $table = "don_vi_to_chucs";
    protected $fillable = ["ma_don_vi", "ten_don_vi", "user_id"];
    public function user() { return $this->belongsTo(User::class, "user_id"); }
    public function hoatDongs() { return $this->hasMany(HoatDong::class, "don_vi_to_chuc_id"); }
}