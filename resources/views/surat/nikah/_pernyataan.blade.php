{{-- Surat Pernyataan Belum Pernah Menikah (formulir lokal, bermaterai + saksi). --}}
<p class="judul-formulir">SURAT PERNYATAAN BELUM PERNAH MENIKAH/ {{ $pria ? 'PERJAKA' : 'PERAWAN' }}</p>

<p class="alinea spasi">Yang bertanda tangan di bawah ini :</p>

@include('surat.nikah._orang', ['orang' => $pemohon, 'binLabel' => $pria ? 'Bin' : 'Binti'])

<p class="alinea spasi">Dengan ini menyatakan dengan sebenar-benarnya bahwa sampai dengan saat ini belum pernah menikah dengan siapapun baik secara adat maupun tercatat di Kantor Urusan Agama dan atau Kantor Catatan Sipil.</p>

<p class="alinea">Demikian surat pernyataan ini saya buat dalam keadaan sehat jasmani maupun rohani, serta tanpa adanya paksaan dari maupun pengaruh dari pihak manapun.</p>

<p class="alinea">Apabila dikemudian hari ternyata surat pernyataan ini tidak benar, ataupun ada pihak yang merasa dirugikan saya sanggup mempertanggungjawabkan sendiri tanpa melibatkan aparat/ orang lain yang turut menjadi saksi dalam surat ini.</p>

<p style="text-align:right;margin-top:8px;">{{ $setting->nama_kelurahan }}, <strong>{{ $tglSurat }}</strong></p>

<table class="ttd-n" style="margin-top:6px;">
    <tr>
        <td style="width:35%;"><span style="text-decoration:underline;">Saksi-saksi:</span></td>
        <td style="width:30%;"></td>
        <td style="width:35%;text-align:center;">Yang menyatakan</td>
    </tr>
    <tr>
        <td style="width:35%;text-align:center;">Dukuh {{ $p->pedukuhan ?? '……………' }}</td>
        <td style="width:30%;text-align:center;">Ketua RT</td>
        <td style="width:35%;text-align:center;"><p style="font-style:italic;font-size:7pt;margin-top:8px;">materai<br>10000</p></td>
    </tr>
    <tr>
        <td style="width:35%;text-align:center;"><p class="ttd-n-nama">{{ $e['saksi_dukuh'] ?? '………………………' }}</p></td>
        <td style="width:30%;text-align:center;"><p class="ttd-n-nama">{{ $p->nama_ketua_rt ?: '……………….' }}</p></td>
        <td style="width:35%;text-align:center;"><p class="ttd-n-nama">{{ $pemohon['nama'] }}</p></td>
    </tr>
</table>

<p style="text-align:center;margin-top:8px;">Mengetahui</p>
<p style="text-align:center;">{{ $penanda['jabatan'] }}</p>
<p style="text-align:center;" class="ttd-n-nama">{{ $penanda['nama'] }}</p>
