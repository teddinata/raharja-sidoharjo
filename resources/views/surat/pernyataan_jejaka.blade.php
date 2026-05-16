@extends('surat.layout')
@section('content')
@php $p = $surat->penduduk; $ttd = $surat->ttd; $tgl = now()->translatedFormat('d F Y'); @endphp
<div class="judul"><h3>Surat Pernyataan Jejaka / Belum Pernah Menikah</h3><p>Nomor: {{ $surat->nomor_surat }}</p></div>
<div class="isi">
    <p>Yang bertanda tangan di bawah ini:</p>
    <table class="data" style="margin-top:12px;">
        <tr><td class="label">Nama</td><td class="sep">:</td><td class="value">{{ $p->nama_lengkap }}</td></tr>
        <tr><td class="label">NIK</td><td class="sep">:</td><td class="value">{{ $p->nik ?? '-' }}</td></tr>
        <tr><td class="label">Tempat / Tanggal Lahir</td><td class="sep">:</td><td class="value">{{ $p->tempat_lahir ?? '-' }}, {{ $p->tanggal_lahir_format ?? '-' }}</td></tr>
        <tr><td class="label">Agama</td><td class="sep">:</td><td class="value">{{ $p->agama ?? '-' }}</td></tr>
        <tr><td class="label">Pekerjaan</td><td class="sep">:</td><td class="value">{{ $p->pekerjaan ?? '-' }}</td></tr>
        <tr><td class="label">Alamat</td><td class="sep">:</td><td class="value">{{ $p->pedukuhan ?? '-' }} RT {{ $p->rt_format }} RW {{ $p->rw_format }}, {{ $setting->nama_kelurahan }}, {{ $setting->nama_kapanewon }}, {{ $setting->nama_kabupaten }}</td></tr>
    </table>
    <p style="margin-top:16px;">Dengan ini menyatakan dengan sesungguhnya bahwa saya <strong>belum pernah menikah / berstatus jejaka</strong> dan tidak sedang terikat hubungan perkawinan dengan siapapun.</p>
    <p style="margin-top:8px;">Pernyataan ini saya buat dengan sebenarnya dan apabila dikemudian hari terbukti tidak benar, saya bersedia menerima segala akibat hukum yang berlaku.</p>
    <p style="margin-top:8px;">Demikian pernyataan ini dibuat dan diketahui oleh Lurah {{ $setting->nama_kelurahan }}.</p>
</div>
<div style="margin-top:32px; display:flex; justify-content:space-between;">
    <div style="text-align:center; width:45%;">
        <p>Yang membuat pernyataan,</p>
        <p style="margin-top:70px; font-weight:bold; text-decoration:underline;">{{ $p->nama_lengkap }}</p>
    </div>
    @include('surat._ttd', ['ttd' => $ttd, 'setting' => $setting])
</div>
@endsection