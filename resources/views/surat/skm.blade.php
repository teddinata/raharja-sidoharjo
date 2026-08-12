@extends('surat.layout')

@section('content')
@php
    $p     = $surat->penduduk;
    $extra = $surat->data_tambahan ?? [];
    $ttd   = $surat->ttd;
    $tgl   = now()->translatedFormat('d F Y');
@endphp

<div class="judul">
    <h3>Surat Keterangan Tidak Mampu</h3>
    <p>Nomor: {{ $surat->nomor_surat }}</p>
</div>

<div class="isi">
    <p>Yang bertanda tangan di bawah ini, Lurah {{ $setting->nama_kelurahan }},
    Kapanewon {{ $setting->nama_kapanewon }}, Kabupaten {{ $setting->nama_kabupaten }},
    dengan ini menerangkan bahwa:</p>

    <table class="data" style="margin-top:6px;">
        <tr><td class="label">Nama</td><td class="sep">:</td><td class="value">{{ $p->nama_lengkap }}</td></tr>
        <tr><td class="label">NIK</td><td class="sep">:</td><td class="value">{{ $p->nik ?? '-' }}</td></tr>
        <tr><td class="label">Tempat / Tanggal Lahir</td><td class="sep">:</td><td class="value">{{ $p->tempat_lahir ?? '-' }}, {{ $p->tanggal_lahir_format ?? '-' }}</td></tr>
        <tr><td class="label">Jenis Kelamin</td><td class="sep">:</td><td class="value">{{ $p->jenis_kelamin ?? '-' }}</td></tr>
        <tr><td class="label">Agama</td><td class="sep">:</td><td class="value">{{ $p->agama ?? '-' }}</td></tr>
        <tr><td class="label">Pekerjaan</td><td class="sep">:</td><td class="value">{{ $p->pekerjaan ?? '-' }}</td></tr>
        <tr><td class="label">Alamat</td><td class="sep">:</td>
            <td class="value">{{ $p->pedukuhan ?? '-' }} RT {{ $p->rt_format }} RW {{ $p->rw_format }}, {{ $setting->nama_kelurahan }}, {{ $setting->nama_kapanewon }}, {{ $setting->nama_kabupaten }}</td></tr>
    </table>

    <p style="margin-top:6px;">
        Adalah benar-benar warga tidak mampu / kurang mampu yang berdomisili di wilayah
        Kalurahan {{ $setting->nama_kelurahan }}, Kapanewon {{ $setting->nama_kapanewon }},
        Kabupaten {{ $setting->nama_kabupaten }}.
    </p>

    @if(!empty($extra['keperluan']))
    <p style="margin-top:6px;">
        Surat keterangan ini dibuat untuk keperluan: <strong>{{ $extra['keperluan'] }}</strong>.
    </p>
    @endif
</div>

<div class="penutup">
    <p>Demikian surat keterangan ini dibuat dengan sebenarnya untuk dapat dipergunakan
    sebagaimana mestinya.</p>
</div>

@include('surat._berlaku', ['extra' => $extra, 'surat' => $surat])
@include('surat._ttd', ['ttd' => $ttd, 'setting' => $setting])
@endsection