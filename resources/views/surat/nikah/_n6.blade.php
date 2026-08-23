{{-- Model N6 — Surat Keterangan Kematian Suami/Istri (untuk duda/janda cerai mati). --}}
@include('surat.nikah._model', ['model' => 'N6'])

<table class="rincian">
    <tr><td class="nomor"></td><td class="label">KANTOR KELURAHAN</td><td class="sep">:</td><td class="value">{{ strtoupper($setting->nama_kelurahan) }}</td></tr>
    <tr><td class="nomor"></td><td class="label">KAPANEWON</td><td class="sep">:</td><td class="value">{{ strtoupper($setting->nama_kapanewon) }}</td></tr>
    <tr><td class="nomor"></td><td class="label">KABUPATEN/KOTA</td><td class="sep">:</td><td class="value">{{ strtoupper($setting->nama_kabupaten) }}</td></tr>
</table>

<p class="judul-formulir" style="margin-top:8px;">SURAT KETERANGAN KEMATIAN SUAMI/ISTRI</p>
<p class="nomor-formulir">Nomor: {{ $surat->nomor_surat }}</p>

<p class="alinea">Yang bertanda tangan di bawah ini menerangkan dengan sesungguhnya bahwa :</p>

<p>A.</p>
@include('surat.nikah._orang', ['orang' => $almarhum, 'binLabel' => $pria ? 'Binti' : 'Bin'])

<table class="rincian" style="margin-top:6px;">
    <tr><td class="nomor"></td><td class="label">Telah meninggal dunia pada tanggal</td><td class="sep">:</td><td class="value">{{ $almarhum['tgl_meninggal'] }}</td></tr>
    <tr><td class="nomor"></td><td class="label">Di</td><td class="sep">:</td><td class="value">{{ $almarhum['tempat_meninggal'] }}</td></tr>
</table>

<p class="alinea spasi">Yang bersangkutan adalah {{ $pria ? 'istri' : 'suami' }} dari :</p>

<p>B.</p>
@include('surat.nikah._orang', ['orang' => $pemohon, 'binLabel' => $pria ? 'Bin' : 'Binti'])

<p class="alinea spasi">Demikian, surat keterangan ini dibuat dengan mengingat sumpah jabatan dan untuk digunakan seperlunya.</p>

<p style="text-align:right;margin-top:8px;">{{ strtoupper($setting->nama_kelurahan) }} , <strong>{{ $tglSurat }}</strong></p>
<p style="text-align:right;">Kepala Desa/Lurah</p>
<p style="text-align:right;" class="ttd-n-nama">{{ $penanda['nama'] }}</p>
