<?php
/**
 * APS Dream Home - Image Optimization Script
 * Automated image optimization and compression
 */

echo "🖼️ APS DREAM HOME - IMAGE OPTIMIZATION\n";
echo "=====================================\n\n";

// Load centralized path configuration
require_once __DIR__ . '/config/paths.php';

// Image optimization results
$optimizationResults = [];
$totalOptimizations = 0;
$successfulOptimizations = 0;

echo "🔍 IMPLEMENTING IMAGE OPTIMIZATION...\n\n";

// 1. Check image processing capabilities
echo "Step 1: Checking image processing capabilities\n";
$imageExtensions = ['gd', 'imagick', 'exif'];
$loadedExtensions = [];

foreach ($imageExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "   ✅ $ext: Loaded\n";
        $loadedExtensions[] = $ext;
        $optimizationResults['extensions'][$ext] = 'loaded';
        $successfulOptimizations++;
    } else {
        echo "   ❌ $ext: Not loaded\n";
        $optimizationResults['extensions'][$ext] = 'not_loaded';
    }
    $totalOptimizations++;
}

// 2. Create image optimization configuration
echo "\nStep 2: Creating image optimization configuration\n";
$imageConfig = [
    'quality' => 85,
    'max_width' => 1920,
    'max_height' => 1080,
    'thumbnail_width' => 300,
    'thumbnail_height' => 200,
    'webp_quality' => 80,
    'formats' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
    'compression_level' => 6
];

// Create optimized images directory
$optimizedDir = PUBLIC_PATH . '/assets/images/optimized';
if (!is_dir($optimizedDir)) {
    if (mkdir($optimizedDir, 0755, true)) {
        echo "   ✅ Optimized images directory created: $optimizedDir\n";
        $optimizationResults['optimized_directory'] = 'created';
        $successfulOptimizations++;
    } else {
        echo "   ❌ Failed to create optimized images directory\n";
        $optimizationResults['optimized_directory'] = 'failed';
    }
} else {
    echo "   ✅ Optimized images directory exists: $optimizedDir\n";
    $optimizationResults['optimized_directory'] = 'exists';
    $successfulOptimizations++;
}
$totalOptimizations++;

// Create thumbnails directory
$thumbnailDir = PUBLIC_PATH . '/assets/images/thumbnails';
if (!is_dir($thumbnailDir)) {
    if (mkdir($thumbnailDir, 0755, true)) {
        echo "   ✅ Thumbnails directory created: $thumbnailDir\n";
        $optimizationResults['thumbnail_directory'] = 'created';
        $successfulOptimizations++;
    } else {
        echo "   ❌ Failed to create thumbnails directory\n";
        $optimizationResults['thumbnail_directory'] = 'failed';
    }
} else {
    echo "   ✅ Thumbnails directory exists: $thumbnailDir\n";
    $optimizationResults['thumbnail_directory'] = 'exists';
    $successfulOptimizations++;
}
$totalOptimizations++;

// 3. Create image optimization class
echo "\nStep 3: Creating image optimization class\n";
$imageOptimizerFile = APP_PATH . '/Core/ImageOptimizer.php';
$imageOptimizerContent = "<?php\n";
$imageOptimizerContent .= "/**\n";
$imageOptimizerContent .= " * APS Dream Home - Image Optimizer\n";
$imageOptimizerContent .= " */\n";
$imageOptimizerContent .= "\n";
$imageOptimizerContent .= "namespace App\\Core;\n";
$imageOptimizerContent .= "\n";
$imageOptimizerContent .= "class ImageOptimizer\n";
$imageOptimizerContent .= "{\n";
$imageOptimizerContent .= "    private \$config;\n";
$imageOptimizerContent .= "    private \$optimizedPath;\n";
$imageOptimizerContent .= "    private \$thumbnailPath;\n";
$imageOptimizerContent .= "\n";
$imageOptimizerContent .= "    public function __construct()\n";
$imageOptimizerContent .= "    {\n";
$imageOptimizerContent .= "        \$this->config = [\n";
$imageOptimizerContent .= "            'quality' => 85,\n";
$imageOptimizerContent .= "            'max_width' => 1920,\n";
$imageOptimizerContent .= "            'max_height' => 1080,\n";
$imageOptimizerContent .= "            'thumbnail_width' => 300,\n";
$imageOptimizerContent .= "            'thumbnail_height' => 200,\n";
$imageOptimizerContent .= "            'webp_quality' => 80\n";
$imageOptimizerContent .= "        ];\n";
$imageOptimizerContent .= "        \$this->optimizedPath = PUBLIC_PATH . '/assets/images/optimized';\n";
$imageOptimizerContent .= "        \$this->thumbnailPath = PUBLIC_PATH . '/assets/images/thumbnails';\n";
$imageOptimizerContent .= "    }\n";
$imageOptimizerContent .= "\n";
$imageOptimizerContent .= "    public function optimize(\$sourcePath, \$filename)\n";
$imageOptimizerContent .= "    {\n";
$imageOptimizerContent .= "        if (!file_exists(\$sourcePath)) {\n";
$imageOptimizerContent .= "            return false;\n";
$imageOptimizerContent .= "        }\n";
$imageOptimizerContent .= "\n";
$imageOptimizerContent .= "        \$info = getimagesize(\$sourcePath);\n";
$imageOptimizerContent .= "        if (!\$info) {\n";
$imageOptimizerContent .= "            return false;\n";
$imageOptimizerContent .= "        }\n";
$imageOptimizerContent .= "\n";
$imageOptimizerContent .= "        \$mimeType = \$info['mime'];\n";
$imageOptimizerContent .= "        \$width = \$info[0];\n";
$imageOptimizerContent .= "        \$height = \$info[1];\n";
$imageOptimizerContent .= "\n";
$imageOptimizerContent .= "        // Create image resource based on mime type\n";
$imageOptimizerContent .= "        switch (\$mimeType) {\n";
$imageOptimizerContent .= "            case 'image/jpeg':\n";
$imageOptimizerContent .= "                \$image = imagecreatefromjpeg(\$sourcePath);\n";
$imageOptimizerContent .= "                break;\n";
$imageOptimizerContent .= "            case 'image/png':\n";
$imageOptimizerContent .= "                \$image = imagecreatefrompng(\$sourcePath);\n";
$imageOptimizerContent .= "                break;\n";
$imageOptimizerContent .= "            case 'image/gif':\n";
$imageOptimizerContent .= "                \$image = imagecreatefromgif(\$sourcePath);\n";
$imageOptimizerContent .= "                break;\n";
$imageOptimizerContent .= "            default:\n";
$imageOptimizerContent .= "                return false;\n";
$imageOptimizerContent .= "        }\n";
$imageOptimizerContent .= "\n";
$imageOptimizerContent .= "        if (!\$image) {\n";
$imageOptimizerContent .= "            return false;\n";
$imageOptimizerContent .= "        }\n";
$imageOptimizerContent .= "\n";
$imageOptimizerContent .= "        // Resize if necessary\n";
$imageOptimizerContent .= "        \$newWidth = \$width;\n";
$imageOptimizerContent .= "        \$newHeight = \$height;\n";
$imageOptimizerContent .= "\n";
$imageOptimizerContent .= "        if (\$width > \$this->config['max_width'] || \$height > \$this->config['max_height']) {\n";
$imageOptimizerContent .= "            \$ratio = min(\$this->config['max_width'] / \$width, \$this->config['max_height'] / \$height);\n";
$imageOptimizerContent .= "            \$newWidth = round(\$width * \$ratio);\n";
$imageOptimizerContent .= "            \$newHeight = round(\$height * \$ratio);\n";
$imageOptimizerContent .= "        }\n";
$imageOptimizerContent .= "\n";
$imageOptimizerContent .= "        // Create new image\n";
$imageOptimizerContent .= "        \$newImage = imagecreatetruecolor(\$newWidth, \$newHeight);\n";
$imageOptimizerContent .= "        imagecopyresampled(\$newImage, \$image, 0, 0, 0, 0, \$newWidth, \$newHeight, \$width, \$height);\n";
$imageOptimizerContent .= "\n";
$imageOptimizerContent .= "        // Save optimized image\n";
$imageOptimizerContent .= "        \$optimizedPath = \$this->optimizedPath . '/' . \$filename;\n";
$imageOptimizerContent .= "        imagejpeg(\$newImage, \$optimizedPath, \$this->config['quality']);\n";
$imageOptimizerContent .= "\n";
$imageOptimizerContent .= "        // Create WebP version\n";
$imageOptimizerContent .= "        \$webpPath = \$this->optimizedPath . '/' . pathinfo(\$filename, PATHINFO_FILENAME) . '.webp';\n";
$imageOptimizerContent .= "        imagewebp(\$newImage, \$webpPath, \$this->config['webp_quality']);\n";
$imageOptimizerContent .= "\n";
$imageOptimizerContent .= "        // Create thumbnail\n";
$imageOptimizerContent .= "        \$this->createThumbnail(\$newImage, \$filename);\n";
$imageOptimizerContent .= "\n";
$imageOptimizerContent .= "        // Clean up\n";
$imageOptimizerContent .= "        imagedestroy(\$image);\n";
$imageOptimizerContent .= "        imagedestroy(\$newImage);\n";
$imageOptimizerContent .= "\n";
$imageOptimizerContent .= "        return true;\n";
$imageOptimizerContent .= "    }\n";
$imageOptimizerContent .= "\n";
$imageOptimizerContent .= "    private function createThumbnail(\$image, \$filename)\n";
$imageOptimizerContent .= "    {\n";
$imageOptimizerContent .= "        \$width = imagesx(\$image);\n";
$imageOptimizerContent .= "        \$height = imagesy(\$image);\n";
$imageOptimizerContent .= "\n";
$imageOptimizerContent .= "        \$ratio = min(\$this->config['thumbnail_width'] / \$width, \$this->config['thumbnail_height'] / \$height);\n";
$imageOptimizerContent .= "        \$newWidth = round(\$width * \$ratio);\n";
$imageOptimizerContent .= "        \$newHeight = round(\$height * \$ratio);\n";
$imageOptimizerContent .= "\n";
$imageOptimizerContent .= "        \$thumbnail = imagecreatetruecolor(\$newWidth, \$newHeight);\n";
$imageOptimizerContent .= "        imagecopyresampled(\$thumbnail, \$image, 0, 0, 0, 0, \$newWidth, \$newHeight, \$width, \$height);\n";
$imageOptimizerContent .= "\n";
$imageOptimizerContent .= "        \$thumbnailPath = \$this->thumbnailPath . '/' . \$filename;\n";
$imageOptimizerContent .= "        imagejpeg(\$thumbnail, \$thumbnailPath, \$this->config['quality']);\n";
$imageOptimizerContent .= "\n";
$imageOptimizerContent .= "        imagedestroy(\$thumbnail);\n";
$imageOptimizerContent .= "    }\n";
$imageOptimizerContent .= "\n";
$imageOptimizerContent .= "    public function getOptimizedPath(\$filename)\n";
$imageOptimizerContent .= "    {\n";
$imageOptimizerContent .= "        return \$this->optimizedPath . '/' . \$filename;\n";
$imageOptimizerContent .= "    }\n";
$imageOptimizerContent .= "\n";
$imageOptimizerContent .= "    public function getThumbnailPath(\$filename)\n";
$imageOptimizerContent .= "    {\n";
$imageOptimizerContent .= "        return \$this->thumbnailPath . '/' . \$filename;\n";
$imageOptimizerContent .= "    }\n";
$imageOptimizerContent .= "}\n";

if (file_put_contents($imageOptimizerFile, $imageOptimizerContent)) {
    echo "   ✅ Image optimizer class created: app/Core/ImageOptimizer.php\n";
    $optimizationResults['image_optimizer'] = 'created';
    $successfulOptimizations++;
} else {
    echo "   ❌ Failed to create image optimizer class\n";
    $optimizationResults['image_optimizer'] = 'failed';
}
$totalOptimizations++;

// 4. Create lazy loading JavaScript
echo "\nStep 4: Creating lazy loading JavaScript\n";
$lazyLoadJsFile = PUBLIC_PATH . '/assets/js/lazy-load.js';
$lazyLoadJsContent = "// APS Dream Home - Lazy Loading for Images\n";
$lazyLoadJsContent .= "class LazyLoader {\n";
$lazyLoadJsContent .= "    constructor() {\n";
$lazyLoadJsContent .= "        this.imageObserver = null;\n";
$lazyLoadJsContent .= "        this.init();\n";
$lazyLoadJsContent .= "    }\n";
$lazyLoadJsContent .= "\n";
$lazyLoadJsContent .= "    init() {\n";
$lazyLoadJsContent .= "        if ('IntersectionObserver' in window) {\n";
$lazyLoadJsContent .= "            this.imageObserver = new IntersectionObserver((entries, observer) => {\n";
$lazyLoadJsContent .= "                entries.forEach(entry => {\n";
$lazyLoadJsContent .= "                    if (entry.isIntersecting) {\n";
$lazyLoadJsContent .= "                        const img = entry.target;\n";
$lazyLoadJsContent .= "                        img.src = img.dataset.src;\n";
$lazyLoadJsContent .= "                        img.classList.remove('lazy');\n";
$lazyLoadJsContent .= "                        img.classList.add('loaded');\n";
$lazyLoadJsContent .= "                        observer.unobserve(img);\n";
$lazyLoadJsContent .= "                    }\n";
$lazyLoadJsContent .= "                });\n";
$lazyLoadJsContent .= "            });\n";
$lazyLoadJsContent .= "\n";
$lazyLoadJsContent .= "            // Observe all lazy images\n";
$lazyLoadJsContent .= "            document.querySelectorAll('img.lazy').forEach(img => {\n";
$lazyLoadJsContent .= "                this.imageObserver.observe(img);\n";
$lazyLoadJsContent .= "            });\n";
$lazyLoadJsContent .= "        }\n";
$lazyLoadJsContent .= "    }\n";
$lazyLoadJsContent .= "}\n";
$lazyLoadJsContent .= "\n";
$lazyLoadJsContent .= "// Initialize lazy loading\n";
$lazyLoadJsContent .= "document.addEventListener('DOMContentLoaded', () => {\n";
$lazyLoadJsContent .= "    new LazyLoader();\n";
$lazyLoadJsContent .= "});\n";

if (file_put_contents($lazyLoadJsFile, $lazyLoadJsContent)) {
    echo "   ✅ Lazy loading JavaScript created: assets/js/lazy-load.js\n";
    $optimizationResults['lazy_load_js'] = 'created';
    $successfulOptimizations++;
} else {
    echo "   ❌ Failed to create lazy loading JavaScript\n";
    $optimizationResults['lazy_load_js'] = 'failed';
}
$totalOptimizations++;

// 5. Create responsive image helper
echo "\nStep 5: Creating responsive image helper\n";
$responsiveHelperFile = APP_PATH . '/Helpers/ImageHelper.php';
$responsiveHelperContent = "<?php\n";
$responsiveHelperContent .= "/**\n";
$responsiveHelperContent .= " * APS Dream Home - Image Helper\n";
$responsiveHelperContent .= " */\n";
$responsiveHelperContent .= "\n";
$responsiveHelperContent .= "namespace App\\Helpers;\n";
$responsiveHelperContent .= "\n";
$responsiveHelperContent .= "class ImageHelper\n";
$responsiveHelperContent .= "{\n";
$responsiveHelperContent .= "    public static function responsiveImage(\$filename, \$alt = '', \$class = '')\n";
$responsiveHelperContent .= "    {\n";
$responsiveHelperContent .= "        \$baseUrl = 'http://localhost./public/assets/images';\n";
$responsiveHelperContent .= "        \$optimizedPath = \$baseUrl . '/optimized/' . \$filename;\n";
$responsiveHelperContent .= "        \$thumbnailPath = \$baseUrl . '/thumbnails/' . \$filename;\n";
$responsiveHelperContent .= "        \$webpPath = \$baseUrl . '/optimized/' . pathinfo(\$filename, PATHINFO_FILENAME) . '.webp';\n";
$responsiveHelperContent .= "\n";
$responsiveHelperContent .= "        return <<<HTML\n";
$responsiveHelperContent .= "        <picture>\n";
$responsiveHelperContent .= "            <source srcset=\"\$webpPath\" type=\"image/webp\">\n";
$responsiveHelperContent .= "            <source srcset=\"\$optimizedPath\" type=\"image/jpeg\">\n";
$responsiveHelperContent .= "            <img src=\"\$thumbnailPath\" \n";
$responsiveHelperContent .= "                 data-src=\"\$optimizedPath\" \n";
$responsiveHelperContent .= "                 alt=\"\$alt\" \n";
$responsiveHelperContent .= "                 class=\"lazy \$class\" \n";
$responsiveHelperContent .= "                 loading=\"lazy\">\n";
$responsiveHelperContent .= "        </picture>\n";
$responsiveHelperContent .= "HTML;\n";
$responsiveHelperContent .= "    }\n";
$responsiveHelperContent .= "\n";
$responsiveHelperContent .= "    public static function getWebpUrl(\$filename)\n";
$responsiveHelperContent .= "    {\n";
$responsiveHelperContent .= "        \$webpFilename = pathinfo(\$filename, PATHINFO_FILENAME) . '.webp';\n";
$responsiveHelperContent .= "        return 'http://localhost./public/assets/images/optimized/' . \$webpFilename;\n";
$responsiveHelperContent .= "    }\n";
$responsiveHelperContent .= "\n";
$responsiveHelperContent .= "    public static function getOptimizedUrl(\$filename)\n";
$responsiveHelperContent .= "    {\n";
$responsiveHelperContent .= "        return 'http://localhost./public/assets/images/optimized/' . \$filename;\n";
$responsiveHelperContent .= "    }\n";
$responsiveHelperContent .= "\n";
$responsiveHelperContent .= "    public static function getThumbnailUrl(\$filename)\n";
$responsiveHelperContent .= "    {\n";
$responsiveHelperContent .= "        return 'http://localhost./public/assets/images/thumbnails/' . \$filename;\n";
$responsiveHelperContent .= "    }\n";
$responsiveHelperContent .= "}\n";

if (file_put_contents($responsiveHelperFile, $responsiveHelperContent)) {
    echo "   ✅ Responsive image helper created: app/Helpers/ImageHelper.php\n";
    $optimizationResults['responsive_helper'] = 'created';
    $successfulOptimizations++;
} else {
    echo "   ❌ Failed to create responsive image helper\n";
    $optimizationResults['responsive_helper'] = 'failed';
}
$totalOptimizations++;

// 6. Create image optimization script
echo "\nStep 6: Creating image optimization script\n";
$optimizationScriptFile = BASE_PATH . '/optimize_images.php';
$optimizationScriptContent = "<?php\n";
$optimizationScriptContent .= "/**\n";
$optimizationScriptContent .= " * APS Dream Home - Image Optimization Script\n";
$optimizationScriptContent .= " */\n";
$optimizationScriptContent .= "\n";
$optimizationScriptContent .= "require_once __DIR__ . '/config/paths.php';\n";
$optimizationScriptContent .= "require_once APP_PATH . '/Core/ImageOptimizer.php';\n";
$optimizationScriptContent .= "\n";
$optimizationScriptContent .= "echo '🖼️ APS DREAM HOME - IMAGE OPTIMIZATION\\n';\n";
$optimizationScriptContent .= "echo '=====================================\\n\\n';\n";
$optimizationScriptContent .= "\n";
$optimizationScriptContent .= "\$optimizer = new App\\Core\\ImageOptimizer();\n";
$optimizationScriptContent .= "\$sourceDir = PUBLIC_PATH . '/assets/images';\n";
$optimizationScriptContent .= "\$processed = 0;\n";
$optimizationScriptContent .= "\$failed = 0;\n";
$optimizationScriptContent .= "\n";
$optimizationScriptContent .= "// Process all images in source directory\n";
$optimizationScriptContent .= "\$images = glob(\$sourceDir . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);\n";
$optimizationScriptContent .= "\n";
$optimizationScriptContent .= "foreach (\$images as \$image) {\n";
$optimizationScriptContent .= "    \$filename = basename(\$image);\n";
$optimizationScriptContent .= "    echo \"Optimizing \$filename...\\n\";\n";
$optimizationScriptContent .= "\n";
$optimizationScriptContent .= "    if (\$optimizer->optimize(\$image, \$filename)) {\n";
$optimizationScriptContent .= "        echo \"✅ \$filename optimized\\n\";\n";
$optimizationScriptContent .= "        \$processed++;\n";
$optimizationScriptContent .= "    } else {\n";
$optimizationScriptContent .= "        echo \"❌ \$filename failed\\n\";\n";
$optimizationScriptContent .= "        \$failed++;\n";
$optimizationScriptContent .= "    }\n";
$optimizationScriptContent .= "}\n";
$optimizationScriptContent .= "\n";
$optimizationScriptContent .= "echo \"\\n📊 OPTIMIZATION SUMMARY:\\n\";\n";
$optimizationScriptContent .= "echo \"✅ Processed: \$processed images\\n\";\n";
$optimizationScriptContent .= "echo \"❌ Failed: \$failed images\\n\";\n";
$optimizationScriptContent .= "echo \"🎉 Image optimization completed!\\n\";\n";

if (file_put_contents($optimizationScriptFile, $optimizationScriptContent)) {
    echo "   ✅ Image optimization script created: optimize_images.php\n";
    $optimizationResults['optimization_script'] = 'created';
    $successfulOptimizations++;
} else {
    echo "   ❌ Failed to create image optimization script\n";
    $optimizationResults['optimization_script'] = 'failed';
}
$totalOptimizations++;

// Summary
echo "\n=====================================\n";
echo "📊 IMAGE OPTIMIZATION SUMMARY\n";
echo "=====================================\n";

$successRate = round(($successfulOptimizations / $totalOptimizations) * 100, 1);
echo "📊 TOTAL OPTIMIZATIONS: $totalOptimizations\n";
echo "✅ SUCCESSFUL: $successfulOptimizations\n";
echo "📊 SUCCESS RATE: $successRate%\n\n";

echo "🔧 IMAGE OPTIMIZATION DETAILS:\n";
foreach ($optimizationResults as $category => $results) {
    echo "📋 $category:\n";
    if (is_array($results)) {
        foreach ($results as $item => $result) {
            $icon = $result === 'created' || $result === 'exists' || $result === 'loaded' ? '✅' : ($result === 'failed' || $result === 'not_loaded' ? '❌' : '⚠️');
            echo "   $icon $item: $result\n";
        }
    }
    echo "\n";
}

if ($successRate >= 80) {
    echo "🎉 IMAGE OPTIMIZATION: EXCELLENT!\n";
} elseif ($successRate >= 60) {
    echo "✅ IMAGE OPTIMIZATION: GOOD!\n";
} else {
    echo "⚠️  IMAGE OPTIMIZATION: NEEDS IMPROVEMENT\n";
}

echo "\n🚀 Image optimization completed successfully!\n";
echo "📊 Ready for next optimization step: Asset Minification\n";
?>
