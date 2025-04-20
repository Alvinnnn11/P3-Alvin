<?php

namespace App\Http\Controllers\API\Settingg;

use App\Http\Controllers\Controller;
use App\Models\setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Cabang;
use Illuminate\Support\Facades\Log;

class SettingC extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $cabangInfo = session('assigned_cabang'); // Ambil dari session
        $setting = null; // Inisialisasi setting global
        $isBranchView = false; // Defaultnya bukan view cabang

        // Cek apakah user adalah petugas ATAU supervisor DAN punya info cabang di session
        if (in_array($user->level, ['petugas', 'supervisor'])) {
            if ($cabangInfo) {
                // Coba reload dari DB
                $cabangInfo = Cabang::find($cabangInfo->id);
                if ($cabangInfo) {
                    session(['assigned_cabang' => $cabangInfo]); // Update session
                    $isBranchView = true;
                    Log::info("Displaying branch view for {$user->level} ID: {$user->id}, Cabang ID: {$cabangInfo->id}");
                } else {
                    Log::error("Assigned cabang ID {$cabangInfo->id} not found in DB for user {$user->id}. Clearing session.");
                    session()->forget('assigned_cabang');
                    $cabangInfo = null; // Pastikan null jika tidak ketemu
                    // Biarkan $isBranchView false, akan jatuh ke logika else di bawah jika admin, atau else di view jika petugas/spv
                }
            } else {
                 Log::warning("User {$user->level} ID: {$user->id} has no assigned_cabang in session.");
                 // Biarkan $isBranchView false
            }
        }
    
        // Jika BUKAN view cabang, coba ambil setting global (hanya untuk admin?)
        // Asumsi hanya admin yang bisa lihat/edit setting global
        if (!$isBranchView && $user->level === 'admin') {
            Log::info("User is admin. Fetching global settings.");
            $setting = Setting::first(); // Gunakan S Kapital
            if (!$setting) {
                Log::warning("Global setting record not found in 'settings' table.");
                // Anda bisa memilih:
                // 1. Buat objek kosong agar form tetap tampil
                 $setting = new Setting();
                // 2. Atau biarkan null dan tampilkan pesan error di view jika $setting null
                // $setting = null; // <-- Pilihan 2
            } else {
                 Log::info("Global setting found.");
            }
        } else if (!$isBranchView && !in_array($user->level, ['petugas', 'supervisor', 'admin'])) {
            // Handle user level lain yg tidak punya view cabang & bukan admin
             Log::warning("User level '{$user->level}' does not have access to global settings or branch view.");
             // Mungkin redirect atau set variabel agar view menampilkan pesan akses ditolak
             // $setting = null; // Pastikan null
             // $cabangInfo = null;
        }
    
    
        Log::info('Variables before returning setting.index view:', [
            'isBranchView' => $isBranchView,
            'cabangInfo_isset' => isset($cabangInfo),
            'setting_isset' => isset($setting),
            'setting_type' => isset($setting) ? get_class($setting) : gettype($setting)
        ]);
    
        // View akan menampilkan form cabang jika $isBranchView=true & $cabangInfo ada
        // View akan menampilkan form global jika $isBranchView=false & $setting ada (meski kosong)
        // View akan menampilkan error jika $isBranchView=false & $setting null (jika Anda memilih opsi 2 di atas)
        return view('setting.index', compact('setting', 'cabangInfo', 'isBranchView'));
    }

    /**
     * Mengupdate pengaturan global (HANYA untuk admin/supervisor).
     */
    public function update(Request $request) // Tidak perlu ID setting, kita tentukan targetnya
    {
        $user = Auth::user();

        if (in_array($user->level, ['petugas', 'supervisor'])) {
            // --- LOGIKA UPDATE CABANG (UNTUK PETUGAS/SUPERVISOR) ---
            $cabangInfo = session('assigned_cabang');
            if (!$cabangInfo) { return redirect()->route('setting.index')->with('error', 'Data cabang Anda tidak ditemukan di session.'); }
            $cabangToUpdate = Cabang::find($cabangInfo->id);
            if (!$cabangToUpdate) { return redirect()->route('setting.index')->with('error', 'Cabang yang akan diupdate tidak ditemukan.'); }


            // Validasi data input (mirip CabangController@update, tapi tanpa kode_cabang)
            $validator = Validator::make($request->all(), [
                'nama_perusahaan' => 'required|string|max:255',
                'alamat_perusahaan' => 'nullable|string',
                'provinsi_perusahaan' => 'nullable|string|max:100',
                'kota_perusahaan' => 'nullable|string|max:100',
                'kecamatan_perusahaan' => 'nullable|string|max:100',
                'kelurahan_perusahaan' => 'nullable|string|max:100',
                'kode_pos' => 'nullable|string|max:10',
                 // Nama input file HARUS 'logo_perusahaan' agar cocok dengan form
                'logo_perusahaan' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                 // Status mungkin tidak boleh diubah oleh petugas? Jika boleh, tambahkan:
                 // 'status' => 'required|boolean',
            ]);

             if ($validator->fails()) {
                return redirect()->route('setting.index')
                            ->withErrors($validator)
                            ->withInput();
            }

            $validatedData = $validator->validated();

            try {
                // Handle upload logo cabang
                if ($request->hasFile('logo_perusahaan')) { // Cek nama input file
                    // Hapus logo lama
                    if ($cabangToUpdate->logo_perusahaan && Storage::disk('public')->exists($cabangToUpdate->logo_perusahaan)) {
                        Storage::disk('public')->delete($cabangToUpdate->logo_perusahaan);
                    }
                    // Simpan logo baru
                    $filePath = $request->file('logo_perusahaan')->store('logos_cabang', 'public');
                    $validatedData['logo_perusahaan'] = $filePath;
                }

                // Update data cabang
                $cabangToUpdate->update($validatedData);

                // Update session dengan data cabang terbaru
                session(['assigned_cabang' => $cabangToUpdate->fresh()]); // Ambil data terbaru dari DB
                 Log::info('Cabang setting updated by Petugas ID: ' . $user->id . ' for Cabang ID: ' . $cabangToUpdate->id);

                return redirect()->route('setting.index')->with('success', 'Pengaturan cabang berhasil diperbarui.');

            } catch (\Exception $e) {
                 Log::error('Error updating cabang setting by Petugas ID: ' . $user->id . ' - ' . $e->getMessage());
                 return redirect()->route('setting.index')->with('error', 'Gagal memperbarui pengaturan cabang.');
            }

        } else if ($user->level !== 'petugas') {
            // --- LOGIKA UPDATE SETTING GLOBAL (UNTUK ADMIN/SUPERVISOR) ---
            $setting = Setting::first();
            if (!$setting) { $setting = new Setting(); }

            $rules = [
                'nama_perusahaan' => 'required|string|max:255',
                'alamat' => 'nullable|string',
                'email' => 'nullable|email|max:255',
                'website' => 'nullable|url|max:255',
                 // Nama input file HARUS 'logo' agar cocok dengan form
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
            ];

            $validatedData = $request->validate($rules);

            try {
                // Handle Upload Logo Global
                if ($request->hasFile('logo')) { // Cek nama input file 'logo'
                    if ($setting->logo && Storage::disk('public')->exists('back/logo/' . $setting->logo)) {
                        Storage::disk('public')->delete('back/logo/' . $setting->logo);
                    }
                    $logoName = time() . '.' . $request->logo->extension();
                    $request->logo->storeAs('back/logo', $logoName, 'public');
                    $validatedData['logo'] = $logoName;
                }

                $setting->fill($validatedData);
                $setting->save();
                Log::info('Global setting updated by User ID: ' . $user->id);

                return redirect()->route('setting.index')->with('success', 'Pengaturan global berhasil diperbarui.');

            } catch (\Exception $e) {
                 Log::error('Error updating global setting by User ID: ' . $user->id . ' - ' . $e->getMessage());
                 return redirect()->route('setting.index')->with('error', 'Gagal memperbarui pengaturan global.');
            }
        } else {
            // Jika user adalah petugas tapi $cabangInfo tidak ada (seharusnya tidak terjadi jika login benar)
            return redirect()->back()->with('error', 'Aksi tidak diizinkan atau data tidak ditemukan.');
        }
    }
}