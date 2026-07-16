<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\TrackAService;
use App\Services\TrackBService;
use App\Services\TrackCService;

class TrackServicesTest extends TestCase
{
    private TrackAService $trackA;
    private TrackBService $trackB;
    private TrackCService $trackC;

    protected function setUp(): void
    {
        // We need to mock the database for these tests
        // For now, test that the classes can be instantiated
        $this->trackA = new TrackAService();
        $this->trackB = new TrackBService();
        $this->trackC = new TrackCService();
    }

    public function testTrackAServiceInstantiation(): void
    {
        $this->assertInstanceOf(TrackAService::class, $this->trackA);
    }

    public function testTrackBServiceInstantiation(): void
    {
        $this->assertInstanceOf(TrackBService::class, $this->trackB);
    }

    public function testTrackCServiceInstantiation(): void
    {
        $this->assertInstanceOf(TrackCService::class, $this->trackC);
    }

    public function testTrackAHasCalculateMethod(): void
    {
        $this->assertTrue(method_exists($this->trackA, 'calculateTrackA'));
    }

    public function testTrackBHasCalculateMethod(): void
    {
        $this->assertTrue(method_exists($this->trackB, 'calculateTrackB'));
    }

    public function testTrackCHasCalculateMethod(): void
    {
        $this->assertTrue(method_exists($this->trackC, 'calculateTrackC'));
    }

    public function testTrackCHasReleaseEscrowMethod(): void
    {
        $this->assertTrue(method_exists($this->trackC, 'releaseEscrow'));
    }
}