<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rfq extends Model
{
    use HasFactory;

    protected $table = 'rfqs';

    protected $fillable = [
        'purchase_request_id',
        'rfq_number',
        'vendor_id',
        'note',
        'status',
        'is_sent_to_user',
        'sent_to_user_at',
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'is_sent_to_user' => 'boolean',
        'sent_to_user_at' => 'datetime',
        'opened_at'       => 'datetime',
        'closed_at'       => 'datetime',
    ];

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /** Latest finalized quotation (backward compat for old controllers) */
    public function quotation()
    {
        return $this->hasOne(Quotation::class)->latestOfMany();
    }

    public function quotationPeriods()
    {
        return $this->hasMany(QuotationPeriod::class);
    }

    public function vendorQuotations()
    {
        return $this->hasMany(VendorQuotation::class);
    }

    public function quotationSummaries()
    {
        return $this->hasMany(QuotationSummary::class);
    }

    public function vendorSelections()
    {
        return $this->hasMany(VendorSelection::class);
    }

    public function histories()
    {
        return $this->hasMany(History::class);
    }
}