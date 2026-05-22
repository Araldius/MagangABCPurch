<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\History;

class PurchaseRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'document_number', 
        'title', 
        'department', 
        'priority', 
        'plant', 
        'submission_date', 
        'requested_date', 
        'need_date', 
        'note', 
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseRequestItem::class, 'purchase_request_id');
    }

    public function rfqs()
    {
        return $this->hasMany(Rfq::class);
    }

    public function histories()
    {
        return $this->hasMany(History::class);
    }
}
