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
        Schema::create('layanans', function (Blueprint $table) {
            $table->id('layanan_id'); // Primary key auto-increment 'layanan_id'
            $table->string('nama_layanan');
            $table->decimal('harga_per_unit', 12, 2); // 12 digit total, 2 di belakang koma

            // Foreign key ke tabel satuans (pastikan tabel satuans sudah ada)
            $table->foreignId('satuan_id')
                  ->constrained('satuans', 'satuan_id') // Merujuk ke tabel 'satuans' kolom 'satuan_id'
                  ->onUpdate('cascade') // Opsi: jika satuan_id di tabel satuans berubah, update juga di sini
                  ->onDelete('restrict'); // Opsi: jangan biarkan satuan dihapus jika masih dipakai di layanan

            $table->integer('estimasi_durasi_hari')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('layanans');
    }
};
