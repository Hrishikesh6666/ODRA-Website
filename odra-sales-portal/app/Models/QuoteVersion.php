<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_id',
        'version_number',
        'status',
        'finalized_at',
    ];

    protected $casts = [
        'finalized_at' => 'datetime',
    ];

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function snapshot()
    {
        return $this->hasOne(QuoteSnapshot::class);
    }

    public function quoteSnapshot()
    {
        return $this->snapshot();
    }

    public function items()
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function quoteItems()
    {
        return $this->items();
    }

    public function isFinalized(): bool
    {
        return strtolower($this->status) === 'finalized';
    }

    public function isDraft(): bool
    {
        return strtolower($this->status) === 'draft';
    }
}
