<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorSelection extends Model
{
    protected $fillable = [
        'rfq_id',
        'vendor_id',
        'quotation_id',
        'decision_notes',
        'decided_at',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
    ];

    public function rfq()
    {
        return $this->belongsTo(Rfq::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function selectionItems()
    {
        return $this->hasMany(SelectionItem::class);
    }
}
