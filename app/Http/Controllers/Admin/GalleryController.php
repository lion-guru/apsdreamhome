<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class GalleryController extends AdminController
{
    use \App\Traits\TenantAwareTrait;
    public function index()
    {
        $category = $_GET['category'] ?? '';
        $sql = "SELECT * FROM gallery WHERE 1=1";
        $params = [];
        if ($category) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }
        $sql .= " ORDER BY sort_order ASC, created_at DESC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $images = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $images = [];
        }

        $categories = [];
        try {
            $categories = $this->db->fetchAll("SELECT DISTINCT category, COUNT(*) as cnt FROM gallery GROUP BY category ORDER BY category");
        } catch (\Exception $e) { error_log("GalleryController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        $data = [
            'page_title' => 'Gallery Management',
            'active_page' => 'gallery',
            'images' => $images,
            'categories' => $categories,
            'current_category' => $category,
            'success' => $_SESSION['success'] ?? null,
            'error' => $_SESSION['error'] ?? null
        ];
        unset($_SESSION['success'], $_SESSION['error']);
        $this->render('admin/gallery/index', $data);
    }

    public function create()
    {
        $data = [
            'page_title' => 'Add Gallery Image',
            'active_page' => 'gallery',
            'categories' => ['residential', 'commercial', 'projects', 'team', 'events', 'general']
        ];
        $this->render('admin/gallery/create', $data);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/gallery');
            return;
        }
        try {
            $imagePath = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $validation = \UploadValidator::validate($_FILES['image'], ['types' => 'images', 'max_size' => 10]);
                if ($validation['valid']) {
                    $uploadDir = dirname(__DIR__, 3) . '/assets/images/gallery/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $safeName = \UploadValidator::safeFilename($_FILES['image']['name']);
                    $filename = 'gallery_' . $safeName;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                        $imagePath = 'assets/images/gallery/' . $filename;
                    }
                } else {
                    $_SESSION['error'] = 'Image upload failed: ' . $validation['error'];
                    header('Location: ' . BASE_URL . '/admin/gallery/create');
                    return;
                }
            }

            $stmt = $this->db->prepare("INSERT INTO gallery (title, category, caption, description, image_path, status, sort_order, tenant_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([
                $_POST['title'] ?? '',
                $_POST['category'] ?? 'general',
                $_POST['caption'] ?? '',
                $_POST['description'] ?? '',
                $imagePath,
                $_POST['status'] ?? 'active',
                (int)($_POST['sort_order'] ?? 0),
                $this->tenantId()
            ]);

            $_SESSION['success'] = 'Image added to gallery!';
            $this->redirect('/admin/gallery');
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
            $this->redirect('/admin/gallery/create');
        }
    }

    public function edit($id = null)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM gallery WHERE id = ?");
            $stmt->execute([$id]);
            $image = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$image) {
                $_SESSION['error'] = 'Image not found';
                $this->redirect('/admin/gallery');
                return;
            }
            $data = [
                'page_title' => 'Edit Gallery Image',
                'active_page' => 'gallery',
                'image' => $image,
                'categories' => ['residential', 'commercial', 'projects', 'team', 'events', 'general']
            ];
            $this->render('admin/gallery/edit', $data);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
            $this->redirect('/admin/gallery');
        }
    }

    public function update($id = null)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/gallery');
            return;
        }
        try {
            $updateData = [
                'title' => $_POST['title'] ?? '',
                'category' => $_POST['category'] ?? 'general',
                'caption' => $_POST['caption'] ?? '',
                'description' => $_POST['description'] ?? '',
                'status' => $_POST['status'] ?? 'active',
                'sort_order' => (int)($_POST['sort_order'] ?? 0),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (in_array($_FILES['image']['type'], $allowed) && $_FILES['image']['size'] <= 10 * 1024 * 1024) {
                    $uploadDir = dirname(__DIR__, 3) . '/assets/images/gallery/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    $filename = 'gallery_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                        $updateData['image_path'] = 'assets/images/gallery/' . $filename;
                    }
                }
            }

            $fields = [];
            $values = [];
            foreach ($updateData as $k => $v) {
                $fields[] = "$k = ?";
                $values[] = $v;
            }
            [$tw, $tp] = $this->tenantWhere();
            $values[] = $id;
            $values = array_merge($values, $tp);
            $this->db->prepare("UPDATE gallery SET " . implode(', ', $fields) . " WHERE id = ?" . $tw)->execute($values);

            $_SESSION['success'] = 'Image updated!';
            $this->redirect('/admin/gallery');
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
            $this->redirect("/admin/gallery/$id/edit");
        }
    }

    public function destroy($id = null)
    {
        try {
            $stmt = $this->db->prepare("SELECT image_path FROM gallery WHERE id = ?");
            $stmt->execute([$id]);
            $image = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($image && !empty($image['image_path'])) {
                $filePath = dirname(__DIR__, 3) . '/' . $image['image_path'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            [$tw, $tp] = $this->tenantWhere();
            $this->db->prepare("DELETE FROM gallery WHERE id = ?" . $tw)->execute([$id, ...$tp]);
            $_SESSION['success'] = 'Image deleted!';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
        $this->redirect('/admin/gallery');
    }
}
