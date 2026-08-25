{{-- Surat Keterangan kalurahan (pengiring paket N1-N6) — kolom kiri lembar 1 & 4. --}}
<table class="kop-n">
    <tr>
        <td class="kop-n-logo">
            @if($setting->logo_path && file_exists(storage_path('app/public/' . $setting->logo_path)))
                <img src="{{ storage_path('app/public/' . $setting->logo_path) }}" width="55" style="width:55px;height:auto;">
            @endif
        </td>
        <td class="kop-n-teks">
            <p class="kop-n-kab">KABUPATEN {{ strtoupper($setting->nama_kabupaten) }}</p>
            <p class="kop-n-kab">KAPANEWON {{ strtoupper($setting->nama_kapanewon) }}</p>
            <p class="kop-n-kal">PEMERINTAH KALURAHAN {{ strtoupper($setting->nama_kelurahan) }}</p>
            <p class="kop-n-jawa"><img src="{{ public_path('images/kop-jawa.png') }}" width="180" style="height:26px;width:auto;"></p>
        </td>
    </tr>
</table>

<p class="kop-n-alamat">Alamat : {{ rtrim($setting->alamat ?? '-', ' ,') }}</p>
<p class="kop-n-alamat">Daerah Istimewa Yogyakarta {{ $setting->kode_pos }}</p>

<div class="kop-n-garis"></div>

<p class="judul-formulir">SURAT KETERANGAN</p>

@php
    $barisKeterangan = [
        ['1.',  'Nama lengkap dan alias',   $pemohon['nama']],
        ['2.',  'Jenis Kelamin',            $pemohon['jenis_kelamin']],
        ['3.',  'Tempat dan Tanggal Lahir', $pemohon['ttl']],
        ['4.',  'Kawin/Belum Kawin',        $pemohon['status']],
        ['5.',  'Agama',                    $pemohon['agama']],
        ['6.',  'Pekerjaan',                $pemohon['pekerjaan']],
        ['7.',  'C1/KTP',                   $pemohon['nik']],
        ['8.',  'Alamat',                   $pemohon['alamat']],
        ['9.',  'Pergi ke',                 $keterangan['pergi_ke']],
        ['10.', 'Pengikut',                 $keterangan['pengikut']],
        ['11.', 'Keperluan',                $keterangan['keperluan']],
        ['12.', 'Adat istiadat',            $keterangan['adat']],
        ['13.', 'Keterangan',               $keterangan['lain']],
        ['14.', 'Surat keterangan ini berlaku sampai tanggal', $keterangan['berlaku_sampai']],
    ];
@endphp

<table class="rincian" style="margin-top:4px;">
    @foreach($barisKeterangan as [$no, $label, $nilai])
        <tr>
            <td class="nomor">{{ $no }}</td>
            <td class="label">{{ $label }}</td>
            <td class="sep">:</td>
            <td class="value">{{ $nilai }}</td>
        </tr>
    @endforeach
</table>

<table class="ttd-n" style="margin-top:10px;">
    <tr>
        <td style="width:50%;"></td>
        <td style="width:50%;">{{ $setting->nama_kelurahan }}, {{ $tglSurat }}</td>
    </tr>
    <tr>
        <td style="width:50%;">Tanda tangan</td>
        <td style="width:50%;">{{ $penanda['jabatan'] }}</td>
    </tr>
    <tr>
        <td style="width:50%;">Pemegang Surat</td>
        <td style="width:50%;"></td>
    </tr>
    <tr>
        <td style="width:50%;"><p class="ttd-n-nama">{{ $pemohon['nama'] }}</p></td>
        <td style="width:50%;"><p class="ttd-n-nama">{{ $penanda['nama'] }}</p></td>
    </tr>
</table>

{{--
    Blok "Mengetahui" diletakkan di tengah-tengah antara nama pemegang surat (rata kiri)
    dan nama Lurah (mulai di 50%), bukan di tengah halaman — kalau ditengahkan penuh
    posisinya jatuh tepat di bawah nama Lurah dan terbaca seolah bagian dari tanda
    tangan Lurah. Dibungkus sel supaya perataannya juga terbawa ke DOCX.
--}}
<table style="width:100%;border-collapse:collapse;margin-top:8px;">
    <tr>
        <td style="width:60%;text-align:center;">
            <p>Mengetahui</p>
            <p>Panewu {{ $setting->nama_kapanewon }}</p>
            <p class="ttd-n-garis">……………………………………</p>
        </td>
        <td style="width:40%;"></td>
    </tr>
</table>
