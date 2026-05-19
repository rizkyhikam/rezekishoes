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
        
        // AMBIL JUGA DATA PRODUK BUAT DROPDOWN DI MODAL
        $products = Product::where('stock', '>', 0)->orderBy('category', 'asc')->get();
        
        // Kirim variabel $products ke view
        return view('inventory.keluar', compact('outbounds', 'status', 'products'));
    }

    // 3. HALAMAN ANALITIK
public function analitik() {
        $summary = [
            'proses' => Outbound::where('status', 'Perlu Dikirim')->count(), // IN (Sedang diproses)
            'cetak'  => Outbound::where('status', 'Selesai')->count(),       // OUT (Selesai Kirim)
            'cancel' => Outbound::where('status', 'Dibatalkan')->count(),   // CANCEL (Gagal/Batal)
        ];

        $kurirs = ['GoSend', 'J&T', 'SPX', 'NinjaVan'];
        $detail = [];

        foreach ($kurirs as $k) {
            $detail[$k] = [
                'in'     => Outbound::where('ekspedisi', $k)->where('status', 'Perlu Dikirim')->count(),
                'out'    => Outbound::where('ekspedisi', $k)->where('status', 'Selesai')->count(),
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
        $product = Product::findOrFail($id);

        // Hapus dulu semua riwayat barang keluar yang numpang pake barcode ini biar MySQL ga ngamuk
        Outbound::where('barcode', $product->barcode)->delete();

        // Baru deh hapus master produknya dengan aman sentosa
        $product->delete();

        return back()->with('success', 'Barang dan seluruh riwayat pengirimannya berhasil dihapus dari sistem!');
    }

    // 7. SIMPAN PESANAN BARANG KELUAR
public function storeOutbound(Request $request) {
        $request->validate([
            'barcode'   => 'required',
            'jumlah'    => 'required|integer|min:1',
            'penerima'  => 'required',
            'ekspedisi' => 'required',
        ]);

        // Cari produk berdasarkan barcode di gudang
        $product = Product::where('barcode', $request->barcode)->first();

        // VALIDASI 1: Cek apakah barangnya beneran ada di gudang
        if (!$product) {
            return redirect()->back()->with('error', 'Gagal! Produk dengan barcode tersebut tidak ditemukan di gudang.');
        }

        // VALIDASI 2: Cek apakah stok di gudang mencukupi, biar GA MINUS!
        if ($product->stock < $request->jumlah) {
            return redirect()->back()->with('error', 'Gagal! Stok tidak mencukupi. Sisa stok ' . $product->stock . ' ' . $product->unit);
        }

        // Jika lolos validasi, potong stok produk di gudang
        $product->decrement('stock', $request->jumlah);

        // Catat data ke tabel outbounds
        Outbound::create([
            'category'  => $request->category, 
            'sku'       => $request->sku,
            'barcode'   => $request->barcode,
            'jumlah'    => $request->jumlah,
            'penerima'  => $request->penerima,
            'ekspedisi' => $request->ekspedisi,
            'status'    => 'Perlu Dikirim',
            'user_id'   => auth()->id() ?? 1, 
        ]);

        return redirect()->back()->with('success', 'Pesanan baru berhasil dicatat dan stok otomatis dipotong!');
    }

    // 8. SCANNER LOGIC
public function scanStatus(Request $request) {
        $data = Outbound::where('barcode', $request->barcode)->first();

        if (!$data) {
            return response()->json(['success' => false, 'message' => 'Barcode tidak ditemukan!'], 404);
        }

        // Logika kalau pesanan DIBATALKAN via scan/tombol
        if ($request->action == 'cancel') {
            // Balikin lagi stoknya ke gudang karena gajadi dikirim
            $product = Product::where('barcode', $data->barcode)->first();
            if ($product) {
                $product->increment('stock', $data->jumlah);
            }

            $data->status = 'Dibatalkan';
            $data->save();
            return response()->json(['success' => true, 'message' => 'Pesanan berhasil dibatalkan dan stok dikembalikan.']);
        }

        // Logika naik status otomatis pas di-scan
        if ($data->status == 'Perlu Dikirim') {
            $data->status = 'Dikirim';
        } elseif ($data->status == 'Dikirim') {
            $data->status = 'Selesai'; // Status 'Selesai' ini yang dibaca di dashboard analitik OUT
        }

        $data->save();
        return response()->json(['success' => true, 'message' => 'Status sekarang: ' . $data->status]);
    }

    // 9. IMPORT CSV DENGAN AUTOMATIC HEADER MAPPING (ANTI-OBRAK-ABRIK KOLOM)
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
        
        // 1. Ambil baris pertama sebagai Header untuk dipetakan posisinya
        $header = fgetcsv($handle, 1000, $separator);
        if (!$header) {
            fclose($handle);
            return redirect()->back()->with('error', 'File CSV kosong atau rusak!');
        }

        // Bersihkan spasi gaib di nama header dan ubah jadi lowercase biar gampang dicocokin
        $headerMap = array_map(function($title) {
            return strtolower(trim($title));
        }, $header);

        // Cari tahu nama kolom ini ada di indeks nomor berapa saja di Excel secara dinamis
        $keySku        = array_search('sku', $headerMap) !== false ? array_search('sku', $headerMap) : array_search('kode item', $headerMap);
        $keyBarcode    = array_search('barcode', $headerMap);
        $keyName       = array_search('nama barang', $headerMap) !== false ? array_search('nama barang', $headerMap) : array_search('nama item', $headerMap);
        $keyStock      = array_search('stok', $headerMap) !== false ? array_search('stok', $headerMap) : array_search('stock', $headerMap);
        $keyUnit       = array_search('satuan', $headerMap) !== false ? array_search('satuan', $headerMap) : array_search('unit', $headerMap);
        $keyRack       = array_search('rak', $headerMap) !== false ? array_search('rak', $headerMap) : array_search('rack', $headerMap);
        $keyBrand      = array_search('merek', $headerMap) !== false ? array_search('merek', $headerMap) : array_search('brand', $headerMap);
        $keyPriceSell  = array_search('harga jual', $headerMap);
        $keyPriceCost  = array_search('harga pokok', $headerMap);
        $keyStatusJual = array_search('status jual', $headerMap);
        $keyKeterangan = array_search('keterangan', $headerMap);

        // Validasi minimal: Kolom kunci wajib ada, kalau nggak ada langsung tolak biar ga crash
        if ($keySku === false || $keyBarcode === false || $keyName === false) {
            fclose($handle);
            return redirect()->back()->with('error', 'Format judul header Excel salah atau kolom penting (SKU/Barcode/Nama Barang) tidak ditemukan!');
        }

        // 2. Looping membaca baris data di bawah header
        while (($row = fgetcsv($handle, 1000, $separator)) !== FALSE) {
            // Pengaman kalau baris kosong, skip
            if (empty($row) || !isset($row[$keySku]) || empty(trim($row[$keySku]))) {
                continue;
            }

            // --- PROSES AMBIL DATA BERDASARKAN INDEKS DINAMIS ---
            
            // Ambil data stok (Gunakan indeks dinamis hasil mapping tadi)
            $rawStock = $keyStock !== false && isset($row[$keyStock]) ? $row[$keyStock] : '0';
            $cleanStock = (int) preg_replace('/[^\d]/', '', explode('.', explode(',', $rawStock)[0])[0]);

            // Ambil data harga jual
            $rawSell = $keyPriceSell !== false && isset($row[$keyPriceSell]) ? trim($row[$keyPriceSell]) : '0';
            if (strpos($rawSell, '.') !== false && strpos($rawSell, ',') === false && substr($rawSell, -3) !== '.00') {
                $rawSell = str_replace('.', '', $rawSell);
            }
            $cleanSell = (float) preg_replace('/[^\d]/', '', explode('.', explode(',', $rawSell)[0])[0]);

            // Ambil data harga pokok
            $rawCost = $keyPriceCost !== false && isset($row[$keyPriceCost]) ? trim($row[$keyPriceCost]) : '0';
            if (strpos($rawCost, '.') !== false && strpos($rawCost, ',') === false && substr($rawCost, -3) !== '.00') {
                $rawCost = str_replace('.', '', $rawCost);
            }
            $cleanCost = (float) preg_replace('/[^\d]/', '', explode('.', explode(',', $rawCost)[0])[0]);

            // Eksekusi Save / Update ke database MySQL
            Product::updateOrCreate(
                ['barcode' => trim($row[$keyBarcode])], 
                [
                    'sku'         => trim($row[$keySku]),
                    
                    // Supaya sinkron sama {{ $p->category }} di blade lo yang nampilin Nama Sepatu
                    'category'    => trim($row[$keyName]),  
                    
                    'stock'       => $cleanStock,
                    'unit'        => $keyUnit !== false && isset($row[$keyUnit]) ? trim($row[$keyUnit]) : 'PSG',
                    'rack'        => $keyRack !== false && isset($row[$keyRack]) ? trim($row[$keyRack]) : '-',
                    'brand'       => $keyBrand !== false && isset($row[$keyBrand]) ? trim($row[$keyBrand]) : '-',
                    'price_cost'  => $cleanCost,
                    'price_sell'  => $cleanSell,
                    'status_jual' => $keyStatusJual !== false && isset($row[$keyStatusJual]) ? trim($row[$keyStatusJual]) : 'Masih Dijual',
                    'keterangan'  => $keyKeterangan !== false && isset($row[$keyKeterangan]) ? trim($row[$keyKeterangan]) : '-',
                    
                    // Kolom default/bawaan database iPos lo biar ga error kekosongan
                    'jenis'       => 'SPT',
                    'tipe_item'   => 'INV',
                    'system_hpp'  => 'FIFO',
                    'stok_min'    => 0,
                ]
            );
        }
        fclose($handle);

        return redirect()->back()->with('success', 'Master Data Berhasil Disinkronkan Otomatis Berdasarkan Judul Kolom!');
    }

    // 10. HAPUS DATA BARANG KELUAR
    public function destroyOutbound($id) {
        $data = Outbound::findOrFail($id);
        $data->delete();
        return back()->with('success', 'Data pengiriman berhasil dihapus.');
    }
}