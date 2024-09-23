<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function proseslogin(Request $request)
    {
        if(Auth::guard('anggota')->attempt(['nis' =>$request->nis, 'password'=>$request->password ])) {
         return redirect('/dashboard'); 
        }else {
            echo "Gagal Login";
        }
    }

    public function proseslogout() 
    {
        if(Auth::guard('anggota')->check()){
            Auth::guard('anggota')->logout();
           return redirect('/');
        }
    }
}
