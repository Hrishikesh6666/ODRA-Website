<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PricingBasis extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'price_field',
        'subtotal_label',
        'logo_key',
        'show_discount',
        'use_municipality_freight',
        'is_active',
    ];

    protected $casts = [
        'show_discount' => 'boolean',
        'use_municipality_freight' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
