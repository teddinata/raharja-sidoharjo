<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            color: #000;
            padding: 1.5cm 2cm;
        }
        .kop {
            display: flex;
            align-items: center;
            border-bottom: 3px double #000;
            padding-bottom: 8px;
            margin-bottom: 16px;
        }
        .kop-logo {
            width: 80px;
            margin-right: 16px;
        }
        .kop-teks { text-align: center; flex: 1; }
        .kop-teks h1 { font-size: 15pt; text-transform: uppercase; }
        .kop-teks h2 { font-size: 13pt; text-transform: uppercase; }
        .kop-teks p  { font-size: 10pt; }
        .judul {
            text-align: center;
            margin: 16px 0 8px;
        }
        .judul h3 {
            font-size: 13pt;
            text-transform: uppercase;
            text-decoration: underline;
        }
        .judul p { font-size: 11pt; }
        .isi { margin-top: 16px; line-height: 1.8; }
        table.data { width: 100%; border-collapse: collapse; margin: 8px 0; }
        table.data td { padding: 2px 4px; vertical-align: top; font-size: 11pt; }
        table.data td.label { width: 38%; }
        table.data td.sep   { width: 4%; text-align: center; }
        table.data td.value { width: 58%; }
        .ttd {
            margin-top: 32px;
            text-align: right;
            line-height: 1.8;
        }
        .ttd .nama {
            margin-top: 70px;
            font-weight: bold;
            text-decoration: underline;
        }
        .ttd-image {
            max-width: 120px;
            max-height: 60px;
            display: block;
            margin: 4px 0;
        }
        .penutup { margin-top: 16px; line-height: 1.8; }
    </style>
</head>
<body>

{{-- KOP SURAT --}}
<div class="kop">
    @if($setting->logo_path && file_exists(storage_path('app/public/' . $setting->logo_path)))
        <img class="kop-logo" src="{{ storage_path('app/public/' . $setting->logo_path) }}">
    @endif
    <div class="kop-teks">
        <h2>Pemerintah Kabupaten {{ $setting->nama_kabupaten }}</h2>
        <h2>Kapanewon {{ $setting->nama_kapanewon }}</h2>
        <h1>Kalurahan {{ $setting->nama_kelurahan }}</h1>
        @if($setting->alamat)
            <p>{{ $setting->alamat }}
            @if($setting->telepon) &nbsp;|&nbsp; Telp. {{ $setting->telepon }} @endif
            </p>
        @endif
    </div>
</div>

@yield('content')

</body>
</html>