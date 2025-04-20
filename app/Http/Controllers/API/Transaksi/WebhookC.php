<?php

namespace App\Http\Controllers\API\Transaksi;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\setting;
use App\Models\transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Config; 
use Midtrans\Notification;

class WebhookC extends Controller
{
    public function __construct() {
        try {
            Config::$serverKey = config('midtrans.server_key');
            Config::$isProduction = config('midtrans.is_production');
            if (!Config::$serverKey) { throw new \Exception('Midtrans Server Key not configured.'); }
        } catch (\Exception $e) { Log::critical("MIDTRANS SETUP ERROR in WebhookController: " . $e->getMessage()); }
    }

    /**
     * Menangani HTTP Notification dari Midtrans.
     */
    public function handleMidtrans(Request $request)
    {
        Log::info('--- MIDTRANS WEBHOOK RECEIVED (TESTING) ---');
        Log::info('Midtrans Webhook received.');
        Log::debug('Midtrans Webhook Payload:', $request->all());
        Log::info('Headers:', $request->headers->all()); // Lihat header (ada signature?)
        Log::info('Payload:', $request->all());
         // Log detail payload

        // 1. Gunakan helper Midtrans untuk memproses notifikasi
        try {
            // Notification::handle() akan:
            // - Membaca payload JSON dari request
            // - Melakukan verifikasi signature (JIKA DI MODE PRODUCTION)
            // - Mengembalikan objek notifikasi jika valid, atau throw Exception jika tidak valid/error
            $notif = new Notification(); // Gunakan instance Notification
        } catch (\Exception $e) {
             Log::error('Error processing Midtrans Notification payload or invalid signature: ' . $e->getMessage());
             return response()->json(['message' => 'Invalid notification format or signature'], 400);
        }

        // 2. Ambil data penting dari notifikasi
        $transactionStatus = $notif->transaction_status;
        $fraudStatus = $notif->fraud_status ?? null; // Mungkin tidak selalu ada
        $orderId = $notif->order_id; // Ini adalah ID unik dari sistem KITA ($internalTransactionId)
        $paymentType = $notif->payment_type;
        $grossAmount = $notif->gross_amount;
        $midtransTransactionId = $notif->transaction_id; // ID transaksi dari Midtrans

        Log::info("Processing Midtrans Notification: OrderID={$orderId}, Status={$transactionStatus}, Fraud={$fraudStatus}, Type={$paymentType}, Amount={$grossAmount}");

        // 3. Cari transaksi PENDING di sistem kita berdasarkan order_id
        // Gunakan DB Transaction untuk keamanan
        DB::beginTransaction();
        try {
             $transaction = Transaction::where('gateway_ref_id', $orderId) // Cari berdasarkan Order ID kita
                                       ->where('status', 'pending')
                                       ->lockForUpdate() // Kunci row
                                       ->first();

            if (!$transaction) {
                 Log::warning("Webhook received for already processed or unknown transaction. Order ID: {$orderId}. Ignoring.");
                 DB::commit(); // Commit saja karena tidak ada yg diubah
                 return response()->json(['message' => 'Transaction already processed or not found'], 200);
            }

             // Simpan payload notifikasi lengkap ke transaksi kita
             $transaction->gateway_payload = json_encode($notif->getResponse()); // Simpan respons notifikasi
             $transaction->gateway_transaction_id = $midtransTransactionId; // Simpan ID Midtrans jika perlu kolomnya

            // 4. Update status transaksi kita berdasarkan status Midtrans
            $newStatus = 'pending'; // Default
            $updateBalance = false;

            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'accept') {
                    $newStatus = 'paid'; // Atau 'completed'
                    $updateBalance = true;
                } else if ($fraudStatus == 'deny') {
                     $newStatus = 'failed'; // Atau 'denied'
                }
                // Jika fraud 'challenge', biarkan 'pending' menunggu review manual?
            } else if ($transactionStatus == 'settlement') {
                 // Settlement biasanya status akhir sukses untuk banyak metode
                 $newStatus = 'paid'; // Atau 'completed'
                 $updateBalance = true;
            } else if ($transactionStatus == 'pending') {
                 $newStatus = 'pending'; // Tetap pending
            } else if ($transactionStatus == 'deny') {
                 $newStatus = 'failed'; // Atau 'denied'
            } else if ($transactionStatus == 'expire') {
                 $newStatus = 'expired';
            } else if ($transactionStatus == 'cancel') {
                 $newStatus = 'cancelled';
            }

            // Update status transaksi lokal
            $transaction->status = $newStatus;
            $transaction->processed_at = now();
            $transaction->save();
             Log::info("Transaction ID {$transaction->id} (OrderID: {$orderId}) status updated to '{$newStatus}'.");


            // 5. Jika status sukses, update saldo & membership
            if ($updateBalance) {
                 $user = User::find($transaction->user_id);
                 if (!$user) { throw new \Exception("User ID {$transaction->user_id} not found for transaction {$transaction->id}."); }

                 // Cari atau buat record member
                 $member = Member::firstOrCreate(
                     ['user_id' => $user->id],
                     ['balance' => 0.00, 'is_active' => false] // Default jika baru
                 );

                 // Tambah saldo
                 $paidAmount = (float) $grossAmount; // Jumlah yg benar-benar dibayar
                 $newBalance = $member->balance + $paidAmount;
                 $member->balance = $newBalance;
                  Log::info("Adding balance {$paidAmount} to User ID {$user->id}. New balance: {$newBalance}");

                 // Cek aktivasi membership jika belum aktif
                 if (!$member->is_active) {
                     Log::info("Membership for User ID {$user->id} is not active. Checking fee deduction.");
                     $setting = Setting::first();
                     $membershipFee = $setting->membership_fee ?? 25000;

                     if ($newBalance >= $membershipFee) {
                         $member->balance -= $membershipFee;
                         $member->is_active = true;
                         $member->joined_at = now();
                         $member->save(); // Simpan perubahan member
                          Log::info("Membership activated for User ID {$user->id}. Fee {$membershipFee} deducted. Final Balance: {$member->balance}");

                         // Buat record transaksi biaya member
                         Transaction::create([
                             'user_id' => $user->id, 'type' => 'membership_fee',
                             'amount' => -$membershipFee, 'description' => 'Biaya Pendaftaran Member',
                             'status' => 'completed', 'processed_at' => now(),
                         ]);
                          Log::info("Membership fee transaction created for User ID {$user->id}.");

                     } else {
                          Log::warning("User ID {$user->id} topped up {$paidAmount}, balance {$newBalance} insufficient for fee {$membershipFee}. Membership remains inactive.");
                          $member->save(); // Simpan saldo baru saja
                     }
                 } else {
                     // Jika sudah member aktif, simpan saja saldo barunya
                     $member->save();
                 }
            }

            // Commit transaksi DB jika semua lancar
            DB::commit();
            Log::info("Midtrans notification processed successfully for Order ID: {$orderId}");
            return response()->json(['message' => 'Notification processed successfully'], 200); // Kirim 200 OK ke Midtrans

        } catch (\Exception $e) {
            DB::rollBack(); // Batalkan jika ada error
            Log::error("Error processing Midtrans notification for Order ID {$orderId}: " . $e->getMessage());
            // Kirim response error tapi mungkin tetap 200 OK agar Midtrans tidak coba lagi? Atau 500? Cek dok Midtrans.
            // Kita coba kirim 500 agar bisa diinvestigasi.
             return response()->json(['message' => 'Internal server error processing notification'], 500);
        }
    }
}