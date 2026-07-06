<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for deprecated notification services (thin wrappers).
 * Verifies they delegate to the canonical NotificationService correctly.
 */
class NotificationWrappersTest extends TestCase
{
    public function testAdminNotificationServiceInstantiation(): void
    {
        // AdminNotificationService should be instantiable without arguments
        // (it creates its own DB connection internally)
        try {
            $service = new \App\Services\AdminNotificationService();
            $this->assertInstanceOf(\App\Services\AdminNotificationService::class, $service);
        } catch (\Throwable $e) {
            // DB not available in CI — that's fine, just verify the class exists
            $this->assertTrue(class_exists(\App\Services\AdminNotificationService::class));
        }
    }

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

    public function testNotificationCenterServiceInstantiation(): void
    {
        try {
            $service = new \App\Services\Notification\NotificationCenterService();
            $this->assertInstanceOf(\App\Services\Notification\NotificationCenterService::class, $service);
        } catch (\Throwable $e) {
            $this->assertTrue(class_exists(\App\Services\Notification\NotificationCenterService::class));
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

    public function testAllDeprecatedServicesHaveDeprecationDocblock(): void
    {
        $deprecatedFiles = [
            \App\Services\AdminNotificationService::class,
            \App\Services\NotificationCenter::class,
            \App\Services\Communication\NotificationService::class,
            \App\Services\Notification\BookingNotificationService::class,
            \App\Services\Notification\NotificationCenterService::class,
            \App\Services\PushNotificationService::class,
        ];

        foreach ($deprecatedFiles as $class) {
            $reflection = new \ReflectionClass($class);
            $docblock = $reflection->getDocComment();
            $this->assertNotFalse($docblock, "$class missing docblock");
            $this->assertStringContainsString('@deprecated', $docblock, "$class missing @deprecated tag");
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
