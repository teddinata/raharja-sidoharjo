{{--
    Paket surat permohonan pernikahan mengikuti Keputusan Dirjen Bimas Islam
    Nomor 473 Tahun 2020: Surat Keterangan (kalurahan) + N1, N2, N4, N5, N6, serta
    Surat Pernyataan Belum Pernah Menikah.

    Dipakai bersama oleh nikah_l (calon suami) dan nikah_p (calon istri) lewat $jk.
    N5 & N6 hanya ikut kalau dicentang petugas — keduanya memang tidak selalu relevan
    (N5 untuk yang belum 21 tahun, N6 untuk duda/janda cerai mati).
--}}
@php
    use App\Support\DataSuratNikah;

    $ctx  = DataSuratNikah::untuk($surat, $setting, $jk);
    $pria = $jk === 'L';

    $forms = [
        view('surat.nikah._surat_keterangan', $ctx)->render(),
        view('surat.nikah._n1', $ctx)->render(),
        view('surat.nikah._n2', $ctx)->render(),
        view('surat.nikah._n4', $ctx)->render(),
    ];

    if (DataSuratNikah::dicentang($surat, 'sertakan_n5')) {
        $forms[] = view('surat.nikah._n5', $ctx)->render();
    }
    if (DataSuratNikah::dicentang($surat, 'sertakan_n6')) {
        $forms[] = view('surat.nikah._n6', $ctx)->render();
    }

    // Surat Keterangan kedua: calon suami ke KUA lagi, calon istri ke Puskesmas
    // untuk imunisasi TT.
    $forms[] = view('surat.nikah._surat_keterangan', array_merge($ctx, [
        'keterangan' => $ctx['keterangan2'],
    ]))->render();

    // Pernyataan belum menikah lazimnya menyertai berkas calon suami, sedangkan
    // keterangan wali nikah hanya relevan untuk calon istri — keduanya tetap bisa
    // diubah lewat centang petugas.
    if (DataSuratNikah::dicentang($surat, 'sertakan_pernyataan', $pria)) {
        $forms[] = view('surat.nikah._pernyataan', $ctx)->render();
    }
    if (DataSuratNikah::dicentang($surat, 'sertakan_wali', ! $pria)) {
        $forms[] = view('surat.nikah._wali_nikah', $ctx)->render();
    }
@endphp

@include('surat.nikah.lembar', ['forms' => $forms])
