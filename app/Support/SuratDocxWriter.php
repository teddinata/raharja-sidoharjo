<?php

namespace App\Support;

use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\SimpleType\TblWidth;

/**
 * Membangun DOCX surat dari HTML blade yang sama dengan PDF.
 *
 * Selain konversi HTML-nya (lihat WordHtmlFragment), ada beberapa default PhpWord yang
 * harus ditimpa di level dokumen supaya hasilnya sama dengan PDF — kalau tidak, DOCX
 * memakai ukuran halaman/margin/font bawaan PhpWord dan surat jadi bergeser serta
 * berbeda titik pindah barisnya.
 */
class SuratDocxWriter
{
    /** A4, sama dengan setPaper('a4','portrait') di sisi PDF. */
    private const PAGE_WIDTH_CM  = 21.0;
    private const PAGE_HEIGHT_CM = 29.7;

    /**
     * Margin efektif PDF = margin halaman bawaan dompdf (1,2cm) + padding body di
     * layout.blade.php (0,6cm atas / 2cm samping / 1,5cm bawah).
     */
    private const MARGIN_TOP_CM    = 1.8;
    private const MARGIN_RIGHT_CM  = 3.2;
    private const MARGIN_BOTTOM_CM = 2.7;
    private const MARGIN_LEFT_CM   = 3.2;

    /** Sama dengan body di layout.blade.php. */
    private const FONT_NAME = 'Arial';
    private const FONT_SIZE = 11;

    public static function save(string $html, string $path): void
    {
        $phpWord = new PhpWord();

        // Tanpa ini PhpWord memakai 10pt, sehingga setiap elemen yang gayanya tidak
        // terbaca parser CSS-nya tampil lebih kecil dari PDF.
        $phpWord->setDefaultFontName(self::FONT_NAME);
        $phpWord->setDefaultFontSize(self::FONT_SIZE);

        $section = $phpWord->addSection([
            'pageSizeW'    => Converter::cmToTwip(self::PAGE_WIDTH_CM),
            'pageSizeH'    => Converter::cmToTwip(self::PAGE_HEIGHT_CM),
            'marginTop'    => Converter::cmToTwip(self::MARGIN_TOP_CM),
            'marginRight'  => Converter::cmToTwip(self::MARGIN_RIGHT_CM),
            'marginBottom' => Converter::cmToTwip(self::MARGIN_BOTTOM_CM),
            'marginLeft'   => Converter::cmToTwip(self::MARGIN_LEFT_CM),
        ]);

        Html::addHtml($section, WordHtmlFragment::prepare($html), false, false);

        self::normalizeTables($section->getElements());

        IOFactory::createWriter($phpWord, 'Word2007')->save($path);
    }

    /**
     * PhpWord memberi setiap sel tabel noWrap = true (default library-nya, tidak bisa
     * diatur lewat HTML), sehingga isi sel yang panjang — alamat, misalnya — tidak pernah
     * dibungkus dan malah melebarkan tabel keluar dari margin. Lebar kolom yang sudah
     * ditetapkan juga baru dipatuhi Word kalau layout tabelnya fixed.
     */
    private static function normalizeTables(array $elements): void
    {
        foreach ($elements as $element) {
            if (! $element instanceof Table) {
                if (method_exists($element, 'getElements')) {
                    self::normalizeTables($element->getElements());
                }

                continue;
            }

            $style = $element->getStyle();
            if ($style) {
                $style->setLayout(\PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED);
                if (! $style->getWidth()) {
                    $style->setWidth(100 * 50);
                    $style->setUnit(TblWidth::PERCENT);
                }
            }

            foreach ($element->getRows() as $row) {
                foreach ($row->getCells() as $cell) {
                    $cell->getStyle()->setNoWrap(false);
                    self::normalizeTables($cell->getElements());
                }
            }
        }
    }
}
