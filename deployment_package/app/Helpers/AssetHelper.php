<?php
/**
 * APS Dream Home - Asset Helper
 */

namespace App\Helpers;

class AssetHelper
{
    private static $config;

    public static function init()
    {
        self::$config = require CONFIG_PATH . '/assets.php';
    }

    public static function css($bundle)
    {
        if (!self::$config) {
            self::init();
        }

        $minified = self::$config['minification']['enabled'];
        $suffix = $minified ? '.min' : '';

        return '<link rel="stylesheet" href="http://localhost./public/assets/minified/' . $bundle . $suffix . '.css">';
    }

    public static function js($bundle)
    {
        if (!self::$config) {
            self::init();
        }

        $minified = self::$config['minification']['enabled'];
        $suffix = $minified ? '.min' : '';

        return '<script src="http://localhost./public/assets/minified/' . $bundle . $suffix . '.js"></script>';
    }

    public static function image($path)
    {
        return 'http://localhost./public/assets/images/' . $path;
    }

    public static function versioned($path)
    {
        $version = filemtime(PUBLIC_PATH . '/' . $path);
        return 'http://localhost./public/' . $path . '?v=' . $version;
    }
}
