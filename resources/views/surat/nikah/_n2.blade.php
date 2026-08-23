{{-- Model N2 — Permohonan Kehendak Nikah (dari pemohon ke KUA). --}}
@include('surat.nikah._model', ['model' => 'N2'])

<table class="rincian">
    <tr>
        <td class="label" style="width:60%;">Perihal : <strong>Permohonan kehendak Nikah</strong></td>
        <td class="value" style="width:40%;">{{ strtoupper($setting->nama_kelurahan) }} {{ $tglSurat }}</td>
    </tr>
</table>

<p>Kepada Yth,</p>
<p>Kepala KBRI/KJRI/KUA Kapanewon {{ $akad['kua'] !== '-' ? $akad['kua'] : $setting->nama_kapanewon }}</p>
<p>di Tempat</p>

<p class="alinea spasi">Dengan hormat, kami mengajukan permohonan kehendak nikah untuk atas nama :</p>

<table class="rincian">
    <tr><td class="nomor"></td><td class="label">calon suami</td><td class="sep">:</td><td class="value"><strong>{{ $pria ? $pemohon['nama'] : $pasangan['nama'] }}</strong></td></tr>
    <tr><td class="nomor"></td><td class="label">Calon Istri</td><td class="sep">:</td><td class="value"><strong>{{ $pria ? $pasangan['nama'] : $pemohon['nama'] }}</strong></td></tr>
    <tr><td class="nomor"></td><td class="label">Hari/Tanggal/Jam</td><td class="sep">:</td><td class="value"><strong>{{ $akad['hari'] }} {{ $akad['tanggal'] }}</strong> &nbsp; Jam : <strong>{{ $akad['jam'] }}</strong></td></tr>
    <tr><td class="nomor"></td><td class="label">Tempat Akad Nikah</td><td class="sep">:</td><td class="value">{{ $akad['tempat'] }}</td></tr>
</table>

<p class="alinea spasi">Bersama ini kami sampaikan surat-surat yang diperlukan untuk diperiksa, sebagai berikut:</p>
<p>1. Surat pengantar perkawinan dari Desa/Kelurahan</p>
<p>2. Persetujuan calon mempelai</p>
<p>3. Fotokopi KTP</p>
<p>4. Fotokopi akte kelahiran</p>
<p>5. Fotokopi kartu keluarga</p>
<p>6. Pasfoto 2x3 = 5 lembar berlatar belakang biru</p>
<p>7. ………………………………………………………..</p>
<p>8. ……………………………………….……………….</p>

<p class="alinea spasi">Demikian permohonan ini kami sampaikan, kiranya dapat diperiksa, dihadiri dan dicatat sesuai dengan ketentuan peraturan perundang-undangan.</p>

<table class="ttd-n" style="margin-top:10px;">
    <tr>
        <td style="width:55%;">Diterima tanggal ………................................</td>
        <td style="width:45%;">Wassalam</td>
    </tr>
    <tr>
        <td style="width:55%;">Yang menerima</td>
        <td style="width:45%;">Pemohon</td>
    </tr>
    <tr>
        <td style="width:55%;">Kepala KUA / Penghulu*)</td>
        <td style="width:45%;"></td>
    </tr>
    <tr>
        <td style="width:55%;"><p class="ttd-n-garis">…………………………………..……..</p></td>
        <td style="width:45%;"><p class="ttd-n-nama">{{ $pemohon['nama'] }}</p></td>
    </tr>
</table>
