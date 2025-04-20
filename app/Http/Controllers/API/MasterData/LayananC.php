<?php

namespace App\Http\Controllers\API\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Layanan;
use App\Models\Satuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LayananC extends Controller
{
    public function index()
    {
        // Eager load relasi 'satuan' untuk efisiensi
        $layanans = Layanan::with('satuan')->latest()->get();
        $satuans = Satuan::orderBy('nama_satuan')->get(); // Ambil semua satuan untuk dropdown
        return view('layanan.index', compact('layanans', 'satuans'));
    }

    /**
     * Mengambil data Layanan untuk refresh tabel via AJAX.
     */
    public function data()
    {
        // Eager load relasi 'satuan'
        $layanans = Layanan::with('satuan')->latest()->get();
        return view('layanan.tbody', compact('layanans'))->render();
    }

    /**
     * Menyimpan data Layanan baru.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_layanan' => 'required|string|max:255|unique:layanans,nama_layanan',
            'harga_per_unit' => 'required|numeric|min:0',
            // Pastikan satuan_id ada di tabel satuans
            'satuan_id' => 'required|integer|exists:satuans,satuan_id',
            'estimasi_durasi_hari' => 'nullable|integer|min:0',
            'status' => 'required|boolean', // 0 atau 1
        ], [
            'nama_layanan.required' => 'Nama layanan wajib diisi.',
            'nama_layanan.unique' => 'Nama layanan sudah terdaftar.',
            'harga_per_unit.required' => 'Harga wajib diisi.',
            'harga_per_unit.numeric' => 'Harga harus berupa angka.',
            'satuan_id.required' => 'Satuan wajib dipilih.',
            'satuan_id.exists' => 'Satuan yang dipilih tidak valid.',
            'estimasi_durasi_hari.integer' => 'Estimasi hari harus angka bulat.',
            'status.required' => 'Status wajib dipilih.',
            'status.boolean' => 'Status tidak valid.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            Layanan::create([
                'nama_layanan' => $request->nama_layanan,
                'harga_per_unit' => $request->harga_per_unit,
                'satuan_id' => $request->satuan_id,
                'estimasi_durasi_hari' => $request->estimasi_durasi_hari,
                'status' => $request->status,
            ]);
            return response()->json(['success' => true, 'message' => 'Data layanan berhasil ditambahkan.']);
        } catch (\Exception $e) {
            Log::error('Error storing Layanan: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan data layanan.'], 500);
        }
    }

    /**
     * Mengupdate data Layanan yang sudah ada.
     * Menggunakan Route Model Binding ($layanan otomatis didapat dari ID di URL)
     */
    public function update(Request $request, Layanan $layanan)
    {
        $validator = Validator::make($request->all(), [
            'nama_layanan' => [
                'required',
                'string',
                'max:255',
                // Pastikan unik, kecuali untuk dirinya sendiri
                Rule::unique('layanans', 'nama_layanan')->ignore($layanan->layanan_id, 'layanan_id'),
            ],
            'harga_per_unit' => 'required|numeric|min:0',
            'satuan_id' => 'required|integer|exists:satuans,satuan_id',
            'estimasi_durasi_hari' => 'nullable|integer|min:0',
            'status' => 'required|boolean',
        ], [
            // Salin pesan error dari store jika perlu disesuaikan
            'nama_layanan.required' => 'Nama layanan wajib diisi.',
            'nama_layanan.unique' => 'Nama layanan sudah digunakan data lain.',
            'harga_per_unit.required' => 'Harga wajib diisi.',
            'harga_per_unit.numeric' => 'Harga harus berupa angka.',
            'satuan_id.required' => 'Satuan wajib dipilih.',
            'satuan_id.exists' => 'Satuan yang dipilih tidak valid.',
            'estimasi_durasi_hari.integer' => 'Estimasi hari harus angka bulat.',
            'status.required' => 'Status wajib dipilih.',
            'status.boolean' => 'Status tidak valid.',
        ]);


        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $layanan->update([
                'nama_layanan' => $request->nama_layanan,
                'harga_per_unit' => $request->harga_per_unit,
                'satuan_id' => $request->satuan_id,
                'estimasi_durasi_hari' => $request->estimasi_durasi_hari,
                'status' => $request->status,
            ]);
            return response()->json(['success' => true, 'message' => 'Data layanan berhasil diperbarui.']);
        } catch (\Exception $e) {
            Log::error('Error updating Layanan: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui data layanan.'], 500);
        }
    }

    /**
     * Menghapus data Layanan.
     * Menggunakan Route Model Binding
     */
    public function destroy(Layanan $layanan)
    {
        try {
            // Tambahkan cek relasi jika perlu (misal, ke tabel item_pesanan_laundry)
            // if ($layanan->itemPesanan()->exists()) { // Uncomment jika relasi itemPesanan ada di model
            //     return response()->json(['success' => false, 'message' => 'Layanan tidak dapat dihapus karena sudah digunakan dalam pesanan.'], 409); // 409 Conflict
            // }

            $layanan->delete();
            return response()->json(['success' => true, 'message' => 'Data layanan berhasil dihapus.']);
        } catch (\Exception $e) {
            Log::error('Error deleting Layanan: ' . $e->getMessage());
            // Cek jika error karena foreign key constraint
            if ($e instanceof \Illuminate\Database\QueryException && str_contains($e->getMessage(), 'foreign key constraint fails')) {
                 return response()->json(['success' => false, 'message' => 'Gagal menghapus: Layanan ini mungkin masih terkait dengan data lain (misal: pesanan atau promo).'], 409); // Conflict
            }
            return response()->json(['success' => false, 'message' => 'Gagal menghapus data layanan.'], 500);
        }
    }
}
