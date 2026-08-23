{{-- Surat Keterangan Wali Nikah — hanya relevan untuk calon istri. --}}
<p class="judul-formulir">SURAT KETERANGAN WALI NIKAH</p>
<p class="nomor-formulir">Nomor; {{ $surat->nomor_surat }}</p>

<p class="alinea">Yang bertanda tangan di bawah ini Lurah/ Kepala Desa {{ $setting->nama_kelurahan }}
Kapanewon {{ $setting->nama_kapanewon }} Kabupaten {{ $setting->nama_kabupaten }} menerangkan bahwa :</p>

<p class="spasi">Wali Nikah dari seorang perempuan :</p>
<table class="rincian">
    <tr><td class="nomor"></td><td class="label">Nama</td><td class="sep">:</td><td class="value">{{ $pemohon['nama'] }}</td></tr>
    <tr><td class="nomor"></td><td class="label">Tempat/ Tanggal lahir</td><td class="sep">:</td><td class="value">{{ $pemohon['ttl'] }}</td></tr>
    <tr><td class="nomor"></td><td class="label">Agama</td><td class="sep">:</td><td class="value">{{ $pemohon['agama'] }}</td></tr>
    <tr><td class="nomor"></td><td class="label">Alamat</td><td class="sep">:</td><td class="value">{{ $pemohon['alamat'] }}</td></tr>
</table>

<p class="alinea spasi">Adalah seorang laki-laki/ Hakim *)</p>
<table class="rincian">
    <tr><td class="nomor"></td><td class="label">Nama</td><td class="sep">:</td><td class="value">{{ $wali['nama'] }}</td></tr>
    <tr><td class="nomor"></td><td class="label">Tempat/Tanggal lahir</td><td class="sep">:</td><td class="value">{{ $wali['ttl'] }}</td></tr>
    <tr><td class="nomor"></td><td class="label">Agama</td><td class="sep">:</td><td class="value">{{ $wali['agama'] }}</td></tr>
    <tr><td class="nomor"></td><td class="label">Hubungan nasab</td><td class="sep">:</td><td class="value">{{ $wali['nasab'] }}</td></tr>
    <tr><td class="nomor"></td><td class="label">Sebab</td><td class="sep">:</td><td class="value">{{ $wali['sebab'] }}</td></tr>
</table>

<p class="alinea spasi">Demikian surat keterangan ini dibuat dengan mengingat sumpah jabatan dan untuk dipergunakan seperlunya.</p>

<p style="text-align:right;margin-top:8px;">{{ $setting->nama_kelurahan }}, <strong>{{ $tglSurat }}</strong></p>
<p style="text-align:right;">{{ $penanda['jabatan'] }}</p>
<p style="text-align:right;" class="ttd-n-nama">{{ $penanda['nama'] }}</p>
