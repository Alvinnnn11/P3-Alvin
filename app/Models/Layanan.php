<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Layanan extends Model
{
    use HasFactory;
    protected $primaryKey = 'layanan_id';
    protected $fillable = [
        'nama_layanan',
        'harga_per_unit',
        'satuan_id',
        'estimasi_durasi_hari',
        'status',
    ]; 
    protected $casts = [
        'harga_per_unit' => 'decimal:2', // Pastikan harga dianggap desimal
        'status' => 'boolean',        // Pastikan status dianggap boolean (true/false)
    ];

    /**
     * Mendefinisikan relasi "belongsTo" ke model Satuan.
     * Setiap Layanan "milik" satu Satuan.
     */
    public function satuan(): BelongsTo
    {
        // Parameter kedua adalah foreign key (di tabel ini, layanans)
        // Parameter ketiga adalah owner key (primary key di tabel satuan)
        return $this->belongsTo(Satuan::class, 'satuan_id', 'satuan_id');
    }

    /**
     * (Opsional) Mendefinisikan relasi "hasMany" ke model ItemPesananLaundry.
     * Satu Layanan bisa ada di banyak Item Pesanan.
     * Pastikan Model ItemPesananLaundry sudah ada jika ingin menggunakan ini.
     */
    // public function itemPesanan(): HasMany
    // {
    //     // Parameter kedua adalah foreign key di tabel item_pesanan_laundry
    //     // Parameter ketiga adalah local key (primary key di tabel ini, layanans)
    //     return $this->hasMany(ItemPesananLaundry::class, 'layanan_id', 'layanan_id');
    // }
}