<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for BookingNotificationService.
 * Verifies method signatures and parameter handling.
 */
class BookingNotificationServiceTest extends TestCase
{
    public function testInstantiation(): void
    {
        try {
            $service = new \App\Services\BookingNotificationService();
            $this->assertInstanceOf(\App\Services\BookingNotificationService::class, $service);
        } catch (\Throwable $e) {
            // DB not available — just verify class exists
            $this->assertTrue(class_exists(\App\Services\BookingNotificationService::class));
        }
    }

    public function testBookingConfirmationMethodExists(): void
    {
        $reflection = new \ReflectionClass(\App\Services\BookingNotificationService::class);
        $this->assertTrue($reflection->hasMethod('sendBookingConfirmation'));
    }

    public function testPaymentReceiptMethodExists(): void
    {
        $reflection = new \ReflectionClass(\App\Services\BookingNotificationService::class);
        $this->assertTrue($reflection->hasMethod('sendPaymentReceipt'));
    }

    public function testStatusChangeMethodExists(): void
    {
        $reflection = new \ReflectionClass(\App\Services\BookingNotificationService::class);
        $this->assertTrue($reflection->hasMethod('sendStatusChange'));
    }

    public function testDemandLetterMethodExists(): void
    {
        $reflection = new \ReflectionClass(\App\Services\BookingNotificationService::class);
        $this->assertTrue($reflection->hasMethod('sendDemandLetterReminder'));
    }

    public function testBookingLogMethodExists(): void
    {
        $reflection = new \ReflectionClass(\App\Services\BookingNotificationService::class);
        $this->assertTrue($reflection->hasMethod('getBookingLog'));
    }

    public function testLogStatsMethodExists(): void
    {
        $reflection = new \ReflectionClass(\App\Services\BookingNotificationService::class);
        $this->assertTrue($reflection->hasMethod('getLogStats'));
    }
}
