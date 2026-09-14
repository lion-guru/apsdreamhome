<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class ProjectProgressController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, d.name as district_name,
                    (SELECT COUNT(*) FROM plots WHERE colony_id = c.id) as total_plots,
                    (SELECT COUNT(*) FROM plots WHERE colony_id = c.id AND status = 'sold') as sold_plots
                FROM projects p
                LEFT JOIN colonies c ON p.colony_id = c.id
                LEFT JOIN districts d ON c.district_id = d.id
                ORDER BY p.created_at DESC
            ");
            $stmt->execute();
            $projects = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $projects = [];
        }
        return $this->render('admin/projects/progress/index', [
            'page_title' => 'Project Progress',
            'projects' => $projects
        ]);
    }

    public function show($id)
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, d.name as district_name, s.name as state_name,
                    c.name as colony_name
                FROM projects p
                LEFT JOIN colonies c ON p.colony_id = c.id
                LEFT JOIN districts d ON c.district_id = d.id
                LEFT JOIN states s ON d.state_id = s.id
                WHERE p.id = ?
            ");
            $stmt->execute([$id]);
            $project = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $project = null;
        }
        if (!$project) {
            $this->setFlash('error', 'Project not found');
            $this->redirect('/admin/projects/progress');
        }
        $milestones = [];
        if (!empty($project['milestone_json'])) {
            $milestones = json_decode($project['milestone_json'], true) ?? [];
        }
        return $this->render('admin/projects/progress/show', [
            'page_title' => 'Progress: ' . ($project['name'] ?? ''),
            'project' => $project,
            'milestones' => $milestones
        ]);
    }

    public function updateProgress($id)
    {
        $this->requireAdmin();
        $progress_pct = $_POST['progress_pct'] ?? 0;
        $milestone_title = $_POST['milestone_title'] ?? '';
        $milestone_status = $_POST['milestone_status'] ?? 'pending';
        try {
            $stmt = $this->db->prepare("SELECT milestone_json FROM projects WHERE id = ?");
            $stmt->execute([$id]);
            $project = $stmt->fetch(\PDO::FETCH_ASSOC);
            $milestones = [];
            if ($project && !empty($project['milestone_json'])) {
                $milestones = json_decode($project['milestone_json'], true) ?? [];
            }
            if (!empty($milestone_title)) {
                $milestones[] = [
                    'title' => $milestone_title,
                    'status' => $milestone_status,
                    'date' => date('Y-m-d H:i:s')
                ];
            }
            $milestone_json = json_encode($milestones);
            [$tenantSql, $tenantParams] = $this->tenantWhere();
            $updateStmt = $this->db->prepare("UPDATE projects SET progress_pct = ?, milestone_json = ?, progress_last_updated = NOW() WHERE id = ?" . $tenantSql);
            $updateStmt->execute(array_merge([$progress_pct, $milestone_json, $id], $tenantParams));
            $this->setFlash('success', 'Project progress updated successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to update progress: ' . $e->getMessage());
        }
        $this->redirect('/admin/projects/progress/show/' . $id);
    }

    public function budget($id)
    {
        $this->requireAdmin();
        $project_budget = $_POST['project_budget'] ?? 0;
        $amount_spent = $_POST['amount_spent'] ?? 0;
        $project_manager = $_POST['project_manager'] ?? '';
        $site_supervisor = $_POST['site_supervisor'] ?? '';
        $contractor_name = $_POST['contractor_name'] ?? '';
        try {
            [$tenantSql, $tenantParams] = $this->tenantWhere();
            $stmt = $this->db->prepare("UPDATE projects SET project_budget = ?, amount_spent = ?, project_manager = ?, site_supervisor = ?, contractor_name = ? WHERE id = ?" . $tenantSql);
            $stmt->execute(array_merge([$project_budget, $amount_spent, $project_manager, $site_supervisor, $contractor_name, $id], $tenantParams));
            $this->setFlash('success', 'Project budget & team updated successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to update budget: ' . $e->getMessage());
        }
        $this->redirect('/admin/projects/progress/show/' . $id);
    }

    public function colonyProgress()
    {
        $this->requireAdmin();
        $colonyId = isset($_GET['colony_id']) ? (int)$_GET['colony_id'] : 0;
        $colonies = [];
        $milestones = [];
        $selectedColony = null;
        try {
            [$tenantSql, $tenantParams] = $this->tenantWhere();
            $stmt = $this->db->prepare("
                SELECT c.id, c.name, c.slug, c.phase, c.total_plots, c.available_plots, c.status as colony_status,
                    d.name as district_name,
                    (SELECT COUNT(*) FROM colony_milestones m WHERE m.colony_id = c.id) as total_milestones,
                    (SELECT COUNT(*) FROM colony_milestones m WHERE m.colony_id = c.id AND m.status = 'completed') as completed_milestones,
                    (SELECT COALESCE(AVG(m.progress_pct), 0) FROM colony_milestones m WHERE m.colony_id = c.id) as avg_progress
                FROM colonies c
                LEFT JOIN districts d ON c.district_id = d.id
                WHERE 1=1" . $tenantSql . "
                ORDER BY c.name ASC
            ");
            $stmt->execute($tenantParams);
            $colonies = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if (!empty($colonies)) {
                foreach ($colonies as $c) {
                    if ((int)$c['id'] === $colonyId) {
                        $selectedColony = $c;
                        break;
                    }
                }
            }
            if ($colonyId > 0) {
                $stmt = $this->db->prepare("
                    SELECT m.*, c.name as colony_name
                    FROM colony_milestones m
                    LEFT JOIN colonies c ON m.colony_id = c.id
                    WHERE m.colony_id = ?" . $tenantSql . "
                    ORDER BY m.category ASC, m.created_at DESC
                ");
                $stmt->execute(array_merge([$colonyId], $tenantParams));
                $milestones = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }
        } catch (\Exception $e) {
            error_log('ProjectProgressController::colonyProgress error: ' . $e->getMessage());
            $colonies = [];
            $milestones = [];
        }
        return $this->render('admin/construction/colony_progress', [
            'page_title' => 'Colony Construction Progress',
            'colonies' => $colonies,
            'colony_id' => $colonyId,
            'milestones' => $milestones,
            'selected_colony' => $selectedColony
        ]);
    }

    public function updateColonyProgress()
    {
        $this->requireAdmin();
        $milestoneId = isset($_POST['milestone_id']) ? (int)$_POST['milestone_id'] : 0;
        $colonyId = isset($_POST['colony_id']) ? (int)$_POST['colony_id'] : 0;
        $milestoneName = trim($_POST['milestone_name'] ?? '');
        $category = $_POST['category'] ?? 'other';
        $status = $_POST['status'] ?? 'not_started';
        $progressPct = isset($_POST['progress_pct']) ? (float)$_POST['progress_pct'] : 0;
        $startDate = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
        $expectedCompletion = !empty($_POST['expected_completion']) ? $_POST['expected_completion'] : null;
        $actualCompletion = !empty($_POST['actual_completion']) ? $_POST['actual_completion'] : null;
        $contractorName = trim($_POST['contractor_name'] ?? '');
        $contractorContact = trim($_POST['contractor_contact'] ?? '');
        $estimatedCost = isset($_POST['estimated_cost']) ? (float)$_POST['estimated_cost'] : 0;
        $actualCost = isset($_POST['actual_cost']) ? (float)$_POST['actual_cost'] : 0;
        $notes = trim($_POST['notes'] ?? '');
        $remarks = trim($_POST['remarks'] ?? $notes);

        $validCategories = ['roads', 'drainage', 'electricity', 'boundary', 'water', 'landscaping', 'other'];
        $validStatuses = ['not_started', 'in_progress', 'on_hold', 'completed', 'cancelled'];
        if (!in_array($category, $validCategories, true)) {
            $category = 'other';
        }
        if (!in_array($status, $validStatuses, true)) {
            $status = 'not_started';
        }
        if ($progressPct < 0 || $progressPct > 100) {
            $progressPct = 0;
        }

        // Handle site photo upload (optional, additive)
        $sitePhotoPath = null;
        $hasNewPhoto = false;
        if (!empty($_FILES['site_photo']['name']) && ($_FILES['site_photo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $tmp = $_FILES['site_photo']['tmp_name'];
            $orig = $_FILES['site_photo']['name'];
            $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array($ext, $allowed, true) && is_uploaded_file($tmp)) {
                $dir = __DIR__ . '/../../../public/uploads/colony_progress';
                if (!is_dir($dir)) { mkdir($dir, 0755, true); }
                $safe = 'colony_' . $colonyId . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $dest = $dir . '/' . $safe;
                if (move_uploaded_file($tmp, $dest)) {
                    $sitePhotoPath = 'uploads/colony_progress/' . $safe;
                    $hasNewPhoto = true;
                }
            }
        }

        try {
            [$tenantSql, $tenantParams] = $this->tenantWhere();
            if ($milestoneId > 0) {
                // Preserve existing photo if no new upload
                if ($hasNewPhoto) {
                    $stmt = $this->db->prepare("UPDATE colony_milestones SET category = ?, status = ?, progress_pct = ?, start_date = ?, expected_completion = ?, actual_completion = ?, contractor_name = ?, contractor_contact = ?, estimated_cost = ?, actual_cost = ?, notes = ?, remarks = ?, site_photo_path = ?, updated_at = NOW() WHERE id = ?" . $tenantSql);
                    $stmt->execute(array_merge([$category, $status, $progressPct, $startDate, $expectedCompletion, $actualCompletion, $contractorName, $contractorContact, $estimatedCost, $actualCost, $notes, $remarks, $sitePhotoPath, $milestoneId], $tenantParams));
                } else {
                    $stmt = $this->db->prepare("UPDATE colony_milestones SET category = ?, status = ?, progress_pct = ?, start_date = ?, expected_completion = ?, actual_completion = ?, contractor_name = ?, contractor_contact = ?, estimated_cost = ?, actual_cost = ?, notes = ?, remarks = ?, updated_at = NOW() WHERE id = ?" . $tenantSql);
                    $stmt->execute(array_merge([$category, $status, $progressPct, $startDate, $expectedCompletion, $actualCompletion, $contractorName, $contractorContact, $estimatedCost, $actualCost, $notes, $remarks, $milestoneId], $tenantParams));
                }
                $this->setFlash('success', 'Milestone updated successfully' . ($hasNewPhoto ? ' with site photo' : ''));
            } else {
                if ($colonyId <= 0 || empty($milestoneName)) {
                    $this->setFlash('error', 'Colony and milestone name are required to create a milestone');
                    $this->redirect('/admin/construction/colony-progress');
                }
                $tid = (int)$this->tenantId();
                $stmt = $this->db->prepare("INSERT INTO colony_milestones (tenant_id, colony_id, milestone_name, category, status, progress_pct, start_date, expected_completion, actual_completion, contractor_name, contractor_contact, estimated_cost, actual_cost, notes, remarks, site_photo_path, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$tid, $colonyId, $milestoneName, $category, $status, $progressPct, $startDate, $expectedCompletion, $actualCompletion, $contractorName, $contractorContact, $estimatedCost, $actualCost, $notes, $remarks, $sitePhotoPath]);
                $this->setFlash('success', 'Milestone created successfully' . ($hasNewPhoto ? ' with site photo' : ''));
            }
        } catch (\Exception $e) {
            error_log('ProjectProgressController::updateColonyProgress error: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to save milestone: ' . $e->getMessage());
        }
        $this->redirect('/admin/construction/colony-progress');
    }

    public function materialInventory()
    {
        $this->requireAdmin();
        $activeCategory = $_GET['category'] ?? '';
        $activeStatus = $_GET['status'] ?? '';
        $colonies = [];
        $materials = [];
        $usageLogs = [];
        $totalValue = 0;
        try {
            [$tenantSql, $tenantParams] = $this->tenantWhere();
            $colStmt = $this->db->prepare("SELECT id, name FROM colonies WHERE 1=1" . $tenantSql . " ORDER BY name ASC");
            $colStmt->execute($tenantParams);
            $colonies = $colStmt->fetchAll(\PDO::FETCH_ASSOC);
            $sql = "SELECT * FROM material_inventory WHERE 1=1" . $tenantSql;
            $params = $tenantParams;
            if (!empty($activeCategory)) {
                $sql .= " AND material_category = ?";
                $params[] = $activeCategory;
            }
            if (!empty($activeStatus)) {
                $sql .= " AND status = ?";
                $params[] = $activeStatus;
            }
            $sql .= " ORDER BY material_name ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $materials = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($materials as $m) {
                $totalValue += (float)($m['total_value'] ?? 0);
            }
            $stmt = $this->db->prepare("
                SELECT l.*, m.material_name, m.material_category, c.name as colony_name
                FROM material_usage_log l
                LEFT JOIN material_inventory m ON l.material_id = m.id
                LEFT JOIN colonies c ON l.colony_id = c.id
                WHERE 1=1" . $tenantSql . "
                ORDER BY l.usage_date DESC, l.id DESC
                LIMIT 50
            ");
            $stmt->execute($tenantParams);
            $usageLogs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('ProjectProgressController::materialInventory error: ' . $e->getMessage());
            $colonies = [];
            $materials = [];
            $usageLogs = [];
        }
        return $this->render('admin/construction/material_inventory', [
            'page_title' => 'Material Inventory & Usage',
            'colonies' => $colonies,
            'materials' => $materials,
            'usage_logs' => $usageLogs,
            'total_value' => $totalValue,
            'active_category' => $activeCategory,
            'active_status' => $activeStatus
        ]);
    }

    public function logMaterialUsage()
    {
        $this->requireAdmin();
        $materialId = isset($_POST['material_id']) ? (int)$_POST['material_id'] : 0;
        $colonyId = isset($_POST['colony_id']) ? (int)$_POST['colony_id'] : 0;
        $plotId = isset($_POST['plot_id']) ? (int)$_POST['plot_id'] : 0;
        $quantity = isset($_POST['quantity']) ? (float)$_POST['quantity'] : 0;
        $unit = trim($_POST['unit'] ?? 'qty');
        $purpose = trim($_POST['purpose'] ?? '');
        $usedBy = trim($_POST['used_by'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $usageDate = !empty($_POST['usage_date']) ? $_POST['usage_date'] : date('Y-m-d');
        try {
            [$tenantSql, $tenantParams] = $this->tenantWhere();
            if ($materialId <= 0 || $quantity <= 0) {
                $this->setFlash('error', 'A material and quantity greater than zero are required');
                $this->redirect('/admin/construction/material-inventory');
            }
            $stmt = $this->db->prepare("SELECT * FROM material_inventory WHERE id = ?" . $tenantSql);
            $stmt->execute(array_merge([$materialId], $tenantParams));
            $material = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$material) {
                $this->setFlash('error', 'Material not found');
                $this->redirect('/admin/construction/material-inventory');
            }
            $currentStock = (float)$material['current_stock'];
            if ($quantity > $currentStock) {
                $this->setFlash('error', 'Insufficient stock. Available: ' . $currentStock . ' ' . $unit);
                $this->redirect('/admin/construction/material-inventory');
            }
            $this->db->beginTransaction();
            $tid = (int)$this->tenantId();
            $stmt = $this->db->prepare("INSERT INTO material_usage_log (tenant_id, material_id, colony_id, plot_id, usage_date, quantity, unit, purpose, used_by, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$tid, $materialId, $colonyId > 0 ? $colonyId : null, $plotId > 0 ? $plotId : null, $usageDate, $quantity, $unit, $purpose, $usedBy, $notes]);
            $newStock = $currentStock - $quantity;
            $unitCost = (float)$material['unit_cost'];
            $totalValue = round($newStock * $unitCost, 2);
            if ($newStock <= 0) {
                $newStatus = 'out_of_stock';
            } elseif ($material['minimum_stock'] !== null && $newStock <= (float)$material['minimum_stock']) {
                $newStatus = 'low_stock';
            } else {
                $newStatus = 'in_stock';
            }
            $stmt = $this->db->prepare("UPDATE material_inventory SET current_stock = ?, total_value = ?, status = ?, updated_at = NOW() WHERE id = ?" . $tenantSql);
            $stmt->execute(array_merge([$newStock, $totalValue, $newStatus, $materialId], $tenantParams));
            $this->db->commit();
            $this->setFlash('success', 'Material usage logged successfully');
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('ProjectProgressController::logMaterialUsage error: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to log usage: ' . $e->getMessage());
        }
        $this->redirect('/admin/construction/material-inventory');
    }
}
