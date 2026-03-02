<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

/**
 * VisitController
 * Handles Visit Management in the Admin panel
 */
class VisitController extends AdminController
{
    protected $visitModel;

    public function __construct()
    {
        parent::__construct();

        $this->visitModel = $this->model('Visit');
    }

    /**
     * Display visit dashboard
     */
    public function index()
    {
        $this->data['visits'] = $this->visitModel ? $this->visitModel->getUpcoming(20) : [];
        $this->data['stats'] = $this->visitModel ? $this->visitModel->getStats() : [];
        $this->data['page_title'] = 'Visit Management System';

        $this->render('admin/visits/index');
    }

    /**
     * Show visit creation form
     */
    public function create()
    {
        // Fetch properties and customers for dropdowns
        $this->data['properties'] = $this->db->query("SELECT id, title FROM properties WHERE status = 'available'")->fetchAll(\PDO::FETCH_ASSOC);
        $this->data['customers'] = $this->db->query("SELECT id, name FROM customers")->fetchAll(\PDO::FETCH_ASSOC);
        $this->data['page_title'] = 'Schedule New Visit';

        $this->render('admin/visits/create');
    }

    /**
     * Store a new visit
     */
    public function store()
    {
        if ($this->request->method() !== 'POST') {
            $this->redirect('admin/visits/create');
            return;
        }

        if (!$this->verifyCsrfToken($this->request->post('csrf_token') ?? '')) {
            $this->session->setFlash('error', 'Invalid CSRF token.');
            $this->redirect('admin/visits/create');
            return;
        }

        $data = $this->request->post();

        // Add defaults and user info
        $data['status'] = 'scheduled';
        $data['created_by'] = $this->session->get('admin_id') ?? $this->session->get('user_id') ?? 0;

        if ($this->visitModel) {
            $this->visitModel->fill($data);
            $result = $this->visitModel->save();
        } else {
            $result = false;
        }

        if ($result) {
            $this->session->setFlash('success', 'Visit scheduled successfully.');
            $this->redirect('admin/visits');
        } else {
            $this->session->setFlash('error', 'Failed to schedule visit.');
            $this->redirect('admin/visits/create');
        }
    }

    /**
     * Update visit status (Complete/Cancel/Reschedule)
     */
    public function updateStatus($id)
    {
        if ($this->request->method() !== 'POST') {
            return $this->jsonResponse(['success' => false, 'error' => 'Invalid request method']);
        }

        if (!$this->verifyCsrfToken($this->request->post('csrf_token') ?? '')) {
            return $this->jsonResponse(['success' => false, 'error' => 'Invalid CSRF token.']);
        }

        $status = $this->request->post('status') ?? '';
        $sql = "UPDATE property_visits SET status = ?, updated_at = NOW() WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$status, $id]);

        return $this->jsonResponse(['success' => $result]);
    }
}
