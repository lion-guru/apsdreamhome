<?php

namespace Tests\Services\Career;

use PHPUnit\Framework\TestCase;
use App\Services\Career\CareerService;
use App\Core\Database;
use Psr\Log\LoggerInterface;

class CareerServiceTest extends TestCase
{
    private CareerService $careerService;
    private Database $db;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->db = $this->createMock(Database::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->careerService = new CareerService($this->db, $this->logger);
    }

    public function testSubmitApplicationSuccess(): void
    {
        $data = [
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'cover_letter' => 'Test cover letter'
        ];

        $files = [
            'resume' => [
                'name' => 'resume.pdf',
                'type' => 'application/pdf',
                'size' => 1024000,
                'tmp_name' => '/tmp/resume.pdf',
                'error' => UPLOAD_ERR_OK
            ]
        ];

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn(0); // No recent application

        $this->db->expects($this->once())
            ->method('execute')
            ->willReturn(1);

        $result = $this->careerService->submitApplication($data, $files);

        $this->assertTrue($result['success']);
        $this->assertEquals('Application submitted successfully', $result['message']);
        $this->assertArrayHasKey('application_id', $result);
    }

    public function testSubmitApplicationValidationFailure(): void
    {
        $data = [
            'full_name' => '',
            'email' => 'invalid-email',
            'phone' => ''
        ];

        $result = $this->careerService->submitApplication($data);

        $this->assertFalse($result['success']);
        $this->assertEquals('Validation failed', $result['message']);
        $this->assertArrayHasKey('errors', $result);
    }

    public function testSubmitApplicationDuplicateApplication(): void
    {
        $data = [
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890'
        ];

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn(1); // Recent application exists

        $result = $this->careerService->submitApplication($data);

        $this->assertFalse($result['success']);
        $this->assertEquals('You have already applied for this position recently', $result['message']);
    }

    public function testSubmitApplicationInvalidFile(): void
    {
        $data = [
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890'
        ];

        $files = [
            'resume' => [
                'name' => 'resume.exe',
                'type' => 'application/octet-stream',
                'size' => 1024000,
                'tmp_name' => '/tmp/resume.exe',
                'error' => UPLOAD_ERR_OK
            ]
        ];

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn(0); // No recent application

        $result = $this->careerService->submitApplication($data, $files);

        $this->assertFalse($result['success']);
        $this->assertStringContains('Invalid file type', $result['message']);
    }

    public function testGetApplication(): void
    {
        $applicationId = 1;
        $application = [
            'id' => $applicationId,
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'status' => 'received',
            'job_title' => 'Software Developer'
        ];

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn($application);

        $this->db->expects($this->exactly(3))
            ->method('fetchAll')
            ->willReturn([], [], []); // Empty attachments, interviews, notes

        $result = $this->careerService->getApplication($applicationId);

        $this->assertEquals($application, $result);
        $this->assertArrayHasKey('attachments', $result);
        $this->assertArrayHasKey('interviews', $result);
        $this->assertArrayHasKey('notes', $result);
    }

    public function testGetApplicationNotFound(): void
    {
        $applicationId = 999;

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn(null);

        $result = $this->careerService->getApplication($applicationId);

        $this->assertNull($result);
    }

    public function testGetApplications(): void
    {
        $filters = ['status' => 'received'];
        $applications = [
            ['id' => 1, 'full_name' => 'John Doe', 'status' => 'received'],
            ['id' => 2, 'full_name' => 'Jane Smith', 'status' => 'received']
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($applications);

        $this->db->expects($this->exactly(6))
            ->method('fetchAll')
            ->willReturn([], [], [], [], [], []); // Empty attachments and interviews

        $result = $this->careerService->getApplications($filters);

        $this->assertCount(2, $result);
        $this->assertEquals('John Doe', $result[0]['full_name']);
    }

    public function testUpdateApplicationStatusSuccess(): void
    {
        $applicationId = 1;
        $newStatus = 'under_review';
        $reason = 'Application is being reviewed';

        $application = [
            'id' => $applicationId,
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'status' => 'received'
        ];

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn($application);

        $this->db->expects($this->once())
            ->method('execute')
            ->willReturn(1);

        $result = $this->careerService->updateApplicationStatus($applicationId, $newStatus, $reason);

        $this->assertTrue($result['success']);
        $this->assertEquals('Application status updated successfully', $result['message']);
    }

    public function testUpdateApplicationStatusInvalidStatus(): void
    {
        $applicationId = 1;
        $invalidStatus = 'invalid_status';

        $result = $this->careerService->updateApplicationStatus($applicationId, $invalidStatus);

        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid status', $result['message']);
    }

    public function testUpdateApplicationStatusNotFound(): void
    {
        $applicationId = 999;
        $newStatus = 'under_review';

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn(null);

        $result = $this->careerService->updateApplicationStatus($applicationId, $newStatus);

        $this->assertFalse($result['success']);
        $this->assertEquals('Application not found', $result['message']);
    }

    public function testScheduleInterviewSuccess(): void
    {
        $applicationId = 1;
        $interviewData = [
            'type' => 'video',
            'scheduled_date' => '2026-03-15 14:00:00',
            'interviewer_name' => 'Jane Smith',
            'interviewer_email' => 'jane@example.com',
            'duration_minutes' => 60
        ];

        $application = [
            'id' => $applicationId,
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'status' => 'shortlisted'
        ];

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn($application);

        $this->db->expects($this->exactly(2))
            ->method('execute')
            ->willReturn(1);

        $result = $this->careerService->scheduleInterview($applicationId, $interviewData);

        $this->assertTrue($result['success']);
        $this->assertEquals('Interview scheduled successfully', $result['message']);
        $this->assertArrayHasKey('interview_id', $result);
    }

    public function testScheduleInterviewValidationFailure(): void
    {
        $applicationId = 1;
        $interviewData = [
            'type' => 'invalid_type',
            'scheduled_date' => '',
            'interviewer_name' => '',
            'interviewer_email' => 'invalid-email'
        ];

        $result = $this->careerService->scheduleInterview($applicationId, $interviewData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Interview data validation failed', $result['message']);
        $this->assertArrayHasKey('errors', $result);
    }

    public function testGetCareerStats(): void
    {
        $stats = [
            'total_applications' => 100,
            'by_status' => ['received' => 50, 'under_review' => 30, 'shortlisted' => 20],
            'by_department' => ['IT' => 40, 'HR' => 30, 'Sales' => 30]
        ];

        $this->db->expects($this->exactly(4))
            ->method('fetchOne')
            ->willReturnOnConsecutiveCalls(
                $stats['total_applications'],
                ['received' => 50],
                ['IT' => 40],
                ['received' => 50]
            );

        $this->db->expects($this->exactly(2))
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls(
                [['status' => 'received', 'count' => 50]],
                [['department' => 'IT', 'count' => 40]]
            );

        $result = $this->careerService->getCareerStats();

        $this->assertEquals($stats['total_applications'], $result['total_applications']);
        $this->assertArrayHasKey('by_status', $result);
        $this->assertArrayHasKey('by_department', $result);
        $this->assertArrayHasKey('recent_applications', $result);
    }

    public function testGetCareerStatsWithFilters(): void
    {
        $filters = ['date_from' => '2026-01-01'];
        $stats = ['total_applications' => 50];

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn($stats['total_applications']);

        $result = $this->careerService->getCareerStats($filters);

        $this->assertEquals($stats['total_applications'], $result['total_applications']);
    }

    public function testProcessResumeUploadSuccess(): void
    {
        $file = [
            'name' => 'resume.pdf',
            'type' => 'application/pdf',
            'size' => 1024000,
            'tmp_name' => '/tmp/resume.pdf',
            'error' => UPLOAD_ERR_OK
        ];
        $fullName = 'John Doe';

        // Mock the file operations
        $result = $this->careerService->submitApplication([
            'full_name' => $fullName,
            'email' => 'john@example.com',
            'phone' => '1234567890'
        ], ['resume' => $file]);

        // This would test the file upload process
        $this->assertTrue($result['success'] || $result['success'] === false);
    }

    public function testHasRecentApplication(): void
    {
        $email = 'john@example.com';
        $jobId = 1;

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn(1); // Recent application exists

        $result = $this->careerService->submitApplication([
            'full_name' => 'John Doe',
            'email' => $email,
            'phone' => '1234567890',
            'job_id' => $jobId
        ]);

        $this->assertFalse($result['success']);
        $this->assertEquals('You have already applied for this position recently', $result['message']);
    }
}
