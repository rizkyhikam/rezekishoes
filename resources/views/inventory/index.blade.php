@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container-fluid p-0">
    <div class="mb-4 d-flex justify-content-between align-items-end">
        <div>
            <h1 class="fw-bold text-dark display-6 mb-1" style="letter-spacing: -1.5px;">Stock Barang</h1>
            <p class="text-muted small mb-0"><i class="fas fa-layer-group me-1"></i> Manajemen inventori standar profesional iPos.</p>
        </div>
        <div class="text-end border-start ps-3 d-none d-md-block">
            <div class="text-muted small text-uppercase fw-bold" style="font-size: 0.65rem;">Total Inventori</div>
            <div class="h4 fw-bold text-primary mb-0">{{ count($products) }} <span class="small fw-light text-muted">Items</span></div>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-header bg-white py-4 px-4 border-bottom border-light">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary fw-bold px-4 rounded-pill shadow-sm" onclick="openAddModal()">
                        <i class="fas fa-plus-circle me-2"></i>Tambah Barang
                    </button>
                    <button type="button" class="btn btn-outline-success fw-bold px-4 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#modalImport">
                        <i class="fas fa-file-excel me-2"></i>Import Excel
                    </button>
                </div>
                <div class="ms-auto position-relative d-none d-lg-block" style="width: 250px;">
                    <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" class="form-control rounded-pill ps-5 bg-light border-0" placeholder="Cari Kode atau Nama...">
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-dark" style="font-size: 0.72rem; white-space: nowrap;">
                    <thead class="bg-light text-muted text-uppercase" style="font-size: 0.62rem; letter-spacing: 0.5px;">
                        <tr>
                            <th class="ps-4">No</th>
                            <th>Kode Item</th>
                            <th>Barcode</th>
                            <th>Nama Item</th>
                            <th>Stok</th>
                            <th>Satuan</th>
                            <th>Rak</th>
                            <th>Jenis</th>
                            <th>Merek</th>
                            <th>Harga Pokok</th>
                            <th>Harga Jual</th>
                            <th>Tipe Item</th>
                            <th>System HPP</th>
                            <th>Stok Min.</th>
                            <th>Status Jual</th>
                            <th>Keterangan</th>
                            <th class="pe-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $key => $p)
                        <tr>
                            <td class="ps-4">{{ $key + 1 }}</td>
                            <td class="fw-bold text-primary">{{ $p->sku }}</td>
                            <td class="fw-mono text-muted">{{ $p->barcode ?? '-' }}</td>
                            <td class="fw-semibold" style="max-width: 250px; overflow: hidden; text-overflow: ellipsis;">{{ $p->category }}</td>
                            <td class="fw-bold text-dark">{{ number_format($p->stock, 2) }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $p->unit ?? 'PSG' }}</span></td>
                            <td>{{ $p->rack ?? '-' }}</td>
                            <td>SPT</td> <td>{{ $p->brand ?? '-' }}</td>
                            <td>Rp{{ number_format($p->price_cost, 0, ',', '.') }}</td>
                            <td class="fw-bold text-success">Rp{{ number_format($p->price_sell, 0, ',', '.') }}</td>
                            <td>INV</td> <td>FIFO</td> <td>0,00</td>
                            <td>
                                <span class="badge rounded-pill {{ $p->status_jual == 'Masih Dijual' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                    {{ $p->status_jual ?? 'Masih Dijual' }}
                                </span>
                            </td>
                            <td class="text-muted small">-</td>
                            <td class="text-center pe-4">
                                <div class="btn-group shadow-sm rounded-pill border overflow-hidden">
                                    <button type="button" class="btn btn-sm btn-white text-primary border-0" onclick='openEditModal({!! json_encode($p) !!})'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-white text-danger border-0" onclick="deleteProduct({{ $p->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <form id="delete-form-{{ $p->id }}" action="{{ route('inventory.destroy', $p->id) }}" method="POST" style="display: none;">
                                    @csrf @method('DELETE')
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="17" class="text-center py-5 text-muted">Data masih kosong.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalMasterBarang" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg text-dark">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold" id="modalTitle">Data Master Barang</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formMaster" action="{{ route('inventory.store') }}" method="POST">
                @csrf
                <div id="method_field"></div>
                <div class="modal-body p-4 bg-light">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="card card-body border-0 shadow-sm p-3 h-100">
                                <h6 class="fw-bold small mb-3 text-primary border-bottom pb-2">IDENTITAS ITEM</h6>
                                <label class="small fw-bold mb-1 text-primary">Kode Item (SKU) *</label>
                                <input type="text" name="sku" id="sku" class="form-control form-control-sm mb-2" required>
                                
                                <label class="small fw-bold mb-1 text-danger">Barcode</label>
                                <input type="text" name="barcode" id="barcode" class="form-control form-control-sm mb-2">
                                
                                <label class="small fw-bold mb-1">Nama Item *</label>
                                <textarea name="category" id="category" class="form-control form-control-sm mb-2" rows="2" required></textarea>
                                
                                <label class="small fw-bold mb-1">Merek</label>
                                <input type="text" name="brand" id="brand" class="form-control form-control-sm">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card card-body border-0 shadow-sm p-3 h-100">
                                <h6 class="fw-bold small mb-3 text-success border-bottom pb-2">STOK & LOKASI</h6>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="small fw-bold mb-1">Stok Awal</label>
                                        <input type="number" name="stock" id="stock" class="form-control form-control-sm mb-2" value="0">
                                    </div>
                                    <div class="col-6">
                                        <label class="small fw-bold mb-1">Stok Min</label>
                                        <input type="number" name="stok_min" id="stok_min" class="form-control form-control-sm mb-2" value="0">
                                    </div>
                                </div>
                                
                                <label class="small fw-bold mb-1">Satuan</label>
                                <select name="unit" id="unit" class="form-select form-select-sm mb-2">
                                    <option value="PSG">PSG</option>
                                    <option value="PCS">PCS</option>
                                    <option value="BOX">BOX</option>
                                </select>

                                <label class="small fw-bold mb-1">Posisi Rak</label>
                                <input type="text" name="rack" id="rack" class="form-control form-control-sm" placeholder="A1-01">
                                
                                <label class="small fw-bold mt-2 mb-1">Tipe Item</label>
                                <input type="text" class="form-control form-control-sm bg-light" value="Inventory" readonly>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card card-body border-0 shadow-sm p-3 h-100">
                                <h6 class="fw-bold small mb-3 text-warning border-bottom pb-2">HARGA & STATUS</h6>
                                <label class="small fw-bold mb-1">Sistem HPP</label>
                                <input type="text" class="form-control form-control-sm bg-light mb-2" value="FIFO" readonly>

                                <label class="small fw-bold mb-1">Harga Pokok (Modal)</label>
                                <input type="number" name="price_cost" id="price_cost" class="form-control form-control-sm mb-2" value="0">

                                <label class="small fw-bold mb-1 text-success">Harga Jual</label>
                                <input type="number" name="price_sell" id="price_sell" class="form-control form-control-sm mb-2 border-success text-success fw-bold" value="0">

                                <label class="small fw-bold mb-1">Status Jual</label>
                                <select name="status_jual" id="status_jual" class="form-select form-select-sm">
                                    <option value="Masih Dijual">Masih Dijual</option>
                                    <option value="Tidak Dijual">Tidak Dijual</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-top-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-5 fw-bold shadow-sm rounded-pill">Simpan ke Master iPos</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalImport" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered text-dark">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white py-3">
                <h5 class="modal-title fw-bold">Import Master iPos (.csv)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('inventory.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4 text-center">
                    <i class="fas fa-file-csv fa-3x text-success mb-3"></i>
                    <input type="file" name="file" class="form-control mb-2" required>
                    <p class="small text-muted">Pastikan urutan kolom sesuai standar iPos Professional.</p>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="submit" class="btn btn-success w-100 fw-bold">Upload & Sinkronkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modalElement = document.getElementById('modalMasterBarang');
        const myModal = new bootstrap.Modal(modalElement);
        const formMaster = document.getElementById('formMaster');

        window.openAddModal = function() {
            formMaster.reset();
            formMaster.action = "{{ route('inventory.store') }}";
            document.getElementById('method_field').innerHTML = '';
            document.getElementById('modalTitle').innerText = 'Tambah Data Barang Baru';
            myModal.show();
        };

        window.openEditModal = function(data) {
            formMaster.reset();
            formMaster.action = "/inventory/update/" + data.id;
            document.getElementById('method_field').innerHTML = '@method("POST")';
            document.getElementById('modalTitle').innerText = 'Edit Item: ' + data.sku;

            document.getElementById('sku').value = data.sku;
            document.getElementById('barcode').value = data.barcode;
            document.getElementById('category').value = data.category;
            document.getElementById('brand').value = data.brand;
            document.getElementById('stock').value = data.stock;
            document.getElementById('unit').value = data.unit;
            document.getElementById('rack').value = data.rack;
            document.getElementById('price_cost').value = data.price_cost;
            document.getElementById('price_sell').value = data.price_sell;
            document.getElementById('status_jual').value = data.status_jual;

            myModal.show();
        };
    });

    function deleteProduct(id) {
        Swal.fire({
            title: 'Hapus Item?',
            text: "Data akan hilang permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) document.getElementById('delete-form-' + id).submit();
        })
    }
</script>
@endsection