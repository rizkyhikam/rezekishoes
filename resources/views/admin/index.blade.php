@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <div class="mb-4">
        <h1 class="fw-bold text-dark display-6 mb-1" style="letter-spacing: -1.5px;">User Management</h1>
        <p class="text-muted small">Kelola hak akses administrator sistem RSS Warehouse.</p>
    </div>

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
            <button class="btn btn-primary fw-bold px-4 rounded-pill shadow-sm" onclick="openAddAdminModal()">
                <i class="fas fa-plus-circle me-2"></i>Tambah Admin Baru
            </button>
            <span class="badge bg-light text-muted border px-3 py-2 rounded-pill">Total: {{ count($admins) }} Admin</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-dark">
                    <thead class="bg-light text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 1px;">
                        <tr>
                            <th class="ps-4">No</th>
                            <th>Nama Lengkap</th>
                            <th>Email Address</th>
                            <th class="pe-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($admins as $key => $admin)
                        <tr>
                            <td class="ps-4">{{ $key + 1 }}</td>
                            <td class="fw-bold text-dark">{{ $admin->name }}</td>
                            <td class="text-muted">{{ $admin->email }}</td>
                            <td class="text-center pe-4">
                                <div class="btn-group shadow-sm rounded-pill overflow-hidden border">
                                    <button class="btn btn-sm btn-white text-primary border-0 px-3" onclick='openEditAdminModal({!! json_encode($admin) !!})'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-white text-danger border-0 px-3" onclick="confirmDeleteAdmin({{ $admin->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <form id="delete-admin-form-{{ $admin->id }}" action="{{ route('admin.destroy', $admin->id) }}" method="POST" style="display: none;">
                                    @csrf @method('DELETE')
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAdminPerfect" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-primary text-white py-3 border-0">
                <h5 class="modal-title fw-bold" id="modalAdminTitle">Daftarkan Admin Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAdminMaster" action="{{ route('admin.store') }}" method="POST">
                @csrf
                <div id="method_field_admin"></div>
                <div class="modal-body p-4 text-dark">
                    <div class="mb-3">
                        <label class="small fw-bold mb-2">Nama Lengkap</label>
                        <input type="text" name="name" id="adm_name" class="form-control rounded-3" placeholder="Contoh: Rizky Hikam" required>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold mb-2">Alamat Email</label>
                        <input type="email" name="email" id="adm_email" class="form-control rounded-3" placeholder="Contoh: admin@rezekishoes.com" required>
                    </div>
                    <div class="mb-0">
                        <label class="small fw-bold mb-2">Password</label>
                        <input type="password" name="password" id="adm_password" class="form-control rounded-3" placeholder="Masukkan minimal 8 karakter">
                        <small class="text-muted mt-2 d-block" id="passHint" style="display:none; font-style: italic;">
                            *Kosongkan jika tidak ingin mengubah password
                        </small>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2 rounded-pill shadow">Simpan Data Admin</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Pake listener biar aman gak macet
    document.addEventListener('DOMContentLoaded', function() {
        const adminModalEl = document.getElementById('modalAdminPerfect');
        const btmModal = new bootstrap.Modal(adminModalEl);
        const formAdm = document.getElementById('formAdminMaster');

        window.openAddAdminModal = function() {
            formAdm.reset();
            formAdm.action = "{{ route('admin.store') }}";
            document.getElementById('method_field_admin').innerHTML = '';
            document.getElementById('modalAdminTitle').innerText = 'Daftarkan Admin Baru';
            document.getElementById('adm_password').required = true;
            document.getElementById('passHint').style.display = 'none';
            btmModal.show();
        };

        window.openEditAdminModal = function(data) {
            formAdm.reset();
            formAdm.action = "/kelola-admin/update/" + data.id;
            document.getElementById('method_field_admin').innerHTML = '@method("POST")';
            document.getElementById('modalAdminTitle').innerText = 'Perbarui Akses Admin';
            
            document.getElementById('adm_name').value = data.name;
            document.getElementById('adm_email').value = data.email;
            document.getElementById('adm_password').required = false;
            document.getElementById('passHint').style.display = 'block';
            
            btmModal.show();
        };
    });

    function confirmDeleteAdmin(id) {
        Swal.fire({
            title: 'Hapus Akun Admin?',
            text: "Akses login akan dicabut secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus Akun',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            customClass: { confirmButton: 'rounded-pill px-4', cancelButton: 'rounded-pill px-4' }
        }).then((result) => {
            if (result.isConfirmed) document.getElementById('delete-admin-form-' + id).submit();
        });
    }
</script>
@endsection