@extends('surat.layout')
@section('content')
@php $p = $surat->penduduk; $extra = $surat->data_tambahan ?? []; $ttd = $surat->ttd; $tgl = now()->translatedFormat('d F Y'); @endphp
<div class="judul"><h3>Surat Keterangan Kematian</h3><p>Nomor: {{ $surat->nomor_surat }}</p></div>
<div class="isi">
    <p>Yang bertanda tangan di bawah ini, Lurah {{ $setting->nama_kelurahan }}, dengan ini menerangkan bahwa:</p>
    <table class="data" style="margin-top:12px;">
        <tr><td class="label">Nama</td><td class="sep">:</td><td class="value">{{ $p->nama_lengkap }}</td></tr>
        <tr><td class="label">NIK</td><td class="sep">:</td><td class="value">{{ $p->nik ?? '-' }}</td></tr>
        <tr><td class="label">Tempat / Tanggal Lahir</td><td class="sep">:</td><td class="value">{{ $p->tempat_lahir ?? '-' }}, {{ $p->tanggal_lahir_format ?? '-' }}</td></tr>
        <tr><td class="label">Jenis Kelamin</td><td class="sep">:</td><td class="value">{{ $p->jenis_kelamin ?? '-' }}</td></tr>
        <tr><td class="label">Agama</td><td class="sep">:</td><td class="value">{{ $p->agama ?? '-' }}</td></tr>
        <tr><td class="label">Alamat</td><td class="sep">:</td><td class="value">{{ $p->pedukuhan ?? '-' }}, RT {{ $p->rt ?? '-' }}/RW {{ $p->rw ?? '-' }}, Kalurahan {{ $setting->nama_kelurahan }}</td></tr>
        <tr><td class="label">Tanggal Meninggal</td><td class="sep">:</td><td class="value">{{ isset($extra['tanggal_meninggal']) ? \Carbon\Carbon::parse($extra['tanggal_meninggal'])->translatedFormat('d F Y') : '-' }}</td></tr>
        <tr><td class="label">Tempat Meninggal</td><td class="sep">:</td><td class="value">{{ $extra['tempat_meninggal'] ?? '-' }}</td></tr>
        <tr><td class="label">Sebab Meninggal</td><td class="sep">:</td><td class="value">{{ $extra['sebab_meninggal'] ?? '-' }}</td></tr>
        @if(!empty($extra['nama_pelapor']))<tr><td class="label">Pelapor</td><td class="sep">:</td><td class="value">{{ $extra['nama_pelapor'] }} ({{ $extra['hubungan_pelapor'] ?? '-' }})</td></tr>@endif
    </table>
    <p style="margin-top:12px;">Adalah benar-benar telah meninggal dunia pada waktu tersebut di atas.</p>
</div>
<div class="penutup"><p>Demikian surat keterangan kematian ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.</p></div>
@include('surat._ttd', ['ttd' => $ttd, 'setting' => $setting])
@endsection