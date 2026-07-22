@extends('surat.layout')
@section('content')
@php $p = $surat->penduduk; $ttd = $surat->ttd; $tgl = now()->translatedFormat('d F Y'); @endphp
<div class="judul"><h3>Surat Pengantar Pembuatan KIA</h3><p>Nomor: {{ $surat->nomor_surat }}</p></div>
<div class="isi">
    <p>Yang bertanda tangan di bawah ini, Lurah {{ $setting->nama_kelurahan }}, dengan ini mengantarkan permohonan Kartu Identitas Anak (KIA) atas nama:</p>
    <table class="data" style="margin-top:12px;">
        <tr><td class="label">Nama Anak</td><td class="sep">:</td><td class="value">{{ $p->nama_lengkap }}</td></tr>
        <tr><td class="label">NIK</td><td class="sep">:</td><td class="value">{{ $p->nik ?? '-' }}</td></tr>
        <tr><td class="label">Tempat / Tanggal Lahir</td><td class="sep">:</td><td class="value">{{ $p->tempat_lahir ?? '-' }}, {{ $p->tanggal_lahir_format ?? '-' }}</td></tr>
        <tr><td class="label">Umur</td><td class="sep">:</td><td class="value">{{ $p->umur ?? '-' }} tahun</td></tr>
        <tr><td class="label">Nama Ayah</td><td class="sep">:</td><td class="value">{{ $p->nama_ayah ?? '-' }}</td></tr>
        <tr><td class="label">Nama Ibu</td><td class="sep">:</td><td class="value">{{ $p->nama_ibu ?? '-' }}</td></tr>
        <tr><td class="label">Alamat</td><td class="sep">:</td><td class="value">{{ $p->pedukuhan ?? '-' }} RT {{ $p->rt_format }} RW {{ $p->rw_format }}, {{ $setting->nama_kelurahan }}, {{ $setting->nama_kapanewon }}, {{ $setting->nama_kabupaten }}</td></tr>
    </table>
</div>
<div class="penutup"><p>Demikian surat pengantar ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p></div>
@include('surat._berlaku', ['extra' => $extra])
@include('surat._ttd', ['ttd' => $ttd, 'setting' => $setting])
@endsection