<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outbounds', function (Blueprint $table) {
            $table->id();
            $table->string('category');    // Sesuai revisi: Kategori (dulu Nama Barang)
            $table->string('sku');         // Sesuai revisi: SKU (dulu Deskripsi)
            $table->integer('jumlah');
            $table->string('penerima');
            $table->string('ekspedisi');   // J&T, GoSend, dll
            $table->string('status')->default('Perlu Dikirim'); // Default-nya ini
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbounds');
    }
};