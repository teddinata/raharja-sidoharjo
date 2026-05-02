@extends('surat.layout')
@section('content')
@php $p = $surat->penduduk; $extra = $surat->data_tambahan ?? []; $ttd = $surat->ttd; $tgl = now()->translatedFormat('d F Y'); @endphp
<div class="judul"><h3>Surat Keterangan Ijin Keramaian</h3><p>Nomor: {{ $surat->nomor_surat }}</p></div>
<div class="isi">
    <p>Yang bertanda tangan di bawah ini, Lurah {{ $setting->nama_kelurahan }}, dengan ini menerangkan bahwa:</p>
    <table class="data" style="margin-top:12px;">
        <tr><td class="label">Nama Pemohon</td><td class="sep">:</td><td class="value">{{ $p->nama_lengkap }}</td></tr>
        <tr><td class="label">NIK</td><td class="sep">:</td><td class="value">{{ $p->nik ?? '-' }}</td></tr>
        <tr><td class="label">Alamat</td><td class="sep">:</td><td class="value">{{ $p->pedukuhan ?? '-' }}, RT {{ $p->rt ?? '-' }}/RW {{ $p->rw ?? '-' }}, Kalurahan {{ $setting->nama_kelurahan }}</td></tr>
    </table>
    <p style="margin-top:12px;">Bermaksud menyelenggarakan kegiatan:</p>
    <table class="data" style="margin-top:8px;">
        <tr><td class="label">Nama Acara</td><td class="sep">:</td><td class="value">{{ $extra['nama_acara'] ?? '-' }}</td></tr>
        <tr><td class="label">Tanggal Acara</td><td class="sep">:</td><td class="value">{{ isset($extra['tanggal_acara']) ? \Carbon\Carbon::parse($extra['tanggal_acara'])->translatedFormat('d F Y') : '-' }}</td></tr>
        <tr><td class="label">Tempat Acara</td><td class="sep">:</td><td class="value">{{ $extra['tempat_acara'] ?? '-' }}</td></tr>
        @if(!empty($extra['perkiraan_peserta']))<tr><td class="label">Perkiraan Peserta</td><td class="sep">:</td><td class="value">± {{ number_format($extra['perkiraan_peserta'], 0, ',', '.') }} orang</td></tr>@endif
    </table>
    <p style="margin-top:12px;">Tidak keberatan atas penyelenggaraan kegiatan tersebut dengan ketentuan tidak mengganggu ketertiban umum dan keamanan masyarakat.</p>
</div>
<div class="penutup"><p>Demikian surat keterangan ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p></div>
@include('surat._ttd', ['ttd' => $ttd, 'setting' => $setting])
@endsection