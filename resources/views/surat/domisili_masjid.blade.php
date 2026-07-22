@extends('surat.layout')
@section('content')
@php $p = $surat->penduduk; $extra = $surat->data_tambahan ?? []; $ttd = $surat->ttd; $tgl = now()->translatedFormat('d F Y'); @endphp
<div class="judul"><h3>Surat Keterangan Domisili Masjid / Mushola</h3><p>Nomor: {{ $surat->nomor_surat }}</p></div>
<div class="isi">
    <p>Yang bertanda tangan di bawah ini, Lurah {{ $setting->nama_kelurahan }}, dengan ini menerangkan bahwa:</p>
    <table class="data" style="margin-top:12px;">
        <tr><td class="label">Nama Masjid/Mushola</td><td class="sep">:</td><td class="value">{{ $extra['nama_masjid'] ?? '-' }}</td></tr>
        <tr><td class="label">Alamat</td><td class="sep">:</td><td class="value">{{ $extra['alamat_masjid'] ?? ($p->pedukuhan ?? '-').' RT '.$p->rt_format.' RW '.$p->rw_format.', '.$setting->nama_kelurahan.', '.$setting->nama_kapanewon.', '.$setting->nama_kabupaten }}</td></tr>
        <tr><td class="label">Nama Pemohon</td><td class="sep">:</td><td class="value">{{ $p->nama_lengkap }}</td></tr>
        <tr><td class="label">NIK</td><td class="sep">:</td><td class="value">{{ $p->nik ?? '-' }}</td></tr>
        <tr><td class="label">Jabatan</td><td class="sep">:</td><td class="value">{{ $p->pekerjaan ?? 'Pengurus' }}</td></tr>
    </table>
    <p style="margin-top:12px;">Adalah benar-benar masjid/mushola yang berdomisili di wilayah Kalurahan {{ $setting->nama_kelurahan }}, Kapanewon {{ $setting->nama_kapanewon }}, Kabupaten {{ $setting->nama_kabupaten }}.</p>
</div>
<div class="penutup"><p>Demikian surat keterangan ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.</p></div>
@include('surat._berlaku', ['extra' => $extra])
@include('surat._ttd', ['ttd' => $ttd, 'setting' => $setting])
@endsection