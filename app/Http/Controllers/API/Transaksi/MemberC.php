<?php
namespace App\Http\Controllers\API\Transaksi;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Member; // Pastikan model ini ada jika diperlukan saat aktivasi
use App\Models\User;
use App\Models\Transaction;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use Midtrans\Config;
use Midtrans\Snap;
// --------------------------------

// Pertimbangkan mengganti nama class menjadi MemberController jika lebih sesuai
class MemberC extends Controller
{
    /**
     * Konstruktor untuk setup Xendit API Key.
     */
    
     public function __construct() {
        try {
            $serverKey = config('midtrans.server_key');
            $isProduction = config('midtrans.is_production');
            if (!$serverKey) { throw new \Exception('Midtrans Server Key not configured.'); }
            if (!class_exists(Config::class)) { throw new \Exception('Midtrans Config class not found.'); }

            Config::$serverKey = $serverKey;
            Config::$isProduction = $isProduction;
            Config::$isSanitized = config('midtrans.is_sanitized', true);
            Config::$is3ds = config('midtrans.is_3ds', true);
            Log::info('Midtrans configured successfully for MemberC.');
        } catch (\Throwable $e) { Log::critical("MIDTRANS SETUP ERROR in MemberC: " . $e->getMessage()); }
    }

    /**
     * Menampilkan status membership & tombol topup. (Tetap Sama)
     */
    public function findAll(){
        $member = Member::with('user')->get();
        return view('members.index', compact('member'));
    }

    public function index() {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user) { return redirect('/login'); }
        try {
            $memberInfo = $user->membership()->first(); 
            $hasRecord = $user->hasMembershipRecord(); 
            $isActive = $user->isMemberActive();       
            $canBecomeMember = ($user->level === 'pengguna' && !$hasRecord);
            $membershipFee = optional(Setting::first())->membership_fee ?? 25000; // Lebih aman pakai optional()
            return view('members.status', compact('user', 'memberInfo', 'membershipFee', 'canBecomeMember', 'isActive')); // Kirim isActive juga
        } catch (\Throwable $th) {
            Log::error("Error in MemberC@index for user {$user->id}: " . $th->getMessage());
            return redirect('/dashboard')->with('error', 'Gagal memuat info membership.');
         }
    }

     /**
      * Menampilkan form input jumlah topup. (Tetap Sama)
      */

      public function showTopupForm()
      {
          $userId = auth()->id();
          $isAlreadyCustomer = Customer::where('user_id', $userId)->exists();
          $isAlreadyMember = Member::where('user_id', $userId)->exists();
          
          return view('members.topup', [
              'isAlreadyCustomer' => $isAlreadyCustomer,
              'isAlreadyMember' => $isAlreadyMember,
              'minTopup' => 25000,
            //   'membershipFee' => 50000,
          ]);
      }
      

     /**
      * Memulai proses Top Up: Membuat transaksi PENDING dan MENDAPATKAN Snap Token Midtrans.
      * Mengarahkan ke halaman  internal setelah pembayaran (TANPA WEBHOOK).
      */
      public function initiateTopup(Request $request)
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
            'name' => 'Pendaftaran Member',
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
            return view('members.payment', compact('snapToken'));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat transaksi: ' . $e->getMessage());
        }
      }
      
      
    public function handleSuccess()
    {
        try {
            $userId = session('user_id_to_register');
            // dd($userId);
            $amount = session('topup_amount');
         
            DB::transaction(function () use ($userId, $amount) {
                $customer = Customer::firstOrCreate(
                    ['user_id' => $userId],
                    ['saldo' => 0]
                );
            
                Log::info("Saldo sebelum:", ['saldo' => $customer->saldo]);
                $customer->saldo += $amount;
                $customer->save();
                Log::info("Saldo sesudah:", ['saldo' => $customer->saldo]);
            
                Transaction::create([
                    'user_id' => $userId,
                    'type' => 'topup',
                    'amount' => $amount,
                    'description' => 'Pendaftaran Member',
                    'status' => 'completed',
                    'processed_at' => now(),
                    'payment_gateway' => 'midtrans',
                    'gateway_ref_id' => 'SNAP-' . uniqid(),
                ]);
            
                Member::updateOrCreate(
                    ['user_id' => $userId],
                    [
                        'is_active' => true,
                        'joined_at' => now()
                    ]
                );
            });
            

            session()->forget(['user_id_to_register', 'topup_amount']);
        } catch (\Exception $e) {
            Log::error('Payment success error: ' . $e->getMessage());
            return back()->with('error', 'Gagal memproses pembayaran.');
        }
        return view('members.success');
    }

      


    /**
     * Method untuk menangani redirect jika pembayaran gagal/dibatalkan (opsional).
     * Membutuhkan route: GET /member/topup/failed/{orderId} -> name('member.topup.failed')
     */
     public function handlePaymentFailed(Request $request, $orderId) {
        Log::warning("Handling simulated failed/pending payment for Order ID: {$orderId}");
         $user = Auth::user();
         if(!$user) return redirect('/login');

          // Update status transaksi lokal jadi 'failed' atau 'cancelled' (opsional)
         $transaction = Transaction::where('gateway_ref_id', $orderId)
                                  ->where('user_id', $user->id)
                                  ->where('status', 'pending')
                                  ->first();
         if ($transaction) {
             $transaction->status = 'failed'; // Atau 'cancelled'
             // $transaction->processed_at = now();
             $transaction->save();
              Log::info("Transaction ID {$transaction->id} status updated to '{$transaction->status}' via failed redirect.");
         }

         return redirect()->route('member.topup.form')->with('error', 'Pembayaran Anda gagal atau dibatalkan. Silakan coba lagi.');
     }

} 