@extends('surat.layout')
@section('content')
@php $p = $surat->penduduk; $extra = $surat->data_tambahan ?? []; $ttd = $surat->ttd; $tgl = now()->translatedFormat('d F Y'); @endphp
<div class="judul"><h3>Surat Keterangan Usaha</h3><p>Nomor: {{ $surat->nomor_surat }}</p></div>
<div class="isi">
    <p>Yang bertanda tangan di bawah ini, Lurah {{ $setting->nama_kelurahan }}, dengan ini menerangkan bahwa:</p>
    <table class="data" style="margin-top:6px;">
        <tr><td class="label">Nama</td><td class="sep">:</td><td class="value">{{ $p->nama_lengkap }}</td></tr>
        <tr><td class="label">NIK</td><td class="sep">:</td><td class="value">{{ $p->nik ?? '-' }}</td></tr>
        <tr><td class="label">Tempat / Tanggal Lahir</td><td class="sep">:</td><td class="value">{{ $p->tempat_lahir ?? '-' }}, {{ $p->tanggal_lahir_format ?? '-' }}</td></tr>
        <tr><td class="label">Alamat</td><td class="sep">:</td><td class="value">{{ $p->pedukuhan ?? '-' }} RT {{ $p->rt_format }} RW {{ $p->rw_format }}, {{ $setting->nama_kelurahan }}, {{ $setting->nama_kapanewon }}, {{ $setting->nama_kabupaten }}</td></tr>
    </table>
    <p style="margin-top:6px;">Adalah benar-benar memiliki usaha:</p>
    <table class="data" style="margin-top:6px;">
        <tr><td class="label">Nama Usaha</td><td class="sep">:</td><td class="value">{{ $extra['nama_usaha'] ?? '-' }}</td></tr>
        <tr><td class="label">Jenis Usaha</td><td class="sep">:</td><td class="value">{{ $extra['jenis_usaha'] ?? '-' }}</td></tr>
        <tr><td class="label">Alamat Usaha</td><td class="sep">:</td><td class="value">{{ $extra['alamat_usaha'] ?? '-' }}</td></tr>
        @if(!empty($extra['kantor_unit_bri']))<tr><td class="label">Kantor / Unit BRI</td><td class="sep">:</td><td class="value">{{ $extra['kantor_unit_bri'] }}</td></tr>@endif
        @if(!empty($extra['keperluan_kredit']))<tr><td class="label">Keperluan Kredit</td><td class="sep">:</td><td class="value">{{ $extra['keperluan_kredit'] }}</td></tr>@endif
    </table>
    <p style="margin-top:6px;">Surat keterangan ini dibuat untuk keperluan pengajuan kredit di Bank BRI.</p>
</div>
<div class="penutup"><p>Demikian surat keterangan ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.</p></div>
@include('surat._berlaku', ['extra' => $extra, 'surat' => $surat])
@include('surat._ttd', ['ttd' => $ttd, 'setting' => $setting])
@endsection