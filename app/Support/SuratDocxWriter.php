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
    /** Sama dengan body di layout.blade.php. */
    private const FONT_NAME = 'Arial';
    private const FONT_SIZE = 11;

    public static function save(string $html, string $path): void
    {
        $page    = SuratPageSetup::fromHtml($html);
        $phpWord = new PhpWord();

        // Tanpa ini PhpWord memakai 10pt, sehingga setiap elemen yang gayanya tidak
        // terbaca parser CSS-nya tampil lebih kecil dari PDF.
        $phpWord->setDefaultFontName(self::FONT_NAME);
        $phpWord->setDefaultFontSize(self::FONT_SIZE);

        [$top, $right, $bottom, $left] = $page->marginCm;

        $section = $phpWord->addSection([
            'orientation'  => $page->orientation,
            'pageSizeW'    => Converter::cmToTwip($page->widthCm),
            'pageSizeH'    => Converter::cmToTwip($page->heightCm),
            'marginTop'    => Converter::cmToTwip($top),
            'marginRight'  => Converter::cmToTwip($right),
            'marginBottom' => Converter::cmToTwip($bottom),
            'marginLeft'   => Converter::cmToTwip($left),
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
