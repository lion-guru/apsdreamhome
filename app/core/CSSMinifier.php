<?php
/**
 * APS Dream Home - CSS Minifier
 */

namespace App\Core;

class CSSMinifier
{
    public static function minify($css)
    {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);

        // Remove whitespace
        $css = preg_replace('/\s+/s', ' ', $css);
        $css = preg_replace('/\s*([{}:;,>+~])\s*/', '\$1', $css);
        $css = preg_replace('/;}/', '}', $css);

        // Remove unnecessary semicolons
        $css = str_replace(';}', '}', $css);

        return trim($css);
    }

    public static function minifyFile($inputFile, $outputFile)
    {
        if (!file_exists($inputFile)) {
            return false;
        }

        $css = file_get_contents($inputFile);
        $minified = self::minify($css);

        return file_put_contents($outputFile, $minified);
    }
}
