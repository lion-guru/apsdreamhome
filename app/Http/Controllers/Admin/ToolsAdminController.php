<?php

namespace App\Http\Controllers\Admin;

use App\Services\AI\DocumentAIService;
use App\Services\Legal\ESignService;
use App\Services\Finance\StampDutyCalculatorService;
use PDO;

class ToolsAdminController extends AdminController
{
    use \App\Traits\TenantAwareTrait;

    private function getDb(): PDO
    {
        return \App\Core\Database\Database::getInstance()->getPdo();
    }

    // ========== Document AI ==========

    public function documentExtraction()
    {
        $this->requireAdmin();
        $db = $this->getDb();
        $service = new DocumentAIService();

        $filters = [
            'status' => $_GET['status'] ?? '',
            'document_type' => $_GET['document_type'] ?? '',
            'engine' => $_GET['engine'] ?? '',
        ];

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $jobs = $service->listJobs(array_filter($filters), $perPage, $offset);

        $totalJobs = 0;
        $totalPages = 1;
        try {
            $where = [];
            $params = [];
            if (!empty($filters['status'])) { $where[] = 'status = ?'; $params[] = $filters['status']; }
            if (!empty($filters['document_type'])) { $where[] = 'document_type = ?'; $params[] = $filters['document_type']; }
            if (!empty($filters['engine'])) { $where[] = 'extraction_engine = ?'; $params[] = $filters['engine']; }
            $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            $countRow = $db->prepare("SELECT COUNT(*) as cnt FROM document_extraction_jobs $whereClause");
            $countRow->execute($params);
            $totalJobs = (int)$countRow->fetchColumn();
            $totalPages = max(1, (int)ceil($totalJobs / $perPage));
        } catch (\Exception $e) { error_log("ToolsAdminController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        $stats = ['pending_review' => 0, 'approved_today' => 0, 'corrected_today' => 0, 'avg_confidence' => 0];
        try {
            $stats['pending_review'] = (int)$db->query("SELECT COUNT(*) FROM document_extraction_jobs WHERE status IN ('completed','corrected') AND review_required = 1")->fetchColumn();
            $stats['approved_today'] = (int)$db->query("SELECT COUNT(*) FROM document_extraction_jobs WHERE status = 'approved' AND DATE(reviewed_at) = CURDATE()")->fetchColumn();
            $stats['corrected_today'] = (int)$db->query("SELECT COUNT(*) FROM document_extraction_jobs WHERE status = 'corrected' AND DATE(reviewed_at) = CURDATE()")->fetchColumn();
            $avgRow = $db->query("SELECT AVG(confidence_score) FROM document_extraction_jobs WHERE status IN ('completed','approved','corrected')");
            $stats['avg_confidence'] = round((float)$avgRow->fetchColumn());
        } catch (\Exception $e) { error_log("ToolsAdminController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        return $this->render('admin/ai/document-extraction', [
            'page_title' => 'Document AI Review Queue',
            'jobs' => $jobs,
            'stats' => $stats,
            'filters' => $filters,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_jobs' => $totalJobs,
        ]);
    }

    // ========== eSign Management ==========

    public function esignDashboard()
    {
        $this->requireAdmin();
        $db = $this->getDb();
        $service = new ESignService();

        $stats = ['total' => 0, 'initiated' => 0, 'signed' => 0, 'expired' => 0, 'today' => 0];
        try {
            $stats['total'] = (int)$db->query("SELECT COUNT(*) FROM esign_transactions")->fetchColumn();
            $stats['initiated'] = (int)$db->query("SELECT COUNT(*) FROM esign_transactions WHERE status IN ('initiated','pending_otp')")->fetchColumn();
            $stats['signed'] = (int)$db->query("SELECT COUNT(*) FROM esign_transactions WHERE status = 'signed'")->fetchColumn();
            $stats['expired'] = (int)$db->query("SELECT COUNT(*) FROM esign_transactions WHERE status = 'expired'")->fetchColumn();
            $stats['today'] = (int)$db->query("SELECT COUNT(*) FROM esign_transactions WHERE DATE(created_at) = CURDATE()")->fetchColumn();
        } catch (\Exception $e) { error_log("ToolsAdminController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        $transactions = [];
        try {
            $page = max(1, (int)($_GET['page'] ?? 1));
            $perPage = 20;
            $offset = ($page - 1) * $perPage;
            $statusFilter = $_GET['status'] ?? '';
            $where = $statusFilter ? "WHERE status = ?" : "";
            $params = $statusFilter ? [$statusFilter] : [];
            $stmt = $db->prepare("SELECT * FROM esign_transactions $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
            $stmt->execute($params);
            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) { error_log("ToolsAdminController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        $config = [];
        try {
            $configRows = $db->query("SELECT config_key, config_value FROM esign_config WHERE is_active = 1")->fetchAll(PDO::FETCH_KEY_PAIR);
            $config = $configRows;
        } catch (\Exception $e) { error_log("ToolsAdminController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        return $this->render('admin/tools/esign', [
            'page_title' => 'eSign Management',
            'transactions' => $transactions,
            'stats' => $stats,
            'config' => $config,
            'status_filter' => $_GET['status'] ?? '',
        ]);
    }

    // ========== Stamp Duty Config ==========

    public function stampDutyConfig()
    {
        $this->requireAdmin();
        $db = $this->getDb();

        $configs = [];
        try {
            $configs = $db->query("SELECT * FROM stamp_duty_config ORDER BY state_code, property_type")->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) { error_log("ToolsAdminController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        $rates = [];
        try {
            $rates = $db->query("SELECT * FROM stamp_duty_rates ORDER BY state_code, effective_from DESC")->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) { error_log("ToolsAdminController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        $circleRates = [];
        try {
            $page = max(1, (int)($_GET['page'] ?? 1));
            $perPage = 30;
            $offset = ($page - 1) * $perPage;
            $stateFilter = $_GET['state'] ?? '';
            $where = $stateFilter ? "WHERE state_code = ?" : "";
            $params = $stateFilter ? [$stateFilter] : [];
            $stmt = $db->prepare("SELECT * FROM circle_rates $where ORDER BY state_code, district, area_type LIMIT $perPage OFFSET $offset");
            $stmt->execute($params);
            $circleRates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) { error_log("ToolsAdminController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        $states = [];
        try {
            $states = $db->query("SELECT DISTINCT state_code FROM circle_rates ORDER BY state_code")->fetchAll(PDO::FETCH_COLUMN);
        } catch (\Exception $e) { error_log("ToolsAdminController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        $totalCircleRates = 0;
        try {
            $totalCircleRates = (int)$db->query("SELECT COUNT(*) FROM circle_rates")->fetchColumn();
        } catch (\Exception $e) { error_log("ToolsAdminController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        return $this->render('admin/tools/stamp-duty', [
            'page_title' => 'Stamp Duty & Circle Rate Config',
            'configs' => $configs,
            'rates' => $rates,
            'circle_rates' => $circleRates,
            'states' => $states,
            'state_filter' => $_GET['state'] ?? '',
            'total_circle_rates' => $totalCircleRates,
        ]);
    }

    public function stampDutyConfigSave()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $db = $this->getDb();

        $stateCode = strtoupper(trim($_POST['state_code'] ?? ''));
        $propertyType = $_POST['property_type'] ?? 'residential';
        $stampRate = (float)($_POST['stamp_rate'] ?? 0);
        $regRate = (float)($_POST['registration_rate'] ?? 1.0);

        if (empty($stateCode)) {
            $_SESSION['error'] = 'State code is required';
            header('Location: ' . BASE_URL . '/admin/tools/stamp-duty');
            exit;
        }

        try {
            $existing = $db->prepare("SELECT id FROM stamp_duty_config WHERE state_code = ? AND property_type = ?");
            $existing->execute([$stateCode, $propertyType]);
            if ($existing->fetch()) {
                $stmt = $db->prepare("UPDATE stamp_duty_config SET stamp_rate = ?, registration_rate = ?, updated_at = NOW() WHERE state_code = ? AND property_type = ? AND tenant_id = ?");
                $stmt->execute([$stampRate, $regRate, $stateCode, $propertyType, $this->tenantId()]);
            } else {
                $stmt = $db->prepare("INSERT INTO stamp_duty_config (state_code, property_type, stamp_rate, registration_rate, created_at, updated_at, tenant_id) VALUES (?, ?, ?, ?, NOW(), NOW(), ?)");
                $stmt->execute([$stateCode, $propertyType, $stampRate, $regRate, $this->tenantId()]);
            }
            $_SESSION['success'] = "Stamp duty config saved for $stateCode ($propertyType)";
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/admin/tools/stamp-duty');
        exit;
    }

    // ========== Landmarks ==========

    public function landmarks()
    {
        $this->requireAdmin();
        $db = $this->getDb();

        $landmarks = [];
        try {
            $landmarks = $db->query("SELECT l.*, (SELECT COUNT(*) FROM colony_landmark_distances WHERE landmark_id = l.id) as linked_colonies FROM landmarks l ORDER BY l.category, l.name")->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) { error_log("ToolsAdminController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        $colonies = [];
        try {
            $colonies = $db->query("SELECT id, name FROM colonies WHERE status = 'active' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) { error_log("ToolsAdminController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        $distances = [];
        try {
            $distances = $db->query("
                SELECT cld.*, l.name as landmark_name, l.category as landmark_category, c.name as colony_name 
                FROM colony_landmark_distances cld 
                JOIN landmarks l ON cld.landmark_id = l.id 
                JOIN colonies c ON cld.colony_id = c.id 
                ORDER BY c.name, cld.distance_km
            ")->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) { error_log("ToolsAdminController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        $categories = [];
        try {
            $categories = $db->query("SELECT DISTINCT category FROM landmarks ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
        } catch (\Exception $e) { error_log("ToolsAdminController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        return $this->render('admin/tools/landmarks', [
            'page_title' => 'Landmarks & Distance Config',
            'landmarks' => $landmarks,
            'colonies' => $colonies,
            'distances' => $distances,
            'categories' => $categories,
        ]);
    }

    public function landmarksSave()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $db = $this->getDb();

        $name = trim($_POST['name'] ?? '');
        $category = $_POST['category'] ?? 'other';
        $address = trim($_POST['address'] ?? '');
        $latitude = (float)($_POST['latitude'] ?? 0);
        $longitude = (float)($_POST['longitude'] ?? 0);
        $pincode = trim($_POST['pincode'] ?? '');

        if (empty($name)) {
            $_SESSION['error'] = 'Landmark name is required';
            header('Location: ' . BASE_URL . '/admin/tools/landmarks');
            exit;
        }

        try {
            $tid = $this->tenantId();
            $stmt = $db->prepare("INSERT INTO landmarks (name, category, address, latitude, longitude, pincode, is_active, created_at, tenant_id) VALUES (?, ?, ?, ?, ?, ?, 1, NOW(), ?)");
            $stmt->execute([$name, $category, $address, $latitude, $longitude, $pincode, $tid]);
            $landmarkId = (int)$db->lastInsertId();

            // Auto-calculate distances to all active colonies
            $colonies = $db->query("SELECT id, latitude, longitude FROM colonies WHERE status = 'active' AND latitude IS NOT NULL AND longitude IS NOT NULL")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($colonies as $colony) {
                $distance = $this->haversineDistance($latitude, $longitude, (float)$colony['latitude'], (float)$colony['longitude']);
                $stmt2 = $db->prepare("INSERT IGNORE INTO colony_landmark_distances (colony_id, landmark_id, distance_km, created_at, tenant_id) VALUES (?, ?, ?, NOW(), ?)");
                $stmt2->execute([$colony['id'], $landmarkId, round($distance, 2), $tid]);
            }

            $_SESSION['success'] = "Landmark '$name' added. Distances calculated for " . count($colonies) . " colonies.";
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/admin/tools/landmarks');
        exit;
    }

    public function landmarksDelete($id)
    {
        $this->requireAdmin();
        $db = $this->getDb();

        try {
            $db->prepare("DELETE FROM colony_landmark_distances WHERE landmark_id = ? AND tenant_id = ?")->execute([$id, $this->tenantId()]);
            $db->prepare("DELETE FROM landmarks WHERE id = ? AND tenant_id = ?")->execute([$id, $this->tenantId()]);
            $_SESSION['success'] = 'Landmark deleted';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/admin/tools/landmarks');
        exit;
    }

    // ========== WhatsApp Template Editor ==========

    public function whatsappTemplates()
    {
        $this->requireAdmin();
        $db = $this->getDb();

        $templates = [];
        try {
            $templates = $db->query("SELECT * FROM whatsapp_templates ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) { error_log("ToolsAdminController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        $stats = ['total' => 0, 'active' => 0, 'inactive' => 0, 'message_logs' => 0];
        try {
            $stats['total'] = (int)$db->query("SELECT COUNT(*) FROM whatsapp_templates")->fetchColumn();
            $stats['active'] = (int)$db->query("SELECT COUNT(*) FROM whatsapp_templates WHERE is_active = 1")->fetchColumn();
            $stats['inactive'] = $stats['total'] - $stats['active'];
            $stats['message_logs'] = (int)$db->query("SELECT COUNT(*) FROM whatsapp_message_logs")->fetchColumn();
        } catch (\Exception $e) { error_log("ToolsAdminController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        return $this->render('admin/tools/whatsapp-templates', [
            'page_title' => 'WhatsApp Template Manager',
            'templates' => $templates,
            'stats' => $stats,
        ]);
    }

    public function whatsappTemplatesSave()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $db = $this->getDb();

        $id = (int)($_POST['template_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $content = $_POST['content'] ?? '';
        $category = $_POST['category'] ?? 'MARKETING';
        $language = $_POST['language'] ?? 'en';
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if (empty($name)) {
            $_SESSION['error'] = 'Template name is required';
            header('Location: ' . BASE_URL . '/admin/tools/whatsapp-templates');
            exit;
        }

        try {
            if ($id > 0) {
                $stmt = $db->prepare("UPDATE whatsapp_templates SET name = ?, content = ?, category = ?, language = ?, is_active = ?, updated_at = NOW() WHERE id = ? AND tenant_id = ?");
                $stmt->execute([$name, $content, $category, $language, $isActive, $id, $this->tenantId()]);
            } else {
                $stmt = $db->prepare("INSERT INTO whatsapp_templates (name, content, category, language, is_active, created_at, updated_at, tenant_id) VALUES (?, ?, ?, ?, ?, NOW(), NOW(), ?)");
                $stmt->execute([$name, $content, $category, $language, $isActive, $this->tenantId()]);
            }
            $_SESSION['success'] = "Template '$name' saved";
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/admin/tools/whatsapp-templates');
        exit;
    }

    public function whatsappTemplatesDelete($id)
    {
        $this->requireAdmin();
        $db = $this->getDb();

        try {
            $db->prepare("DELETE FROM whatsapp_templates WHERE id = ? AND tenant_id = ?")->execute([$id, $this->tenantId()]);
            $_SESSION['success'] = 'Template deleted';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/admin/tools/whatsapp-templates');
        exit;
    }

    // ========== Helper ==========

    private function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}
