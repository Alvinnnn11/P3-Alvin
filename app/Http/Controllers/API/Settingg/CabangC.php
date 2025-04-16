<?php

namespace App\Http\Controllers\API\Settingg;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CabangC extends Controller
{
    public function index()
    {
        $cabangs = Cabang::orderBy('nama_perusahaan')->get();
        return view('cabang.index', compact('cabangs'));
    }

    public function showSelection()
    {
        if (!in_array(Auth::user()->level, ['member', 'pengguna'])) {
             Log::warning('User ' . Auth::id() . ' mencoba akses pemilihan cabang, tapi levelnya ' . Auth::user()->level);
             return redirect('/dashboard');
        }
        $availableCabangs = Cabang::where('status', true)->orderBy('nama_perusahaan')->get();
        Log::info('Menampilkan halaman pemilihan cabang untuk user: ' . Auth::id());
        return view('cabang.select', compact('availableCabangs'));
    }

    public function storeSelection(Request $request)
    {
        // Pastikan hanya member/pengguna
         if (!in_array(Auth::user()->level, ['member', 'pengguna'])) {
            return redirect('/dashboard');
        }

        // Validasi input
        $validator = Validator::make($request->all(), [
            'cabang_id' => [
                'required',
                // Pastikan ID cabang yg dipilih ada dan statusnya aktif
                Rule::exists('cabangs', 'id')->where(function ($query) {
                    $query->where('status', true);
                }),
            ]
        ], [
            'cabang_id.required' => 'Anda harus memilih cabang.',
            'cabang_id.exists' => 'Cabang yang Anda pilih tidak valid atau tidak aktif.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('cabang.select')
                        ->withErrors($validator)
                        ->withInput();
        }

        // Ambil data cabang yang valid
        $selectedCabang = Cabang::find($request->input('cabang_id'));

        if ($selectedCabang) {
            // Simpan objek cabang ke session
            session(['assigned_cabang' => $selectedCabang]);
            Log::info('User ID: ' . Auth::id() . ' memilih Cabang ID: ' . $selectedCabang->id . '. Disimpan ke session.');
            // Redirect ke dashboard setelah memilih
            return redirect()->intended('/dashboard');
        } else {
             // Seharusnya tidak terjadi karena sudah divalidasi exists
             Log::error('Gagal menemukan cabang (ID: '.$request->input('cabang_id').') setelah validasi untuk user: ' . Auth::id());
             return redirect()->route('cabang.select')->with('error', 'Gagal memproses pilihan cabang.');
        }
    }


    public function getCabangs() // Fungsi untuk AJAX refresh
    {
        Log::info('getCabangs method called for AJAX refresh.'); // Log Panggilan
        $cabangs = Cabang::orderBy('nama_perusahaan')->get();
        return view('cabang.tbody', compact('cabangs')); // <-- PERBAIKI DI SINI
    }

    public function store(Request $request)
    {
        // --- GENERATE KODE CABANG ---
        $prefix = "CBNG";
        $lastCabang = Cabang::orderBy('id', 'desc')->first();
        $nextNumber = 1;
        if ($lastCabang) {
            $lastCode = $lastCabang->kode_cabang;
            if (str_starts_with($lastCode, $prefix)) {
                $lastNumber = (int) substr($lastCode, strlen($prefix));
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = ($lastCabang->id ?? 0) + 1;
            }
        }
        $generatedKode = $prefix . sprintf('%04d', $nextNumber);
        // --------------------------

        $dataToValidate = $request->except(['_token', '_method', 'logo_perusahaan']);
        $dataToValidate['kode_cabang'] = $generatedKode;

        $validator = Validator::make($dataToValidate, [
            // Pastikan 'required' sudah dihapus dari sini
            'kode_cabang' => 'string|max:50|unique:cabangs,kode_cabang', // No 'required'
            'nama_perusahaan' => 'required|string|max:255',
            'alamat_perusahaan' => 'nullable|string',
            'provinsi_perusahaan' => 'nullable|string|max:100',
            'kota_perusahaan' => 'nullable|string|max:100',
            'kecamatan_perusahaan' => 'nullable|string|max:100',
            'kelurahan_perusahaan' => 'nullable|string|max:100',
            'kode_pos' => 'nullable|string|max:10',
            'status' => 'required|boolean',
        ]);

         $fileValidator = Validator::make($request->only('logo_perusahaan'), [
            'logo_perusahaan' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
         ]);

        if ($validator->fails() || $fileValidator->fails()) {
            $errors = $validator->errors()->merge($fileValidator->errors());
             if ($errors->has('kode_cabang')) {
                 Log::warning('Generated kode_cabang conflict: ' . $generatedKode);
             }
            return response()->json(['success' => false, 'errors' => $errors], 422);
        }

        $validatedData = $validator->validated();

        try {
            if ($request->hasFile('logo_perusahaan')) {
                $filePath = $request->file('logo_perusahaan')->store('logos_cabang', 'public');
                $validatedData['logo_perusahaan'] = $filePath;
            } else {
                 $validatedData['logo_perusahaan'] = null;
            }

            // Log data sebelum create untuk memastikan kode ada
            Log::info('Creating Cabang with data:', $validatedData);

            $cabang = Cabang::create($validatedData);

            return response()->json(['success' => true, 'message' => 'Cabang berhasil ditambahkan!', 'data' => $cabang]);

        } catch (\Exception $e) {
            Log::error('Error storing cabang: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menambahkan cabang: ' . $e->getMessage()], 500);
        }
    }


    public function update(Request $request, Cabang $cabang) // Gunakan Route Model Binding
    {
        $validator = Validator::make($request->all(), [
            'kode_cabang' => 'string|max:50|unique:cabangs,kode_cabang,' . $cabang->id,
            'nama_perusahaan' => 'required|string|max:255',
            'alamat_perusahaan' => 'nullable|string',
            'provinsi_perusahaan' => 'nullable|string|max:100',
            'kota_perusahaan' => 'nullable|string|max:100',
            'kecamatan_perusahaan' => 'nullable|string|max:100',
            'kelurahan_perusahaan' => 'nullable|string|max:100',
            'kode_pos' => 'nullable|string|max:10',
            'logo_perusahaan' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        try {
            if ($request->hasFile('logo_perusahaan')) {
                if ($cabang->logo_perusahaan && Storage::disk('public')->exists($cabang->logo_perusahaan)) {
                    Storage::disk('public')->delete($cabang->logo_perusahaan);
                }
                $filePath = $request->file('logo_perusahaan')->store('logos_cabang', 'public');
                $validatedData['logo_perusahaan'] = $filePath;
            }

            $cabang->update($validatedData);

            return response()->json(['success' => true, 'message' => 'Cabang berhasil diupdate!']);
        } catch (\Exception $e) {
            Log::error('Error updating cabang ' . $cabang->id . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mengupdate cabang: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Cabang $cabang)
    {
        try {
            if ($cabang->logo_perusahaan && Storage::disk('public')->exists($cabang->logo_perusahaan)) {
                Storage::disk('public')->delete($cabang->logo_perusahaan);
            }

            $cabang->delete();

            return response()->json(['success' => true, 'message' => 'Cabang berhasil dihapus!']);
        } catch (\Exception $e) {
            Log::error('Error deleting cabang ' . $cabang->id . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menghapus cabang: ' . $e->getMessage()], 500);
        }
    }
}
