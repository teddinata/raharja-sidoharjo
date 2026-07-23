@extends('surat.layout')

@section('content')
@php
    $p     = $surat->penduduk;
    $extra = $surat->data_tambahan ?? [];
    $ttd   = $surat->ttd;
    $tgl   = now()->translatedFormat('d F Y');
    $judul = strtoupper($extra['judul_surat'] ?? 'SURAT KETERANGAN');
@endphp

<div class="judul">
    <h3>{{ $judul }}</h3>
    <p>Nomor: {{ $surat->nomor_surat }}</p>
    @if(!empty($extra['perihal']))
    <p style="margin-top:4px;">Perihal: {{ $extra['perihal'] }}</p>
    @endif
</div>

{{-- Data pemohon ringkas --}}
<div class="isi">
    <table class="data" style="margin-top:10px;">
        <tr><td class="label">Nama</td><td class="sep">:</td><td class="value">{{ $p->nama_lengkap }}</td></tr>
        <tr><td class="label">NIK</td><td class="sep">:</td><td class="value">{{ $p->nik ?? '-' }}</td></tr>
        <tr><td class="label">Alamat</td><td class="sep">:</td>
            <td class="value">{{ $p->pedukuhan ?? '-' }} RT {{ $p->rt_format }} RW {{ $p->rw_format }}, {{ $setting->nama_kelurahan }}, {{ $setting->nama_kapanewon }}, {{ $setting->nama_kabupaten }}</td></tr>
    </table>
</div>

{{-- Area kosong untuk isi kustom --}}
<div style="min-height: 200px; margin-top: 20px; border-bottom: 1px dashed #ccc;"></div>

<div class="penutup" style="margin-top:14px;">
    <p>Demikian surat keterangan ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p>
</div>

@include('surat._berlaku', ['extra' => $extra, 'surat' => $surat])
@include('surat._ttd', ['ttd' => $ttd, 'setting' => $setting])
@endsection
