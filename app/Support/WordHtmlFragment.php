<?php

namespace App\Support;

use DOMDocument;

class WordHtmlFragment
{
    /**
     * Ubah HTML surat (hasil render blade yang sama dipakai untuk PDF) jadi fragment
     * yang bisa dimengerti PhpWord\Shared\Html::addHtml(), sekaligus menutup beberapa
     * celah parser CSS/HTML PhpWord yang jauh lebih sederhana dari dompdf:
     *
     *  - Html::addHtml() perlu XML well-formed (loadXML), bukan HTML5 biasa
     *    (tag kosong seperti <br>/<meta> harus self-closed) -> di-normalisasi lewat
     *    DOMDocument::loadHTML (toleran) lalu diserialisasi ulang jadi XML.
     *  - Parser CSS bawaan PhpWord tidak membuang komentar /* ... *\/ , sehingga
     *    komentar "menempel" ke selector berikutnya dan bikin lookup class gagal.
     *  - Parser itu juga tidak mendukung selector turunan (mis. ".judul h3"), jadi
     *    beberapa gaya (uppercase judul, line-break pada jabatan TTD) diterapkan
     *    manual di sini karena tidak bisa diwakili oleh selector class tunggal.
     */
    public static function prepare(string $html): string
    {
        $css = self::extractStyle($html);
        $html = self::forceUppercaseTitle($html);
        $html = self::preserveLineBreaks($html);

        return '<style>' . $css . '</style>' . self::bodyToXml($html);
    }

    private static function extractStyle(string $html): string
    {
        preg_match('/<style>(.*?)<\/style>/s', $html, $match);
        $css = $match[1] ?? '';

        // Buang komentar CSS supaya tidak menempel ke selector berikutnya.
        return preg_replace('#/\*.*?\*/#s', '', $css);
    }

    private static function forceUppercaseTitle(string $html): string
    {
        return preg_replace_callback('/<h3>(.*?)<\/h3>/s', function ($m) {
            $text = mb_strtoupper(strip_tags($m[1]), 'UTF-8');

            return '<h3 style="font-weight:bold;text-decoration:underline;text-align:center;">' . $text . '</h3>';
        }, $html);
    }

    private static function preserveLineBreaks(string $html): string
    {
        return preg_replace_callback('/(<p class="jabatan"[^>]*>)(.*?)(<\/p>)/s', function ($m) {
            return $m[1] . nl2br($m[2]) . $m[3];
        }, $html);
    }

    private static function bodyToXml(string $html): string
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8"?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

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
}
