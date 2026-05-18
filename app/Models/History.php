<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    protected $table = 'history';

    protected $fillable = [
        'user_id',
        'vendor_id',
        'rfq_id',
        'vendor_selection_id',
        'action',
        'transaction_status',
        'notes',
        'action_date',
    ];

    protected $casts = [
        'action_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function rfq()
    {
        return $this->belongsTo(Rfq::class);
    }

    public function vendorSelection()
    {
        return $this->belongsTo(VendorSelection::class);
    }
}
