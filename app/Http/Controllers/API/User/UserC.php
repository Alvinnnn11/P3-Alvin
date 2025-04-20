<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\user;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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

    public function listPengguna()
    {
        $penggunas = User::where('level', 'pengguna')->latest()->get();
        // Ke view baru: users.manage-pengguna
        return view('user.manage-pengguna', compact('penggunas'));
    }

    public function getPenggunaData()
    {
        // Method untuk refresh tabel /pengguna
        Log::info('getPenggunaData method called for AJAX refresh.');
        $penggunas = User::where('level', 'pengguna')->latest()->get();
         // Ke view partial baru: users.tbody-pengguna
        return view('user.tbody-pengguna', compact('penggunas'));
    }

    // == METHOD BARU UNTUK LIST ADMIN ==
    public function listAdmins()
    {
        $admins = User::where('level', 'admin')->latest()->get();
        // Ke view baru: users.manage-admins
        return view('user.manage-admins', compact('admins'));
    }

    public function getAdminData()
    {
        // Method untuk refresh tabel /admins
        Log::info('getAdminData method called for AJAX refresh.');
        $admins = User::where('level', 'admin')->latest()->get();
         // Ke view partial baru: users.tbody-admin
        return view('user.tbody-admin', compact('admins'));
    }

     // == METHOD BARU UNTUK LIST SUPERVISOR ==
     public function listSupervisors()
     {
         $supervisors = User::where('level', 'supervisor')->latest()->get();
         // Ke view baru: users.manage-supervisors
         return view('user.manage-supervisors', compact('supervisors'));
     }

     public function getSupervisorData()
     {
         // Method untuk refresh tabel /supervisors
         Log::info('getSupervisorData method called for AJAX refresh.');
         $supervisors = User::where('level', 'supervisor')->latest()->get();
          // Ke view partial baru: users.tbody-supervisor
         return view('user.tbody-supervisor', compact('supervisors'));
     }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Aturan validasi dasar
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'foto_profile' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'password' => 'required|string|min:7',
            'status' => 'required|boolean',
            'intended_level' => ['sometimes', Rule::in(['admin', 'supervisor', 'pengguna'])], 
        ];

        $levelToSave = 'pengguna'; // Default level

        // Cek apakah request datang dari form Admin/Supervisor
        if ($request->has('intended_level') && in_array($request->input('intended_level'), ['admin', 'supervisor','pengguna'])) {
            // Jika ya, level ditentukan otomatis, tidak perlu validasi 'level' dari input
            $levelToSave = $request->input('intended_level');
            Log::info('Storing user from Admin/Supervisor form. Intended Level: ' . $levelToSave);
        } else {
            // Jika dari form User utama, level HARUS dipilih dan divalidasi
            Log::info('Storing user from main User form. Validating selected level.');
            $rules['level'] = 'required|in:admin,supervisor,petugas,pengguna'; // <-- Tambahkan validasi level
        }

        // Lakukan validasi
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            Log::error('Validation failed on store:', $validator->errors()->toArray());
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Ambil data valid
        $validatedData = $validator->validated();

        // Tetapkan level yang benar
        if ($request->has('intended_level') && in_array($request->input('intended_level'), ['admin', 'supervisor','pengguna'])) {
             $validatedData['level'] = $levelToSave; // Gunakan level dari hidden input
        } else {
             // Level sudah ada di $validatedData['level'] dari dropdown form utama
              $levelToSave = $validatedData['level']; // Update variabel untuk pesan log/sukses
        }
        unset($validatedData['intended_level']); // Hapus field helper

        try {
            // ... (handle file upload, hash password) ...
             if ($request->hasFile('foto_profile')) {
                 $filePath = $request->file('foto_profile')->store('profile_photos', 'public');
                 $validatedData['foto_profile'] = $filePath;
             } else { $validatedData['foto_profile'] = null; }
             $validatedData['password'] = Hash::make($validatedData['password']);

            Log::info('Creating user with final data:', $validatedData);
            $user = User::create($validatedData);

            if ($user->level === 'pengguna') {
                Customer::create([
                    'user_id' => $user->id,
                    'saldo'   => 0, 
                ]);
                Log::info('Customer record created for pengguna: ' . $user->id);
            }

            return response()->json(['success' => true, 'message' => 'User ' . ucfirst($levelToSave) . ' berhasil ditambahkan!', 'data' => $user]);

        } catch (\Exception $e) {
             // ... (error handling) ...
             Log::error('Error storing user: ' . $e->getMessage());
             return response()->json(['success' => false, 'message' => 'Gagal menambahkan user: ' . $e->getMessage()], 500);
        }
    }


    // == METHOD UPDATE YANG DISEMPURNAKAN ==
    public function update(Request $request, User $user)
    {
        // Aturan validasi dasar
         $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'foto_profile' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'password' => 'nullable|string|min:7',
            'status' => 'required|boolean',
             // Level hanya divalidasi jika DIKIRIM (dari form user utama)
             'level' => ['sometimes', 'required', Rule::in(['admin', 'supervisor', 'petugas', 'pengguna'])],
        ];

        // Lakukan validasi
        $validator = Validator::make($request->all(), $rules);

         if ($validator->fails()) {
             Log::error('Validation failed on update for user ID ' . $user->id . ':', $validator->errors()->toArray());
             return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Ambil data valid. 'level' hanya akan ada jika dikirim & valid
        $validatedData = $validator->validated();

        try {
             // ... (handle foto) ...
              if ($request->hasFile('foto_profile')) {
                  if ($user->foto_profile && Storage::disk('public')->exists($user->foto_profile)) {
                      Storage::disk('public')->delete($user->foto_profile);
                  }
                  $filePath = $request->file('foto_profile')->store('profile_photos', 'public');
                  $validatedData['foto_profile'] = $filePath;
             }

            // Handle password jika diisi
            if ($request->filled('password')) {
                $validatedData['password'] = Hash::make($request->password);
            } else {
                // Hapus password dari array update jika KOSONG
                 unset($validatedData['password']);
            }

            // 'level' akan terupdate HANYA jika ada di $validatedData (dikirim dari form user utama)
            Log::info('Updating user ID: ' . $user->id . ' with data:', $validatedData);
            $user->update($validatedData);

            return response()->json(['success' => true, 'message' => 'User berhasil diupdate!']);

        } catch (\Exception $e) {
             // ... (error handling) ...
             Log::error('Error updating user ' . $user->id . ': ' . $e->getMessage());
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
