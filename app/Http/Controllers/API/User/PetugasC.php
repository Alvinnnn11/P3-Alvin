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
        // Ambil semua penugasan, eager load relasi user dan cabang
        $assignments = Petugas::with(['user', 'cabang'])->latest()->get();

        // Ambil user yang levelnya 'petugas' DAN *belum* ada di tabel petugas
        $assignedUserIds = Petugas::pluck('user_id')->all();
        $availableUsers = User::where('level', 'petugas')
                              ->whereNotIn('id', $assignedUserIds)
                              ->orderBy('name')
                              ->get();

        // Ambil semua cabang untuk dropdown
        $cabangs = Cabang::where('status', true)->orderBy('nama_perusahaan')->get();

        return view('petugas.index', compact('assignments', 'availableUsers', 'cabangs'));
    }

    public function getPetugasData()
    {
        // Fungsi untuk refresh tabel AJAX
        Log::info('getPetugasData method called for AJAX refresh.');
        $assignments = Petugas::with(['user', 'cabang'])->latest()->get();
        return view('petugas.tbody', compact('assignments')); // Return view tbody
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where(function ($query) {
                    // Pastikan user yg dipilih memang levelnya petugas
                    $query->where('level', 'petugas');
                }),
                // Pastikan user belum ditugaskan di tabel petugas
                Rule::unique('petugas', 'user_id')
            ],
            'cabang_id' => 'required|exists:cabangs,id',
            'tugas' => 'nullable|string|max:500',
        ], [
            'user_id.required' => 'Petugas (User) wajib dipilih.',
            'user_id.exists' => 'User yang dipilih tidak valid atau bukan level petugas.',
            'user_id.unique' => 'User ini sudah memiliki penugasan di cabang lain.',
            'cabang_id.required' => 'Cabang wajib dipilih.',
            'cabang_id.exists' => 'Cabang yang dipilih tidak valid.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $assignment = Petugas::create($validator->validated());
            // Eager load relasi untuk data response jika perlu
            $assignment->load(['user', 'cabang']);
            return response()->json(['success' => true, 'message' => 'Penugasan petugas berhasil ditambahkan!', 'data' => $assignment]);
        } catch (\Exception $e) {
            Log::error('Error storing petugas assignment: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan penugasan: ' . $e->getMessage()], 500);
        }
    }

    // Note: Parameter $petuga harus sama dengan {petuga} di route
    public function update(Request $request, Petugas $petuga)
    {
         $validator = Validator::make($request->all(), [
            // Saat update, user mungkin tidak bisa diubah, atau jika bisa, validasinya beda
             'user_id' => [
                 'required',
                 Rule::exists('users', 'id')->where(function ($query) {
                     $query->where('level', 'petugas');
                 }),
                 // Cek unique, abaikan record saat ini
                 Rule::unique('petugas', 'user_id')->ignore($petuga->id)
             ],
            'cabang_id' => 'required|exists:cabangs,id',
            'tugas' => 'nullable|string|max:500',
        ], [
            // Pesan error sama seperti store
            'user_id.required' => 'Petugas (User) wajib dipilih.',
            'user_id.exists' => 'User yang dipilih tidak valid atau bukan level petugas.',
            'user_id.unique' => 'User ini sudah memiliki penugasan di cabang lain.',
            'cabang_id.required' => 'Cabang wajib dipilih.',
            'cabang_id.exists' => 'Cabang yang dipilih tidak valid.',
        ]);

         if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            // Hanya update field yang relevan
            $petuga->update($validator->validated());
            return response()->json(['success' => true, 'message' => 'Penugasan petugas berhasil diupdate!']);
        } catch (\Exception $e) {
            Log::error('Error updating petugas assignment ' . $petuga->id . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mengupdate penugasan: ' . $e->getMessage()], 500);
        }
    }

    // Note: Parameter $petuga harus sama dengan {petuga} di route
    public function destroy(Petugas $petuga)
    {
        try {
            $petuga->delete();
            return response()->json(['success' => true, 'message' => 'Penugasan petugas berhasil dihapus!']);
        } catch (\Exception $e) {
            Log::error('Error deleting petugas assignment ' . $petuga->id . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menghapus penugasan: ' . $e->getMessage()], 500);
        }
    }
}
