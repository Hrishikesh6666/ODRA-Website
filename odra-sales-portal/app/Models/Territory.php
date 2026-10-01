<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Territory extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'label',
        'assigned_rep_id',
        'states',
        'cities',
    ];

    protected $casts = [
        'states' => 'array',
        'cities' => 'array',
    ];

    public function assignedRep()
    {
        return $this->belongsTo(User::class, 'assigned_rep_id');
    }
}
