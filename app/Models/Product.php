<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    // Daftarkan SEMUA kolom baru yang udah kita buat di migrasi tadi
    protected $fillable = [
        'category', 
        'sku', 
        'barcode',    // Tambahin ini
        'brand',      // Tambahin ini
        'stock', 
        'unit',       // Tambahin ini
        'rack',       // Tambahin ini
        'price_cost', // Tambahin ini
        'price_sell', // Tambahin ini
        'status_jual',// Tambahin ini
        'image',
        'keterangan',
    ];

    public function outbounds()
    {
        return $this->hasMany(Outbound::class);
    }
}