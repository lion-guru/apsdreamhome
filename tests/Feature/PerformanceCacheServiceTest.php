<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\PerformanceCacheService;
use App\Http\Controllers\PerformanceCacheController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PerformanceCacheServiceTest extends TestCase
{
    use RefreshDatabase;

    private PerformanceCacheService $cacheService;
    private PerformanceCacheController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheService = app(PerformanceCacheService::class);
        $this->controller = new PerformanceCacheController($this->cacheService);
    }

    /** @test */
    public function it_can_set_and_get_cache_item()
    {
        $key = 'test_key_' . time();
        $value = ['data' => 'test_value', 'timestamp' => time()];

        // Set cache item
        $result = $this->cacheService->set($key, $value, 3600);
        $this->assertTrue($result);

        // Get cache item
        $retrievedValue = $this->cacheService->get($key);
        $this->assertEquals($value, $retrievedValue);

        // Get with default
        $nonExistentValue = $this->cacheService->get('non_existent_key', 'default');
        $this->assertEquals('default', $nonExistentValue);
    }

    /** @test */
    public function it_can_remember_value_with_callback()
    {
        $key = 'remember_key_' . time();
        $callback = function () {
            return 'remembered_value_' . time();
        };

        // First call should execute callback
        $value1 = $this->cacheService->remember($key, 3600, $callback);
        $this->assertIsString($value1);

        // Second call should return cached value
        $value2 = $this->cacheService->remember($key, 3600, $callback);
        $this->assertEquals($value1, $value2);
    }

    /** @test */
    public function it_can_delete_cache_item()
    {
        $key = 'delete_key_' . time();
        $value = 'test_value';

        // Set and verify
        $this->cacheService->set($key, $value, 3600);
        $this->assertEquals($value, $this->cacheService->get($key));

        // Delete and verify
        $result = $this->cacheService->delete($key);
        $this->assertTrue($result);
        $this->assertNull($this->cacheService->get($key));
    }

    /** @test */
    public function it_can_clear_all_cache()
    {
        // Set multiple items
        $this->cacheService->set('key1', 'value1', 3600);
        $this->cacheService->set('key2', 'value2', 3600);
        $this->cacheService->set('key3', 'value3', 3600);

        // Verify items exist
        $this->assertEquals('value1', $this->cacheService->get('key1'));
        $this->assertEquals('value2', $this->cacheService->get('key2'));
        $this->assertEquals('value3', $this->cacheService->get('key3'));

        // Clear all
        $result = $this->cacheService->clear();
        $this->assertTrue($result);

        // Verify items are gone
        $this->assertNull($this->cacheService->get('key1'));
        $this->assertNull($this->cacheService->get('key2'));
        $this->assertNull($this->cacheService->get('key3'));
    }

    /** @test */
    public function it_can_memoize_function_results()
    {
        $callCount = 0;
        $callback = function ($input) use (&$callCount) {
            $callCount++;
            return "result_{$input}_" . time();
        };

        $args = ['input1', 'input2'];

        // First call should execute function
        $result1 = $this->cacheService->memoize($callback, $args, 3600);
        $this->assertEquals(1, $callCount);

        // Second call should return cached result
        $result2 = $this->cacheService->memoize($callback, $args, 3600);
        $this->assertEquals($result1, $result2);
        $this->assertEquals(1, $callCount); // Should not have increased
    }

    /** @test */
    public function it_can_cache_query_results()
    {
        $queryKey = 'test_query_' . time();
        $queryCallback = function () {
            return ['query_result' => 'sample_data', 'timestamp' => time()];
        };

        // First call should execute query
        $result1 = $this->cacheService->cacheQuery($queryKey, $queryCallback, 3600);
        $this->assertIsArray($result1);
        $this->assertArrayHasKey('query_result', $result1);

        // Second call should return cached result
        $result2 = $this->cacheService->cacheQuery($queryKey, $queryCallback, 3600);
        $this->assertEquals($result1, $result2);
    }

    /** @test */
    public function it_can_cache_api_responses()
    {
        $endpoint = '/api/test_' . time();
        $apiCallback = function () {
            return ['api_response' => 'sample_data', 'status' => 'success'];
        };

        // First call should execute API call
        $result1 = $this->cacheService->cacheApiResponse($endpoint, $apiCallback, 300);
        $this->assertIsArray($result1);
        $this->assertEquals('success', $result1['status']);

        // Second call should return cached result
        $result2 = $this->cacheService->cacheApiResponse($endpoint, $apiCallback, 300);
        $this->assertEquals($result1, $result2);
    }

    /** @test */
    public function it_can_cache_computed_values()
    {
        $key = 'computed_key_' . time();
        $computeCallback = function () {
            return ['computed_value' => 'calculation_result', 'computation_time' => '0.05s'];
        };

        // First call should execute computation
        $result1 = $this->cacheService->cacheComputed($key, $computeCallback, 3600);
        $this->assertIsArray($result1);
        $this->assertArrayHasKey('computed_value', $result1);

        // Second call should return cached result
        $result2 = $this->cacheService->cacheComputed($key, $computeCallback, 3600);
        $this->assertEquals($result1, $result2);
    }

    /** @test */
    public function it_can_get_cache_statistics()
    {
        // Perform some cache operations
        $this->cacheService->set('stat_key1', 'value1', 3600);
        $this->cacheService->get('stat_key1'); // Hit
        $this->cacheService->get('non_existent_key'); // Miss
        $this->cacheService->delete('stat_key1');

        $stats = $this->cacheService->getStats();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('hits', $stats);
        $this->assertArrayHasKey('misses', $stats);
        $this->assertArrayHasKey('sets', $stats);
        $this->assertArrayHasKey('deletes', $stats);
        $this->assertArrayHasKey('total_requests', $stats);
        $this->assertArrayHasKey('hit_rate', $stats);
        $this->assertArrayHasKey('cache_driver', $stats);

        $this->assertGreaterThanOrEqual(1, $stats['sets']);
        $this->assertGreaterThanOrEqual(1, $stats['hits']);
        $this->assertGreaterThanOrEqual(1, $stats['misses']);
        $this->assertGreaterThanOrEqual(1, $stats['deletes']);
    }

    /** @test */
    public function it_can_reset_statistics()
    {
        // Perform some operations
        $this->cacheService->set('reset_key', 'value', 3600);
        $this->cacheService->get('reset_key');

        // Get stats before reset
        $statsBefore = $this->cacheService->getStats();
        $this->assertGreaterThan(0, $statsBefore['sets']);

        // Reset stats
        $this->cacheService->resetStats();

        // Verify stats are reset
        $statsAfter = $this->cacheService->getStats();
        $this->assertEquals(0, $statsAfter['hits']);
        $this->assertEquals(0, $statsAfter['misses']);
        $this->assertEquals(0, $statsAfter['sets']);
        $this->assertEquals(0, $statsAfter['deletes']);
        $this->assertEquals(0, $statsAfter['clears']);
    }

    /** @test */
    public function it_can_get_cache_info()
    {
        $info = $this->cacheService->getCacheInfo();

        $this->assertIsArray($info);
        $this->assertArrayHasKey('driver', $info);
        $this->assertArrayHasKey('prefix', $info);
        $this->assertArrayHasKey('supports_tags', $info);
        $this->assertArrayHasKey('supports_locking', $info);
        $this->assertArrayHasKey('supports_many', $info);

        $this->assertIsString($info['driver']);
        $this->assertIsString($info['prefix']);
        $this->assertIsBool($info['supports_tags']);
    }

    /** @test */
    public function it_can_warm_up_cache()
    {
        $warmupData = [
            'warm_key1' => [
                'value' => 'warm_value1',
                'ttl' => 3600
            ],
            'warm_key2' => [
                'value' => 'warm_value2',
                'ttl' => 1800,
                'options' => ['tags' => ['warmup']]
            ]
        ];

        $this->cacheService->warmUp($warmupData);

        // Verify warmup data is cached
        $this->assertEquals('warm_value1', $this->cacheService->get('warm_key1'));
        $this->assertEquals('warm_value2', $this->cacheService->get('warm_key2'));
    }

    /** @test */
    public function it_can_get_cache_size()
    {
        $size = $this->cacheService->getCacheSize();

        $this->assertIsArray($size);
        // The structure depends on the cache driver, so we check for common keys
        $possibleKeys = ['total_bytes', 'used_memory', 'file_count', 'items_count'];
        $hasValidKey = false;
        
        foreach ($possibleKeys as $key) {
            if (array_key_exists($key, $size)) {
                $hasValidKey = true;
                break;
            }
        }
        
        $this->assertTrue($hasValidKey, 'Cache size should contain at least one valid metric');
    }

    /** @test */
    public function it_can_optimize_cache()
    {
        $optimizations = $this->cacheService->optimize();

        $this->assertIsArray($optimizations);
        $this->assertNotEmpty($optimizations);
        $this->assertContains('Reset cache statistics', $optimizations);
    }

    /** @test */
    public function it_can_generate_cache_report()
    {
        // Perform some operations first
        $this->cacheService->set('report_key', 'report_value', 3600);
        $this->cacheService->get('report_key');

        $report = $this->cacheService->generateReport();

        $this->assertIsArray($report);
        $this->assertArrayHasKey('timestamp', $report);
        $this->assertArrayHasKey('statistics', $report);
        $this->assertArrayHasKey('cache_info', $report);
        $this->assertArrayHasKey('cache_size', $report);
        $this->assertArrayHasKey('performance_metrics', $report);

        $this->assertIsString($report['timestamp']);
        $this->assertIsArray($report['statistics']);
        $this->assertIsArray($report['cache_info']);
        $this->assertIsArray($report['cache_size']);
        $this->assertIsArray($report['performance_metrics']);
    }

    /** @test */
    public function performance_cache_api_endpoints_work()
    {
        // Test set endpoint
        $response = $this->postJson('/api/performance-cache/set', [
            'key' => 'api_test_key',
            'value' => 'api_test_value',
            'ttl' => 3600
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['success']);

        // Test get endpoint
        $response = $this->getJson('/api/performance-cache/get', [
            'key' => 'api_test_key'
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);

        // Test stats endpoint
        $response = $this->getJson('/api/performance-cache/stats');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);

        // Test info endpoint
        $response = $this->getJson('/api/performance-cache/info');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);

        // Test dashboard endpoint
        $response = $this->getJson('/api/performance-cache/dashboard');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
    }

    /** @test */
    public function it_can_set_cache_via_api()
    {
        $response = $this->postJson('/api/performance-cache/set', [
            'key' => 'api_set_test',
            'value' => ['data' => 'test', 'timestamp' => time()],
            'ttl' => 1800,
            'tags' => ['test', 'api'],
            'compressed' => false,
            'priority' => 'high'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
        $response->assertJsonStructure(['success', 'message']);
    }

    /** @test */
    public function it_can_get_cache_via_api()
    {
        // First set a value
        $this->cacheService->set('api_get_test', 'test_value', 3600);

        $response = $this->getJson('/api/performance-cache/get', [
            'key' => 'api_get_test',
            'default' => 'default_value'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data.found' => true
        ]);
        $response->assertJsonStructure(['success', 'data.key', 'data.value', 'data.found']);
    }

    /** @test */
    public function it_can_delete_cache_via_api()
    {
        // First set a value
        $this->cacheService->set('api_delete_test', 'test_value', 3600);

        $response = $this->deleteJson('/api/performance-cache/delete', [
            'key' => 'api_delete_test'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
        $response->assertJsonStructure(['success', 'message']);
    }

    /** @test */
    public function it_can_clear_cache_via_api()
    {
        // Set some values first
        $this->cacheService->set('clear_test1', 'value1', 3600);
        $this->cacheService->set('clear_test2', 'value2', 3600);

        $response = $this->deleteJson('/api/performance-cache/clear');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
        $response->assertJsonStructure(['success', 'message']);
    }

    /** @test */
    public function it_can_remember_value_via_api()
    {
        $response = $this->postJson('/api/performance-cache/remember', [
            'key' => 'api_remember_test',
            'ttl' => 3600,
            'callback' => 'test_callback'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
        $response->assertJsonStructure(['success', 'message', 'data']);
    }

    /** @test */
    public function it_can_get_cache_stats_via_api()
    {
        // Perform some operations first
        $this->cacheService->set('stats_test', 'value', 3600);
        $this->cacheService->get('stats_test');

        $response = $this->getJson('/api/performance-cache/stats');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
        $response->assertJsonStructure(['success', 'data']);
        
        $data = $response->json('data');
        $this->assertArrayHasKey('hits', $data);
        $this->assertArrayHasKey('misses', $data);
        $this->assertArrayHasKey('hit_rate', $data);
    }

    /** @test */
    public function it_can_optimize_cache_via_api()
    {
        $response = $this->postJson('/api/performance-cache/optimize');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
        $response->assertJsonStructure(['success', 'message', 'data']);
        
        $data = $response->json('data');
        $this->assertArrayHasKey('optimizations', $data);
        $this->assertArrayHasKey('optimizations_count', $data);
    }

    /** @test */
    public function it_can_generate_cache_report_via_api()
    {
        $response = $this->getJson('/api/performance-cache/report');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
        $response->assertJsonStructure(['success', 'message', 'data']);
        
        $data = $response->json('data');
        $this->assertArrayHasKey('timestamp', $data);
        $this->assertArrayHasKey('statistics', $data);
        $this->assertArrayHasKey('cache_info', $data);
        $this->assertArrayHasKey('cache_size', $data);
        $this->assertArrayHasKey('performance_metrics', $data);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        // Test set without key
        $response = $this->postJson('/api/performance-cache/set', [
            'value' => 'test'
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['key']);

        // Test get without key
        $response = $this->getJson('/api/performance-cache/get');
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['key']);

        // Test delete without key
        $response = $this->deleteJson('/api/performance-cache/delete');
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['key']);
    }

    /** @test */
    public function it_can_warm_up_cache_via_api()
    {
        $warmupData = [
            ['key' => 'warm_api_test1', 'value' => 'warm_value1', 'ttl' => 3600],
            ['key' => 'warm_api_test2', 'value' => 'warm_value2', 'ttl' => 1800]
        ];

        $response = $this->postJson('/api/performance-cache/warmup', [
            'warmup_data' => $warmupData
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
        $response->assertJsonStructure(['success', 'message', 'data']);
        
        $data = $response->json('data');
        $this->assertEquals(2, $data['items_count']);
    }

    protected function tearDown(): void
    {
        Cache::flush();
        parent::tearDown();
    }
}
