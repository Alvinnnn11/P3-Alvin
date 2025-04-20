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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type'); // 'topup', 'membership_fee', 'purchase', dll
            $table->decimal('amount', 15, 2); // Positif = masuk, Negatif = keluar
            $table->string('description');
            $table->string('status')->default('pending'); // pending, paid, failed, completed, expired
            $table->string('payment_gateway')->nullable(); // mis: 'xendit'
            $table->string('gateway_ref_id')->nullable()->index(); // ID dari Xendit (Invoice ID)
            $table->string('gateway_payment_link', 1024)->nullable(); // URL Invoice Xendit
            $table->json('gateway_payload')->nullable(); // Simpan data dari Xendit
            $table->timestamp('processed_at')->nullable(); // Kapan transaksi diproses (saldo berubah)
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
