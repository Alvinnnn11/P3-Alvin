<?php

namespace App\Http\Controllers\API\AUTH;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

        Log::info('Mencoba login untuk: ' . $request->email); // Log Awal

        if (Auth::attempt($credentials)) {
            // Autentikasi BERHASIL
            $request->session()->regenerate();
            /** @var \App\Models\User $user */
            $user = Auth::user(); // Dapatkan objek user yang berhasil login
            Log::info('Login BERHASIL untuk User ID: ' . $user->id . ', Email: ' . $user->email . ', Level: ' . $user->level); // Log Level

            // **MULAI DEBUGGING SESSION CABANG**
            session()->forget('assigned_cabang'); // Hapus session lama dulu untuk tes bersih
            Log::info('Session assigned_cabang dibersihkan (jika ada).');

            if ($user->level === 'petugas') {
                Log::info('User adalah petugas. Mencoba load relasi Petugas.cabang...');
                try {
                    // Coba load relasi
                    $user->load('Petugas.cabang'); // Pastikan nama relasi benar!

                    // Cek apakah relasi berhasil di-load
                    $assignment = $user->Petugas;
                    $cabang = $assignment ? $assignment->cabang : null; // Ambil cabang dari assignment

                    if ($assignment && $cabang) {
                        Log::info('Relasi ditemukan: Assignment ID=' . $assignment->id . ', Cabang ID=' . $cabang->id . ', Nama Cabang=' . $cabang->nama_perusahaan);

                        // **Simpan ke Session**
                        session(['assigned_cabang' => $cabang]); // atau Session::put('assigned_cabang', $cabang);

                        // **Verifikasi Session Langsung**
                        if (session()->has('assigned_cabang')) {
                            $savedCabang = session('assigned_cabang');
                            Log::info('VERIFIKASI: Session assigned_cabang BERHASIL disimpan. ID Cabang di Session: ' . $savedCabang->id);
                        } else {
                            Log::error('VERIFIKASI GAGAL: Session assigned_cabang TIDAK ditemukan setelah disimpan!');
                        }

                    } else {
                        // Log jika relasi tidak ditemukan
                        Log::warning('Petugas User ID: ' . $user->id . ' tidak memiliki penugasan cabang yang valid.');
                        if (!$assignment) {
                            Log::warning('Detail: Tidak ada record di tabel `petugas` untuk user_id=' . $user->id);
                        } elseif (!$cabang) {
                             Log::warning('Detail: Ada record penugasan (ID: '.$assignment->id.'), tapi relasi ke cabang (cabang_id: '.$assignment->cabang_id.') tidak valid/ditemukan.');
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Error saat load relasi Petugas.cabang: ' . $e->getMessage());
                }
            } else {
                Log::info('User bukan petugas, tidak perlu menyimpan info cabang.');
            }
            // **AKHIR DEBUGGING SESSION CABANG**

             // Log isi session sebelum redirect
             Log::info('Isi session sebelum redirect:', session()->all());

            return redirect()->intended('/dashboard');

        }

        // Jika Auth::attempt GAGAL
        Log::warning('Login GAGAL (Credentials Salah) untuk: ' . $request->email);
        return back()->with('error', 'Email atau password salah.')->withInput($request->only('email'));
    }

    

    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'nis' => 'required|string|max:20|unique:users,nis',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'name' => 'required|string|max:255',
        ]);

        $user = User::create([
            'nis' => $validatedData['nis'],
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'level' => 'pengguna',
        ]);
    return redirect('/login')->with('success', 'Registrasi berhasil! Selamat datang di dashboard.');
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login')->with('success', 'Logout berhasil.');
    }

}
