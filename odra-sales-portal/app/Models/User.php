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
        'company_id',
        'password',
        'entra_object_id',
        'role_id',
        'territory',
        'rep_code',
        'is_active',
        'last_login_at',
        'failed_login_count',
        'locked_until',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'locked_until' => 'datetime',
        'password' => 'hashed',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function hasRole(string $slug): bool
    {
        return $this->role && $this->role->slug === $slug;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isManager(): bool
    {
        return $this->hasRole('manager');
    }

    public function isSalesRep(): bool
    {
        return $this->hasRole('sales');
    }

    /**
     * Strict Financial Shielding: ONLY Managers are permitted to see financial numbers,
     * sales revenue, quote amounts, and commercial totals.
     */
    public function canSeeFinancials(): bool
    {
        return $this->isManager();
    }

    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }
}
