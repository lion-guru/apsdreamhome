<?php

namespace App\Http\Controllers;

use App\Services\FieldCollectionService;

/**
 * On-Field Cash Collection Controller for Associates & Agents
 *
 * Allows field collectors (associates/agents) to submit cash collection
 * receipts, view their own history, and check collection stats.
 * Routes: /associate/collections/* and /agent/collections/*
 */
class FieldCollectionController extends BaseController
{
    private $service;

    public function __construct()
    {
        parent::__construct();
        try {
            $this->service = new FieldCollectionService($this->db);
        } catch (\Throwable $e) {
            error_log('[FieldCollectionController] Service init error: ' . $e->getMessage());
            $this->service = null;
        }
    }

    /**
     * Require authentication with associate or agent role.
     */
    private function requireFieldAuth()
    {
        $this->requireLogin();
        $role = $_SESSION['role'] ?? '';
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!in_array($role, ['associate', 'agent'], true) || $userId === 0) {
            $_SESSION['flash_error'] = 'Access denied. Please login as an associate or agent.';
            $this->redirect('/login');
        }
    }

    /**
     * Detect which layout to use based on session role.
     */
    private function detectLayout(): string
    {
        $role = $_SESSION['role'] ?? '';
        if ($role === 'associate') {
            return 'layouts/associate';
        }
        if ($role === 'agent') {
            return 'agent/layouts/agent';
        }
        return 'layouts/main';
    }

    /**
     * GET /associate/collections or /agent/collections
     * List this collector's cash submissions with stats cards.
     */
    public function index()
    {
        $this->requireFieldAuth();
        $this->layout = $this->detectLayout();

        $userId = (int)$_SESSION['user_id'];
        $status = $_GET['status'] ?? '';
        $fromDate = $_GET['from'] ?? '';
        $toDate = $_GET['to'] ?? '';

        $stats = $this->service ? $this->service->getMyStats($userId) : [];
        $collections = $this->service ? $this->service->getMyCollections($userId, $status, $fromDate, $toDate) : [];

        $role = $_SESSION['role'] ?? '';
        $viewPrefix = ($role === 'agent') ? 'agent' : 'associate';

        $this->render($viewPrefix . '/collections/index', [
            'page_title' => 'My Cash Collections',
            'page_heading' => 'On-Field Cash Collections',
            'stats' => $stats,
            'collections' => $collections,
            'filters' => ['status' => $status, 'from' => $fromDate, 'to' => $toDate],
        ]);
    }

    /**
     * GET /associate/collections/create or /agent/collections/create
     * Show the collection submission form.
     */
    public function create()
    {
        $this->requireFieldAuth();
        $this->layout = $this->detectLayout();

        $role = $_SESSION['role'] ?? '';
        $viewPrefix = ($role === 'agent') ? 'agent' : 'associate';

        $this->render($viewPrefix . '/collections/create', [
            'page_title' => 'Submit Collection Receipt',
            'page_heading' => 'Record Cash Collection',
            'today' => date('Y-m-d'),
        ]);
    }

    /**
     * POST /associate/collections/store or /agent/collections/store
     * Handle form submission and redirect back.
     */
    public function store()
    {
        $this->requireFieldAuth();

        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $this->setFlash('error', 'Invalid session token. Please try again.');
            $role = $_SESSION['role'] ?? '';
            $prefix = ($role === 'agent') ? 'agent' : 'associate';
            $this->redirect(BASE_URL . '/' . $prefix . '/collections/create');
        }

        $userId = (int)$_SESSION['user_id'];
        $photo = $_FILES['receipt_photo'] ?? null;

        if (!$this->service) {
            $this->setFlash('error', 'Service unavailable. Please contact admin.');
            $role = $_SESSION['role'] ?? '';
            $prefix = ($role === 'agent') ? 'agent' : 'associate';
            $this->redirect(BASE_URL . '/' . $prefix . '/collections/create');
        }

        $result = $this->service->submitFieldCollection($_POST, $userId, $photo);

        $role = $_SESSION['role'] ?? '';
        $prefix = ($role === 'agent') ? 'agent' : 'associate';

        if ($result['success']) {
            $this->setFlash('success', 'Collection recorded successfully! Receipt #' . ($result['collection_id'] ?? '') . ' is pending verification.');
            $this->redirect(BASE_URL . '/' . $prefix . '/collections');
        } else {
            $this->setFlash('error', 'Failed to record collection: ' . ($result['error'] ?? 'Unknown error'));
            $this->redirect(BASE_URL . '/' . $prefix . '/collections/create');
        }
    }

    /**
     * GET /associate/collections/{id} or /agent/collections/{id}
     * View a single collection's details.
     *
     * @param int $id
     */
    public function show(int $id = 0)
    {
        $this->requireFieldAuth();
        $this->layout = $this->detectLayout();

        $userId = (int)$_SESSION['user_id'];
        $collection = $this->service ? $this->service->getMyCollectionById($id, $userId) : null;

        if (!$collection) {
            $this->setFlash('error', 'Collection not found or access denied.');
            $role = $_SESSION['role'] ?? '';
            $prefix = ($role === 'agent') ? 'agent' : 'associate';
            $this->redirect(BASE_URL . '/' . $prefix . '/collections');
        }

        $role = $_SESSION['role'] ?? '';
        $viewPrefix = ($role === 'agent') ? 'agent' : 'associate';

        $this->render($viewPrefix . '/collections/show', [
            'page_title' => 'Collection #' . $collection['id'],
            'collection' => $collection,
        ]);
    }
}
