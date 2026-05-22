<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequestJob extends Model
{
    use HasFactory;

    protected $table = 'service_request_jobs';

    protected $fillable = [
        'service_request_id',
        'job_description',
    ];

    // Relasi balik (belongsTo) ke Induk Service Request
    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_id');
    }

    // Relasi 1-to-Many ke tabel Items
    public function items()
    {
        return $this->hasMany(ServiceRequestItem::class, 'job_id');
    }
}