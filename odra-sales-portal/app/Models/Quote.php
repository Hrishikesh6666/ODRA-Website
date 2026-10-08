<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Quote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'quote_number',
        'current_version',
        'status',
    ];

    protected static function booted()
    {
        static::addGlobalScope('owned', function ($query) {
            if (Auth::check() && !Auth::user()->isAdmin() && !Auth::user()->isManager()) {
                $query->where('user_id', Auth::id());
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function versions()
    {
        return $this->hasMany(QuoteVersion::class);
    }

    public function quoteVersions()
    {
        return $this->versions();
    }

    public function latestVersion()
    {
        return $this->hasOne(QuoteVersion::class)->latestOfMany('version_number');
    }
}
