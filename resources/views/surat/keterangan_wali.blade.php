@extends('surat.layout')
@section('content')
@php $p = $surat->penduduk; $extra = $surat->data_tambahan ?? []; $ttd = $surat->ttd; $tgl = now()->translatedFormat('d F Y'); @endphp
<div class="judul"><h3>Surat Keterangan Wali Nikah</h3><p>Nomor: {{ $surat->nomor_surat }}</p></div>
<div class="isi">
    <p>Yang bertanda tangan di bawah ini, Lurah {{ $setting->nama_kelurahan }}, Kapanewon {{ $setting->nama_kapanewon }}, Kabupaten {{ $setting->nama_kabupaten }}, dengan ini menerangkan bahwa:</p>
    <table class="data" style="margin-top:12px;">
        <tr><td class="label">Nama Wali</td><td class="sep">:</td><td class="value">{{ $extra['nama_wali'] ?? '-' }}</td></tr>
        <tr><td class="label">Hubungan</td><td class="sep">:</td><td class="value">{{ $extra['hubungan_wali'] ?? '-' }}</td></tr>
        <tr><td class="label">Alamat</td><td class="sep">:</td><td class="value">{{ $p->pedukuhan ?? '-' }} RT {{ $p->rt_format }} RW {{ $p->rw_format }}, {{ $setting->nama_kelurahan }}, {{ $setting->nama_kapanewon }}, {{ $setting->nama_kabupaten }}</td></tr>
    </table>
    <p style="margin-top:12px;">Adalah wali nikah yang sah dari:</p>
    <table class="data" style="margin-top:8px;">
        <tr><td class="label">Nama Calon Istri</td><td class="sep">:</td><td class="value">{{ $extra['nama_calon_istri'] ?? $p->nama_lengkap }}</td></tr>
        <tr><td class="label">NIK</td><td class="sep">:</td><td class="value">{{ $p->nik ?? '-' }}</td></tr>
        <tr><td class="label">Tempat / Tanggal Lahir</td><td class="sep">:</td><td class="value">{{ $p->tempat_lahir ?? '-' }}, {{ $p->tanggal_lahir_format ?? '-' }}</td></tr>
        <tr><td class="label">Alamat</td><td class="sep">:</td><td class="value">{{ $p->pedukuhan ?? '-' }} RT {{ $p->rt_format }} RW {{ $p->rw_format }}, {{ $setting->nama_kelurahan }}, {{ $setting->nama_kapanewon }}, {{ $setting->nama_kabupaten }}</td></tr>
    </table>
</div>
<div class="penutup"><p>Demikian surat keterangan ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.</p></div>
@include('surat._berlaku', ['extra' => $extra, 'surat' => $surat])
@include('surat._ttd', ['ttd' => $ttd, 'setting' => $setting])
@endsection