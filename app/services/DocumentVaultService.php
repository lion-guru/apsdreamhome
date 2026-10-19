<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;
use PDO;
use Exception;
use InvalidArgumentException;

class DocumentVaultService
{
    use ServiceTenantTrait;

    private $conn;

    public function __construct()
    {
        $db = Database::getInstance();
        $this->conn = method_exists($db, 'getConnection') ? $db->getConnection() : $db;
    }

    // ==================== DOCUMENT CATEGORIES ====================

    public function getCategories(array $filters = []): array
    {
        $where = ['dc.tenant_id = ?'];
        $params = [$this->tenantId()];

        if (!empty($filters['is_active'])) { $where[] = 'dc.is_active = ?'; $params[] = (int)$filters['is_active']; }
        if (!empty($filters['is_mandatory'])) { $where[] = 'dc.is_mandatory = ?'; $params[] = (int)$filters['is_mandatory']; }

        $whereSql = 'WHERE ' . implode(' AND ', $where);
        $sql = "SELECT dc.* FROM document_categories dc $whereSql ORDER BY dc.sort_order ASC, dc.name ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(array_merge([$this->tenantId()], $params));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategory(int $id): ?array
    {
        $stmt = $this->conn->prepare('SELECT * FROM document_categories WHERE id = ? AND tenant_id = ?');
        $stmt->execute([$id, $this->tenantId()]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function createCategory(array $data): array
    {
        $required = ['name', 'code'];
        foreach ($required as $field) {
            if (empty($data[$field])) throw new InvalidArgumentException("$field is required");
        }

        $columns = array_merge(
            ['name', 'code', 'description', 'is_mandatory', 'retention_years', 'allowed_types', 'max_size_mb', 'requires_expiry', 'expiry_alert_days', 'is_active', 'sort_order', 'created_at'],
            array_keys($this->tenantInsertData())
        );

        $values = array_merge([
            $data['name'], $data['code'], $data['description'] ?? null,
            $data['is_mandatory'] ?? false, $data['retention_years'] ?? 7,
            $data['allowed_types'] ? json_encode($data['allowed_types']) : null,
            $data['max_size_mb'] ?? 10, $data['requires_expiry'] ?? false,
            $data['expiry_alert_days'] ?? 30, $data['is_active'] ?? true,
            $data['sort_order'] ?? 0, date('Y-m-d H:i:s')
        ], array_values($this->tenantInsertData()));

        $placeholders = str_repeat('?,', count($columns) - 1) . '?';
        $sql = "INSERT INTO document_categories (" . implode(', ', $columns) . ") VALUES ($placeholders)";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($values);
            return ['success' => true, 'id' => (int)$this->conn->lastInsertId(), 'message' => 'Category created'];
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                return ['success' => false, 'message' => 'Category code already exists'];
            }
            throw $e;
        }
    }

    public function updateCategory(int $id, array $data): array
    {
        $allowed = ['name', 'description', 'is_mandatory', 'retention_years', 'allowed_types', 'max_size_mb', 'requires_expiry', 'expiry_alert_days', 'is_active', 'sort_order'];
        $fields = []; $params = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $params[] = $field === 'allowed_types' && is_array($data[$field]) ? json_encode($data[$field]) : $data[$field];
            }
        }
        if (empty($fields)) return ['success' => false, 'message' => 'No valid fields'];
        $params[] = $id; $params[] = $this->tenantId();
        $sql = "UPDATE document_categories SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ? AND tenant_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return ['success' => $stmt->rowCount() > 0, 'message' => $stmt->rowCount() > 0 ? 'Updated' : 'Not found'];
    }

    // ==================== EMPLOYEE DOCUMENTS ====================

    public function getDocuments(array $filters = []): array
    {
        $where = ['ed.tenant_id = ?'];
        $params = [$this->tenantId()];

        if (!empty($filters['employee_id'])) { $where[] = 'ed.employee_id = ?'; $params[] = $filters['employee_id']; }
        if (!empty($filters['category_id'])) { $where[] = 'ed.category_id = ?'; $params[] = $filters['category_id']; }
        if (!empty($filters['verification_status'])) { $where[] = 'ed.verification_status = ?'; $params[] = $filters['verification_status']; }
        if (!empty($filters['expiring_within'])) { $where[] = 'ed.expiry_date IS NOT NULL AND ed.expiry_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)'; $params[] = $filters['expiring_within']; }

        $whereSql = 'WHERE ' . implode(' AND ', $where);
        $sql = "SELECT ed.*, dc.name as category_name, u.name as employee_name, u.email as employee_email,
                    v.name as verified_by_name
                FROM employee_documents ed
                JOIN document_categories dc ON ed.category_id = dc.id
                JOIN employees e ON ed.employee_id = e.id
                JOIN users u ON e.user_id = u.id
                LEFT JOIN users v ON ed.verified_by = v.id
                WHERE $whereSql
                ORDER BY ed.created_at DESC";

        $params[] = $this->tenantId();
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(array_merge([$this->tenantId()], $params));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEmployeeDocuments(int $employeeId): array
    {
        $stmt = $this->conn->prepare("
            SELECT ed.*, dc.name as category_name
            FROM employee_documents ed
            JOIN document_categories dc ON ed.category_id = dc.id
            WHERE ed.employee_id = ? AND ed.tenant_id = ?
            ORDER BY ed.created_at DESC
        ");
        $stmt->execute([$employeeId, $this->tenantId()]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDocument(int $id): ?array
    {
        $stmt = $this->conn->prepare("
            SELECT ed.*, dc.name as category_name, u.name as employee_name, u.email as employee_email,
                   v.name as verified_by_name
            FROM employee_documents ed
            JOIN document_categories dc ON ed.category_id = dc.id
            JOIN employees e ON ed.employee_id = e.id
            JOIN users u ON e.user_id = u.id
            LEFT JOIN users v ON ed.verified_by = v.id
            WHERE ed.id = ? AND ed.tenant_id = ?
        ");
        $stmt->execute([$id, $this->tenantId()]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function uploadDocument(int $employeeId, array $data, $file): array
    {
        $required = ['category_id', 'document_name'];
        foreach ($required as $field) {
            if (empty($data[$field])) throw new InvalidArgumentException("$field is required");
        }

        $category = $this->getCategory($data['category_id']);
        if (!$category) throw new InvalidArgumentException('Invalid category');

        // Validate file
        $allowedTypes = $category['allowed_types'] ? json_decode($category['allowed_types'], true) : ['pdf', 'jpg', 'jpeg', 'png'];
        $maxSize = ($category['max_size_mb'] ?? 10) * 1024 * 1024;

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedTypes)) {
            throw new InvalidArgumentException('File type not allowed. Allowed: ' . implode(', ', $allowedTypes));
        }
        if ($file['size'] > $maxSize) {
            throw new InvalidArgumentException('File too large. Max: ' . ($maxSize / 1024 / 1024) . 'MB');
        }

        // Generate document number
        $docNumber = 'DOC-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

        // Calculate file hash
        $fileHash = hash_file('sha256', $file['tmp_name']);

        // Check for duplicates
        $existing = $this->conn->fetch("SELECT id FROM employee_documents WHERE file_hash = ? AND tenant_id = ?", [$fileHash, $this->tenantId()]);
        if ($existing) throw new InvalidArgumentException('Document already uploaded (duplicate file)');

        // Save file
        $uploadDir = PUBLIC_PATH . '/assets/uploads/documents/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $fileName = $docNumber . '_' . $file['name'];
        $filePath = 'assets/uploads/documents/' . $fileName;
        move_uploaded_file($file['tmp_name'], $uploadDir . $fileName);

        $columns = array_merge(
            ['document_number', 'employee_id', 'category_id', 'document_name', 'file_path', 'file_name', 'file_size', 'mime_type', 'file_hash',
             'issue_date', 'expiry_date', 'issuing_authority', 'document_number_ref', 'verification_status',
             'is_confidential', 'access_level', 'uploaded_by', 'uploaded_at'],
            array_keys($this->tenantInsertData())
        );

        $values = array_merge([
            $docNumber, $employeeId, $data['category_id'], $data['document_name'], $filePath,
            $file['name'], $file['size'], $file['type'], $fileHash,
            $data['issue_date'] ?? null, $data['expiry_date'] ?? null, $data['issuing_authority'] ?? null,
            $data['document_number_ref'] ?? null, 'not_verified',
            $data['is_confidential'] ?? false, $data['access_level'] ?? 'self',
            $data['uploaded_by'] ?? 1, date('Y-m-d H:i:s')
        ], array_values($this->tenantInsertData()));

        $placeholders = str_repeat('?,', count($columns) - 1) . '?';
        $sql = "INSERT INTO employee_documents (" . implode(', ', $columns) . ") VALUES ($placeholders)";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($values);

        return ['success' => true, 'id' => (int)$this->conn->lastInsertId(), 'document_number' => $docNumber, 'message' => 'Document uploaded'];
    }

    public function verifyDocument(int $id, int $verifierId, string $status, string $reason = ''): array
    {
        $status = in_array($status, ['verified', 'rejected']) ? $status : 'verified';
        $fields = ['verification_status = ?', 'verified_by = ?', 'verified_at = NOW()'];
        $params = [$status, $verifierId];

        if ($status === 'rejected') {
            $fields[] = 'rejection_reason = ?';
            $params[] = $reason;
        }

        $params[] = $id; $params[] = $this->tenantId();
        $sql = "UPDATE employee_documents SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ? AND tenant_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return ['success' => $stmt->rowCount() > 0, 'message' => $stmt->rowCount() > 0 ? 'Document ' . $status : 'Not found'];
    }

    public function getExpiringDocuments(int $days = 30): array
    {
        $stmt = $this->conn->prepare("
            SELECT ed.*, dc.name as category_name, u.name as employee_name, u.email as employee_email
            FROM employee_documents ed
            JOIN document_categories dc ON ed.category_id = dc.id
            JOIN employees e ON ed.employee_id = e.id
            JOIN users u ON e.user_id = u.id
            WHERE ed.tenant_id = ? AND ed.expiry_date IS NOT NULL
            AND ed.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
            AND ed.verification_status = 'verified'
            ORDER BY ed.expiry_date ASC
        ");
        $stmt->execute([$this->tenantId(), $days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==================== DOCUMENT REQUESTS ====================

    public function getRequests(array $filters = []): array
    {
        $where = ['dr.tenant_id = ?'];
        $params = [$this->tenantId()];

        if (!empty($filters['employee_id'])) { $where[] = 'dr.employee_id = ?'; $params[] = $filters['employee_id']; }
        if (!empty($filters['category_id'])) { $where[] = 'dr.category_id = ?'; $params[] = $filters['category_id']; }
        if (!empty($filters['status'])) { $where[] = 'dr.status = ?'; $params[] = $filters['status']; }

        $whereSql = 'WHERE ' . implode(' AND ', $where);
        $sql = "SELECT dr.*, dc.name as category_name, u.name as employee_name, u.email as employee_email,
                    r.name as requested_by_name
                FROM document_requests dr
                JOIN document_categories dc ON dr.category_id = dc.id
                JOIN employees e ON dr.employee_id = e.id
                JOIN users u ON e.user_id = u.id
                JOIN users r ON dr.requested_by = r.id
                WHERE $whereSql
                ORDER BY dr.due_date ASC";

        $params[] = $this->tenantId();
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(array_merge([$this->tenantId()], $params));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createRequest(array $data): array
    {
        $required = ['employee_id', 'category_id', 'due_date'];
        foreach ($required as $field) {
            if (empty($data[$field])) throw new InvalidArgumentException("$field is required");
        }

        $reqNumber = 'DOCREQ-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $columns = array_merge(
            ['request_number', 'employee_id', 'category_id', 'requested_by', 'due_date', 'priority', 'message', 'status', 'created_at'],
            array_keys($this->tenantInsertData())
        );

        $values = array_merge([
            $reqNumber, $data['employee_id'], $data['category_id'], $data['requested_by'],
            $data['due_date'], $data['priority'] ?? 'normal', $data['message'] ?? null,
            'pending', date('Y-m-d H:i:s')
        ], array_values($this->tenantInsertData()));

        $placeholders = str_repeat('?,', count($columns) - 1) . '?';
        $sql = "INSERT INTO document_requests (" . implode(', ', $columns) . ") VALUES ($placeholders)";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($values);

        return ['success' => true, 'id' => (int)$this->conn->lastInsertId(), 'request_number' => $reqNumber, 'message' => 'Request created'];
    }

    public function submitDocument(int $requestId, int $employeeId, array $data, $file): array
    {
        $req = $this->conn->fetch("SELECT * FROM document_requests WHERE id = ? AND employee_id = ? AND tenant_id = ?", [$requestId, $employeeId, $this->tenantId()]);
        if (!$req) throw new InvalidArgumentException('Request not found');

        // Upload document
        $docData = [
            'category_id' => $req['category_id'],
            'document_name' => $data['document_name'] ?? 'Document for ' . $req['request_number'],
            'issue_date' => $data['issue_date'] ?? date('Y-m-d'),
            'expiry_date' => $data['expiry_date'] ?? null,
            'issuing_authority' => $data['issuing_authority'] ?? null,
            'document_number_ref' => $data['document_number_ref'] ?? null,
            'uploaded_by' => $employeeId
        ];
        $docResult = $this->uploadDocument($employeeId, $docData, $file);

        // Update request
        $this->conn->execute("UPDATE document_requests SET status = 'submitted', submitted_at = NOW() WHERE id = ? AND tenant_id = ?", [$requestId, $this->tenantId()]);

        return $docResult;
    }

    public function verifyRequest(int $id, int $verifierId, string $status, string $reason = ''): array
    {
        $status = in_array($status, ['verified', 'rejected']) ? $status : 'verified';
        $fields = ['status = ?', 'verified_by = ?', 'verified_at = NOW()'];
        $params = [$status, $verifierId];

        if ($status === 'rejected') {
            $fields[] = 'rejection_reason = ?';
            $params[] = $reason;
        }

        $params[] = $id; $params[] = $this->tenantId();
        $sql = "UPDATE document_requests SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ? AND tenant_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return ['success' => $stmt->rowCount() > 0, 'message' => $stmt->rowCount() > 0 ? 'Request ' . $status : 'Not found'];
    }

    public function sendReminders(): int
    {
        $requests = $this->conn->fetchAll("
            SELECT dr.*, u.email as employee_email, u.name as employee_name, dc.name as category_name
            FROM document_requests dr
            JOIN employees e ON dr.employee_id = e.id
            JOIN users u ON e.user_id = u.id
            JOIN document_categories dc ON dr.category_id = dc.id
            WHERE dr.tenant_id = ? AND dr.status = 'pending' AND dr.due_date <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)
            AND (dr.last_reminder_at IS NULL OR dr.last_reminder_at < DATE_SUB(NOW(), INTERVAL 24 HOUR))
        ", [$this->tenantId()]);

        $sent = 0;
        foreach ($requests as $req) {
            // TODO: Send email/WhatsApp notification
            $this->conn->execute("UPDATE document_requests SET reminder_count = reminder_count + 1, last_reminder_at = NOW() WHERE id = ? AND tenant_id = ?", [$req['id'], $this->tenantId()]);
            $sent++;
        }
        return $sent;
    }

    public function getStats(): array
    {
        $stats = [];
        $stmt = $this->conn->prepare('SELECT COUNT(*) as c, verification_status as status FROM employee_documents WHERE tenant_id = ? GROUP BY verification_status');
        $stmt->execute([$this->tenantId()]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) $stats['docs_by_status'][$row['status']] = $row['c'];

        $stmt = $this->conn->prepare('SELECT COUNT(*) as c FROM employee_documents WHERE tenant_id = ? AND expiry_date IS NOT NULL AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND verification_status = "verified"');
        $stmt->execute([$this->tenantId()]);
        $stats['expiring_30_days'] = $stmt->fetchColumn();

        $stmt = $this->conn->prepare('SELECT COUNT(*) as c FROM document_requests WHERE tenant_id = ? AND status = "pending"');
        $stmt->execute([$this->tenantId()]);
        $stats['pending_requests'] = $stmt->fetchColumn();

        return $stats;
    }
}