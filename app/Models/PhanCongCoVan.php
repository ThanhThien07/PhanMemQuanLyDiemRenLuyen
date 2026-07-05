<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhanCongCoVan extends Model
{
    protected $table = 'phan_cong_co_vans';

    protected $fillable = [
        'user_id',
        'lop_id',
        'hoc_ky_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function lop()
    {
        return $this->belongsTo(Lop::class, 'lop_id');
    }

    public function hocKy()
    {
        return $this->belongsTo(HocKy::class, 'hoc_ky_id');
    }
}
