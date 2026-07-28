<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;

/**
 * FAQ Controller
 * Handles FAQ management for admin panel
 */
class FaqController extends AdminController
{
    use \App\Traits\TenantAwareTrait;
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Show all FAQs
     */
    public function index()
    {
        try {
            $stmt = $this->db->query("SELECT * FROM faqs ORDER BY display_order ASC, created_at DESC");
            $faqs = $stmt->fetchAll();
        } catch (\Exception $e) {
            $faqs = [];
        }

        $this->render('admin/faqs/index', [
            'page_title' => 'FAQs',
            'page_description' => 'Manage frequently asked questions',
            'faqs' => $faqs
        ]);
    }

    /**
     * Show create FAQ form
     */
    public function create()
    {
        $this->render('admin/faqs/create', [
            'page_title' => 'Add New FAQ',
            'page_description' => 'Add a new frequently asked question'
        ]);
    }

    /**
     * Store new FAQ
     */
    public function store()
    {
        $data = [
            'question' => $_POST['question'] ?? '',
            'answer' => $_POST['answer'] ?? '',
            'category' => $_POST['category'] ?? 'General',
            'display_order' => $_POST['display_order'] ?? 0,
            'status' => $_POST['status'] ?? 'active',
            'created_at' => date('Y-m-d H:i:s')
        ];

        try {
            $tid = $this->tenantId();
            $sql = "INSERT INTO faqs (question, answer, category, display_order, status, tenant_id, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['question'],
                $data['answer'],
                $data['category'],
                $data['display_order'],
                $data['status'],
                $tid,
                $data['created_at']
            ]);

            $_SESSION['success'] = 'FAQ added successfully!';
            header('Location: ' . BASE_URL . '/admin/faqs');
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error adding FAQ: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/admin/faqs/create');
            exit;
        }
    }

    /**
     * Show FAQ details
     */
    public function show($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM faqs WHERE id = ?");
            $stmt->execute([$id]);
            $faq = $stmt->fetch();

            if (!$faq) {
                $_SESSION['error'] = 'FAQ not found';
                header('Location: ' . BASE_URL . '/admin/faqs');
                exit;
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error loading FAQ';
            header('Location: ' . BASE_URL . '/admin/faqs');
            exit;
        }

        $this->render('admin/faqs/show', [
            'page_title' => 'FAQ Details',
            'page_description' => 'View FAQ details',
            'faq' => $faq
        ]);
    }

    /**
     * Edit FAQ
     */
    public function edit($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM faqs WHERE id = ?");
            $stmt->execute([$id]);
            $faq = $stmt->fetch();

            if (!$faq) {
                $_SESSION['error'] = 'FAQ not found';
                header('Location: ' . BASE_URL . '/admin/faqs');
                exit;
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error loading FAQ';
            header('Location: ' . BASE_URL . '/admin/faqs');
            exit;
        }

        $this->render('admin/faqs/edit', [
            'page_title' => 'Edit FAQ',
            'page_description' => 'Edit FAQ details',
            'faq' => $faq
        ]);
    }

    /**
     * Update FAQ
     */
    public function update($id)
    {
        $data = [
            'question' => $_POST['question'] ?? '',
            'answer' => $_POST['answer'] ?? '',
            'category' => $_POST['category'] ?? 'General',
            'display_order' => $_POST['display_order'] ?? 0,
            'status' => $_POST['status'] ?? 'active'
        ];

        try {
            [$tw, $tp] = $this->tenantWhere();
            $sql = "UPDATE faqs 
                    SET question = ?, answer = ?, category = ?, display_order = ?, status = ? 
                    WHERE id = ?" . $tw;
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['question'],
                $data['answer'],
                $data['category'],
                $data['display_order'],
                $data['status'],
                $id,
                ...$tp
            ]);

            $_SESSION['success'] = 'FAQ updated successfully!';
            header('Location: ' . BASE_URL . '/admin/faqs');
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error updating FAQ: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/admin/faqs/' . $id . '/edit');
            exit;
        }
    }

    /**
     * Delete FAQ
     */
    public function delete($id)
    {
        try {
            [$tw, $tp] = $this->tenantWhere();
            $stmt = $this->db->prepare("DELETE FROM faqs WHERE id = ?" . $tw);
            $stmt->execute([$id, ...$tp]);

            $_SESSION['success'] = 'FAQ deleted successfully!';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error deleting FAQ: ' . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/admin/faqs');
        exit;
    }
}
