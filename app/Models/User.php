<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relationship with Roles (Many to Many)
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    /**
     * Check if user has a specific role (checks both roles table and role string for backward-compat)
     */
    public function hasRole($role)
    {
        if (is_array($role)) {
            return in_array($this->role, $role) || $this->roles()->whereIn('name', $role)->exists();
        }
        return $this->role === $role || $this->roles()->where('name', $role)->exists();
    }

    /**
     * Profile relationships
     */
    public function sinhVien()
    {
        return $this->hasOne(SinhVien::class, 'user_id');
    }

    public function donViToChuc()
    {
        return $this->hasOne(DonViToChuc::class, 'user_id');
    }
}

