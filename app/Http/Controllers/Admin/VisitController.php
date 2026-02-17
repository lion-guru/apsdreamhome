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
        $data = $_POST;

        // Add defaults and user info
        $data['status'] = 'scheduled';
        $data['created_by'] = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;

        if ($this->visitModel) {
            $this->visitModel->fill($data);
            $result = $this->visitModel->save();
        } else {
            $result = false;
        }

        if ($result) {
            $this->redirect('admin/visits');
        } else {
            $this->redirect('admin/visits/create');
        }
    }

    /**
     * Update visit status (Complete/Cancel/Reschedule)
     */
    public function updateStatus($id)
    {
        $status = $_POST['status'] ?? '';
        $sql = "UPDATE property_visits SET status = ?, updated_at = NOW() WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$status, $id]);

        return $this->jsonResponse(['success' => $result]);
    }
}
