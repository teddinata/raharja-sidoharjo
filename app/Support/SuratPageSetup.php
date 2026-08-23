<?php

namespace App\Support;

/**
 * Ukuran & margin halaman surat, dibaca dari <meta> di template blade supaya sisi PDF
 * dan sisi DOCX memakai angka yang sama persis (kalau tidak, dua keluaran itu memakai
 * default masing-masing dan hasilnya bergeser).
 *
 * Template yang tidak menyatakan apa pun tetap memakai default lama: A4 potrait dengan
 * margin efektif 1,8 / 3,2 / 2,7 / 3,2 cm.
 */
class SuratPageSetup
{
    public const A4_WIDTH_CM  = 21.0;
    public const A4_HEIGHT_CM = 29.7;

    /**
     * [atas, kanan, bawah, kiri] dalam cm — sama dengan padding body di layout.blade.php.
     *
     * Bukan padding + margin halaman bawaan dompdf: selector universal "* { margin: 0 }"
     * di layout itu ikut menihilkan margin halaman, jadi jarak isi ke tepi kertas murni
     * berasal dari padding body. Diverifikasi dengan mengukur PDF hasil render
     * (kiri 1,96cm, kanan 2,00cm, atas 0,74cm).
     */
    private const DEFAULT_MARGIN_CM = [0.6, 2.0, 1.5, 2.0];

    public function __construct(
        public readonly string $orientation,
        public readonly float $widthCm,
        public readonly float $heightCm,
        public readonly array $marginCm,
    ) {
    }

    public static function fromHtml(string $html): self
    {
        $orientation = self::meta($html, 'page-orientation') === 'landscape' ? 'landscape' : 'portrait';

        $margin = self::meta($html, 'page-margin-cm');
        $margin = $margin ? array_map('floatval', preg_split('/[\s,]+/', trim($margin))) : self::DEFAULT_MARGIN_CM;
        if (count($margin) !== 4) {
            $margin = self::DEFAULT_MARGIN_CM;
        }

        [$width, $height] = $orientation === 'landscape'
            ? [self::A4_HEIGHT_CM, self::A4_WIDTH_CM]
            : [self::A4_WIDTH_CM, self::A4_HEIGHT_CM];

        return new self($orientation, $width, $height, $margin);
    }

    private static function meta(string $html, string $name): ?string
    {
        return preg_match('/<meta\s+name="' . preg_quote($name, '/') . '"\s+content="([^"]*)"/i', $html, $m)
            ? $m[1]
            : null;
    }
}
