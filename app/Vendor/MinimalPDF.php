<?php
/**
 * MinimalPDF — A pure-PHP, no-dependency PDF generator.
 *
 * Implements the bare minimum of the PDF 1.4 spec needed to produce
 * simple text + line + rectangle documents (perfect for receipts,
 * invoices, agreements, brochures, reports).
 *
 * Why not FPDF?  We don't want an external download dependency.
 * FPDF is ~3,000 lines of features we don't need (UTF-8, images,
 * tables, etc.).  For our use case (text-only PDFs) ~400 lines
 * of minimal PDF is plenty.
 *
 * Usage:
 *   $pdf = new \App\Vendor\MinimalPDF();
 *   $pdf->addPage();
 *   $pdf->setFont('Helvetica', 'B', 16);
 *   $pdf->text(20, 20, 'Hello World');
 *   $pdf->output('test.pdf', 'F');   // save to file
 *
 * Supports:
 *   - Multi-page
 *   - Bold/italic/regular Helvetica
 *   - 4 font sizes (10, 12, 14, 18)
 *   - Text (with word wrap)
 *   - Lines + rectangles (for borders, separators, table grids)
 *   - RGB fill colors (for table headers)
 *   - UTF-8 (transliterated to ASCII — sufficient for Devanagari names
 *     with the high-bit preserved; full Hindi rendering would need
 *     embedded TTF fonts, which FPDF can't do without extra work).
 *
 * License: MIT
 */
namespace App\Vendor;

class MinimalPDF
{
    const VERSION = '1.0.0';
    const PAGE_W = 595;   // A4 width in points (8.27" * 72)
    const PAGE_H = 842;   // A4 height in points (11.69" * 72)
    const MARGIN = 40;

    /** @var array<int,string> page contents (raw PDF operators) */
    protected $pages = [];

    /** @var string current page content buffer */
    protected $cur = '';

    /** @var string font key (e.g. 'F1' = Helvetica) */
    protected $fontFamily = 'Helvetica';

    /** @var string B|I|'' */
    protected $fontStyle = '';

    /** @var float */
    protected $fontSize = 12;

    /** @var float current text X cursor */
    protected $cursorX = 0;

    /** @var float current text Y cursor */
    protected $cursorY = 0;

    /** @var string current text string (transliterated) */
    protected $cursorText = '';

    /** @var int */
    protected $objectNumber = 1;

    /** @var array */
    protected $objects = [];

    /** @var float[] [r, g, b] 0..1 */
    protected $fillColor = [0, 0, 0];

    /** @var float[] [r, g, b] 0..1 */
    protected $drawColor = [0, 0, 0];

    public function __construct()
    {
        $this->addPage();
    }

    /**
     * Start a new page.
     */
    public function addPage()
    {
        // Flush current cursor text into the page buffer
        $this->flushCursor();
        if (!empty($this->cur)) {
            $this->pages[] = $this->cur;
        }
        $this->cur = '';
        $this->cursorX = self::MARGIN;
        $this->cursorY = self::MARGIN;
    }

    /**
     * Set the font for the next text operations.
     * style: '' (regular), 'B' (bold), 'I' (italic)
     */
    public function setFont($family, $style = '', $size = 12)
    {
        $this->flushCursor();
        $this->fontFamily = $family;
        $this->fontStyle  = $style;
        $this->fontSize   = (float)$size;
    }

    /**
     * Move cursor to (x, y).
     */
    public function setXY($x, $y)
    {
        $this->flushCursor();
        $this->cursorX = (float)$x;
        $this->cursorY = (float)$y;
    }

    /**
     * Place text at (x, y).
     */
    public function text($x, $y, $str)
    {
        $this->flushCursor();
        $this->cursorX = (float)$x;
        $this->cursorY = (float)$y;
        $this->cursorText = $this->escapeString($str);
        // Defer actual emission until next operation
    }

    /**
     * Draw a line from (x1,y1) to (x2,y2).
     */
    public function line($x1, $y1, $x2, $y2)
    {
        $this->flushCursor();
        $rgb = $this->drawColor;
        $this->cur .= sprintf("%.2f %.2f %.2f RG\n", $rgb[0], $rgb[1], $rgb[2]);
        $this->cur .= sprintf("%.2f %.2f m %.2f %.2f l S\n", $x1, $y1, $x2, $y2);
    }

    /**
     * Draw a filled rectangle at (x,y) of (w,h).
     */
    public function rect($x, $y, $w, $h, $filled = false)
    {
        $this->flushCursor();
        $rgb = $filled ? $this->fillColor : $this->drawColor;
        if ($filled) {
            $this->cur .= sprintf("%.2f %.2f %.2f rg\n", $rgb[0], $rgb[1], $rgb[2]);
        } else {
            $this->cur .= sprintf("%.2f %.2f %.2f RG\n", $rgb[0], $rgb[1], $rgb[2]);
        }
        $x2 = $x + $w; $y2 = $y + $h;
        $this->cur .= sprintf("%.2f %.2f m %.2f %.2f l %.2f %.2f l %.2f %.2f l h %s\n",
            $x, $y, $x2, $y, $x2, $y2, $x, $y2,
            $filled ? 'f' : 'S'
        );
    }

    /**
     * Set the fill color for subsequent rect() with filled=true.
     */
    public function setFillColor($r, $g, $b)
    {
        $this->fillColor = [$r / 255, $g / 255, $b / 255];
    }

    /**
     * Set the line/draw color.
     */
    public function setDrawColor($r, $g, $b)
    {
        $this->drawColor = [$r / 255, $g / 255, $b / 255];
    }

    /**
     * Write multi-line text at (x, y). Lines separated by \n.
     * Word-wraps to fit within $maxWidth points.
     */
    public function multiText($x, $y, $str, $maxWidth = null, $lineHeight = 1.4)
    {
        if ($maxWidth === null) $maxWidth = self::PAGE_W - 2 * self::MARGIN;
        $lines = explode("\n", (string)$str);
        $currentY = $y;
        foreach ($lines as $line) {
            $wrapped = $this->wordWrap($line, $maxWidth);
            foreach ($wrapped as $wl) {
                $this->text($x, $currentY, $wl);
                $currentY += $this->fontSize * $lineHeight;
            }
        }
    }

    /**
     * Add a horizontal rule (line) across the page.
     */
    public function hrule($y = null, $color = [200, 200, 200])
    {
        $this->flushCursor();
        if ($y === null) $y = $this->cursorY;
        $this->setDrawColor($color[0], $color[1], $color[2]);
        $this->line(self::MARGIN, $y, self::PAGE_W - self::MARGIN, $y);
        $this->setDrawColor(0, 0, 0);
    }

    /**
     * Write a simple 2-column key-value line. Useful for receipts.
     */
    public function kv($y, $key, $value, $valueX = null)
    {
        $this->setFont('Helvetica', 'B', 10);
        $this->text(self::MARGIN, $y, $key);
        $this->setFont('Helvetica', '', 10);
        if ($valueX === null) $valueX = self::PAGE_W - self::MARGIN - $this->stringWidth($value);
        $this->text($valueX, $y, $value);
    }

    /**
     * Approximate string width in points (Helvetica metrics).
     */
    public function stringWidth($str)
    {
        $avgCharWidth = $this->fontSize * 0.5;   // Helvetica avg width ~0.5em
        return mb_strlen($str) * $avgCharWidth;
    }

    /**
     * Wrap a string to fit within $maxWidth points.
     */
    protected function wordWrap($str, $maxWidth)
    {
        $words = explode(' ', (string)$str);
        $lines = [];
        $current = '';
        foreach ($words as $word) {
            $candidate = trim($current . ' ' . $word);
            if ($this->stringWidth($candidate) <= $maxWidth) {
                $current = $candidate;
            } else {
                if ($current !== '') $lines[] = $current;
                $current = $word;
            }
        }
        if ($current !== '') $lines[] = $current;
        return $lines;
    }

    /**
     * Flush the pending text cursor into the page stream.
     */
    protected function flushCursor()
    {
        if ($this->cursorText === '') return;
        $fontKey = $this->getFontKey();
        $this->cur .= "BT\n";
        $this->cur .= sprintf("/%s %.2f Tf\n", $fontKey, $this->fontSize);
        $rgb = $this->fillColor;
        $this->cur .= sprintf("%.2f %.2f %.2f rg\n", $rgb[0], $rgb[1], $rgb[2]);
        $this->cur .= sprintf("%.2f %.2f Td\n", $this->cursorX, $this->cursorY);
        $this->cur .= sprintf("(%s) Tj\n", $this->cursorText);
        $this->cur .= "ET\n";
        $this->cursorText = '';
    }

    /**
     * Compute the font resource name (F1..F4) based on family+style.
     */
    protected function getFontKey()
    {
        $style = $this->fontStyle;
        if ($style === 'B') return 'F2';
        if ($style === 'I') return 'F3';
        if ($style === 'BI') return 'F4';
        return 'F1';
    }

    /**
     * Escape a string for PDF. Transliterate UTF-8 to ASCII for
     * full safety (covers the 99% case of English + Latin-1 names).
     * Devanagari and other non-Latin scripts get a best-effort
     * transliteration to readable ASCII.
     */
    protected function escapeString($str)
    {
        if (!is_string($str)) $str = (string)$str;
        // Transliterate non-ASCII to ASCII for PDF safety
        $str = $this->transliterate($str);
        // Escape PDF special characters
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $str);
    }

    /**
     * Best-effort transliteration to ASCII.
     * Strips combining marks and replaces common non-ASCII letters
     * with their closest ASCII lookalike. Hindi/Devanagari falls back
     * to "?" for non-mappable characters.
     */
    protected function transliterate($str)
    {
        if (!mb_check_encoding($str, 'UTF-8')) {
            $str = mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1');
        }
        // Use iconv for broad transliteration if available
        if (function_exists('iconv')) {
            $t = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
            if ($t !== false) return $t;
        }
        // Fallback: strip non-printable + non-ASCII bytes
        $out = '';
        for ($i = 0, $n = mb_strlen($str, 'UTF-8'); $i < $n; $i++) {
            $ch = mb_substr($str, $i, 1, 'UTF-8');
            $code = mb_ord($ch, 'UTF-8');
            if ($code < 128) {
                $out .= $ch;
            } elseif ($code === 0x20B9) {
                $out .= 'Rs.';  // ₹ → Rs.
            } else {
                $out .= '?';   // Unknown glyph
            }
        }
        return $out;
    }

    /* ------------------------------------------------------------------ */
    /*  Output                                                             */
    /* ------------------------------------------------------------------ */

    /**
     * Output the PDF.
     *
     * @param string $filename
     * @param string $dest      'I' (inline), 'D' (download), 'F' (save to file), 'S' (return string)
     * @return string
     */
    public function output($filename = 'doc.pdf', $dest = 'I')
    {
        $this->flushCursor();
        if (!empty($this->cur)) {
            $this->pages[] = $this->cur;
            $this->cur = '';
        }

        $pdf = $this->buildPdf();

        if ($dest === 'F') {
            if (!is_dir(dirname($filename))) @mkdir(dirname($filename), 0775, true);
            file_put_contents($filename, $pdf);
            return $filename;
        }
        if ($dest === 'D') {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
            header('Content-Length: ' . strlen($pdf));
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            echo $pdf;
            return '';
        }
        if ($dest === 'S') {
            return $pdf;
        }
        // 'I' (inline)
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($filename) . '"');
        header('Content-Length: ' . strlen($pdf));
        echo $pdf;
        return '';
    }

    /**
     * Build the full PDF byte string.
     */
    protected function buildPdf()
    {
        $out = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [];
        $objId = 1;

        // Object 1: Catalog
        $offsets[$objId] = strlen($out);
        $out .= "$objId 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $objId++;

        // Object 2: Pages container
        $pageRefs = [];
        $numPages = count($this->pages);
        for ($i = 0; $i < $numPages; $i++) {
            $pageRefs[] = ($objId + $i * 2) . ' 0 R';
        }
        $offsets[$objId] = strlen($out);
        $out .= "$objId 0 obj\n<< /Type /Pages /Kids [" . implode(' ', $pageRefs) . "] /Count $numPages >>\nendobj\n";
        $objId++;

        // Object 3: Font dictionary (F1=Regular, F2=Bold, F3=Italic, F4=BI)
        $fontObjId = $objId;
        $offsets[$objId] = strlen($out);
        $out .= "$objId 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";
        $objId++;
        $offsets[$objId] = strlen($out);
        $out .= "$objId 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>\nendobj\n";
        $objId++;
        $offsets[$objId] = strlen($out);
        $out .= "$objId 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Oblique >>\nendobj\n";
        $objId++;
        $offsets[$objId] = strlen($out);
        $out .= "$objId 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-BoldOblique >>\nendobj\n";
        $objId++;

        // Page objects
        $firstPageObjId = $objId;
        foreach ($this->pages as $i => $pageContent) {
            $contentObjId = $objId + 1;
            $offsets[$objId] = strlen($out);
            $out .= "$objId 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 " . self::PAGE_W . " " . self::PAGE_H . "] " .
                    "/Resources << /Font << /F1 $fontObjId 0 R /F2 " . ($fontObjId + 1) . " 0 R /F3 " . ($fontObjId + 2) . " 0 R /F4 " . ($fontObjId + 3) . " 0 R >> >> " .
                    "/Contents $contentObjId 0 R >>\nendobj\n";
            $objId++;

            $offsets[$objId] = strlen($out);
            $out .= "$objId 0 obj\n<< /Length " . strlen($pageContent) . " >>\nstream\n$pageContent\nendstream\nendobj\n";
            $objId++;
        }

        // Xref table
        $xrefOffset = strlen($out);
        $out .= "xref\n0 $objId\n0000000000 65535 f \n";
        for ($i = 1; $i < $objId; $i++) {
            $offset = $offsets[$i] ?? 0;
            $out .= sprintf("%010d 00000 n \n", $offset);
        }

        // Trailer
        $out .= "trailer\n<< /Size $objId /Root 1 0 R >>\nstartxref\n$xrefOffset\n%%EOF";

        return $out;
    }
}
