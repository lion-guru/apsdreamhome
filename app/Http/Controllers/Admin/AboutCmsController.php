<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class AboutCmsController extends AdminController
{
    use \App\Traits\TenantAwareTrait;
    public function index()
    {
        $content = [];
        try {
            $stmt = $this->db->prepare("SELECT content_key, content_value, content_group FROM site_content WHERE section = 'about' ORDER BY content_group, sort_order");
            $stmt->execute();
            foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $content[$row['content_group']][$row['content_key']] = $row['content_value'];
            }
        } catch (\Exception $e) { error_log("AboutCmsController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        $data = [
            'page_title' => 'About Page CMS',
            'active_page' => 'about_cms',
            'content' => $content,
            'success' => $_SESSION['success'] ?? null,
            'error' => $_SESSION['error'] ?? null
        ];
        unset($_SESSION['success'], $_SESSION['error']);
        $this->render('admin/about-cms/index', $data);
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/about-cms');
            return;
        }

        try {
            $post = $_POST;
            $updated = 0;

            [$tenantWhere, $tenantParams] = $this->tenantWhere();
            $stmt = $this->db->prepare("UPDATE site_content SET content_value = ? WHERE section = 'about' AND content_key = ?" . $tenantWhere);
            $exists = $this->db->prepare("SELECT COUNT(*) as cnt FROM site_content WHERE section = 'about' AND content_key = ?");
            $insert = $this->db->prepare("INSERT INTO site_content (section, content_key, content_value, content_group, sort_order) VALUES ('about', ?, ?, ?, ?)");

            foreach ($post as $key => $value) {
                if ($key === 'csrf_token') continue;

                $group = 'general';
                if (strpos($key, 'leader_') === 0) {
                    $group = 'leader_' . explode('_', $key)[1];
                } elseif (strpos($key, 'stat_') === 0) {
                    $group = 'stats';
                } elseif (strpos($key, 'reg_') === 0) {
                    $group = 'registration';
                } elseif (strpos($key, 'vision_') === 0 || strpos($key, 'mission_') === 0) {
                    $group = 'vision_mission';
                }

                $exists->execute([$key]);
                if ($exists->fetch()['cnt'] > 0) {
                    $stmt->execute([$value, $key, ...$tenantParams]);
                } else {
                    $insert->execute([$key, $value, $group, 0]);
                }
                $updated++;
            }

            $_SESSION['success'] = "Updated $updated fields successfully!";
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
        $this->redirect('/admin/about-cms');
    }

    public function uploadPhoto()
    {
        $key = $_POST['content_key'] ?? '';
        $group = $_POST['content_group'] ?? 'leader';

        if (empty($key) || !isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Invalid upload';
            $this->redirect('/admin/about-cms');
            return;
        }

        try {
            $uploadDir = dirname(__DIR__, 3) . '/assets/images/team/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $filename = $key . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename)) {
                $path = 'assets/images/team/' . $filename;
                [$tw, $tp] = $this->tenantWhere();
                $this->db->prepare("UPDATE site_content SET content_value = ? WHERE section = 'about' AND content_key = ?" . $tw)->execute([$path, $key, ...$tp]);
                $_SESSION['success'] = 'Photo uploaded!';
            } else {
                $_SESSION['error'] = 'Upload failed';
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
        $this->redirect('/admin/about-cms');
    }
}
