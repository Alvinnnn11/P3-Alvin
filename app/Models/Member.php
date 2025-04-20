<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;
    protected $table = 'members';
    protected $fillable = [
        'user_id',
        'balance',
        'is_active',
        'joined_at',
    ];
    protected $casts = [
        'is_active' => 'boolean',
        'joined_at' => 'datetime',
        'balance' => 'decimal:2', // Penting untuk presisi saldo
    ];

    public function user() { return $this->belongsTo(User::class, 'user_id'); }

    // Helper cek keaktifan
    public function isActive(): bool { return $this->is_active; }
}
