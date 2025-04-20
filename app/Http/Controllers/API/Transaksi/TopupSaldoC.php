<?php

namespace App\Http\Controllers\API\Transaksi;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Midtrans\Config;
use Midtrans\Snap;

class TopupSaldoC extends Controller
{
    public function topup($id){
        $users = Customer::with('user')->findOrFail($id);
        return view('topup.index', compact('users'));
    }

    public function topupStore(Request $request)
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;
    
        $order_id = 'invoice-' . time();
        $transaction_details = [
            'order_id' => $order_id,
            'gross_amount' => 50000,
        ];
    
        $customer_details = [
            'first_name' => $request->name,
            'email' => $request->email,
            'phone' => $request->telepon,
        ];
    
        $item_details = [[
            'id' => 'item-1',
            'price' => $request->amount,
            'quantity' => 1,
            'name' => 'Top Up saldo',
        ]];
    
        $userId = $request->Id; 

        $transaction_data = [
            'transaction_details' => $transaction_details,
            'customer_details' => $customer_details,
            'item_details' => $item_details,
            'custom_fields' => [
                'custom_field1' => $userId,
            ],
        ];
    
        try {
            session([
                'user_id_to_register' => $userId,
                'topup_amount' => $request->amount, 
            ]); 
            $snapToken = Snap::getSnapToken($transaction_data);
            return view('topup.payment', compact('snapToken'));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat transaksi: ' . $e->getMessage());
        }
    }

    public function success()
    {
        $userId = session('user_id_to_register');
        $amount = session('topup_amount');
    
        $customer = Customer::where('user_id', $userId)->first();
    
        DB::beginTransaction();
        try {
            if ($customer) {
                Transaction::create([
                    'user_id' => $userId,
                    'type' => 'topup',
                    'amount' => $amount,
                    'description' => 'Top up saldo',
                    'status' => 'completed',
                    'processed_at' => now(),
                    'payment_gateway' => 'midtrans',
                    'gateway_ref_id' => 'SNAP-' . uniqid(),
                ]);
            
    
                $customer->saldo += $amount;
                $customer->save();
            }
    
            DB::commit();
    
            session()->forget(['user_id_to_register', 'topup_amount']);
    
            return view('topup.success');
    
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat menyimpan top up: ' . $e->getMessage());
        }
    }
}
