<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $table = 'service_requests';

    protected $fillable = [
        'user_id',
        'service_name',
        'submission_date',
        'requested_date',
        'plant',
        'status',
    ];

    // Relasi ke User pembuat request
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi 1-to-Many ke tabel Jobs (Scope of Work)
    public function jobs()
    {
        return $this->hasMany(ServiceRequestJob::class, 'sr_id');
    }

    // Relasi ke RFQ (jika nantinya diproses oleh Purchasing)
    public function rfqs()
    {
        return $this->hasMany(Rfq::class, 'sr_id');
    }
}