<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationPeriod extends Model
{
    protected $fillable = [
        'rfq_id',
        'round',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function rfq()
    {
        return $this->belongsTo(Rfq::class);
    }
}
