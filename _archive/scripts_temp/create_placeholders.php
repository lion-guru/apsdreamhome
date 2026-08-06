<?php
/**
 * Simple image placeholder generator
 * Creates basic placeholder images for missing assets
 */

$placeholders = [
    'no-image.jpg' => ['No Image Available', 'f1f5f9', '64748b'],
    'no-project-image.jpg' => ['No Project Image', 'f1f5f9', '64748b'],
    'map-placeholder.jpg' => ['Map View', 'e0f2fe', '0369a1'],
    'hero/luxury-home-1.jpg' => ['APS Dream Home', '0d9488', 'ffffff'],
    'logo.png' => ['APS', '0d9488', 'ffffff'],
    'testimonials/testimonial-1.jpg' => ['Happy Customer', 'fef3c7', 'd97706'],
    'testimonials/testimonial-2.jpg' => ['Happy Customer', 'fef3c7', 'd97706'],
    'testimonials/testimonial-3.jpg' => ['Happy Customer', 'fef3c7', 'd97706'],
];

foreach ($placeholders as $file => $config) {
    $dir = dirname(__DIR__ . '/assets/images/' . $file);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    $path = __DIR__ . '/assets/images/' . $file;
    if (!file_exists($path)) {
        // Create a simple placeholder
        $width = 400;
        $height = 300;
        
        $img = imagecreatetruecolor($width, $height);
        $bgColor = imagecolorallocate($img, hexdec(substr($config[1], 0, 2)), hexdec(substr($config[1], 2, 2)), hexdec(substr($config[1], 4, 2)));
        $textColor = imagecolorallocate($img, hexdec(substr($config[2], 0, 2)), hexdec(substr($config[2], 2, 2)), hexdec(substr($config[2], 4, 2)));
        
        imagefilledrectangle($img, 0, 0, $width, $height, $bgColor);
        
        // Add text
        $fontSize = 5;
        $text = $config[0];
        $textWidth = imagefontwidth($fontSize) * strlen($text);
        $textHeight = imagefontheight($fontSize);
        $x = ($width - $textWidth) / 2;
        $y = ($height - $textHeight) / 2;
        
        imagestring($img, $fontSize, $x, $y, $text, $textColor);
        
        // Save as JPEG
        imagejpeg($img, $path, 85);
        imagedestroy($img);
        
        echo "Created: " . basename($file) . "\n";
    }
}

echo "Done!\n";
