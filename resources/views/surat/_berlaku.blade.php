@php
    $berlakuDari   = $extra['berlaku_dari'] ?? null;
    $berlakuSampai = $extra['berlaku_sampai'] ?? null;
@endphp
@if($berlakuDari && $berlakuSampai)
<p class="berlaku">Surat ini berlaku sejak tanggal {{ \Carbon\Carbon::parse($berlakuDari)->translatedFormat('d F Y') }} sampai dengan {{ \Carbon\Carbon::parse($berlakuSampai)->translatedFormat('d F Y') }}</p>
@endif
