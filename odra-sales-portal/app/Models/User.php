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
        'state',
        'city',
        'assigned_states',
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
        'assigned_states' => 'array',
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

    public function territories()
    {
        return $this->hasMany(Territory::class, 'assigned_rep_id');
    }

    /**
     * Get all states assigned to this representative (supports multiple states).
     */
    public function getAssignedStates(): array
    {
        // 1. Explicitly assigned states on user profile
        if (!empty($this->assigned_states) && is_array($this->assigned_states)) {
            $cleaned = array_values(array_unique(array_filter($this->assigned_states)));
            if (!empty($cleaned)) {
                return $cleaned;
            }
        }

        // 2. From territories table via assigned_rep_id
        $states = [];
        $territories = $this->territories;
        if ($territories && $territories->count()) {
            foreach ($territories as $t) {
                if (!empty($t->states)) {
                    $s = is_array($t->states) ? $t->states : json_decode($t->states, true);
                    if (is_array($s)) {
                        $states = array_merge($states, $s);
                    }
                }
                if (str_contains($t->label, 'Florida') || in_array($t->code, ['NFL', 'SWFL', 'SEFL'])) {
                    $states[] = 'Florida';
                }
                if (str_contains($t->label, 'Texas') || in_array($t->code, ['NTX', 'STX'])) {
                    $states[] = 'Texas';
                }
            }
        }

        if (!empty($this->state)) {
            $states[] = $this->state;
        }

        $states = array_values(array_unique(array_filter($states)));
        if (!empty($states)) {
            return $states;
        }

        // 3. Fallback: all available states from FreightRule
        return FreightRule::distinct()->orderBy('state')->pluck('state')->toArray();
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

    /**
     * Get the FreightRule corresponding to the user's assigned state and city.
     */
    public function assignedFreightRule()
    {
        if (! $this->state || ! $this->city) {
            return null;
        }

        return FreightRule::where('state', $this->state)
            ->where('city', $this->city)
            ->first();
    }

    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }
}
