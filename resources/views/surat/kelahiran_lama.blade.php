@extends('surat.layout')
@section('content')
@php $p = $surat->penduduk; $extra = $surat->data_tambahan ?? []; $ttd = $surat->ttd; $tgl = now()->translatedFormat('d F Y'); @endphp
<div class="judul"><h3>Surat Keterangan Kelahiran Terlambat</h3><p>Nomor: {{ $surat->nomor_surat }}</p></div>
<div class="isi">
    <p>Yang bertanda tangan di bawah ini, Lurah {{ $setting->nama_kelurahan }}, dengan ini menerangkan bahwa:</p>
    <table class="data" style="margin-top:6px;">
        <tr><td class="label">Nama</td><td class="sep">:</td><td class="value">{{ $extra['nama_bayi'] ?? '-' }}</td></tr>
        <tr><td class="label">Jenis Kelamin</td><td class="sep">:</td><td class="value">{{ $extra['jenis_kelamin_bayi'] ?? '-' }}</td></tr>
        <tr><td class="label">Tempat Lahir</td><td class="sep">:</td><td class="value">{{ $extra['tempat_lahir_bayi'] ?? '-' }}</td></tr>
        <tr><td class="label">Tanggal Lahir</td><td class="sep">:</td><td class="value">{{ isset($extra['tanggal_lahir_bayi']) ? \Carbon\Carbon::parse($extra['tanggal_lahir_bayi'])->translatedFormat('d F Y') : '-' }}</td></tr>
        <tr><td class="label">Nama Ayah</td><td class="sep">:</td><td class="value">{{ $extra['nama_ayah'] ?? '-' }}</td></tr>
        <tr><td class="label">Nama Ibu</td><td class="sep">:</td><td class="value">{{ $extra['nama_ibu'] ?? '-' }}</td></tr>
        <tr><td class="label">Alamat</td><td class="sep">:</td><td class="value">{{ $p->pedukuhan ?? '-' }} RT {{ $p->rt_format }} RW {{ $p->rw_format }}, {{ $setting->nama_kelurahan }}, {{ $setting->nama_kapanewon }}, {{ $setting->nama_kabupaten }}</td></tr>
    </table>
    @if(!empty($extra['keterangan_lama']))<p style="margin-top:6px;">Keterangan keterlambatan: {{ $extra['keterangan_lama'] }}</p>@endif
</div>
<div class="penutup"><p>Demikian surat keterangan ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.</p></div>
@include('surat._berlaku', ['extra' => $extra, 'surat' => $surat])
@include('surat._ttd', ['ttd' => $ttd, 'setting' => $setting])
@endsection