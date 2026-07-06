<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\NotificationService;

/**
 * Unit tests for NotificationService (canonical notification service).
 * These tests verify method signatures, parameter handling, and edge cases.
 * DB-dependent tests require a running MySQL instance.
 */
class NotificationServiceTest extends TestCase
{
    private $mockPdo;
    private $mockStmt;
    private $service;

    protected function setUp(): void
    {
        // Create mock PDOStatement
        $this->mockStmt = $this->createMock(\PDOStatement::class);
        $this->mockStmt->method('fetch')->willReturn(['cnt' => 0]);
        $this->mockStmt->method('fetchAll')->willReturn([]);
        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('rowCount')->willReturn(0);

        // Create mock PDO
        $this->mockPdo = $this->createMock(\PDO::class);
        $this->mockPdo->method('prepare')->willReturn($this->mockStmt);
        $this->mockPdo->method('query')->willReturn($this->mockStmt);
        $this->mockPdo->method('lastInsertId')->willReturn('1');

        // Pass PDO directly (NotificationService accepts PDO or Database object)
        $this->service = new NotificationService($this->mockPdo);
    }

    public function testConstructorRequiresDb(): void
    {
        $this->expectException(\TypeError::class);
        new NotificationService();
    }

    public function testSendReturnsArray(): void
    {
        $result = $this->service->send(1, 'email', 'Test', 'Body');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('ok', $result);
    }

    public function testSendWithAllChannels(): void
    {
        foreach (['email', 'sms', 'push', 'whatsapp'] as $channel) {
            $result = $this->service->send(1, $channel, 'Test', 'Body');
            $this->assertIsArray($result);
        }
    }

    public function testSendNotificationAlias(): void
    {
        $result = $this->service->sendNotification(1, 'email', 'Test', 'Body');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('ok', $result);
    }

    public function testGetUnreadCountReturnsInt(): void
    {
        $count = $this->service->getUnreadCount(1);
        $this->assertIsInt($count);
    }

    public function testMarkReadReturnsBool(): void
    {
        $result = $this->service->markRead(1);
        $this->assertIsBool($result);
    }

    public function testMarkAsReadAlias(): void
    {
        $result = $this->service->markAsRead(1);
        $this->assertIsBool($result);
    }

    public function testMarkAllReadReturnsBool(): void
    {
        $result = $this->service->markAllRead(1);
        $this->assertIsBool($result);
    }

    public function testMarkAllAsReadAlias(): void
    {
        $result = $this->service->markAllAsRead(1);
        $this->assertIsBool($result);
    }

    public function testNotifyReturnsBool(): void
    {
        $result = $this->service->notify('lead', 'New lead', 1, '/admin/leads/1', 'New Lead');
        $this->assertIsBool($result);
    }

    public function testGetUnreadReturnsArray(): void
    {
        $result = $this->service->getUnread(1, 10);
        $this->assertIsArray($result);
    }

    public function testGetRecentReturnsArray(): void
    {
        $result = $this->service->getRecent(1, 10);
        $this->assertIsArray($result);
    }

    public function testGetCustomerNotificationsReturnsArray(): void
    {
        $result = $this->service->getCustomerNotifications(1, 10);
        $this->assertIsArray($result);
    }

    public function testPublishReturnsInt(): void
    {
        $result = $this->service->publish('global', 'test_event', 1, ['message' => 'hello']);
        $this->assertIsInt($result);
    }

    public function testFetchPendingReturnsArray(): void
    {
        $result = $this->service->fetchPending(1, 'global', 20);
        $this->assertIsArray($result);
    }

    public function testMarkDeliveredReturnsInt(): void
    {
        $result = $this->service->markDelivered([1, 2, 3]);
        $this->assertIsInt($result);
    }

    public function testMarkDeliveredEmptyReturnsZero(): void
    {
        $result = $this->service->markDelivered([]);
        $this->assertEquals(0, $result);
    }

    public function testCleanupReturnsInt(): void
    {
        $result = $this->service->cleanup(30);
        $this->assertIsInt($result);
    }

    public function testDomainTriggerHelpers(): void
    {
        $this->assertIsBool($this->service->newLead(1, 'Test Lead'));
        $this->assertIsBool($this->service->newProperty(1, 'Test Property'));
        $this->assertIsBool($this->service->newRegistration(1, 'Test User'));
        $this->assertIsBool($this->service->newBooking(1, 'Test Buyer'));
        $this->assertIsBool($this->service->paymentReceived(1, 1000.00));
    }

    public function testBookingLifecycleMethodsReturnVoid(): void
    {
        $this->assertNull($this->service->sendBookingConfirmed(1));
        $this->assertNull($this->service->sendBookingConfirmedEmail(1));
        $this->assertNull($this->service->sendAgreementGenerated(1, 'sale_deed'));
        $this->assertNull($this->service->sendPaymentReceived(1, 5000.00));
        $this->assertNull($this->service->sendRegistryUpdate(1, 'registered'));
        $this->assertNull($this->service->sendPossessionScheduled(1, '2026-01-01'));
        $this->assertNull($this->service->sendPossessionCompleted(1));
    }
}
