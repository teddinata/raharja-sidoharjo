<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        @font-face {
            font-family: 'Arial';
            src: url('{{ public_path("fonts/Arial.ttf") }}');
            font-weight: normal;
            font-style: normal;
        }
        @font-face {
            font-family: 'Arial';
            src: url('{{ public_path("fonts/Arial-Bold.ttf") }}');
            font-weight: bold;
            font-style: normal;
        }
        @font-face {
            font-family: 'NgayogyanJawa';
            src: url('{{ public_path("fonts/NgayogyanNewItalic.ttf") }}');
            font-weight: normal;
            font-style: normal;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            color: #000;
            padding: 0.6cm 2cm 1.5cm 2cm;
        }

        /* ── KOP ──────────────────────────────────────────────── */
        table.kop {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        td.kop-logo-cell {
            width: 108px;
            vertical-align: top;
            padding-right: 10px;
        }
        td.kop-logo-cell img {
            width: 103px;
            height: auto;
        }
        td.kop-teks-cell {
            vertical-align: top;
            text-align: center;
        }
        .kop-kab {
            font-family: Arial, sans-serif;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.05;
        }
        .kop-kap {
            font-family: Arial, sans-serif;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.05;
        }
        .kop-kal {
            font-family: Arial, sans-serif;
            font-size: 15pt;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.05;
        }
        .kop-jawa {
            font-family: 'NgayogyanJawa', serif;
            font-size: 12pt;
            line-height: 1;
            margin-top: -10px;
            text-align: center;
        }
        .kop-alamat {
            font-family: Arial, sans-serif;
            font-size: 8pt;
            line-height: 1.1;
            margin-top: 0;
            text-align: center;
        }

        /* Separator garis tebal + tipis (double line) */
        .kop-separator {
            width: 100%;
            border-top: 3px solid #000;
            border-bottom: 1px solid #000;
            padding-bottom: 1px;
            margin-top: 2px;
            margin-bottom: 4px;
        }

        /* ── JUDUL SURAT ──────────────────────────────────────── */
        .judul {
            text-align: center;
            margin: 10px 0 6px;
        }
        .judul h3 {
            font-family: Arial, sans-serif;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
        }
        .judul p {
            font-family: Arial, sans-serif;
            font-size: 11pt;
        }

        /* ── ISI ─────────────────────────────────────────────── */
        .isi {
            margin-top: 8px;
            line-height: 1.5;
            font-family: Arial, sans-serif;
            font-size: 11pt;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0;
        }
        table.data td {
            padding: 1px 4px;
            vertical-align: top;
            font-family: Arial, sans-serif;
            font-size: 11pt;
        }
        table.data td.label { width: 38%; }
        table.data td.sep   { width: 4%;  text-align: center; }
        table.data td.value { width: 58%; }

        /* ── PENUTUP & TTD ───────────────────────────────────── */
        .penutup {
            margin-top: 10px;
            line-height: 1.5;
            font-family: Arial, sans-serif;
            font-size: 11pt;
        }
        .ttd {
            margin-top: 16px;
            text-align: right;
            line-height: 1.5;
            font-family: Arial, sans-serif;
            font-size: 11pt;
            page-break-inside: avoid;
        }
        .ttd .nama {
            margin-top: 30px;
            font-weight: bold;
            text-decoration: underline;
        }
        .ttd-image {
            max-width: 120px;
            max-height: 60px;
            display: block;
            margin: 4px 0;
        }
    </style>
</head>
<body>

{{-- ── KOP SURAT ─────────────────────────────────────────────────────── --}}
<table class="kop">
    <tr>
        {{-- Logo --}}
        <td class="kop-logo-cell">
            @if($setting->logo_path && file_exists(storage_path('app/public/' . $setting->logo_path)))
                <img src="{{ storage_path('app/public/' . $setting->logo_path) }}">
            @endif
        </td>

        {{-- Teks kop --}}
        <td class="kop-teks-cell">
            <p class="kop-kab">KABUPATEN {{ strtoupper($setting->nama_kabupaten) }}</p>
            <p class="kop-kap">KAPANEWON {{ strtoupper($setting->nama_kapanewon) }}</p>
            <p class="kop-kal">PEMERINTAH KALURAHAN {{ strtoupper($setting->nama_kelurahan) }}</p>
            <p class="kop-jawa">ꦥꦼꦩꦼꦫꦶꦤ꧀ꦠꦃꦏꦭꦸꦫꦲꦤ꧀ꦱꦶꦢꦲꦂꦗ</p>
            @php
                $alamat  = rtrim($setting->alamat ?? '-', ' ,');
                $kontak  = [];
                if ($setting->email)   $kontak[] = 'Email : ' . $setting->email;
                if ($setting->website) $kontak[] = 'Website : ' . $setting->website;
            @endphp
            <p class="kop-alamat">Alamat : {{ $alamat }}</p>
            @if(count($kontak))
            <p class="kop-alamat" style="margin-top:0;">{{ implode('  |  ', $kontak) }}</p>
            @endif
        </td>
    </tr>
</table>

<div class="kop-separator"></div>

@yield('content')

</body>
</html>
