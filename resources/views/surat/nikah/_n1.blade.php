{{-- Model N1 — Surat Pengantar Perkawinan (dari Lurah/Kepala Desa). --}}
@include('surat.nikah._model', ['model' => 'N1'])

<table class="rincian">
    <tr><td class="nomor"></td><td class="label">KAPANEWON</td><td class="sep">:</td><td class="value">{{ strtoupper($setting->nama_kapanewon) }}</td></tr>
    <tr><td class="nomor"></td><td class="label">KABUPATEN/KOTA</td><td class="sep">:</td><td class="value">{{ strtoupper($setting->nama_kabupaten) }}</td></tr>
</table>

<p class="judul-formulir" style="margin-top:8px;">SURAT PENGANTAR PERKAWINAN</p>
<p class="nomor-formulir">Nomor, {{ $surat->nomor_surat }}</p>

<p class="alinea">Yang bertanda tangan di bawah ini menerangkan dengan sesungguhnya, bahwa :</p>

@php
    $barisPemohon = [
        ['1.',  'Nama',                           $pemohon['nama']],
        ['2.',  'Nomor Induk Kependudukan (NIK)', $pemohon['nik']],
        ['3.',  'Jenis Kelamin',                  $pemohon['jenis_kelamin']],
        ['4.',  'Tempat dan tanggal lahir',       $pemohon['ttl']],
        ['5.',  'Kewarganegaraan',                $pemohon['kewarganegaraan']],
        ['6.',  'Agama',                          $pemohon['agama']],
        ['7.',  'Pekerjaan',                      $pemohon['pekerjaan']],
        ['8.',  'Alamat',                         $pemohon['alamat']],
    ];

    $barisOrangTua = [
        'Nama lengkap dan alias',
        'Nomor Induk Kependudukan (NIK)',
        'Tempat dan tanggal lahir',
        'Kewarganegaraan',
        'Agama',
        'Pekerjaan',
        'Alamat',
    ];

    $nilaiAyah = [$ayah['nama'], $ayah['nik'], $ayah['ttl'], $ayah['kewarganegaraan'], $ayah['agama'], $ayah['pekerjaan'], $ayah['alamat']];
    $nilaiIbu  = [$ibu['nama'],  $ibu['nik'],  $ibu['ttl'],  $ibu['kewarganegaraan'],  $ibu['agama'],  $ibu['pekerjaan'],  $ibu['alamat']];
@endphp

<table class="rincian">
    @foreach($barisPemohon as [$no, $label, $nilai])
        <tr><td class="nomor">{{ $no }}</td><td class="label">{{ $label }}</td><td class="sep">:</td><td class="value">{{ $nilai }}</td></tr>
    @endforeach
    <tr><td class="nomor">9.</td><td class="label">Status Perkawinan</td><td class="sep"></td><td class="value"></td></tr>
    <tr>
        <td class="nomor"></td>
        <td class="label">&nbsp;&nbsp;&nbsp;{{ $pria ? 'Laki-laki : Perjaka, Duda' : 'Perempuan : Perawan, Janda' }}</td>
        <td class="sep">:</td>
        <td class="value">{{ $pemohon['status'] }}</td>
    </tr>
    <tr>
        <td class="nomor">10.</td>
        <td class="label">Nama {{ $pria ? 'Istri' : 'Suami' }} terdahulu</td>
        <td class="sep">:</td>
        <td class="value">{{ $pemohon['terdahulu'] }}</td>
    </tr>
</table>

<p class="alinea">Adalah benar anak dari perkawinan seorang pria :</p>
<table class="rincian">
    @foreach($barisOrangTua as $i => $label)
        <tr><td class="nomor"></td><td class="label">{{ $label }}</td><td class="sep">:</td><td class="value">{{ $nilaiAyah[$i] }}</td></tr>
    @endforeach
</table>

<p class="alinea">dengan seorang wanita :</p>
<table class="rincian">
    @foreach($barisOrangTua as $i => $label)
        <tr><td class="nomor"></td><td class="label">{{ $label }}</td><td class="sep">:</td><td class="value">{{ $nilaiIbu[$i] }}</td></tr>
    @endforeach
</table>

<p class="alinea" style="margin-top:6px;">Demikian, surat pengantar ini dibuat dengan mengingat sumpah jabatan dan untuk digunakan sebagaimana mestinya.</p>

<table class="ttd-n" style="margin-top:8px;">
    <tr>
        <td style="width:55%;"></td>
        <td style="width:45%;">Kepala Desa/Lurah</td>
    </tr>
    <tr>
        <td style="width:55%;"></td>
        <td style="width:45%;"><p class="ttd-n-nama">{{ $penanda['nama'] }}</p></td>
    </tr>
</table>
