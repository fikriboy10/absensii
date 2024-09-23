<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // Mendapatkan tanggal, bulan, dan tahun saat ini
        $hariini = date("Y-m-d");
        $bulanini = date("m");
        $tahunini = date("Y");

        // Mendapatkan NIS dari user yang sedang login
        $nis = Auth::guard('anggota')->user()->nis;

        // Query untuk absen hari ini
        $absensihariini = DB::table('absensi')
            ->where('nis', $nis)
            ->whereDate('tgl_absensi', $hariini)  // Menggunakan whereDate() untuk perbandingan tanggal
            ->first();

        // Query untuk mendapatkan histori absensi bulan ini
        $historibulanini = DB::table('absensi')
            ->where('nis', $nis)
            ->whereMonth('tgl_absensi', $bulanini)  // Menggunakan whereMonth() untuk bulan
            ->whereYear('tgl_absensi', $tahunini)   // Menggunakan whereYear() untuk tahun
            ->orderBy('tgl_absensi')
            ->get();

        // Rekap absensi untuk bulan ini
        $rekapabsensi = DB::table('absensi')
            ->selectRaw('COUNT(nis) as jmlhadir, SUM(IF(jam_in > "08:00", 1, 0)) as jmlterlambat')
            ->where('nis', $nis)
            ->whereMonth('tgl_absensi', $bulanini)
            ->whereYear('tgl_absensi', $tahunini)
            ->first();

        // Leaderboard absensi hari ini
        $leaderboard = DB::table('absensi')
            ->join('anggota', 'absensi.nis', '=', 'anggota.nis')
            ->whereDate('tgl_absensi', $hariini)
            ->orderBy('jam_in')
            ->get();

        // Daftar nama bulan
        $namabulan = [
            "", "Januari", "Februari", "Maret", "April", "Mei", "Juni", 
            "Juli", "Agustus", "September", "Oktober", "November", "Desember"
        ];

        // Rekap izin dan sakit untuk bulan ini
        
        $rekapizin = DB::table('pengajuan_izin')
            ->selectRaw('SUM(IF(status = "i", 1, 0)) as jmlizin, SUM(IF(status = "s", 1, 0)) as jmlsakit')
            ->where('nis', $nis)
            ->whereMonth('tgl_izin', $bulanini)
            ->whereYear('tgl_izin', $tahunini)
            ->where('status_approved', 1)
            ->first();

           

        // Mengirim semua variabel ke view 'dashboard.dashboard'
        return view('dashboard.dashboard', compact(
            'absensihariini', 'historibulanini', 'namabulan', 
            'bulanini', 'tahunini', 'rekapabsensi', 'leaderboard', 'rekapizin'
        ));
    }
}
