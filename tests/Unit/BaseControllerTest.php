<?php

use PHPUnit\Framework\TestCase;
use App\Http\Controllers\BaseController;

class BaseControllerTest extends TestCase
{
    public function testBaseControllerExtendsCorrectly()
    {
        $reflection = new ReflectionClass(BaseController::class);
        $this->assertTrue($reflection->isAbstract() || $reflection->hasMethod('render'));
    }

    public function testRenderMethodExists()
    {
        $this->assertTrue(method_exists(BaseController::class, 'render'));
    }

    public function testCsrfProtection()
    {
        $this->assertTrue(method_exists(BaseController::class, 'validateCsrfOrFail') || method_exists(BaseController::class, 'skipCsrfProtection'));
    }

    public function testEnforceTenantStatusExists()
    {
        $this->assertTrue(method_exists(BaseController::class, 'enforceTenantStatus'));
    }
}
