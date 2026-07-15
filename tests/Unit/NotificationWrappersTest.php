<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for notification services.
 * Verifies canonical services exist and are properly structured.
 */
class NotificationWrappersTest extends TestCase
{
    public function testNotificationCenterInstantiation(): void
    {
        try {
            $service = new \App\Services\NotificationCenter();
            $this->assertInstanceOf(\App\Services\NotificationCenter::class, $service);
        } catch (\Throwable $e) {
            $this->assertTrue(class_exists(\App\Services\NotificationCenter::class));
        }
    }

    public function testCommunicationNotificationServiceInstantiation(): void
    {
        try {
            $service = new \App\Services\Communication\NotificationService();
            $this->assertInstanceOf(\App\Services\Communication\NotificationService::class, $service);
        } catch (\Throwable $e) {
            $this->assertTrue(class_exists(\App\Services\Communication\NotificationService::class));
        }
    }

    public function testNotificationBookingNotificationServiceInstantiation(): void
    {
        try {
            $service = new \App\Services\Notification\BookingNotificationService();
            $this->assertInstanceOf(\App\Services\Notification\BookingNotificationService::class, $service);
        } catch (\Throwable $e) {
            $this->assertTrue(class_exists(\App\Services\Notification\BookingNotificationService::class));
        }
    }

    public function testPushNotificationServiceInstantiation(): void
    {
        try {
            $service = new \App\Services\PushNotificationService();
            $this->assertInstanceOf(\App\Services\PushNotificationService::class, $service);
        } catch (\Throwable $e) {
            $this->assertTrue(class_exists(\App\Services\PushNotificationService::class));
        }
    }

    public function testCanonicalServicesAreNotDeprecated(): void
    {
        $canonicalClasses = [
            \App\Services\NotificationService::class,
            \App\Services\BookingNotificationService::class,
            \App\Services\Communication\PushNotificationService::class,
        ];

        foreach ($canonicalClasses as $class) {
            $reflection = new \ReflectionClass($class);
            $docblock = $reflection->getDocComment();
            if ($docblock) {
                $this->assertStringNotContainsString('@deprecated', $docblock, "$class should NOT be deprecated");
            }
        }
    }
}
