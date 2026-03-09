<?php

namespace Tests\Services\Backup;

use PHPUnit\Framework\TestCase;
use App\Services\Backup\BackupIntegrityService;
use App\Core\Database;
use Psr\Log\LoggerInterface;

class BackupIntegrityServiceTest extends TestCase
{
    private BackupIntegrityService $backupIntegrityService;
    private Database $db;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->db = $this->createMock(Database::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->backupIntegrityService = new BackupIntegrityService($this->db, $this->logger);
    }

    public function testVerifyBackupIntegritySuccess(): void
    {
        $backupFile = 'test_backup.sql';
        $checksum = 'abc123';
        $fileSize = 1024;

        // Mock file existence and size
        $this->mockFileExists($backupFile, true);
        $this->mockFileSize($backupFile, $fileSize);
        $this->mockFileGetContents($backupFile, 'CREATE TABLE test (id INT); INSERT INTO test VALUES (1);');

        // Mock database operations
        $this->db->expects($this->once())
            ->method('execute')
            ->willReturn(1);

        $this->db->expects($this->once())
            ->method('getLastInsertId')
            ->willReturn(1);

        $this->db->expects($this->once())
            ->method('execute')
            ->willReturn(1);

        $result = $this->backupIntegrityService->verifyBackupIntegrity($backupFile);

        $this->assertIsArray($result);
        $this->assertEquals($backupFile, $result['backup_file']);
        $this->assertEquals($fileSize, $result['file_size']);
        $this->assertArrayHasKey('verification_status', $result);
        $this->assertArrayHasKey('checks', $result);
    }

    public function testVerifyBackupIntegrityFileNotExists(): void
    {
        $backupFile = 'nonexistent_backup.sql';

        $this->mockFileExists($backupFile, false);

        $this->expectException(\InvalidArgumentException::class);
        $this->backupIntegrityService->verifyBackupIntegrity($backupFile);
    }

    public function testVerifyBackupIntegrityFileTooSmall(): void
    {
        $backupFile = 'small_backup.sql';

        $this->mockFileExists($backupFile, true);
        $this->mockFileSize($backupFile, 512); // Less than min_backup_size

        $this->expectException(\RuntimeException::class);
        $this->backupIntegrityService->verifyBackupIntegrity($backupFile);
    }

    public function testGetVerificationHistory(): void
    {
        $records = [
            [
                'id' => 1,
                'backup_file' => 'backup1.sql',
                'checksum' => 'abc123',
                'file_size' => 1024,
                'verification_status' => 'passed',
                'verification_details' => '{"test": "passed"}',
                'verification_time' => '2026-03-08 12:00:00'
            ],
            [
                'id' => 2,
                'backup_file' => 'backup2.sql',
                'checksum' => 'def456',
                'file_size' => 2048,
                'verification_status' => 'failed',
                'verification_details' => '{"test": "failed"}',
                'verification_time' => '2026-03-08 11:00:00'
            ]
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($records);

        $result = $this->backupIntegrityService->getVerificationHistory(50);

        $this->assertCount(2, $result);
        $this->assertEquals(1, $result[0]['id']);
        $this->assertEquals('backup1.sql', $result[0]['backup_file']);
        $this->assertEquals('passed', $result[0]['verification_status']);
    }

    public function testGetVerificationHistoryDatabaseError(): void
    {
        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willThrowException(new \Exception('Database error'));

        $this->logger->expects($this->once())
            ->method('error');

        $result = $this->backupIntegrityService->getVerificationHistory(50);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetBackupStatistics(): void
    {
        $this->db->expects($this->exactly(6))
            ->method('fetchOne')
            ->willReturnOnConsecutiveCalls(
                ['total' => 100], // total verifications
                ['passed' => 80],  // passed verifications
                ['failed' => 20],  // failed verifications
                ['recent' => 10],   // recent verifications
                ['avg_size' => 2048], // average file size
                ['total' => 100]   // total for success rate calculation
            );

        $stats = $this->backupIntegrityService->getBackupStatistics();

        $this->assertIsArray($stats);
        $this->assertEquals(100, $stats['total_verifications']);
        $this->assertEquals(80, $stats['passed_verifications']);
        $this->assertEquals(20, $stats['failed_verifications']);
        $this->assertEquals(80.0, $stats['success_rate']);
        $this->assertEquals(10, $stats['recent_verifications']);
        $this->assertEquals(2048.0, $stats['average_file_size']);
    }

    public function testGetBackupStatisticsDatabaseError(): void
    {
        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willThrowException(new \Exception('Database error'));

        $this->logger->expects($this->once())
            ->method('error');

        $stats = $this->backupIntegrityService->getBackupStatistics();

        $this->assertIsArray($stats);
        $this->assertEmpty($stats);
    }

    public function testScheduleVerificationSuccess(): void
    {
        $backupFile = 'test_backup.sql';
        $scheduleTime = '02:00:00';

        $this->db->expects($this->once())
            ->method('execute')
            ->willReturn(1);

        $this->logger->expects($this->once())
            ->method('info');

        $result = $this->backupIntegrityService->scheduleVerification($backupFile, $scheduleTime);

        $this->assertTrue($result);
    }

    public function testScheduleVerificationFailure(): void
    {
        $backupFile = 'test_backup.sql';
        $scheduleTime = '02:00:00';

        $this->db->expects($this->once())
            ->method('execute')
            ->willThrowException(new \Exception('Database error'));

        $this->logger->expects($this->once())
            ->method('error');

        $result = $this->backupIntegrityService->scheduleVerification($backupFile, $scheduleTime);

        $this->assertFalse($result);
    }

    public function testGetScheduledVerifications(): void
    {
        $schedules = [
            [
                'id' => 1,
                'backup_name' => 'daily_backup',
                'backup_type' => 'full',
                'schedule_type' => 'daily',
                'schedule_time' => '02:00:00',
                'last_run' => '2026-03-07 02:00:00',
                'next_run' => '2026-03-08 02:00:00',
                'is_active' => 1
            ]
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($schedules);

        $result = $this->backupIntegrityService->getScheduledVerifications();

        $this->assertCount(1, $result);
        $this->assertEquals(1, $result[0]['id']);
        $this->assertEquals('daily_backup', $result[0]['backup_name']);
        $this->assertTrue($result[0]['is_active']);
    }

    public function testGetScheduledVerificationsDatabaseError(): void
    {
        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willThrowException(new \Exception('Database error'));

        $this->logger->expects($this->once())
            ->method('error');

        $result = $this->backupIntegrityService->getScheduledVerifications();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testCleanupOldRecords(): void
    {
        $daysToKeep = 30;
        $deletedCount = 5;

        $this->db->expects($this->once())
            ->method('execute')
            ->willReturn($deletedCount);

        $this->logger->expects($this->once())
            ->method('info');

        $result = $this->backupIntegrityService->cleanupOldRecords($daysToKeep);

        $this->assertEquals($deletedCount, $result);
    }

    public function testCleanupOldRecordsFailure(): void
    {
        $daysToKeep = 30;

        $this->db->expects($this->once())
            ->method('execute')
            ->willThrowException(new \Exception('Database error'));

        $this->logger->expects($this->once())
            ->method('error');

        $result = $this->backupIntegrityService->cleanupOldRecords($daysToKeep);

        $this->assertEquals(0, $result);
    }

    public function testExportVerificationReport(): void
    {
        $records = [
            [
                'id' => 1,
                'backup_file' => 'backup1.sql',
                'checksum' => 'abc123',
                'file_size' => 1024,
                'verification_status' => 'passed',
                'verification_time' => '2026-03-08 12:00:00'
            ]
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($records);

        // Capture output
        ob_start();
        $filename = $this->backupIntegrityService->exportVerificationReport();
        $output = ob_get_clean();

        $this->assertStringContainsString('backup_verification_report_', $filename);
        $this->assertStringContainsString('ID,Backup File,Checksum,File Size,Status,Verification Time', $output);
        $this->assertStringContainsString('1,backup1.sql,abc123,1024,passed,2026-03-08 12:00:00', $output);
    }

    public function testExportVerificationReportWithFilters(): void
    {
        $records = [
            [
                'id' => 1,
                'backup_file' => 'backup1.sql',
                'checksum' => 'abc123',
                'file_size' => 1024,
                'verification_status' => 'passed',
                'verification_time' => '2026-03-08 12:00:00'
            ]
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($records);

        $filters = ['status' => 'passed'];

        ob_start();
        $filename = $this->backupIntegrityService->exportVerificationReport($filters);
        $output = ob_get_clean();

        $this->assertStringContainsString('backup_verification_report_', $filename);
        $this->assertNotEmpty($output);
    }

    public function testExportVerificationReportDatabaseError(): void
    {
        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willThrowException(new \Exception('Database error'));

        $this->expectException(\Exception::class);
        $this->backupIntegrityService->exportVerificationReport();
    }

    // Helper methods for mocking file operations
    private function mockFileExists(string $filename, bool $exists): void
    {
        // Mock the file_exists function
        if (!function_exists('file_exists')) {
            eval('function file_exists($filename) { return false; }');
        }
    }

    private function mockFileSize(string $filename, int $size): void
    {
        // Mock the filesize function
        if (!function_exists('filesize')) {
            eval('function filesize($filename) { return 0; }');
        }
    }

    private function mockFileGetContents(string $filename, string $content): void
    {
        // Mock the file_get_contents function
        if (!function_exists('file_get_contents')) {
            eval('function file_get_contents($filename) { return ""; }');
        }
    }

    private function mockHashFile(string $algorithm, string $filename): string
    {
        // Mock the hash_file function
        return 'mock_checksum_' . md5($filename);
    }

    private function mockMkdir(string $pathname, int $mode = 0777, bool $recursive = false): bool
    {
        // Mock the mkdir function
        return true;
    }

    private function mockIsDir(string $filename): bool
    {
        // Mock the is_dir function
        return true;
    }

    private function mockMoveUploadedFile(string $filename, string $destination): bool
    {
        // Mock the move_uploaded_file function
        return true;
    }

    private function mockHeader(string $header, bool $replace = true, int $response_code = 0): void
    {
        // Mock the header function
    }

    private function mockFopen(string $filename, string $mode)
    {
        // Mock the fopen function
        return fopen('php://memory', $mode);
    }

    private function mockFputcsv($handle, array $fields, string $separator = ',', string $enclosure = '"', string $escape = '\\'): int
    {
        // Mock the fputcsv function
        return 1;
    }

    private function mockFclose($handle): bool
    {
        // Mock the fclose function
        return true;
    }
}
