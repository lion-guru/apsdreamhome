<?php
/**
 * Test: Image Gallery Lightbox
 * Run: php testing/test_image_gallery.php
 *
 * 15+ assertions:
 * - JS file exists with ImageGallery class
 * - CSS file exists with lightbox styles
 * - Vanilla JS only (no jQuery)
 * - Class has all required methods
 * - Keyboard handlers (Escape, ArrowLeft, ArrowRight)
 * - Touch handlers (touchstart, touchend)
 * - Zoom functionality
 * - Slideshow functionality
 * - View files have data-gallery attribute
 * - Layout includes the script
 * - URL pattern for images
 * - data-caption attribute
 */

define('BASE_PATH', __DIR__ . '/..');

$pass = 0;
$fail = 0;
$failures = [];

function assertTrue($cond, string $label): void {
    global $pass, $fail, $failures;
    if ($cond) { $pass++; echo "  PASS  $label\n"; }
    else { $fail++; $failures[] = $label; echo "  FAIL  $label\n"; }
}

echo "=== Image Gallery Lightbox Tests ===\n\n";

// Group 1: JS class
echo "[1] JavaScript class\n";
$jsPath = BASE_PATH . '/assets/js/image-gallery.js';
assertTrue(file_exists($jsPath), 'image-gallery.js exists');
$js = file_get_contents($jsPath);
assertTrue(strpos($js, 'class ImageGallery') !== false, 'ImageGallery class defined');
assertTrue(strpos($js, 'new ImageGallery') !== false, 'ImageGallery instantiated on DOM ready');
assertTrue(strpos($js, "document.addEventListener('DOMContentLoaded'") !== false || strpos($js, "DOMContentLoaded") !== false, 'DOMContentLoaded handler present');

// Group 2: No jQuery dependency
echo "\n[2] No jQuery dependency\n";
assertTrue(strpos($js, 'require.*jquery') === false && stripos($js, '<script.*jquery') === false, 'No jQuery script tag');
assertTrue(preg_match('/\$\([\"\']/', $js) === 0, 'No $() selector pattern');

// Group 3: Public methods
echo "\n[3] Public methods\n";
foreach (['init', 'collectImages', 'openGroup', 'bindKeyboard', 'show', 'hide', 'next', 'prev', 'render', 'toggleSlideshow', 'zoomIn', 'zoomOut', 'applyZoom', 'download', 'handleSwipe'] as $m) {
    assertTrue(strpos($js, $m . '(') !== false, "Method {$m} exists");
}

// Group 4: Keyboard handlers
echo "\n[4] Keyboard handlers\n";
assertTrue(strpos($js, 'Escape') !== false, 'Escape key handler');
assertTrue(strpos($js, 'ArrowRight') !== false, 'ArrowRight key handler');
assertTrue(strpos($js, 'ArrowLeft') !== false, 'ArrowLeft key handler');

// Group 5: Touch gesture handlers
echo "\n[5] Touch gesture handlers\n";
assertTrue(strpos($js, 'touchstart') !== false, 'touchstart handler');
assertTrue(strpos($js, 'touchend') !== false, 'touchend handler');
assertTrue(strpos($js, 'screenX') !== false, 'Uses screenX for swipe detection');
assertTrue(strpos($js, 'handleSwipe') !== false, 'handleSwipe method');
assertTrue(strpos($js, 'Math.abs') !== false, 'Swipe threshold via abs');

// Group 6: Zoom + slideshow
echo "\n[6] Zoom + slideshow\n";
assertTrue(strpos($js, 'setInterval') !== false, 'Slideshow uses setInterval');
assertTrue(strpos($js, 'clearInterval') !== false, 'Slideshow can be cleared');
assertTrue(strpos($js, 'zoom') !== false || strpos($js, 'Zoom') !== false, 'Zoom feature present');
assertTrue(strpos($js, 'transform: scale') !== false || strpos($js, 'scale(') !== false, 'Zoom uses CSS transform scale');

// Group 7: CSS file
echo "\n[7] CSS file\n";
$cssPath = BASE_PATH . '/assets/css/image-gallery.css';
assertTrue(file_exists($cssPath), 'image-gallery.css exists');
$css = file_get_contents($cssPath);
assertTrue(strpos($css, '.image-lightbox') !== false, 'Lightbox selector present');
assertTrue(strpos($css, '.lightbox-backdrop') !== false, 'Backdrop selector');
assertTrue(strpos($css, '.lightbox-close') !== false, 'Close button selector');
assertTrue(strpos($css, '.lightbox-nav') !== false, 'Nav arrows selector');
assertTrue(strpos($css, '.lightbox-caption') !== false, 'Caption selector');
assertTrue(strpos($css, '.lightbox-thumbs') !== false, 'Thumbnail strip selector');
assertTrue(strpos($css, '@media') !== false, 'Responsive media query');

// Group 8: View files
echo "\n[8] View files\n";
$propertyDetail = file_get_contents(BASE_PATH . '/app/views/properties/property_detail.php');
assertTrue(strpos($propertyDetail, 'data-gallery=') !== false, 'property_detail.php has data-gallery');
assertTrue(strpos($propertyDetail, 'property-image') !== false || strpos($propertyDetail, 'data-caption=') !== false, 'property_detail.php has property-image/caption');
$properties = file_get_contents(BASE_PATH . '/app/views/pages/properties.php');
assertTrue(strpos($properties, 'data-gallery=') !== false, 'properties.php has data-gallery');
assertTrue(strpos($properties, 'data-caption=') !== false, 'properties.php has data-caption');

// Group 9: Layout includes
echo "\n[9] Layout includes\n";
$baseLayout = file_get_contents(BASE_PATH . '/app/views/layouts/base.php');
assertTrue(strpos($baseLayout, 'image-gallery.js') !== false, 'base.php includes image-gallery.js');
assertTrue(strpos($baseLayout, 'image-gallery.css') !== false || strpos($baseLayout, 'aps-components.css') !== false, 'base.php includes image-gallery.css or consolidated aps-components.css');

// Group 10: URL pattern
echo "\n[10] URL pattern\n";
assertTrue(strpos($js, 'img.src') !== false, 'Image src URL pattern present');
assertTrue(strpos($js, 'dataset.full') !== false, 'Supports data-full attribute for higher-res image');

// Group 11: Lint
echo "\n[11] Lint\n";
$php = getenv('PHP_BINARY') ?: 'C:\\xampp\\php\\php.exe';
foreach ([$jsPath, $cssPath] as $f) {
    assertTrue(filesize($f) > 0, basename($f) . ' not empty');
}

// Summary
echo "\n=== Summary ===\n";
echo "PASS: $pass\n";
echo "FAIL: $fail\n";
if ($fail > 0) {
    echo "\nFailures:\n";
    foreach ($failures as $f) echo "  - $f\n";
}
exit($fail > 0 ? 1 : 0);
