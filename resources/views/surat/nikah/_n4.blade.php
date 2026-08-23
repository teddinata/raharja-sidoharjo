{{-- Model N4 — Surat Persetujuan Mempelai. --}}
@include('surat.nikah._model', ['model' => 'N4'])

<p class="judul-formulir">SURAT PERSETUJUAN MEMPELAI</p>

<p class="alinea spasi">Yang bertanda tangan di bawah ini :</p>

@php
    $calonSuami = $pria ? $pemohon : $pasangan;
    $calonIstri = $pria ? $pasangan : $pemohon;
@endphp

<p class="spasi">A. &nbsp;Calon Suami</p>
@include('surat.nikah._orang', ['orang' => $calonSuami, 'binLabel' => 'Bin'])

<p class="spasi">B. &nbsp;Calon Istri</p>
@include('surat.nikah._orang', ['orang' => $calonIstri, 'binLabel' => 'Binti'])

<p class="alinea spasi">Menyatakan dengan sesungguhnya bahwa atas dasar sukarela, dengan kesadaran sendiri, tanpa ada paksaan dari siapapun juga, setuju untuk melangsungkan perkawinan.</p>
<p class="alinea">Demikian surat persetujuan ini dibuat untuk digunakan seperlunya.</p>

<p style="text-align:right;margin-top:8px;">{{ strtoupper($setting->nama_kelurahan) }} , <strong>{{ $tglSurat }}</strong></p>

<table class="ttd-n" style="margin-top:6px;">
    <tr>
        <td style="width:50%;text-align:center;">Calon Suami</td>
        <td style="width:50%;text-align:center;">Calon Istri</td>
    </tr>
    <tr>
        <td style="width:50%;text-align:center;"><p class="ttd-n-nama">{{ $calonSuami['nama'] }}</p></td>
        <td style="width:50%;text-align:center;"><p class="ttd-n-nama">{{ $calonIstri['nama'] }}</p></td>
    </tr>
</table>
