<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'sweeper_model_id',
        'label',
        'short_label',
        'price',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function sweeperModel()
    {
        return $this->belongsTo(SweeperModel::class, 'sweeper_model_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
