<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    protected $fillable = [
        'user_id', 'type', 'amount', 'description', 'status',
        'payment_gateway', 'gateway_ref_id', 'gateway_payment_link',
        'gateway_payload', 'processed_at',
    ];

    protected $casts = [
        'gateway_payload' => 'array',
        'processed_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}


