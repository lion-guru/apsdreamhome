<?php
/**
 * Create Placeholder Images for APS Dream Home
 * Run this script to generate all missing placeholder images
 */

// Create a simple placeholder image
function createPlaceholder($path, $width = 300, $height = 200, $text = 'Property') {
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    // Create image
    $image = imagecreatetruecolor($width, $height);
    
    // Colors
    $bgColor = imagecolorallocate($image, 200, 200, 200);
    $textColor = imagecolorallocate($image, 100, 100, 100);
    $borderColor = imagecolorallocate($image, 150, 150, 150);
    
    // Fill background
    imagefill($image, 0, 0, $bgColor);
    
    // Add border
    imagerectangle($image, 0, 0, $width-1, $height-1, $borderColor);
    
    // Add text
    $fontSize = 5;
    $textX = ($width - strlen($text) * imagefontwidth($fontSize)) / 2;
    $textY = ($height - imagefontheight($fontSize)) / 2;
    imagestring($image, $fontSize, $textX, $textY, $text, $textColor);
    
    // Add dimensions text
    $dimText = $width . 'x' . $height;
    $dimX = ($width - strlen($dimText) * imagefontwidth(2)) / 2;
    $dimY = $textY + 20;
    imagestring($image, 2, $dimX, $dimY, $dimText, $textColor);
    
    // Save image
    imagejpeg($image, $path, 90);
    imagedestroy($image);
    
    return true;
}

echo "Creating placeholder images...\n";

// Property placeholder
createPlaceholder('assets/images/property-placeholder.jpg', 300, 200, 'Property');
echo "✓ assets/images/property-placeholder.jpg\n";

// Blog placeholder
createPlaceholder('assets/images/blog-placeholder.jpg', 300, 200, 'Blog');
echo "✓ assets/images/blog-placeholder.jpg\n";

// User placeholder
createPlaceholder('assets/images/user-placeholder.jpg', 100, 100, 'User');
echo "✓ assets/images/user-placeholder.jpg\n";

// Property placeholder in img folder
createPlaceholder('assets/img/property-placeholder.jpg', 300, 200, 'Property');
echo "✓ assets/img/property-placeholder.jpg\n";

// Gorakhpur project placeholder
createPlaceholder('assets/images/projects/gorakhpur/placeholder.jpg', 800, 600, 'Gorakhpur Project');
echo "✓ assets/images/projects/gorakhpur/placeholder.jpg\n";

// Lucknow project placeholder
createPlaceholder('assets/images/projects/lucknow/placeholder.jpg', 800, 600, 'Lucknow Project');
echo "✓ assets/images/projects/lucknow/placeholder.jpg\n";

echo "\nAll placeholder images created successfully!\n";
echo "You can delete this script after running.\n";
?>
