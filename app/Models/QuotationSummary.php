<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationSummary extends Model
{
    protected $fillable = [
        'rfq_id',
        'quotation_detail_id',
        'is_sent_to_user',
        'sent_to_user_at',
    ];

    protected $casts = [
        'is_sent_to_user' => 'boolean',
        'sent_to_user_at' => 'datetime',
    ];

    public function rfq()
    {
        return $this->belongsTo(Rfq::class);
    }

    public function quotationDetail()
    {
        return $this->belongsTo(QuotationDetail::class);
    }

    public function selectionItems()
    {
        return $this->hasMany(SelectionItem::class);
    }
}
