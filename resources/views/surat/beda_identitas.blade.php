@extends('surat.layout')
@section('content')
@php $p = $surat->penduduk; $extra = $surat->data_tambahan ?? []; $ttd = $surat->ttd; $tgl = now()->translatedFormat('d F Y'); @endphp
<div class="judul"><h3>Surat Keterangan Perbedaan Identitas</h3><p>Nomor: {{ $surat->nomor_surat }}</p></div>
<div class="isi">
    <p>Yang bertanda tangan di bawah ini, Lurah {{ $setting->nama_kelurahan }}, dengan ini menerangkan bahwa:</p>
    <table class="data" style="margin-top:12px;">
        <tr><td class="label">Nama</td><td class="sep">:</td><td class="value">{{ $p->nama_lengkap }}</td></tr>
        <tr><td class="label">NIK</td><td class="sep">:</td><td class="value">{{ $p->nik ?? '-' }}</td></tr>
        <tr><td class="label">Tempat / Tanggal Lahir</td><td class="sep">:</td><td class="value">{{ $p->tempat_lahir ?? '-' }}, {{ $p->tanggal_lahir_format ?? '-' }}</td></tr>
        <tr><td class="label">Alamat</td><td class="sep">:</td><td class="value">{{ $p->pedukuhan ?? '-' }} RT {{ $p->rt_format }} RW {{ $p->rw_format }}, {{ $setting->nama_kelurahan }}, {{ $setting->nama_kapanewon }}, {{ $setting->nama_kabupaten }}</td></tr>
    </table>
    <p style="margin-top:12px;">Bahwa terdapat perbedaan <strong>{{ $extra['jenis_beda'] ?? '-' }}</strong> pada dokumen yang bersangkutan:</p>
    <table class="data" style="margin-top:8px;">
        <tr><td class="label">Data di Dokumen Lama</td><td class="sep">:</td><td class="value">{{ $extra['data_di_dokumen'] ?? '-' }}</td></tr>
        <tr><td class="label">Data yang Benar</td><td class="sep">:</td><td class="value"><strong>{{ $extra['data_yang_benar'] ?? '-' }}</strong></td></tr>
        @if(!empty($extra['nama_dokumen']))<tr><td class="label">Nama Dokumen</td><td class="sep">:</td><td class="value">{{ $extra['nama_dokumen'] }}</td></tr>@endif
    </table>
    <p style="margin-top:12px;">Bahwa kedua data tersebut adalah benar-benar milik orang yang sama.</p>
</div>
<div class="penutup"><p>Demikian surat keterangan ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.</p></div>
@include('surat._berlaku', ['extra' => $extra])
@include('surat._ttd', ['ttd' => $ttd, 'setting' => $setting])
@endsection