<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void {
    Schema::table('products', function (Blueprint $table) {
        // Hapus baris barcode karena udah ada
        if (!Schema::hasColumn('products', 'brand')) {
            $table->string('brand')->nullable()->after('category');
        }
        $table->string('unit')->default('PSG')->after('brand');
        $table->string('rack')->nullable()->after('unit');
        $table->string('type')->default('INV')->after('rack');
        $table->decimal('price_cost', 15, 2)->default(0)->after('type');
        $table->decimal('price_sell', 15, 2)->default(0)->after('price_cost');
        $table->string('status_jual')->default('Masih Dijual')->after('price_sell');
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
};
