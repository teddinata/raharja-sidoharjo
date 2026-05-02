@extends('surat.layout')
@section('content')
@php $p = $surat->penduduk; $extra = $surat->data_tambahan ?? []; $ttd = $surat->ttd; $tgl = now()->translatedFormat('d F Y'); @endphp
<div class="judul"><h3>Surat Pengantar Pindah Penduduk</h3><p>Nomor: {{ $surat->nomor_surat }}</p></div>
<div class="isi">
    <p>Yang bertanda tangan di bawah ini, Lurah {{ $setting->nama_kelurahan }}, dengan ini menerangkan bahwa:</p>
    <table class="data" style="margin-top:12px;">
        <tr><td class="label">Nama</td><td class="sep">:</td><td class="value">{{ $p->nama_lengkap }}</td></tr>
        <tr><td class="label">NIK</td><td class="sep">:</td><td class="value">{{ $p->nik ?? '-' }}</td></tr>
        <tr><td class="label">Tempat / Tanggal Lahir</td><td class="sep">:</td><td class="value">{{ $p->tempat_lahir ?? '-' }}, {{ $p->tanggal_lahir_format ?? '-' }}</td></tr>
        <tr><td class="label">Alamat Asal</td><td class="sep">:</td><td class="value">{{ $p->pedukuhan ?? '-' }}, RT {{ $p->rt ?? '-' }}/RW {{ $p->rw ?? '-' }}, Kalurahan {{ $setting->nama_kelurahan }}</td></tr>
        <tr><td class="label">Alamat Tujuan</td><td class="sep">:</td><td class="value">{{ $extra['alamat_tujuan'] ?? '-' }}</td></tr>
        @if(!empty($extra['alasan_pindah']))<tr><td class="label">Alasan Pindah</td><td class="sep">:</td><td class="value">{{ $extra['alasan_pindah'] }}</td></tr>@endif
    </table>
    <p style="margin-top:12px;">Bermaksud untuk pindah domisili ke alamat tujuan tersebut di atas. Mohon kiranya dapat diproses lebih lanjut.</p>
</div>
<div class="penutup"><p>Demikian surat pengantar ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p></div>
@include('surat._ttd', ['ttd' => $ttd, 'setting' => $setting])
@endsection