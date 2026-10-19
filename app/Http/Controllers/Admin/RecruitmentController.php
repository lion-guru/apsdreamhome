<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Services\RecruitmentService;

class RecruitmentController extends AdminController
{
    private $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new RecruitmentService();
    }

    public function index()
    {
        $this->requireAdmin();
        $filters = [
            'status' => $_GET['status'] ?? null,
            'department' => $_GET['department'] ?? null,
            'employment_type' => $_GET['employment_type'] ?? null,
            'search' => $_GET['search'] ?? null,
        ];
        $jobs = $this->service->getJobPostings($filters);
        $stats = $this->service->getStats();

        return $this->render('admin/recruitment/index', [
            'page_title' => 'Recruitment / ATS',
            'jobs' => $jobs,
            'stats' => $stats,
            'filters' => $_GET,
        ]);
    }

    public function create()
    {
        $this->requireAdmin();
        $managers = $this->db->fetchAll("SELECT id, name FROM users WHERE status = 'active' AND role IN ('admin','super_admin','manager','director','hr_manager') ORDER BY name");
        return $this->render('admin/recruitment/create', [
            'page_title' => 'Create Job Posting',
            'managers' => $managers,
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $data = $_POST;
        $data['created_by'] = $_SESSION['admin_id'] ?? 1;
        $data['hiring_manager_id'] = $data['hiring_manager_id'] ?? ($_SESSION['admin_id'] ?? 1);
        $result = $this->service->createJobPosting($data);
        $this->setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect(BASE_URL . '/admin/recruitment');
    }

    public function show($id)
    {
        $this->requireAdmin();
        $job = $this->service->getJobPosting($id);
        if (!$job) {
            $this->setFlash('error', 'Job not found');
            $this->redirect(BASE_URL . '/admin/recruitment');
        }
        $applications = $this->service->getApplications(['job_id' => $id]);
        return $this->render('admin/recruitment/show', [
            'page_title' => 'Job: ' . $job['title'],
            'job' => $job,
            'applications' => $applications,
        ]);
    }

    public function edit($id)
    {
        $this->requireAdmin();
        $job = $this->service->getJobPosting($id);
        if (!$job) {
            $this->setFlash('error', 'Job not found');
            $this->redirect(BASE_URL . '/admin/recruitment');
        }
        $managers = $this->db->fetchAll("SELECT id, name FROM users WHERE status = 'active' AND role IN ('admin','super_admin','manager','director','hr_manager') ORDER BY name");
        return $this->render('admin/recruitment/create', [
            'page_title' => 'Edit Job Posting',
            'job' => $job,
            'managers' => $managers,
            'mode' => 'edit',
        ]);
    }

    public function update($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $result = $this->service->updateJobPosting($id, $_POST);
        $this->setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect(BASE_URL . '/admin/recruitment/' . $id);
    }

    public function publish($id)
    {
        $this->requireAdmin();
        $result = $this->service->publishJob($id);
        $this->setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect(BASE_URL . '/admin/recruitment/' . $id);
    }

    public function applications($jobId)
    {
        $this->requireAdmin();
        $filters = ['job_id' => $jobId, 'status' => $_GET['status'] ?? null];
        $applications = $this->service->getApplications($filters);
        $job = $this->service->getJobPosting($jobId);
        return $this->render('admin/recruitment/applications', [
            'page_title' => 'Applications: ' . $job['title'],
            'job' => $job,
            'applications' => $applications,
        ]);
    }

    public function scheduleInterview()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $result = $this->service->scheduleInterview((int)($_POST['application_id'] ?? 0), $_POST);
        $this->setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect(BASE_URL . '/admin/recruitment/' . ($_POST['job_id'] ?? 1) . '/applications');
    }

    public function submitFeedback()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $result = $this->service->submitInterviewFeedback((int)($_POST['interview_id'] ?? 0), $_POST);
        $this->setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect(BASE_URL . '/admin/recruitment');
    }

    public function createOffer()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $data = $_POST;
        $data['created_by'] = $_SESSION['admin_id'] ?? 1;
        $result = $this->service->createOffer($data);
        $this->setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect(BASE_URL . '/admin/recruitment');
    }

    public function sendOffer($id)
    {
        $this->requireAdmin();
        $result = $this->service->sendOffer($id);
        $this->setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect(BASE_URL . '/admin/recruitment');
    }

    public function acceptOffer($id)
    {
        $this->requireAdmin();
        $result = $this->service->acceptOffer($id);
        $this->setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect(BASE_URL . '/admin/recruitment');
    }

    public function stats()
    {
        $this->requireAdmin();
        return $this->jsonResponse($this->service->getStats());
    }
}