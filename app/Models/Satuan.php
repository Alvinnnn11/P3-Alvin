<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Satuan extends Model
{
    use HasFactory;
    protected $table = 'satuans';
    protected $primaryKey = 'satuan_id';
    protected $fillable = [
        'nama_satuan',
        'deskripsi',
    ];
    public function layanan(): HasMany
    {
        // Parameter kedua adalah foreign key di tabel layanan
        // Parameter ketiga adalah local key (primary key) di tabel satuan
        return $this->hasMany(Layanan::class, 'satuan_id', 'satuan_id');
    }
}
