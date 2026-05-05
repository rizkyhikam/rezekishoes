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
            Manajemen Pengiriman <span class="fw-light text-muted">| Barang Keluar</span>
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
            <span class="text-muted small"><i class="fas fa-barcode me-1"></i> Scanner Active...</span>
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
                                <div class="small text-muted">{{ $data->brand ?? 'No Brand' }}</div>
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
            <div id="barcodeArea" class="mb-3 d-flex justify-content-center"></div>
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
                        <label class="small fw-bold mb-1">Cari Nama Item</label>
                        <input type="text" name="category" class="form-control" placeholder="Contoh: Sepatu Safety" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="small fw-bold mb-1">Kode SKU</label>
                            <input type="text" name="sku" class="form-control" placeholder="SKU-01" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="small fw-bold mb-1 text-danger">Barcode</label>
                            <input type="text" name="barcode" class="form-control" placeholder="Ketik/Scan" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="small fw-bold mb-1">Qty</label>
                            <input type="number" name="jumlah" class="form-control" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="small fw-bold mb-1">Ekspedisi</label>
                            <select name="ekspedisi" class="form-select">
                                <option value="J&T">J&T</option>
                                <option value="GoSend">GoSend</option>
                                <option value="SPX">SPX</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="small fw-bold mb-1">Nama Penerima</label>
                        <input type="text" name="penerima" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Simpan Pesanan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Scanner focus otomatis
    document.addEventListener('click', () => document.getElementById('barcodeScanner').focus());

    function showBarcode(code, name) {
        document.getElementById('zoomTitle').innerText = name;
        document.getElementById('zoomCodeText').innerText = code;
        document.getElementById('barcodeArea').innerHTML = `<svg id="barcodeDisplay"></svg>`;
        JsBarcode("#barcodeDisplay", code, { width: 2.5, height: 80, displayValue: false });
        new bootstrap.Modal(document.getElementById('modalBarcodeZoom')).show();
    }

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

    document.getElementById('barcodeScanner').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendRequest(this.value, 'update');
            this.value = '';
        }
    });
</script>
@endsection