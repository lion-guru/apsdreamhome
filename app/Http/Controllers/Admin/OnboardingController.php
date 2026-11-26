<?php
/**
 * Admin Onboarding Controller - Admin workflow for employee onboarding
 * Live since: Admin onboarding index view existed but was never wired to data
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Services\OnboardingService;
use App\Traits\TenantAwareTrait;

class OnboardingController extends BaseController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
        // Admin views need the admin chrome; BaseController defaults to the public layout.
        $this->layout = 'layouts/admin';
    }

    private function service(): OnboardingService
    {
        return new OnboardingService();
    }

    private function getPostInput(): array
    {
        $raw = file_get_contents('php://input');
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        return $_POST;
    }

    /**
     * Index: employee onboarding dashboard (stats, templates, employees in flow)
     */
    public function index()
    {
        $this->requireAdmin();
        try {
            $svc = $this->service();
            $templates = $svc->getTemplates();
            $stats = $svc->getOnboardingStats();
            $employees = $this->getEmployeesWithProgress();
            return $this->render('admin/onboarding/index', [
                'page_title' => 'Employee Onboarding',
                'page_description' => 'Templates, checklists, and new-hire progress',
                'templates' => $templates,
                'stats' => $stats,
                'employees' => $employees,
            ]);
        } catch (\Throwable $e) {
            error_log('[OnboardingController::index] ' . $e->getMessage());
            return $this->render('admin/onboarding/index', [
                'page_title' => 'Employee Onboarding',
                'page_description' => 'Set up templates, assign, track progress',
                'templates' => [],
                'stats' => [
                    'total_items' => 0,
                    'completed' => 0,
                    'in_progress' => 0,
                    'pending' => 0,
                    'completion_rate' => 0,
                ],
                'employees' => [],
                'error' => 'Could not load onboarding data: ' . htmlspecialchars($e->getMessage()),
            ]);
        }
    }

    /**
     * Create-template form (GET) / save (POST)
     */
    public function create()
    {
        $this->requireAdmin();
        return $this->render('admin/onboarding/template', [
            'page_title' => 'Create Onboarding Template',
            'mode' => 'create',
            'template' => ['name' => '', 'description' => '', 'category' => 'general', 'items' => []],
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        $this->validateCsrfMethod($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $name = trim((string)($_POST['name'] ?? ''));
        if ($name === '') {
            $this->setFlash('error', 'Template name is required');
            return $this->redirect('/admin/onboarding/create-template');
        }
        $data = $_POST;
        $data['items'] = $this->collectItemsFromPost();
        $result = $this->service()->createTemplate($data);
        if (!$result['success']) {
            $this->setFlash('error', $result['message'] ?? 'Failed to save template');
            return $this->redirect('/admin/onboarding/create-template');
        }
        $this->setFlash('success', $result['message'] ?? 'Template saved');
        return $this->redirect('/admin/onboarding');
    }

    public function edit($id)
    {
        $this->requireAdmin();
        $id = (int)$id;
        $detail = $this->loadTemplate($id);
        if (!$detail) {
            $this->setFlash('error', 'Template not found');
            return $this->redirect('/admin/onboarding');
        }
        return $this->render('admin/onboarding/template', [
            'page_title' => 'Edit Onboarding Template',
            'mode' => 'edit',
            'template' => $detail,
        ]);
    }

    public function update($id)
    {
        $this->requireAdmin();
        $this->validateCsrfMethod($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $id = (int)$id;
        $detail = $this->loadTemplate($id);
        if (!$detail) {
            $this->setFlash('error', 'Template not found');
            return $this->redirect('/admin/onboarding');
        }
        $name = trim((string)($_POST['name'] ?? ''));
        if ($name === '') {
            $this->setFlash('error', 'Template name is required');
            return $this->redirect('/admin/onboarding/template/' . $id);
        }
        $data = $_POST;
        $data['items'] = $this->collectItemsFromPost();
        $ok = $this->updateTemplate($id, $data);
        $this->setFlash($ok ? 'success' : 'error', $ok ? 'Template updated' : 'Could not update template');
        return $this->redirect('/admin/onboarding');
    }

    public function destroy($id)
    {
        $this->requireAdmin();
        $this->validateCsrfMethod($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $id = (int)$id;
        $tid = (int)$this->tenantId();
        $detail = $this->loadTemplate($id);
        if (!$detail) {
            $this->setFlash('error', 'Template not found');
            return $this->redirect('/admin/onboarding');
        }
        $db = \App\Core\Database::getInstance();
        $db->beginTransaction();
        try {
            $tSql = $tid > 1 ? ' AND tenant_id = ?' : '';
            $tParams = $tid > 1 ? [$tid] : [];
            $db->execute("DELETE FROM onboarding_template_items WHERE template_id = ?" . $tSql, array_merge([$id], $tParams));
            $db->execute("DELETE FROM onboarding_templates WHERE id = ?" . $tSql, array_merge([$id], $tParams));
            $db->commit();
            $this->setFlash('success', 'Template deleted');
        } catch (\Throwable $e) {
            $db->rollBack();
            $this->setFlash('error', 'Could not delete template: ' . $e->getMessage());
        }
        return $this->redirect('/admin/onboarding');
    }

    /**
     * Assign-template POST (from the assign modal)
     */
    public function assignTemplate()
    {
        $this->requireAdmin();
        $this->validateCsrfMethod($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $templateId = (int)($_POST['template_id'] ?? 0);
        $employeeId = (int)($_POST['employee_id'] ?? 0);
        if ($templateId < 1 || $employeeId < 1) {
            $this->setFlash('error', 'Template and employee are required');
            return $this->redirect($employeeId ? '/admin/onboarding/assign/' . $employeeId : '/admin/onboarding');
        }
        $result = $this->service()->assignTemplate($employeeId, $templateId, $this->getCurrentUserId());
        if (!$result['success']) {
            $this->setFlash('error', $result['message'] ?? 'Failed to assign template');
        } else {
            $this->setFlash('success', $result['message'] ?? 'Template assigned');
        }
        return $this->redirect('/admin/onboarding/assign/' . $employeeId);
    }

    /**
     * GET assignment view with per-item checklist (detail.php)
     */
    public function assignmentDetail($employeeId = null)
    {
        $this->requireAdmin();

        if ($employeeId === null || $employeeId === '') {
            $this->setFlash('info', 'Pick an employee from the list to assign a template');
            return $this->redirect('/admin/onboarding');
        }

        $employeeId = (int)$employeeId;
        $svc = $this->service();
        $progress = $svc->getOnboardingProgress($employeeId);
        $templates = $svc->getTemplates();
        $employee = $this->getEmployee($employeeId);
        if (!$employee) {
            $this->setFlash('error', 'Employee not found');
            return $this->redirect('/admin/onboarding');
        }
        return $this->render('admin/onboarding/detail', [
            'page_title' => 'Employee Onboarding: ' . htmlspecialchars($employee['name'] ?? ''),
            'employee' => $employee,
            'progress' => $progress,
            'templates' => $templates,
        ]);
    }

    // ──────────────────────────────────────────
    // Wizard (legacy, kept working)
    // ──────────────────────────────────────────

    public function updateItem()
    {
        $this->requireAdmin();

        $token = (string)($_POST['csrf_token'] ?? '');
        $session = (string)($_SESSION['csrf_token'] ?? '');
        if ($token === '' || $session === '' || !hash_equals($session, $token)) {
            $this->json(['success' => false, 'message' => 'Session expired. Reload and retry.'], 403);
            return;
        }

        $itemId = (int)($_POST['item_id'] ?? 0);
        $employeeId = (int)($_POST['employee_id'] ?? 0);
        $status = trim((string)($_POST['status'] ?? ''));
        $notes = trim((string)($_POST['notes'] ?? ''));

        $allowed = ['pending', 'in_progress', 'completed', 'skipped', 'waived'];
        if ($itemId < 1 || $employeeId < 1 || !in_array($status, $allowed, true)) {
            $this->json(['success' => false, 'message' => 'Invalid item, employee, or status'], 422);
            return;
        }

        $result = $this->service()->updateItemStatus($itemId, $employeeId, $status, ['notes' => $notes]);
        $code = $result['success'] ? 200 : 422;
        $this->json($result, $code);
    }

    public function saveStep()
    {
        // kept as previously implemented
        $this->requireAdmin();
        $input = $this->getPostInput();
        $step = max(1, (int)($input['step'] ?? 1));
        $data = $input['data'] ?? [];
        $db = $this->pdo();
        $tid = (int)$this->tenantId();

        if ($step === 1) {
            $this->saveCompany($db, $tid, $data);
        } elseif ($step === 2) {
            $this->saveTeam($db, $tid, $data);
        } elseif ($step === 3) {
            $this->saveColonies($db, $tid, $data);
        } elseif ($step === 4) {
            $this->savePlots($db, $tid, $data);
        } elseif ($step === 5) {
            $this->saveLeadSources($db, $tid, $data);
        }

        $this->markStepCompleted($step);

        // these two lines were previously here; keep the behavior intact
        // (score endpoint uses the metrics computed from real data below)

        return $this->redirect('/admin/onboarding?step=' . ($step + 1));
    }

    public function complete()
    {
        $this->requireAdmin();
        return $this->redirect('/admin/erp');
    }

    public function readiness()
    {
        $this->requireAdmin();
        $tid = (int)$this->tenantId();
        $db = $this->pdo();
        $metrics = [
            'company' => (bool)$db->fetchOne("SELECT 1 FROM company_settings WHERE tenant_id = ? LIMIT 1", [$tid]),
            'team' => (int)$db->fetchOne("SELECT COUNT(*) as c FROM users WHERE tenant_id = ? AND role IN ('admin','manager','sales_manager')", [$tid])['c'] > 0,
            'colonies' => (int)$db->fetchOne("SELECT COUNT(*) as c FROM colonies WHERE tenant_id = ? AND status = 'active'", [$tid])['c'] > 0,
            'plots' => (int)$db->fetchOne("SELECT COUNT(*) as c FROM plots WHERE tenant_id = ? AND status = 'available'", [$tid])['c'] > 0,
            'leads' => (int)$db->fetchOne("SELECT COUNT(*) as c FROM lead_sources WHERE is_active = 1", [])['c'] > 0,
        ];
        $score = (int)(array_sum(array_map('intval', $metrics)) / count($metrics) * 100);

        $this->json([
            'success' => true,
            'metrics' => $metrics,
            'score' => $score,
            'ready' => $score >= 80,
        ]);
    }

    // ──────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────

    private function pdo(): \PDO
    {
        return $this->db->getConnection();
    }

    private function saveCompany($db, int $tid, array $data): void
    {
        $sql = "
            INSERT INTO company_settings (tenant_id, company_name, gstin, address, city, state, pincode, phone, email, logo_url, primary_color, secondary_color, created_at, updated_at)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())
            ON DUPLICATE KEY UPDATE
                company_name = VALUES(company_name), gstin = VALUES(gstin), address = VALUES(address),
                city = VALUES(city), state = VALUES(state), pincode = VALUES(pincode),
                phone = VALUES(phone), email = VALUES(email), logo_url = VALUES(logo_url),
                primary_color = VALUES(primary_color), secondary_color = VALUES(secondary_color),
                updated_at = NOW()
        ";
        $db->execute($sql, [
            $tid,
            $data['company_name'] ?? '',
            $data['gstin'] ?? '',
            $data['address'] ?? '',
            $data['city'] ?? '',
            $data['state'] ?? '',
            $data['pincode'] ?? '',
            $data['phone'] ?? '',
            $data['email'] ?? '',
            $data['logo_url'] ?? '',
            $data['primary_color'] ?? '#0a192f',
            $data['secondary_color'] ?? '#d4af37',
        ]);
    }

    private function saveTeam($db, int $tid, array $data): void
    {
        $users = $data['users'] ?? [];
        foreach ($users as $user) {
            if (empty($user['email'])) continue;
            $password = $user['password'] ?? 'Aps@2026';
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $db->execute("
                INSERT INTO users (tenant_id, name, email, phone, password, role, status, created_at, updated_at)
                VALUES (?,?,?,?,?,?, 'active', NOW(), NOW())
                ON DUPLICATE KEY UPDATE name = VALUES(name), role = VALUES(role), updated_at = NOW()
            ", [$tid, $user['name'] ?? '', $user['email'], $user['phone'] ?? '', $hashed, $user['role'] ?? 'manager']);
        }
    }

    private function saveColonies($db, int $tid, array $data): void
    {
        $colonies = $data['colonies'] ?? [];
        foreach ($colonies as $colony) {
            if (empty($colony['name'])) continue;
            $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $colony['name']));
            $db->execute("
                INSERT INTO colonies (tenant_id, name, slug, address, city, state, pincode, latitude, longitude, status, created_at, updated_at)
                VALUES (?,?,?,?,?,?,?,?,?, 'active', NOW(), NOW())
                ON DUPLICATE KEY UPDATE address = VALUES(address), city = VALUES(city), status = VALUES(status), updated_at = NOW()
            ", [$tid, $colony['name'], $slug, $colony['address'] ?? '', $colony['city'] ?? '', $colony['state'] ?? '', $colony['pincode'] ?? '', $colony['latitude'] ?? null, $colony['longitude'] ?? null]);
        }
    }

    private function savePlots($db, int $tid, array $data): void
    {
        $plots = $data['plots'] ?? [];
        foreach ($plots as $plot) {
            if (empty($plot['plot_number']) || empty($plot['colony_id'])) continue;
            $db->execute("
                INSERT INTO plots (tenant_id, colony_id, plot_number, block, area_sqft, width_ft, length_ft, facing, corner_plot, base_price_per_sqft, status, created_at, updated_at)
                VALUES (?,?,?,?,?,?,?,?,?,?, 'available', NOW(), NOW())
                ON DUPLICATE KEY UPDATE block = VALUES(block), area_sqft = VALUES(area_sqft), base_price_per_sqft = VALUES(base_price_per_sqft), updated_at = NOW()
            ", [$tid, $plot['colony_id'], $plot['plot_number'], $plot['block'] ?? '', $plot['area_sqft'] ?? 0, $plot['width_ft'] ?? 0, $plot['length_ft'] ?? 0, $plot['facing'] ?? '', (int)($plot['corner_plot'] ?? 0), $plot['base_price_per_sqft'] ?? 0]);
        }
    }

    private function saveLeadSources($db, int $tid, array $data): void
    {
        $sources = $data['sources'] ?? [];
        foreach ($sources as $source) {
            if (empty($source['name'])) continue;
            $db->execute("
                INSERT INTO lead_sources (name, description, is_active, color, icon, sort_order)
                VALUES (?,?,?,?,?,?)
                ON DUPLICATE KEY UPDATE description = VALUES(description), is_active = VALUES(is_active), updated_at = NOW()
            ", [$source['name'], $source['description'] ?? '', (int)($source['is_active'] ?? 1), $source['color'] ?? '#3b82f6', $source['icon'] ?? 'fa-globe', $source['sort_order'] ?? 0]);
        }
    }

    private function markStepCompleted(int $step): void
    {
        $db = $this->pdo();
        $tid = (int)$this->tenantId();
        $key = 'onboarding_step_' . $step . '_filled';
        $json = json_encode(['ok' => true]);
        $note = json_encode(['note' => 'Onboarding wizard step completed']);
        // Never a standalone `key_value_storage` table — it does not exist in any
        // environment and the write fatal-1054s every time (CI gate, S465).
        // The closest honest store is service_configs (same config_key/config_value
        // shape + unique (service_name, config_key) upsert semantics).
        $db->execute("
            INSERT INTO service_configs (tenant_id, service_name, config_key, config_value, group_name, description, created_at, updated_at)
            VALUES (?, 'onboarding', ?, ?, 'onboarding', ?, NOW(), NOW())
            ON DUPLICATE KEY UPDATE config_value = VALUES(config_value), updated_at = NOW()
        ", [$tid, $key, $json, substr($note, 0, 250)]);
    }

    private function collectItemsFromPost(): array
    {
        $items = $_POST['items'] ?? [];
        if (!is_array($items)) return [];
        $out = [];
        foreach ($items as $i => $item) {
            if (!is_array($item)) continue;
            $name = trim((string)($item['item_name'] ?? ''));
            if ($name === '') continue;
            $out[] = [
                'item_name' => $name,
                'description' => trim((string)($item['description'] ?? '')),
                'category' => in_array($item['category'] ?? '', ['document', 'access', 'training', 'equipment', 'compliance', 'other'], true) ? $item['category'] : 'other',
                'is_mandatory' => !empty($item['is_mandatory']) ? 1 : 0,
                'assigned_to_role' => trim((string)($item['assigned_to_role'] ?? '')),
                'due_days' => trim((string)($item['due_days'] ?? '')) ?: null,
            ];
        }
        return $out;
    }

    private function loadTemplate(int $id): ?array
    {
        $db = \App\Core\Database::getInstance();
        $tid = (int)$this->tenantId();
        $tSql = $tid > 1 ? ' WHERE tenant_id = ?' : '';
        $tParams = $tid > 1 ? [$tid] : [];
        $template = $db->fetchOne("SELECT * FROM onboarding_templates WHERE id = ?" . $tSql, array_merge([$id], $tParams));
        if (!$template) return null;
        $items = $db->fetchAll("SELECT * FROM onboarding_template_items WHERE template_id = ?" . $tSql . " ORDER BY CAST(sort_order AS UNSIGNED) ASC, id ASC", array_merge([$id], $tParams)) ?: [];
        $template['items'] = $items;
        return $template;
    }

    private function updateTemplate(int $id, array $data): bool
    {
        $db = \App\Core\Database::getInstance();
        $tid = (int)$this->tenantId();
        $tSql = $tid > 1 ? ' AND tenant_id = ?' : '';
        $tParams = $tid > 1 ? [$tid] : [];
        $name = trim((string)($data['name'] ?? ''));
        $desc = trim((string)($data['description'] ?? ''));
        $category = in_array($data['category'] ?? '', ['general', 'sales', 'hr', 'technical', 'custom'], true) ? (string)$data['category'] : 'general';
        try {
            $db->beginTransaction();
            $db->execute("UPDATE onboarding_templates SET name = ?, description = ?, category = ? WHERE id = ?" . $tSql, array_merge([$name, $desc, $category, $id], $tParams));
            $db->execute("DELETE FROM onboarding_template_items WHERE template_id = ?" . $tSql, array_merge([$id], $tParams));
            if (!empty($data['items'])) {
                $stmt = $db->prepare("INSERT INTO onboarding_template_items (tenant_id, template_id, item_name, description, category, is_mandatory, assigned_to_role, due_days, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                foreach ($data['items'] as $i => $item) {
                    $stmt->execute([
                        $tid, $id,
                        $item['item_name'], $item['description'] ?? null,
                        $item['category'] ?? 'other', $item['is_mandatory'] ?? 1,
                        $item['assigned_to_role'] ?? null, $item['due_days'] ?? null, (string)$i,
                    ]);
                }
            }
            $db->commit();
            return true;
        } catch (\Throwable $e) {
            $db->rollBack();
            error_log('[OnboardingController::updateTemplate] ' . $e->getMessage());
            return false;
        }
    }

    private function getEmployeesWithProgress(): array
    {
        $db = \App\Core\Database::getInstance();
        $rows = $db->fetchAll(
            "SELECT p.id, p.employee_code, p.department, p.designation, p.user_id, u.name
             FROM employee_profiles p
             JOIN users u ON u.id = p.user_id
             WHERE p.status = 'active'
             ORDER BY u.name ASC
             LIMIT 200"
        ) ?: [];
        $out = [];
        foreach ($rows as $r) {
            $empId = (int)$r['id'];
            $counts = $db->fetchOne(
                "SELECT COUNT(*) AS total_items, SUM(status = 'completed') AS completed_items
                 FROM onboarding_checklists WHERE employee_id = ?",
                [$empId]
            ) ?: ['total_items' => 0, 'completed_items' => 0];
            $out[] = [
                'id' => $empId,
                'name' => (string)$r['name'],
                'employee_code' => (string)$r['employee_code'],
                'department' => (string)($r['department'] ?? '—'),
                'designation' => (string)($r['designation'] ?? '—'),
                'total_items' => (int)($counts['total_items'] ?? 0),
                'completed_items' => (int)($counts['completed_items'] ?? 0),
            ];
        }
        return $out;
    }

    private function getEmployee(int $id): ?array
    {
        $db = \App\Core\Database::getInstance();
        $tid = (int)$this->tenantId();
        $tSql = $tid > 1 ? ' AND p.tenant_id = ?' : '';
        $tParams = $tid > 1 ? [$tid] : [];
        $row = $db->fetchOne(
            "SELECT p.*, u.name FROM employee_profiles p JOIN users u ON u.id = p.user_id WHERE p.id = ?" . $tSql . " LIMIT 1",
            array_merge([$id], $tParams)
        );
        return $row ?: null;
    }

    private function getCurrentUserId(): int
    {
        return (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 1);
    }

    private function validateCsrfMethod(string $method): void
    {
        if ($method === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            $session = $_SESSION['csrf_token'] ?? '';
            if ($token === '' || $session === '' || !hash_equals((string)$session, (string)$token)) {
                $this->setFlash('error', 'Session expired. Please reload and try again.');
                $this->redirect('/admin/onboarding');
                exit;
            }
        }
    }
}
