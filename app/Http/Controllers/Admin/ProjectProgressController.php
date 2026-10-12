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
        $progressPct = ($_POST['progress_pct'] ?? '') === '' ? null : (isset($_POST['progress_pct']) ? (float)$_POST['progress_pct'] : 0);
        $startDate = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
        $expectedCompletion = !empty($_POST['expected_completion']) ? $_POST['expected_completion'] : null;
        $actualCompletion = !empty($_POST['actual_completion']) ? $_POST['actual_completion'] : null;
        $contractorName = trim($_POST['contractor_name'] ?? '');
        $contractorContact = trim($_POST['contractor_contact'] ?? '');
        $estimatedCost = ($_POST['estimated_cost'] ?? '') === '' ? null : (isset($_POST['estimated_cost']) ? (float)$_POST['estimated_cost'] : 0);
        $actualCost = ($_POST['actual_cost'] ?? '') === '' ? null : (isset($_POST['actual_cost']) ? (float)$_POST['actual_cost'] : 0);
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

        // Handle site photo upload (optional, additive) — 5MB limit, tenant-safe path
        $sitePhotoPath = null;
        $hasNewPhoto = false;
        if (!empty($_FILES['site_photo']['name']) && ($_FILES['site_photo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $tmp = $_FILES['site_photo']['tmp_name'];
            $orig = $_FILES['site_photo']['name'];
            $size = (int)($_FILES['site_photo']['size'] ?? 0);
            if ($size > 5 * 1024 * 1024) {
                $this->setFlash('error', 'Site photo exceeds 5MB limit.');
                $this->redirect('/admin/construction/colony-progress?colony_id=' . $colonyId);
            }
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
            } else if (!in_array($ext, $allowed, true)) {
                $this->setFlash('error', 'Invalid photo format — only jpg, png, webp allowed.');
                $this->redirect('/admin/construction/colony-progress?colony_id=' . $colonyId);
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

    /* =========================================================
     *  Civil work log (JCB hours, soling, damar, poles …) —
     *  machine/site work that has no stock to decrement.
     * ========================================================= */

    public function civilWorkLog()
    {
        $this->requireAdmin();
        $logs = $colonies = [];
        $totalMonth = 0;
        try {
            [$tenantSql, $tenantParams] = $this->tenantWhere();
            $cStmt = $this->db->prepare("SELECT id, name FROM colonies WHERE 1=1" . $tenantSql . " ORDER BY name ASC");
            $cStmt->execute($tenantParams);
            $colonies = $cStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $lStmt = $this->db->prepare(
                "SELECT l.*, c.name AS colony_name FROM civil_work_log l
                 LEFT JOIN colonies c ON c.id = l.colony_id
                 WHERE 1=1" . $tenantSql . "
                 ORDER BY l.work_date DESC, l.id DESC LIMIT 100"
            );
            $lStmt->execute($tenantParams);
            $logs = $lStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $mStmt = $this->db->prepare(
                "SELECT COALESCE(SUM(amount),0) AS t FROM civil_work_log
                 WHERE MONTH(work_date) = MONTH(CURDATE()) AND YEAR(work_date) = YEAR(CURDATE())" . $tenantSql
            );
            $mStmt->execute($tenantParams);
            $totalMonth = (float)($mStmt->fetch(\PDO::FETCH_ASSOC)['t'] ?? 0);
        } catch (\Exception $e) { error_log('ProjectProgressController::civilWorkLog: ' . $e->getMessage()); }
        return $this->render('admin/construction/civil_work_log', [
            'page_title' => 'Civil Work Log',
            'colonies' => $colonies,
            'logs' => $logs,
            'total_month' => $totalMonth,
            'work_types' => $this->civilWorkTypes(),
        ]);
    }

    public function civilWorkStore()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        try {
            $types = array_keys($this->civilWorkTypes());
            $type = in_array($_POST['work_type'] ?? '', $types, true) ? $_POST['work_type'] : 'other';
            $qty = max(0, (float)($_POST['quantity'] ?? 0));
            $rate = max(0, (float)($_POST['rate'] ?? 0));
            $amount = $qty * $rate;
            if ($amount <= 0) {
                $this->setFlash('error', 'Quantity × rate must be greater than zero.');
                return $this->redirect('/admin/construction/civil-work');
            }
            [$tenantSql, $tenantParams] = $this->tenantWhere();
            $tid = (int)$this->tenantId();
            $st = $this->db->prepare(
                "INSERT INTO civil_work_log (tenant_id, colony_id, work_date, work_type, description, quantity, unit, rate, amount, vendor_name, recorded_by, notes)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $st->execute([
                $tid,
                (int)($_POST['colony_id'] ?? 0) ?: null,
                $_POST['work_date'] ?? date('Y-m-d'),
                $type,
                trim((string)($_POST['description'] ?? '')),
                $qty,
                trim((string)($_POST['unit'] ?? 'qty')),
                $rate, $amount,
                trim((string)($_POST['vendor_name'] ?? '')),
                $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null,
                trim((string)($_POST['notes'] ?? '')),
            ]);
            $this->setFlash('success', 'Civil work logged: ₹' . number_format($amount, 0));
        } catch (\Exception $e) {
            error_log('ProjectProgressController::civilWorkStore: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to log civil work.');
        }
        return $this->redirect('/admin/construction/civil-work');
    }

    /* =========================================================
     *  Daily site labor muster (mason/helper attendance + wages)
     * ========================================================= */

    public function musterRoll()
    {
        $this->requireAdmin();
        $logs = $colonies = [];
        $dayTotal = 0.0;
        $dayHeadcount = 0;
        try {
            [$tenantSql, $tenantParams] = $this->tenantWhere();
            $cStmt = $this->db->prepare("SELECT id, name FROM colonies WHERE 1=1" . $tenantSql . " ORDER BY name ASC");
            $cStmt->execute($tenantParams);
            $colonies = $cStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $workDate = trim((string)($_GET['work_date'] ?? date('Y-m-d')));
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $workDate)) $workDate = date('Y-m-d');
            $colonyId = (int)($_GET['colony_id'] ?? 0);
            $where = "l.work_date = ?";
            $params = [$workDate];
            if ($colonyId > 0) {
                $where .= " AND l.colony_id = ?";
                $params[] = $colonyId;
            }
            $lStmt = $this->db->prepare(
                "SELECT l.*, c.name AS colony_name FROM site_muster_log l
                 LEFT JOIN colonies c ON c.id = l.colony_id
                 WHERE $where" . $tenantSql . "
                 ORDER BY l.colony_id, l.worker_role, l.worker_name"
            );
            $lStmt->execute(array_merge($params, $tenantParams));
            $logs = $lStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            foreach ($logs as $l) {
                if (!empty($l['is_present'])) {
                    $dayHeadcount++;
                    $dayTotal += (float)($l['daily_wage'] ?? 0);
                }
            }
        } catch (\Exception $e) {
            error_log('ProjectProgressController::musterRoll: ' . $e->getMessage());
            $workDate = date('Y-m-d');
            $colonyId = 0;
        }
        return $this->render('admin/construction/muster_roll', [
            'page_title' => 'Daily Site Muster Roll',
            'colonies' => $colonies,
            'logs' => $logs,
            'work_date' => $workDate ?? date('Y-m-d'),
            'colony_id' => $colonyId ?? 0,
            'day_total' => $dayTotal,
            'day_headcount' => $dayHeadcount,
            'roles' => $this->musterRoles(),
        ]);
    }

    public function musterStore()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        try {
            $roles = array_keys($this->musterRoles());
            $name = trim((string)($_POST['worker_name'] ?? ''));
            if ($name === '') {
                $this->setFlash('error', 'Worker name is required.');
                return $this->redirect('/admin/construction/muster-roll');
            }
            $role = in_array($_POST['worker_role'] ?? '', $roles, true) ? $_POST['worker_role'] : 'helper';
            $wage = max(0, (float)($_POST['daily_wage'] ?? 0));
            $workDate = trim((string)($_POST['work_date'] ?? date('Y-m-d')));
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $workDate)) $workDate = date('Y-m-d');
            $tid = (int)$this->tenantId();
            $st = $this->db->prepare(
                "INSERT INTO site_muster_log (tenant_id, colony_id, work_date, worker_name, worker_role, daily_wage, is_present, work_done, recorded_by, notes)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $st->execute([
                $tid,
                (int)($_POST['colony_id'] ?? 0) ?: null,
                $workDate,
                mb_substr($name, 0, 150),
                $role,
                $wage,
                isset($_POST['is_present']) ? 1 : 0,
                mb_substr(trim((string)($_POST['work_done'] ?? '')), 0, 255) ?: null,
                $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null,
                mb_substr(trim((string)($_POST['notes'] ?? '')), 0, 500) ?: null,
            ]);
            $this->setFlash('success', 'Muster entry added: ' . $name . ' (₹' . number_format($wage, 0) . ')');
        } catch (\Exception $e) {
            error_log('ProjectProgressController::musterStore: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to save muster entry.');
        }
        $back = '/admin/construction/muster-roll?work_date=' . urlencode($_POST['work_date'] ?? date('Y-m-d'));
        if (!empty($_POST['colony_id'])) $back .= '&colony_id=' . (int)$_POST['colony_id'];
        return $this->redirect($back);
    }

    public function musterDelete($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        try {
            [$tenantSql, $tenantParams] = $this->tenantWhere();
            $st = $this->db->prepare("DELETE FROM site_muster_log WHERE id = ?" . $tenantSql);
            $params = array_merge([(int)$id], $tenantParams);
            $st->execute($params);
            $this->setFlash('success', $st->rowCount() > 0 ? 'Muster entry deleted.' : 'Entry not found.');
        } catch (\Exception $e) {
            error_log('ProjectProgressController::musterDelete: ' . $e->getMessage());
            $this->setFlash('error', 'Delete failed.');
        }
        return $this->redirect('/admin/construction/muster-roll');
    }

    private function musterRoles(): array
    {
        return [
            'mason' => 'Mason (Mistry)', 'helper' => 'Helper (Beldaar)',
            'operator' => 'Machine Operator', 'supervisor' => 'Supervisor', 'other' => 'Other',
        ];
    }

    private function civilWorkTypes(): array
    {
        return [
            'jcb_hours' => 'JCB / Machine Hours', 'road_soling' => 'Road Soling',
            'morang' => 'Morang', 'gitti' => 'Gitti', 'damar' => 'Damar / Bitumen',
            'brickwork' => 'Brickwork (Eent)', 'boundary_wall' => 'Boundary Wall',
            'pillar_demarcation' => 'Pillar Demarcation', 'cement_work' => 'Cement Work',
            'electric_pole' => 'Electric Poles', 'transformer' => 'Transformer',
            'drainage' => 'Drainage', 'gate' => 'Gate / Entry', 'other' => 'Other',
        ];
    }

    /* =========================================================
     *  Colony-wise Actual Cost vs Profit
     * ========================================================= */

    public function colonyCostProfit()
    {
        $this->requireAdmin();
        $rows = [];
        $grand = ['civil' => 0, 'material' => 0, 'contracted' => 0, 'collected' => 0];
        try {
            [$tenantSql, $tenantParams] = $this->tenantWhere();
            $cStmt = $this->db->prepare("SELECT id, name FROM colonies WHERE 1=1" . $tenantSql . " ORDER BY name ASC");
            $cStmt->execute($tenantParams);
            $colonies = $cStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            foreach ($colonies as $c) {
                $cid = (int)$c['id'];
                // Civil machine/site work
                $cw = $this->db->prepare("SELECT COALESCE(SUM(amount),0) AS t FROM civil_work_log WHERE colony_id = ?" . $tenantSql);
                $cw->execute(array_merge([$cid], $tenantParams));
                $civil = (float)($cw->fetch(\PDO::FETCH_ASSOC)['t'] ?? 0);
                // Material consumed (qty × unit cost at log time → use current unit_cost)
                $mu = $this->db->prepare(
                    "SELECT COALESCE(SUM(l.quantity * m.unit_cost),0) AS t
                     FROM material_usage_log l JOIN material_inventory m ON m.id = l.material_id
                     WHERE l.colony_id = ? AND l.tenant_id = ?"
                );
                $mu->execute([$cid, (int)$this->tenantId()]);
                $material = (float)($mu->fetch(\PDO::FETCH_ASSOC)['t'] ?? 0);
                // Contracted revenue: sold + booked plots
                $rv = $this->db->prepare(
                    "SELECT COALESCE(SUM(total_price),0) AS t, COUNT(*) AS n FROM plots
                     WHERE colony_id = ? AND status IN ('sold','booked')" . $tenantSql
                );
                $rv->execute(array_merge([$cid], $tenantParams));
                $rr = $rv->fetch(\PDO::FETCH_ASSOC);
                $contracted = (float)($rr['t'] ?? 0);
                $plotsN = (int)($rr['n'] ?? 0);
                // Collected cash via booking payments on this colony's plots
                $collected = 0.0;
                try {
                    $cl = $this->db->prepare(
                        "SELECT COALESCE(SUM(bp.payment_amount),0) AS t
                         FROM booking_payments bp JOIN bookings b ON b.id = bp.booking_id
                         JOIN plots p ON p.id = b.plot_id
                         WHERE p.colony_id = ? AND p.tenant_id = ?"
                    );
                    $cl->execute([$cid, (int)$this->tenantId()]);
                    $collected = (float)($cl->fetch(\PDO::FETCH_ASSOC)['t'] ?? 0);
                } catch (\Throwable $e) { error_log('colonyCostProfit collected: ' . $e->getMessage()); }
                $cost = $civil + $material;
                $profit = $contracted - $cost;
                $margin = $contracted > 0 ? round($profit * 100 / $contracted, 1) : 0;
                $rows[] = [
                    'id' => $cid, 'name' => $c['name'], 'civil' => $civil, 'material' => $material,
                    'cost' => $cost, 'contracted' => $contracted, 'collected' => $collected,
                    'profit' => $profit, 'margin' => $margin, 'plots' => $plotsN,
                ];
                $grand['civil'] += $civil; $grand['material'] += $material;
                $grand['contracted'] += $contracted; $grand['collected'] += $collected;
            }
        } catch (\Exception $e) { error_log('ProjectProgressController::colonyCostProfit: ' . $e->getMessage()); }
        return $this->render('admin/construction/colony_cost_profit', [
            'page_title' => 'Colony Cost vs Profit',
            'rows' => $rows,
            'grand' => $grand,
        ]);
    }
}
