<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Outbound;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    // 1. HALAMAN STOCK BARANG
    public function index() {
        $products = Product::latest()->get();
        return view('inventory.index', compact('products'));
    }

    // 2. HALAMAN BARANG KELUAR
    public function barangKeluar(Request $request) {
        $status = $request->query('status', 'Semua');
        $query = Outbound::query();
        
        if ($status !== 'Semua') {
            $query->where('status', $status);
        }
        
        $outbounds = $query->latest()->get();
        return view('inventory.keluar', compact('outbounds', 'status'));
    }

    // 3. HALAMAN ANALITIK
    public function analitik() {
        $summary = [
            'cetak'  => Outbound::where('status', 'Dikirim')->count(), 
            'proses' => Outbound::where('status', 'Perlu Dikirim')->count(),
            'cancel' => Outbound::where('status', 'Dibatalkan')->count(),
        ];

        $kurirs = ['GoSend', 'J&T', 'SPX', 'NinjaVan'];
        $detail = [];

        foreach ($kurirs as $k) {
            $detail[$k] = [
                'in'     => Outbound::where('ekspedisi', $k)->where('status', 'Perlu Dikirim')->count(),
                'out'    => Outbound::where('ekspedisi', $k)->where('status', 'Dikirim')->count(),
                'cancel' => Outbound::where('ekspedisi', $k)->where('status', 'Dibatalkan')->count(),
            ];
        }

        return view('inventory.analitik', compact('summary', 'detail'));
    }

    // 4. SIMPAN MASTER DATA BARU
    public function store(Request $request) {
        $request->validate([
            'sku'     => 'required|unique:products,sku',
            'barcode' => 'required|unique:products,barcode',
            'stock'   => 'required|integer',
        ]);

        Product::create([
            'category'    => $request->category,
            'brand'       => $request->brand,
            'sku'         => $request->sku,
            'barcode'     => $request->barcode,
            'stock'       => $request->stock,
            'unit'        => $request->unit,
            'rack'        => $request->rack,
            'price_cost'  => $request->price_cost ?? 0,
            'price_sell'  => $request->price_sell ?? 0,
            'status_jual' => 'Masih Dijual',
        ]);

        return back()->with('success', 'Master data berhasil ditambah!');
    }

    // 5. UPDATE MASTER DATA (EDIT)
    public function update(Request $request, $id) {
        $product = Product::findOrFail($id);
        
        $product->update([
            'sku'         => $request->sku,
            'barcode'     => $request->barcode,
            'category'    => $request->category,
            'brand'       => $request->brand,
            'stock'       => $request->stock,
            'unit'        => $request->unit,
            'rack'        => $request->rack,
            'price_cost'  => $request->price_cost,
            'price_sell'  => $request->price_sell,
            'status_jual' => $request->status_jual,
        ]);

        return back()->with('success', 'Data berhasil diperbarui!');
    }

    // 6. HAPUS MASTER DATA
    public function destroy($id) {
        Product::findOrFail($id)->delete();
        return back()->with('success', 'Barang berhasil dihapus dari sistem!');
    }

    // 7. SIMPAN PESANAN BARANG KELUAR
    public function storeOutbound(Request $request) {
        $request->validate([
            'barcode'   => 'required',
            'jumlah'    => 'required|integer',
            'penerima'  => 'required',
            'ekspedisi' => 'required',
        ]);

        Outbound::create([
            'category'  => $request->category,
            'brand'     => $request->brand,
            'sku'       => $request->sku,
            'barcode'   => $request->barcode,
            'jumlah'    => $request->jumlah,
            'penerima'  => $request->penerima,
            'ekspedisi' => $request->ekspedisi,
            'status'    => 'Perlu Dikirim',
        ]);

        return redirect()->back()->with('success', 'Pesanan baru berhasil dicatat!');
    }

    // 8. SCANNER LOGIC
    public function scanStatus(Request $request) {
        $data = Outbound::where('barcode', $request->barcode)->first();

        if (!$data) {
            return response()->json(['success' => false, 'message' => 'Barcode tidak ditemukan!'], 404);
        }

        if ($request->action == 'cancel') {
            $data->status = 'Dibatalkan';
            $data->save();
            return response()->json(['success' => true, 'message' => 'Pesanan berhasil dibatalkan.']);
        }

        if ($data->status == 'Perlu Dikirim') {
            $data->status = 'Dikirim';
        } elseif ($data->status == 'Dikirim') {
            $data->status = 'Selesai';
        }

        $data->save();
        return response()->json(['success' => true, 'message' => 'Status sekarang: ' . $data->status]);
    }

    // 9. IMPORT CSV MULTI-FORMAT (FIX SINKRON KOLOM EXCEL CLIENT)
    public function importExcel(Request $request) 
    {
        $request->validate(['file' => 'required|mimes:csv,txt']);

        $file = $request->file('file');
        $fileContent = file_get_contents($file->path());
        
        $separator = ';'; 
        if (strpos($fileContent, ',') !== false && strpos($fileContent, ';') === false) {
            $separator = ',';
        }

        $handle = fopen($file->path(), 'r');
        
        $globalJenis = 'SPT';
        $globalTipe  = 'INV';
        $globalHPP   = 'FIFO';
        $globalMin   = 0;

        $rows = [];
        while (($row = fgetcsv($handle, 1000, $separator)) !== FALSE) {
            $rows[] = $row;
        }
        fclose($handle);

        // Langkah 1: Berburu data kecil yang ada di baris-baris atas
        foreach ($rows as $r) {
            if (isset($r[7]) && trim($r[7]) == 'Jenis' && isset($r[11]) && trim($r[11]) == 'Tipe Item') {
                continue; 
            }
            if (isset($r[11]) && trim($r[11]) == 'INV') {
                $globalJenis = trim($r[7]) ?? 'SPT';
                $globalTipe  = trim($r[11]) ?? 'INV';
                $globalHPP   = isset($r[12]) ? trim($r[12]) : 'FIFO';
                $globalMin   = isset($r[13]) ? (int)trim($r[13]) : 0;
            }
        }

        // Langkah 2: Proses data tabel utama sepatu di bawah (SINKRON KOLOM)
        foreach ($rows as $r) {
            if (empty($r[0]) || trim($r[0]) == 'Kode Item' || count($r) < 5 || trim($r[0]) == 'Jenis') {
                continue; 
            }

            // Stok ada di Kolom E ($r[4])
            $cleanStock = isset($r[4]) ? (int) preg_replace('/[^\d]/', '', explode('.', explode(',', $r[4])[0])[0]) : 0;
            if (!is_numeric($cleanStock)) {
                $cleanStock = 0;
            }

            // Harga Jual ada di Kolom H ($r[7])
            $sellRaw = isset($r[7]) ? trim($r[7]) : '0';
            if (strpos($sellRaw, '.') !== false && strpos($sellRaw, ',') === false && substr($sellRaw, -3) !== '.00') {
                $sellRaw = str_replace('.', '', $sellRaw);
            }
            $cleanSell = (float) preg_replace('/[^\d]/', '', explode('.', explode(',', $sellRaw)[0])[0]);

            // Harga Pokok ada di Kolom I ($r[8])
            $costRaw = isset($r[8]) ? trim($r[8]) : '0';
            if (strpos($costRaw, '.') !== false && strpos($costRaw, ',') === false && substr($costRaw, -3) !== '.00') {
                $costRaw = str_replace('.', '', $costRaw);
            }
            $cleanCost = (float) preg_replace('/[^\d]/', '', explode('.', explode(',', $costRaw)[0])[0]);

            // Simpan ke Database
            Product::updateOrCreate(
                ['barcode' => trim($r[1])], // Barcode (Kolom B)
                [
                    'sku'         => trim($r[0]),  // Kode Item / SKU (Kolom A)
                    'category'    => trim($r[3]),  // Kategori (Kolom D)
                    'stock'       => $cleanStock,  // Stok Bersih
                    'unit'        => 'PSG',        // Default Satuan (atau sesuaikan kebutuhan)
                    'rack'        => trim($r[5]) ?? '-',   // Rak (Kolom F)
                    'brand'       => trim($r[6]) ?? '-',   // Merek / Brand (Kolom G)
                    'price_cost'  => $cleanCost,   // Harga Pokok Bersih
                    'price_sell'  => $cleanSell,   // Harga Jual Bersih
                    'status_jual' => trim($r[9]) ?? 'Masih Dijual', // Status Jual (Kolom J)
                    'keterangan'  => trim($r[10]) ?? '-', // Keterangan (Kolom K)
                    
                    // Kolom metadata dari atas Excel
                    'jenis'       => $globalJenis,
                    'tipe_item'   => $globalTipe,
                    'system_hpp'  => $globalHPP,
                    'stok_min'    => $globalMin,
                ]
            );
        }

        return redirect()->back()->with('success', 'Master Data Berhasil Disinkronkan!');
    }

    // 10. HAPUS DATA BARANG KELUAR
    public function destroyOutbound($id) {
        $data = Outbound::findOrFail($id);
        $data->delete();
        return back()->with('success', 'Data pengiriman berhasil dihapus.');
    }
}