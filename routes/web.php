<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Route untuk halaman login (dapat diakses publik)
Route::get('/', function () {
    return view('auth.login');
})->name('login');

Route::post('/proseslogin', [AuthController::class, 'proseslogin']);

// Route yang membutuhkan autentikasi (hanya bisa diakses setelah login)
Route::middleware(['auth:anggota'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    
    // Logout
    Route::get('/proseslogout', [AuthController::class, 'proseslogout']);
    
    // Route untuk absensi
    Route::get('/absensi/create', [AbsensiController::class, 'create']);
    Route::post('/absensi/store', [AbsensiController::class, 'store']);
    
    // Edit profil
    Route::get('/editprofile', [AbsensiController::class, 'editprofile']);
    Route::post('/absensi/{nis}/updateprofile', [AbsensiController::class, 'updateprofile']);

    // Histori
    Route::get('/absensi/histori', [AbsensiController::class, 'histori']);
    Route::post('/gethistori', [AbsensiController::class, 'gethistori']);
   
    //izin
    Route::get('/absensi/izin', [AbsensiController::class, 'izin']);
    Route::get('/absensi/buatizin', [AbsensiController::class, 'buatizin']);
    Route::post('/absensi/storeizin', [AbsensiController::class, 'storeizin']);
});
 