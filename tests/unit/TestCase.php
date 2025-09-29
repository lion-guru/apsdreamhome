<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Base test case for all unit tests
 */
class TestCase extends BaseTestCase
{
    use \Tests\TestTrait;
    
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
    }
}
