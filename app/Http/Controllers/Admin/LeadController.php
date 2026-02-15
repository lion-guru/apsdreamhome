<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\Lead;
use Exception;

class LeadController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->data = [];
    }

    /**
     * List all leads
     */
    public function index()
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }

        $this->data['page_title'] = 'Lead Management - ' . APP_NAME;
        
        // Fetch leads using the Lead model
        $stmt = $this->db->query("SELECT * FROM leads WHERE is_deleted = 0 ORDER BY created_at DESC");
        $this->data['leads'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->render('admin/leads/index');
    }

    /**
     * Show form to create a new lead
     */
    public function create()
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }

        $this->data['page_title'] = 'Add New Lead - ' . APP_NAME;
        $this->render('admin/leads/create');
    }

    /**
     * Store a newly created lead
     */
    public function store()
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }

        try {
            $leadData = $_POST;
            // Basic validation could be added here
            
            $sql = "INSERT INTO leads (first_name, last_name, email, phone, status, source, description, created_at) 
                    VALUES (:first_name, :last_name, :email, :phone, :status, :source, :description, NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':first_name' => $leadData['first_name'] ?? '',
                ':last_name' => $leadData['last_name'] ?? '',
                ':email' => $leadData['email'] ?? '',
                ':phone' => $leadData['phone'] ?? '',
                ':status' => $leadData['status'] ?? 'New',
                ':source' => $leadData['source'] ?? 'Direct',
                ':description' => $leadData['description'] ?? ''
            ]);

            $this->setFlash('success', "Lead created successfully!");
            $this->redirect('admin/leads');
        } catch (Exception $e) {
            $this->setFlash('error', "Error creating lead: " . $e->getMessage());
            $this->redirect('admin/leads/create');
        }
    }

    /**
     * Show form to edit a lead
     */
    public function edit($id)
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }

        $stmt = $this->db->prepare("SELECT * FROM leads WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $lead = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$lead) {
            $this->setFlash('error', "Lead not found!");
            $this->redirect('admin/leads');
            return;
        }

        $this->data['lead'] = $lead;
        $this->data['page_title'] = 'Edit Lead - ' . APP_NAME;
        $this->render('admin/leads/edit');
    }

    /**
     * Update an existing lead
     */
    public function update($id)
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }

        try {
            $leadData = $_POST;
            $sql = "UPDATE leads SET first_name = :first_name, last_name = :last_name, email = :email, phone = :phone, status = :status, source = :source, description = :description, updated_at = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':first_name' => $leadData['first_name'],
                ':last_name' => $leadData['last_name'],
                ':email' => $leadData['email'],
                ':phone' => $leadData['phone'],
                ':status' => $leadData['status'],
                ':source' => $leadData['source'],
                ':description' => $leadData['description'],
                ':id' => $id
            ]);

            $this->setFlash('success', "Lead updated successfully!");
            $this->redirect('admin/leads');
        } catch (Exception $e) {
            $this->setFlash('error', "Error updating lead: " . $e->getMessage());
            $this->redirect("admin/leads/edit/{$id}");
        }
    }

    /**
     * Soft delete a lead
     */
    public function delete($id)
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }

        try {
            $stmt = $this->db->prepare("UPDATE leads SET is_deleted = 1, updated_at = NOW() WHERE id = :id");
            $stmt->execute([':id' => $id]);

            $this->setFlash('success', "Lead deleted successfully!");
        } catch (Exception $e) {
            $this->setFlash('error', "Error deleting lead: " . $e->getMessage());
        }
        
        $this->redirect('admin/leads');
    }
}
