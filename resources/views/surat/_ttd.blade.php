<div class="ttd">
    <p>{{ $setting->nama_kelurahan }}, {{ now()->translatedFormat('d F Y') }}</p>
    <p>{{ $ttd?->jabatan ?? 'Lurah '.$setting->nama_kelurahan }}</p>

    @php
        $ttdPath = $ttd?->ttd_image_path ?? $setting->ttd_lurah_path;
        $ttdAbsPath = $ttdPath ? storage_path('app/public/' . $ttdPath) : null;
    @endphp

    @if($ttdAbsPath && file_exists($ttdAbsPath))
        <img class="ttd-image"
             src="data:image/png;base64,{{ base64_encode(file_get_contents($ttdAbsPath)) }}">
    @else
        <br>
    @endif

    <p class="nama">{{ $ttd?->atas_nama ?? $setting->nama_lurah ?? '................................' }}</p>
    @if($ttd?->nip)<p>NIP. {{ $ttd->nip }}</p>@endif
</div>