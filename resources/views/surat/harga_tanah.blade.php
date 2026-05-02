@extends('surat.layout')
@section('content')
@php $p = $surat->penduduk; $extra = $surat->data_tambahan ?? []; $ttd = $surat->ttd; $tgl = now()->translatedFormat('d F Y'); @endphp
<div class="judul"><h3>Surat Keterangan Harga Tanah</h3><p>Nomor: {{ $surat->nomor_surat }}</p></div>
<div class="isi">
    <p>Yang bertanda tangan di bawah ini, Lurah {{ $setting->nama_kelurahan }}, dengan ini menerangkan harga tanah atas permohonan:</p>
    <table class="data" style="margin-top:12px;">
        <tr><td class="label">Nama Pemohon</td><td class="sep">:</td><td class="value">{{ $p->nama_lengkap }}</td></tr>
        <tr><td class="label">NIK</td><td class="sep">:</td><td class="value">{{ $p->nik ?? '-' }}</td></tr>
        <tr><td class="label">Alamat</td><td class="sep">:</td><td class="value">{{ $p->pedukuhan ?? '-' }}, RT {{ $p->rt ?? '-' }}/RW {{ $p->rw ?? '-' }}, Kalurahan {{ $setting->nama_kelurahan }}</td></tr>
    </table>
    <p style="margin-top:12px;">Dengan keterangan tanah sebagai berikut:</p>
    <table class="data" style="margin-top:8px;">
        <tr><td class="label">Lokasi Tanah</td><td class="sep">:</td><td class="value">{{ $extra['lokasi_tanah'] ?? '-' }}</td></tr>
        <tr><td class="label">Luas Tanah</td><td class="sep">:</td><td class="value">{{ number_format($extra['luas_tanah'] ?? 0, 0, ',', '.') }} m²</td></tr>
        <tr><td class="label">Harga per m²</td><td class="sep">:</td><td class="value">Rp {{ number_format($extra['harga_per_m2'] ?? 0, 0, ',', '.') }},-</td></tr>
        <tr><td class="label">Total Nilai</td><td class="sep">:</td><td class="value"><strong>Rp {{ number_format(($extra['luas_tanah'] ?? 0) * ($extra['harga_per_m2'] ?? 0), 0, ',', '.') }},-</strong></td></tr>
        @if(!empty($extra['peruntukan']))<tr><td class="label">Peruntukan</td><td class="sep">:</td><td class="value">{{ $extra['peruntukan'] }}</td></tr>@endif
    </table>
</div>
<div class="penutup"><p>Demikian surat keterangan ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.</p></div>
@include('surat._ttd', ['ttd' => $ttd, 'setting' => $setting])
@endsection