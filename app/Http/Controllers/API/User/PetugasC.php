<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\Petugas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class PetugasC extends Controller
{
    public function index()
    {
        try {
            // Eager load relasi untuk efisiensi query di view
            $assignments = Petugas::with(['user', 'cabang'])->latest('id')->get(); // Urutkan berdasarkan ID terbaru

            // Ambil user yang levelnya 'petugas' DAN *belum* ada di tabel petugas
            $assignedUserIds = Petugas::pluck('user_id')->all();
            $availableUsers = User::where('level', 'petugas')
                                  ->whereNotIn('id', $assignedUserIds)
                                  ->orderBy('name')
                                  ->select('id', 'name', 'email') // Pilih kolom yg perlu saja
                                  ->get();

            // Ambil semua cabang yang aktif untuk dropdown
            $cabangs = Cabang::where('status', true)->orderBy('nama_perusahaan')->select('id', 'nama_perusahaan', 'kode_cabang')->get();

            return view('petugas.index', compact('assignments', 'availableUsers', 'cabangs'));

        } catch (\Exception $e) {
            Log::error("Error fetching data for Petugas index: " . $e->getMessage());
            // Redirect ke dashboard atau halaman lain dengan pesan error
            return redirect()->route('dashboard')->with('error', 'Gagal memuat data penugasan petugas.');
        }
    }

    /**
     * Mengambil data untuk refresh tabel AJAX.
     */
    public function getPetugasData()
    {
        try {
            Log::info('getPetugasData method called for AJAX refresh.');
            $assignments = Petugas::with(['user', 'cabang'])->latest('id')->get();
            return view('petugas.tbody', compact('assignments')); // Return view tbody
        } catch (\Exception $e) {
             Log::error("Error fetching data for Petugas tbody refresh: " . $e->getMessage());
             // Return response error atau view kosong dengan pesan error
             return response('<tr><td colspan="7" class="text-center text-danger">Gagal memuat data.</td></tr>', 500);
        }
    }

    /**
     * Menyimpan penugasan petugas baru.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('level', 'petugas');
                }),
                Rule::unique('petugas', 'user_id') // Pastikan user belum ditugaskan
            ],
            'cabang_id' => 'required|exists:cabangs,id,status,1', // Pastikan cabang ada & aktif
            'tugas' => 'nullable|string|max:1000', // Perbesar max length jika perlu
        ], [
            'user_id.required' => 'Petugas (User) wajib dipilih.',
            'user_id.exists' => 'User yang dipilih tidak valid atau bukan level petugas.',
            'user_id.unique' => 'User ini sudah memiliki penugasan.',
            'cabang_id.required' => 'Cabang wajib dipilih.',
            'cabang_id.exists' => 'Cabang yang dipilih tidak valid atau tidak aktif.',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed on Petugas store:', $validator->errors()->toArray());
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $validatedData = $validator->validated();
            Log::info('Creating Petugas Assignment with data:', $validatedData);
            $assignment = Petugas::create($validatedData);
            // Eager load relasi agar bisa dikirim kembali jika perlu
            $assignment->load(['user', 'cabang']);
            return response()->json(['success' => true, 'message' => 'Penugasan petugas berhasil ditambahkan!', 'data' => $assignment]); // Mengirim data assignment baru
        } catch (\Exception $e) {
            Log::error('Error storing petugas assignment: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan penugasan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Mengupdate penugasan petugas.
     * Variabel $petuga HARUS sama dengan parameter route {petuga}.
     */
    public function update(Request $request, Petugas $petuga) // Terima objek Petugas (assignment)
    {
         // Validasi (User ID mungkin tidak boleh diubah, jadi tidak perlu divalidasi lagi jika inputnya disabled)
         $validator = Validator::make($request->all(), [
            // 'user_id' tidak divalidasi karena disabled di form edit
            'cabang_id' => 'required|exists:cabangs,id,status,1', // Pastikan cabang ada & aktif
            'tugas' => 'nullable|string|max:1000',
        ], [
            'cabang_id.required' => 'Cabang wajib dipilih.',
            'cabang_id.exists' => 'Cabang yang dipilih tidak valid atau tidak aktif.',
        ]);

         if ($validator->fails()) {
             Log::warning('Validation failed on Petugas update for assignment ID ' . $petuga->id . ':', $validator->errors()->toArray());
             return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $validatedData = $validator->validated();
             // Ambil user_id dari objek $petuga yang ada, bukan dari request (karena disabled)
             $validatedData['user_id'] = $petuga->user_id;

            Log::info('Updating Petugas Assignment ID: ' . $petuga->id . ' with data:', $validatedData);
            $petuga->update($validatedData);
            return response()->json(['success' => true, 'message' => 'Penugasan petugas berhasil diupdate!']);
        } catch (\Exception $e) {
            Log::error('Error updating petugas assignment ' . $petuga->id . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mengupdate penugasan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Menghapus penugasan petugas.
     * Variabel $petuga HARUS sama dengan parameter route {petuga}.
     */
    public function destroy(Petugas $petuga) // Terima objek Petugas (assignment)
    {
        try {
            $userId = $petuga->user_id; // Simpan ID user sebelum dihapus (untuk log)
            Log::info('Attempting to delete Petugas Assignment ID: ' . $petuga->id . ' for User ID: ' . $userId);

            // Hanya hapus record penugasan, BUKAN user atau cabangnya
            $petuga->delete();

            Log::info('Successfully deleted Petugas Assignment ID: ' . $petuga->id);
            return response()->json(['success' => true, 'message' => 'Penugasan petugas berhasil dihapus!']);
        } catch (\Exception $e) {
            Log::error('Error deleting petugas assignment ' . $petuga->id . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menghapus penugasan: ' . $e->getMessage()], 500);
        }
    }
}