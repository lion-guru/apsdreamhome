<?php
$base = __DIR__ . '/../assets/images';

@mkdir($base . '/hero', 0777, true);
@mkdir($base . '/testimonials', 0777, true);

// 1. no-image.jpg (600x400 gray)
$img = imagecreatetruecolor(600, 400);
$bg = imagecolorallocate($img, 200, 200, 200);
$fg = imagecolorallocate($img, 120, 120, 120);
imagefill($img, 0, 0, $bg);
imageline($img, 0, 0, 600, 400, $fg);
imageline($img, 600, 0, 0, 400, $fg);
imagestring($img, 5, 230, 190, 'No Image', $fg);
imagejpeg($img, $base . '/no-image.jpg', 85);
imagedestroy($img);
echo "Created no-image.jpg\n";

// 2. no-project-image.jpg (600x400 light blue)
$img = imagecreatetruecolor(600, 400);
$bg = imagecolorallocate($img, 230, 240, 250);
$fg = imagecolorallocate($img, 100, 130, 160);
imagefill($img, 0, 0, $bg);
imagestring($img, 5, 200, 190, 'No Project Image', $fg);
imagejpeg($img, $base . '/no-project-image.jpg', 85);
imagedestroy($img);
echo "Created no-project-image.jpg\n";

// 3. map-placeholder.jpg (600x400 light green)
$img = imagecreatetruecolor(600, 400);
$bg = imagecolorallocate($img, 230, 245, 230);
$fg = imagecolorallocate($img, 80, 140, 80);
imagefill($img, 0, 0, $bg);
imagestring($img, 5, 220, 190, 'Map Preview', $fg);
imagejpeg($img, $base . '/map-placeholder.jpg', 85);
imagedestroy($img);
echo "Created map-placeholder.jpg\n";

// 4. hero/luxury-home-1.jpg (1200x600 dark gradient)
$img = imagecreatetruecolor(1200, 600);
for ($y = 0; $y < 600; $y++) {
    $r = (int)(15 + ($y / 600) * 30);
    $g = (int)(23 + ($y / 600) * 20);
    $b = (int)(42 + ($y / 600) * 30);
    $lineColor = imagecolorallocate($img, $r, $g, $b);
    imageline($img, 0, $y, 1200, $y, $lineColor);
}
$fg = imagecolorallocate($img, 255, 255, 255);
imagestring($img, 5, 520, 290, 'APS Dream Homes', $fg);
imagejpeg($img, $base . '/hero/luxury-home-1.jpg', 85);
imagedestroy($img);
echo "Created hero/luxury-home-1.jpg\n";

// 5. logo.png (200x60 transparent)
$img = imagecreatetruecolor(200, 60);
imagesavealpha($img, true);
$trans = imagecolorallocatealpha($img, 0, 0, 0, 127);
imagefill($img, 0, 0, $trans);
$fg = imagecolorallocate($img, 13, 148, 136);
imagestring($img, 5, 30, 20, 'APS Dream Home', $fg);
imagepng($img, $base . '/logo.png');
imagedestroy($img);
echo "Created logo.png\n";

echo "All done!\n";
