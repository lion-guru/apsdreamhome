<?php

namespace Tests\Services\Events;

use PHPUnit\Framework\TestCase;
use App\Services\Events\EventDispatcherService;
use App\Core\Database;
use Psr\Log\LoggerInterface;

class EventDispatcherServiceTest extends TestCase
{
    private EventDispatcherService $eventDispatcher;
    private Database $db;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->db = $this->createMock(Database::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->eventDispatcher = new EventDispatcherService($this->db, $this->logger);
    }

    public function testSubscribe(): void
    {
        $eventName = 'test.event';
        $listener = function ($event) {
            return $event;
        };

        $this->db->expects($this->once())
            ->method('execute')
            ->willReturn(1);

        $result = $this->eventDispatcher->subscribe($eventName, $listener);

        $this->assertTrue($result['success']);
        $this->assertEquals('Listener subscribed successfully', $result['message']);
    }

    public function testPublish(): void
    {
        $eventName = 'test.event';
        $data = ['key' => 'value'];

        $this->db->expects($this->once())
            ->method('execute')
            ->willReturn(1);

        $result = $this->eventDispatcher->publish($eventName, $data);

        $this->assertTrue($result['success']);
        $this->assertEquals('Event published successfully', $result['message']);
        $this->assertArrayHasKey('event_id', $result);
    }

    public function testPublishWithWildcard(): void
    {
        $eventName = 'test.event';
        $data = ['key' => 'value'];

        // Subscribe to wildcard
        $this->eventDispatcher->subscribe('test.*', function ($event) {
            return $event;
        });

        $this->db->expects($this->once())
            ->method('execute')
            ->willReturn(1);

        $result = $this->eventDispatcher->publish($eventName, $data);

        $this->assertTrue($result['success']);
        $this->assertEquals('Event published successfully', $result['message']);
    }

    public function testProcessQueue(): void
    {
        $events = [
            ['id' => 1, 'event_name' => 'test.event', 'event_data' => '{"key":"value"}'],
            ['id' => 2, 'event_name' => 'test.event2', 'event_data' => '{"key2":"value2"}']
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($events);

        $this->db->expects($this->exactly(2))
            ->method('execute')
            ->willReturn(1);

        $result = $this->eventDispatcher->processQueue(10);

        $this->assertTrue($result['success']);
        $this->assertEquals('Processed 2 events', $result['message']);
        $this->assertEquals(2, $result['processed']);
        $this->assertEquals(2, $result['completed']);
    }

    public function testGetEvent(): void
    {
        $eventId = 1;
        $event = [
            'id' => $eventId,
            'event_name' => 'test.event',
            'event_data' => '{"key":"value"}',
            'status' => 'completed'
        ];

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn($event);

        $result = $this->eventDispatcher->getEvent($eventId);

        $this->assertEquals($event, $result);
        $this->assertArrayHasKey('data', $result);
    }

    public function testGetEvents(): void
    {
        $filters = ['status' => 'completed'];
        $events = [
            ['id' => 1, 'event_name' => 'test.event', 'status' => 'completed'],
            ['id' => 2, 'event_name' => 'test.event2', 'status' => 'completed']
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($events);

        $result = $this->eventDispatcher->getEvents($filters);

        $this->assertCount(2, $result);
        $this->assertEquals('test.event', $result[0]['event_name']);
    }

    public function testGetEventStats(): void
    {
        $stats = [
            'total_events' => 100,
            'completed_events' => 90,
            'failed_events' => 5,
            'pending_events' => 5
        ];

        $this->db->expects($this->exactly(4))
            ->method('fetchOne')
            ->willReturnOnConsecutiveCalls(
                $stats['total_events'],
                $stats['completed_events'],
                $stats['failed_events'],
                $stats['pending_events']
            );

        $result = $this->eventDispatcher->getEventStats();

        $this->assertEquals($stats['total_events'], $result['total_events']);
        $this->assertEquals($stats['completed_events'], $result['completed_events']);
        $this->assertEquals($stats['failed_events'], $result['failed_events']);
        $this->assertEquals($stats['pending_events'], $result['pending_events']);
    }

    public function testClearOldLogs(): void
    {
        $days = 30;

        $this->db->expects($this->once())
            ->method('execute')
            ->willReturn(10);

        $result = $this->eventDispatcher->clearOldLogs($days);

        $this->assertTrue($result['success']);
        $this->assertEquals("Cleared logs older than {$days} days", $result['message']);
        $this->assertEquals(10, $result['deleted_rows']);
    }

    public function testUnsubscribe(): void
    {
        $eventName = 'test.event';
        $listenerId = 1;

        $this->db->expects($this->once())
            ->method('execute')
            ->willReturn(1);

        $result = $this->eventDispatcher->unsubscribe($eventName, $listenerId);

        $this->assertTrue($result['success']);
        $this->assertEquals('Listener unsubscribed successfully', $result['message']);
    }

    public function testGetSubscribers(): void
    {
        $eventName = 'test.event';
        $subscribers = [
            ['id' => 1, 'event_name' => $eventName, 'listener_class' => 'TestListener'],
            ['id' => 2, 'event_name' => $eventName, 'listener_class' => 'TestListener2']
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($subscribers);

        $result = $this->eventDispatcher->getSubscribers($eventName);

        $this->assertCount(2, $result);
        $this->assertEquals('TestListener', $result[0]['listener_class']);
    }

    public function testPublishWithPriority(): void
    {
        $eventName = 'test.event';
        $data = ['key' => 'value'];
        $priority = EventDispatcherService::PRIORITY_HIGH;

        $this->db->expects($this->once())
            ->method('execute')
            ->willReturn(1);

        $result = $this->eventDispatcher->publish($eventName, $data, $priority);

        $this->assertTrue($result['success']);
        $this->assertEquals('Event published successfully', $result['message']);
        $this->assertArrayHasKey('event_id', $result);
    }

    public function testPublishAsync(): void
    {
        $eventName = 'test.event';
        $data = ['key' => 'value'];

        $this->db->expects($this->once())
            ->method('execute')
            ->willReturn(1);

        $result = $this->eventDispatcher->publish($eventName, $data, EventDispatcherService::PRIORITY_NORMAL, true);

        $this->assertTrue($result['success']);
        $this->assertEquals('Event queued successfully', $result['message']);
    }
}
