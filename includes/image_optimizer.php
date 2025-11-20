<?php
/**
 * Image Optimization and Lazy Loading Utility
 * Provides advanced lazy loading and image optimization features
 */

class ImageOptimizer {
    
    /**
     * Generate responsive image with lazy loading
     */
    public static function generateResponsiveImage($src, $alt, $options = []) {
        $defaults = [
            'class' => '',
            'width' => null,
            'height' => null,
            'loading' => 'lazy',
            'sizes' => '(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw',
            'srcset' => null,
            'placeholder' => 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 1 1\'%3E%3C/svg%3E',
            'fallback' => true
        ];
        
        $options = array_merge($defaults, $options);
        
        // Generate srcset if not provided
        if (!$options['srcset'] && $options['fallback']) {
            $options['srcset'] = self::generateSrcset($src);
        }
        
        $html = '<img ';
        $html .= 'src="' . htmlspecialchars($options['placeholder']) . '" ';
        $html .= 'data-src="' . htmlspecialchars($src) . '" ';
        
        if ($options['srcset']) {
            $html .= 'data-srcset="' . htmlspecialchars($options['srcset']) . '" ';
            $html .= 'sizes="' . htmlspecialchars($options['sizes']) . '" ';
        }
        
        $html .= 'alt="' . htmlspecialchars($alt) . '" ';
        $html .= 'loading="' . $options['loading'] . '" ';
        $html .= 'class="lazyload ' . htmlspecialchars($options['class']) . '" ';
        
        if ($options['width']) {
            $html .= 'width="' . $options['width'] . '" ';
        }
        
        if ($options['height']) {
            $html .= 'height="' . $options['height'] . '" ';
        }
        
        $html .= '/>';
        
        return $html;
    }
    
    /**
     * Generate srcset for responsive images
     */
    private static function generateSrcset($src) {
        $base_path = pathinfo($src, PATHINFO_DIRNAME);
        $filename = pathinfo($src, PATHINFO_FILENAME);
        $extension = pathinfo($src, PATHINFO_EXTENSION);
        
        $sizes = [320, 640, 768, 1024, 1200, 1600];
        $srcset_parts = [];
        
        foreach ($sizes as $size) {
            $responsive_src = $base_path . '/' . $filename . '-' . $size . '.' . $extension;
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . $responsive_src)) {
                $srcset_parts[] = $responsive_src . ' ' . $size . 'w';
            }
        }
        
        // Always include original image
        $srcset_parts[] = $src . ' 1920w';
        
        return implode(', ', $srcset_parts);
    }
    
    /**
     * Optimize image attributes for better performance
     */
    public static function optimizeImageAttributes($src, $alt, $options = []) {
        $defaults = [
            'class' => '',
            'loading' => 'lazy',
            'decoding' => 'async',
            'fetchpriority' => 'low',
            'style' => ''
        ];
        
        $options = array_merge($defaults, $options);
        
        // Add LQIP (Low Quality Image Placeholder) if available
        $lqip = self::getLQIP($src);
        
        $html = '<img ';
        
        if ($lqip) {
            $html .= 'src="' . htmlspecialchars($lqip) . '" ';
            $html .= 'data-src="' . htmlspecialchars($src) . '" ';
            $html .= 'class="lazyload blur-up ' . htmlspecialchars($options['class']) . '" ';
        } else {
            $html .= 'src="' . htmlspecialchars($src) . '" ';
            $html .= 'class="' . htmlspecialchars($options['class']) . '" ';
        }
        
        $html .= 'alt="' . htmlspecialchars($alt) . '" ';
        $html .= 'loading="' . $options['loading'] . '" ';
        $html .= 'decoding="' . $options['decoding'] . '" ';
        $html .= 'fetchpriority="' . $options['fetchpriority'] . '" ';
        
        if ($options['style']) {
            $html .= 'style="' . htmlspecialchars($options['style']) . '" ';
        }
        
        $html .= '/>';
        
        return $html;
    }
    
    /**
     * Generate LQIP (Low Quality Image Placeholder)
     */
    private static function getLQIP($src) {
        $lqip_path = str_replace(['.jpg', '.jpeg', '.png', '.webp'], '-lqip.jpg', $src);
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . $lqip_path)) {
            return $lqip_path;
        }
        return null;
    }
    
    /**
     * Generate picture element with WebP support
     */
    public static function generatePictureElement($src, $alt, $options = []) {
        $defaults = [
            'class' => '',
            'loading' => 'lazy',
            'sizes' => '(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw'
        ];
        
        $options = array_merge($defaults, $options);
        
        $base_path = pathinfo($src, PATHINFO_DIRNAME);
        $filename = pathinfo($src, PATHINFO_FILENAME);
        
        $webp_src = $base_path . '/' . $filename . '.webp';
        $fallback_src = $src;
        
        $html = '<picture>';
        
        // WebP source
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . $webp_src)) {
            $html .= '<source srcset="' . htmlspecialchars($webp_src) . '" type="image/webp">';
        }
        
        // Fallback image
        $html .= '<img ';
        $html .= 'src="' . htmlspecialchars($fallback_src) . '" ';
        $html .= 'alt="' . htmlspecialchars($alt) . '" ';
        $html .= 'loading="' . $options['loading'] . '" ';
        $html .= 'class="' . htmlspecialchars($options['class']) . '" ';
        $html .= 'sizes="' . htmlspecialchars($options['sizes']) . '" ';
        $html .= '/>';
        
        $html .= '</picture>';
        
        return $html;
    }
    
    /**
     * Generate optimized background image
     */
    public static function generateBackgroundImage($selector, $image_url, $options = []) {
        $defaults = [
            'lazy' => true,
            'preload' => false,
            'sizes' => ['mobile', 'tablet', 'desktop']
        ];
        
        $options = array_merge($defaults, $options);
        
        $css = '';
        
        if ($options['lazy']) {
            $css .= $selector . ' { background-image: none; }' . "\n";
            $css .= $selector . '.lazyloaded { background-image: url(' . $image_url . '); }' . "\n";
        } else {
            $css .= $selector . ' { background-image: url(' . $image_url . '); }' . "\n";
        }
        
        return $css;
    }
    
    /**
     * Get image dimensions for better layout stability
     */
    public static function getImageDimensions($src) {
        $full_path = $_SERVER['DOCUMENT_ROOT'] . $src;
        
        if (!file_exists($full_path)) {
            return ['width' => null, 'height' => null];
        }
        
        $size = getimagesize($full_path);
        
        if ($size) {
            return [
                'width' => $size[0],
                'height' => $size[1],
                'ratio' => $size[1] / $size[0]
            ];
        }
        
        return ['width' => null, 'height' => null, 'ratio' => null];
    }
}

// Helper function for easy usage
function optimized_image($src, $alt, $options = []) {
    return ImageOptimizer::optimizeImageAttributes($src, $alt, $options);
}

function responsive_image($src, $alt, $options = []) {
    return ImageOptimizer::generateResponsiveImage($src, $alt, $options);
}

function picture_element($src, $alt, $options = []) {
    return ImageOptimizer::generatePictureElement($src, $alt, $options);
}
?>