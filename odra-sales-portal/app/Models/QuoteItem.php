<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_version_id',
        'item_type',
        'source_enhancement_id',
        'description',
        'category',
        'qty',
        'msrp_at_time',
        'list_price_at_time',
        'unit_price_at_time',
        'extended_price',
        'sort_order',
    ];

    protected $casts = [
        'qty' => 'integer',
        'msrp_at_time' => 'decimal:2',
        'list_price_at_time' => 'decimal:2',
        'unit_price_at_time' => 'decimal:2',
        'extended_price' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function quoteVersion()
    {
        return $this->belongsTo(QuoteVersion::class);
    }

    public function enhancement()
    {
        return $this->belongsTo(Enhancement::class, 'source_enhancement_id');
    }
}
