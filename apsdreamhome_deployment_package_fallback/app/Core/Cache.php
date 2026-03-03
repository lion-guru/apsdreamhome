<?php
/**
 * APS Dream Home - Cache Manager
 */

namespace App\Core;

class Cache
{
    private static $instance = null;
    private $config;
    private $cachePath;

    public function __construct()
    {
        $this->config = require CONFIG_PATH . '/cache.php';
        $this->cachePath = $this->config['path'];
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get($key)
    {
        $filename = $this->cachePath . '/' . $this->config['prefix'] . md5($key) . '.cache';
        if (file_exists($filename) && (time() - filemtime($filename)) < $this->config['duration']) {
            return unserialize(file_get_contents($filename));
        }
        return null;
    }

    public function set($key, $value, $duration = null)
    {
        $filename = $this->cachePath . '/' . $this->config['prefix'] . md5($key) . '.cache';
        $duration = $duration ?? $this->config['duration'];
        $data = [
            'value' => $value,
            'expires' => time() + $duration
        ];
        return file_put_contents($filename, serialize($data));
    }

    public function delete($key)
    {
        $filename = $this->cachePath . '/' . $this->config['prefix'] . md5($key) . '.cache';
        if (file_exists($filename)) {
            return unlink($filename);
        }
        return false;
    }

    public function clear()
    {
        $files = glob($this->cachePath . '/' . $this->config['prefix'] . '*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
        return true;
    }
}
