<?php

namespace App\Http\Controllers\API\Promo;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\Promo;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PromoC extends Controller
{
    public function index(Request $request)
    {
        Log::info('Entering PromoController@index');
    
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
    
            if (!$user) {
                Log::error("User not authenticated in PromoController@index");
                return redirect()->route('login');
            }
    
            Log::info("User Level: {$user->level}");
    
            $query = Promo::with('cabang')->latest('id');
            $cabangsForFilter = collect();
            $targetCabangId = null;
            $filterCabangId = $request->query('filter_cabang', null); // pastikan selalu ada variabel
    
            if ($user->level === 'admin') {
                Log::info("User is admin, preparing filters.");
    
                $cabangsForFilter = Cabang::where('status', true)
                    ->orderBy('nama_perusahaan')
                    ->select('id', 'nama_perusahaan', 'kode_cabang')
                    ->get();
    
                if ($filterCabangId && $filterCabangId !== 'all') {
                    Log::info("Applying Cabang filter: {$filterCabangId}");
                    $query->where('cabang_id', $filterCabangId);
                    $targetCabangId = $filterCabangId;
                }
            } else {
                Log::info("User is {$user->level}, showing all relevant promos.");
    
                // Catatan: jika cabangsForFilter tidak digunakan di tampilan non-admin, bagian ini bisa dihapus
                $cabangsForFilter = Cabang::where('status', true)
                    ->orderBy('nama_perusahaan')
                    ->select('id', 'nama_perusahaan')
                    ->get();
            }
    
            Log::info("Fetching promos...");
            $promos = $query->paginate(10)->appends($request->query());
            Log::info("Found {$promos->total()} promos.");
    
            return view('promos.index', compact(
                'promos',
                'cabangsForFilter',
                'targetCabangId',
                'filterCabangId'
            ));
        } catch (\Exception $e) {
            Log::error("FATAL ERROR in PromoC@index: " . $e->getMessage());
            Log::error($e->getTraceAsString());
    
            return redirect()->route('dashboard.index')
                ->with('error', 'Gagal memuat data promo. Terjadi kesalahan sistem.');
        }
    }
    


     /**
      * Mengambil data untuk refresh tabel AJAX (dengan filter).
      */
      public function getPromoData(Request $request)
      {
           try {
               Log::info('getPromoData called for AJAX refresh.');
               $user = Auth::user();
               $query = Promo::with('cabang')->latest('id');
               $filterCabangId = $request->query('filter_cabang');
  
               if ($user->level === 'admin' && $filterCabangId && $filterCabangId !== 'all') {
                   $query->where('cabang_id', $filterCabangId);
                   Log::info("AJAX Refresh Promo: Admin filtered by Cabang ID: {$filterCabangId}");
               } else {
                   Log::info("AJAX Refresh Promo: User {$user->level} showing all relevant.");
                   // Non-admin melihat semua di refresh, filter view di sisi frontend jika perlu
               }
  
               $promos = $query->get();
               return view('promos.tbody', compact('promos'));
  
           } catch (\Exception $e) {
                Log::error("Error fetching data for Promo tbody refresh: " . $e->getMessage());
                return response('<tr><td colspan="10" class="text-center text-danger">Gagal memuat data promo.</td></tr>', 500);
           }
       }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_promo' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            // Hanya validasi cabang_id jika dikirim (admin)
            'cabang_id' => 'nullable|exists:cabangs,id,status,1',
            'khusus_member' => 'nullable|boolean', // Terima 0 atau 1
            'tipe_diskon' => 'required|in:percentage,fixed',
            'nilai_diskon' => 'required|numeric|min:0',
            'minimal_total_harga' => 'nullable|numeric|min:0',
            'tanggal_mulai' => 'required|date', // Terima format Y-m-d H:i:s atau Y-m-d\TH:i
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'status_promo' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $validatedData = $validator->validated();
            // Konversi boolean dari checkbox/radio (jika perlu, tergantung setup JS)
            $validatedData['khusus_member'] = $request->input('khusus_member', 0) == '1'; // Default false jika tidak ada
            $validatedData['status_promo'] = $request->input('status_promo', 0) == '1';  // Default false jika tidak ada

            // Pastikan cabang_id null jika string kosong dikirim dari select "Semua Cabang"
            if(isset($validatedData['cabang_id']) && $validatedData['cabang_id'] === '') {
                $validatedData['cabang_id'] = null;
            }

            Log::info('Creating Promo with data:', $validatedData);
            $promo = Promo::create($validatedData);

            return response()->json(['success' => true, 'message' => 'Promo baru berhasil ditambahkan.']);

        } catch (\Exception $e) {
            Log::error("Error creating promo: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menambahkan promo: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Mengupdate promo via AJAX.
     */
    public function update(Request $request, Promo $promo) // Route Model Binding {promo}
    {
         // Otorisasi sederhana (misal: hanya admin yg bisa edit?)
         // if (Auth::user()->level !== 'admin') {
         //      return response()->json(['success' => false, 'message' => 'Aksi tidak diizinkan.'], 403);
         // }

         $validator = Validator::make($request->all(), [
            'nama_promo' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'cabang_id' => 'nullable|exists:cabangs,id,status,1',
            'khusus_member' => 'nullable|boolean',
            'tipe_diskon' => 'required|in:percentage,fixed',
            'nilai_diskon' => 'required|numeric|min:0',
            'minimal_total_harga' => 'nullable|numeric|min:0',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'status_promo' => 'required|boolean',
        ]);

          if ($validator->fails()) {
             return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
         }

          try {
             $validatedData = $validator->validated();
             $validatedData['khusus_member'] = $request->input('khusus_member', 0) == '1';
             $validatedData['status_promo'] = $request->input('status_promo', 0) == '1';
              if(isset($validatedData['cabang_id']) && $validatedData['cabang_id'] === '') {
                 $validatedData['cabang_id'] = null;
             }

              Log::info("Updating Promo ID: {$promo->id} with data:", $validatedData);
              $promo->update($validatedData);

              return response()->json(['success' => true, 'message' => 'Promo berhasil diperbarui.']);

          } catch (\Exception $e) {
              Log::error("Error updating promo {$promo->id}: " . $e->getMessage());
               return response()->json(['success' => false, 'message' => 'Gagal memperbarui promo: ' . $e->getMessage()], 500);
          }
    }

    /**
     * Menghapus promo via AJAX.
     */
    public function destroy(Promo $promo) // Route Model Binding {promo}
    {
         // Otorisasi sederhana
         // if (Auth::user()->level !== 'admin') {
         //      return response()->json(['success' => false, 'message' => 'Aksi tidak diizinkan.'], 403);
         // }

         try {
             Log::warning("Deleting Promo ID: {$promo->id}, Name: {$promo->nama_promo}");
             $promo->delete();
             return response()->json(['success' => true, 'message' => 'Promo berhasil dihapus.']);
         } catch (\Exception $e) {
              Log::error("Error deleting promo {$promo->id}: " . $e->getMessage());
              return response()->json(['success' => false, 'message' => 'Gagal menghapus promo.'], 500);
         }
    }
}