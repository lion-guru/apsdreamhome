<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;

/**
 * Testimonial Controller
 * Handles testimonial management for admin panel
 */
class TestimonialController extends AdminController
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Show all testimonials
     */
    public function index()
    {
        try {
            $stmt = $this->db->query("SELECT * FROM testimonials ORDER BY created_at DESC");
            $testimonials = $stmt->fetchAll();
        } catch (\Exception $e) {
            $testimonials = [];
        }

        $this->render('admin/testimonials/index', [
            'page_title' => 'Testimonials',
            'page_description' => 'Manage customer testimonials',
            'testimonials' => $testimonials
        ]);
    }

    /**
     * Show create testimonial form
     */
    public function create()
    {
        $this->render('admin/testimonials/create', [
            'page_title' => 'Add New Testimonial',
            'page_description' => 'Add a new customer testimonial'
        ]);
    }

    /**
     * Store new testimonial
     */
    public function store()
    {
        $data = [
            'customer_name' => $_POST['customer_name'] ?? '',
            'customer_email' => $_POST['customer_email'] ?? '',
            'customer_phone' => $_POST['customer_phone'] ?? '',
            'rating' => $_POST['rating'] ?? 5,
            'content' => $_POST['content'] ?? '',
            'status' => $_POST['status'] ?? 'pending',
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s')
        ];

        try {
            $sql = "INSERT INTO testimonials (tenant_id, customer_name, customer_email, customer_phone, rating, content, status, is_featured, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $this->tenantId(),
                $data['customer_name'],
                $data['customer_email'],
                $data['customer_phone'],
                $data['rating'],
                $data['content'],
                $data['status'],
                $data['is_featured'],
                $data['created_at']
            ]);

            $_SESSION['success'] = 'Testimonial added successfully!';
            header('Location: ' . BASE_URL . '/admin/testimonials');
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error adding testimonial: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/admin/testimonials/create');
            exit;
        }
    }

    /**
     * Show testimonial details
     */
    public function show($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM testimonials WHERE id = ?");
            $stmt->execute([$id]);
            $testimonial = $stmt->fetch();

            if (!$testimonial) {
                $_SESSION['error'] = 'Testimonial not found';
                header('Location: ' . BASE_URL . '/admin/testimonials');
                exit;
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error loading testimonial';
            header('Location: ' . BASE_URL . '/admin/testimonials');
            exit;
        }

        $this->render('admin/testimonials/show', [
            'page_title' => 'Testimonial Details',
            'page_description' => 'View testimonial details',
            'testimonial' => $testimonial
        ]);
    }

    /**
     * Edit testimonial
     */
    public function edit($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM testimonials WHERE id = ?");
            $stmt->execute([$id]);
            $testimonial = $stmt->fetch();

            if (!$testimonial) {
                $_SESSION['error'] = 'Testimonial not found';
                header('Location: ' . BASE_URL . '/admin/testimonials');
                exit;
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error loading testimonial';
            header('Location: ' . BASE_URL . '/admin/testimonials');
            exit;
        }

        $this->render('admin/testimonials/edit', [
            'page_title' => 'Edit Testimonial',
            'page_description' => 'Edit testimonial details',
            'testimonial' => $testimonial
        ]);
    }

    /**
     * Update testimonial
     */
    public function update($id)
    {
        $data = [
            'customer_name' => $_POST['customer_name'] ?? '',
            'customer_email' => $_POST['customer_email'] ?? '',
            'customer_phone' => $_POST['customer_phone'] ?? '',
            'rating' => $_POST['rating'] ?? 5,
            'content' => $_POST['content'] ?? '',
            'status' => $_POST['status'] ?? 'pending',
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0
        ];

        try {
            [$tenantSql, $tenantParams] = $this->tenantWhere();
            $sql = "UPDATE testimonials 
                    SET customer_name = ?, customer_email = ?, customer_phone = ?, 
                        rating = ?, content = ?, status = ?, is_featured = ? 
                    WHERE id = ?" . $tenantSql;
            $stmt = $this->db->prepare($sql);
            $stmt->execute(array_merge([
                $data['customer_name'],
                $data['customer_email'],
                $data['customer_phone'],
                $data['rating'],
                $data['content'],
                $data['status'],
                $data['is_featured'],
                $id
            ], $tenantParams));

            $_SESSION['success'] = 'Testimonial updated successfully!';
            header('Location: ' . BASE_URL . '/admin/testimonials');
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error updating testimonial: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/admin/testimonials/' . $id . '/edit');
            exit;
        }
    }

    /**
     * Delete testimonial
     */
    public function delete($id)
    {
        try {
            [$tenantSql, $tenantParams] = $this->tenantWhere();
            $stmt = $this->db->prepare("DELETE FROM testimonials WHERE id = ?" . $tenantSql);
            $stmt->execute(array_merge([$id], $tenantParams));

            $_SESSION['success'] = 'Testimonial deleted successfully!';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error deleting testimonial: ' . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/admin/testimonials');
        exit;
    }

    public function manage() { return $this->render('admin/testimonials/index'); }
}
