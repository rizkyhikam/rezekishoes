@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* Biar inputan pencarian datatable gak berantakan */
    .datatable-top { padding: 15px 20px !important; background: white; }
    .datatable-input { background-color: #f8f9fa !important; border: 1px solid #dee2e6 !important; }
</style>

<div class="container-fluid p-0">
    <div class="mb-4 text-start">
        <h1 class="fw-bold text-white display-6" style="letter-spacing: -1.5px;">
        <span class="fw-light text-muted">Manajemen Pengiriman | Barang Keluar</span>
        </h1>
    </div>

    <div class="btn-group mb-4 shadow-sm" role="group">
        @php $statuses = ['Semua', 'Perlu Dikirim', 'Dikirim', 'Selesai', 'Dibatalkan']; @endphp
        @foreach($statuses as $st)
            <a href="{{ route('barang.keluar', ['status' => $st]) }}" 
               class="btn {{ $status == $st ? 'btn-primary' : 'btn-dark border-secondary text-muted' }} px-4">
               {{ $st }}
            </a>
        @endforeach
    </div>

    <input type="text" id="barcodeScanner" style="position: absolute; opacity: 0;" autofocus>

    <div class="card shadow-sm border-0">
        <div class="card-header py-3 bg-white border-bottom d-flex justify-content-between align-items-center">
            <button class="btn btn-primary fw-bold px-4" data-bs-toggle="modal" data-bs-target="#modalKeluar">
                <i class="fas fa-plus-circle me-2"></i>Input Pesanan Baru
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="datatablesSimple" class="table table-hover align-middle mb-0 text-dark" style="font-size: 0.85rem;">
                    <thead class="table-light text-muted text-uppercase" style="font-size: 0.7rem;">
                        <tr>
                            <th class="ps-4">Tanggal</th>
                            <th>Nama Item / Merek</th>
                            <th>Kode (SKU)</th>
                            <th>Barcode</th>
                            <th>Qty</th>
                            <th>Ekspedisi</th>
                            <th>Status</th>
                            <th class="pe-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($outbounds as $data)
                        <tr>
                            <td class="ps-4 text-muted small">{{ $data->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="fw-bold">{{ $data->category }}</div>
                            </td>
                            <td><code class="text-primary fw-bold">{{ $data->sku }}</code></td>
                            <td>
                                <button class="btn btn-sm btn-outline-dark px-2 py-0" 
                                        onclick="showBarcode('{{ $data->barcode }}', '{{ $data->category }}')" 
                                        style="font-size: 0.7rem;">
                                    <i class="fas fa-barcode me-1 text-muted"></i> {{ $data->barcode ?? 'N/A' }}
                                </button>
                            </td>
                            <td class="fw-bold text-info">{{ $data->jumlah }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $data->ekspedisi }}</span></td>
                            <td>
                                @php
                                    $badgeClass = [
                                        'Perlu Dikirim' => 'bg-warning text-dark',
                                        'Dikirim' => 'bg-success',
                                        'Selesai' => 'bg-primary',
                                        'Dibatalkan' => 'bg-danger'
                                    ][$data->status] ?? 'bg-secondary';
                                @endphp
                                <span class="badge {{ $badgeClass }} px-2 py-1" style="font-size: 0.7rem;">
                                    {{ $data->status }}
                                </span>
                            </td>
                            <td class="pe-4 text-center">
                                <div class="btn-group">
                                    @if($data->status != 'Selesai' && $data->status != 'Dibatalkan')
                                    <button class="btn btn-sm text-success" onclick="manualUpdate('{{ $data->barcode }}')" title="Proses">
                                        <i class="fas fa-check-circle"></i>
                                    </button>
                                    <button class="btn btn-sm text-warning" onclick="cancelOrder('{{ $data->barcode }}')" title="Batalkan">
                                        <i class="fas fa-times-circle"></i>
                                    </button>
                                    @endif
                                    
                                    <button class="btn btn-sm text-danger" onclick="deleteOrder('{{ $data->id }}')" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                    <form id="form-delete-{{ $data->id }}" action="{{ route('outbound.destroy', $data->id) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted italic">Belum ada data pengiriman.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalBarcodeZoom" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content bg-white text-center p-4 shadow-lg border-0 text-dark">
            <h6 class="fw-bold mb-3" id="zoomTitle"></h6>
            <div id="barcodeArea" class="mb-3 d-flex justify-content-center overflow-hidden">
                <svg id="barcodeDisplay"></svg>
            </div>
            <h5 class="fw-bold text-primary" id="zoomCodeText"></h5>
            <button type="button" class="btn btn-secondary btn-sm mt-3 w-100" data-bs-dismiss="modal">Tutup</button>
        </div>
    </div>
</div>

<div class="modal fade" id="modalKeluar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg text-dark">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold">Input Pesanan Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('outbound.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="small fw-bold mb-1">Pilih Produk / Barang *</label>
                        <select name="product_select" id="product_select" class="form-select" required onchange="updateProductDetails()">
                            <option value="">-- Pilih Produk dari Stok Gudang --</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}" 
                                        data-sku="{{ $p->sku }}" 
                                        data-barcode="{{ $p->barcode }}" 
                                        data-name="{{ $p->category }}"
                                        data-stock="{{ $p->stock }}">
                                    {{ $p->category }} [Merek: {{ $p->brand ?? '-' }}] - Sisa Stok: ({{ number_format($p->stock, 0) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <input type="hidden" name="category" id="hidden_category">
                    <input type="hidden" name="sku" id="hidden_sku">
                    <input type="hidden" name="barcode" id="hidden_barcode">

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="small fw-bold mb-1 text-muted">Kode SKU (Otomatis)</label>
                            <input type="text" id="display_sku" class="form-control bg-light" placeholder="Terisi Otomatis" readonly disabled>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="small fw-bold mb-1 text-muted">Barcode (Otomatis)</label>
                            <input type="text" id="display_barcode" class="form-control bg-light" placeholder="Terisi Otomatis" readonly disabled>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="small fw-bold mb-1">Qty (Jumlah Keluar) *</label>
                            <input type="number" name="jumlah" id="input_jumlah" class="form-control" min="1" placeholder="0" required oninput="validateMaxStock()">
                            <small id="stock_warning" class="text-danger small d-none">Jumlah keluar melebihi stok!</small>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="small fw-bold mb-1">Ekspedisi *</label>
                            <select name="ekspedisi" class="form-select" required>
                                <option value="J&T">J&T</option>
                                <option value="GoSend">GoSend</option>
                                <option value="SPX">SPX</option>
                                <option value="NinjaVan">NinjaVan</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="small fw-bold mb-1">Nama Penerima *</label>
                        <input type="text" name="penerima" class="form-control" placeholder="Masukkan nama pembeli" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="submit" id="btn_submit_pesanan" class="btn btn-primary w-100 fw-bold">Simpan Pesanan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // 1. SCANNER FOCUS DENGAN PENGAMAN MODAL
    document.addEventListener('click', function(e) {
        const modalKeluar = document.getElementById('modalKeluar');
        const modalBarcodeZoom = document.getElementById('modalBarcodeZoom');
        
        if ((modalKeluar && modalKeluar.classList.contains('show')) || 
            (modalBarcodeZoom && modalBarcodeZoom.classList.contains('show'))) {
            return; 
        }
        
        document.getElementById('barcodeScanner').focus();
    });

    document.getElementById('modalKeluar').addEventListener('hidden.bs.modal', function () {
        document.getElementById('barcodeScanner').focus();
    });
    document.getElementById('modalBarcodeZoom').addEventListener('hidden.bs.modal', function () {
        document.getElementById('barcodeScanner').focus();
    });

    // 2. FUNGSI DROPDOWN OTOMATIS
    function updateProductDetails() {
        const select = document.getElementById('product_select');
        const selectedOption = select.options[select.selectedIndex];

        if (selectedOption && selectedOption.value !== "") {
            const sku = selectedOption.getAttribute('data-sku');
            const barcode = selectedOption.getAttribute('data-barcode');
            const name = selectedOption.getAttribute('data-name');
            const stock = selectedOption.getAttribute('data-stock');

            document.getElementById('display_sku').value = sku;
            document.getElementById('display_barcode').value = barcode;
            
            document.getElementById('hidden_sku').value = sku;
            document.getElementById('hidden_barcode').value = barcode;
            document.getElementById('hidden_category').value = name;

            document.getElementById('input_jumlah').setAttribute('max', stock);
        } else {
            document.getElementById('display_sku').value = "";
            document.getElementById('display_barcode').value = "";
            document.getElementById('hidden_sku').value = "";
            document.getElementById('hidden_barcode').value = "";
            document.getElementById('hidden_category').value = "";
            document.getElementById('input_jumlah').removeAttribute('max');
        }
        validateMaxStock();
    }
    
    // Validasi input Qty vs Stok Asli
    function validateMaxStock() {
        const select = document.getElementById('product_select');
        const selectedOption = select.options[select.selectedIndex];
        const inputJumlah = document.getElementById('input_jumlah');
        const warning = document.getElementById('stock_warning');
        const btnSubmit = document.getElementById('btn_submit_pesanan');

        if (selectedOption && selectedOption.value !== "") {
            const currentStock = parseInt(selectedOption.getAttribute('data-stock'));
            const inputStock = parseInt(inputJumlah.value) || 0;

            if (inputStock > currentStock) {
                warning.classList.remove('d-none');
                btnSubmit.setAttribute('disabled', true);
            } else {
                warning.classList.add('d-none');
                btnSubmit.removeAttribute('disabled');
            }
        }
    }

    // 3. POPUP ZOOM BARCODE
    function showBarcode(code, name) {
        document.getElementById('zoomTitle').innerText = name;
        document.getElementById('zoomCodeText').innerText = code;
        
        JsBarcode("#barcodeDisplay", code, { 
            format: "CODE128",
            width: 2.5, 
            height: 70, 
            displayValue: false 
        });
        
        var myModal = new bootstrap.Modal(document.getElementById('modalBarcodeZoom'));
        myModal.show();
    }

    // 4. LOGIKA SWEETALERT UPDATE STATUS SCAN & TOMBOL
    function manualUpdate(code) {
        Swal.fire({
            title: 'Proses Pesanan?',
            text: "Status akan naik ke tahap selanjutnya",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Proses!'
        }).then((result) => { if (result.isConfirmed) sendRequest(code, 'update'); })
    }

    function cancelOrder(code) {
        Swal.fire({
            title: 'Batalkan Pesanan?',
            text: "Data akan dipindah ke daftar Dibatalkan",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f39c12',
            confirmButtonText: 'Ya, Batalkan!'
        }).then((result) => { if (result.isConfirmed) sendRequest(code, 'cancel'); })
    }

    function deleteOrder(id) {
        Swal.fire({
            title: 'Hapus Data?',
            text: "Data ini akan hilang selamanya!",
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Hapus!'
        }).then((result) => { if (result.isConfirmed) document.getElementById('form-delete-' + id).submit(); })
    }

    // 5. KIRIM AJAX REQUEST KE BACKEND
    function sendRequest(code, actionType) {
        fetch("{{ route('barcode.scan') }}", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            body: JSON.stringify({ barcode: code, action: actionType })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                Swal.fire('Berhasil!', data.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Gagal!', data.message, 'error');
            }
        });
    }

    // Listener otomatis scanner enter fisik
    document.getElementById('barcodeScanner').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendRequest(this.value, 'update');
            this.value = '';
        }
    });
</script>
@endsection

@if(session('success'))
<script>
    Swal.fire({ icon: 'success', title: 'Berhasil!', text: "{{ session('success') }}", timer: 3000, showConfirmButton: false });
</script>
@endif

@if(session('error'))
<script>
    Swal.fire({ icon: 'error', title: 'Opps, Gagal!', text: "{{ session('error') }}" });
</script>
@endif