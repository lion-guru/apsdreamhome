<?php
/**
 * APS Dream Home - JavaScript Minifier
 */

namespace App\Core;

class JSMinifier
{
    public static function minify($js)
    {
        // Remove single line comments
        $js = preg_replace('/\/\/.*$/m', '', $js);

        // Remove multi-line comments
        $js = preg_replace('/\/\*.*?\*\//s', '', $js);

        // Remove whitespace
        $js = preg_replace('/\s+/s', ' ', $js);
        $js = preg_replace('/\s*([{}();,=+\-*&|<>!?:])\s*/', '\$1', $js);

        return trim($js);
    }

    public static function minifyFile($inputFile, $outputFile)
    {
        if (!file_exists($inputFile)) {
            return false;
        }

        $js = file_get_contents($inputFile);
        $minified = self::minify($js);

        return file_put_contents($outputFile, $minified);
    }
}
