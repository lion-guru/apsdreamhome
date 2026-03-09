<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\DependencyContainer;
use App\Contracts\ContainerInterface;
use App\Contracts\ServiceNotFoundException;
use App\Contracts\ContainerException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DependencyContainerTest extends TestCase
{
    use RefreshDatabase;

    private DependencyContainer $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = DependencyContainer::getInstance();
        $this->container->clear();
    }

    protected function tearDown(): void
    {
        $this->container->clear();
        parent::tearDown();
    }

    /** @test */
    public function it_implements_container_interface()
    {
        $this->assertInstanceOf(ContainerInterface::class, $this->container);
    }

    /** @test */
    public function it_can_register_and_resolve_simple_service()
    {
        $this->container->register('test_service', fn() => 'Hello World');

        $this->assertTrue($this->container->has('test_service'));
        $this->assertEquals('Hello World', $this->container->get('test_service'));
    }

    /** @test */
    public function it_can_register_and_resolve_class_service()
    {
        $this->container->register('test_class', \stdClass::class);

        $service = $this->container->get('test_class');
        $this->assertInstanceOf(\stdClass::class, $service);
    }

    /** @test */
    public function it_can_register_singleton_service()
    {
        $this->container->singleton('singleton_service', fn() => new \stdClass());

        $instance1 = $this->container->get('singleton_service');
        $instance2 = $this->container->get('singleton_service');

        $this->assertSame($instance1, $instance2);
    }

    /** @test */
    public function it_can_register_non_singleton_service()
    {
        $this->container->register('non_singleton', fn() => new \stdClass());

        $instance1 = $this->container->get('non_singleton');
        $instance2 = $this->container->get('non_singleton');

        $this->assertNotSame($instance1, $instance2);
    }

    /** @test */
    public function it_can_create_aliases()
    {
        $this->container->register('original_service', fn() => 'Original');
        $this->container->alias('alias', 'original_service');

        $this->assertTrue($this->container->has('alias'));
        $this->assertEquals('Original', $this->container->get('alias'));
    }

    /** @test */
    public function it_throws_exception_for_non_existent_service()
    {
        $this->expectException(ServiceNotFoundException::class);
        $this->container->get('non_existent_service');
    }

    /** @test */
    public function it_can_remove_service()
    {
        $this->container->register('temp_service', fn() => 'Temporary');
        $this->assertTrue($this->container->has('temp_service'));

        $this->container->remove('temp_service');
        $this->assertFalse($this->container->has('temp_service'));
    }

    /** @test */
    public function it_can_clear_all_services()
    {
        $this->container->register('service1', fn() => 'Service 1');
        $this->container->register('service2', fn() => 'Service 2');

        $this->container->clear();

        $this->assertFalse($this->container->has('service1'));
        $this->assertFalse($this->container->has('service2'));
    }

    /** @test */
    public function it_returns_registered_services_list()
    {
        $this->container->register('service1', fn() => 'Service 1');
        $this->container->register('service2', fn() => 'Service 2');

        $services = $this->container->getRegisteredServices();

        $this->assertCount(2, $services);
        $this->assertContains('service1', $services);
        $this->assertContains('service2', $services);
    }

    /** @test */
    public function it_returns_aliases_list()
    {
        $this->container->register('original', fn() => 'Original');
        $this->container->alias('alias1', 'original');
        $this->container->alias('alias2', 'original');

        $aliases = $this->container->getAliases();

        $this->assertCount(2, $aliases);
        $this->assertEquals('original', $aliases['alias1']);
        $this->assertEquals('original', $aliases['alias2']);
    }

    /** @test */
    public function it_can_resolve_with_dependencies()
    {
        // Register a dependency
        $this->container->register('dependency', fn() => 'Dependency Value');

        // Register a service that uses the dependency
        $this->container->register('main_service', function($container) {
            return new class($container->get('dependency')) {
                public function __construct(private $dependency) {}
                public function getDependency() { return $this->dependency; }
            };
        });

        $service = $this->container->get('main_service');
        $this->assertEquals('Dependency Value', $service->getDependency());
    }

    /** @test */
    public function it_can_register_instance()
    {
        $instance = new \stdClass();
        $instance->property = 'test';

        $this->container->register('instance_service', $instance);

        $resolved = $this->container->get('instance_service');
        $this->assertSame($instance, $resolved);
        $this->assertEquals('test', $resolved->property);
    }

    /** @test */
    public function it_can_call_callable_with_dependency_injection()
    {
        $this->container->register('test_dep', fn() => 'Test Dependency');

        $result = $this->container->call(function($test_dep) {
            return "Received: {$test_dep}";
        });

        $this->assertEquals('Received: Test Dependency', $result);
    }

    /** @test */
    public function container_api_endpoints_work()
    {
        // Test container index endpoint
        $response = $this->getJson('/api/container');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);

        // Test container stats endpoint
        $response = $this->getJson('/api/container/stats/info');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);

        // Test container test endpoint
        $response = $this->getJson('/api/container/test/functionality');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
    }

    /** @test */
    public function container_register_endpoint_works()
    {
        $response = $this->postJson('/api/container/register', [
            'id' => 'test_api_service',
            'definition' => fn() => 'API Test Service',
            'shared' => false
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify service was registered
        $this->assertTrue($this->container->has('test_api_service'));
    }

    /** @test */
    public function container_show_endpoint_works()
    {
        $this->container->register('test_show', fn() => 'Show Test');

        $response = $this->getJson('/api/container/test_show');
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'id' => 'test_show',
                'registered' => true,
                'resolvable' => true
            ]
        ]);
    }

    /** @test */
    public function container_resolve_endpoint_works()
    {
        $this->container->register('test_resolve', fn() => 'Resolve Test');

        $response = $this->getJson('/api/container/test_resolve/resolve');
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'id' => 'test_resolve',
                'type' => 'string'
            ]
        ]);
    }

    /** @test */
    public function container_destroy_endpoint_works()
    {
        $this->container->register('test_destroy', fn() => 'Destroy Test');

        $response = $this->deleteJson('/api/container/test_destroy');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify service was removed
        $this->assertFalse($this->container->has('test_destroy'));
    }

    /** @test */
    public function container_clear_endpoint_works()
    {
        $this->container->register('test_clear1', fn() => 'Clear Test 1');
        $this->container->register('test_clear2', fn() => 'Clear Test 2');

        $response = $this->deleteJson('/api/container');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify all services were cleared
        $this->assertFalse($this->container->has('test_clear1'));
        $this->assertFalse($this->container->has('test_clear2'));
    }

    /** @test */
    public function it_handles_non_existent_service_gracefully()
    {
        $response = $this->getJson('/api/container/non_existent_service');
        $response->assertStatus(404);
        $response->assertJson(['success' => false]);

        $response = $this->getJson('/api/container/non_existent_service/resolve');
        $response->assertStatus(404);
        $response->assertJson(['success' => false]);

        $response = $this->deleteJson('/api/container/non_existent_service');
        $response->assertStatus(404);
        $response->assertJson(['success' => false]);
    }

    /** @test */
    public function it_validates_registration_data()
    {
        $response = $this->postJson('/api/container/register', [
            'definition' => 'Invalid - missing id'
        ]);

        $response->assertStatus(422);
    }
}
