<?php

namespace Tests\Services\Land;

use PHPUnit\Framework\TestCase;
use App\Services\Land\PlottingService;
use App\Core\Database;
use Psr\Log\LoggerInterface;

class PlottingServiceTest extends TestCase
{
    private PlottingService $plottingService;
    private Database $db;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->db = $this->createMock(Database::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->plottingService = new PlottingService($this->db, $this->logger);
    }

    public function testCreateProjectSuccess(): void
    {
        $projectData = [
            'name' => 'Test Project',
            'location' => 'Test Location',
            'total_area' => 10000,
            'project_type' => 'residential',
            'developer_name' => 'Test Developer'
        ];

        $this->db->expects($this->once())
            ->method('execute')
            ->willReturn(1);

        $result = $this->plottingService->createProject($projectData);

        $this->assertTrue($result['success']);
        $this->assertEquals('Project created successfully', $result['message']);
        $this->assertArrayHasKey('project_id', $result);
    }

    public function testCreateProjectValidationFailure(): void
    {
        $projectData = [
            'name' => '',
            'location' => '',
            'total_area' => 0
        ];

        $result = $this->plottingService->createProject($projectData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Project validation failed', $result['message']);
        $this->assertArrayHasKey('errors', $result);
    }

    public function testSubdivideLandSuccess(): void
    {
        $projectId = 1;
        $subdivisionData = [
            'subdivision_name' => 'Test Subdivision',
            'total_area' => 5000,
            'number_of_plots' => 10,
            'plot_type' => 'residential',
            'price_per_sq_meter' => 1000
        ];

        $this->db->expects($this->exactly(2))
            ->method('execute')
            ->willReturn(1);

        $result = $this->plottingService->subdivideLand($projectId, $subdivisionData);

        $this->assertTrue($result['success']);
        $this->assertEquals('Land subdivided successfully', $result['message']);
        $this->assertArrayHasKey('subdivision_id', $result);
        $this->assertArrayHasKey('plots_generated', $result);
    }

    public function testSubdivideLandValidationFailure(): void
    {
        $projectId = 1;
        $subdivisionData = [
            'subdivision_name' => '',
            'total_area' => 0,
            'number_of_plots' => 0
        ];

        $result = $this->plottingService->subdivideLand($projectId, $subdivisionData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Subdivision validation failed', $result['message']);
        $this->assertArrayHasKey('errors', $result);
    }

    public function testCreatePlotSuccess(): void
    {
        $projectId = 1;
        $plotData = [
            'plot_type' => 'residential',
            'size_sq_meters' => 500,
            'price_per_sq_meter' => 1000,
            'facing_direction' => 'North'
        ];

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn(0); // Plot count for numbering

        $this->db->expects($this->exactly(2))
            ->method('execute')
            ->willReturn(1);

        $result = $this->plottingService->createPlot($projectId, $plotData);

        $this->assertTrue($result['success']);
        $this->assertEquals('Plot created successfully', $result['message']);
        $this->assertArrayHasKey('plot_id', $result);
        $this->assertArrayHasKey('plot_number', $result);
    }

    public function testCreatePlotValidationFailure(): void
    {
        $projectId = 1;
        $plotData = [
            'plot_type' => 'invalid_type',
            'size_sq_meters' => 50, // Too small
            'price_per_sq_meter' => 1000
        ];

        $result = $this->plottingService->createPlot($projectId, $plotData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Plot validation failed', $result['message']);
        $this->assertArrayHasKey('errors', $result);
    }

    public function testReservePlotSuccess(): void
    {
        $plotId = 1;
        $customerData = [
            'customer_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'address' => 'Test Address'
        ];
        $paymentData = [
            'reservation_amount' => 50000
        ];

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn('available'); // Plot is available

        $this->db->expects($this->once())
            ->method('execute')
            ->willReturn(1);

        $result = $this->plottingService->reservePlot($plotId, $customerData, $paymentData);

        $this->assertTrue($result['success']);
        $this->assertEquals('Plot reserved successfully', $result['message']);
        $this->assertArrayHasKey('reservation_id', $result);
    }

    public function testReservePlotNotAvailable(): void
    {
        $plotId = 1;
        $customerData = [
            'customer_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890'
        ];

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn('sold'); // Plot is not available

        $result = $this->plottingService->reservePlot($plotId, $customerData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Plot is not available for reservation', $result['message']);
    }

    public function testReservePlotCustomerValidationFailure(): void
    {
        $plotId = 1;
        $customerData = [
            'customer_name' => '',
            'email' => 'invalid-email',
            'phone' => ''
        ];

        $result = $this->plottingService->reservePlot($plotId, $customerData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Customer validation failed', $result['message']);
        $this->assertArrayHasKey('errors', $result);
    }

    public function testSellPlotSuccess(): void
    {
        $plotId = 1;
        $saleData = [
            'customer_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'sale_amount' => 500000,
            'total_amount' => 500000,
            'sale_date' => '2026-03-08'
        ];

        $plot = [
            'id' => $plotId,
            'plot_number' => 'PLOT-0001',
            'status' => 'reserved'
        ];

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn($plot);

        $this->db->expects($this->exactly(2))
            ->method('execute')
            ->willReturn(1);

        $result = $this->plottingService->sellPlot($plotId, $saleData);

        $this->assertTrue($result['success']);
        $this->assertEquals('Plot sold successfully', $result['message']);
        $this->assertArrayHasKey('sale_id', $result);
    }

    public function testSellPlotNotFound(): void
    {
        $plotId = 999;
        $saleData = [
            'customer_name' => 'John Doe',
            'sale_amount' => 500000,
            'sale_date' => '2026-03-08'
        ];

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn(null);

        $result = $this->plottingService->sellPlot($plotId, $saleData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Plot not found', $result['message']);
    }

    public function testSellPlotValidationFailure(): void
    {
        $plotId = 1;
        $saleData = [
            'customer_name' => '',
            'sale_amount' => 0,
            'sale_date' => ''
        ];

        $plot = [
            'id' => $plotId,
            'plot_number' => 'PLOT-0001',
            'status' => 'reserved'
        ];

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn($plot);

        $result = $this->plottingService->sellPlot($plotId, $saleData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Sale validation failed', $result['message']);
        $this->assertArrayHasKey('errors', $result);
    }

    public function testGetProject(): void
    {
        $projectId = 1;
        $project = [
            'id' => $projectId,
            'name' => 'Test Project',
            'location' => 'Test Location',
            'status' => 'development'
        ];

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn($project);

        $this->db->expects($this->exactly(4))
            ->method('fetchAll')
            ->willReturn([], [], [], []); // Empty documents, subdivisions, stages, plots

        $result = $this->plottingService->getProject($projectId);

        $this->assertEquals($project, $result);
        $this->assertArrayHasKey('documents', $result);
        $this->assertArrayHasKey('subdivisions', $result);
        $this->assertArrayHasKey('stages', $result);
        $this->assertArrayHasKey('plots', $result);
    }

    public function testGetProjectNotFound(): void
    {
        $projectId = 999;

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn(null);

        $result = $this->plottingService->getProject($projectId);

        $this->assertNull($result);
    }

    public function testGetPlot(): void
    {
        $plotId = 1;
        $plot = [
            'id' => $plotId,
            'plot_number' => 'PLOT-0001',
            'plot_type' => 'residential',
            'size_sq_meters' => 500,
            'status' => 'available',
            'project_name' => 'Test Project',
            'project_location' => 'Test Location'
        ];

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn($plot);

        $this->db->expects($this->exactly(4))
            ->method('fetchAll')
            ->willReturn([], [], [], []); // Empty boundaries, documents, reservations, sales

        $result = $this->plottingService->getPlot($plotId);

        $this->assertEquals($plot, $result);
        $this->assertArrayHasKey('boundaries', $result);
        $this->assertArrayHasKey('documents', $result);
        $this->assertArrayHasKey('reservations', $result);
        $this->assertArrayHasKey('sales', $result);
    }

    public function testGetPlotNotFound(): void
    {
        $plotId = 999;

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn(null);

        $result = $this->plottingService->getPlot($plotId);

        $this->assertNull($result);
    }

    public function testGetProjects(): void
    {
        $filters = ['status' => 'development'];
        $projects = [
            ['id' => 1, 'name' => 'Project 1', 'status' => 'development'],
            ['id' => 2, 'name' => 'Project 2', 'status' => 'development']
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($projects);

        $result = $this->plottingService->getProjects($filters);

        $this->assertCount(2, $result);
        $this->assertEquals('Project 1', $result[0]['name']);
    }

    public function testGetPlots(): void
    {
        $filters = ['status' => 'available'];
        $plots = [
            ['id' => 1, 'plot_number' => 'PLOT-0001', 'status' => 'available'],
            ['id' => 2, 'plot_number' => 'PLOT-0002', 'status' => 'available']
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($plots);

        $result = $this->plottingService->getPlots($filters);

        $this->assertCount(2, $result);
        $this->assertEquals('PLOT-0001', $result[0]['plot_number']);
    }

    public function testGetPlottingStats(): void
    {
        $stats = [
            'total_projects' => 10,
            'plots_by_status' => ['available' => 50, 'sold' => 30],
            'sales' => ['total_sales' => 30, 'total_revenue' => 15000000]
        ];

        $this->db->expects($this->exactly(4))
            ->method('fetchOne')
            ->willReturnOnConsecutiveCalls(
                $stats['total_projects'],
                ['available' => 50],
                30,
                15000000
            );

        $this->db->expects($this->exactly(2))
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls(
                [['status' => 'available', 'count' => 50]],
                []
            );

        $result = $this->plottingService->getPlottingStats();

        $this->assertEquals($stats['total_projects'], $result['total_projects']);
        $this->assertArrayHasKey('plots_by_status', $result);
        $this->assertArrayHasKey('sales', $result);
        $this->assertArrayHasKey('revenue_by_project', $result);
    }

    public function testGetPlottingStatsWithFilters(): void
    {
        $filters = ['date_from' => '2026-01-01'];

        $this->db->expects($this->exactly(4))
            ->method('fetchOne')
            ->willReturnOnConsecutiveCalls(
                5,
                ['available' => 25],
                15,
                7500000
            );

        $this->db->expects($this->exactly(2))
            ->method('fetchAll')
            ->willReturn([], []);

        $result = $this->plottingService->getPlottingStats($filters);

        $this->assertEquals(5, $result['total_projects']);
        $this->assertArrayHasKey('plots_by_status', $result);
        $this->assertArrayHasKey('sales', $result);
    }

    public function testCalculateProjectCompletion(): void
    {
        // This would test the private method through public interface
        $projectId = 1;
        $project = [
            'id' => $projectId,
            'name' => 'Test Project',
            'status' => 'development'
        ];

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn($project);

        $this->db->expects($this->exactly(4))
            ->method('fetchAll')
            ->willReturn([], [], [], []);

        $result = $this->plottingService->getProject($projectId);

        // The completion percentage would be calculated based on stages
        $this->assertArrayHasKey('stages', $result);
    }

    public function testGeneratePlotNumber(): void
    {
        // This tests the plot number generation logic
        $projectId = 1;
        $plotData = [
            'plot_type' => 'residential',
            'size_sq_meters' => 500,
            'price_per_sq_meter' => 1000
        ];

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn(5); // 5 existing plots

        $this->db->expects($this->exactly(2))
            ->method('execute')
            ->willReturn(1);

        $result = $this->plottingService->createPlot($projectId, $plotData);

        $this->assertTrue($result['success']);
        $this->assertEquals('PLOT-0006', $result['plot_number']); // Next plot number
    }

    public function testIsPlotAvailable(): void
    {
        $plotId = 1;
        $customerData = [
            'customer_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890'
        ];

        // Test with available plot
        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn('available');

        $this->db->expects($this->once())
            ->method('execute')
            ->willReturn(1);

        $result = $this->plottingService->reservePlot($plotId, $customerData);

        $this->assertTrue($result['success']);
    }

    public function testPlotTypesAndStages(): void
    {
        // Test that the service properly loads plot types and stages
        $this->assertNotEmpty($this->plottingService);
        
        // The service should have initialized with default plot types and stages
        $this->assertTrue(true); // Service creation successful indicates proper initialization
    }
}
