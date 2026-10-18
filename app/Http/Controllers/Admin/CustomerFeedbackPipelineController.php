<?php
namespace App\Http\Controllers\Admin;

use App\Services\CustomerFeedbackPipelineService;

class CustomerFeedbackPipelineController extends AdminController
{
    private CustomerFeedbackPipelineService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new CustomerFeedbackPipelineService();
    }

    public function index()
    {
        $this->requireAdmin();
        $items = $this->service->getAll($_GET);
        $stats = $this->service->getStats();
        return $this->render('admin/feedback_pipeline/index', [
            'page_title' => 'Customer Feedback Pipeline',
            'items' => $items,
            'stats' => $stats,
        ]);
    }

    public function create()
    {
        $this->requireAdmin();
        return $this->render('admin/feedback_pipeline/create', [
            'page_title' => 'Dispatch Feedback Survey',
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirect('/admin/feedback-pipeline');
        }
        $this->validateCsrfOrFail();
        $id = $this->service->dispatchSurvey($_POST);
        $this->setFlash('success', 'Feedback survey dispatched');
        return $this->redirect('/admin/feedback-pipeline/show/' . $id);
    }

    public function show(int $id)
    {
        $this->requireAdmin();
        $item = $this->service->getById($id);
        if (!$item) {
            $this->setFlash('error', 'Feedback not found');
            return $this->redirect('/admin/feedback-pipeline');
        }
        return $this->render('admin/feedback_pipeline/show', [
            'page_title' => 'Feedback Pipeline Detail',
            'item' => $item,
        ]);
    }

    public function updateStatus(int $id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirect('/admin/feedback-pipeline');
        }
        $this->validateCsrfOrFail();
        $this->service->updateStatus($id, $_POST['status'] ?? 'new', $_POST['admin_response'] ?? '');
        $this->setFlash('success', 'Status updated');
        return $this->redirect('/admin/feedback-pipeline/show/' . $id);
    }

    public function respond(int $id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirect('/admin/feedback-pipeline');
        }
        $this->validateCsrfOrFail();
        $item = $this->service->getById($id);
        if (!$item) {
            $this->setFlash('error', 'Feedback not found');
            return $this->redirect('/admin/feedback-pipeline');
        }
        $this->service->recordResponse($item['survey_token'], (int)($_POST['rating'] ?? 0), $_POST['comments'] ?? '');
        $this->setFlash('success', 'Response recorded');
        return $this->redirect('/admin/feedback-pipeline/show/' . $id);
    }

    public function publicResponse(string $token)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->service->recordResponse($token, (int)($_POST['rating'] ?? 0), $_POST['comments'] ?? '');
            $this->setFlash('success', 'Thank you for your feedback');
            return $this->redirect('/feedback/respond/' . $token);
        }
        $item = $this->service->getByToken($token);
        return $this->render('customer/feedback/respond', [
            'page_title' => 'Customer Feedback',
            'item' => $item,
            'token' => $token,
        ]);
    }
}
