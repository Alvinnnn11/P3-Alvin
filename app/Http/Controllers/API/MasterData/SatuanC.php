<?php

namespace App\Http\Controllers\API\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Satuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SatuanC extends Controller
{
    public function index()
    {
        // Mengambil data awal untuk ditampilkan di tabel saat halaman pertama kali dimuat
        $satuans = Satuan::latest()->get(); // Ambil data terbaru
        return view('satuan.index', compact('satuans')); // Kirim data ke view
    }

    /**
     * Mengambil data Satuan untuk refresh tabel via AJAX.
     */
    public function data()
    {
        $satuans = Satuan::latest()->get();
        // Mengembalikan partial view yang hanya berisi baris tabel (tbody)
        return view('satuan.tbody', compact('satuans'))->render();
    }

    /**
     * Menyimpan data Satuan baru.
     */
    public function store(Request $request)
    {
        // Validasi Input
        $validator = Validator::make($request->all(), [
            'nama_satuan' => 'required|string|max:50|unique:satuans,nama_satuan', // Pastikan unik di tabel satuan
            'deskripsi' => 'nullable|string',
        ], [
            'nama_satuan.required' => 'Nama satuan wajib diisi.',
            'nama_satuan.unique' => 'Nama satuan sudah ada.',
            'nama_satuan.max' => 'Nama satuan maksimal 50 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Buat data baru
        try {
            Satuan::create([
                'nama_satuan' => $request->nama_satuan,
                'deskripsi' => $request->deskripsi,
            ]);
            return response()->json(['success' => true, 'message' => 'Data satuan berhasil ditambahkan.']);
        } catch (\Exception $e) {
            // Tangani error jika terjadi saat menyimpan
             Log::error('Error saving Satuan: '.$e->getMessage()); // Log error
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan data satuan.'], 500);
        }
    }


    /**
     * Mengupdate data Satuan yang sudah ada.
     */
    public function update(Request $request, Satuan $satuan) // Route Model Binding
    {
        // Validasi Input
        $validator = Validator::make($request->all(), [
             // Pastikan unik, kecuali untuk dirinya sendiri
            'nama_satuan' => [
                'required',
                'string',
                'max:50',
                Rule::unique('satuans', 'nama_satuan')->ignore($satuan->satuan_id, 'satuan_id'), // Pengecualian ID saat cek unik
            ],
            'deskripsi' => 'nullable|string',
        ], [
            'nama_satuan.required' => 'Nama satuan wajib diisi.',
            'nama_satuan.unique' => 'Nama satuan sudah digunakan oleh data lain.',
            'nama_satuan.max' => 'Nama satuan maksimal 50 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Update data
        try {
            $satuan->update([
                'nama_satuan' => $request->nama_satuan,
                'deskripsi' => $request->deskripsi,
            ]);
             return response()->json(['success' => true, 'message' => 'Data satuan berhasil diperbarui.']);
        } catch (\Exception $e) {
             log::error('Error updating Satuan: '.$e->getMessage()); // Log error
             return response()->json(['success' => false, 'message' => 'Gagal memperbarui data satuan.'], 500);
        }
    }

    /**
     * Menghapus data Satuan.
     */
    public function destroy(Satuan $satuan) // Route Model Binding
    {
        try {
            // Tambahkan cek relasi jika perlu (misal, jangan hapus jika masih dipakai di tabel Layanan)
            // if ($satuan->layanan()->exists()) {
            //     return response()->json(['success' => false, 'message' => 'Satuan tidak dapat dihapus karena masih digunakan oleh data Layanan.'], 409); // 409 Conflict
            // }

            $satuan->delete();
            return response()->json(['success' => true, 'message' => 'Data satuan berhasil dihapus.']);
        } catch (\Exception $e) {
            Log::error('Error deleting Satuan: '.$e->getMessage()); // Log error
            return response()->json(['success' => false, 'message' => 'Gagal menghapus data satuan.'], 500);
        }
    }
}