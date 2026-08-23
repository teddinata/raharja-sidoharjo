{{--
    Delapan baris identitas orang, format baku formulir N (dipakai N4, N5, N6, dan
    surat pernyataan).

    $orang    : array hasil olahan di paket.blade.php
    $binLabel : "Bin" untuk laki-laki, "Binti" untuk perempuan
--}}
<table class="rincian">
    <tr><td class="nomor">1.</td><td class="label">Nama lengkap dan alias</td><td class="sep">:</td><td class="value">{{ $orang['nama'] }}</td></tr>
    <tr><td class="nomor">2.</td><td class="label">{{ $binLabel }}</td><td class="sep">:</td><td class="value">{{ $orang['bin'] }}</td></tr>
    <tr><td class="nomor">3.</td><td class="label">Nomor Induk Kependudukan (NIK)</td><td class="sep">:</td><td class="value">{{ $orang['nik'] }}</td></tr>
    <tr><td class="nomor">4.</td><td class="label">Tempat dan tanggal lahir</td><td class="sep">:</td><td class="value">{{ $orang['ttl'] }}</td></tr>
    <tr><td class="nomor">5.</td><td class="label">Kewarganegaraan</td><td class="sep">:</td><td class="value">{{ $orang['kewarganegaraan'] }}</td></tr>
    <tr><td class="nomor">6.</td><td class="label">Agama</td><td class="sep">:</td><td class="value">{{ $orang['agama'] }}</td></tr>
    <tr><td class="nomor">7.</td><td class="label">Pekerjaan</td><td class="sep">:</td><td class="value">{{ $orang['pekerjaan'] }}</td></tr>
    <tr><td class="nomor">8.</td><td class="label">Alamat</td><td class="sep">:</td><td class="value">{{ $orang['alamat'] }}</td></tr>
</table>
