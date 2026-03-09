<?php

namespace Tests\Feature\Land;

use App\Services\Land\PlottingService;
use PHPUnit\Framework\TestCase;

/**
 * Plotting Service Test - APS Dream Home
 * Custom MVC testing without Laravel dependencies
 */
class PlottingServiceTest extends TestCase
{
    private $plottingService;
    private $testAcquisitionId;
    private $testPlotId;
    private $testBookingId;
    
    protected function setUp(): void
    {
        $this->plottingService = new PlottingService();
    }
    
    /** @test */
    public function it_can_be_initialized()
    {
        $this->assertInstanceOf(PlottingService::class, $this->plottingService);
    }
    
    /** @test */
    public function it_can_add_land_acquisition()
    {
        $data = [
            'farmer_id' => 1,
            'land_area' => 5000.50,
            'land_area_unit' => 'sqft',
            'location' => 'Test Location',
            'village' => 'Test Village',
            'tehsil' => 'Test Tehsil',
            'district' => 'Test District',
            'state' => 'Test State',
            'acquisition_date' => '2023-01-15',
            'acquisition_cost' => 2500000.00,
            'payment_status' => 'completed',
            'land_type' => 'residential',
            'soil_type' => 'Clay',
            'water_source' => 'Well',
            'electricity_available' => true,
            'road_access' => true,
            'documents' => ['doc1.pdf', 'doc2.jpg'],
            'remarks' => 'Test acquisition',
            'status' => 'active',
            'created_by' => 1
        ];
        
        $result = $this->plottingService->addLandAcquisition($data);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('acquisition_id', $result);
        $this->assertArrayHasKey('acquisition_number', $result);
        $this->assertStringContainsString('LAQ', $result['acquisition_number']);
        
        // Store for cleanup
        $this->testAcquisitionId = $result['acquisition_id'];
    }
    
    /** @test */
    public function it_validates_required_land_acquisition_fields()
    {
        $data = [
            'land_area' => '', // Required field missing
            'location' => '', // Required field missing
            'created_by' => 1
        ];
        
        $result = $this->plottingService->addLandAcquisition($data);
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Failed to add land acquisition', $result['message']);
    }
    
    /** @test */
    public function it_can_get_land_acquisitions()
    {
        // First create a test acquisition
        $this->createTestLandAcquisition();
        
        $result = $this->plottingService->getLandAcquisitions();
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertIsArray($result['data']);
    }
    
    /** @test */
    public function it_can_filter_land_acquisitions()
    {
        $result = $this->plottingService->getLandAcquisitions(['status' => 'active']);
        
        $this->assertTrue($result['success']);
        
        // Verify all results have the specified status (if any results exist)
        if (!empty($result['data'])) {
            foreach ($result['data'] as $acquisition) {
                $this->assertEquals('active', $acquisition['status']);
            }
        }
    }
    
    /** @test */
    public function it_can_add_plot()
    {
        // First create a test land acquisition
        $this->createTestLandAcquisition();
        
        $data = [
            'land_acquisition_id' => $this->testAcquisitionId,
            'plot_area' => 500.25,
            'plot_area_unit' => 'sqft',
            'plot_type' => 'residential',
            'dimensions_length' => 25.5,
            'dimensions_width' => 20.0,
            'corner_plot' => false,
            'park_facing' => true,
            'road_facing' => true,
            'current_price' => 500000.00,
            'base_price' => 450000.00,
            'plc_amount' => 50000.00,
            'other_charges' => 0,
            'total_price' => 500000.00,
            'remarks' => 'Test plot',
            'created_by' => 1
        ];
        
        $result = $this->plottingService->addPlot($data);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('plot_id', $result);
        $this->assertArrayHasKey('plot_number', $result);
        $this->assertStringContainsString('PLOT', $result['plot_number']);
        
        // Store for cleanup
        $this->testPlotId = $result['plot_id'];
    }
    
    /** @test */
    public function it_validates_required_plot_fields()
    {
        $data = [
            'land_acquisition_id' => 0, // Invalid
            'plot_area' => '', // Required field missing
            'current_price' => 0, // Required field missing
            'created_by' => 1
        ];
        
        $result = $this->plottingService->addPlot($data);
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Failed to add plot', $result['message']);
    }
    
    /** @test */
    public function it_can_get_plots()
    {
        // First create test data
        $this->createTestLandAcquisition();
        $this->createTestPlot();
        
        $result = $this->plottingService->getPlots();
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertIsArray($result['data']);
    }
    
    /** @test */
    public function it_can_filter_plots()
    {
        $result = $this->plottingService->getPlots(['plot_status' => 'available']);
        
        $this->assertTrue($result['success']);
        
        // Verify all results have the specified status (if any results exist)
        if (!empty($result['data'])) {
            foreach ($result['data'] as $plot) {
                $this->assertEquals('available', $plot['plot_status']);
            }
        }
    }
    
    /** @test */
    public function it_can_book_plot()
    {
        // First create test data
        $this->createTestLandAcquisition();
        $this->createTestPlot();
        
        $data = [
            'plot_id' => $this->testPlotId,
            'customer_id' => 1,
            'associate_id' => 1,
            'booking_type' => 'direct',
            'booking_amount' => 50000.00,
            'total_amount' => 500000.00,
            'payment_plan' => 'lump_sum',
            'payment_method' => 'cash',
            'transaction_id' => 'TXN123456',
            'booking_date' => '2023-01-20',
            'status' => 'confirmed',
            'created_by' => 1
        ];
        
        $result = $this->plottingService->bookPlot($data);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('booking_id', $result);
        $this->assertArrayHasKey('booking_number', $result);
        $this->assertStringContainsString('BK', $result['booking_number']);
        
        // Store for cleanup
        $this->testBookingId = $result['booking_id'];
    }
    
    /** @test */
    public function it_validates_plot_booking()
    {
        $data = [
            'plot_id' => 0, // Invalid plot ID
            'customer_id' => 0, // Invalid customer ID
            'booking_amount' => 0, // Required field missing
            'total_amount' => 0, // Required field missing
            'booking_date' => '',
            'created_by' => 1
        ];
        
        $result = $this->plottingService->bookPlot($data);
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Failed to book plot', $result['message']);
    }
    
    /** @test */
    public function it_cannot_book_unavailable_plot()
    {
        // First create a test plot and book it
        $this->createTestLandAcquisition();
        $this->createTestPlot();
        
        // Book the plot first
        $bookingData = [
            'plot_id' => $this->testPlotId,
            'customer_id' => 1,
            'booking_amount' => 50000.00,
            'total_amount' => 500000.00,
            'booking_date' => '2023-01-20',
            'created_by' => 1
        ];
        
        $this->plottingService->bookPlot($bookingData);
        
        // Try to book the same plot again
        $secondBookingData = [
            'plot_id' => $this->testPlotId,
            'customer_id' => 2,
            'booking_amount' => 50000.00,
            'total_amount' => 500000.00,
            'booking_date' => '2023-01-21',
            'created_by' => 1
        ];
        
        $result = $this->plottingService->bookPlot($secondBookingData);
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('not available', $result['message']);
    }
    
    /** @test */
    public function it_can_get_plot_bookings()
    {
        // First create test data
        $this->createTestLandAcquisition();
        $this->createTestPlot();
        $this->createTestBooking();
        
        $result = $this->plottingService->getPlotBookings();
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertIsArray($result['data']);
    }
    
    /** @test */
    public function it_can_filter_plot_bookings()
    {
        $result = $this->plottingService->getPlotBookings(['status' => 'confirmed']);
        
        $this->assertTrue($result['success']);
        
        // Verify all results have the specified status (if any results exist)
        if (!empty($result['data'])) {
            foreach ($result['data'] as $booking) {
                $this->assertEquals('confirmed', $booking['status']);
            }
        }
    }
    
    /** @test */
    public function it_can_add_booking_payment()
    {
        // First create test data
        $this->createTestLandAcquisition();
        $this->createTestPlot();
        $this->createTestBooking();
        
        $data = [
            'booking_id' => $this->testBookingId,
            'amount' => 100000.00,
            'payment_date' => '2023-01-25',
            'payment_method' => 'bank_transfer',
            'transaction_id' => 'PAY123456',
            'installment_number' => 1,
            'payment_status' => 'completed',
            'receipt_number' => 'REC789',
            'bank_reference' => 'BANK456',
            'remarks' => 'First installment'
        ];
        
        $result = $this->plottingService->addBookingPayment($data);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('payment_id', $result);
    }
    
    /** @test */
    public function it_validates_booking_payment()
    {
        $data = [
            'booking_id' => 0, // Invalid booking ID
            'amount' => 0, // Required field missing
            'payment_date' => '', // Required field missing
            'payment_method' => '' // Required field missing
        ];
        
        $result = $this->plottingService->addBookingPayment($data);
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Failed to add payment', $result['message']);
    }
    
    /** @test */
    public function it_can_get_plotting_statistics()
    {
        $result = $this->plottingService->getPlottingStats();
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        
        $stats = $result['data'];
        
        // Check required stats keys
        $requiredKeys = [
            'land_acquired',
            'plots',
            'bookings',
            'monthly_sales'
        ];
        
        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $stats);
        }
        
        // Check land acquired stats
        $this->assertArrayHasKey('total_acquisitions', $stats['land_acquired']);
        $this->assertArrayHasKey('total_area', $stats['land_acquired']);
        $this->assertArrayHasKey('total_cost', $stats['land_acquired']);
        
        // Check plots stats
        $this->assertArrayHasKey('total_plots', $stats['plots']);
        $this->assertArrayHasKey('available_plots', $stats['plots']);
        $this->assertArrayHasKey('sold_plots', $stats['plots']);
        
        // Check bookings stats
        $this->assertArrayHasKey('total_bookings', $stats['bookings']);
        $this->assertArrayHasKey('confirmed_bookings', $stats['bookings']);
        $this->assertArrayHasKey('completed_bookings', $stats['bookings']);
    }
    
    /** @test */
    public function it_handles_pagination_correctly()
    {
        // Test land acquisitions pagination
        $page1 = $this->plottingService->getLandAcquisitions([], 5, 0);
        $page2 = $this->plottingService->getLandAcquisitions([], 5, 5);
        
        $this->assertTrue($page1['success']);
        $this->assertTrue($page2['success']);
        
        $this->assertLessThanOrEqual(5, count($page1['data']));
        $this->assertLessThanOrEqual(5, count($page2['data']));
        
        // Test plots pagination
        $plotsPage1 = $this->plottingService->getPlots([], 5, 0);
        $plotsPage2 = $this->plottingService->getPlots([], 5, 5);
        
        $this->assertTrue($plotsPage1['success']);
        $this->assertTrue($plotsPage2['success']);
        
        $this->assertLessThanOrEqual(5, count($plotsPage1['data']));
        $this->assertLessThanOrEqual(5, count($plotsPage2['data']));
        
        // Test bookings pagination
        $bookingsPage1 = $this->plottingService->getPlotBookings([], 5, 0);
        $bookingsPage2 = $this->plottingService->getPlotBookings([], 5, 5);
        
        $this->assertTrue($bookingsPage1['success']);
        $this->assertTrue($bookingsPage2['success']);
        
        $this->assertLessThanOrEqual(5, count($bookingsPage1['data']));
        $this->assertLessThanOrEqual(5, count($bookingsPage2['data']));
    }
    
    /** @test */
    public function it_generates_unique_acquisition_numbers()
    {
        $this->createTestLandAcquisition();
        
        $data = [
            'farmer_id' => 2,
            'land_area' => 3000.00,
            'location' => 'Another Location',
            'acquisition_date' => '2023-01-16',
            'created_by' => 1
        ];
        
        $result = $this->plottingService->addLandAcquisition($data);
        
        if ($result['success']) {
            // Clean up
            $this->database->query("DELETE FROM land_acquisitions WHERE id = ?", [$result['acquisition_id']]);
            
            // Verify the acquisition number format
            $this->assertStringContainsString('LAQ', $result['acquisition_number']);
            $this->assertStringContainsString(date('Y'), $result['acquisition_number']);
        }
    }
    
    /** @test */
    public function it_generates_unique_plot_numbers()
    {
        $this->createTestLandAcquisition();
        
        // Add first plot
        $plotData1 = [
            'land_acquisition_id' => $this->testAcquisitionId,
            'plot_area' => 400.00,
            'current_price' => 400000.00,
            'created_by' => 1
        ];
        
        $result1 = $this->plottingService->addPlot($plotData1);
        
        // Add second plot
        $plotData2 = [
            'land_acquisition_id' => $this->testAcquisitionId,
            'plot_area' => 450.00,
            'current_price' => 450000.00,
            'created_by' => 1
        ];
        
        $result2 = $this->plottingService->addPlot($plotData2);
        
        if ($result1['success'] && $result2['success']) {
            // Clean up
            $this->database->query("DELETE FROM plots WHERE id IN (?, ?)", [$result1['plot_id'], $result2['plot_id']]);
            
            // Verify plot numbers are different
            $this->assertNotEquals($result1['plot_number'], $result2['plot_number']);
            
            // Verify plot number format
            $this->assertStringContainsString('PLOT', $result1['plot_number']);
            $this->assertStringContainsString('-', $result1['plot_number']);
        }
    }
    
    /**
     * Helper method to create test land acquisition
     */
    private function createTestLandAcquisition()
    {
        if ($this->testAcquisitionId) {
            return; // Already created
        }
        
        $data = [
            'farmer_id' => 1,
            'land_area' => 5000.00,
            'location' => 'Test Location',
            'acquisition_date' => '2023-01-15',
            'created_by' => 1
        ];
        
        $result = $this->plottingService->addLandAcquisition($data);
        
        if ($result['success']) {
            $this->testAcquisitionId = $result['acquisition_id'];
        }
    }
    
    /**
     * Helper method to create test plot
     */
    private function createTestPlot()
    {
        if ($this->testPlotId) {
            return; // Already created
        }
        
        if (!$this->testAcquisitionId) {
            $this->createTestLandAcquisition();
        }
        
        $data = [
            'land_acquisition_id' => $this->testAcquisitionId,
            'plot_area' => 500.00,
            'current_price' => 500000.00,
            'created_by' => 1
        ];
        
        $result = $this->plottingService->addPlot($data);
        
        if ($result['success']) {
            $this->testPlotId = $result['plot_id'];
        }
    }
    
    /**
     * Helper method to create test booking
     */
    private function createTestBooking()
    {
        if ($this->testBookingId) {
            return; // Already created
        }
        
        if (!$this->testPlotId) {
            $this->createTestPlot();
        }
        
        $data = [
            'plot_id' => $this->testPlotId,
            'customer_id' => 1,
            'booking_amount' => 50000.00,
            'total_amount' => 500000.00,
            'booking_date' => '2023-01-20',
            'created_by' => 1
        ];
        
        $result = $this->plottingService->bookPlot($data);
        
        if ($result['success']) {
            $this->testBookingId = $result['booking_id'];
        }
    }
    
    protected function tearDown(): void
    {
        // Clean up test data
        if ($this->testBookingId) {
            $this->database->query("DELETE FROM plot_bookings WHERE id = ?", [$this->testBookingId]);
        }
        
        if ($this->testPlotId) {
            $this->database->query("DELETE FROM plots WHERE id = ?", [$this->testPlotId]);
        }
        
        if ($this->testAcquisitionId) {
            $this->database->query("DELETE FROM land_acquisitions WHERE id = ?", [$this->testAcquisitionId]);
        }
        
        parent::tearDown();
    }
}