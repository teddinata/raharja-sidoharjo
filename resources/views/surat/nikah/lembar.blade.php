{{--
    Pembungkus paket surat permohonan pernikahan (Model N1-N6 + surat lokal).

    Bentuknya mengikuti formulir resmi: A4 landscape, dua formulir bersebelahan per
    lembar. $forms berisi HTML tiap formulir yang sudah dirender; isinya dipasang
    langsung ke dalam <td> tanpa <div> pembungkus, karena PhpWord (konversi DOCX)
    gagal "Cannot add TextRun in TextRun" bila <div> berisi banyak <p> ditaruh di <td>.
--}}
@php
    $lembar = array_chunk(array_values(array_filter($forms)), 2);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="page-orientation" content="landscape">
    <meta name="page-margin-cm" content="1 1 1 1">
    <style>
        /*
           Reset ditulis per elemen, bukan pakai selector universal: "*" ikut mengenai
           kotak halaman dompdf sehingga margin @page jadi nol dan surat meluber sampai
           tepi kertas. Angka margin yang sama diumumkan lewat <meta name="page-margin-cm">
           supaya DOCX memakai margin identik.
        */
        @page { margin: 1cm; }

        p, table, tbody, tr, td, div, img, h1, h2, h3 { margin: 0; padding: 0; }

        body {
            font-family: Arial, sans-serif;
            font-size: 7.5pt;
            line-height: 1.15;
            color: #000;
        }

        /*
           Lebar kolom ditulis inline di elemennya (lihat <td> di bawah), bukan lewat
           class: selector anak "table > tr" tidak cocok karena parser menyisipkan
           <tbody>, dan lebar dari class juga tidak terbaca saat konversi ke DOCX.
        */
        table.lembar { width: 100%; border-collapse: collapse; }
        table.lembar td { vertical-align: top; }

        .pemisah-lembar { page-break-after: always; }

        /* ── Baris "label : isi" ──────────────────────────────── */
        table.rincian { width: 100%; border-collapse: collapse; }
        table.rincian td {
            vertical-align: top;
            font-family: Arial, sans-serif;
            font-size: 7.5pt;
            line-height: 1.1;
            padding: 0;
        }
        table.rincian td.nomor { width: 6%; }
        table.rincian td.label { width: 48%; }
        table.rincian td.sep   { width: 3%; }
        table.rincian td.value { width: 43%; }

        /* ── Judul & teks ─────────────────────────────────────── */
        .judul-formulir {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            font-size: 9pt;
            margin-bottom: 1px;
        }
        .nomor-formulir { text-align: center; margin-bottom: 6px; }
        .model-formulir { text-align: right; font-size: 6.5pt; line-height: 1.15; }
        .model-formulir-akhir { margin-bottom: 5px; }
        .alinea { margin: 3px 0; text-align: justify; }
        .spasi  { margin-top: 4px; }

        /* ── Kop kalurahan ────────────────────────────────────── */
        table.kop-n { width: 100%; border-collapse: collapse; }
        td.kop-n-logo { width: 52px; vertical-align: middle; }
        td.kop-n-logo img { width: 46px; height: auto; }
        td.kop-n-teks { vertical-align: middle; text-align: center; }
        .kop-n-kab { font-size: 8.5pt; font-weight: bold; line-height: 1.1; }
        .kop-n-kal { font-size: 9.5pt; font-weight: bold; line-height: 1.1; }
        .kop-n-jawa { text-align: center; line-height: 1; }
        .kop-n-jawa img { height: 20px; width: auto; }
        .kop-n-alamat { font-size: 6.5pt; font-style: italic; text-align: center; line-height: 1.15; }
        .kop-n-garis {
            width: 100%;
            border-top: 2px solid #000;
            border-bottom: 1px solid #000;
            padding-bottom: 1px;
            margin: 2px 0 4px;
        }

        /* ── Tanda tangan ─────────────────────────────────────── */
        table.ttd-n { width: 100%; border-collapse: collapse; }
        table.ttd-n td { vertical-align: top; font-size: 7.5pt; }
        .ttd-n-nama { font-weight: bold; margin-top: 18px; }
        .ttd-n-garis { margin-top: 18px; }
    </style>
</head>
<body>

@foreach($lembar as $pasangan)
    <table class="lembar">
        <tr>
            <td class="kolom" style="width:48%;vertical-align:top;">{!! $pasangan[0] !!}</td>
            <td class="selang" style="width:4%;vertical-align:top;"></td>
            <td class="kolom" style="width:48%;vertical-align:top;">{!! $pasangan[1] ?? '' !!}</td>
        </tr>
    </table>
    @if(! $loop->last)
        <div class="pemisah-lembar"></div>
    @endif
@endforeach

</body>
</html>
