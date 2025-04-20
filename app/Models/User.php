<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'users';
    protected $fillable = [
        'name',
        'email',
        'address',
        'phone',
        'level',
        'foto_profile',
        'password',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'status' => 'boolean', 
    ];

    public function Petugas() 
    {
        return $this->hasOne(Petugas::class, 'user_id');
    }
    public function supervisor() 
    {
        return $this->hasOne(Supervisor::class, 'user_id');
    }
    public function customer() 
    {
        return $this->hasOne(Customer::class, 'user_id');
    }
    public function membership() { 
        return $this->hasOne(Member::class, 'user_id'); 
    }
    public function transactions() { 
        return $this->hasMany(transaction::class, 'user_id');
     } // Relasi ke transaksi

    // Helper cek status member AKTIF
    public function isMemberActive(): bool{
        // Cek apakah relasi membership ada DAN status is_active di tabel members = true
        return $this->membership && $this->membership->is_active === true;
        // Jika Anda pakai expires_at dan status string 'active':
        // return $this->membership && $this->membership->status === 'active' && ($this->membership->expires_at === null || $this->membership->expires_at->isFuture());
    }
    // Helper cek apakah sudah pernah join member (meski mungkin skrg tidak aktif)
    public function hasMembershipRecord(): bool {
        return $this->membership()->exists();
    }
    // Helper ambil saldo
    public function getBalance(): float {
        return $this->membership ? (float) $this->membership->balance : 0.00;
    }
}
