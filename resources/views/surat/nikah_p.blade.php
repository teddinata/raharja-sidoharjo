@extends('surat.layout')
@section('content')
@php $p = $surat->penduduk; $extra = $surat->data_tambahan ?? []; $ttd = $surat->ttd; $tgl = now()->translatedFormat('d F Y'); @endphp
<div class="judul"><h3>Surat Pengantar Perkawinan</h3><p>Nomor: {{ $surat->nomor_surat }}</p></div>
<div class="isi">
    <p>Yang bertanda tangan di bawah ini, Lurah {{ $setting->nama_kelurahan }}, menerangkan bahwa:</p>
    <p style="margin-top:10px;"><strong>CALON ISTRI</strong></p>
    <table class="data">
        <tr><td class="label">Nama</td><td class="sep">:</td><td class="value">{{ $p->nama_lengkap }}</td></tr>
        <tr><td class="label">NIK</td><td class="sep">:</td><td class="value">{{ $p->nik ?? '-' }}</td></tr>
        <tr><td class="label">Tempat / Tanggal Lahir</td><td class="sep">:</td><td class="value">{{ $p->tempat_lahir ?? '-' }}, {{ $p->tanggal_lahir_format ?? '-' }}</td></tr>
        <tr><td class="label">Agama</td><td class="sep">:</td><td class="value">{{ $p->agama ?? '-' }}</td></tr>
        <tr><td class="label">Pekerjaan</td><td class="sep">:</td><td class="value">{{ $p->pekerjaan ?? '-' }}</td></tr>
        <tr><td class="label">Status Perkawinan</td><td class="sep">:</td><td class="value">{{ $p->status_perkawinan ?? '-' }}</td></tr>
        <tr><td class="label">Alamat</td><td class="sep">:</td><td class="value">{{ $p->pedukuhan ?? '-' }} RT {{ $p->rt_format }} RW {{ $p->rw_format }}, {{ $setting->nama_kelurahan }}, {{ $setting->nama_kapanewon }}, {{ $setting->nama_kabupaten }}</td></tr>
    </table>
    <p style="margin-top:14px;"><strong>CALON SUAMI</strong></p>
    <table class="data">
        <tr><td class="label">Nama</td><td class="sep">:</td><td class="value">{{ $extra['nama_calon_suami'] ?? '-' }}</td></tr>
        <tr><td class="label">NIK</td><td class="sep">:</td><td class="value">{{ $extra['nik_calon_suami'] ?? '-' }}</td></tr>
        <tr><td class="label">Tempat / Tanggal Lahir</td><td class="sep">:</td><td class="value">{{ $extra['tempat_lahir_suami'] ?? '-' }}, {{ isset($extra['tgl_lahir_suami']) ? \Carbon\Carbon::parse($extra['tgl_lahir_suami'])->translatedFormat('d F Y') : '-' }}</td></tr>
        <tr><td class="label">Pekerjaan</td><td class="sep">:</td><td class="value">{{ $extra['pekerjaan_suami'] ?? '-' }}</td></tr>
        <tr><td class="label">Alamat</td><td class="sep">:</td><td class="value">{{ $extra['alamat_suami'] ?? '-' }}</td></tr>
    </table>
    @if(!empty($extra['tanggal_akad']))<p style="margin-top:12px;">Rencana akad nikah: <strong>{{ \Carbon\Carbon::parse($extra['tanggal_akad'])->translatedFormat('d F Y') }}</strong></p>@endif
    <p style="margin-top:12px;">Bahwa yang bersangkutan bermaksud melangsungkan perkawinan dan memohon surat pengantar ke KUA Kapanewon {{ $setting->nama_kapanewon }}.</p>
</div>
<div class="penutup"><p>Demikian surat pengantar ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p></div>
@include('surat._berlaku', ['extra' => $extra, 'surat' => $surat])
@include('surat._ttd', ['ttd' => $ttd, 'setting' => $setting])
@endsection