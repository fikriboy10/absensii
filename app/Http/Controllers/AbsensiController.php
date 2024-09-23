<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class AbsensiController extends Controller
{
    public function create() 
    {
        $hariini = date("Y-m-d");
        
        // Cek apakah pengguna terautentikasi
        if (!Auth::guard('anggota')->check()) {
            return redirect()->route('login')->with('error', 'Anda harus login terlebih dahulu.');
        }

        $nis = Auth::guard('anggota')->user()->nis;
        $cek = DB::table('absensi')->where('tgl_absensi', $hariini)->where('nis', $nis)->count();
        
        return view('absensi.create', compact('cek'));
    }

    public function store(Request $request) 
    {
        // Cek apakah pengguna terautentikasi
        if (!Auth::guard('anggota')->check()) {
            return response()->json(['error' => 'Anda harus login terlebih dahulu.'], 401);
        }

        $nis = Auth::guard('anggota')->user()->nis;
        $tgl_absensi = date("Y-m-d");
        $jam = date("H:i:s");
        $latitudelokasi = -6.981115460523293 ; 
        $longitudelokasi = 107.67425241962124;
        $lokasi = $request->lokasi;
        $lokasiuser = explode(",", $lokasi);
        $latitudeuser = $lokasiuser[0];
        $longitudeuser = $lokasiuser[1];

        $jarak = $this->distance($latitudelokasi, $longitudelokasi, $latitudeuser, $longitudeuser);
        $radius = round($jarak["meters"]); 
        $image = $request->image; 

        // Path penyimpanan di public disk
        $folderPath = "uploads/absensi/";
        $formatname = $nis . "-" . $tgl_absensi;
        $image_parts = explode(";base64,", $image);

        // Memastikan data gambar base64 valid
        if (count($image_parts) == 2) {
            $image_base64 = base64_decode($image_parts[1]);
        } else {
            return response()->json(['error' => 'Data gambar tidak valid'], 400);
        }

        // Cek apakah absensi sudah ada
        $cek = DB::table('absensi')->where('tgl_absensi', $tgl_absensi)->where('nis', $nis)->count();
        if ($radius > 100) {
            echo "error|Maaf Anda Sedang Berada Di Luar Jangkauan";
        } else {
        if ($cek > 0) {
            // Nama file unik untuk foto keluar
            $filename_out = $formatname . "-out.png"; 
            $filePath_out = $folderPath . $filename_out;

            // Update data jam pulang
            $data_pulang = [
                'jam_out' => $jam,
                'foto_out' => $filename_out,
                'lokasi_out' => $lokasi
            ];
            $update = DB::table('absensi')->where('tgl_absensi', $tgl_absensi)->where('nis', $nis)->update($data_pulang);
            
            if ($update) {
                echo "success|Terimakasih, Hati Hati Di Jalan|out";
                Storage::disk('public')->put($filePath_out, $image_base64);
            } else {
                echo "error|Maaf Gagal Absen|out";
            }
        } else {
            // Nama file unik untuk foto masuk
            $filename_in = $formatname . "-in.png"; 
            $filePath_in = $folderPath . $filename_in;

            // Simpan data jam masuk
            $data = [
                'nis' => $nis,
                'tgl_absensi' => $tgl_absensi,
                'jam_in' => $jam,
                'foto_in' => $filename_in,
                'lokasi_in' => $lokasi
            ];
            $simpan = DB::table('absensi')->insert($data);
            
            if ($simpan) {
                echo "success|Terimakasih, Selamat Beraktivitas|in";
                Storage::disk('public')->put($filePath_in, $image_base64);
            } else {
                echo "error|Maaf Gagal Absen|in";
            }
        }
        }
    }



        //Menghitung Jarak
        function distance($lat1, $lon1, $lat2, $lon2)
        {
            $theta = $lon1 - $lon2;
            $miles = (sin(deg2rad($lat1)) * sin(deg2rad($lat2))) + (cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)));
            $miles = acos($miles);
            $miles = rad2deg($miles);
            $miles = $miles * 60 * 1.1515;
            $feet = $miles * 5280;
            $yards = $feet / 3;
            $kilometers = $miles * 1.609344;
            $meters = $kilometers * 1000;
            return compact('meters');
        }

        public function editprofile() 
        {
            $nis = Auth::guard('anggota')->user()->nis;
            $anggota = DB::table('anggota')->where('nis', $nis)->first();
            return view('absensi.editprofile', compact('anggota'));
        }

        public function updateprofile(Request $request)
        {
            $nis = Auth::guard('anggota')->user()->nis;
            $nama_lengkap = $request->nama_lengkap;
            $no_hp = $request->no_hp;
            $password = Hash::make($request->password);
            $anggota = DB::table('anggota')->where('nis', $nis)->first();

            if($request->hasFile('foto')){
                $foto = $nis . "." . $request->file('foto')->getClientOriginalExtension();
            } else {
                $foto = $anggota->foto;
            }
            if  (empty($request->password)) {
                $data = [
                    'nama_lengkap' => $nama_lengkap,
                    'no_hp' => $no_hp,
                    'foto' => $foto
                ];              
            } else {
                $data = [
                    'nama_lengkap' => $nama_lengkap,
                    'no_hp' => $no_hp,
                    'password' => $password,
                    'foto' => $foto
                ]; 
            }

            $update = DB::table('anggota')->where('nis', $nis)->update($data);
            if ($update) {
                if ($request->hasFile('foto')) {
                    $folderPath = 'public/uploads/anggota';
                    $request->file('foto')->storeAs($folderPath, $foto);
                }
                return Redirect::back()->with(['success' => 'Data Berhasil Di Update']);
            } else {
                return Redirect::back()>with(['error' => 'Data Gagal Di Update']);
            }
        }

        public function histori()
        {
            $namabulan = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
            return view('absensi.histori', compact('namabulan'));
        }

        public function gethistori(Request $request) 
        {
            $bulan = $request->bulan;
            $tahun = $request->tahun;
            $nis = Auth::guard('anggota')->user()->nis;

            $histori = DB::table('absensi')
            ->whereRaw('MONTH(tgl_absensi)="' . $bulan . '"')
            ->whereRaw('YEAR(tgl_absensi)="' . $tahun . '"')
            ->where('nis', $nis)
            ->orderBy('tgl_absensi')
            ->get();

            return view('absensi.gethistori', compact('histori'));
        }

        public function izin()
        {
            $nama_lengkap = Auth::guard('anggota')->user()->nama_lengkap;
            $dataizin = DB::table('pengajuan_izin')->where('nama_lengkap', $nama_lengkap)->get();
            return view('absensi.izin', compact('dataizin'));
        }

        public function buatizin()
        {         
            return view('absensi.buatizin');
        }

        public function storeizin(Request $request) 
        {
            $nama_lengkap = Auth::guard('anggota')->user()->nama_lengkap;
            $tgl_izin = $request->tgl_izin;
            $status = $request->status;
            $keterangan = $request->keterangan;

            $data = [
                'nama_lengkap' => $nama_lengkap,
                'tgl_izin' => $tgl_izin,
                'status' => $status,
                'keterangan' => $keterangan
            ];

            $simpan = DB::table('pengajuan_izin')->insert($data);

            if ($simpan) {
                return redirect('/absensi/izin')->with(['success'=>'Data Berhasil Disimpan']);
            } else {
                return redirect('/absensi/izin')->with(['error'=>'Data Gagal Disimpan']);
            }
        }
}
