<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request; // Pastikan ini yang diimpor, bukan facade

// Route tanpa middleware sanctum, menggunakan guard 'anggota'
Route::get('/test-user', function () {
    $user = Auth::guard('anggota')->user();
    return response()->json($user);
});

// Route dengan middleware sanctum, menggunakan auth default
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user(); // Mengembalikan data user yang diautentikasi
});
