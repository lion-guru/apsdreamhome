<?php
/**
 * Lazy Loading Helper - Add loading="lazy" to all images
 */
class LazyLoader {
    /**
     * Add lazy loading to image tags in HTML
     */
    public static function addLazyToImages($html) {
        // Add loading="lazy" to images that don't have it
        $html = preg_replace_callback(
            '/<img([^>]*)(?!loading=)([^>]*?)>/i',
            function($matches) {
                $attrs = $matches[1];
                $extra = $matches[2];
                if (strpos($attrs, 'loading=') === false) {
                    return '<img' . $attrs . ' loading="lazy"' . $extra . '>';
                }
                return $matches[0];
            },
            $html
        );
        return $html;
    }

    /**
     * Create optimized image tag
     */
    public static function optimizedImage($src, $alt = '', $width = null, $height = null) {
        $attrs = 'src="' . htmlspecialchars($src) . '" alt="' . htmlspecialchars($alt) . '" loading="lazy"';
        if ($width) $attrs .= ' width="' . $width . '"';
        if ($height) $attrs .= ' height="' . $height . '"';
        return '<img ' . $attrs . '>';
    }
}
?>