<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AuditLog extends Model {
    protected $table = "audit_logs";
    protected $fillable = ["user_id", "action", "target_table", "old_data", "new_data"];
    public function user() { return $this->belongsTo(User::class, "user_id"); }
}