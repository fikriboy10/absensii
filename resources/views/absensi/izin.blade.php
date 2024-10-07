@extends('layouts.absensi')
@section('header')

<!-- App Header -->
<div class="appHeader bg-primary text-light">
   <div class="left">
        <a href="javascript:;" class="headerButton goBack"> 
            <ion-icon name="chevron-back-outline"></ion-icon>
        </a>
    </div> <!-- Penutupan div .left yang benar -->
    <div class="pageTitle">Data Izin / Sakit</div>
    <div class="right"></div>
</div>
<!-- App Header -->
@endsection

@section('content')
<div class="row" style="margin-top: 70px">
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
<div class="row">
    <div class="col">
        <ul class="listview image-listview">
            @foreach ($dataizin as $d)
                <li>
                    <div class="item">
                        <div>
                            <b>{{ $d->nis}}|{{ date('d-m-Y', strtotime($d->tgl_izin)) }}|{{ $d->status== "s" ? "Sakit" : "Izin" }} </b> <br> <!-- Gunakan tgl_izin untuk tanggal -->
                            <small class="text-muted">{{ $d->keterangan }}</small>
                        </div>
                        <div class="row" style="margin-left: 120px">
                            <div class="col">
                        @if ($d->status_approved == 0)
                            <span class="badge bg-warning">Harap Tunggu</span>
                        @elseif ($d->status_approved == 1)
                            <span class="badge bg-success">Disetujui</span>
                        @elseif ($d->status_approved == 2)
                            <span class="badge bg-danger">Ditolak</span>
                        @endif
                            </div>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</div>

    <div class="fab-button bottom-right" style="margin-bottom: 70px">
        <a href="/absensi/buatizin" class="fab">
            <ion-icon name="add-outline"></ion-icon>
        </a>
    </div>
@endsection