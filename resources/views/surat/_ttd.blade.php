{{--
    $noWrapper: pakai true saat partial ini ditaruh di dalam <td> (mis. layout tanda tangan 2 kolom).
    PhpWord (konversi DOCX) gagal "Cannot add TextRun in TextRun" kalau <div> berisi banyak <p>
    ditaruh langsung di dalam <td>, jadi untuk konteks itu tag <div>-nya dilewati sepenuhnya.
--}}
@if($noWrapper ?? false)
    @include('surat._ttd_content', ['ttd' => $ttd, 'setting' => $setting])
@else
<div class="ttd">
    @include('surat._ttd_content', ['ttd' => $ttd, 'setting' => $setting])
</div>
@endif
