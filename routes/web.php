<?php

use App\Http\Controllers\API\AUTH\AuthC;
use App\Http\Controllers\API\Dashboard\DashboardC;
use App\Http\Controllers\API\Settingg\CabangC;
use App\Http\Controllers\API\Settingg\SettingC;
use App\Http\Controllers\API\User\PetugasC;
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


Route::middleware('auth')->group(function () {

    // Route::get('/dashboard', [DashboardC::class, 'index'])->name('dashboard.index');
    Route::get('/dashboard', [DashboardC::class, 'index'])->name('dashboard.index');
    Route::get('/logout', [AuthC::class, 'logout'])->name('logout');
    Route::get('/admins', [UserC::class, 'listAdmins'])->name('admins.list');
    Route::get('/supervisors', [UserC::class, 'listSupervisors'])->name('supervisors.list');

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
    Route::put('/{petuga}', [PetugasC::class, 'update'])->name('petugas.update');
    Route::delete('/{petuga}', [PetugasC::class, 'destroy'])->name('petugas.destroy');
});
