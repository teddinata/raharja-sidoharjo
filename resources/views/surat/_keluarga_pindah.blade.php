{{--
    Daftar keluarga yang ikut pindah. Dipakai Surat Pengantar Pindah Penduduk dan
    Surat Pernyataan Pindah.

    Anggota diambil dari kartu keluarga pemohon (lihat Penduduk::serumah()) lalu
    disaring sesuai pilihan petugas saat membuat surat, dengan NIK dipecah satu digit
    per kotak. Baris sisanya dibiarkan kosong supaya bisa ditambah tangan.

    Butuh $p (penduduk pemohon) dan $extra (data_tambahan surat).

    Gaya ditulis inline, bukan lewat class di layout: layout dipakai bersama 38 jenis
    surat lain, dan lebar/garis dari class tidak terbaca saat konversi ke DOCX.
--}}
@php
    $garis  = 'border:1px #000000 solid;';
    $tinggi = 'height:22px;';
    $baris  = $jumlahBaris ?? 5;

    // Keberadaan key-nya yang jadi penanda, bukan isinya: surat lama (dibuat sebelum
    // pilihan ini ada) sama sekali tidak menyimpan key ini, jadi jatuh ke perilaku
    // sebelumnya — seluruh anggota kartu keluarga. Key yang ada tapi kosong berarti
    // petugas memang tidak memilih siapa pun, dan tabelnya dibiarkan kosong.
    $anggota = $p->serumah();

    if (array_key_exists('anggota_pindah', $extra)) {
        $nikDipilih = array_filter(array_map('trim', explode(',', (string) $extra['anggota_pindah'])));
        $anggota    = $anggota->whereIn('nik', $nikDipilih)->values();
    }

    $anggota = $anggota->take($baris)->values();
@endphp

<p style="font-weight:bold;margin-top:14px;margin-bottom:4px;">Keluarga yang Pindah</p>

<table style="width:100%;border-collapse:collapse;">
    <tr>
        <td style="width:6%;{{ $garis }}text-align:center;font-weight:bold;">NO</td>
        <td colspan="16" style="width:68%;{{ $garis }}text-align:center;font-weight:bold;">NIK</td>
        <td style="width:26%;{{ $garis }}text-align:center;font-weight:bold;">NAMA</td>
    </tr>
    @for($i = 0; $i < $baris; $i++)
        @php
            $orang = $anggota[$i] ?? null;
            // NIK selalu diratakan ke 16 digit supaya sejajar dengan jumlah kotak.
            $digit = str_split(str_pad(substr((string) ($orang->nik ?? ''), 0, 16), 16));
        @endphp
        <tr>
            <td style="width:6%;{{ $garis }}{{ $tinggi }}text-align:center;">{{ $i + 1 }}</td>
            @foreach($digit as $d)
                <td style="width:4.25%;{{ $garis }}{{ $tinggi }}text-align:center;">{{ $d === ' ' ? '' : $d }}</td>
            @endforeach
            <td style="width:26%;{{ $garis }}{{ $tinggi }}">{{ $orang->nama_lengkap ?? '' }}</td>
        </tr>
    @endfor
</table>
