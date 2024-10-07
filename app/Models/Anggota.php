<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Anggota extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Menentukan tabel yang digunakan
    protected $table = "anggota";

    // Menentukan primary key
    protected $primaryKey = "nis";
    
    // Jika 'nis' bukan auto-increment dan bukan integer
    public $incrementing = false; 
    protected $keyType = 'string';

    // Field yang bisa diisi
    protected $fillable = [
        'nis',
        'nama_lengkap',
        'kelas_jurusan',
        'no_hp',
        'password',
    ];

    // Field yang disembunyikan
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Cast attribute ke tipe data tertentu
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
