<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;

class GalleryController extends BaseController
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
    }

    /**
     * Gallery listing
     */
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['admin_id'])) {
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }

        try {
            $images = $this->db->fetchAll("SELECT * FROM gallery_images ORDER BY created_at DESC") ?? [];
        } catch (\Exception $e) {
            $images = [];
        }

        $data = [
            'page_title' => 'Gallery Management',
            'page_description' => 'Manage photo gallery',
            'images' => $images,
            'success' => $_SESSION['success'] ?? null,
            'error' => $_SESSION['error'] ?? null
        ];
        unset($_SESSION['success'], $_SESSION['error']);

        $this->render('admin/gallery/index', $data);
    }

    /**
     * Create gallery image form
     */
    public function create()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['admin_id'])) {
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }

        $data = [
            'page_title' => 'Add Gallery Image',
            'page_description' => 'Upload a new image to the gallery'
        ];

        $this->render('admin/gallery/create', $data);
    }

    /**
     * Store gallery image
     */
    public function store()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['admin_id'])) {
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }

        try {
            $category = $_POST['category'] ?? 'general';
            $caption = $_POST['caption'] ?? '';
            $status = $_POST['status'] ?? 'active';

            // Handle file upload
            $imagePath = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = dirname(__DIR__, 3) . '/assets/images/gallery/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = 'gallery_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
                $destination = $uploadDir . $filename;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                    $imagePath = 'assets/images/gallery/' . $filename;
                }
            }

            $this->db->insert('gallery_images', [
                'category' => $category,
                'image_path' => $imagePath,
                'caption' => $caption,
                'status' => $status,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $_SESSION['success'] = 'Image added to gallery successfully!';
            header('Location: ' . BASE_URL . '/admin/gallery');
            exit;

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error adding image: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/admin/gallery/create');
            exit;
        }
    }

    /**
     * Edit gallery image
     */
    public function edit($id = null)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['admin_id'])) {
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }

        try {
            $image = $this->db->fetch("SELECT * FROM gallery_images WHERE id = ?", [$id]);
            if (!$image) {
                $_SESSION['error'] = 'Image not found';
                header('Location: ' . BASE_URL . '/admin/gallery');
                exit;
            }

            $data = [
                'page_title' => 'Edit Gallery Image',
                'page_description' => 'Update gallery image details',
                'image' => $image
            ];

            $this->render('admin/gallery/edit', $data);

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error loading image: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/admin/gallery');
            exit;
        }
    }

    /**
     * Update gallery image
     */
    public function update($id = null)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['admin_id'])) {
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }

        try {
            $updateData = [
                'category' => $_POST['category'] ?? 'general',
                'caption' => $_POST['caption'] ?? '',
                'status' => $_POST['status'] ?? 'active',
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Handle file upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = dirname(__DIR__, 3) . '/assets/images/gallery/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = 'gallery_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
                $destination = $uploadDir . $filename;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                    $updateData['image_path'] = 'assets/images/gallery/' . $filename;
                }
            }

            $this->db->update('gallery_images', $updateData, ['id' => $id]);

            $_SESSION['success'] = 'Image updated successfully!';
            header('Location: ' . BASE_URL . '/admin/gallery');
            exit;

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error updating image: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/admin/gallery/' . $id . '/edit');
            exit;
        }
    }

    /**
     * Delete gallery image
     */
    public function destroy($id = null)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['admin_id'])) {
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }

        try {
            // Get image path to delete file
            $image = $this->db->fetch("SELECT image_path FROM gallery_images WHERE id = ?", [$id]);
            if ($image && !empty($image['image_path'])) {
                $filePath = dirname(__DIR__, 3) . '/' . $image['image_path'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $this->db->delete('gallery_images', ['id' => $id]);

            $_SESSION['success'] = 'Image deleted successfully!';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error deleting image: ' . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/admin/gallery');
        exit;
    }
}
