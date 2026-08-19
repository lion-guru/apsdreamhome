<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\MlmSettings;

class MlmSettingsTest extends TestCase
{
    public function test_instantiation(): void
    {
        $this->assertTrue(class_exists(MlmSettings::class));
    }

    public function test_has_required_methods(): void
    {
        $this->assertTrue(method_exists(MlmSettings::class, 'get'));
        $this->assertTrue(method_exists(MlmSettings::class, 'set'));
        $this->assertTrue(method_exists(MlmSettings::class, 'getFloat'));
        $this->assertTrue(method_exists(MlmSettings::class, 'getInt'));
        $this->assertTrue(method_exists(MlmSettings::class, 'getBool'));
        $this->assertTrue(method_exists(MlmSettings::class, 'getAll'));
        $this->assertTrue(method_exists(MlmSettings::class, 'clearCache'));
    }

    public function test_get_returns_default_for_missing_key(): void
    {
        $result = MlmSettings::get('nonexistent_key_' . uniqid(), 'default_value');
        $this->assertEquals('default_value', $result);
    }

    public function test_get_float_returns_default(): void
    {
        $result = MlmSettings::getFloat('nonexistent_float_' . uniqid(), 3.14);
        $this->assertEquals(3.14, $result);
    }

    public function test_get_int_returns_default(): void
    {
        $result = MlmSettings::getInt('nonexistent_int_' . uniqid(), 42);
        $this->assertEquals(42, $result);
    }

    public function test_get_bool_returns_default(): void
    {
        $result = MlmSettings::getBool('nonexistent_bool_' . uniqid(), true);
        $this->assertTrue($result);
    }

    public function test_clear_cache_does_not_throw(): void
    {
        MlmSettings::clearCache();
        $this->assertTrue(true);
    }

    public function test_get_all_returns_array(): void
    {
        $result = MlmSettings::getAll();
        $this->assertIsArray($result);
    }
}
