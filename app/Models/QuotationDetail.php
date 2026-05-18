<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationDetail extends Model
{
    protected $fillable = [
        'quotation_id',
        'purchase_request_item_id',
        'offered_price_per_item',
        'offered_quantity',
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function purchaseRequestItem()
    {
        return $this->belongsTo(PurchaseRequestItem::class);
    }

    public function quotationSummaries()
    {
        return $this->hasMany(QuotationSummary::class);
    }
}
