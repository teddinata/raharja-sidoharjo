@extends('surat.layout')
@section('content')
@php $p = $surat->penduduk; $extra = $surat->data_tambahan ?? []; $ttd = $surat->ttd; $tgl = now()->translatedFormat('d F Y'); @endphp
<div class="judul"><h3>Surat Pengantar Pembuatan Kartu Keluarga</h3><p>Nomor: {{ $surat->nomor_surat }}</p></div>
<div class="isi">
    <p>Yang bertanda tangan di bawah ini, Lurah {{ $setting->nama_kelurahan }}, dengan ini mengantarkan permohonan Kartu Keluarga atas nama:</p>
    <table class="data" style="margin-top:12px;">
        <tr><td class="label">Nama Kepala Keluarga</td><td class="sep">:</td><td class="value">{{ $p->nama_lengkap }}</td></tr>
        <tr><td class="label">NIK</td><td class="sep">:</td><td class="value">{{ $p->nik ?? '-' }}</td></tr>
        <tr><td class="label">No. KK</td><td class="sep">:</td><td class="value">{{ $p->no_kk ?? '-' }}</td></tr>
        <tr><td class="label">Alamat</td><td class="sep">:</td><td class="value">{{ $p->pedukuhan ?? '-' }} RT {{ $p->rt_format }} RW {{ $p->rw_format }}, {{ $setting->nama_kelurahan }}, {{ $setting->nama_kapanewon }}, {{ $setting->nama_kabupaten }}</td></tr>
        <tr><td class="label">Jenis Permohonan</td><td class="sep">:</td><td class="value"><strong>{{ $extra['jenis_permohonan'] ?? 'Baru' }}</strong></td></tr>
    </table>
    <p style="margin-top:12px;">Mohon kiranya dapat diproses lebih lanjut ke Dinas Kependudukan dan Pencatatan Sipil Kabupaten {{ $setting->nama_kabupaten }}.</p>
</div>
<div class="penutup"><p>Demikian surat pengantar ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p></div>
@include('surat._berlaku', ['extra' => $extra])
@include('surat._ttd', ['ttd' => $ttd, 'setting' => $setting])
@endsection