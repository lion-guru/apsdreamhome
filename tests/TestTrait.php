<?php

namespace Tests;

use PHPUnit\Framework\Assert;

trait TestTrait
{
    use Assert;
    
    /**
     * Assert that the array has all the given keys.
     *
     * @param array $keys
     * @param array $array
     * @param string $message
     */
    public function assertArrayHasKeys(array $keys, array $array, string $message = ''): void
    {
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $array, $message ?: "The array does not have the key: {$key}");
        }
    }
    
    /**
     * Assert that the given JSON response has the expected structure.
     *
     * @param array $structure
     * @param mixed $jsonData
     * @param string $message
     */
    public function assertJsonStructure(array $structure, $jsonData, string $message = ''): void
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
