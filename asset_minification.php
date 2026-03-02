<?php
/**
 * APS Dream Home - Asset Minification Script
 * Automated CSS and JavaScript minification
 */

echo "📦 APS DREAM HOME - ASSET MINIFICATION\n";
echo "====================================\n\n";

// Load centralized path configuration
require_once __DIR__ . '/config/paths.php';

// Asset minification results
$minificationResults = [];
$totalOptimizations = 0;
$successfulOptimizations = 0;

echo "🔍 IMPLEMENTING ASSET MINIFICATION...\n\n";

// 1. Create minified assets directory
echo "Step 1: Creating minified assets directory\n";
$minifiedDir = PUBLIC_PATH . '/assets/minified';
if (!is_dir($minifiedDir)) {
    if (mkdir($minifiedDir, 0755, true)) {
        echo "   ✅ Minified assets directory created: $minifiedDir\n";
        $minificationResults['minified_directory'] = 'created';
        $successfulOptimizations++;
    } else {
        echo "   ❌ Failed to create minified assets directory\n";
        $minificationResults['minified_directory'] = 'failed';
    }
} else {
    echo "   ✅ Minified assets directory exists: $minifiedDir\n";
    $minificationResults['minified_directory'] = 'exists';
    $successfulOptimizations++;
}
$totalOptimizations++;

// 2. Create CSS minifier class
echo "\nStep 2: Creating CSS minifier class\n";
$cssMinifierFile = APP_PATH . '/Core/CSSMinifier.php';
$cssMinifierContent = "<?php\n";
$cssMinifierContent .= "/**\n";
$cssMinifierContent .= " * APS Dream Home - CSS Minifier\n";
$cssMinifierContent .= " */\n";
$cssMinifierContent .= "\n";
$cssMinifierContent .= "namespace App\\Core;\n";
$cssMinifierContent .= "\n";
$cssMinifierContent .= "class CSSMinifier\n";
$cssMinifierContent .= "{\n";
$cssMinifierContent .= "    public static function minify(\$css)\n";
$cssMinifierContent .= "    {\n";
$cssMinifierContent .= "        // Remove comments\n";
$cssMinifierContent .= "        \$css = preg_replace('!/\\*[^*]*\\*+([^/][^*]*\\*+)*/!', '', \$css);\n";
$cssMinifierContent .= "\n";
$cssMinifierContent .= "        // Remove whitespace\n";
$cssMinifierContent .= "        \$css = preg_replace('/\\s+/s', ' ', \$css);\n";
$cssMinifierContent .= "        \$css = preg_replace('/\\s*([{}:;,>+~])\\s*/', '\\$1', \$css);\n";
$cssMinifierContent .= "        \$css = preg_replace('/;}/', '}', \$css);\n";
$cssMinifierContent .= "\n";
$cssMinifierContent .= "        // Remove unnecessary semicolons\n";
$cssMinifierContent .= "        \$css = str_replace(';}', '}', \$css);\n";
$cssMinifierContent .= "\n";
$cssMinifierContent .= "        return trim(\$css);\n";
$cssMinifierContent .= "    }\n";
$cssMinifierContent .= "\n";
$cssMinifierContent .= "    public static function minifyFile(\$inputFile, \$outputFile)\n";
$cssMinifierContent .= "    {\n";
$cssMinifierContent .= "        if (!file_exists(\$inputFile)) {\n";
$cssMinifierContent .= "            return false;\n";
$cssMinifierContent .= "        }\n";
$cssMinifierContent .= "\n";
$cssMinifierContent .= "        \$css = file_get_contents(\$inputFile);\n";
$cssMinifierContent .= "        \$minified = self::minify(\$css);\n";
$cssMinifierContent .= "\n";
$cssMinifierContent .= "        return file_put_contents(\$outputFile, \$minified);\n";
$cssMinifierContent .= "    }\n";
$cssMinifierContent .= "}\n";

if (file_put_contents($cssMinifierFile, $cssMinifierContent)) {
    echo "   ✅ CSS minifier class created: app/Core/CSSMinifier.php\n";
    $minificationResults['css_minifier'] = 'created';
    $successfulOptimizations++;
} else {
    echo "   ❌ Failed to create CSS minifier class\n";
    $minificationResults['css_minifier'] = 'failed';
}
$totalOptimizations++;

// 3. Create JavaScript minifier class
echo "\nStep 3: Creating JavaScript minifier class\n";
$jsMinifierFile = APP_PATH . '/Core/JSMinifier.php';
$jsMinifierContent = "<?php\n";
$jsMinifierContent .= "/**\n";
$jsMinifierContent .= " * APS Dream Home - JavaScript Minifier\n";
$jsMinifierContent .= " */\n";
$jsMinifierContent .= "\n";
$jsMinifierContent .= "namespace App\\Core;\n";
$jsMinifierContent .= "\n";
$jsMinifierContent .= "class JSMinifier\n";
$jsMinifierContent .= "{\n";
$jsMinifierContent .= "    public static function minify(\$js)\n";
$jsMinifierContent .= "    {\n";
$jsMinifierContent .= "        // Remove single line comments\n";
$jsMinifierContent .= "        \$js = preg_replace('/\\/\\/.*$/m', '', \$js);\n";
$jsMinifierContent .= "\n";
$jsMinifierContent .= "        // Remove multi-line comments\n";
$jsMinifierContent .= "        \$js = preg_replace('/\\/\\*.*?\\*\\//s', '', \$js);\n";
$jsMinifierContent .= "\n";
$jsMinifierContent .= "        // Remove whitespace\n";
$jsMinifierContent .= "        \$js = preg_replace('/\\s+/s', ' ', \$js);\n";
$jsMinifierContent .= "        \$js = preg_replace('/\\s*([{}();,=+\\-*&|<>!?:])\\s*/', '\\$1', \$js);\n";
$jsMinifierContent .= "\n";
$jsMinifierContent .= "        return trim(\$js);\n";
$jsMinifierContent .= "    }\n";
$jsMinifierContent .= "\n";
$jsMinifierContent .= "    public static function minifyFile(\$inputFile, \$outputFile)\n";
$jsMinifierContent .= "    {\n";
$jsMinifierContent .= "        if (!file_exists(\$inputFile)) {\n";
$jsMinifierContent .= "            return false;\n";
$jsMinifierContent .= "        }\n";
$jsMinifierContent .= "\n";
$jsMinifierContent .= "        \$js = file_get_contents(\$inputFile);\n";
$jsMinifierContent .= "        \$minified = self::minify(\$js);\n";
$jsMinifierContent .= "\n";
$jsMinifierContent .= "        return file_put_contents(\$outputFile, \$minified);\n";
$jsMinifierContent .= "    }\n";
$jsMinifierContent .= "}\n";

if (file_put_contents($jsMinifierFile, $jsMinifierContent)) {
    echo "   ✅ JavaScript minifier class created: app/Core/JSMinifier.php\n";
    $minificationResults['js_minifier'] = 'created';
    $successfulOptimizations++;
} else {
    echo "   ❌ Failed to create JavaScript minifier class\n";
    $minificationResults['js_minifier'] = 'failed';
}
$totalOptimizations++;

// 4. Create asset bundler
echo "\nStep 4: Creating asset bundler\n";
$assetBundlerFile = APP_PATH . '/Core/AssetBundler.php';
$assetBundlerContent = "<?php\n";
$assetBundlerContent .= "/**\n";
$assetBundlerContent .= " * APS Dream Home - Asset Bundler\n";
$assetBundlerContent .= " */\n";
$assetBundlerContent .= "\n";
$assetBundlerContent .= "namespace App\\Core;\n";
$assetBundlerContent .= "\n";
$assetBundlerContent .= "class AssetBundler\n";
$assetBundlerContent .= "{\n";
$assetBundlerContent .= "    private \$minifiedPath;\n";
$assetBundlerContent .= "    private \$cssMinifier;\n";
$assetBundlerContent .= "    private \$jsMinifier;\n";
$assetBundlerContent .= "\n";
$assetBundlerContent .= "    public function __construct()\n";
$assetBundlerContent .= "    {\n";
$assetBundlerContent .= "        \$this->minifiedPath = PUBLIC_PATH . '/assets/minified';\n";
$assetBundlerContent .= "        \$this->cssMinifier = new CSSMinifier();\n";
$assetBundlerContent .= "        \$this->jsMinifier = new JSMinifier();\n";
$assetBundlerContent .= "    }\n";
$assetBundlerContent .= "\n";
$assetBundlerContent .= "    public function bundleCSS(\$files, \$outputName)\n";
$assetBundlerContent .= "    {\n";
$assetBundlerContent .= "        \$bundledCSS = '';\n";
$assetBundlerContent .= "\n";
$assetBundlerContent .= "        foreach (\$files as \$file) {\n";
$assetBundlerContent .= "            if (file_exists(\$file)) {\n";
$assetBundlerContent .= "                \$css = file_get_contents(\$file);\n";
$assetBundlerContent .= "                \$bundledCSS .= \$css . '\\n';\n";
$assetBundlerContent .= "            }\n";
$assetBundlerContent .= "        }\n";
$assetBundlerContent .= "\n";
$assetBundlerContent .= "        \$minifiedCSS = CSSMinifier::minify(\$bundledCSS);\n";
$assetBundlerContent .= "        \$outputFile = \$this->minifiedPath . '/' . \$outputName . '.min.css';\n";
$assetBundlerContent .= "\n";
$assetBundlerContent .= "        return file_put_contents(\$outputFile, \$minifiedCSS);\n";
$assetBundlerContent .= "    }\n";
$assetBundlerContent .= "\n";
$assetBundlerContent .= "    public function bundleJS(\$files, \$outputName)\n";
$assetBundlerContent .= "    {\n";
$assetBundlerContent .= "        \$bundledJS = '';\n";
$assetBundlerContent .= "\n";
$assetBundlerContent .= "        foreach (\$files as \$file) {\n";
$assetBundlerContent .= "            if (file_exists(\$file)) {\n";
$assetBundlerContent .= "                \$js = file_get_contents(\$file);\n";
$assetBundlerContent .= "                \$bundledJS .= \$js . ';\\n';\n";
$assetBundlerContent .= "            }\n";
$assetBundlerContent .= "        }\n";
$assetBundlerContent .= "\n";
$assetBundlerContent .= "        \$minifiedJS = JSMinifier::minify(\$bundledJS);\n";
$assetBundlerContent .= "        \$outputFile = \$this->minifiedPath . '/' . \$outputName . '.min.js';\n";
$assetBundlerContent .= "\n";
$assetBundlerContent .= "        return file_put_contents(\$outputFile, \$minifiedJS);\n";
$assetBundlerContent .= "    }\n";
$assetBundlerContent .= "\n";
$assetBundlerContent .= "    public function getMinifiedCSS(\$filename)\n";
$assetBundlerContent .= "    {\n";
$assetBundlerContent .= "        return '/apsdreamhome/public/assets/minified/' . \$filename . '.min.css';\n";
$assetBundlerContent .= "    }\n";
$assetBundlerContent .= "\n";
$assetBundlerContent .= "    public function getMinifiedJS(\$filename)\n";
$assetBundlerContent .= "    {\n";
$assetBundlerContent .= "        return '/apsdreamhome/public/assets/minified/' . \$filename . '.min.js';\n";
$assetBundlerContent .= "    }\n";
$assetBundlerContent .= "}\n";

if (file_put_contents($assetBundlerFile, $assetBundlerContent)) {
    echo "   ✅ Asset bundler created: app/Core/AssetBundler.php\n";
    $minificationResults['asset_bundler'] = 'created';
    $successfulOptimizations++;
} else {
    echo "   ❌ Failed to create asset bundler\n";
    $minificationResults['asset_bundler'] = 'failed';
}
$totalOptimizations++;

// 5. Create asset configuration
echo "\nStep 5: Creating asset configuration\n";
$assetConfigFile = CONFIG_PATH . '/assets.php';
$assetConfigContent = "<?php\n";
$assetConfigContent .= "/**\n";
$assetConfigContent .= " * APS Dream Home - Asset Configuration\n";
$assetConfigContent .= " */\n";
$assetConfigContent .= "\n";
$assetConfigContent .= "return [\n";
$assetConfigContent .= "    'css' => [\n";
$assetConfigContent .= "        'bootstrap' => [\n";
$assetConfigContent .= "            'bootstrap.min.css',\n";
$assetConfigContent .= "            'bootstrap-icons.css'\n";
$assetConfigContent .= "        ],\n";
$assetConfigContent .= "        'custom' => [\n";
$assetConfigContent .= "            'style.css',\n";
$assetConfigContent .= "            'responsive.css',\n";
$assetConfigContent .= "            'animations.css'\n";
$assetConfigContent .= "        ]\n";
$assetConfigContent .= "    ],\n";
$assetConfigContent .= "    'js' => [\n";
$assetConfigContent .= "        'vendor' => [\n";
$assetConfigContent .= "            'bootstrap.bundle.min.js',\n";
$assetConfigContent .= "            'jquery.min.js'\n";
$assetConfigContent .= "        ],\n";
$assetConfigContent .= "        'custom' => [\n";
$assetConfigContent .= "            'utils.js',\n";
$assetConfigContent .= "            'layout.js',\n";
$assetConfigContent .= "            'lazy-load.js'\n";
$assetConfigContent .= "        ]\n";
$assetConfigContent .= "    ],\n";
$assetConfigContent .= "    'bundles' => [\n";
$assetConfigContent .= "        'vendor' => [\n";
$assetConfigContent .= "            'css' => ['bootstrap.min.css', 'bootstrap-icons.css'],\n";
$assetConfigContent .= "            'js' => ['bootstrap.bundle.min.js', 'jquery.min.js']\n";
$assetConfigContent .= "        ],\n";
$assetConfigContent .= "        'app' => [\n";
$assetConfigContent .= "            'css' => ['style.css', 'responsive.css', 'animations.css'],\n";
$assetConfigContent .= "            'js' => ['utils.js', 'layout.js', 'lazy-load.js']\n";
$assetConfigContent .= "        ]\n";
$assetConfigContent .= "    ],\n";
$assetConfigContent .= "    'minification' => [\n";
$assetConfigContent .= "        'enabled' => true,\n";
$assetConfigContent .= "        'cache_busting' => true,\n";
$assetConfigContent .= "        'gzip' => true\n";
$assetConfigContent .= "    ]\n";
$assetConfigContent .= "];\n";

if (file_put_contents($assetConfigFile, $assetConfigContent)) {
    echo "   ✅ Asset configuration created: config/assets.php\n";
    $minificationResults['asset_config'] = 'created';
    $successfulOptimizations++;
} else {
    echo "   ❌ Failed to create asset configuration\n";
    $minificationResults['asset_config'] = 'failed';
}
$totalOptimizations++;

// 6. Create asset helper
echo "\nStep 6: Creating asset helper\n";
$assetHelperFile = APP_PATH . '/Helpers/AssetHelper.php';
$assetHelperContent = "<?php\n";
$assetHelperContent .= "/**\n";
$assetHelperContent .= " * APS Dream Home - Asset Helper\n";
$assetHelperContent .= " */\n";
$assetHelperContent .= "\n";
$assetHelperContent .= "namespace App\\Helpers;\n";
$assetHelperContent .= "\n";
$assetHelperContent .= "class AssetHelper\n";
$assetHelperContent .= "{\n";
$assetHelperContent .= "    private static \$config;\n";
$assetHelperContent .= "\n";
$assetHelperContent .= "    public static function init()\n";
$assetHelperContent .= "    {\n";
$assetHelperContent .= "        self::\$config = require CONFIG_PATH . '/assets.php';\n";
$assetHelperContent .= "    }\n";
$assetHelperContent .= "\n";
$assetHelperContent .= "    public static function css(\$bundle)\n";
$assetHelperContent .= "    {\n";
$assetHelperContent .= "        if (!self::\$config) {\n";
$assetHelperContent .= "            self::init();\n";
$assetHelperContent .= "        }\n";
$assetHelperContent .= "\n";
$assetHelperContent .= "        \$minified = self::\$config['minification']['enabled'];\n";
$assetHelperContent .= "        \$suffix = \$minified ? '.min' : '';\n";
$assetHelperContent .= "\n";
$assetHelperContent .= "        return '<link rel=\"stylesheet\" href=\"/apsdreamhome/public/assets/minified/' . \$bundle . \$suffix . '.css\">';\n";
$assetHelperContent .= "    }\n";
$assetHelperContent .= "\n";
$assetHelperContent .= "    public static function js(\$bundle)\n";
$assetHelperContent .= "    {\n";
$assetHelperContent .= "        if (!self::\$config) {\n";
$assetHelperContent .= "            self::init();\n";
$assetHelperContent .= "        }\n";
$assetHelperContent .= "\n";
$assetHelperContent .= "        \$minified = self::\$config['minification']['enabled'];\n";
$assetHelperContent .= "        \$suffix = \$minified ? '.min' : '';\n";
$assetHelperContent .= "\n";
$assetHelperContent .= "        return '<script src=\"/apsdreamhome/public/assets/minified/' . \$bundle . \$suffix . '.js\"></script>';\n";
$assetHelperContent .= "    }\n";
$assetHelperContent .= "\n";
$assetHelperContent .= "    public static function image(\$path)\n";
$assetHelperContent .= "    {\n";
$assetHelperContent .= "        return '/apsdreamhome/public/assets/images/' . \$path;\n";
$assetHelperContent .= "    }\n";
$assetHelperContent .= "\n";
$assetHelperContent .= "    public static function versioned(\$path)\n";
$assetHelperContent .= "    {\n";
$assetHelperContent .= "        \$version = filemtime(PUBLIC_PATH . '/' . \$path);\n";
$assetHelperContent .= "        return '/apsdreamhome/public/' . \$path . '?v=' . \$version;\n";
$assetHelperContent .= "    }\n";
$assetHelperContent .= "}\n";

if (file_put_contents($assetHelperFile, $assetHelperContent)) {
    echo "   ✅ Asset helper created: app/Helpers/AssetHelper.php\n";
    $minificationResults['asset_helper'] = 'created';
    $successfulOptimizations++;
} else {
    echo "   ❌ Failed to create asset helper\n";
    $minificationResults['asset_helper'] = 'failed';
}
$totalOptimizations++;

// 7. Create asset build script
echo "\nStep 7: Creating asset build script\n";
$buildScriptFile = BASE_PATH . '/build_assets.php';
$buildScriptContent = "<?php\n";
$buildScriptContent .= "/**\n";
$buildScriptContent .= " * APS Dream Home - Asset Build Script\n";
$buildScriptContent .= " */\n";
$buildScriptContent .= "\n";
$buildScriptContent .= "require_once __DIR__ . '/config/paths.php';\n";
$buildScriptContent .= "require_once APP_PATH . '/Core/CSSMinifier.php';\n";
$buildScriptContent .= "require_once APP_PATH . '/Core/JSMinifier.php';\n";
$buildScriptContent .= "require_once APP_PATH . '/Core/AssetBundler.php';\n";
$buildScriptContent .= "\n";
$buildScriptContent .= "echo '📦 APS DREAM HOME - ASSET BUILD\\n';\n";
$buildScriptContent .= "echo '================================\\n\\n';\n";
$buildScriptContent .= "\n";
$buildScriptContent .= "\$bundler = new App\\Core\\AssetBundler();\n";
$buildScriptContent .= "\$config = require CONFIG_PATH . '/assets.php';\n";
$buildScriptContent .= "\n";
$buildScriptContent .= "echo 'Building CSS bundles...\\n';\n";
$buildScriptContent .= "foreach (\$config['bundles'] as \$bundleName => \$bundle) {\n";
$buildScriptContent .= "    if (!empty(\$bundle['css'])) {\n";
$buildScriptContent .= "        \$cssFiles = [];\n";
$buildScriptContent .= "        foreach (\$bundle['css'] as \$cssFile) {\n";
$buildScriptContent .= "            \$cssFiles[] = PUBLIC_PATH . '/assets/css/' . \$cssFile;\n";
$buildScriptContent .= "        }\n";
$buildScriptContent .= "\n";
$buildScriptContent .= "        if (\$bundler->bundleCSS(\$cssFiles, \$bundleName)) {\n";
$buildScriptContent .= "            echo \"✅ CSS bundle '\$bundleName' created\\n\";\n";
$buildScriptContent .= "        } else {\n";
$buildScriptContent .= "            echo \"❌ CSS bundle '\$bundleName' failed\\n\";\n";
$buildScriptContent .= "        }\n";
$buildScriptContent .= "    }\n";
$buildScriptContent .= "}\n";
$buildScriptContent .= "\n";
$buildScriptContent .= "echo '\\nBuilding JS bundles...\\n';\n";
$buildScriptContent .= "foreach (\$config['bundles'] as \$bundleName => \$bundle) {\n";
$buildScriptContent .= "    if (!empty(\$bundle['js'])) {\n";
$buildScriptContent .= "        \$jsFiles = [];\n";
$buildScriptContent .= "        foreach (\$bundle['js'] as \$jsFile) {\n";
$buildScriptContent .= "            \$jsFiles[] = PUBLIC_PATH . '/assets/js/' . \$jsFile;\n";
$buildScriptContent .= "        }\n";
$buildScriptContent .= "\n";
$buildScriptContent .= "        if (\$bundler->bundleJS(\$jsFiles, \$bundleName)) {\n";
$buildScriptContent .= "            echo \"✅ JS bundle '\$bundleName' created\\n\";\n";
$buildScriptContent .= "        } else {\n";
$buildScriptContent .= "            echo \"❌ JS bundle '\$bundleName' failed\\n\";\n";
$buildScriptContent .= "        }\n";
$buildScriptContent .= "    }\n";
$buildScriptContent .= "}\n";
$buildScriptContent .= "\n";
$buildScriptContent .= "echo '\\n🎉 Asset build completed!\\n';\n";

if (file_put_contents($buildScriptFile, $buildScriptContent)) {
    echo "   ✅ Asset build script created: build_assets.php\n";
    $minificationResults['build_script'] = 'created';
    $successfulOptimizations++;
} else {
    echo "   ❌ Failed to create asset build script\n";
    $minificationResults['build_script'] = 'failed';
}
$totalOptimizations++;

// Summary
echo "\n====================================\n";
echo "📊 ASSET MINIFICATION SUMMARY\n";
echo "====================================\n";

$successRate = round(($successfulOptimizations / $totalOptimizations) * 100, 1);
echo "📊 TOTAL OPTIMIZATIONS: $totalOptimizations\n";
echo "✅ SUCCESSFUL: $successfulOptimizations\n";
echo "📊 SUCCESS RATE: $successRate%\n\n";

echo "🔧 ASSET MINIFICATION DETAILS:\n";
foreach ($minificationResults as $category => $results) {
    echo "📋 $category:\n";
    if (is_array($results)) {
        foreach ($results as $item => $result) {
            $icon = $result === 'created' || $result === 'exists' ? '✅' : ($result === 'failed' ? '❌' : '⚠️');
            echo "   $icon $item: $result\n";
        }
    }
    echo "\n";
}

if ($successRate >= 80) {
    echo "🎉 ASSET MINIFICATION: EXCELLENT!\n";
} elseif ($successRate >= 60) {
    echo "✅ ASSET MINIFICATION: GOOD!\n";
} else {
    echo "⚠️  ASSET MINIFICATION: NEEDS IMPROVEMENT\n";
}

echo "\n🚀 Asset minification completed successfully!\n";
echo "📊 Ready for next optimization step: Server Optimization\n";
?>
