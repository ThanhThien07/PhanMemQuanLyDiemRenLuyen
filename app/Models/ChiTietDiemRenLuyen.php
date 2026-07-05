<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChiTietDiemRenLuyen extends Model
{
    protected $table = 'chi_tiet_diem_ren_luyens';

    protected $fillable = [
        'diem_ren_luyen_id',
        'ma_tieu_chi',
        'diem_sv',
        'ghi_chu_sv',
        'diem_bcs',
        'ghi_chu_bcs',
        'diem_cvht',
        'ghi_chu_cvht'
    ];

    public function diemRenLuyen()
    {
        return $this->belongsTo(DiemRenLuyen::class, 'diem_ren_luyen_id');
    }
}
