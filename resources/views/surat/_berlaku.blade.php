@php
    $diterbitkan   = $surat->diterbitkan_at ?? $surat->created_at;
    $berlakuDari   = $extra['berlaku_dari']   ?? $diterbitkan?->toDateString();
    $berlakuSampai = $extra['berlaku_sampai'] ?? ($diterbitkan ? $diterbitkan->copy()->addMonths(3)->toDateString() : null);
@endphp
@if($berlakuDari && $berlakuSampai)
<p class="berlaku">Surat ini berlaku sejak tanggal {{ \Carbon\Carbon::parse($berlakuDari)->translatedFormat('d F Y') }} sampai dengan {{ \Carbon\Carbon::parse($berlakuSampai)->translatedFormat('d F Y') }}</p>
@endif
