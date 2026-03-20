<?php

namespace App\Services\Caching;

/**
 * Fallback APCu functions for environments without APCu extension
 */
if (!function_exists('apcu_fetch')) {
    function apcu_fetch($key, &$success = null)
    {
        $success = false;
        return false;
    }
}

if (!function_exists('apcu_store')) {
    function apcu_store($key, $value, $ttl = 0)
    {
        return false;
    }
}

if (!function_exists('apcu_delete')) {
    function apcu_delete($key)
    {
        return false;
    }
}

if (!function_exists('apcu_clear_cache')) {
    function apcu_clear_cache()
    {
        return false;
    }
}

if (!function_exists('apcu_cache_info')) {
    function apcu_cache_info($limited = false)
    {
        return [];
    }
}

if (!function_exists('apcu_enabled')) {
    function apcu_enabled()
    {
        return false;
    }
}

/**
 * APCu Cache Layer Implementation
 */
class ApcuCacheLayer
{
    public function get($key)
    {
        $success = false;
        if (function_exists('apcu_fetch')) {
            $value = apcu_fetch($key, $success);
            return $success ? $value : null;
        }
        return null;
    }

    public function set($key, $value, $ttl)
    {
        if (function_exists('apcu_store')) {
            return apcu_store($key, $value, $ttl);
        }
        return false;
    }

    public function delete($key)
    {
        if (function_exists('apcu_delete')) {
            return apcu_delete($key);
        }
        return false;
    }

    public function clear()
    {
        if (function_exists('apcu_clear_cache')) {
            return apcu_clear_cache();
        }
        return false;
    }

    public function getStats()
    {
        if (function_exists('apcu_cache_info')) {
            return apcu_cache_info(true);
        }
        return [];
    }
}

/**
 * File Cache Layer Implementation
 */
class FileCacheLayer
{
    private $cacheDir;

    public function __construct($cacheDir)
    {
        $this->cacheDir = $cacheDir;
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
    }

    public function get($key)
    {
        $filePath = $this->getFilePath($key);
        if (file_exists($filePath)) {
            $data = unserialize(file_get_contents($filePath));
            if ($data['expires'] > time()) {
                return $data['value'];
            }
            unlink($filePath);
        }
        return null;
    }

    public function set($key, $value, $ttl)
    {
        $filePath = $this->getFilePath($key);
        $data = [
            'value' => $value,
            'expires' => time() + $ttl
        ];
        return file_put_contents($filePath, serialize($data)) !== false;
    }

    public function delete($key)
    {
        $filePath = $this->getFilePath($key);
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return false;
    }

    public function clear()
    {
        $files = glob($this->cacheDir . '*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
        return true;
    }

    public function getStats()
    {
        $files = glob($this->cacheDir . '*.cache');
        return [
            'files' => count($files),
            'size' => array_sum(array_map('filesize', $files))
        ];
    }

    private function getFilePath($key)
    {
        return $this->cacheDir . '/' . md5($key) . '.cache';
    }
}
