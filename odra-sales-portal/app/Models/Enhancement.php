<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Enhancement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sweeper_model_id',
        'category',
        'description',
        'msrp',
        'discount_percent',
        'sw_list',
        'sw_net',
        'sort_order',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'msrp' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'sw_list' => 'decimal:2',
        'sw_net' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function sweeperModel()
    {
        return $this->belongsTo(SweeperModel::class, 'sweeper_model_id');
    }

    public function model()
    {
        return $this->sweeperModel();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForModel($query, $modelId)
    {
        return $query->where('sweeper_model_id', $modelId);
    }
}
