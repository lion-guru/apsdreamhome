<?php

namespace App\Http\Controllers\Admin;

class AdminPackageController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->requireAdmin();
        $stmt = $this->db->query("SELECT * FROM premium_packages ORDER BY priority_order ASC, price ASC");
        $packages = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $this->render('admin/premium-packages/index', [
            'page_title' => 'Premium Packages',
            'packages' => $packages,
        ]);
    }

    public function create()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfOrFail();
            $name = trim($_POST['name'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $price = (float)($_POST['price'] ?? 0);
            $durationDays = (int)($_POST['duration_days'] ?? 30);
            $badgeLabel = trim($_POST['badge_label'] ?? 'Featured');
            $badgeColor = trim($_POST['badge_color'] ?? '#ff6b35');
            $priority = (int)($_POST['priority_order'] ?? 0);
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            $features = [];
            for ($i = 1; $i <= 10; $i++) {
                $f = trim($_POST['feature_' . $i] ?? '');
                if ($f) $features[] = $f;
            }
            if (empty($name) || empty($slug)) {
                $this->setFlash('error', 'Name and slug are required');
                $this->redirect('/admin/premium-packages/create');
            }
            $this->db->prepare("INSERT INTO premium_packages (name, slug, description, price, duration_days, features, badge_label, badge_color, priority_order, is_active) VALUES (?,?,?,?,?,?,?,?,?,?)")->execute([
                $name, $slug, trim($_POST['description'] ?? ''), $price, $durationDays,
                json_encode($features), $badgeLabel, $badgeColor, $priority, $isActive
            ]);
            $this->setFlash('success', 'Package created');
            $this->redirect('/admin/premium-packages');
        }
        $this->render('admin/premium-packages/form', [
            'page_title' => 'Create Package',
            'package' => [],
        ]);
    }

    public function edit($id)
    {
        $this->requireAdmin();
        $stmt = $this->db->prepare("SELECT * FROM premium_packages WHERE id = ?");
        $stmt->execute([$id]);
        $pkg = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$pkg) {
            $this->setFlash('error', 'Package not found');
            $this->redirect('/admin/premium-packages');
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfOrFail();
            $features = [];
            for ($i = 1; $i <= 10; $i++) {
                $f = trim($_POST['feature_' . $i] ?? '');
                if ($f) $features[] = $f;
            }
            $this->db->prepare("UPDATE premium_packages SET name=?, slug=?, description=?, price=?, duration_days=?, features=?, badge_label=?, badge_color=?, priority_order=?, is_active=? WHERE id=?")->execute([
                trim($_POST['name'] ?? ''),
                trim($_POST['slug'] ?? ''),
                trim($_POST['description'] ?? ''),
                (float)($_POST['price'] ?? 0),
                (int)($_POST['duration_days'] ?? 30),
                json_encode($features),
                trim($_POST['badge_label'] ?? 'Featured'),
                trim($_POST['badge_color'] ?? '#ff6b35'),
                (int)($_POST['priority_order'] ?? 0),
                isset($_POST['is_active']) ? 1 : 0,
                $id
            ]);
            $this->setFlash('success', 'Package updated');
            $this->redirect('/admin/premium-packages');
        }
        $pkg['features'] = json_decode($pkg['features'] ?? '[]', true);
        $this->render('admin/premium-packages/form', [
            'page_title' => 'Edit Package',
            'package' => $pkg,
        ]);
    }

    public function delete($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $this->db->prepare("DELETE FROM premium_packages WHERE id = ?")->execute([$id]);
        $this->setFlash('success', 'Package deleted');
        $this->redirect('/admin/premium-packages');
    }
}
