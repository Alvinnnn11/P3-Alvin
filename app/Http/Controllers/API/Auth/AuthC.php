<?php

namespace App\Http\Controllers\API\AUTH;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password; // <-- Tambahkan ini

class AuthC extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showRegisterForm()
    {
        return view('auth.regis');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        Log::info('Mencoba login untuk: ' . $request->email);
        $user = User::where('email', $credentials['email'])->first();

    
            if (!$user) {
                Log::warning('Percobaan login gagal (email tidak ditemukan): ' . $request->email);
                // Kembali dengan pesan error umum (jangan beritahu emailnya tidak ada)
                return back()->with('error', 'Email atau password salah.')->withInput($request->only('email'));
            }
            if (!$user->status) { // Cek jika statusnya false atau 0 (Tidak Aktif)
                Log::warning('Percobaan login gagal (akun tidak aktif): ' . $request->email . ' (User ID: ' . $user->id . ')');
                // Kembali dengan pesan error spesifik untuk akun nonaktif
                return back()->with('error', 'Akun Anda tidak aktif. Silakan hubungi administrator.')->withInput($request->only('email'));
            }
            if (!Hash::check($credentials['password'], $user->password)) {
                Log::warning('Percobaan login gagal (password salah): ' . $request->email . ' (User ID: ' . $user->id . ')');
                // Kembali dengan pesan error umum
                return back()->with('error', 'Email atau password salah.')->withInput($request->only('email'));
            }
            Auth::login($user);
            session()->forget('assigned_cabang'); // Selalu bersihkan session cabang saat login baru
            $redirectRoute = '/dashboard'; // Default redirect

            if ($user->level === 'petugas') {
                Log::info('User adalah petugas. Mencoba load assignment...');
                            $user->load('Petugas.cabang');
                if ($user->Petugas && $user->Petugas->cabang) {
                    $assignedCabang = $user->Petugas->cabang;
                    Log::info('Ditemukan Cabang Penugasan: ID=' . $assignedCabang->id . ', Nama=' . $assignedCabang->nama_perusahaan);
                    session(['assigned_cabang' => $assignedCabang]);
                    // Petugas langsung ke dashboard
                } else {
                    Log::warning('Petugas User ID: ' . $user->id . ' tidak memiliki penugasan cabang yang valid.');
                    // Tetap ke dashboard, tapi tanpa info cabang di session
                }
            } elseif ($user->level === 'member' || $user->level === 'pengguna') {
                Log::info('User adalah member/pengguna. Mengarahkan ke pemilihan cabang.');
                // Ganti tujuan redirect ke halaman pemilihan cabang
                $redirectRoute = route('cabang.select'); // Gunakan nama route
            } else {
                 Log::info('User adalah admin/supervisor. Mengarahkan ke dashboard (global).');
                 // Admin/Supervisor langsung ke dashboard (tanpa session cabang)
            }
            Log::info('Isi session sebelum redirect:', session()->all());
            // Redirect ke tujuan yang sudah ditentukan
            return redirect()->intended($redirectRoute);
              
        Log::warning('Login GAGAL (Credentials Salah) untuk: ' . $request->email);
        return back()->with('error', 'Email atau password salah.')->withInput($request->only('email'));
    }

    // ... (register, logout) ...
     public function register(Request $request)
     {
         $validatedData = $request->validate([
             // 'nis' => 'nullable|string|max:20|unique:users,nis', // Jadikan nullable jika opsional
             'email' => 'required|email|unique:users,email',
             'password' => 'required|string|min:8|confirmed',
             'name' => 'required|string|max:255',
             'alamat' => 'required|string', // Tambahkan validasi jika input ada
             'phone' => 'required|string|max:20', // Tambahkan validasi jika input ada
         ]);

         $user = User::create([
             // 'nis' => $validatedData['nis'] ?? null,
             'name' => $validatedData['name'],
             'email' => $validatedData['email'],
             'password' => Hash::make($validatedData['password']),
             'level' => 'pengguna', // Default level 'pengguna'
             'address' => $validatedData['alamat'], // Simpan alamat
             'phone' => $validatedData['phone'], // Simpan telepon
         ]);
         return redirect()->route('login')->with('success', 'Registrasi berhasil! Silakan login.');
     }
       public function logout(Request $request)
       {
           $userEmail = Auth::user()->email ?? 'Guest';
           Auth::logout();
           $request->session()->invalidate();
           $request->session()->regenerateToken();
           session()->forget('assigned_cabang'); // Hapus session cabang saat logout
           Log::info('Logout berhasil: ' . $userEmail);
           return redirect('/login')->with('success', 'Logout berhasil.');
       }


}
