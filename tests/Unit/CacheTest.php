<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Core\Cache;

/**
 * Unit tests for the Cache system.
 * Tests file-based caching operations.
 */
class CacheTest extends TestCase
{
    private static $testCacheDir;

    protected function setUp(): void
    {
        self::$testCacheDir = sys_get_temp_dir() . '/aps_cache_test_' . uniqid();
        mkdir(self::$testCacheDir, 0755, true);
    }

    protected function tearDown(): void
    {
        // Clean up test cache files
        $files = glob(self::$testCacheDir . '/*.cache');
        if ($files) {
            foreach ($files as $file) {
                @unlink($file);
            }
        }
        @rmdir(self::$testCacheDir);
    }

    public function testSetAndGet(): void
    {
        Cache::init(self::$testCacheDir);
        Cache::set('test_key', 'test_value', 60);
        $result = Cache::get('test_key');
        $this->assertEquals('test_value', $result);
    }

    public function testGetReturnsDefaultForMissingKey(): void
    {
        Cache::init(self::$testCacheDir);
        $result = Cache::get('nonexistent_key', 'default_value');
        $this->assertEquals('default_value', $result);
    }

    public function testDelete(): void
    {
        Cache::init(self::$testCacheDir);
        Cache::set('to_delete', 'value', 60);
        Cache::delete('to_delete');
        $result = Cache::get('to_delete');
        $this->assertNull($result);
    }

    public function testRememberCachesCallbackResult(): void
    {
        Cache::init(self::$testCacheDir);
        $callCount = 0;
        $result = Cache::remember('remember_key', function () use (&$callCount) {
            $callCount++;
            return 'computed_value';
        }, 60);
        $this->assertEquals('computed_value', $result);
        $this->assertEquals(1, $callCount);

        // Second call should use cache, not callback
        $result2 = Cache::remember('remember_key', function () use (&$callCount) {
            $callCount++;
            return 'new_value';
        }, 60);
        $this->assertEquals('computed_value', $result2);
        $this->assertEquals(1, $callCount); // Still 1 — callback not called again
    }

    public function testClear(): void
    {
        Cache::init(self::$testCacheDir);
        Cache::set('key1', 'value1', 60);
        Cache::set('key2', 'value2', 60);
        Cache::clear();
        $this->assertNull(Cache::get('key1'));
        $this->assertNull(Cache::get('key2'));
    }

    public function testGetStats(): void
    {
        Cache::init(self::$testCacheDir);
        Cache::set('stats_key', 'stats_value', 60);
        $stats = Cache::getStats();
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_files', $stats);
        $this->assertArrayHasKey('active_files', $stats);
        $this->assertArrayHasKey('expired_files', $stats);
        $this->assertGreaterThanOrEqual(1, $stats['total_files']);
    }

    public function testStoresArrays(): void
    {
        Cache::init(self::$testCacheDir);
        $data = ['name' => 'Test', 'values' => [1, 2, 3]];
        Cache::set('array_key', $data, 60);
        $result = Cache::get('array_key');
        $this->assertEquals($data, $result);
    }

    public function testStoresObjects(): void
    {
        Cache::init(self::$testCacheDir);
        $obj = new \stdClass();
        $obj->name = 'Test Object';
        Cache::set('object_key', $obj, 60);
        $result = Cache::get('object_key');
        // Cache serializes to JSON and back — objects become associative arrays
        $this->assertIsArray($result);
        $this->assertEquals('Test Object', $result['name']);
    }
}
