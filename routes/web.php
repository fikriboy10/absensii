<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KonfigurasiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['guest:anggota'])->group(function () {
    Route::get('/', function () {
        return view('auth.login');
    })->name('login');

    Route::post('/proseslogin', [AuthController::class, 'proseslogin']);
});


Route::middleware(['guest:user'])->group(function () {
    Route::get('/panel', function () {
        return view('auth.loginadmin');
    })->name('loginadmin');

    Route::post('/prosesloginadmin', [AuthController::class, 'prosesloginadmin']);
});


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
    // Admin
   Route::middleware(['auth:user'])->group(function () {
    Route::get('/proseslogoutadmin', [AuthController::class, 'proseslogoutadmin']);
    Route::get('/panel/dashboardadmin', [DashboardController::class, 'dashboardadmin']);

    // Anggota 
    Route::get('/anggota', [AnggotaController::class, 'index']);
    Route::post('/anggota/store', [AnggotaController::class, 'store']);
    Route::post('/anggota/edit', [AnggotaController::class, 'edit']);
    Route::post('/anggota/{nis}/update', [AnggotaController::class, 'update'])->name('anggota.update');
    Route::post('/anggota/{nis}/delete', [AnggotaController::class, 'delete'])->name('anggota.delete');

     //Presensi
     Route::get('/absensi/monitoring', [AbsensiController::class, 'monitoring']);
     Route::post('/getabsensi', [AbsensiController::class, 'getabsensi']);
     Route::post('/tampilkanpeta', [AbsensiController::class, 'tampilkanpeta']);
     Route::get('/absensi/laporan', [AbsensiController::class, 'laporan']);
     Route::post('/absensi/cetaklaporan', [AbsensiController::class, 'cetaklaporan']);
     Route::get('/absensi/rekap', [AbsensiController::class, 'rekap']);
     Route::post('/absensi/cetakrekap', [AbsensiController::class, 'cetakrekap']);
     Route::get('/absensi/izinsakit', [AbsensiController::class, 'izinsakit']);
     Route::post('/absensi/approveizinsakit', [AbsensiController::class, 'approveizinsakit']);
     Route::get('/absensi/{id}/batalkanizinsakit', [AbsensiController::class, 'batalkanizinsakit']);
    
     //Konfigurasi
    Route::get('/konfigurasi/lokasiabsen', [KonfigurasiController::class, 'lokasiabsen']);
    Route::post('/konfigurasi/updatelokasiabsen', [KonfigurasiController::class, 'updatelokasiabsen']);
    
});

 
   