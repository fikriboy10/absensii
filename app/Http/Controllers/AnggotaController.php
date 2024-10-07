<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;

class AnggotaController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('anggota')->orderBy('nama_lengkap');
        
        // Jika ada input pencarian
        if (!empty($request->nama_anggota)) {
            $query->where('nama_lengkap', 'like', '%' . $request->nama_anggota . '%');
        }
    
        // Pagination dengan hasil pencarian
        $anggota = $query->paginate(10); 
    
        return view('anggota.index', compact('anggota'));
    }
    
    public function store(Request $request)
    {
        // Validate input
        $request->validate([
            'nis' => 'required|unique:anggota|numeric',
            'nama_lengkap' => 'required|string|max:255',
            'kelas_jurusan' => 'required|string|max:255',
            'no_hp' => 'required|numeric',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
    
        // Collect input data
        $nis = $request->nis;
        $nama_lengkap = $request->nama_lengkap;
        $kelas_jurusan = $request->kelas_jurusan;
        $no_hp = $request->no_hp;
        $password = Hash::make('12345678');
    
        // Handle photo upload
        $foto = null;
        if ($request->hasFile('foto')) {
            $foto = $nis . "." . $request->file('foto')->getClientOriginalExtension();
        }
    
        // Try to store the data
        try {
            $data = [
                'nis' => $nis,
                'nama_lengkap' => $nama_lengkap,
                'kelas_jurusan' => $kelas_jurusan,
                'no_hp' => $no_hp,
                'foto' => $foto,
                'password' => $password,
            ];
    
            $simpan = DB::table('anggota')->insert($data);
    
            if ($simpan) {
                if ($request->hasFile('foto')) {
                    $folderPath = "public/uploads/anggota/";
                    $request->file('foto')->storeAs($folderPath, $foto);
                }
                
                return Redirect::back()->with(['success' => 'Data Berhasil Disimpan']);
            }
        } catch (\Exception $e) {
            $message = $e->getCode() == 23000 ? "Data dengan Nis " . $nis . " Sudah Ada" : $e->getMessage();
            return Redirect::back()->with(['warning' => 'Data Gagal Disimpan. ' . $message]);
           
        }
    }
    
    public function edit(Request $request)
    {
        // Validate input
        $request->validate([
            'nis' => 'required|numeric',
        ]);
    
        // Retrieve data based on 'nis'
        $nis = $request->nis;   
        $anggota = DB::table('anggota')->where('nis', $nis)->first();
    
        if (!$anggota) {
            return Redirect::back()->with(['warning' => 'Data Anggota Tidak Ditemukan']);
        }
    
        return view('anggota.edit', compact('anggota'));
    }
    
    public function update($nis, Request $request)
    {
        $nis = $request->nis;
        $nama_lengkap = $request->nama_lengkap;
        $kelas_jurusan = $request->kelas_jurusan;
        $no_hp = $request->no_hp;  
        $password = Hash::make('12345678');
        $old_foto = $request->old_foto;
        if ($request->hasFile('foto')) {
            $foto = $nis . "." . $request->file('foto')->getClientOriginalExtension();
        } else {
            $foto = $old_foto;
        }

        try {
            $data =  [
                'nama_lengkap' => $nama_lengkap,
                'kelas_jurusan' => $kelas_jurusan,
                'no_hp' => $no_hp,
                'foto' => $foto,
                'password' => $password,
            ];
            $update = DB::table('anggota')->where('nis', $nis)->update($data);
            if ($update) {
                if ($request->hasFile('foto')) {
                    $folderPath = "public/uploads/anggota/";
                    $folderPathOld = "public/uploads/anggota/" . $old_foto;
                    Storage::delete($folderPathOld);
                    $request->file('foto')->storeAs($folderPath, $foto);
                }
                return Redirect::back()->with(['success' => 'Data Berhasil Update']);
            }
        } catch (\Exception $e) {
            //dd($e->message);
            return Redirect::back()->with(['warning' => 'Data Gagal Diupdate']);
        }
    }

    public function delete($nis)
    {
        $delete = DB::table('anggota')->where('nis', $nis)->first();
        if ($delete) {
           
            $folderPath = "public/uploads/anggota/";
            $folderPathOld = "public/uploads/anggota/" . $delete->foto;
            Storage::delete($folderPathOld);
            $delete = DB::table('anggota')->where('nis', $nis)->delete();
            return Redirect::back()->with(['success' => 'Data Berhasil Dihapus']);
        } else {
            return Redirect::back()->with(['warning' => 'Data Gagal Dihapus']);
        }
    }
}
