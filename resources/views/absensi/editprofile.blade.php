@extends('layouts.absensi')

@section('header')

<!-- App Header -->
<div class="appHeader bg-primary text-light">
   <div class="left">
        <a href="javascript:;" class="headerButton goBack"> 
            <ion-icon name="chevron-back-outline"></ion-icon>
        </a>
    </div> <!-- Penutupan div yang benar -->
    <div class="pageTitle">Edit Profile</div>
    <div class="right"></div>
</div>
<!-- App Header -->
@endsection

@section('content')
<div class="row" style="margin-top: 4rem">
    <div class="col">
        @php
            $messagesuccess = Session::get('success');
            $messageerror = Session::get('error');
        @endphp
        
        @if ($messagesuccess)
            <div class="alert alert-success">
                {{ $messagesuccess }}
            </div>
        @endif

        @if ($messageerror)
            <div class="alert alert-danger"> <!-- Menggunakan alert-danger untuk error -->
                {{ $messageerror }}
            </div>
        @endif
    </div>
</div>

<form action="/absensi/{{ $anggota->nis }}/updateprofile" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row"> <!-- Menambahkan row untuk form -->
        <div class="col">
            <div class="form-group boxed">
                <div class="input-wrapper">
                    <input type="text" class="form-control" value="{{ $anggota->nama_lengkap }}" name="nama_lengkap" placeholder="Nama Lengkap" autocomplete="off">
                </div>
            </div>
            <div class="form-group boxed">
                <div class="input-wrapper">
                    <input type="text" class="form-control" value="{{ $anggota->no_hp }}" name="no_hp" placeholder="No. HP" autocomplete="off">
                </div>
            </div>
            <div class="form-group boxed">
                <div class="input-wrapper">
                    <input type="password" class="form-control" name="password" placeholder="Password" autocomplete="off">
                </div>
            </div>
            <div class="custom-file-upload" id="fileUpload1">
                <input type="file" name="foto" id="fileuploadInput" accept=".png, .jpg, .jpeg">
                <label for="fileuploadInput">
                    <span>
                        <strong>
                            <ion-icon name="cloud-upload-outline" role="img" class="md hydrated" aria-label="cloud upload outline"></ion-icon>
                            <i>Tap to Upload</i>
                        </strong>
                    </span>
                </label>
            </div>
            <div class="form-group boxed">
                <div class="input-wrapper">
                    <button type="submit" class="btn btn-primary btn-block">
                        <ion-icon name="refresh-outline"></ion-icon>
                        Update
                    </button>
                </div>
            </div>
        </div>
    </div> <!-- Menambahkan penutupan row untuk form -->
</form>
@endsection
