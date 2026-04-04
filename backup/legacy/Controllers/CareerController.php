<?php

namespace App\Http\Controllers;

use App\Services\Career\CareerService;
use App\Services\SystemLogger as Logger;
use App\Models\JobApplication;

class CareerController extends BaseController
{
    private CareerService $careerService;
    private $logger;

    public function __construct(CareerService $careerService, Logger $logger)
    {
        parent::__construct();
        $this->careerService = $careerService;
        $this->logger = $logger;
    }

    /**
     * Get request data helper
     */
    private function request()
    {
        return $this->request;
    }

    /**
     * Get request input
     */
    private function getInput($key, $default = null)
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    /**
     * Get request files
     */
    private function getFiles()
    {
        return $_FILES;
    }

    /**
     * Display career page
     */
    public function index()
    {
        try {
            $stats = $this->careerService->getCareerStats();

            return $this->view('careers.index', [
                'stats' => $stats,
                'page_title' => 'Careers - APS Dream Home'
            ]);
        } catch (\Exception $e) {
            // Simple error logging
            error_log("Failed to load career page: " . $e->getMessage());
            return $this->view('errors.500');
        }
    }

    /**
     * Submit job application
     */
    public function submitApplication()
    {
        try {
            $data = $this->request->all();
            $files = $this->getFiles();

            $result = $this->careerService->submitApplication($data, $files);

            if ($result['success']) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => $result['message'],
                    'application_id' => $result['application_id']
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => $result['message'],
                    'errors' => $result['errors'] ?? []
                ], 400);
            }
        } catch (\Exception $e) {
            // Simple error logging
            error_log("Failed to submit application: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to submit application'
            ], 500);
        }
    }

    /**
     * Get application details
     */
    public function getApplication($id)
    {
        try {
            $application = $this->careerService->getApplication((int)$id);

            if (!$application) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Application not found'
                ], 404);
            }

            return $this->jsonResponse([
                'success' => true,
                'application' => $application
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to get application", ['id' => $id, 'error' => $e->getMessage()]);
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get application'
            ], 500);
        }
    }

    /**
     * Get applications list
     */
    public function getApplications()
    {
        try {
            $filters = $this->request->all();
            $applications = $this->careerService->getApplications($filters);

            return $this->jsonResponse([
                'success' => true,
                'applications' => $applications,
                'total' => count($applications)
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to get applications", ['error' => $e->getMessage()]);
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get applications'
            ], 500);
        }
    }

    /**
     * Update application status
     */
    public function updateStatus($id)
    {
        try {
            $status = $this->getInput('status');
            $reason = $this->getInput('reason', '');

            $result = $this->careerService->updateApplicationStatus((int)$id, $status, $reason);

            if ($result['success']) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => $result['message']
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            $this->logger->error("Failed to update application status", [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to update status'
            ], 500);
        }
    }

    /**
     * Schedule interview
     */
    public function scheduleInterview($id)
    {
        try {
            $interviewData = $this->request->all();
            $result = $this->careerService->scheduleInterview((int)$id, $interviewData);

            if ($result['success']) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => $result['message'],
                    'interview_id' => $result['interview_id']
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => $result['message'],
                    'errors' => $result['errors'] ?? []
                ], 400);
            }
        } catch (\Exception $e) {
            $this->logger->error("Failed to schedule interview", [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to schedule interview'
            ], 500);
        }
    }

    /**
     * Get career statistics
     */
    public function getStats()
    {
        try {
            $filters = $this->request->all();
            $stats = $this->careerService->getCareerStats($filters);

            return $this->jsonResponse([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to get career stats", ['error' => $e->getMessage()]);
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get statistics'
            ], 500);
        }
    }

    /**
     * Application dashboard
     */
    public function dashboard()
    {
        try {
            $stats = $this->careerService->getCareerStats();
            $recentApplications = $this->careerService->getApplications(['limit' => 10]);

            return $this->view('careers.dashboard', [
                'stats' => $stats,
                'recent_applications' => $recentApplications,
                'page_title' => 'Career Dashboard - APS Dream Home'
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to load career dashboard", ['error' => $e->getMessage()]);
            return $this->view('errors.500');
        }
    }

    /**
     * Application details page
     */
    public function applicationDetails($id)
    {
        try {
            $application = $this->careerService->getApplication((int)$id);

            if (!$application) {
                return $this->redirect('/careers/dashboard');
            }

            return $this->view('careers.details', [
                'application' => $application,
                'page_title' => 'Application Details - APS Dream Home'
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to load application details", [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return $this->view('errors.500');
        }
    }

    /**
     * Export applications to CSV
     */
    public function exportApplications()
    {
        try {
            $filters = $this->request->all();
            $applications = $this->careerService->getApplications($filters);

            $csvData = [];
            $csvData[] = ['ID', 'Name', 'Email', 'Phone', 'Job Title', 'Department', 'Status', 'Applied Date'];

            foreach ($applications as $app) {
                $csvData[] = [
                    $app['id'],
                    $app['full_name'],
                    $app['email'],
                    $app['phone'],
                    $app['job_title'] ?? 'N/A',
                    $app['department'] ?? 'N/A',
                    $app['status'],
                    $app['created_at']
                ];
            }

            $filename = 'applications_' . date('Y-m-d') . '.csv';

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $output = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($output, $row);
            }
            fclose($output);
        } catch (\Exception $e) {
            $this->logger->error("Failed to export applications", ['error' => $e->getMessage()]);
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to export applications'
            ], 500);
        }
    }

    /**
     * Add note to application
     */
    public function addNote($id)
    {
        try {
            $note = $this->getInput('note');
            $createdBy = $this->getInput('created_by', 'System');

            if (empty($note)) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Note content is required'
                ], 400);
            }

            $sql = "INSERT INTO application_notes (application_id, note, created_by, created_at) VALUES (?, ?, ?, NOW())";
            $this->db->execute($sql, [$id, $note, $createdBy]);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Note added successfully'
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to add note", [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to add note'
            ], 500);
        }
    }

    /**
     * Get application timeline
     */
    public function getTimeline($id)
    {
        try {
            $application = JobApplication::find($id);

            if (!$application) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Application not found'
                ], 404);
            }

            $timeline = $application->getTimeline();

            return $this->jsonResponse([
                'success' => true,
                'timeline' => $timeline
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to get application timeline", [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get timeline'
            ], 500);
        }
    }
}
