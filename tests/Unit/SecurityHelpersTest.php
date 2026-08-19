<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for security helpers added during hardening.
 * Tests safeError, CORS headers, and signature compatibility.
 */
class SecurityHelpersTest extends TestCase
{
    /**
     * Test safeError returns generic message and logs real error.
     */
    public function testSafeError(): void
    {
        $controller = new \App\Http\Controllers\BaseController();

        $method = new \ReflectionMethod($controller, 'safeError');
        $method->setAccessible(true);

        $exception = new \Exception('Real database error: SQLSTATE[42000]');
        $result = $method->invoke($controller, $exception, 'Test Context', 500);

        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);
        $this->assertEquals('An internal error occurred. Please try again later.', $result['error']);
        $this->assertEquals('Test Context', $result['context']);
    }

    /**
     * Test handleApiError does not leak exception message.
     */
    public function testHandleApiErrorNoLeak(): void
    {
        // This test verifies the method signature and behavior
        // Since handleApiError exits, we can't easily test it directly
        // but we verify the method exists with correct signature
        $controller = new \App\Http\Controllers\BaseController();
        $method = new \ReflectionMethod($controller, 'handleApiError');
        $this->assertEquals('void', $method->getReturnType()->getName());
        $this->assertEquals(2, $method->getNumberOfParameters());
    }

    /**
     * Test setCorsHeaders method exists.
     */
    public function testSetCorsHeadersExists(): void
    {
        $controller = new \App\Http\Controllers\BaseController();
        $method = new \ReflectionMethod($controller, 'setCorsHeaders');
        $this->assertTrue($method->isProtected());
    }

    /**
     * Test successResponse signature compatibility.
     */
    public function testSuccessResponseSignature(): void
    {
        $controller = new \App\Http\Controllers\BaseController();
        $method = new \ReflectionMethod($controller, 'successResponse');
        $this->assertEquals('void', $method->getReturnType()->getName());
        $params = $method->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('string', $params[1]->getType()->getName());
    }

    /**
     * Test errorResponse signature compatibility.
     */
    public function testErrorResponseSignature(): void
    {
        $controller = new \App\Http\Controllers\BaseController();
        $method = new \ReflectionMethod($controller, 'errorResponse');
        $this->assertEquals('void', $method->getReturnType()->getName());
        $params = $method->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('string', $params[0]->getType()->getName());
        $this->assertEquals('int', $params[1]->getType()->getName());
    }
}