<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cabang extends Model
{
    use HasFactory;
    protected $table = 'cabangs'; 
    protected $fillable = [
        'kode_cabang',
        'nama_perusahaan',
        'alamat_perusahaan',
        'provinsi_perusahaan',
        'kota_perusahaan',
        'kecamatan_perusahaan',
        'kelurahan_perusahaan',
        'kode_pos',
        'logo_perusahaan',
        'status',
    ];
    protected $casts = [
        'status' => 'boolean',
    ];
    public function Petugas() 
    {
        return $this->hasMany(Petugas::class, 'cabang_id');
    }
}
