<?php
/**
 * APS Dream Home - Asset Bundler
 */

namespace App\Core;

class AssetBundler
{
    private $minifiedPath;
    private $cssMinifier;
    private $jsMinifier;

    public function __construct()
    {
        $this->minifiedPath = PUBLIC_PATH . '/assets/minified';
        $this->cssMinifier = new CSSMinifier();
        $this->jsMinifier = new JSMinifier();
    }

    public function bundleCSS($files, $outputName)
    {
        $bundledCSS = '';

        foreach ($files as $file) {
            if (file_exists($file)) {
                $css = file_get_contents($file);
                $bundledCSS .= $css . '\n';
            }
        }

        $minifiedCSS = CSSMinifier::minify($bundledCSS);
        $outputFile = $this->minifiedPath . '/' . $outputName . '.min.css';

        return file_put_contents($outputFile, $minifiedCSS);
    }

    public function bundleJS($files, $outputName)
    {
        $bundledJS = '';

        foreach ($files as $file) {
            if (file_exists($file)) {
                $js = file_get_contents($file);
                $bundledJS .= $js . ';\n';
            }
        }

        $minifiedJS = JSMinifier::minify($bundledJS);
        $outputFile = $this->minifiedPath . '/' . $outputName . '.min.js';

        return file_put_contents($outputFile, $minifiedJS);
    }

    public function getMinifiedCSS($filename)
    {
        return '/apsdreamhome/public/assets/minified/' . $filename . '.min.css';
    }

    public function getMinifiedJS($filename)
    {
        return '/apsdreamhome/public/assets/minified/' . $filename . '.min.js';
    }
}
