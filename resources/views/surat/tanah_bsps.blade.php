@extends('surat.layout')
@section('content')
@php $p = $surat->penduduk; $extra = $surat->data_tambahan ?? []; $ttd = $surat->ttd; $tgl = now()->translatedFormat('d F Y'); @endphp
<div class="judul"><h3>Surat Keterangan Tanah Program BSPS</h3><p>Nomor: {{ $surat->nomor_surat }}</p></div>
<div class="isi">
    <p>Yang bertanda tangan di bawah ini, Lurah {{ $setting->nama_kelurahan }}, dengan ini menerangkan bahwa:</p>
    <table class="data" style="margin-top:12px;">
        <tr><td class="label">Nama</td><td class="sep">:</td><td class="value">{{ $p->nama_lengkap }}</td></tr>
        <tr><td class="label">NIK</td><td class="sep">:</td><td class="value">{{ $p->nik ?? '-' }}</td></tr>
        <tr><td class="label">Alamat</td><td class="sep">:</td><td class="value">{{ $p->pedukuhan ?? '-' }} RT {{ $p->rt_format }} RW {{ $p->rw_format }}, {{ $setting->nama_kelurahan }}, {{ $setting->nama_kapanewon }}, {{ $setting->nama_kabupaten }}</td></tr>
        @if(!empty($extra['nomor_peserta']))<tr><td class="label">No. Peserta BSPS</td><td class="sep">:</td><td class="value">{{ $extra['nomor_peserta'] }}</td></tr>@endif
    </table>
    <p style="margin-top:12px;">Memiliki tanah dengan keterangan:</p>
    <table class="data" style="margin-top:8px;">
        <tr><td class="label">Lokasi Tanah</td><td class="sep">:</td><td class="value">{{ $extra['lokasi_tanah'] ?? '-' }}</td></tr>
        <tr><td class="label">Luas Tanah</td><td class="sep">:</td><td class="value">{{ number_format((int) str_replace('.', '', $extra['luas_tanah'] ?? '0'), 0, ',', '.') }} m²</td></tr>
    </table>
    <p style="margin-top:12px;">Yang bersangkutan adalah peserta program Bantuan Stimulan Perumahan Swadaya (BSPS) di Kalurahan {{ $setting->nama_kelurahan }}.</p>
</div>
<div class="penutup"><p>Demikian surat keterangan ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.</p></div>
@include('surat._ttd', ['ttd' => $ttd, 'setting' => $setting])
@endsection