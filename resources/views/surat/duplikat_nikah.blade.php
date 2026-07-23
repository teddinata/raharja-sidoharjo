@extends('surat.layout')
@section('content')
@php $p = $surat->penduduk; $extra = $surat->data_tambahan ?? []; $ttd = $surat->ttd; $tgl = now()->translatedFormat('d F Y'); @endphp
<div class="judul"><h3>Surat Pengantar Duplikat Buku Nikah</h3><p>Nomor: {{ $surat->nomor_surat }}</p></div>
<div class="isi">
    <p>Yang bertanda tangan di bawah ini, Lurah {{ $setting->nama_kelurahan }}, dengan ini menerangkan bahwa:</p>
    <table class="data" style="margin-top:12px;">
        <tr><td class="label">Nama</td><td class="sep">:</td><td class="value">{{ $p->nama_lengkap }}</td></tr>
        <tr><td class="label">NIK</td><td class="sep">:</td><td class="value">{{ $p->nik ?? '-' }}</td></tr>
        <tr><td class="label">Tempat / Tanggal Lahir</td><td class="sep">:</td><td class="value">{{ $p->tempat_lahir ?? '-' }}, {{ $p->tanggal_lahir_format ?? '-' }}</td></tr>
        <tr><td class="label">Nama Pasangan</td><td class="sep">:</td><td class="value">{{ $extra['nama_pasangan'] ?? '-' }}</td></tr>
        <tr><td class="label">Tahun Menikah</td><td class="sep">:</td><td class="value">{{ $extra['tahun_menikah'] ?? '-' }}</td></tr>
        <tr><td class="label">Alamat</td><td class="sep">:</td><td class="value">{{ $p->pedukuhan ?? '-' }} RT {{ $p->rt_format }} RW {{ $p->rw_format }}, {{ $setting->nama_kelurahan }}, {{ $setting->nama_kapanewon }}, {{ $setting->nama_kabupaten }}</td></tr>
    </table>
    <p style="margin-top:12px;">Adalah benar pasangan suami istri yang sah dan membutuhkan duplikat buku nikah karena buku nikah asli hilang/rusak. Surat ini dibuat sebagai pengantar ke KUA Kapanewon {{ $setting->nama_kapanewon }}.</p>
</div>
<div class="penutup"><p>Demikian surat pengantar ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p></div>
@include('surat._berlaku', ['extra' => $extra, 'surat' => $surat])
@include('surat._ttd', ['ttd' => $ttd, 'setting' => $setting])
@endsection