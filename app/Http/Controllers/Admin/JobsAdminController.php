<?php
/**
 * Careers/Jobs Admin Controller
 * HR/Admin can post jobs, manage applications, review candidates
 */

namespace App\Http\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database\Database;
use App\Services\CoreFunctionsServiceCustom;

class JobsAdminController extends Controller
{
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
        
        // Check admin authentication
        if (!isset($_SESSION['admin_id']) && !isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }
    }
    
    /**
     * Jobs listing page
     */
    public function index()
    {
        try {
            $sql = "SELECT j.*, u.name as posted_by_name, 
                    (SELECT COUNT(*) FROM job_applications WHERE job_id = j.id) as application_count
                    FROM jobs j 
                    LEFT JOIN users u ON j.posted_by = u.id 
                    ORDER BY j.posted_date DESC";
            $stmt = $this->db->query($sql);
            $jobs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $this->view('admin/jobs/index', [
                'page_title' => 'Job Management - HR Dashboard',
                'jobs' => $jobs
            ]);
        } catch (\Exception $e) {
            $this->view('admin/jobs/index', [
                'page_title' => 'Job Management',
                'jobs' => [],
                'error' => 'Failed to load jobs: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Create new job form
     */
    public function create()
    {
        $this->view('admin/jobs/create', [
            'page_title' => 'Post New Job',
            'departments' => $this->getDepartments(),
            'job_types' => ['Full-time', 'Part-time', 'Contract', 'Internship']
        ]);
    }
    
    /**
     * Store new job
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 405);
        }
        
        try {
            $data = [
                'title' => CoreFunctionsServiceCustom::validateInput($_POST['title'] ?? '', 'string'),
                'department' => CoreFunctionsServiceCustom::validateInput($_POST['department'] ?? '', 'string'),
                'location' => CoreFunctionsServiceCustom::validateInput($_POST['location'] ?? '', 'string'),
                'job_type' => CoreFunctionsServiceCustom::validateInput($_POST['job_type'] ?? 'Full-time', 'string'),
                'experience' => CoreFunctionsServiceCustom::validateInput($_POST['experience'] ?? '', 'string'),
                'salary_range' => CoreFunctionsServiceCustom::validateInput($_POST['salary_range'] ?? '', 'string'),
                'description' => CoreFunctionsServiceCustom::validateInput($_POST['description'] ?? '', 'string'),
                'requirements' => CoreFunctionsServiceCustom::validateInput($_POST['requirements'] ?? '', 'string'),
                'responsibilities' => CoreFunctionsServiceCustom::validateInput($_POST['responsibilities'] ?? '', 'string'),
                'benefits' => CoreFunctionsServiceCustom::validateInput($_POST['benefits'] ?? '', 'string'),
                'closing_date' => !empty($_POST['closing_date']) ? $_POST['closing_date'] : null,
                'status' => 'active',
                'posted_by' => $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 1
            ];
            
            $sql = "INSERT INTO jobs (title, department, location, job_type, experience, salary_range, 
                    description, requirements, responsibilities, benefits, closing_date, status, posted_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['title'], $data['department'], $data['location'], $data['job_type'],
                $data['experience'], $data['salary_range'], $data['description'], $data['requirements'],
                $data['responsibilities'], $data['benefits'], $data['closing_date'], $data['status'], $data['posted_by']
            ]);
            
            $jobId = $this->db->lastInsertId();
            
            return $this->jsonResponse([
                'success' => true, 
                'message' => 'Job posted successfully',
                'job_id' => $jobId
            ]);
            
        } catch (\Exception $e) {
            return $this->jsonError('Failed to create job: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Edit job form
     */
    public function edit($id)
    {
        try {
            $sql = "SELECT * FROM jobs WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $job = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$job) {
                header('Location: ' . BASE_URL . '/admin/jobs');
                exit;
            }
            
            $this->view('admin/jobs/edit', [
                'page_title' => 'Edit Job - ' . $job['title'],
                'job' => $job,
                'departments' => $this->getDepartments(),
                'job_types' => ['Full-time', 'Part-time', 'Contract', 'Internship']
            ]);
        } catch (\Exception $e) {
            header('Location: ' . BASE_URL . '/admin/jobs');
            exit;
        }
    }
    
    /**
     * Update job
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 405);
        }
        
        try {
            $sql = "UPDATE jobs SET 
                    title = ?, department = ?, location = ?, job_type = ?, experience = ?,
                    salary_range = ?, description = ?, requirements = ?, responsibilities = ?,
                    benefits = ?, status = ?, closing_date = ?, updated_at = NOW()
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $_POST['title'], $_POST['department'], $_POST['location'], $_POST['job_type'],
                $_POST['experience'], $_POST['salary_range'], $_POST['description'],
                $_POST['requirements'], $_POST['responsibilities'], $_POST['benefits'],
                $_POST['status'], $_POST['closing_date'] ?: null, $id
            ]);
            
            return $this->jsonResponse(['success' => true, 'message' => 'Job updated successfully']);
        } catch (\Exception $e) {
            return $this->jsonError('Failed to update job: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Delete job
     */
    public function delete($id)
    {
        try {
            $sql = "DELETE FROM jobs WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            
            return $this->jsonResponse(['success' => true, 'message' => 'Job deleted successfully']);
        } catch (\Exception $e) {
            return $this->jsonError('Failed to delete job: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * View job applications
     */
    public function applications($jobId = null)
    {
        try {
            if ($jobId) {
                // Applications for specific job
                $sql = "SELECT ja.*, j.title as job_title 
                        FROM job_applications ja 
                        JOIN jobs j ON ja.job_id = j.id 
                        WHERE ja.job_id = ? 
                        ORDER BY ja.applied_at DESC";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$jobId]);
            } else {
                // All applications
                $sql = "SELECT ja.*, j.title as job_title 
                        FROM job_applications ja 
                        JOIN jobs j ON ja.job_id = j.id 
                        ORDER BY ja.applied_at DESC";
                $stmt = $this->db->query($sql);
            }
            
            $applications = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Get all jobs for filter
            $jobsSql = "SELECT id, title FROM jobs WHERE status = 'active' ORDER BY title";
            $jobsStmt = $this->db->query($jobsSql);
            $jobs = $jobsStmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $this->view('admin/jobs/applications', [
                'page_title' => 'Job Applications',
                'applications' => $applications,
                'jobs' => $jobs,
                'selected_job' => $jobId,
                'statuses' => ['new', 'reviewed', 'shortlisted', 'interviewed', 'offered', 'hired', 'rejected']
            ]);
        } catch (\Exception $e) {
            $this->view('admin/jobs/applications', [
                'page_title' => 'Job Applications',
                'applications' => [],
                'error' => 'Failed to load applications: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * View single application
     */
    public function viewApplication($id)
    {
        try {
            $sql = "SELECT ja.*, j.title as job_title, j.department 
                    FROM job_applications ja 
                    JOIN jobs j ON ja.job_id = j.id 
                    WHERE ja.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $application = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$application) {
                header('Location: ' . BASE_URL . '/admin/jobs/applications');
                exit;
            }
            
            $this->view('admin/jobs/view_application', [
                'page_title' => 'Application - ' . $application['first_name'] . ' ' . $application['last_name'],
                'application' => $application,
                'statuses' => ['new', 'reviewed', 'shortlisted', 'interviewed', 'offered', 'hired', 'rejected']
            ]);
        } catch (\Exception $e) {
            header('Location: ' . BASE_URL . '/admin/jobs/applications');
            exit;
        }
    }
    
    /**
     * Update application status
     */
    public function updateApplicationStatus($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 405);
        }
        
        try {
            $status = $_POST['status'] ?? 'new';
            $notes = $_POST['notes'] ?? '';
            
            $sql = "UPDATE job_applications SET 
                    status = ?, notes = ?, reviewed_at = NOW(), reviewed_by = ? 
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $status, $notes, 
                $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 1, 
                $id
            ]);
            
            return $this->jsonResponse([
                'success' => true, 
                'message' => 'Application status updated to: ' . ucfirst($status)
            ]);
        } catch (\Exception $e) {
            return $this->jsonError('Failed to update status: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get departments list
     */
    private function getDepartments()
    {
        return [
            'Sales' => 'Sales',
            'Marketing' => 'Marketing',
            'Operations' => 'Operations',
            'HR' => 'Human Resources',
            'Finance' => 'Finance',
            'IT' => 'IT',
            'Legal' => 'Legal',
            'Customer Service' => 'Customer Service'
        ];
    }
    
    /**
     * JSON response helper
     */
    private function jsonResponse($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * JSON error helper
     */
    private function jsonError($message, $code = 400)
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $message]);
        exit;
    }
}
