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
        $isPetugasView = false; // Flag untuk view

        if ($user->level === 'petugas' && $cabangInfo) {
            // Jika petugas dan punya data cabang di session
            $isPetugasView = true;
            // Data yang dikirim ke view adalah $cabangInfo
        } else if ($user->level !== 'petugas') {
            // Jika bukan petugas (misal admin/supervisor), ambil setting global
            $setting = Setting::first();
            if (!$setting) {
                 // Jika belum ada setting global sama sekali, bisa buat default
                 // $setting = new Setting(); // Atau redirect/beri pesan error
                 // Untuk sekarang, biarkan null dan handle di view
                 // Atau buat record default saat migrasi Setting pertama kali
            }
        } else {
            // Kasus lain (misalnya petugas tapi tidak ada info cabang di session)
            // Mungkin redirect atau tampilkan pesan?
            // Untuk saat ini, kita anggap akan tampilkan pesan "Tidak ada data" di view
        }

        return view('setting.index', compact('setting', 'cabangInfo', 'isPetugasView'));
    }

    /**
     * Mengupdate pengaturan global (HANYA untuk admin/supervisor).
     */
    public function update(Request $request) // Tidak perlu ID setting, kita tentukan targetnya
    {
        $user = Auth::user();

        if ($user->level === 'petugas') {
            // --- LOGIKA UPDATE CABANG (UNTUK PETUGAS) ---
            $cabangInfo = session('assigned_cabang');

            if (!$cabangInfo) {
                return redirect()->route('setting.index')->with('error', 'Data cabang Anda tidak ditemukan di session.');
            }

            $cabangToUpdate = Cabang::find($cabangInfo->id);

            if (!$cabangToUpdate) {
                 return redirect()->route('setting.index')->with('error', 'Cabang yang akan diupdate tidak ditemukan.');
            }

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