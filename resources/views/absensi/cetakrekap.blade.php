<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>A4</title>

    <!-- Normalize or reset CSS with your favorite library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/7.0.0/normalize.min.css">

    <!-- Load paper.css for happy printing -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/paper-css/0.4.1/paper.css">

    <!-- Set page size here: A5, A4 or A3 -->
    <!-- Set also "landscape" if you need -->
    <style>
        @page {
            size: F4
        }

        #title {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 18px;
            font-weight: bold;
        }

        /* Style umum untuk body */
        body {
            font-family: 'Arial', sans-serif; /* Font yang simpel dan profesional */
            margin: 50px; /* Memberi ruang di sekitar dokumen */
            color: #333; /* Warna teks */
        }

        /* Styling untuk logo dan header */
        .logo {
            width: 80px; /* Atur ukuran logo agar lebih kecil */
            display: block;
            margin-left: auto;
            margin-right: auto; /* Menempatkan logo di tengah */
            margin-bottom: 20px; /* Menambah jarak di bawah logo */
        }

        .header-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        /* Atur tabel data karyawan */
        .tabeldatakaryawan {
            width: 100%;
            margin-top: 40px;
            font-size: 12px; /* Ukuran font lebih kecil untuk tabel */
        }

        .tabeldatakaryawan tr td {
            padding: 8px 10px; /* Padding yang lebih besar untuk kenyamanan */
            text-align: left; /* Rata kiri untuk data */
        }

        /* Lebih spesifik untuk tabel */
        table.tabelpresensi {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Efek bayangan lembut */
        }

        table.tabelpresensi th, table.tabelpresensi td {
            border: 1px solid #ddd;
            padding: 12px; /* Tambahkan padding agar tidak mepet */
            text-align: center;
            font-size: 12px;
            color: #555;
        }

        table.tabelpresensi tr:hover {
            background-color: #f9f9f9;
        }

        table.tabelpresensi th {
            background-color: #f0f0f0;
            font-weight: bold;
        }


                /* Style untuk bagian tanda tangan */
                .ttd-container {
                    width: 100%;
                    margin-top: 40px;
                    display: flex;
                    justify-content: space-between; /* Posisi tanda tangan di kanan dan kiri */
                }

                .ttd {
                    text-align: center;
                    font-size: 14px;
                    color: #333;
                }

                /* Style untuk keterangan tanggal */
                .tanggal {
                    text-align: right;
                    margin-top: 20px;
                    font-size: 12px;
                    color: #333;
                }

                /* Style untuk footer */
                .footer {
                    margin-top: 40px;
                    text-align: center;
                    font-size: 12px;
                    color: #555;
                }




    </style>
</head>

<!-- Set "A5", "A4" or "A3" for class name -->
<!-- Set also "landscape" if you need -->
<body class="A4 landscape">
    <?php
    function selisih($jam_masuk, $jam_keluar)
    {
        list($h, $m, $s) = explode(":", $jam_masuk);
        $dtAwal = mktime($h, $m, $s, "1", "1", "1");
        list($h, $m, $s) = explode(":", $jam_keluar);
        $dtAkhir = mktime($h, $m, $s, "1", "1", "1");
        $dtSelisih = $dtAkhir - $dtAwal;
        $totalmenit = $dtSelisih / 60;
        $jam = explode(".", $totalmenit / 60);
        $sisamenit = ($totalmenit / 60) - $jam[0];
        $sisamenit2 = $sisamenit * 60;
        $jml_jam = $jam[0];
        return $jml_jam . ":" . round($sisamenit2);
    }
    ?>
    <!-- Each sheet element should have the class "sheet" -->
    <!-- "padding-**mm" is optional: you can set 10, 15, 20 or 25 -->
    <section class="sheet padding-10mm">

        <table style="width: 100%">
            <tr>
                <td>
                    <img src="{{ asset('assets/img/1.png') }}" width="70" height="70" alt="">
                </td>
                <td>
                    <h5 id="title">
                        LAPORAN ABSENSI ANGGOTA PERIODE {{ strtoupper($namabulan[$bulan]) }} {{ $tahun }} PMR WIRA SMKN 1 KAWALI
                    </h5>
                    <span><i>Jln. H. Dahlan No. 75, Kecamatan Sindangrasa, Kabupaten Ciamis</i></span>
                </td>
            </tr>
        </table>
        <table width="100%" border="1" class="tabelabsensi">
            <tr>
                <th rowspan="2">Nis</th>
                <th rowspan="2">Nama Anggota</th>
                <th colspan="31">Tanggal</th>
                <th rowspan="2">Total Hadir</th>
                <th rowspan="2">Total Terlambat</th>
            </tr>
            <tr>
                <?php
                for($i=1; $i<=31; $i++){
                ?>
                <th>{{ $i }}</th>
                <?php
                }
                ?>

            </tr>
            @foreach ($rekap as $d)
            <tr>
                <td>{{ $d->nis }}</td>
                <td>{{ $d->nama_lengkap }}</td>

                <?php
                $totalhadir = 0;
                $totalterlambat = 0;
                for($i=1; $i<=31; $i++){
                    $tgl = "tgl_".$i;
                    if(empty($d->$tgl)){
                        $hadir = ['',''];
                        $totalhadir += 0;
                    }else{
                        $hadir = explode("-",$d->$tgl);
                        $totalhadir += 1;
                        if($hadir[0] > "08:00:00"){
                            $totalterlambat +=1;
                        }
                    }
                ?>

                <td>
                    <span style="color:{{ $hadir[0]>"08:00:00" ? "red" : "" }}">{{ $hadir[0] }}</span><br>
                    <span style="color:{{ $hadir[1]<"12:00:00" ? "red" : "" }}">{{ $hadir[0] }}</span>
                </td>

                <?php
                }
                ?>
                <td>{{ $totalhadir }}</td>
                <td>{{ $totalterlambat }}</td>
            </tr>
            @endforeach
        </table>

        <table width="100%" style="margin-top:100px">
            <tr>
                <td></td>
                <td style="text-align: right">Tasikmalaya, {{ date('d-m-Y') }}</td>
            </tr>
            <tr>
                <td style="text-align: center; vertical-align:bottom" height="100px">
                    <u>Qiana Aqila</u><br>
                    <i><b>HRD Manager</b></i>
                </td>
                <td style="text-align: center; vertical-align:bottom">
                    <u>Daffa</u><br>
                    <i><b>Direktur</b></i>
                </td>
            </tr>
        </table>
    </section>

</body>

</html>
