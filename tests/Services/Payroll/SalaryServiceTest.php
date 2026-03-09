<?php

namespace Tests\Services\Payroll;

use PHPUnit\Framework\TestCase;
use App\Services\Payroll\SalaryService;
use App\Core\Database;
use Psr\Log\LoggerInterface;

class SalaryServiceTest extends TestCase
{
    private SalaryService $salaryService;
    private Database $db;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->db = $this->createMock(Database::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->salaryService = new SalaryService($this->db, $this->logger);
    }

    public function testCreateSalaryStructureSuccess(): void
    {
        $data = [
            'employee_id' => 1,
            'basic_salary' => 50000,
            'hra' => 15000,
            'da' => 5000,
            'ta' => 3000,
            'medical_allowance' => 2000,
            'special_allowance' => 5000,
            'pf_deduction' => 6000,
            'esi_deduction' => 750,
            'professional_tax' => 200,
            'tds_deduction' => 5000,
            'gross_salary' => 78000,
            'net_salary' => 65050,
            'effective_from' => '2026-03-01',
            'created_by' => 1
        ];

        $this->db->expects($this->exactly(3))
            ->method('execute')
            ->willReturnOnConsecutiveCalls(1, 1, 1);

        $this->db->expects($this->once())
            ->method('getLastInsertId')
            ->willReturn(1);

        $this->logger->expects($this->once())
            ->method('info');

        $result = $this->salaryService->createSalaryStructure($data);

        $this->assertEquals(1, $result);
    }

    public function testCreateSalaryStructureFailure(): void
    {
        $data = [
            'employee_id' => 1,
            'basic_salary' => 50000,
            'gross_salary' => 78000,
            'net_salary' => 65050,
            'effective_from' => '2026-03-01',
            'created_by' => 1
        ];

        $this->db->expects($this->once())
            ->method('execute')
            ->willThrowException(new \Exception('Database error'));

        $this->logger->expects($this->once())
            ->method('error');

        $result = $this->salaryService->createSalaryStructure($data);

        $this->assertEquals(0, $result);
    }

    public function testProcessMonthlySalarySuccess(): void
    {
        $employeeId = 1;
        $month = 3;
        $year = 2026;

        $salaryStructure = [
            'id' => 1,
            'employee_id' => $employeeId,
            'basic_salary' => 50000,
            'hra' => 15000,
            'da' => 5000,
            'ta' => 3000,
            'medical_allowance' => 2000,
            'special_allowance' => 5000,
            'pf_deduction' => 6000,
            'esi_deduction' => 750,
            'professional_tax' => 200,
            'tds_deduction' => 5000,
            'gross_salary' => 78000,
            'net_salary' => 65050
        ];

        $this->db->expects($this->exactly(3))
            ->method('fetchOne')
            ->willReturnOnConsecutiveCalls(
                $salaryStructure, // getCurrentSalaryStructure
                ['count' => 0],    // isSalaryProcessed
                ['count' => 1]     // saveMonthlyPayment
            );

        $this->db->expects($this->once())
            ->method('execute')
            ->willReturn(1);

        $this->db->expects($this->once())
            ->method('getLastInsertId')
            ->willReturn(1);

        $this->logger->expects($this->once())
            ->method('info');

        $result = $this->salaryService->processMonthlySalary($employeeId, $month, $year);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['payment_id']);
        $this->assertArrayHasKey('salary_data', $result);
    }

    public function testProcessMonthlySalaryNoStructure(): void
    {
        $employeeId = 1;
        $month = 3;
        $year = 2026;

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn(null);

        $result = $this->salaryService->processMonthlySalary($employeeId, $month, $year);

        $this->assertFalse($result['success']);
        $this->assertEquals('No salary structure found', $result['message']);
    }

    public function testProcessMonthlySalaryAlreadyProcessed(): void
    {
        $employeeId = 1;
        $month = 3;
        $year = 2026;

        $salaryStructure = [
            'id' => 1,
            'employee_id' => $employeeId,
            'gross_salary' => 78000,
            'net_salary' => 65050
        ];

        $this->db->expects($this->exactly(2))
            ->method('fetchOne')
            ->willReturnOnConsecutiveCalls(
                $salaryStructure, // getCurrentSalaryStructure
                ['count' => 1]     // isSalaryProcessed
            );

        $result = $this->salaryService->processMonthlySalary($employeeId, $month, $year);

        $this->assertFalse($result['success']);
        $this->assertEquals('Salary already processed', $result['message']);
    }

    public function testGetSalaryHistory(): void
    {
        $employeeId = 1;
        $limit = 12;

        $payments = [
            [
                'id' => 1,
                'employee_id' => $employeeId,
                'month' => 3,
                'year' => 2026,
                'gross_salary' => 78000,
                'net_salary' => 65050,
                'payment_status' => 'paid',
                'working_days' => 26,
                'present_days' => 24,
                'overtime_hours' => 4.5,
                'overtime_amount' => 900
            ],
            [
                'id' => 2,
                'employee_id' => $employeeId,
                'month' => 2,
                'year' => 2026,
                'gross_salary' => 78000,
                'net_salary' => 65050,
                'payment_status' => 'paid',
                'working_days' => 26,
                'present_days' => 25,
                'overtime_hours' => 2.0,
                'overtime_amount' => 400
            ]
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($payments);

        $result = $this->salaryService->getSalaryHistory($employeeId, $limit);

        $this->assertCount(2, $result);
        $this->assertEquals(1, $result[0]['id']);
        $this->assertEquals(3, $result[0]['month']);
        $this->assertEquals(78000, $result[0]['gross_salary']);
        $this->assertEquals(65050, $result[0]['net_salary']);
    }

    public function testGetSalaryHistoryDatabaseError(): void
    {
        $employeeId = 1;
        $limit = 12;

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willThrowException(new \Exception('Database error'));

        $this->logger->expects($this->once())
            ->method('error');

        $result = $this->salaryService->getSalaryHistory($employeeId, $limit);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetPayrollStatistics(): void
    {
        $this->db->expects($this->exactly(3))
            ->method('fetchOne')
            ->willReturnOnConsecutiveCalls(
                ['total' => 50],        // total employees
                ['total' => 2500000, 'count' => 45], // monthly payroll
                ['avg_salary' => 65000]   // average salary
            );

        $stats = $this->salaryService->getPayrollStatistics();

        $this->assertIsArray($stats);
        $this->assertEquals(50, $stats['total_employees']);
        $this->assertEquals(2500000, $stats['monthly_payroll_total']);
        $this->assertEquals(45, $stats['monthly_payments_count']);
        $this->assertEquals(65000, $stats['average_salary']);
    }

    public function testGetPayrollStatisticsDatabaseError(): void
    {
        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willThrowException(new \Exception('Database error'));

        $this->logger->expects($this->once())
            ->method('error');

        $stats = $this->salaryService->getPayrollStatistics();

        $this->assertIsArray($stats);
        $this->assertEmpty($stats);
    }

    public function testUpdateSalaryStructureSuccess(): void
    {
        $structureId = 1;
        $data = [
            'basic_salary' => 55000,
            'hra' => 16500,
            'da' => 5500,
            'gross_salary' => 85800,
            'net_salary' => 71555
        ];

        $this->db->expects($this->once())
            ->method('execute')
            ->willReturn(1);

        $this->logger->expects($this->once())
            ->method('info');

        $result = $this->salaryService->updateSalaryStructure($structureId, $data);

        $this->assertTrue($result);
    }

    public function testUpdateSalaryStructureFailure(): void
    {
        $structureId = 1;
        $data = [
            'basic_salary' => 55000,
            'gross_salary' => 85800,
            'net_salary' => 71555
        ];

        $this->db->expects($this->once())
            ->method('execute')
            ->willThrowException(new \Exception('Database error'));

        $this->logger->expects($this->once())
            ->method('error');

        $result = $this->salaryService->updateSalaryStructure($structureId, $data);

        $this->assertFalse($result);
    }

    public function testGetPayrollSettings(): void
    {
        $settings = [
            ['setting_key' => 'pf_percentage', 'setting_value' => '12'],
            ['setting_key' => 'esi_percentage', 'setting_value' => '1.75'],
            ['setting_key' => 'working_days_per_month', 'setting_value' => '26']
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($settings);

        $result = $this->salaryService->getPayrollSettings();

        $this->assertIsArray($result);
        $this->assertEquals('12', $result['pf_percentage']);
        $this->assertEquals('1.75', $result['esi_percentage']);
        $this->assertEquals('26', $result['working_days_per_month']);
    }

    public function testGetPayrollSettingsDatabaseError(): void
    {
        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willThrowException(new \Exception('Database error'));

        $this->logger->expects($this->once())
            ->method('error');

        $result = $this->salaryService->getPayrollSettings();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testUpdatePayrollSettingSuccess(): void
    {
        $key = 'pf_percentage';
        $value = '15';

        $this->db->expects($this->once())
            ->method('execute')
            ->willReturn(1);

        $this->logger->expects($this->once())
            ->method('info');

        $result = $this->salaryService->updatePayrollSetting($key, $value);

        $this->assertTrue($result);
    }

    public function testUpdatePayrollSettingFailure(): void
    {
        $key = 'pf_percentage';
        $value = '15';

        $this->db->expects($this->once())
            ->method('execute')
            ->willThrowException(new \Exception('Database error'));

        $this->logger->expects($this->once())
            ->method('error');

        $result = $this->salaryService->updatePayrollSetting($key, $value);

        $this->assertFalse($result);
    }

    public function testCalculateAttendanceData(): void
    {
        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->salaryService);
        $method = $reflection->getMethod('calculateAttendanceData');
        $method->setAccessible(true);

        $result = $method->invoke($this->salaryService, 1, 3, 2026);

        $this->assertIsArray($result);
        $this->assertEquals(26, $result['working_days']);
        $this->assertEquals(24, $result['present_days']);
        $this->assertEquals(2, $result['leave_days']);
        $this->assertEquals(4.5, $result['overtime_hours']);
    }

    public function testCalculateMonthlySalary(): void
    {
        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->salaryService);
        $method = $reflection->getMethod('calculateMonthlySalary');
        $method->setAccessible(true);

        $salaryStructure = [
            'employee_id' => 1,
            'gross_salary' => 78000,
            'pf_deduction' => 6000,
            'esi_deduction' => 750,
            'professional_tax' => 200,
            'tds_deduction' => 5000
        ];

        $attendanceData = [
            'working_days' => 26,
            'present_days' => 24,
            'leave_days' => 2,
            'overtime_hours' => 4.5
        ];

        $result = $method->invoke($this->salaryService, $salaryStructure, $attendanceData);

        $this->assertIsArray($result);
        $this->assertEquals(1, $result['employee_id']);
        $this->assertEquals(3, $result['month']); // Current month
        $this->assertEquals(2026, $result['year']); // Current year
        $this->assertEquals(72000, $result['gross_salary']); // Pro-rata
        $this->assertEquals(72000 - 11950, $result['net_salary']); // Pro-rata deductions
        $this->assertEquals(26, $result['working_days']);
        $this->assertEquals(24, $result['present_days']);
        $this->assertEquals(2, $result['leave_days']);
        $this->assertEquals(4.5, $result['overtime_hours']);
        $this->assertEquals(900, $result['overtime_amount']); // 4.5 * 200
    }

    public function testSaveMonthlyPayment(): void
    {
        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->salaryService);
        $method = $reflection->getMethod('saveMonthlyPayment');
        $method->setAccessible(true);

        $salaryData = [
            'employee_id' => 1,
            'month' => 3,
            'year' => 2026,
            'gross_salary' => 72000,
            'total_deductions' => 11950,
            'net_salary' => 60050,
            'working_days' => 26,
            'present_days' => 24,
            'leave_days' => 2,
            'overtime_hours' => 4.5,
            'overtime_amount' => 900
        ];

        $this->db->expects($this->once())
            ->method('execute')
            ->willReturn(1);

        $this->db->expects($this->once())
            ->method('getLastInsertId')
            ->willReturn(1);

        $result = $method->invoke($this->salaryService, $salaryData);

        $this->assertEquals(1, $result);
    }

    public function testIsSalaryProcessed(): void
    {
        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->salaryService);
        $method = $reflection->getMethod('isSalaryProcessed');
        $method->setAccessible(true);

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn(['count' => 1]);

        $result = $method->invoke($this->salaryService, 1, 3, 2026);

        $this->assertTrue($result);
    }

    public function testGetCurrentSalaryStructure(): void
    {
        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->salaryService);
        $method = $reflection->getMethod('getCurrentSalaryStructure');
        $method->setAccessible(true);

        $structure = [
            'id' => 1,
            'employee_id' => 1,
            'gross_salary' => 78000,
            'net_salary' => 65050
        ];

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn($structure);

        $result = $method->invoke($this->salaryService, 1);

        $this->assertEquals($structure, $result);
    }
}
