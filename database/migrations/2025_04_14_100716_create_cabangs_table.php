<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cabangs', function (Blueprint $table) {
            $table->id(); // Otomatis jadi id_cabang primary key
            $table->string('kode_cabang')->unique(); // Kode unik untuk cabang
            $table->string('nama_perusahaan');
            $table->text('alamat_perusahaan')->nullable();
            $table->string('provinsi_perusahaan')->nullable();
            $table->string('kota_perusahaan')->nullable();
            $table->string('kecamatan_perusahaan')->nullable();
            $table->string('kelurahan_perusahaan')->nullable();
            $table->string('kode_pos')->nullable();
            $table->string('logo_perusahaan')->nullable(); // Path ke file logo
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cabangs');
    }
};
