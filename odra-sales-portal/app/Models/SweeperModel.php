<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SweeperModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sweeper_models';

    protected $fillable = [
        'code',
        'description',
        'category',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function configurations()
    {
        return $this->hasMany(ModelConfiguration::class, 'sweeper_model_id');
    }

    public function modelConfigurations()
    {
        return $this->configurations();
    }

    public function prices()
    {
        return $this->hasMany(ModelPrice::class, 'sweeper_model_id');
    }

    public function modelPrices()
    {
        return $this->prices();
    }

    public function enhancements()
    {
        return $this->hasMany(Enhancement::class, 'sweeper_model_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
