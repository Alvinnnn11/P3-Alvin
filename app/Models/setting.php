<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class setting extends Model
{
    use HasFactory;
    protected $table = 'settings';
    protected $primaryKey = 'id_setting';
    protected $fillable = ['logo', 'nama_perusahaan', 'alamat', 'telepon', 'website', 'email'];
}
