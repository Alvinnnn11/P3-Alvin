<?php

namespace App\Http\Controllers\API\Transaksi;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TransaksiC extends Controller
{
    public function indexTopup(Request $request) // Terima $request
    {
        try {
            $user = Auth::user();
            // Ambil query pencarian dari URL (?search=...)
            $searchQuery = $request->query('search');

            // Query dasar
            $query = Transaction::with(['user' => function($q){
                            $q->select('id', 'name', 'email');
                        }])
                        ->where('type', 'topup') // Hanya ambil tipe topup
                        ->latest('created_at'); // Urutkan dari terbaru

            // Filter berdasarkan role
            if (in_array($user->level, ['member', 'pengguna'])) {
                // Member & Pengguna hanya lihat transaksi sendiri
                $query->where('user_id', $user->id);
                 Log::info("User {$user->id} ({$user->level}) viewing their own topup history.");
            } elseif (in_array($user->level, ['admin', 'supervisor'])) {
                // Admin & Supervisor bisa lihat semua, tapi bisa difilter
                 Log::info("User {$user->id} ({$user->level}) viewing topup history. Search query: '{$searchQuery}'");

                 // Terapkan filter pencarian jika ada query
                 if ($searchQuery) {
                     $query->whereHas('user', function ($q) use ($searchQuery) {
                         $q->where('name', 'like', "%{$searchQuery}%")
                           ->orWhere('email', 'like', "%{$searchQuery}%");
                     });
                 }
                 // Jika tidak ada searchQuery, tampilkan semua (untuk admin/spv)

            } else {
                 // Role lain tidak bisa lihat
                 Log::warning("User level '{$user->level}' attempted to view topup history. Denied.");
                 $query->whereRaw('1 = 0');
            }

            // Hitung total amount DARI QUERY YANG SUDAH DIFILTER, SEBELUM PAGINASI
            // Clone query agar perhitungan sum tidak mempengaruhi query utama pagination
            $totalTopup = (clone $query)->sum('amount'); // Hitung total

            // Ambil data dengan pagination SETELAH SEMUA FILTER DAN SEBELUM SUM
            $topupHistory = $query->paginate(15)->appends($request->query()); // appends() agar filter terbawa di link pagination

            return view('history.index', compact('topupHistory', 'totalTopup', 'searchQuery')); // Kirim total & search query ke view

        } catch (\Exception $e) {
            Log::error("Error fetching topup history: " . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Gagal memuat history top up.');
        }
    }

    // Anda bisa menambahkan method lain di sini untuk history tipe transaksi lain jika perlu
    // public function indexMembershipFee() { ... }
    // public function indexPurchase() { ... }
}