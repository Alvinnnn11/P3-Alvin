<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    use HasFactory;
    protected $table = 'promos';

    protected $fillable = [
        'nama_promo',
        'deskripsi',
        'cabang_id',
        'khusus_member',
        'tipe_diskon',
        'nilai_diskon',
        'minimal_total_harga',
        'tanggal_mulai',
        'tanggal_selesai',
        'status_promo',
    ];

    protected $casts = [
        'khusus_member' => 'boolean',
        'nilai_diskon' => 'decimal:2',
        'minimal_total_harga' => 'decimal:2',
        'tanggal_mulai' => 'datetime', // Gunakan datetime agar bisa cek jam jika perlu
        'tanggal_selesai' => 'datetime',
        'status_promo' => 'boolean',
    ];

    /**
     * Relasi ke Cabang (Satu promo bisa milik satu cabang atau tidak sama sekali).
     */
    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    /**
     * Accessor: Mengecek apakah promo aktif SAAT INI.
     * Memeriksa status manual DAN periode tanggal.
     *
     * @return bool
     */
    public function getIsActiveAttribute(): bool // Nama accessor: $promo->is_active
    {
        $now = Carbon::now();
        return $this->status_promo === true && // Status manual Aktif
               $now->betweenIncluded($this->tanggal_mulai, $this->tanggal_selesai); // Berada dalam periode
    }

    /**
     * (Opsional) Helper: Mengecek apakah promo ini bisa diterapkan pada kondisi tertentu.
     *
     * @param User|null $user User yang sedang bertransaksi (null jika tamu).
     * @param int|null $cabangId ID cabang tempat transaksi.
     * @param float $subtotal Subtotal transaksi saat ini.
     * @return bool
     */
    public function isApplicable(?User $user, ?int $cabangId, float $subtotal): bool
    {
        // 1. Cek status aktif (manual & tanggal)
        if (!$this->is_active) { // Menggunakan accessor isActiveAttribute
            return false;
        }

        // 2. Cek minimal harga
        if ($this->minimal_total_harga !== null && $subtotal < $this->minimal_total_harga) {
            return false;
        }

        // 3. Cek target cabang
        // Jika promo punya target cabang, ID cabang transaksi harus sama
        // Jika promo tidak punya target cabang (null), berarti berlaku di semua cabang
        if ($this->cabang_id !== null && $this->cabang_id !== $cabangId) {
            return false;
        }

        // 4. Cek target member
        // Jika promo khusus member, user harus login DAN status membernya aktif
        if ($this->khusus_member === true) {
            if (!$user || !$user->isMemberActive()) { // Panggil helper isMemberActive() di model User
                return false;
            }
        }

        // Jika semua kondisi terpenuhi
        return true;
    }

    /**
     * (Opsional) Helper: Menghitung jumlah diskon aktual.
     *
     * @param float $subtotal Harga sebelum diskon.
     * @return float
     */
    public function calculateDiscount(float $subtotal): float
    {
        if ($this->tipe_diskon === 'percentage') {
            return ($subtotal * $this->nilai_diskon) / 100;
        } elseif ($this->tipe_diskon === 'fixed') {
             // Pastikan diskon fixed tidak lebih besar dari subtotal
            return min($this->nilai_diskon, $subtotal);
        }
        return 0; // Tipe diskon tidak valid
    }
}


