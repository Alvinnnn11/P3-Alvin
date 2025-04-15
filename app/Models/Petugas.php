<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Petugas extends Model
{
    use HasFactory;
    protected $table = 'petugas'; // Nama tabel

    protected $fillable = [
        'user_id',
        'cabang_id',
        'tugas',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function cabang()
    {
        // Satu record petugas milik satu cabang
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }
}
