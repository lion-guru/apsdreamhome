<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\FormSelectDataService;

class FormSelectDataServiceTest extends TestCase
{
    public function test_instantiation(): void
    {
        $this->assertTrue(class_exists(FormSelectDataService::class));
    }

    public function test_has_required_methods(): void
    {
        $this->assertTrue(method_exists(FormSelectDataService::class, 'getCustomers'));
        $this->assertTrue(method_exists(FormSelectDataService::class, 'getAssociates'));
        $this->assertTrue(method_exists(FormSelectDataService::class, 'getAgents'));
        $this->assertTrue(method_exists(FormSelectDataService::class, 'getProperties'));
        $this->assertTrue(method_exists(FormSelectDataService::class, 'getPlots'));
        $this->assertTrue(method_exists(FormSelectDataService::class, 'getColonies'));
        $this->assertTrue(method_exists(FormSelectDataService::class, 'getStates'));
        $this->assertTrue(method_exists(FormSelectDataService::class, 'getDistricts'));
        $this->assertTrue(method_exists(FormSelectDataService::class, 'getEmployees'));
        $this->assertTrue(method_exists(FormSelectDataService::class, 'getUsersByRole'));
    }

    public function test_get_customers_returns_array(): void
    {
        $result = FormSelectDataService::getCustomers();
        $this->assertIsArray($result);
    }

    public function test_get_associates_returns_array(): void
    {
        $result = FormSelectDataService::getAssociates();
        $this->assertIsArray($result);
    }

    public function test_get_agents_returns_array(): void
    {
        $result = FormSelectDataService::getAgents();
        $this->assertIsArray($result);
    }

    public function test_get_properties_returns_array(): void
    {
        $result = FormSelectDataService::getProperties();
        $this->assertIsArray($result);
    }

    public function test_get_plots_returns_array(): void
    {
        $result = FormSelectDataService::getPlots();
        $this->assertIsArray($result);
    }

    public function test_get_colonies_returns_array(): void
    {
        $result = FormSelectDataService::getColonies();
        $this->assertIsArray($result);
    }

    public function test_get_states_returns_array(): void
    {
        $result = FormSelectDataService::getStates();
        $this->assertIsArray($result);
    }

    public function test_get_districts_returns_array(): void
    {
        $result = FormSelectDataService::getDistricts();
        $this->assertIsArray($result);
    }

    public function test_get_employees_returns_array(): void
    {
        $result = FormSelectDataService::getEmployees();
        $this->assertIsArray($result);
    }

    public function test_get_users_by_role_returns_array(): void
    {
        $result = FormSelectDataService::getUsersByRole('customer');
        $this->assertIsArray($result);
    }

    public function test_get_customers_with_filter(): void
    {
        $result = FormSelectDataService::getCustomers(['status' => 'active']);
        $this->assertIsArray($result);
    }

    public function test_get_properties_with_filter(): void
    {
        $result = FormSelectDataService::getProperties(['status' => 'active']);
        $this->assertIsArray($result);
    }
}
