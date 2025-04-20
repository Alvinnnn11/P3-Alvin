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
        Schema::create('membership_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Nama Tingkat (Silver, Gold, Diamond)
            $table->integer('min_points')->unique()->default(0); // Poin minimal untuk mencapai tingkat ini
            $table->decimal('discount_percentage', 5, 2)->default(0); // Persentase diskon untuk tingkat ini (contoh)
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_tiers');
    }
};
