{{-- Model N5 — Surat Izin Orang Tua (diperlukan bila calon mempelai belum 21 tahun). --}}
@include('surat.nikah._model', ['model' => 'N5'])

<p class="judul-formulir">SURAT IZIN ORANG TUA</p>

<p class="alinea spasi">Yang bertanda tangan di bawah ini :</p>

<p>A. &nbsp;Ayah</p>
@include('surat.nikah._orang', ['orang' => $ayah, 'binLabel' => 'Bin'])

<p class="spasi">B. &nbsp;Ibu</p>
@include('surat.nikah._orang', ['orang' => $ibu, 'binLabel' => 'Binti'])

<p class="alinea spasi">adalah ayah dan ibu kandung dari :</p>
@include('surat.nikah._orang', ['orang' => $pemohon, 'binLabel' => $pria ? 'Bin' : 'Binti'])

<p class="alinea spasi">memberikan izin kepada anak kami untuk melakukan perkawinan dengan :</p>
@include('surat.nikah._orang', ['orang' => $pasangan, 'binLabel' => $pria ? 'Binti' : 'Bin'])

<p class="alinea spasi">Demikian surat izin ini dibuat dengan kesadaran tanpa ada paksaan dari siapapun dan untuk digunakan seperlunya.</p>

<p style="text-align:right;margin-top:8px;">{{ strtoupper($setting->nama_kelurahan) }}, <strong>{{ $tglSurat }}</strong></p>

<table class="ttd-n" style="margin-top:6px;">
    <tr>
        <td style="width:50%;text-align:center;">Ayah</td>
        <td style="width:50%;text-align:center;">Ibu</td>
    </tr>
    <tr>
        <td style="width:50%;text-align:center;"><p class="ttd-n-nama">{{ $ayah['nama'] }}</p></td>
        <td style="width:50%;text-align:center;"><p class="ttd-n-nama">{{ $ibu['nama'] }}</p></td>
    </tr>
</table>
