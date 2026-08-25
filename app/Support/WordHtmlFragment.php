<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMText;
use DOMXPath;

class WordHtmlFragment
{
    /**
     * Ubah HTML surat (hasil render blade yang sama dipakai untuk PDF) jadi fragment
     * yang bisa dimengerti PhpWord\Shared\Html::addHtml().
     *
     * Parser HTML/CSS PhpWord jauh lebih sederhana dari dompdf, jadi beberapa hal yang
     * "gratis" di PDF harus dituliskan ulang secara eksplisit di sini supaya DOCX tampil
     * sama dengan PDF:
     *
     *  - Html::addHtml() perlu XML well-formed (loadXML), bukan HTML5 biasa -> di-normalisasi
     *    lewat DOMDocument::loadHTML (toleran) lalu diserialisasi ulang jadi XML.
     *  - Parser CSS-nya tidak membuang komentar, tidak mendukung selector turunan
     *    (".judul h3") maupun selector gabungan tag+class ("table.data"), sehingga lebar
     *    kolom & perataan yang di PDF datang dari CSS harus ditempelkan sebagai atribut /
     *    inline style per elemen.
     *  - PhpWord tidak merapatkan spasi seperti HTML: baris blade yang dipotong ke beberapa
     *    baris akan muncul apa adanya (spasi ganda) di Word kalau tidak dirapatkan di sini.
     *  - text-align pada <td> tidak diturunkan ke <p> di dalamnya.
     *  - Gambar ditulis sebagai VML (<w:pict>), yang penanganan transparansi PNG-nya tidak
     *    dapat diandalkan di Word -> gambar diratakan dulu ke latar putih.
     */
    public static function prepare(string $html, ?SuratPageSetup $page = null): string
    {
        $page ??= SuratPageSetup::fromHtml($html);
        $css   = self::extractStyle($html);
        $dom   = self::loadDom($html);
        $xpath = new DOMXPath($dom);

        self::splitJabatanLines($dom, $xpath);
        self::collapseWhitespace($xpath);
        self::replaceTitleHeading($dom, $xpath);
        self::replacePageBreaks($dom, $xpath);
        self::replaceKopSeparator($dom, $xpath);
        self::applyTableWidths($xpath, self::contentWidthPx($page));
        self::pushCellAlignToChildren($xpath, $css);
        self::openSignatureGap($xpath);
        self::flattenImagesOnWhite($xpath);

        return '<style>' . $css . '</style>' . self::serializeBody($dom);
    }

    /**
     * Lebar area isi halaman dalam piksel CSS 96dpi. Lebar tabel ditulis dalam satuan
     * absolut, bukan persen: PhpWord selalu menulis <w:tblGrid> bersatuan twip, jadi
     * lebar persen menghasilkan angka grid yang salah dan Word menghitung kolomnya
     * jauh lebih sempit dari seharusnya.
     */
    private static function contentWidthPx(SuratPageSetup $page): int
    {
        [, $right, , $left] = $page->marginCm;

        return (int) round(($page->widthCm - $left - $right) / 2.54 * 96);
    }

    /**
     * Proporsi kolom per jenis tabel — angkanya harus sama dengan CSS di template PDF.
     *
     * Dipetakan berdasarkan class tabel induknya, bukan class selnya saja: nama class
     * "label"/"sep"/"value" dipakai oleh tabel data surat biasa maupun tabel rincian
     * formulir nikah dengan lebar yang berbeda.
     */
    private const COL_PERCENT = [
        'data'    => ['label' => 38, 'sep' => 4, 'value' => 58],
        'rincian' => ['nomor' => 6, 'label' => 48, 'sep' => 3, 'value' => 43],
    ];

    /** Lebar sel logo kop, sama dengan PDF. */
    private const KOP_LOGO_WIDTH_PX = 108;

    private static function extractStyle(string $html): string
    {
        preg_match('/<style>(.*?)<\/style>/s', $html, $match);
        $css = $match[1] ?? '';

        // Buang komentar CSS supaya tidak menempel ke selector berikutnya.
        return preg_replace('#/\*.*?\*/#s', '', $css);
    }

    private static function loadDom(string $html): DOMDocument
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8"?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        return $dom;
    }

    private static function serializeBody(DOMDocument $dom): string
    {
        $body = $dom->getElementsByTagName('body')->item(0);
        if (! $body) {
            return '';
        }

        $xml = '';
        foreach ($body->childNodes as $child) {
            $xml .= $dom->saveXML($child);
        }

        return $xml;
    }

    /** XPath helper: cocokkan satu class di antara daftar class milik elemen. */
    private static function hasClass(string $tag, string $class): string
    {
        return "//{$tag}[contains(concat(' ', normalize-space(@class), ' '), ' {$class} ')]";
    }

    /**
     * Jabatan bisa berisi newline (mis. "An Lurah Sidoharjo\nCarik"). Di PDF newline itu
     * tampil karena white-space:pre-line; di Word harus jadi <br/> eksplisit — dan harus
     * dilakukan sebelum spasi dirapatkan.
     */
    private static function splitJabatanLines(DOMDocument $dom, DOMXPath $xpath): void
    {
        foreach ($xpath->query(self::hasClass('p', 'jabatan')) as $p) {
            $lines = preg_split('/\R/u', trim($p->textContent));
            if (count($lines) < 2) {
                continue;
            }

            while ($p->firstChild) {
                $p->removeChild($p->firstChild);
            }

            foreach ($lines as $i => $line) {
                if ($i > 0) {
                    $p->appendChild($dom->createElement('br'));
                }
                $p->appendChild($dom->createTextNode(trim($line)));
            }
        }
    }

    /**
     * Rapatkan spasi seperti yang dilakukan HTML: kalimat panjang di blade sering dipotong
     * ke beberapa baris, dan tanpa ini indentasinya ikut tercetak jadi spasi ganda di Word.
     */
    private static function collapseWhitespace(DOMXPath $xpath): void
    {
        $blockParents = ['html', 'body', 'div', 'table', 'thead', 'tbody', 'tfoot', 'tr', 'ul', 'ol'];

        $texts = [];
        foreach ($xpath->query('//text()') as $text) {
            $texts[] = $text;
        }

        foreach ($texts as $text) {
            $collapsed = preg_replace('/\s+/u', ' ', $text->nodeValue);

            if (trim($collapsed) === '' && in_array($text->parentNode->nodeName, $blockParents, true)) {
                $text->parentNode->removeChild($text);

                continue;
            }

            $text->nodeValue = $collapsed;
        }

        // Buang spasi menggantung di awal/akhir elemen teks.
        foreach ($xpath->query('//p|//h1|//h2|//h3|//h4|//h5|//h6|//td|//li') as $el) {
            if ($el->firstChild instanceof DOMText) {
                $el->firstChild->nodeValue = ltrim($el->firstChild->nodeValue);
            }
            if ($el->lastChild instanceof DOMText) {
                $el->lastChild->nodeValue = rtrim($el->lastChild->nodeValue);
            }
        }
    }

    /**
     * <h3> dipetakan PhpWord ke paragraph style "Heading3" bawaan Word (font & warna
     * sendiri), sehingga judul bisa tampil beda dari PDF. Ditukar jadi <p> bergaya
     * eksplisit supaya identik: Arial 12pt, bold, underline, uppercase, rata tengah.
     */
    private static function replaceTitleHeading(DOMDocument $dom, DOMXPath $xpath): void
    {
        $headings = [];
        foreach ($xpath->query('//h3') as $h3) {
            $headings[] = $h3;
        }

        foreach ($headings as $h3) {
            $p = $dom->createElement('p');
            $p->appendChild($dom->createTextNode(mb_strtoupper(trim($h3->textContent), 'UTF-8')));
            $p->setAttribute('style', 'font-family:Arial;font-size:12pt;font-weight:bold;text-decoration:underline;text-align:center;');
            $h3->parentNode->replaceChild($p, $h3);
        }
    }

    /**
     * Lebar tabel & kolom di PDF datang dari selector "table.data" / "td.label" yang tidak
     * dikenali parser CSS PhpWord, akibatnya semua tabel autofit ke isinya dan lebarnya
     * jadi berbeda-beda. Ditulis ulang sebagai atribut width yang memang dibaca PhpWord.
     */
    /**
     * Di PDF semua tabel width:100%; tanpa lebar eksplisit PhpWord meng-autofit ke isi
     * sehingga tiap tabel berakhir dengan lebar berbeda-beda.
     *
     * Ditelusuri dalam urutan dokumen (induk selalu didahulukan) supaya tabel/sel yang
     * bersarang bisa menghitung lebarnya dari induk yang sudah ditetapkan, bukan dari
     * lebar halaman.
     */
    private static function applyTableWidths(DOMXPath $xpath, int $contentWidthPx): void
    {
        foreach ($xpath->query('//table|//td') as $el) {
            $available = self::availableWidthPx($el, $contentWidthPx);

            if ($el->nodeName === 'table') {
                self::setWidthPx($el, $available);

                continue;
            }

            $classes = preg_split('/\s+/', trim($el->getAttribute('class'))) ?: [];

            // Lebar persen inline paling spesifik, jadi didahulukan (mis. layout tanda
            // tangan dua kolom dan kolom lembar formulir nikah).
            if (preg_match('/width\s*:\s*([0-9.]+)%/i', $el->getAttribute('style'), $m)) {
                self::setWidthPx($el, (int) round($available * (float) $m[1] / 100));

                continue;
            }

            if (in_array('kop-logo-cell', $classes, true)) {
                self::setWidthPx($el, self::KOP_LOGO_WIDTH_PX);

                continue;
            }

            if (in_array('kop-teks-cell', $classes, true)) {
                self::setWidthPx($el, $available - self::KOP_LOGO_WIDTH_PX);

                continue;
            }

            $kolom = self::COL_PERCENT[self::tableKind($el)] ?? [];

            foreach ($kolom as $class => $percent) {
                if (in_array($class, $classes, true)) {
                    self::setWidthPx($el, (int) round($available * $percent / 100));

                    break;
                }
            }
        }
    }

    /** Jenis tabel terdekat yang membungkus sebuah sel, dipakai memilih proporsi kolom. */
    private static function tableKind(DOMElement $td): string
    {
        for ($n = $td->parentNode; $n instanceof DOMElement; $n = $n->parentNode) {
            if ($n->nodeName !== 'table') {
                continue;
            }

            $classes = preg_split('/\s+/', trim($n->getAttribute('class'))) ?: [];

            foreach (array_keys(self::COL_PERCENT) as $kind) {
                if (in_array($kind, $classes, true)) {
                    return $kind;
                }
            }

            return '';
        }

        return '';
    }

    /** Lebar yang tersedia: lebar tabel/sel terdekat yang membungkusnya, atau lebar halaman. */
    private static function availableWidthPx(DOMElement $el, int $contentWidthPx): int
    {
        for ($n = $el->parentNode; $n instanceof DOMElement; $n = $n->parentNode) {
            if (in_array($n->nodeName, ['table', 'td'], true)
                && preg_match('/width\s*:\s*([0-9.]+)px/i', $n->getAttribute('style'), $m)) {
                return (int) round((float) $m[1]);
            }
        }

        return $contentWidthPx;
    }

    private static function setWidthPx(DOMElement $el, int $px): void
    {
        $style = preg_replace('/(^|;)\s*width\s*:[^;]*;?/i', '$1', $el->getAttribute('style'));
        $style = trim($style, "; \t\n");

        $el->setAttribute('style', ($style !== '' ? $style . ';' : '') . 'width:' . $px . 'px;');
        $el->removeAttribute('width');
    }

    /** PhpWord tidak menurunkan text-align dari <td> ke <p> di dalamnya. */
    private static function pushCellAlignToChildren(DOMXPath $xpath, string $css): void
    {
        foreach ($xpath->query('//td') as $td) {
            $align = self::cellAlign($td, $css);
            if ($align === null) {
                continue;
            }

            foreach ($xpath->query('.//p|.//div', $td) as $child) {
                if (! preg_match('/text-align\s*:/i', $child->getAttribute('style'))) {
                    self::mergeStyle($child, "text-align:{$align};");
                }
            }
        }
    }

    /** Perataan sel: dari inline style kalau ada, kalau tidak dari aturan CSS untuk class-nya. */
    private static function cellAlign(DOMElement $td, string $css): ?string
    {
        if (preg_match('/text-align\s*:\s*([a-z]+)/i', $td->getAttribute('style'), $m)) {
            return strtolower($m[1]);
        }

        foreach (preg_split('/\s+/', trim($td->getAttribute('class'))) ?: [] as $class) {
            if ($class === '') {
                continue;
            }

            // Cocokkan "td.kelas { ... }" maupun ".kelas { ... }".
            $pola = '/(?:^|[,}])\s*(?:td)?\.' . preg_quote($class, '/') . '\s*\{([^}]*)\}/i';

            if (preg_match($pola, $css, $blok)
                && preg_match('/text-align\s*:\s*([a-z]+)/i', $blok[1], $m)) {
                return strtolower($m[1]);
            }
        }

        return null;
    }

    /**
     * Pemisah lembar di PDF berupa <div> kosong ber-page-break-after. PhpWord tidak
     * menghasilkan apa pun untuk elemen kosong, sehingga di DOCX semua lembar mengalir
     * jadi satu. Diganti paragraf berisi spasi yang membawa gaya page-break-after.
     */
    private static function replacePageBreaks(DOMDocument $dom, DOMXPath $xpath): void
    {
        $pemisah = [];
        foreach ($xpath->query('//div[contains(@style, "page-break-after")]') as $div) {
            $pemisah[] = $div;
        }
        foreach ($xpath->query(self::hasClass('div', 'pemisah-lembar')) as $div) {
            $pemisah[] = $div;
        }

        foreach ($pemisah as $div) {
            $p = $dom->createElement('p');
            $p->setAttribute('style', 'page-break-after:always;font-size:1pt;');
            $p->appendChild($dom->createTextNode(' '));
            $div->parentNode->replaceChild($p, $div);
        }
    }

    /**
     * Garis pemisah di bawah kop adalah <div> kosong yang hanya bergaris (border), dan
     * PhpWord tidak menghasilkan apa pun untuk elemen seperti itu — di DOCX garisnya
     * hilang sama sekali. Diganti tabel satu sel bergaris bawah yang setara.
     */
    private static function replaceKopSeparator(DOMDocument $dom, DOMXPath $xpath): void
    {
        $separators = [];
        foreach ($xpath->query(self::hasClass('div', 'kop-separator')) as $div) {
            $separators[] = $div;
        }

        foreach ($separators as $div) {
            $table = $dom->createElement('table');
            $table->setAttribute('style', 'border-bottom:3px #000000 solid;');

            $tr = $dom->createElement('tr');
            $td = $dom->createElement('td');
            $td->setAttribute('style', 'font-size:1pt;');
            $td->appendChild($dom->createTextNode(' '));

            $tr->appendChild($td);
            $table->appendChild($tr);
            $div->parentNode->replaceChild($table, $div);
        }
    }

    /**
     * Jarak untuk membubuhkan tanda tangan berasal dari ".ttd .nama { margin-top:30px }"
     * (selector turunan, tidak terbaca PhpWord) sehingga di Word nama menempel ke jabatan.
     */
    private static function openSignatureGap(DOMXPath $xpath): void
    {
        foreach ($xpath->query(self::hasClass('p', 'nama')) as $p) {
            if (! preg_match('/margin-top\s*:/i', $p->getAttribute('style'))) {
                self::mergeStyle($p, 'margin-top:30px;');
            }
        }
    }

    /**
     * PhpWord menyisipkan gambar sebagai VML (<w:pict><v:imagedata>), dan Word tidak andal
     * menampilkan transparansi PNG lewat jalur itu — logo bertransparansi bisa muncul
     * berlatar hitam. Karena surat selalu dicetak di atas putih, transparansi diratakan
     * ke putih dulu supaya hasilnya pasti sama dengan PDF.
     */
    private static function flattenImagesOnWhite(DOMXPath $xpath): void
    {
        foreach ($xpath->query('//img') as $img) {
            $binary = self::readImageSource($img->getAttribute('src'));
            if ($binary === null) {
                continue;
            }

            $flattened = self::flattenOnWhite($binary);
            if ($flattened === null) {
                continue;
            }

            $img->setAttribute('src', 'data:image/png;base64,' . base64_encode($flattened));
        }
    }

    private static function readImageSource(string $src): ?string
    {
        if (str_starts_with($src, 'data:')) {
            $comma = strpos($src, ',');

            return $comma === false ? null : (base64_decode(substr($src, $comma + 1), true) ?: null);
        }

        return is_file($src) ? (file_get_contents($src) ?: null) : null;
    }

    private static function flattenOnWhite(string $binary): ?string
    {
        $source = @imagecreatefromstring($binary);
        if (! $source) {
            return null;
        }

        $width  = imagesx($source);
        $height = imagesy($source);
        $canvas = imagecreatetruecolor($width, $height);

        imagefilledrectangle($canvas, 0, 0, $width, $height, imagecolorallocate($canvas, 255, 255, 255));
        imagecopy($canvas, $source, 0, 0, 0, 0, $width, $height);

        ob_start();
        imagepng($canvas);
        $output = ob_get_clean();

        imagedestroy($source);
        imagedestroy($canvas);

        return $output ?: null;
    }

    private static function mergeStyle(DOMElement $el, string $extra): void
    {
        $style = trim($el->getAttribute('style'));
        if ($style !== '' && ! str_ends_with($style, ';')) {
            $style .= ';';
        }

        $el->setAttribute('style', $style . $extra);
    }
}
