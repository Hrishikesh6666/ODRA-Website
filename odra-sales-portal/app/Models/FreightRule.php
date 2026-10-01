<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FreightRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'state',
        'city',
        'customer_rate',
        'municipality_rate',
        'updated_by',
    ];

    protected $casts = [
        'customer_rate' => 'decimal:2',
        'municipality_rate' => 'decimal:2',
    ];

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
