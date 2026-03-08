<?php

namespace App\Controllers\HumanResources;

use App\Services\HR\CareerService;
use App\Services\Auth\AuthenticationService;
use App\Core\ViewRenderer;

/**
 * Career Controller - APS Dream Home
 * Custom MVC implementation without Laravel dependencies
 */
class CareerController
{
    private $careerService;
    private $authService;
    private $viewRenderer;

    public function __construct()
    {
        $this->careerService = new CareerService();
        $this->authService = new AuthenticationService();
        $this->viewRenderer = new ViewRenderer();
    }

    /**
     * Show careers page
     */
    public function index($request)
    {
        $data = [
            'title' => 'Careers - APS Dream Home',
            'user' => $this->authService->getCurrentUser(),
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors']);

        return $this->viewRenderer->render('careers/index', $data);
    }

    /**
     * Show job application form
     */
    public function apply($request)
    {
        $position = $request['get']['position'] ?? '';

        $data = [
            'title' => 'Apply for Position - APS Dream Home',
            'user' => $this->authService->getCurrentUser(),
            'position' => $position,
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? [],
            'old_input' => $_SESSION['old_input'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors'], $_SESSION['old_input']);

        return $this->viewRenderer->render('careers/apply', $data);
    }

    /**
     * Submit job application
     */
    public function submitApplication($request)
    {
        $data = [
            'full_name' => trim($request['post']['full_name'] ?? ''),
            'email' => trim($request['post']['email'] ?? ''),
            'phone' => trim($request['post']['phone'] ?? ''),
            'position' => trim($request['post']['position'] ?? ''),
            'experience' => trim($request['post']['experience'] ?? ''),
            'cover_letter' => trim($request['post']['cover_letter'] ?? ''),
            'availability' => trim($request['post']['availability'] ?? '')
        ];

        $files = $request['files'] ?? [];

        $result = $this->careerService->submitApplication($data, $files);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            $this->redirect('/careers/thank-you');
        } else {
            $_SESSION['errors'] = $result['errors'] ?? [$result['message']];
            $_SESSION['old_input'] = $data;
            $this->redirect('/careers/apply?position=' . urlencode($data['position']));
        }

        return $result;
    }

    /**
     * Show thank you page
     */
    public function thankYou($request)
    {
        $data = [
            'title' => 'Application Submitted - APS Dream Home',
            'user' => $this->authService->getCurrentUser(),
            'success' => $_SESSION['success'] ?? ''
        ];

        unset($_SESSION['success']);

        return $this->viewRenderer->render('careers/thank-you', $data);
    }

    /**
     * Show admin applications list
     */
    public function applications($request)
    {
        // Check authentication and admin access
        if (!$this->authService->isAuthenticated() || !$this->isAdmin($this->authService->getCurrentUser())) {
            $_SESSION['errors'] = ['Access denied'];
            $this->redirect('/login');
            return;
        }

        $page = max(1, intval($request['get']['page'] ?? 1));
        $limit = 20;
        $filters = [
            'status' => $request['get']['status'] ?? '',
            'position' => $request['get']['position'] ?? '',
            'search' => $request['get']['search'] ?? ''
        ];

        $result = $this->careerService->getApplications($page, $limit, $filters);

        // Get available positions for filter
        $positionsResult = $this->careerService->getAvailablePositions();

        $data = [
            'title' => 'Job Applications - APS Dream Home',
            'user' => $this->authService->getCurrentUser(),
            'applications' => $result['success'] ? $result['data']['applications'] : [],
            'pagination' => $result['success'] ? $result['data']['pagination'] : [],
            'positions' => $positionsResult['success'] ? $positionsResult['data'] : [],
            'filters' => $filters,
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors']);

        return $this->viewRenderer->render('careers/applications', $data);
    }

    /**
     * Show application details
     */
    public function applicationDetails($request)
    {
        // Check authentication and admin access
        if (!$this->authService->isAuthenticated() || !$this->isAdmin($this->authService->getCurrentUser())) {
            $_SESSION['errors'] = ['Access denied'];
            $this->redirect('/login');
            return;
        }

        $id = $request['params']['id'] ?? null;

        if (!$id) {
            $_SESSION['errors'] = ['Application ID is required'];
            $this->redirect('/admin/careers/applications');
            return;
        }

        $result = $this->careerService->getApplicationDetails($id);

        if (!$result['success']) {
            $_SESSION['errors'] = [$result['message']];
            $this->redirect('/admin/careers/applications');
            return;
        }

        $data = [
            'title' => 'Application Details - APS Dream Home',
            'user' => $this->authService->getCurrentUser(),
            'application' => $result['data']['application'],
            'history' => $result['data']['history'],
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors']);

        return $this->viewRenderer->render('careers/details', $data);
    }

    /**
     * Update application status
     */
    public function updateStatus($request)
    {
        // Check authentication and admin access
        if (!$this->authService->isAuthenticated() || !$this->isAdmin($this->authService->getCurrentUser())) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $id = $request['params']['id'] ?? null;
        $status = $request['post']['status'] ?? '';
        $notes = $request['post']['notes'] ?? '';

        if (!$id) {
            return [
                'success' => false,
                'message' => 'Application ID is required'
            ];
        }

        $result = $this->careerService->updateApplicationStatus($id, $status, $notes);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['errors'] = [$result['message']];
        }

        $this->redirect("/admin/careers/applications/$id");

        return $result;
    }

    /**
     * Delete application
     */
    public function deleteApplication($request)
    {
        // Check authentication and admin access
        if (!$this->authService->isAuthenticated() || !$this->isAdmin($this->authService->getCurrentUser())) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $id = $request['params']['id'] ?? null;

        if (!$id) {
            return [
                'success' => false,
                'message' => 'Application ID is required'
            ];
        }

        $result = $this->careerService->deleteApplication($id);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['errors'] = [$result['message']];
        }

        $this->redirect('/admin/careers/applications');

        return $result;
    }

    /**
     * Get application statistics (AJAX)
     */
    public function getApplicationStats($request)
    {
        // Check authentication and admin access
        if (!$this->authService->isAuthenticated() || !$this->isAdmin($this->authService->getCurrentUser())) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        return $this->careerService->getApplicationStats();
    }

    /**
     * Get applications (AJAX)
     */
    public function getApplications($request)
    {
        // Check authentication and admin access
        if (!$this->authService->isAuthenticated() || !$this->isAdmin($this->authService->getCurrentUser())) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $page = max(1, intval($request['get']['page'] ?? 1));
        $limit = min(max(intval($request['get']['limit'] ?? 20), 1), 100);
        $filters = [
            'status' => $request['get']['status'] ?? '',
            'position' => $request['get']['position'] ?? '',
            'search' => $request['get']['search'] ?? ''
        ];

        return $this->careerService->getApplications($page, $limit, $filters);
    }

    /**
     * Get available positions (AJAX)
     */
    public function getAvailablePositions($request)
    {
        // Check authentication and admin access
        if (!$this->authService->isAuthenticated() || !$this->isAdmin($this->authService->getCurrentUser())) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        return $this->careerService->getAvailablePositions();
    }

    /**
     * Download resume file
     */
    public function downloadResume($request)
    {
        // Check authentication and admin access
        if (!$this->authService->isAuthenticated() || !$this->isAdmin($this->authService->getCurrentUser())) {
            $_SESSION['errors'] = ['Access denied'];
            $this->redirect('/login');
            return;
        }

        $id = $request['params']['id'] ?? null;

        if (!$id) {
            $_SESSION['errors'] = ['Application ID is required'];
            $this->redirect('/admin/careers/applications');
            return;
        }

        $result = $this->careerService->getApplicationDetails($id);

        if (!$result['success']) {
            $_SESSION['errors'] = [$result['message']];
            $this->redirect('/admin/careers/applications');
            return;
        }

        $application = $result['data']['application'];

        if (empty($application['resume_file'])) {
            $_SESSION['errors'] = ['Resume file not found'];
            $this->redirect("/admin/careers/applications/$id");
            return;
        }

        $resumePath = STORAGE_PATH . '/uploads/resumes/' . $application['resume_file'];

        if (!file_exists($resumePath)) {
            $_SESSION['errors'] = ['Resume file not found'];
            $this->redirect("/admin/careers/applications/$id");
            return;
        }

        // Set headers for file download
        $filename = $application['full_name'] . '_resume.' . pathinfo($application['resume_file'], PATHINFO_EXTENSION);

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($resumePath));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');

        readfile($resumePath);
        exit;
    }

    /**
     * Export applications (AJAX)
     */
    public function exportApplications($request)
    {
        // Check authentication and admin access
        if (!$this->authService->isAuthenticated() || !$this->isAdmin($this->authService->getCurrentUser())) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $format = $request['post']['format'] ?? 'csv';
        $filters = [
            'status' => $request['post']['status'] ?? '',
            'position' => $request['post']['position'] ?? ''
        ];

        // Get all applications (no pagination for export)
        $result = $this->careerService->getApplications(1, 10000, $filters);

        if (!$result['success']) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve applications for export'
            ];
        }

        $applications = $result['data']['applications'];

        if ($format === 'csv') {
            $filename = 'applications_export_' . date('Y-m-d_H-i-s') . '.csv';
            $filepath = STORAGE_PATH . '/exports/' . $filename;

            // Ensure export directory exists
            $exportDir = dirname($filepath);
            if (!is_dir($exportDir)) {
                mkdir($exportDir, 0755, true);
            }

            $handle = fopen($filepath, 'w');

            // Header
            fputcsv($handle, [
                'ID',
                'Full Name',
                'Email',
                'Phone',
                'Position',
                'Experience',
                'Availability',
                'Status',
                'Created At',
                'Updated At'
            ]);

            // Data
            foreach ($applications as $application) {
                fputcsv($handle, [
                    $application['id'],
                    $application['full_name'],
                    $application['email'],
                    $application['phone'],
                    $application['position'],
                    $application['experience'],
                    $application['availability'],
                    $application['status'],
                    $application['created_at'],
                    $application['updated_at']
                ]);
            }

            fclose($handle);

            return [
                'success' => true,
                'file' => $filename,
                'path' => $filepath,
                'count' => count($applications)
            ];
        }

        return [
            'success' => false,
            'message' => 'Unsupported export format'
        ];
    }

    /**
     * Check if user is admin
     */
    private function isAdmin($user)
    {
        return $user && ($user['role'] === 'admin' || $user['role'] === 'super_admin');
    }

    /**
     * Redirect helper
     */
    private function redirect($url)
    {
        if (!headers_sent()) {
            header("Location: $url");
            exit;
        } else {
            echo '<script>window.location.href = "' . $url . '";</script>';
            exit;
        }
    }
}
