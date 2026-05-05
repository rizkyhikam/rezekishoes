<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    // 1. TAMPILAN DAFTAR ADMIN
    public function index() {
        $admins = User::latest()->get();
        return view('admin.index', compact('admins'));
    }

    // 2. SIMPAN ADMIN BARU (DARI MODAL TAMBAH)
    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Admin baru berhasil didaftarkan!');
    }

    // 3. UPDATE DATA ADMIN (DARI MODAL EDIT)
    public function update(Request $request, $id) {
        $user = User::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$id,
            'password' => 'nullable|min:8', // Boleh kosong kalau gak mau ganti pass
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        // Ganti password cuma kalau input password diisi
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return back()->with('success', 'Data admin berhasil diperbarui!');
    }

    // 4. HAPUS ADMIN (DENGAN PROTEKSI)
    public function destroy($id) {
        // Jangan biarkan hapus diri sendiri
        if ($id == Auth::id()) {
            return back()->with('error', 'Lo nggak bisa hapus akun lo sendiri yang lagi dipake!');
        }

        // Jangan biarkan hapus kalau sisa 1 admin doang
        if (User::count() <= 1) {
            return back()->with('error', 'Minimal harus ada 1 admin yang tersisa di sistem!');
        }

        User::findOrFail($id)->delete();
        return back()->with('success', 'Admin berhasil dihapus!');
    }
}