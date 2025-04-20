<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
class CustomerC extends Controller
{
    public function index()
    {
        try {
            // Ambil semua customer beserta data user terkait
            $customers = Customer::with('user')->latest('id')->get();

            // Ambil user yang BELUM menjadi customer untuk dropdown tambah
            $customerUserIds = Customer::pluck('user_id')->all();
            $availableUsers = User::whereNotIn('id', $customerUserIds)
                                  ->orderBy('name')
                                  ->select('id', 'name', 'email') // Pilih kolom yg perlu
                                  ->get();

            return view('customers.index', compact('customers', 'availableUsers'));

        } catch (\Exception $e) {
             Log::error("Error fetching data for Customer index: " . $e->getMessage());
             return redirect()->route('dashboard')->with('error', 'Gagal memuat data customer.');
        }
    }

    /**
     * Mengambil data untuk refresh tabel AJAX.
     */
    public function getCustomerData()
    {
        try {
            Log::info('getCustomerData method called for AJAX refresh.');
            $customers = Customer::with('user')->latest('id')->get();
            return view('customers.tbody', compact('customers'));
        } catch (\Exception $e) {
             Log::error("Error fetching data for Customer tbody refresh: " . $e->getMessage());
             // Sesuaikan colspan (No, Nama, Email, Saldo, Tgl Daftar, Aksi = 6)
             return response('<tr><td colspan="6" class="text-center text-danger">Gagal memuat data.</td></tr>', 500);
        }
    }


    /**
     * Menyimpan customer baru (menghubungkan user dan memberi saldo awal).
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => [
                'required',
                Rule::exists('users', 'id'), // Pastikan user ada
                Rule::unique('customers', 'user_id') // Pastikan user belum jadi customer
            ],
            'saldo' => 'required|numeric|min:0', // Saldo wajib, minimal 0
        ], [
            'user_id.required' => 'User wajib dipilih.',
            'user_id.exists' => 'User yang dipilih tidak valid.',
            'user_id.unique' => 'User ini sudah terdaftar sebagai customer.',
            'saldo.required' => 'Saldo awal wajib diisi.',
            'saldo.numeric' => 'Saldo harus berupa angka.',
            'saldo.min' => 'Saldo tidak boleh negatif.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $validatedData = $validator->validated();
            Log::info('Creating Customer record with data:', $validatedData);
            $customer = Customer::create($validatedData);
            $customer->load('user'); // Load relasi user untuk response

            return response()->json(['success' => true, 'message' => 'Customer berhasil ditambahkan!', 'data' => $customer]);
        } catch (\Exception $e) {
            Log::error('Error storing customer record: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan data customer: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Mengupdate data customer (terutama saldo).
     * Menggunakan Route Model Binding {customer}.
     */
    public function update(Request $request, Customer $customer) // Terima objek Customer
    {
        // Hanya validasi saldo, user_id tidak boleh diubah
         $validator = Validator::make($request->all(), [
            'saldo' => 'required|numeric|min:0',
        ], [
            'saldo.required' => 'Saldo wajib diisi.',
            'saldo.numeric' => 'Saldo harus berupa angka.',
            'saldo.min' => 'Saldo tidak boleh negatif.',
        ]);

         if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $validatedData = $validator->validated();
            // Pastikan user_id tidak ikut terupdate
            // $validatedData['user_id'] = $customer->user_id; // Tidak perlu karena tidak divalidasi

            Log::info('Updating Customer ID: ' . $customer->id . ' with data:', $validatedData);
            $customer->update($validatedData); // Update hanya saldo

            return response()->json(['success' => true, 'message' => 'Data customer berhasil diupdate!']);
        } catch (\Exception $e) {
            Log::error('Error updating customer ' . $customer->id . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mengupdate data customer: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Menghapus data customer.
     * Menggunakan Route Model Binding {customer}.
     */
    public function destroy(Customer $customer) // Terima objek Customer
    {
        try {
            $userName = $customer->user->name ?? 'Customer ini'; // Ambil nama untuk log
            Log::info('Attempting to delete Customer record ID: ' . $customer->id . ' for User: ' . $userName);

            $customer->delete(); // Hapus record customer

            Log::info('Successfully deleted Customer record ID: ' . $customer->id);
            return response()->json(['success' => true, 'message' => 'Data customer ' . $userName . ' berhasil dihapus!']);
        } catch (\Exception $e) {
            Log::error('Error deleting customer record ' . $customer->id . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menghapus customer: ' . $e->getMessage()], 500);
        }
    }

     // Method show, create, edit bawaan dari --resource bisa dihapus jika tidak digunakan (karena pakai AJAX)
     // public function create() { ... }
     // public function show(Customer $customer) { ... }
     // public function edit(Customer $customer) { ... }
}
