<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\Petugas; // Pastikan ini model untuk tabel penugasan
use App\Models\User;
use App\Models\Supervisor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Tambahkan ini
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class PetugasC extends Controller
{
    // Helper function to get user's assigned branch ID (if not admin)
    private function getUserCabangId()
    {
        $user = Auth::user();
        if (!$user || $user->level === 'admin') {
            return null; // Admin tidak punya cabang spesifik di konteks ini
        }

        // Cek level user yang login
        if ($user->level === 'supervisor') {
            // Jika user adalah Supervisor, cari penugasannya di tabel 'supervisors'
            $assignment = Supervisor::where('user_id', $user->id)->select('cabang_id')->first(); // Cukup select cabang_id
            Log::info("Checking branch for Supervisor ID: {$user->id}. Found assignment:", $assignment ? [$assignment->toArray()] : ['Not Found']);
            return $assignment ? $assignment->cabang_id : null;

        } elseif ($user->level === 'petugas') {
            // Jika user adalah Petugas, cari penugasannya di tabel 'petugas'
            $assignment = Petugas::where('user_id', $user->id)->select('cabang_id')->first();
            Log::info("Checking branch for Petugas ID: {$user->id}. Found assignment:", $assignment ? [$assignment->toArray()] : ['Not Found']);
            return $assignment ? $assignment->cabang_id : null;

        } else {
            // Jika ada level lain yang perlu dicek, tambahkan di sini
            Log::warning("User level '{$user->level}' (ID: {$user->id}) does not have a defined branch lookup method in PetugasC.");
            return null; // Level lain dianggap tidak punya cabang spesifik
        }
    }


    public function index(Request $request) // Tambahkan Request
    {
        try {
            $user = Auth::user();
            $userLevel = $user->level;
            $userCabangId = $this->getUserCabangId();
            $selectedCabangId = $request->query('cabang_id'); // Ambil filter dari URL

            // --- Query Penugasan ---
            $assignmentsQuery = Petugas::with(['user', 'cabang'])->latest('id');

            if ($userLevel !== 'admin') {
                // Jika bukan admin, filter berdasarkan cabang user
                if ($userCabangId) {
                    $assignmentsQuery->where('cabang_id', $userCabangId);
                } else {
                    // Jika supervisor/petugas belum punya assignment, jangan tampilkan apa-apa
                    $assignmentsQuery->whereRaw('1 = 0'); // Kondisi false
                }
            } elseif ($selectedCabangId) {
                // Jika admin dan ada filter cabang dipilih
                $assignmentsQuery->where('cabang_id', $selectedCabangId);
            }
            // Jika admin dan tidak ada filter, tampilkan semua

            $assignments = $assignmentsQuery->get();

            // --- Data untuk Form ---
            $assignedUserIds = Petugas::pluck('user_id')->all();
            $availableUsersQuery = User::where('level', 'petugas')
                                    ->whereNotIn('id', $assignedUserIds)
                                    ->orderBy('name')
                                    ->select('id', 'name', 'email');

            // Jika supervisor, mungkin hanya bisa assign user ke cabangnya sendiri?
            // (Tambahkan logic ini jika perlu, contoh: ->whereHas('someRelationToBranch', $userCabangId))
            // Untuk sekarang, biarkan bisa pilih semua user petugas yg available.

            $availableUsers = $availableUsersQuery->get();

            // --- Data Cabang untuk Dropdown/Filter ---
            $cabangsQuery = Cabang::where('status', true)->orderBy('nama_perusahaan')->select('id', 'nama_perusahaan', 'kode_cabang');

            if ($userLevel !== 'admin' && $userCabangId) {
                // Jika bukan admin, hanya perlu data cabang user itu sendiri
                $cabangs = $cabangsQuery->where('id', $userCabangId)->get();
            } else {
                // Jika admin, ambil semua cabang aktif untuk filter/dropdown
                $cabangs = $cabangsQuery->get();
            }

             // Ambil detail cabang user untuk ditampilkan di form (jika bukan admin)
            $userCabangDetail = ($userLevel !== 'admin' && $userCabangId)
                               ? Cabang::find($userCabangId)
                               : null;


            return view('petugas.index', compact(
                'assignments',
                'availableUsers',
                'cabangs', // Berisi semua cabang (admin) atau cabang user (lainnya)
                'userLevel',
                'userCabangId', // ID cabang user (null jika admin/belum assigned)
                'userCabangDetail', // Objek cabang user (null jika admin/belum assigned)
                'selectedCabangId' // Untuk menandai filter yg aktif
            ));

        } catch (\Exception $e) {
            Log::error("Error fetching data for Petugas index: " . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Gagal memuat data penugasan petugas: ' . $e->getMessage()); // Tampilkan pesan error lebih detail
        }
    }

    /**
     * Mengambil data untuk refresh tabel AJAX.
     */
    public function getPetugasData(Request $request) // Tambahkan Request
    {
        try {
            Log::info('getPetugasData method called for AJAX refresh.');
            $user = Auth::user();
            $userLevel = $user->level;
            $userCabangId = $this->getUserCabangId();
            $selectedCabangId = $request->query('cabang_id'); // Ambil filter dari AJAX request

            $assignmentsQuery = Petugas::with(['user', 'cabang'])->latest('id');

            if ($userLevel !== 'admin') {
                if ($userCabangId) {
                    $assignmentsQuery->where('cabang_id', $userCabangId);
                } else {
                    $assignmentsQuery->whereRaw('1 = 0');
                }
            } elseif ($selectedCabangId) {
                $assignmentsQuery->where('cabang_id', $selectedCabangId);
            }

            $assignments = $assignmentsQuery->get();
            return view('petugas.tbody', compact('assignments')); // Return view tbody
        } catch (\Exception $e) {
             Log::error("Error fetching data for Petugas tbody refresh: " . $e->getMessage());
             return response('<tr><td colspan="7" class="text-center text-danger">Gagal memuat data. Server Error.</td></tr>', 500); // Pesan lebih jelas
        }
    }

    /**
     * Menyimpan penugasan petugas baru.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $userLevel = $user->level;
        $userCabangId = $this->getUserCabangId();

        $rules = [
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('level', 'petugas');
                }),
                Rule::unique('petugas', 'user_id')
            ],
            // Cabang hanya required & exists jika admin yg input
            'cabang_id' => ($userLevel === 'admin')
                         ? 'required|exists:cabangs,id,status,1'
                         : 'nullable', // Akan diisi otomatis jika bukan admin
            'tugas' => 'nullable|string|max:1000',
        ];

        $messages = [
            'user_id.required' => 'Petugas (User) wajib dipilih.',
            'user_id.exists' => 'User yang dipilih tidak valid atau bukan level petugas.',
            'user_id.unique' => 'User ini sudah memiliki penugasan.',
            'cabang_id.required' => 'Cabang wajib dipilih (Admin).',
            'cabang_id.exists' => 'Cabang yang dipilih tidak valid atau tidak aktif (Admin).',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            Log::warning('Validation failed on Petugas store:', $validator->errors()->toArray());
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $validatedData = $validator->validated();

            // Jika bukan admin, override cabang_id dengan cabang milik user
            if ($userLevel !== 'admin') {
                if (!$userCabangId) {
                    // Harusnya tidak terjadi jika UI benar, tapi sebagai safeguard
                    return response()->json(['success' => false, 'message' => 'Gagal menyimpan: Anda belum terdaftar di cabang manapun.'], 403);
                }
                $validatedData['cabang_id'] = $userCabangId;
                 // Hapus validasi exists jika ada di $validatedData karena sudah pasti ada
                unset($validatedData['cabang_id_exists_validation_key']); // Ganti dgn key yg benar jika ada
            }

             // Cek lagi apakah cabang_id sudah terisi (khususnya untuk non-admin)
            if (empty($validatedData['cabang_id'])) {
                 return response()->json(['success' => false, 'message' => 'Gagal menyimpan: Informasi cabang tidak ditemukan.'], 400);
            }


            Log::info('Creating Petugas Assignment with data:', $validatedData);
            $assignment = Petugas::create($validatedData);
            $assignment->load(['user', 'cabang']);
            return response()->json(['success' => true, 'message' => 'Penugasan petugas berhasil ditambahkan!', 'data' => $assignment]);
        } catch (\Exception $e) {
            Log::error('Error storing petugas assignment: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan penugasan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Mengupdate penugasan petugas.
     */
    public function update(Request $request, Petugas $petuga) // $petuga adalah assignment yg diedit
    {
        $user = Auth::user();
        $userLevel = $user->level;
        $userCabangId = $this->getUserCabangId();

        // --- Otorisasi ---
        if ($userLevel !== 'admin' && $petuga->cabang_id !== $userCabangId) {
             Log::warning("Unauthorized attempt to update assignment ID {$petuga->id} by user {$user->id} from branch {$userCabangId}. Assignment belongs to branch {$petuga->cabang_id}.");
            return response()->json(['success' => false, 'message' => 'Anda tidak berwenang mengubah data cabang lain.'], 403);
        }

        // --- Validasi ---
        $rules = [
            // user_id tidak diubah
            // cabang_id hanya bisa diubah oleh admin
            'cabang_id' => ($userLevel === 'admin')
                         ? 'required|exists:cabangs,id,status,1'
                         : 'nullable', // Akan diisi otomatis jika bukan admin
            'tugas' => 'nullable|string|max:1000',
        ];
        $messages = [
             'cabang_id.required' => 'Cabang wajib dipilih (Admin).',
             'cabang_id.exists' => 'Cabang yang dipilih tidak valid atau tidak aktif (Admin).',
         ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
             Log::warning('Validation failed on Petugas update for assignment ID ' . $petuga->id . ':', $validator->errors()->toArray());
             return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
         }

        try {
            $validatedData = $validator->validated();
            // User ID tidak diubah, ambil dari data existing
            $validatedData['user_id'] = $petuga->user_id;

            // Jika bukan admin, pastikan cabang_id tetap sama (cabang user)
            if ($userLevel !== 'admin') {
                $validatedData['cabang_id'] = $userCabangId; // Gunakan cabang user yg login
            }

            // Cek lagi apakah cabang_id ada (khususnya non-admin)
             if (empty($validatedData['cabang_id'])) {
                 return response()->json(['success' => false, 'message' => 'Gagal mengupdate: Informasi cabang tidak valid.'], 400);
            }

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
     */
    public function destroy(Petugas $petuga) // $petuga adalah assignment yg dihapus
    {
        $user = Auth::user();
        $userLevel = $user->level;
        $userCabangId = $this->getUserCabangId();

        // --- Otorisasi ---
        if ($userLevel !== 'admin' && $petuga->cabang_id !== $userCabangId) {
            Log::warning("Unauthorized attempt to delete assignment ID {$petuga->id} by user {$user->id} from branch {$userCabangId}. Assignment belongs to branch {$petuga->cabang_id}.");
            return response()->json(['success' => false, 'message' => 'Anda tidak berwenang menghapus data cabang lain.'], 403);
        }

        try {
            $assignmentId = $petuga->id; // Simpan ID sebelum dihapus
            Log::info('Attempting to delete Petugas Assignment ID: ' . $assignmentId . ' for User ID: ' . $petuga->user_id);
            $petuga->delete();
            Log::info('Successfully deleted Petugas Assignment ID: ' . $assignmentId);
            return response()->json(['success' => true, 'message' => 'Penugasan petugas berhasil dihapus!']);
        } catch (\Exception $e) {
            $assignmentId = $petuga->id ?? 'unknown'; // Handle jika $petuga mungkin null? (Seharusnya tidak terjadi karena Route Model Binding)
            Log::error('Error deleting petugas assignment ' . $assignmentId . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menghapus penugasan: ' . $e->getMessage()], 500);
        }
    }
}