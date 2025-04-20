<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\Supervisor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class SupervisorC extends Controller
{
    private function getUserCabangId()
    {
        $user = Auth::user();
        if (!$user || $user->level === 'admin') {
            return null; // Admin tidak punya cabang spesifik di konteks ini
        }

        // Hanya cek level supervisor untuk halaman ini
        if ($user->level === 'supervisor') {
            // Jika user adalah Supervisor, cari penugasannya di tabel 'supervisors'
            $assignment = Supervisor::where('user_id', $user->id)->select('cabang_id')->first();
            Log::info("Checking branch for Supervisor ID: {$user->id}. Found assignment:", $assignment ? [$assignment->toArray()] : ['Not Found']);
            return $assignment ? $assignment->cabang_id : null;
        }
        // Level lain (Petugas) tidak relevan untuk manajemen supervisor oleh non-admin
        Log::warning("User level '{$user->level}' (ID: {$user->id}) accessing Supervisor Management page without admin privileges.");
        return null;
    }
    public function index(Request $request)
    {
        Log::info('--- SupervisorC@index START ---'); // Log awal method
        try {
            $user = Auth::user();
            if (!$user) {
                Log::warning('SupervisorC@index: User not authenticated.');
                return redirect()->route('login');
            }

            $userLevel = $user->level;
            $userCabangId = $this->getUserCabangId();
            $selectedCabangId = $request->query('cabang_id'); // Filter dari URL

            Log::info("User Info: ID={$user->id}, Level={$userLevel}, AssignedCabangID=" . ($userCabangId ?? 'NULL'));
            Log::info("Filter Info: SelectedCabangID=" . ($selectedCabangId ?? 'NULL'));

            $assignmentsQuery = Supervisor::with(['user', 'cabang']); // Query dasar

             // --- Logging SEBELUM Filter ---
             Log::info("Query Base Count (before filter): " . $assignmentsQuery->count());

            // --- Terapkan Filter ---
            if ($userLevel !== 'admin') {
                Log::info("Applying filter: User is NOT Admin.");
                if ($userCabangId) {
                    Log::info("Applying filter: User has Cabang ID {$userCabangId}. Filtering assignments by this branch.");
                    $assignmentsQuery->where('cabang_id', $userCabangId);
                } else {
                    Log::warning("Applying filter: User is Supervisor BUT has no assigned branch. Returning empty set.");
                    $assignmentsQuery->whereRaw('1 = 0'); // Kondisi false
                }
            } elseif ($selectedCabangId) {
                Log::info("Applying filter: User is Admin AND selectedCabangId is {$selectedCabangId}. Filtering assignments.");
                $assignmentsQuery->where('cabang_id', $selectedCabangId);
            } else {
                 Log::info("Applying filter: User is Admin AND no specific branch selected. Showing all.");
                 // Tidak perlu where clause tambahan
             }

            // --- Logging SETELAH Filter ---
            // Log SQL (jika perlu, aktifkan query log atau gunakan package debugbar)
            // Log::info("Generated SQL: " . $assignmentsQuery->toSql());
            $assignments = $assignmentsQuery->latest('id')->get(); // Eksekusi query
            Log::info("Query Result Count (after filter): " . $assignments->count());
            if ($assignments->count() > 0) {
                // Log beberapa detail data pertama untuk verifikasi relasi
                Log::info("First few assignments data (JSON): " . $assignments->take(3)->toJson());
            } else {
                 Log::info("No assignments found after applying filters.");
             }

            // --- Query data lain (availableUsers, cabangs, etc.) ---
            // (Tambahkan log jika perlu di sini juga)
            $assignedUserIds = Supervisor::pluck('user_id')->all();
            $availableUsers = User::where('level', 'Supervisor')
                                    ->whereNotIn('id', $assignedUserIds)
                                    ->orderBy('name')
                                    ->select('id', 'name', 'email')->get();
             Log::info("Available Users Count: " . $availableUsers->count());

            $cabangs = Cabang::where('status', true)->orderBy('nama_perusahaan')->select('id', 'nama_perusahaan', 'kode_cabang')->get();
            Log::info("Cabangs for Dropdown/Filter Count: " . $cabangs->count());

            $userCabangDetail = ($userLevel !== 'admin' && $userCabangId)
                                ? Cabang::find($userCabangId)
                                : null;
             Log::info("User Cabang Detail: " . ($userCabangDetail ? $userCabangDetail->toJson() : 'NULL'));

            Log::info("Attempting to return view 'Supervisor.index'");
            Log::info('--- SupervisorC@index END ---');
            return view('Supervisor.index', compact('cabangs','assignedUserIds','availableUsers','userLevel','userCabangId','userCabangDetail','selectedCabangId','assignments'));

        } catch (\Exception $e) {
            Log::error("!!! EXCEPTION in SupervisorC@index: " . $e->getMessage() . "\n" . $e->getTraceAsString()); // Log trace
            Log::info('--- SupervisorC@index END with EXCEPTION ---');
            return redirect()->route('dashboard.index')->with('error', 'Gagal memuat data penugasan Supervisor: Terjadi kesalahan internal.');
        }
    }

    /**
     * Mengambil data untuk refresh tabel AJAX.
     */
    public function getSupervisorData(Request $request) // Tambahkan Request
    {
        try {
            Log::info('getSupervisorData method called for AJAX refresh.');
            $user = Auth::user();
            $userLevel = $user->level;
            $userCabangId = $this->getUserCabangId();
            $selectedCabangId = $request->query('cabang_id'); // Ambil filter dari AJAX request

            $assignmentsQuery = Supervisor::with(['user', 'cabang'])->latest('id');

             // --- Terapkan Filter Berdasarkan Role ---
             if ($userLevel !== 'admin') {
                 if ($userCabangId) {
                     $assignmentsQuery->where('cabang_id', $userCabangId);
                 } else {
                     $assignmentsQuery->whereRaw('1 = 0'); // Jangan tampilkan jika supervisor tak punya cabang
                 }
             } elseif ($selectedCabangId) {
                 // Admin dengan filter
                 $assignmentsQuery->where('cabang_id', $selectedCabangId);
             }
             // Admin tanpa filter -> tampilkan semua

            $assignments = $assignmentsQuery->get();
            return view('Supervisor.tbody', compact('assignments')); // Return view tbody
        } catch (\Exception $e) {
            Log::error("Error fetching data for Supervisor tbody refresh: " . $e->getMessage());
            return response('<tr><td colspan="7" class="text-center text-danger">Gagal memuat data. Server Error.</td></tr>', 500);
        }
    }

    /**
     * Menyimpan penugasan Supervisor baru.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['success' => false, 'message' => 'Autentikasi Gagal.'], 401);

        $userLevel = $user->level;
        $userCabangId = $this->getUserCabangId();

        // Jika supervisor, pastikan dia punya cabang
        if ($userLevel === 'supervisor' && !$userCabangId) {
            Log::warning("PERMISSION DENIED (Store): Supervisor {$user->id} has no assigned branch."); // LOG TAMBAHAN
            return response()->json(['success' => false, 'message' => 'Gagal: Anda belum ditugaskan pada cabang manapun.'], 403);
        }

        $rules = [
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where('level', 'Supervisor'),
                Rule::unique('supervisors', 'user_id')
            ],
            // Cabang hanya required & exists jika admin yg input
            'cabang_id' => ($userLevel === 'admin')
                            ? 'required|exists:cabangs,id,status,1'
                            : 'nullable', // Akan diisi otomatis jika supervisor
            'tugas' => 'nullable|string|max:1000',
        ];
        $messages = [
            'user_id.required' => 'Supervisor (User) wajib dipilih.',
            'user_id.exists' => 'User yang dipilih tidak valid atau bukan level Supervisor.',
            'user_id.unique' => 'User ini sudah memiliki penugasan sebagai Supervisor.',
            'cabang_id.required' => 'Cabang wajib dipilih (Admin).',
            'cabang_id.exists' => 'Cabang yang dipilih tidak valid atau tidak aktif (Admin).',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            Log::warning('Validation failed on Supervisor store:', $validator->errors()->toArray());
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $validatedData = $validator->validated();

            // Jika supervisor yang menambah, paksa gunakan cabangnya sendiri
            if ($userLevel === 'supervisor') {
                $validatedData['cabang_id'] = $userCabangId;
            }

            // Cek lagi apakah cabang_id valid setelah override (khususnya non-admin)
             if (empty($validatedData['cabang_id'])) {
                  return response()->json(['success' => false, 'message' => 'Gagal menyimpan: Informasi cabang tidak ditemukan atau tidak valid.'], 400);
              }


            Log::info("User Level '{$userLevel}' storing Supervisor Assignment with data:", $validatedData);
            $assignment = Supervisor::create($validatedData);
            $assignment->load(['user', 'cabang']);
            return response()->json(['success' => true, 'message' => 'Penugasan Supervisor berhasil ditambahkan!', 'data' => $assignment]);
        } catch (\Exception $e) {
            Log::error('Error storing Supervisor assignment: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan penugasan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Mengupdate penugasan Supervisor.
     * Supervisor HANYA boleh update penugasan di cabangnya sendiri.
     */
    public function update(Request $request, Supervisor $superviso) // Nama parameter $superviso
    {
        $user = Auth::user();
         if (!$user) return response()->json(['success' => false, 'message' => 'Autentikasi Gagal.'], 401);

        $userLevel = $user->level;
        $userCabangId = $this->getUserCabangId();

        // --- Otorisasi: Cek apakah supervisor mencoba edit data di luar cabangnya ---
        if ($userLevel === 'supervisor') {
            if (!$userCabangId) {
                Log::warning("PERMISSION DENIED (Update): Supervisor {$user->id} has no assigned branch."); // LOG TAMBAHAN
                return response()->json(['success' => false, 'message' => 'Gagal: Anda belum ditugaskan pada cabang manapun.'], 403);
            }
            if ($superviso->cabang_id !== $userCabangId) {
                Log::warning("PERMISSION DENIED (Update): Supervisor {$user->id} (Branch: {$userCabangId}) tried to update assignment {$superviso->id} in different branch ({$superviso->cabang_id})."); // LOG TAMBAHAN
                return response()->json(['success' => false, 'message' => 'Anda tidak memiliki izin untuk mengubah penugasan di cabang lain.'], 403);
            }
        }
        
        // Jika admin, lolos cek ini

        // --- Validasi ---
        $rules = [
             // User ID tidak diubah
             // Cabang hanya bisa diubah oleh Admin
             'cabang_id' => ($userLevel === 'admin')
                             ? 'required|exists:cabangs,id,status,1'
                             : 'nullable', // Supervisor tidak mengirim/mengubah cabang via form
             'tugas' => 'nullable|string|max:1000',
         ];
        $messages = [
              'cabang_id.required' => 'Cabang wajib dipilih (Admin).',
              'cabang_id.exists' => 'Cabang yang dipilih tidak valid atau tidak aktif (Admin).',
         ];

        $validator = Validator::make($request->all(), $rules, $messages);


        if ($validator->fails()) {
            Log::warning('Validation failed on Supervisor update for assignment ID ' . $superviso->id . ':', $validator->errors()->toArray());
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $validatedData = $validator->validated();
            // User ID tidak diubah, ambil dari data existing
            $validatedData['user_id'] = $superviso->user_id;

            // Jika supervisor yang mengupdate, pastikan cabang_id tetap sama (tidak diubah)
            if ($userLevel === 'supervisor') {
                $validatedData['cabang_id'] = $userCabangId; // Gunakan cabang supervisor yg login
            }
            // Jika admin, cabang_id diambil dari validasi request

            // Cek lagi validitas cabang_id setelah logic di atas
             if (empty($validatedData['cabang_id'])) {
                  return response()->json(['success' => false, 'message' => 'Gagal mengupdate: Informasi cabang tidak valid.'], 400);
              }

            Log::info("User Level '{$userLevel}' updating Supervisor Assignment ID: " . $superviso->id . " with data:", $validatedData);
            $superviso->update($validatedData);
            return response()->json(['success' => true, 'message' => 'Penugasan Supervisor berhasil diupdate!']);
        } catch (\Exception $e) {
            Log::error('Error updating Supervisor assignment ' . $superviso->id . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mengupdate penugasan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Menghapus penugasan Supervisor.
     * Supervisor HANYA boleh hapus penugasan di cabangnya sendiri.
     */
    public function destroy(Supervisor $superviso) // Nama parameter $superviso
    {
        $user = Auth::user();
         if (!$user) return response()->json(['success' => false, 'message' => 'Autentikasi Gagal.'], 401);

        $userLevel = $user->level;
        $userCabangId = $this->getUserCabangId();

         // --- Otorisasi: Cek apakah supervisor mencoba hapus data di luar cabangnya ---
         if ($userLevel === 'supervisor') {
            if (!$userCabangId) {
                Log::warning("PERMISSION DENIED (Destroy): Supervisor {$user->id} has no assigned branch."); // LOG TAMBAHAN
                return response()->json(['success' => false, 'message' => 'Gagal: Anda belum ditugaskan pada cabang manapun.'], 403);
            }
            if ($superviso->cabang_id !== $userCabangId) {
                Log::warning("PERMISSION DENIED (Destroy): Supervisor {$user->id} (Branch: {$userCabangId}) tried to delete assignment {$superviso->id} in different branch ({$superviso->cabang_id})."); // LOG TAMBAHAN
                return response()->json(['success' => false, 'message' => 'Anda tidak memiliki izin untuk menghapus penugasan di cabang lain.'], 403);
            }
        }
         // Jika admin, lolos cek ini


        try {
            $assignmentId = $superviso->id;
            Log::info("User Level '{$userLevel}' attempting to delete Supervisor Assignment ID: " . $assignmentId . ' for User ID: ' . $superviso->user_id);
            $superviso->delete();
            Log::info('Successfully deleted Supervisor Assignment ID: ' . $assignmentId);
            return response()->json(['success' => true, 'message' => 'Penugasan Supervisor berhasil dihapus!']);
        } catch (\Exception $e) {
            $assignmentId = $superviso->id ?? 'unknown';
            Log::error('Error deleting Supervisor assignment ' . $assignmentId . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menghapus penugasan: ' . $e->getMessage()], 500);
        }
    }
}