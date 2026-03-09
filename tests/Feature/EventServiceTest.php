<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\EventService;
use App\Http\Controllers\EventControllerNew;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EventServiceTest extends TestCase
{
    use RefreshDatabase;

    private EventService $eventService;
    private EventControllerNew $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->eventService = app(EventService::class);
        $this->controller = new EventControllerNew($this->eventService);
    }

    /** @test */
    public function it_can_subscribe_to_an_event()
    {
        $eventName = 'test.event';
        $handlerCalled = false;
        $handler = function ($payload, $metadata) use (&$handlerCalled) {
            $handlerCalled = true;
        };

        $subscriptionId = $this->eventService->subscribe($eventName, $handler);

        $this->assertIsString($subscriptionId);
        $this->assertStringStartsWith('sub_', $subscriptionId);
    }

    /** @test */
    public function it_can_subscribe_to_wildcard_events()
    {
        $pattern = 'user.*';
        $handlerCalled = false;
        $handler = function ($payload, $metadata) use (&$handlerCalled) {
            $handlerCalled = true;
        };

        $subscriptionId = $this->eventService->subscribeWildcard($pattern, $handler);

        $this->assertIsString($subscriptionId);
        $this->assertStringStartsWith('sub_', $subscriptionId);
    }

    /** @test */
    public function it_can_publish_and_handle_events()
    {
        $eventName = 'user.registered';
        $handledPayload = null;
        $handledMetadata = null;

        $handler = function ($payload, $metadata) use (&$handledPayload, &$handledMetadata) {
            $handledPayload = $payload;
            $handledMetadata = $metadata;
        };

        $this->eventService->subscribe($eventName, $handler);

        $payload = ['email' => 'test@example.com', 'name' => 'Test User'];
        $metadata = ['type' => 'user', 'priority' => 3];

        $eventId = $this->eventService->publish($eventName, $payload, $metadata);

        $this->assertIsString($eventId);
        $this->assertStringStartsWith('event_', $eventId);
        $this->assertEquals($payload, $handledPayload);
        $this->assertArrayHasKey('type', $handledMetadata);
        $this->assertEquals('user', $handledMetadata['type']);
    }

    /** @test */
    public function it_can_handle_wildcard_events()
    {
        $wildcardHandled = false;
        $specificHandled = false;

        $wildcardHandler = function ($payload, $metadata) use (&$wildcardHandled) {
            $wildcardHandled = true;
        };

        $specificHandler = function ($payload, $metadata) use (&$specificHandled) {
            $specificHandled = true;
        };

        $this->eventService->subscribeWildcard('user.*', $wildcardHandler);
        $this->eventService->subscribe('user.login', $specificHandler);

        $this->eventService->publish('user.login', ['user_id' => 123]);

        $this->assertTrue($wildcardHandled);
        $this->assertTrue($specificHandled);
    }

    /** @test */
    public function it_can_unsubscribe_from_events()
    {
        $eventName = 'test.event';
        $handlerCalled = false;
        $handler = function ($payload, $metadata) use (&$handlerCalled) {
            $handlerCalled = true;
        };

        $subscriptionId = $this->eventService->subscribe($eventName, $handler);
        $unsubscribed = $this->eventService->unsubscribe($eventName, $subscriptionId);

        $this->assertTrue($unsubscribed);

        // Publish event after unsubscribing
        $this->eventService->publish($eventName, ['data' => 'test']);

        // Handler should not be called
        $this->assertFalse($handlerCalled);
    }

    /** @test */
    public function it_can_add_event_transformers()
    {
        $transformerCalled = false;
        $transformer = function ($event) use (&$transformerCalled) {
            $transformerCalled = true;
            $event['metadata']['transformed'] = true;
            return $event;
        };

        $transformerId = $this->eventService->addEventTransformer($transformer);

        $this->assertIsString($transformerId);
        $this->assertStringStartsWith('trans_', $transformerId);

        // Publish an event to trigger transformer
        $this->eventService->publish('test.event', ['data' => 'test']);

        $this->assertTrue($transformerCalled);
    }

    /** @test */
    public function it_can_add_event_middleware()
    {
        $middlewareCalled = false;
        $middleware = function ($event) use (&$middlewareCalled) {
            $middlewareCalled = true;
            $event['metadata']['middleware_applied'] = true;
            return $event; // Continue processing
        };

        $middlewareId = $this->eventService->addEventMiddleware($middleware);

        $this->assertIsString($middlewareId);
        $this->assertStringStartsWith('mid_', $middlewareId);

        // Publish an event to trigger middleware
        $this->eventService->publish('test.event', ['data' => 'test']);

        $this->assertTrue($middlewareCalled);
    }

    /** @test */
    public function it_can_get_event_history()
    {
        // Publish some events
        $this->eventService->publish('event1', ['data' => 'test1']);
        $this->eventService->publish('event2', ['data' => 'test2']);
        $this->eventService->publish('event3', ['data' => 'test3']);

        $history = $this->eventService->getEventHistory([], 10);

        $this->assertIsArray($history);
        $this->assertCount(3, $history);
        $this->assertEquals('event3', $history[0]['name']); // Should be sorted by timestamp (newest first)
    }

    /** @test */
    public function it_can_filter_event_history()
    {
        // Publish events with different types
        $this->eventService->publish('user.event', ['data' => 'user'], ['type' => 'user']);
        $this->eventService->publish('system.event', ['data' => 'system'], ['type' => 'system']);
        $this->eventService->publish('user.event2', ['data' => 'user2'], ['type' => 'user']);

        // Filter by event type
        $userEvents = $this->eventService->getEventHistory(['event_type' => 'user'], 10);

        $this->assertCount(2, $userEvents);
        foreach ($userEvents as $event) {
            $this->assertEquals('user', $event['metadata']['type']);
        }

        // Filter by event name
        $systemEvents = $this->eventService->getEventHistory(['event_name' => 'system'], 10);

        $this->assertCount(1, $systemEvents);
        $this->assertEquals('system.event', $systemEvents[0]['name']);
    }

    /** @test */
    public function it_can_get_subscriptions()
    {
        // Add some subscriptions
        $this->eventService->subscribe('event1', function () {});
        $this->eventService->subscribe('event2', function () {});
        $this->eventService->subscribeWildcard('wildcard.*', function () {});

        $subscriptions = $this->eventService->getSubscriptions();
        $wildcardSubscriptions = $this->eventService->getWildcardSubscriptions();

        $this->assertIsArray($subscriptions);
        $this->assertIsArray($wildcardSubscriptions);
        $this->assertCount(2, $subscriptions);
        $this->assertCount(1, $wildcardSubscriptions);
    }

    /** @test */
    public function it_can_clear_event_history()
    {
        // Publish some events
        $this->eventService->publish('event1', ['data' => 'test1']);
        $this->eventService->publish('event2', ['data' => 'test2']);

        // Verify events exist
        $history = $this->eventService->getEventHistory();
        $this->assertGreaterThan(0, count($history));

        // Clear history
        $result = $this->eventService->clearEventHistory();

        $this->assertTrue($result);

        // Verify history is cleared
        $clearedHistory = $this->eventService->getEventHistory();
        $this->assertCount(0, $clearedHistory);
    }

    /** @test */
    public function it_can_generate_report()
    {
        // Add some subscriptions and events
        $this->eventService->subscribe('test.event', function () {});
        $this->eventService->subscribeWildcard('wildcard.*', function () {});
        $this->eventService->addEventTransformer(function ($event) { return $event; });
        $this->eventService->addEventMiddleware(function ($event) { return $event; });
        $this->eventService->publish('test.event', ['data' => 'test']);

        $report = $this->eventService->generateReport();

        $this->assertIsArray($report);
        $this->assertArrayHasKey('timestamp', $report);
        $this->assertArrayHasKey('processing_mode', $report);
        $this->assertArrayHasKey('subscriptions', $report);
        $this->assertArrayHasKey('event_history', $report);
        $this->assertArrayHasKey('middleware', $report);
        $this->assertArrayHasKey('performance_metrics', $report);

        $this->assertEquals(1, $report['subscriptions']['total_direct_subscriptions']);
        $this->assertEquals(1, $report['subscriptions']['total_wildcard_subscriptions']);
        $this->assertEquals(1, $report['middleware']['total_transformers']);
        $this->assertEquals(1, $report['middleware']['total_middleware']);
        $this->assertEquals(1, $report['event_history']['total_events']);
    }

    /** @test */
    public function it_handles_different_processing_modes()
    {
        // Test sync mode
        $syncService = new EventService(EventService::MODE_SYNC);
        $syncHandled = false;
        $syncService->subscribe('sync.test', function () use (&$syncHandled) {
            $syncHandled = true;
        });
        $syncService->publish('sync.test', ['data' => 'sync']);
        $this->assertTrue($syncHandled);

        // Test async mode
        $asyncService = new EventService(EventService::MODE_ASYNC);
        $asyncHandled = false;
        $asyncService->subscribe('async.test', function () use (&$asyncHandled) {
            $asyncHandled = true;
        });
        $asyncService->publish('async.test', ['data' => 'async']);
        // In async mode, handling would be queued, so we can't test immediate execution here
    }

    /** @test */
    public function event_api_endpoints_work()
    {
        // Test subscribe endpoint
        $response = $this->postJson('/api/events/subscribe', [
            'event_name' => 'api.test.event',
            'handler' => 'test_handler',
            'priority' => 3
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'message', 'data']);

        // Test publish endpoint
        $response = $this->postJson('/api/events/publish', [
            'event_name' => 'api.test.event',
            'payload' => ['data' => 'test'],
            'metadata' => ['type' => 'user', 'priority' => 2]
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'message', 'data']);

        // Test history endpoint
        $response = $this->getJson('/api/events/history');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);

        // Test subscriptions endpoint
        $response = $this->getJson('/api/events/subscriptions');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);

        // Test report endpoint
        $response = $this->getJson('/api/events/report');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'message', 'data']);

        // Test dashboard endpoint
        $response = $this->getJson('/api/events/dashboard');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);

        // Test statistics endpoint
        $response = $this->getJson('/api/events/statistics');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
    }

    /** @test */
    public function it_can_subscribe_via_api()
    {
        $response = $this->postJson('/api/events/subscribe', [
            'event_name' => 'api.subscribe.test',
            'handler' => 'test_handler',
            'priority' => 3
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
        $response->assertJsonStructure(['success', 'message', 'data.subscription_id', 'data.event_name', 'data.priority']);
    }

    /** @test */
    public function it_can_subscribe_wildcard_via_api()
    {
        $response = $this->postJson('/api/events/subscribe-wildcard', [
            'pattern' => 'api.wildcard.*',
            'handler' => 'test_handler'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
        $response->assertJsonStructure(['success', 'message', 'data.subscription_id', 'data.pattern']);
    }

    /** @test */
    public function it_can_publish_via_api()
    {
        $response = $this->postJson('/api/events/publish', [
            'event_name' => 'api.publish.test',
            'payload' => [
                'user_id' => 123,
                'action' => 'test_action'
            ],
            'metadata' => [
                'type' => 'user',
                'priority' => 3
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
        $response->assertJsonStructure(['success', 'message', 'data.event_id', 'data.event_name', 'data.published_at']);
    }

    /** @test */
    public function it_can_add_transformer_via_api()
    {
        $response = $this->postJson('/api/events/add-transformer', [
            'transformer' => 'test_transformer'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
        $response->assertJsonStructure(['success', 'message', 'data.transformer_id']);
    }

    /** @test */
    public function it_can_add_middleware_via_api()
    {
        $response = $this->postJson('/api/events/add-middleware', [
            'middleware' => 'test_middleware'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
        $response->assertJsonStructure(['success', 'message', 'data.middleware_id']);
    }

    /** @test */
    public function it_can_get_filtered_history_via_api()
    {
        // Publish some events first
        $this->postJson('/api/events/publish', [
            'event_name' => 'user.registered',
            'payload' => ['email' => 'test@example.com'],
            'metadata' => ['type' => 'user']
        ]);

        $this->postJson('/api/events/publish', [
            'event_name' => 'system.startup',
            'payload' => ['service' => 'api'],
            'metadata' => ['type' => 'system']
        ]);

        // Filter by event type
        $response = $this->getJson('/api/events/history', [
            'event_type' => 'user'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data.history', 'data.total_count', 'data.filters', 'data.limit']);
        
        $data = $response->json('data');
        $this->assertEquals('user', $data['filters']['event_type']);
    }

    /** @test */
    public function it_can_clear_history_via_api()
    {
        // Publish an event first
        $this->postJson('/api/events/publish', [
            'event_name' => 'clear.test',
            'payload' => ['data' => 'test']
        ]);

        $response = $this->deleteJson('/api/events/clear-history');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
        $response->assertJsonStructure(['success', 'message']);
    }

    /** @test */
    public function it_can_get_dashboard_via_api()
    {
        $response = $this->getJson('/api/events/dashboard');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success', 
            'data.overview', 
            'data.subscriptions', 
            'data.activity', 
            'data.performance', 
            'data.configuration'
        ]);

        $data = $response->json('data');
        $this->assertArrayHasKey('processing_mode', $data['overview']);
        $this->assertArrayHasKey('total_subscriptions', $data['overview']);
        $this->assertArrayHasKey('total_events', $data['overview']);
        $this->assertArrayHasKey('system_health', $data['overview']);
    }

    /** @test */
    public function it_can_demonstrate_via_api()
    {
        $response = $this->postJson('/api/events/demonstrate');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
        $response->assertJsonStructure([
            'success', 
            'message', 
            'data.subscriptions', 
            'data.transformer_id', 
            'data.middleware_id', 
            'data.published_events', 
            'data.demonstration_completed_at'
        ]);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        // Test subscribe without event name
        $response = $this->postJson('/api/events/subscribe', [
            'handler' => 'test'
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['event_name']);

        // Test publish without event name
        $response = $this->postJson('/api/events/publish', [
            'payload' => ['data' => 'test']
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['event_name']);

        // Test wildcard subscribe without pattern
        $response = $this->postJson('/api/events/subscribe-wildcard', [
            'handler' => 'test'
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['pattern']);
    }

    /** @test */
    public function it_handles_event_priorities()
    {
        $executionOrder = [];

        // Add handlers with different priorities
        $this->eventService->subscribe('priority.test', function () use (&$executionOrder) {
            $executionOrder[] = 'low';
        }, EventService::PRIORITY_LOW);

        $this->eventService->subscribe('priority.test', function () use (&$executionOrder) {
            $executionOrder[] = 'high';
        }, EventService::PRIORITY_HIGH);

        $this->eventService->subscribe('priority.test', function () use (&$executionOrder) {
            $executionOrder[] = 'normal';
        }, EventService::PRIORITY_NORMAL);

        $this->eventService->publish('priority.test', ['data' => 'test']);

        // Should execute in order: high, normal, low
        $this->assertEquals(['high', 'normal', 'low'], $executionOrder);
    }

    /** @test */
    public function it_can_get_statistics_via_api()
    {
        // Publish some events first
        $this->postJson('/api/events/publish', [
            'event_name' => 'stats.test1',
            'payload' => ['data' => 'test1'],
            'metadata' => ['type' => 'user', 'priority' => 3]
        ]);

        $this->postJson('/api/events/publish', [
            'event_name' => 'stats.test2',
            'payload' => ['data' => 'test2'],
            'metadata' => ['type' => 'system', 'priority' => 2]
        ]);

        $response = $this->getJson('/api/events/statistics');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success', 
            'data.overview', 
            'data.event_types', 
            'data.priorities', 
            'data.hourly_distribution', 
            'data.most_active_hour', 
            'data.total_events_processed'
        ]);

        $data = $response->json('data');
        $this->assertArrayHasKey('user', $data['event_types']);
        $this->assertArrayHasKey('system', $data['event_types']);
        $this->assertArrayHasKey(3, $data['priorities']);
        $this->assertArrayHasKey(2, $data['priorities']);
    }

    protected function tearDown(): void
    {
        // Clean up any remaining test data
        $this->eventService->clearEventHistory();
        parent::tearDown();
    }
}
