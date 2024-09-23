<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $hariini = date("Y-m-d");
        $bulanini = date("m") * 1;
        $tahunini = date("Y");
        $nis = Auth::guard('anggota')->user()->nis;
        $absensihariini = DB::table('absensi')->where('nis', $nis)->where('tgl_absensi', $hariini)->first();
        $historibulanini = DB::table('absensi')
            ->where('nis', $nis)
            ->whereRaw('MONTH(tgl_absensi)="' . $bulanini . '"')
            ->whereRaw('YEAR(tgl_absensi)="' . $tahunini . '"')
            ->orderBy('tgl_absensi')
            ->get();

        $rekapabsensi = DB::table('absensi')
            ->selectRaw('COUNT(nis) as jmlhadir, SUM(IF(jam_in > "08:00",1,0)) as jmlterlambat')
            ->where('nis', $nis)
            ->whereRaw('MONTH(tgl_absensi)="' . $bulanini . '"')
            ->whereRaw('YEAR(tgl_absensi)="' . $tahunini . '"')
            ->first();

        $leaderboard = DB::table('absensi')
            ->join('anggota', 'absensi.nis', '=', 'anggota.nis')
            ->where('tgl_absensi', $hariini)
            ->orderBy('jam_in')
            ->get();
         $namabulan = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

       return view('dashboard.dashboard', compact('absensihariini', 'historibulanini', 'namabulan', 'bulanini', 'tahunini', 'rekapabsensi', 'leaderboard'));
    }
}
