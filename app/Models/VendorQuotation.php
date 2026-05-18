<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorQuotation extends Model
{
    protected $fillable = [
        'rfq_id',
        'vendor_id',
        'quotation_file',
        'notes',
        'status',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function rfq()
    {
        return $this->belongsTo(Rfq::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
