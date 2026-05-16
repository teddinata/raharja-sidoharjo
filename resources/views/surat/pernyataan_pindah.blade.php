@extends('surat.layout')
@section('content')
@php $p = $surat->penduduk; $extra = $surat->data_tambahan ?? []; $ttd = $surat->ttd; $tgl = now()->translatedFormat('d F Y'); @endphp
<div class="judul"><h3>Surat Pernyataan Pindah</h3><p>Nomor: {{ $surat->nomor_surat }}</p></div>
<div class="isi">
    <p>Yang bertanda tangan di bawah ini:</p>
    <table class="data" style="margin-top:12px;">
        <tr><td class="label">Nama</td><td class="sep">:</td><td class="value">{{ $p->nama_lengkap }}</td></tr>
        <tr><td class="label">NIK</td><td class="sep">:</td><td class="value">{{ $p->nik ?? '-' }}</td></tr>
        <tr><td class="label">Alamat Asal</td><td class="sep">:</td><td class="value">{{ $p->pedukuhan ?? '-' }} RT {{ $p->rt_format }} RW {{ $p->rw_format }}, {{ $setting->nama_kelurahan }}, {{ $setting->nama_kapanewon }}, {{ $setting->nama_kabupaten }}</td></tr>
        <tr><td class="label">Alamat Tujuan</td><td class="sep">:</td><td class="value">{{ $extra['alamat_tujuan'] ?? '-' }}</td></tr>
    </table>
    <p style="margin-top:16px;">Dengan ini menyatakan bahwa saya benar-benar akan pindah ke alamat tersebut di atas dan tidak keberatan untuk dicoret dari daftar penduduk Kalurahan {{ $setting->nama_kelurahan }}.</p>
</div>
<div style="margin-top:32px; display:flex; justify-content:space-between;">
    <div style="text-align:center; width:45%;">
        <p>Yang membuat pernyataan,</p>
        <p style="margin-top:70px; font-weight:bold; text-decoration:underline;">{{ $p->nama_lengkap }}</p>
    </div>
    @include('surat._ttd', ['ttd' => $ttd, 'setting' => $setting])
</div> 
@endsection