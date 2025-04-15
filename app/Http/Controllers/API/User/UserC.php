<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\user;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserC extends Controller
{

    public function index()
    {
        // Ambil users, urutkan berdasarkan level lalu nama
        $users = User::orderByRaw("FIELD(level, 'admin', 'supervisor', 'petugas', 'member','pengguna')")
            ->orderBy('name')
            ->get();

        // Kembalikan view index dengan data users
        return view('user.index', compact('users'));
    }

    /**
     * Get user data for AJAX table refresh.
     *
     * @return \Illuminate\Http\Response
     */
    public function getUsers()
    {
        // Ambil users, urutkan berdasarkan nama (atau urutan lain sesuai kebutuhan refresh)
        $users = User::orderByRaw("FIELD(level, 'admin', 'supervisor', 'petugas', 'member','pengguna')")
            ->orderBy('name')
            ->get();
        // Kembalikan view tbody yang hanya berisi loop tr
        return view('user.tbody', compact('users'));
    }

    public function listAdmins()
    {
        $admins = User::where('level', 'admin')->orderBy('name')->get();
        // Buat view baru khusus untuk menampilkan admin
        return view('user.list-admin', compact('admins'));
    }
    public function listSupervisors()
    {
        $supervisors = User::where('level', 'supervisor')->orderBy('name')->get();
        // Buat view baru khusus untuk menampilkan supervisor
        return view('user.list-supervisor', compact('supervisors'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'level' => 'required|in:admin,supervisor,petugas,member,pengguna', // Sesuaikan level jika perlu
            'foto_profile' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'password' => 'required|string|min:7', // Password wajib saat create
            'status' => 'required|boolean',
        ]);

        try {
            if ($request->hasFile('foto_profile')) {
                // Simpan file ke storage/app/public/profile_photos
                $filePath = $request->file('foto_profile')->store('profile_photos', 'public');
                $validatedData['foto_profile'] = $filePath;
            }

            // Hash password sebelum disimpan
            $validatedData['password'] = Hash::make($validatedData['password']);

            $user = User::create($validatedData);

            // Berhasil, kembalikan response JSON
            return response()->json(['success' => true, 'message' => 'User berhasil ditambahkan!', 'data' => $user]);
        } catch (\Exception $e) {
            // Tangkap error jika terjadi
            Log::error('Error storing user: ' . $e->getMessage()); // Catat error
            // Kembalikan response JSON error
            return response()->json(['success' => false, 'message' => 'Gagal menambahkan user: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user // <<< Gunakan Route Model Binding
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user) // <<< Terima objek User
    {
        $validatedData = $request->validate([

            'name' => 'required|string|max:255',
            // Email unik, tapi abaikan user saat ini ($user->id)
            'email' => 'required|email|unique:users,email,' . $user->id,
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'level' => 'required|in:admin,supervisor,petugas,member,pengguna', // Sesuaikan level
            // Foto tidak wajib saat update
            'foto_profile' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            // Password tidak wajib saat update, minimal 7 jika diisi
            'password' => 'nullable|string|min:7',
            'status' => 'required|boolean',
        ]);

        try {
            // Handle upload foto jika ada file baru
            if ($request->hasFile('foto_profile')) {
                // Hapus foto lama jika ada dan bukan foto default
                if ($user->foto_profile && Storage::disk('public')->exists($user->foto_profile)) {
                    Storage::disk('public')->delete($user->foto_profile);
                }
                // Simpan foto baru
                $filePath = $request->file('foto_profile')->store('profile_photos', 'public');
                $validatedData['foto_profile'] = $filePath;
            }

            // Update password hanya jika field password diisi
            if ($request->filled('password')) {
                $validatedData['password'] = Hash::make($request->password);
            } else {
                // Jangan update password jika tidak diisi
                unset($validatedData['password']);
            }

            // Lakukan update
            $user->update($validatedData);

            // Berhasil, kembalikan response JSON
            return response()->json(['success' => true, 'message' => 'User berhasil diupdate!']);
        } catch (\Exception $e) {
            // Tangkap error
            Log::error('Error updating user ' . $user->id . ': ' . $e->getMessage());
            // Kembalikan response JSON error
            return response()->json(['success' => false, 'message' => 'Gagal mengupdate user: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user // <<< Gunakan Route Model Binding
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user) // <<< Terima object User
    {
        try {
            // Hapus foto profile dari storage sebelum hapus data user
            if ($user->foto_profile && Storage::disk('public')->exists($user->foto_profile)) {
                Storage::disk('public')->delete($user->foto_profile);
            }

            // Hapus data user
            $user->delete();

            // Berhasil, kembalikan response JSON
            return response()->json(['success' => true, 'message' => 'User berhasil dihapus!']);
        } catch (\Exception $e) {
            // Tangkap error
            Log::error('Error deleting user ' . $user->id . ': ' . $e->getMessage());
            // Kembalikan response JSON error
            return response()->json(['success' => false, 'message' => 'Gagal menghapus user: ' . $e->getMessage()], 500);
        }
    }
}
