<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class MarketingController extends AdminController
{
    use \App\Traits\TenantAwareTrait;
    public function strategies()
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->query("SELECT * FROM marketing_strategies ORDER BY created_at DESC");
            $strategies = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $strategies = [];
        }
        $total = count($strategies);
        $active = count(array_filter($strategies, fn($s) => $s['active'] ?? 0));
        $inactive = $total - $active;
        return $this->render('admin/marketing/strategies', [
            'page_title' => 'Marketing Strategies',
            'strategies' => $strategies,
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive
        ]);
    }

    public function createStrategy()
    {
        $this->requireAdmin();
        return $this->render('admin/marketing/create-strategy', [
            'page_title' => 'Create Strategy'
        ]);
    }

    public function storeStrategy()
    {
        $this->requireAdmin();
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $image_url = $_POST['image_url'] ?? '';
        $active = isset($_POST['active']) ? 1 : 0;
        try {
            $stmt = $this->db->prepare("INSERT INTO marketing_strategies (title, description, image_url, active, tenant_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([$title, $description, $image_url, $active, $this->tenantId()]);
            $this->setFlash('success', 'Strategy created successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to create strategy: ' . $e->getMessage());
        }
        $this->redirect('/admin/marketing/strategies');
    }

    public function editStrategy($id)
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare("SELECT * FROM marketing_strategies WHERE id = ?");
            $stmt->execute([$id]);
            $strategy = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $strategy = null;
        }
        if (!$strategy) {
            $this->setFlash('error', 'Strategy not found');
            $this->redirect('/admin/marketing/strategies');
        }
        return $this->render('admin/marketing/edit-strategy', [
            'page_title' => 'Edit Strategy: ' . ($strategy['title'] ?? ''),
            'strategy' => $strategy
        ]);
    }

    public function updateStrategy($id)
    {
        $this->requireAdmin();
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $image_url = $_POST['image_url'] ?? '';
        $active = isset($_POST['active']) ? 1 : 0;
        try {
            $stmt = $this->db->prepare("UPDATE marketing_strategies SET title = ?, description = ?, image_url = ?, active = ?, updated_at = NOW() WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$title, $description, $image_url, $active, $id, $this->tenantId()]);
            $this->setFlash('success', 'Strategy updated successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to update strategy: ' . $e->getMessage());
        }
        $this->redirect('/admin/marketing/strategies');
    }

    public function toggleStrategy($id)
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare("SELECT active FROM marketing_strategies WHERE id = ?");
            $stmt->execute([$id]);
            $strategy = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($strategy) {
                $newActive = $strategy['active'] ? 0 : 1;
                $updateStmt = $this->db->prepare("UPDATE marketing_strategies SET active = ?, updated_at = NOW() WHERE id = ? AND tenant_id = ?");
                $updateStmt->execute([$newActive, $id, $this->tenantId()]);
                $this->setFlash('success', 'Strategy status toggled successfully');
            } else {
                $this->setFlash('error', 'Strategy not found');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to toggle strategy: ' . $e->getMessage());
        }
        $this->redirect('/admin/marketing/strategies');
    }

    public function marketplace()
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->query("SELECT * FROM marketplace_apps ORDER BY created_at DESC");
            $apps = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $apps = [];
        }
        return $this->render('admin/marketing/marketplace', [
            'page_title' => 'Marketplace Apps',
            'apps' => $apps
        ]);
    }

    public function storeMarketplace()
    {
        $this->requireAdmin();
        $app_name = $_POST['app_name'] ?? '';
        $provider = $_POST['provider'] ?? '';
        $app_url = $_POST['app_url'] ?? '';
        try {
            $stmt = $this->db->prepare("INSERT INTO marketplace_apps (app_name, provider, app_url, tenant_id, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$app_name, $provider, $app_url, $this->tenantId()]);
            $this->setFlash('success', 'Marketplace app added successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to add marketplace app: ' . $e->getMessage());
        }
        $this->redirect('/admin/marketing/marketplace');
    }
}
