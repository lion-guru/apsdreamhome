<?php
/**
 * APS Dream Home - Asset Build Script
 */

require_once __DIR__ . '/config/paths.php';
require_once APP_PATH . '/Core/CSSMinifier.php';
require_once APP_PATH . '/Core/JSMinifier.php';
require_once APP_PATH . '/Core/AssetBundler.php';

echo '📦 APS DREAM HOME - ASSET BUILD\n';
echo '================================\n\n';

$bundler = new App\Core\AssetBundler();
$config = require CONFIG_PATH . '/assets.php';

echo 'Building CSS bundles...\n';
foreach ($config['bundles'] as $bundleName => $bundle) {
    if (!empty($bundle['css'])) {
        $cssFiles = [];
        foreach ($bundle['css'] as $cssFile) {
            $cssFiles[] = PUBLIC_PATH . '/assets/css/' . $cssFile;
        }

        if ($bundler->bundleCSS($cssFiles, $bundleName)) {
            echo "✅ CSS bundle '$bundleName' created\n";
        } else {
            echo "❌ CSS bundle '$bundleName' failed\n";
        }
    }
}

echo '\nBuilding JS bundles...\n';
foreach ($config['bundles'] as $bundleName => $bundle) {
    if (!empty($bundle['js'])) {
        $jsFiles = [];
        foreach ($bundle['js'] as $jsFile) {
            $jsFiles[] = PUBLIC_PATH . '/assets/js/' . $jsFile;
        }

        if ($bundler->bundleJS($jsFiles, $bundleName)) {
            echo "✅ JS bundle '$bundleName' created\n";
        } else {
            echo "❌ JS bundle '$bundleName' failed\n";
        }
    }
}

echo '\n🎉 Asset build completed!\n';
