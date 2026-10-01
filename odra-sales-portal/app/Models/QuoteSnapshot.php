<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_version_id',
        'customer_company',
        'customer_contact',
        'customer_address_1',
        'customer_address_2',
        'model_code',
        'model_description',
        'chassis_configuration',
        'pricing_basis',
        'sales_rep_name',
        'rep_territory',
        'po_number',
        'customer_account',
        'fob_terms',
        'base_qty',
        'base_msrp',
        'base_list_price',
        'base_unit_price',
        'show_discount_amount',
        'freight_amount',
        'subtotal',
        'total',
        'quote_code',
        'enhancements_snapshot',
        'standard_features_html',
        'legal_terms_html',
        'full_state',
        'pdf_path',
        'pdf_generated_at',
    ];

    protected $casts = [
        'base_msrp' => 'decimal:2',
        'base_list_price' => 'decimal:2',
        'base_unit_price' => 'decimal:2',
        'show_discount_amount' => 'decimal:2',
        'freight_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'base_qty' => 'integer',
        'enhancements_snapshot' => 'array',
        'full_state' => 'array',
        'pdf_generated_at' => 'datetime',
    ];

    public function quoteVersion()
    {
        return $this->belongsTo(QuoteVersion::class);
    }
}
