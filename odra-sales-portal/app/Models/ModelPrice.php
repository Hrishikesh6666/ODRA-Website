<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'sweeper_model_id',
        'msrp',
        'sw_list',
        'sw_net',
        'effective_from',
        'effective_until',
        'updated_by',
    ];

    protected $casts = [
        'msrp' => 'decimal:2',
        'sw_list' => 'decimal:2',
        'sw_net' => 'decimal:2',
        'effective_from' => 'datetime',
        'effective_until' => 'datetime',
    ];

    public function sweeperModel()
    {
        return $this->belongsTo(SweeperModel::class, 'sweeper_model_id');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeCurrent($query)
    {
        return $query->where('effective_from', '<=', now())
            ->where(function ($q) {
                $q->whereNull('effective_until')
                  ->orWhere('effective_until', '>', now());
            });
    }
}
