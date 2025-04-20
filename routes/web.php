<?php

use App\Http\Controllers\API\AUTH\AuthC;
use App\Http\Controllers\API\Dashboard\DashboardC;
use App\Http\Controllers\API\MasterData\LayananC;
use App\Http\Controllers\API\MasterData\SatuanC;
use App\Http\Controllers\API\Promo\PromoC;
use App\Http\Controllers\API\Settingg\CabangC;
use App\Http\Controllers\API\Settingg\SettingC;
use App\Http\Controllers\API\Transaksi\MemberC;
use App\Http\Controllers\API\Transaksi\TopupSaldoC;
use App\Http\Controllers\API\Transaksi\TransaksiC;
use App\Http\Controllers\API\Transaksi\WebhookC;
use App\Http\Controllers\API\User\CustomerC;
use App\Http\Controllers\API\User\PetugasC;
use App\Http\Controllers\API\User\SupervisorC;
use App\Http\Controllers\API\User\UserC;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware(['guest'])->group(function () {

    Route::get('/login', [AuthC::class, 'showLoginForm'])->name('auth.login');
    Route::post('/login', [AuthC::class, 'login'])->name('login');
    Route::get('/register', [AuthC::class, 'showRegisterForm'])->name('auth.regis');
    Route::post('/register', [AuthC::class, 'register'])->name('register');

});

Route::post('/webhooks/midtrans', [WebhookC::class, 'handleMidtrans'])->name('webhooks.midtrans');
Route::middleware('auth')->group(function () {

    // Route::get('/dashboard', [DashboardC::class, 'index'])->name('dashboard.index');
    Route::get('/dashboard', [DashboardC::class, 'index'])->name('dashboard.index');
    Route::get('/logout', [AuthC::class, 'logout'])->name('logout');
    Route::get('/admins', [UserC::class, 'listAdmins'])->name('admin.list'); 
    Route::get('/admins/data', [UserC::class, 'getAdminData'])->name('admin.data');
    Route::get('/sepervisors', [UserC::class, 'listSupervisors'])->name('supervisor.list'); // Tampilkan list supervisor
    Route::get('/supervisors/data', [UserC::class, 'getSupervisorData'])->name('supervisor.data');
    Route::get('/pengguna', [UserC::class, 'listPengguna'])->name('pengguna.list');
    Route::get('/pengguna/data', [UserC::class, 'getPenggunaData'])->name('pengguna.data'); 
    Route::get('/select-cabang', [CabangC::class, 'showSelection'])
    ->name('cabang.select')
    ->middleware('auth'); // Hanya user terautentikasi

    Route::post('/select-cabang', [CabangC::class, 'storeSelection'])
    ->name('cabang.storeSelection')
    ->middleware('auth');

    // User routes
    Route::get('/users', [UserC::class, 'index'])->name('user.index'); // Menampilkan halaman manajemen pengguna (GET /user)
    Route::get('/users/data', [UserC::class, 'getUsers'])->name('user.data'); // Mengambil data tabel untuk AJAX (GET /user/data)
    Route::post('/users', [UserC::class, 'store'])->name('user.store'); // Menyimpan pengguna baru (POST /user)
    Route::put('/users/{user}', [UserC::class, 'update'])->name('user.update');
    Route::delete('/users/{user}', [UserC::class, 'destroy'])->name('user.destroy');;

    Route::get('/setting', [SettingC::class, 'index'])->name('setting.index');
    Route::put('/setting/update', [SettingC::class, 'update'])->name('setting.update');

    Route::get('/cabang', [CabangC::class, 'index'])->name('cabang.index');
    Route::get('/cabang/data', [CabangC::class, 'getCabangs'])->name('cabang.data');
    Route::post('/cabang', [CabangC::class, 'store'])->name('cabang.store');
    Route::put('/{cabang}', [CabangC::class, 'update'])->name('cabang.update'); // Gunakan {cabang} untuk model binding
    Route::delete('/{cabang}', [CabangC::class, 'destroy'])->name('cabang.destroy');


    Route::get('/petugas', [PetugasC::class, 'index'])->name('petugas.index');
    Route::get('petugas//data', [PetugasC::class, 'getPetugasData'])->name('petugas.data');
    Route::post('/petugas', [PetugasC::class, 'store'])->name('petugas.store');
    Route::put('/petugas/{petuga}', [PetugasC::class, 'update'])->name('petugas.update');
    Route::delete('/petugas/{petuga}', [PetugasC::class, 'destroy'])->name('petugas.destroy');


    Route::get('/supervisor', [SupervisorC::class, 'index'])->name('supervisor.index');
    Route::get('supervisor//data', [SupervisorC::class, 'getSupervisorData'])->name('supervisor.data');
    Route::post('/supervisor', [SupervisorC::class, 'store'])->name('supervisor.store');
    Route::put('/supervisor/{superviso}', [SupervisorC::class, 'update'])->name('supervisor.update');
    Route::delete('/supervisor/{superviso}', [SupervisorC::class, 'destroy'])->name('supervisor.destroy');

    Route::get('/payment/success', [MemberC::class, 'handleSuccess'])->name('payment.success');
    Route::get('/member', [MemberC::class, 'index'])->name('member.index'); // Halaman status member
    Route::get('/findAllMember', [MemberC::class, 'findAll'])->name('member.findAll'); // Halaman status member
    Route::get('/topup', [MemberC::class, 'showTopupForm'])->name('member.topup.form'); // Halaman form topup
    Route::post('/topup', [MemberC::class, 'initiateTopup'])->name('member.topup.initiate');

    Route::get('/history', [TransaksiC::class, 'indexTopup'])->name('history.topup.index');

     // Proses request token snap
     Route::get('/promo', [PromoC::class, 'index'])->name('promo.index');
     Route::get('/promo/data', [PromoC::class, 'getPromoData'])->name('promo.data'); // AJAX data
     Route::post('/promo', [PromoC::class, 'store'])->name('promo.store');
     // Gunakan {promo} untuk Route Model Binding
     Route::put('/{promo}', [PromoC::class, 'update'])->name('promo.update'); // Akan diakses via POST dg _method=PUT
     Route::delete('/{promo}', [PromoC::class, 'destroy'])->name('promo.destroy'); // Akan diakses via POST dg _method=DELETE 

    // Route untuk redirect callback (opsional, bisa dihapus jika tidak pakai finish URL)

    Route::get('/member/topup/failed/{orderId}', [MemberC::class, 'handlePaymentFailed'])->name('member.topup.handleFailed');
    
    // Route::get('/topup/success', [MemberC::class, 'topupSuccess'])->name('topup.success');
    // Route::get('/topup/failed', [MemberC::class, 'topupFailed'])->name('topup.failed');
    Route::get('/customers', [CustomerC::class, 'index'])->name('customer.index');
    Route::get('/data', [CustomerC::class, 'getCustomerData'])->name('customer.data'); // AJAX data
    Route::post('/', [CustomerC::class, 'store'])->name('customer.store');
    // Gunakan {customer} untuk Route Model Binding
    Route::put('/customer/{customer}', [CustomerC::class, 'update'])->name('customer.update');
    Route::delete('/customer/{customer}', [CustomerC::class, 'destroy'])->name('customer.destroy');

    
    Route::get('/topupsaldo/{id}', [TopupSaldoC::class, 'topup'])->name("topup");
    Route::post('/topupStore', [TopupSaldoC::class, 'topupStore'])->name("topupStore");
    Route::get('/topup/success', [TopupSaldoC::class, 'success'])->name("payment.topup.success");

   
    Route::get('satuan/', [SatuanC::class, 'index'])->name('satuan.index');
    Route::get('/satuan/data', [SatuanC::class, 'data'])->name('satuan.data');
    Route::post('/satuan', [SatuanC::class, 'store'])->name('satuan.store');
    Route::put('/satuan/{satuan}', [SatuanC::class, 'update'])->name('satuan.update');
    Route::delete('/satuan/{satuan}', [SatuanC::class, 'destroy'])->name('satuan.destroy');

    Route::get('/layanan', [LayananC::class, 'index'])->name('layanan.index'); // Halaman utama CRUD
    Route::get('/layanan/data', [LayananC::class, 'data'])->name('layanan.data'); // Mengambil data tabel via AJAX
    Route::post('/layanan/', [LayananC::class, 'store'])->name('layanan.store'); // Menyimpan data baru via AJAX
    // Gunakan {layanan} sesuai konvensi Route Model Binding
    Route::put('/layanan/{layanan}', [LayananC::class, 'update'])->name('layanan.update'); // Mengupdate data via AJAX
    Route::delete('/layanan/{layanan}', [LayananC::class, 'destroy'])->name('layanan.destroy'); 
});

// Grup route BARU untuk Layanan

 
// // app/Http/Middleware/VerifyCsrfToken.php
// protected $except = [
//     '/webhooks/*', // Kecualikan semua URL yg diawali /webhooks/
//     // ... route lain yg perlu dikecualikan ...
// ];

