@extends('surat.layout')
@section('content')
@php $p = $surat->penduduk; $extra = $surat->data_tambahan ?? []; $ttd = $surat->ttd; $tgl = now()->translatedFormat('d F Y'); @endphp
<div class="judul"><h3>Surat Keterangan Penghasilan</h3><p>Nomor: {{ $surat->nomor_surat }}</p></div>
<div class="isi">
    <p>Yang bertanda tangan di bawah ini, Lurah {{ $setting->nama_kelurahan }}, dengan ini menerangkan bahwa:</p>
    <table class="data" style="margin-top:12px;">
        <tr><td class="label">Nama</td><td class="sep">:</td><td class="value">{{ $p->nama_lengkap }}</td></tr>
        <tr><td class="label">NIK</td><td class="sep">:</td><td class="value">{{ $p->nik ?? '-' }}</td></tr>
        <tr><td class="label">Tempat / Tanggal Lahir</td><td class="sep">:</td><td class="value">{{ $p->tempat_lahir ?? '-' }}, {{ $p->tanggal_lahir_format ?? '-' }}</td></tr>
        <tr><td class="label">Pekerjaan</td><td class="sep">:</td><td class="value">{{ $p->pekerjaan ?? '-' }}</td></tr>
        <tr><td class="label">Alamat</td><td class="sep">:</td><td class="value">{{ $p->pedukuhan ?? '-' }} RT {{ $p->rt_format }} RW {{ $p->rw_format }}, {{ $setting->nama_kelurahan }}, {{ $setting->nama_kapanewon }}, {{ $setting->nama_kabupaten }}</td></tr>
        <tr><td class="label">Penghasilan/Bulan</td><td class="sep">:</td><td class="value"><strong>Rp {{ number_format((int) str_replace('.', '', $extra['penghasilan_perbulan'] ?? '0'), 0, ',', '.') }},-</strong></td></tr>
    </table>
    @if(!empty($extra['keperluan']))<p style="margin-top:12px;">Keperluan: <strong>{{ $extra['keperluan'] }}</strong></p>@endif
</div>
<div class="penutup"><p>Demikian surat keterangan ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.</p></div>
@include('surat._berlaku', ['extra' => $extra])
@include('surat._ttd', ['ttd' => $ttd, 'setting' => $setting])
@endsection