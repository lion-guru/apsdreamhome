<?php

namespace Tests;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Tests\TestHelper;

/**
 * Base test case class for all tests
 * 
 * @package Tests
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Run migrations if needed
        if (function_exists('runTestMigrations')) {
            runTestMigrations();
        }
    }
    
    /**
     * Tear down the test environment.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Clean up any resources if needed
    }
    
    /**
     * Assert that the array has all the given keys.
     */
    protected function assertArrayHasKeys(array $keys, array $array, string $message = ''): void
    {
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $array, $message ?: "The array does not have the key: {$key}");
        }
    }
    
    /**
     * Assert that the given JSON response has the expected structure.
     */
    protected function assertJsonStructure(array $structure, $jsonData, string $message = ''): void
    {
        if (is_string($jsonData)) {
            $jsonData = json_decode($jsonData, true);
        }
        
        foreach ($structure as $key => $value) {
            if (is_array($value) && $key === '*') {
                $this->assertIsArray($jsonData, $message ?: 'The response is not an array');
                
                foreach ($jsonData as $item) {
                    $this->assertJsonStructure($structure['*'], $item, $message);
                }
            } elseif (is_array($value)) {
                $this->assertArrayHasKey($key, $jsonData, $message ?: "The key '{$key}' is missing from the response");
                $this->assertJsonStructure($structure[$key], $jsonData[$key], $message);
            } else {
                $this->assertArrayHasKey($value, $jsonData, $message ?: "The key '{$value}' is missing from the response");
            }
        }
    }
}
