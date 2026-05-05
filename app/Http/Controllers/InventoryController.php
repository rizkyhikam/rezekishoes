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
    
    // Pastiin semua kolom ini ada di model Product lo (Protected $fillable)
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

    // 9. IMPORT CSV
    public function importExcel(Request $request) 
    {
        $request->validate(['file' => 'required|mimes:csv,txt']);

        $file = $request->file('file');
        $handle = fopen($file->path(), 'r');
        fgetcsv($handle); 

        while (($row = fgetcsv($handle, 1000, ',')) !== FALSE) {
            Product::updateOrCreate(
                ['barcode' => $row[1]], 
                [
                    'sku'         => $row[0],
                    'category'    => $row[2],
                    'stock'       => $row[3],
                    'unit'        => $row[4] ?? 'PSG',
                    'rack'        => $row[5] ?? '-',
                    'brand'       => $row[6] ?? '-',
                    'price_cost'  => $row[7] ?? 0,
                    'price_sell'  => $row[8] ?? 0,
                    'status_jual' => 'Masih Dijual'
                ]
            );
        }
        fclose($handle);
        return redirect()->back()->with('success', 'Master Data Berhasil Disinkronkan!');
    }

    // 10. HAPUS DATA BARANG KELUAR
    public function destroyOutbound($id) {
        $data = Outbound::findOrFail($id);
        $data->delete();
        return back()->with('success', 'Data pengiriman berhasil dihapus.');
    }
}