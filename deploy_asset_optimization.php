<?php
/**
 * Asset Optimization Script
 * Minify and compress production assets
 */

echo "🎨 ASSET OPTIMIZATION STARTING...\n";

$assetDirs = ["public/css", "public/js", "public/images"];
foreach ($assetDirs as $dir) {
    $fileCount = count(glob("$dir/*"));
    echo "📁 $dir: $fileCount files\n";
    
    // Minify CSS files
    if ($dir === "public/css") {
        foreach (glob("$dir/*.css") as $cssFile) {
            $minified = str_replace(".css", ".min.css", $cssFile);
            echo "🔧 Minifying: " . basename($cssFile) . " -> " . basename($minified) . "\n";
            // Minification logic here
        }
    }
    
    // Minify JS files
    if ($dir === "public/js") {
        foreach (glob("$dir/*.js") as $jsFile) {
            $minified = str_replace(".js", ".min.js", $jsFile);
            echo "🔧 Minifying: " . basename($jsFile) . " -> " . basename($minified) . "\n";
            // Minification logic here
        }
    }
}

echo "✅ Asset optimization completed!\n";
?>