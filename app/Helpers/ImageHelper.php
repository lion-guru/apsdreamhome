<?php
/**
 * APS Dream Home - Image Helper
 */

namespace App\Helpers;

class ImageHelper
{
    public static function responsiveImage($filename, $alt = '', $class = '')
    {
        $baseUrl = '/apsdreamhome/public/assets/images';
        $optimizedPath = $baseUrl . '/optimized/' . $filename;
        $thumbnailPath = $baseUrl . '/thumbnails/' . $filename;
        $webpPath = $baseUrl . '/optimized/' . pathinfo($filename, PATHINFO_FILENAME) . '.webp';

        return <<<HTML
        <picture>
            <source srcset="$webpPath" type="image/webp">
            <source srcset="$optimizedPath" type="image/jpeg">
            <img src="$thumbnailPath" 
                 data-src="$optimizedPath" 
                 alt="$alt" 
                 class="lazy $class" 
                 loading="lazy">
        </picture>
HTML;
    }

    public static function getWebpUrl($filename)
    {
        $webpFilename = pathinfo($filename, PATHINFO_FILENAME) . '.webp';
        return '/apsdreamhome/public/assets/images/optimized/' . $webpFilename;
    }

    public static function getOptimizedUrl($filename)
    {
        return '/apsdreamhome/public/assets/images/optimized/' . $filename;
    }

    public static function getThumbnailUrl($filename)
    {
        return '/apsdreamhome/public/assets/images/thumbnails/' . $filename;
    }
}
