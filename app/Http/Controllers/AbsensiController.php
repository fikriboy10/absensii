<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Pengajuanizin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;


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
        $lok_absen = DB::table('konfigurasi_lokasi')->where('id', 1)->first();
        
        return view('absensi.create', compact('cek', 'lok_absen'));
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
        $lok_absen = DB::table('konfigurasi_lokasi')->where('id', 1)->first();
        $lok = explode(",", $lok_absen->lokasi_absen);
        $latitudelokasi = $lok [0]; 
        $longitudelokasi = $lok [1];
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
        if ($radius > $lok_absen->radius) {
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
            $nis = Auth::guard('anggota')->user()->nis; // Mendapatkan nama lengkap anggota yang sedang login
            $dataizin = DB::table('pengajuan_izin')->where('nis', $nis)->get(); // Mengambil data izin berdasarkan nama lengkap
            return view('absensi.izin', compact('dataizin')); // Mengirim data izin ke tampilan
        }

        public function buatizin()
        {         
            return view('absensi.buatizin'); // Mengirim tampilan untuk membuat izin
        }

        public function storeizin(Request $request) 
        {
            $nis = Auth::guard('anggota')->user()->nis; // Mendapatkan nama lengkap anggota yang sedang login
            $tgl_izin = $request->tgl_izin; // Mengambil tanggal izin dari request
            $status = $request->status; // Mengambil status dari request
            $keterangan = $request->keterangan; // Mengambil keterangan dari request

            $data = [
                'nis' => $nis,
                'tgl_izin' => $tgl_izin,
                'status' => $status,
                'keterangan' => $keterangan
            ];

            $simpan = DB::table('pengajuan_izin')->insert($data); // Menyimpan data izin ke tabel pengajuan_izin

            if ($simpan) {
                return redirect('/absensi/izin')->with(['success'=>'Data Berhasil Disimpan']); // Redirect dengan pesan sukses
            } else {
                return redirect('/absensi/izin')->with(['error'=>'Data Gagal Disimpan']); // Redirect dengan pesan error
            }
        }
        public function monitoring()
        {
            return view('absensi.monitoring');
        }
    
        public function getabsensi(Request $request)
        {
            $tanggal = $request->tanggal;
            $absensi = DB::table('absensi')
                ->select('absensi.*', 'nama_lengkap')
                ->join('anggota', 'absensi.nis', '=', 'anggota.nis')
                ->where('tgl_absensi', $tanggal)
                ->get();
    
            return view('absensi.getabsensi', compact('absensi'));
        }
    
        public function tampilkanpeta(Request $request)
        {
            $id = $request->id;
            $absensi = DB::table('absensi')->where('id', $id)
                ->join('anggota', 'absensi.nis', '=', 'anggota.nis')
                ->first();
            return view('absensi.showmap', compact('absensi'));
        }

        public function laporan()
    {
        $namabulan = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        $anggota = DB::table('anggota')->orderBy('nama_lengkap')->get();
        return view('absensi.laporan', compact('namabulan', 'anggota'));
    }

    public function cetaklaporan(Request $request)
    {
        $nis = $request->nis;
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $namabulan = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        $anggota = DB::table('anggota')->where('nis', $nis)
            
            ->first();

        $absensi = DB::table('absensi')
            ->where('nis', $nis)
            ->whereRaw('MONTH(tgl_absensi)="' . $bulan . '"')
            ->whereRaw('YEAR(tgl_absensi)="' . $tahun . '"')
            ->orderBy('tgl_absensi')
            ->get();

        if (isset($_POST['exportexcel'])) {
            $time = date("d-M-Y H:i:s");
            // Fungsi header dengan mengirimkan raw data excel
            header("Content-type: application/vnd-ms-excel");
            // Mendefinisikan nama file ekspor "hasil-export.xls"
            header("Content-Disposition: attachment; filename=Laporan Absensi Anggota $time.xls");
            return view('absensi.cetaklaporanexcel', compact('bulan', 'tahun', 'namabulan', 'anggota', 'absensi'));
        }
        return view('absensi.cetaklaporan', compact('bulan', 'tahun', 'namabulan', 'anggota', 'absensi'));
    }

    public function rekap()
    {
        $namabulan = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        return view('absensi.rekap', compact('namabulan'));
    }

    public function cetakrekap(Request $request)
    {
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $namabulan = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        $rekap = DB::table('absensi')
            ->selectRaw('absensi.nis,nama_lengkap,
                MAX(IF(DAY(tgl_absensi) = 1,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_1,
                MAX(IF(DAY(tgl_absensi) = 2,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_2,
                MAX(IF(DAY(tgl_absensi) = 3,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_3,
                MAX(IF(DAY(tgl_absensi) = 4,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_4,
                MAX(IF(DAY(tgl_absensi) = 5,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_5,
                MAX(IF(DAY(tgl_absensi) = 6,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_6,
                MAX(IF(DAY(tgl_absensi) = 7,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_7,
                MAX(IF(DAY(tgl_absensi) = 8,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_8,
                MAX(IF(DAY(tgl_absensi) = 9,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_9,
                MAX(IF(DAY(tgl_absensi) = 10,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_10,
                MAX(IF(DAY(tgl_absensi) = 11,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_11,
                MAX(IF(DAY(tgl_absensi) = 12,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_12,
                MAX(IF(DAY(tgl_absensi) = 13,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_13,
                MAX(IF(DAY(tgl_absensi) = 14,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_14,
                MAX(IF(DAY(tgl_absensi) = 15,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_15,
                MAX(IF(DAY(tgl_absensi) = 16,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_16,
                MAX(IF(DAY(tgl_absensi) = 17,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_17,
                MAX(IF(DAY(tgl_absensi) = 18,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_18,
                MAX(IF(DAY(tgl_absensi) = 19,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_19,
                MAX(IF(DAY(tgl_absensi) = 20,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_20,
                MAX(IF(DAY(tgl_absensi) = 21,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_21,
                MAX(IF(DAY(tgl_absensi) = 22,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_22,
                MAX(IF(DAY(tgl_absensi) = 23,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_23,
                MAX(IF(DAY(tgl_absensi) = 24,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_24,
                MAX(IF(DAY(tgl_absensi) = 25,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_25,
                MAX(IF(DAY(tgl_absensi) = 26,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_26,
                MAX(IF(DAY(tgl_absensi) = 27,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_27,
                MAX(IF(DAY(tgl_absensi) = 28,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_28,
                MAX(IF(DAY(tgl_absensi) = 29,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_29,
                MAX(IF(DAY(tgl_absensi) = 30,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_30,
                MAX(IF(DAY(tgl_absensi) = 31,CONCAT(jam_in,"-",IFNULL(jam_out,"00:00:00")),"")) as tgl_31')
            ->join('anggota', 'absensi.nis', '=', 'anggota.nis')
            ->whereRaw('MONTH(tgl_absensi)="' . $bulan . '"')
            ->whereRaw('YEAR(tgl_absensi)="' . $tahun . '"')
            ->groupByRaw('absensi.nis,nama_lengkap')
            ->get();

        if (isset($_POST['exportexcel'])) {
            $time = date("d-M-Y H:i:s");
            // Fungsi header dengan mengirimkan raw data excel
            header("Content-type: application/vnd-ms-excel");
            // Mendefinisikan nama file ekspor "hasil-export.xls"
            header("Content-Disposition: attachment; filename=Rekap Absensi Anggota $time.xls");
        }
        return view('absensi.cetakrekap', compact('bulan', 'tahun', 'namabulan', 'rekap'));
    }

    public function izinsakit(Request $request)
    {

        $query = Pengajuanizin::query();
        $query->select('id', 'tgl_izin', 'pengajuan_izin.nis', 'nama_lengkap', 'kelas_jurusan', 'status', 'status_approved', 'keterangan');
        $query->join('anggota', 'pengajuan_izin.nis', '=', 'anggota.nis');
        if (!empty($request->dari) && !empty($request->sampai)) {
            $query->whereBetween('tgl_izin', [$request->dari, $request->sampai]);
        }

        if (!empty($request->nis)) {
            $query->where('pengajuan_izin.nis', $request->nis);
        }

        if (!empty($request->nama_lengkap)) {
            $query->where('nama_lengkap', 'like', '%' . $request->nama_lengkap . '%');
        }

        if ($request->status_approved === '0' || $request->status_approved === '1' || $request->status_approved === '2') {
            $query->where('status_approved', $request->status_approved);
        }
        $query->orderBy('tgl_izin', 'desc');
        $izinsakit = $query->paginate(3);
        $izinsakit->appends($request->all());
        return view('absensi.izinsakit', compact('izinsakit'));
    }

    public function approveizinsakit(Request $request)
    {
        $status_approved = $request->status_approved;
        $id_izinsakit_form = $request->id_izinsakit_form;
        $update = DB::table('pengajuan_izin')->where('id', $id_izinsakit_form)->update([
            'status_approved' => $status_approved
        ]);
        if ($update) {
            return Redirect::back()->with(['success' => 'Data Berhasil Di Update']);
        } else {
            return Redirect::back()->with(['warning' => 'Data Gagal Di Update']);
        }
    }

    public function batalkanizinsakit($id)
    {
        $update = DB::table('pengajuan_izin')->where('id', $id)->update([
            'status_approved' => 0
        ]);
        if ($update) {
            return Redirect::back()->with(['success' => 'Data Berhasil Di Update']);
        } else {
            return Redirect::back()->with(['warning' => 'Data Gagal Di Update']);
        }
    }
    public function cekpengajuanizin(Request $request)
    {
        $tgl_izin = $request->tgl_izin;
        $nis = Auth::guard('anggota')->user()->nis;

        $cek = DB::table('pengajuan_izin')->where('nis', $nis)->where('tgl_izin', $tgl_izin)->count();
        return $cek;
    }
}
