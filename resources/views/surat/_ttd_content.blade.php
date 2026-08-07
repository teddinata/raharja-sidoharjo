<p style="text-align:right;">{{ $setting->nama_kelurahan }}, {{ now()->translatedFormat('d F Y') }}</p>
<p class="jabatan" style="text-align:right;">{{ ($ttd ? $ttd->jabatan : null) ?? 'Lurah '.$setting->nama_kelurahan }}</p>

@php
    // $ttd null sepenuhnya (surat lama tanpa record ttd) -> fallback ke Lurah.
    // $ttd ada (baik dari surat maupun override Carik) -> pakai data miliknya sendiri, jangan dicampur.
    $ttdPath = $ttd ? $ttd->ttd_image_path : $setting->ttd_lurah_path;
    $ttdAbsPath = $ttdPath ? storage_path('app/public/' . $ttdPath) : null;
@endphp

@if($ttdAbsPath && file_exists($ttdAbsPath))
    <p style="text-align:right;margin:4px 0;"><img class="ttd-image" width="120"
         src="data:image/png;base64,{{ base64_encode(file_get_contents($ttdAbsPath)) }}"></p>
@else
    <br>
@endif

<p class="nama" style="text-align:right;font-weight:bold;text-decoration:underline;">{{ ($ttd ? $ttd->atas_nama : $setting->nama_lurah) ?? '................................' }}</p>
@if($ttd?->nip)<p style="text-align:right;">NIP. {{ $ttd->nip }}</p>@endif
