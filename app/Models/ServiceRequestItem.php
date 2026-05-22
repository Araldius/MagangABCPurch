<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequestItem extends Model
{
    use HasFactory;

    protected $table = 'service_request_items';

    protected $fillable = [
        'job_id',
        'item_name',
        'quantity',
        'unit',
        'specification',
    ];

    // Relasi balik (belongsTo) ke Induk Job
    public function job()
    {
        return $this->belongsTo(ServiceRequestJob::class, 'job_id');
    }

    // Relasi ke Quotation Details (Penawaran Vendor)
    public function quotationDetails()
    {
        return $this->hasMany(QuotationDetail::class, 'sr_item_id');
    }
}