<?php
// Set the content type header to display as an image
header('Content-Type: image/png');

// Get the first letter of the username
$username = isset($_GET['name']) ? $_GET['name'] : 'U';
$initials = strtoupper(substr($username, 0, 1));

// Avatar size
$width = 200;
$height = 200;

// Create a blank image
$image = imagecreatetruecolor($width, $height);

// Define colors
$bgColors = [
    'e74c3c', '3498db', '2ecc71', 'f39c12', '9b59b6',
    '1abc9c', 'd35400', '27ae60', '8e44ad', 'c0392b'
];

// Generate a consistent color based on the initials
$colorIndex = ord($initials) % count($bgColors);
$bgColor = $bgColors[$colorIndex];

// Convert hex to RGB
list($r, $g, $b) = sscanf($bgColor, "%02x%02x%02x");
$backgroundColor = imagecolorallocate($image, $r, $g, $b);
$textColor = imagecolorallocate($image, 255, 255, 255);

// Fill the background
imagefill($image, 0, 0, $backgroundColor);

// Set the font size and calculate the position
$fontSize = $width * 0.4;
$font = __DIR__ . '/arial.ttf'; // Fallback font

// Try to use a system font if available
$systemFonts = [
    'C:/Windows/Fonts/arial.ttf',
    'C:/Windows/Fonts/arialbd.ttf',
    '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
    '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf'
];

foreach ($systemFonts as $fontPath) {
    if (file_exists($fontPath)) {
        $font = $fontPath;
        break;
    }
}

// Calculate text position to center it
$bbox = imagettfbbox($fontSize, 0, $font, $initials);
$textWidth = $bbox[2] - $bbox[0];
$textHeight = $bbox[1] - $bbox[7];
$x = ($width - $textWidth) / 2 - $bbox[0];
$y = ($height - $textHeight) / 2 - $bbox[7];

// Add text to the image
imagettftext($image, $fontSize, 0, $x, $y, $textColor, $font, $initials);

// Output the image
imagepng($image);

// Free up memory
imagedestroy($image);
?>
