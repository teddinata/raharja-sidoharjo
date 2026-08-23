{{--
    Daftar keluarga yang ikut pindah — kolom NIK berupa kotak per digit supaya bisa
    diisi tangan setelah surat dicetak.

    Gaya ditulis inline, bukan lewat class di layout: layout dipakai bersama 38 jenis
    surat lain, dan lebar/garis dari class tidak terbaca saat konversi ke DOCX.
--}}
@php
    $garis  = 'border:1px #000000 solid;';
    $baris  = $jumlahBaris ?? 5;
    $tinggi = 'height:22px;';
@endphp

<p style="font-weight:bold;margin-top:14px;margin-bottom:4px;">Keluarga yang Pindah</p>

<table style="width:100%;border-collapse:collapse;">
    <tr>
        <td style="width:6%;{{ $garis }}text-align:center;font-weight:bold;">NO</td>
        <td colspan="16" style="width:68%;{{ $garis }}text-align:center;font-weight:bold;">NIK</td>
        <td style="width:26%;{{ $garis }}text-align:center;font-weight:bold;">NAMA</td>
    </tr>
    @for($i = 1; $i <= $baris; $i++)
        <tr>
            <td style="width:6%;{{ $garis }}{{ $tinggi }}text-align:center;">{{ $i }}</td>
            @for($d = 0; $d < 16; $d++)
                <td style="width:4.25%;{{ $garis }}{{ $tinggi }}">&nbsp;</td>
            @endfor
            <td style="width:26%;{{ $garis }}{{ $tinggi }}">&nbsp;</td>
        </tr>
    @endfor
</table>
