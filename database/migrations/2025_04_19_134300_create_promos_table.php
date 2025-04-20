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
        Schema::create('promos', function (Blueprint $table) {
            $table->id(); // promo_id akan jadi primary key
            $table->string('nama_promo');
            $table->text('deskripsi')->nullable();
            $table->foreignId('cabang_id')->nullable()->constrained('cabangs')->onDelete('set null'); 
            $table->boolean('khusus_member')->default(false); 
            $table->enum('tipe_diskon', ['percentage', 'fixed'])->default('percentage'); // Tipe: Persen atau Nominal Tetap
            $table->decimal('nilai_diskon', 15, 2)->default(0);
            $table->decimal('minimal_total_harga', 15, 2)->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->boolean('status_promo')->default(true);
            $table->timestamps(); // Indexes untuk pencarian/filter cepat
            $table->index(['status_promo', 'tanggal_mulai', 'tanggal_selesai']);
            $table->index('cabang_id');
            $table->index('khusus_member');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promos');
    }
};
