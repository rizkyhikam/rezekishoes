<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    
    // Master Data (Stock Barang)
    Route::get('/dashboard', [InventoryController::class, 'index'])->name('dashboard');
    Route::post('/inventory/store', [InventoryController::class, 'store'])->name('inventory.store');
    Route::post('/inventory/update/{id}', [InventoryController::class, 'update'])->name('inventory.update');
    Route::delete('/inventory/{id}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
    Route::post('/inventory/import', [InventoryController::class, 'importExcel'])->name('inventory.import');
    
    // Barang Keluar
    Route::get('/barang-keluar', [InventoryController::class, 'barangKeluar'])->name('barang.keluar');
    Route::post('/outbound/store', [InventoryController::class, 'storeOutbound'])->name('outbound.store');
    Route::delete('/outbound/{id}', [InventoryController::class, 'destroyOutbound'])->name('outbound.destroy');
    
    // Scanner & Barcode
    Route::post('/scan-barcode', [InventoryController::class, 'scanStatus'])->name('barcode.scan');

    // Analitik Ekspedisi
    Route::get('/analitik-ekspedisi', [InventoryController::class, 'analitik'])->name('analitik.ekspedisi');

    // Profile Management (Bawaan Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // User Management (Kelola Admin)
    Route::get('/kelola-admin', [AdminController::class, 'index'])->name('admin.index');
    Route::post('/kelola-admin/store', [AdminController::class, 'store'])->name('admin.store');
    // BARIS DI BAWAH INI WAJIB ADA BIAR EDIT ADMIN JALAN:
    Route::post('/kelola-admin/update/{id}', [AdminController::class, 'update'])->name('admin.update'); 
    Route::delete('/kelola-admin/{id}', [AdminController::class, 'destroy'])->name('admin.destroy');
});

require __DIR__.'/auth.php';