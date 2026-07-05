<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class MinhChung extends Model {
    protected $table = "minh_chungs";
    protected $fillable = ["file_name", "file_path", "file_size", "file_type"];
}