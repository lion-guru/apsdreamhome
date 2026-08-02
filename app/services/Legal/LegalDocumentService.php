<?php

namespace App\Services\Legal;

use App\Core\Database;
use App\Core\Middleware\TenantContext;
use PDO;

class LegalDocumentService
{
    protected PDO $db;
    protected int $tenantId;

    public function __construct(?PDO $pdo = null)
    {
        if ($pdo) {
            $this->db = $pdo;
        } else {
            $inst = Database::getInstance();
            $this->db = method_exists($inst, 'getConnection') ? $inst->getConnection() : $inst;
        }
        $this->tenantId = TenantContext::getId();
    }

    // ===== DOCUMENT CATEGORIES =====

    public function getCategories(): array
    {
        try {
            $tid = $this->tenantId;
            $sql = "SELECT c.*, (SELECT COUNT(*) FROM legal_document_templates t WHERE t.category_id = c.id AND t.status != 'archived'";
            $params = [];
            if ($tid > 1) { $sql .= " AND t.tenant_id = ?"; $params[] = $tid; }
            $sql .= ") as template_count FROM legal_document_categories c WHERE c.is_active = 1";
            if ($tid > 1) { $sql .= " AND c.tenant_id = ?"; $params[] = $tid; }
            $sql .= " ORDER BY c.sort_order, c.name";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            error_log('LegalDocumentService::getCategories error: ' . $e->getMessage());
            return [];
        }
    }

    public function getCategoryById(int $id): ?array
    {
        try {
            $tid = $this->tenantId;
            $sql = "SELECT * FROM legal_document_categories WHERE id = ?";
            $params = [$id];
            if ($tid > 1) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\Exception $e) {
            error_log('LegalDocumentService::getCategoryById error: ' . $e->getMessage());
            return null;
        }
    }

    public function getCategoryBySlug(string $slug): ?array
    {
        try {
            $tid = $this->tenantId;
            $sql = "SELECT * FROM legal_document_categories WHERE slug = ?";
            $params = [$slug];
            if ($tid > 1) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\Exception $e) {
            error_log('LegalDocumentService::getCategoryBySlug error: ' . $e->getMessage());
            return null;
        }
    }

    public function createCategory(array $data): array
    {
        try {
            $slug = $data['slug'] ?? strtolower(str_replace(' ', '-', trim($data['name'])));
            $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
            $stmt = $this->db->prepare("INSERT INTO legal_document_categories (name, slug, description, icon, parent_id, sort_order, is_active, created_by, tenant_id) VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?)");
            $stmt->execute([
                $data['name'],
                $slug,
                $data['description'] ?? null,
                $data['icon'] ?? 'fas fa-file-contract',
                $data['parent_id'] ?? null,
                (int)($data['sort_order'] ?? 0),
                $data['created_by'] ?? null,
                $this->tenantId
            ]);
            return ['success' => true, 'id' => (int)$this->db->lastInsertId()];
        } catch (\Exception $e) {
            error_log('LegalDocumentService::createCategory error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function updateCategory(int $id, array $data): array
    {
        try {
            $fields = [];
            $params = [];
            $tid = $this->tenantId;
            foreach (['name', 'description', 'icon', 'parent_id', 'sort_order', 'is_active'] as $f) {
                if (array_key_exists($f, $data)) {
                    $fields[] = "$f = ?";
                    $params[] = $data[$f];
                }
            }
            if (empty($fields)) return ['success' => false, 'error' => 'No fields to update'];
            if (isset($data['name']) && !isset($data['slug'])) {
                $slug = strtolower(str_replace(' ', '-', trim($data['name'])));
                $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
                $fields[] = "slug = ?";
                $params[] = $slug;
            }
            $params[] = $id;
            $sql = "UPDATE legal_document_categories SET " . implode(', ', $fields) . " WHERE id = ?";
            if ($tid > 1) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return ['success' => true];
        } catch (\Exception $e) {
            error_log('LegalDocumentService::updateCategory error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteCategory(int $id): array
    {
        try {
            $tid = $this->tenantId;
            $sql = "UPDATE legal_document_categories SET is_active = 0 WHERE id = ?";
            $params = [$id];
            if ($tid > 1) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
            $this->db->prepare($sql)->execute($params);
            return ['success' => true];
        } catch (\Exception $e) {
            error_log('LegalDocumentService::deleteCategory error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ===== DOCUMENT TEMPLATES =====

    public function getTemplates(array $filters = []): array
    {
        try {
            $tid = $this->tenantId;
            $sql = "SELECT t.*, c.name as category_name, c.icon as category_icon,
                (SELECT COUNT(*) FROM legal_documents d WHERE d.template_id = t.id";
            if ($tid > 1) { $sql .= " AND d.tenant_id = ?"; }
            $sql .= ") as usage_count
                FROM legal_document_templates t
                LEFT JOIN legal_document_categories c ON t.category_id = c.id
                WHERE 1=1";
            $params = [];
            if ($tid > 1) { $sql .= " AND t.tenant_id = ?"; $params[] = $tid; }
            if (!empty($filters['category_id'])) {
                $sql .= " AND t.category_id = ?";
                $params[] = (int)$filters['category_id'];
            }
            if (!empty($filters['status'])) {
                $sql .= " AND t.status = ?";
                $params[] = $filters['status'];
            }
            if (!empty($filters['language'])) {
                $sql .= " AND t.language = ?";
                $params[] = $filters['language'];
            }
            if (!empty($filters['search'])) {
                $sql .= " AND (t.name LIKE ? OR t.description LIKE ?)";
                $params[] = '%' . $filters['search'] . '%';
                $params[] = '%' . $filters['search'] . '%';
            }
            $sql .= " ORDER BY t.name";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            error_log('LegalDocumentService::getTemplates error: ' . $e->getMessage());
            return [];
        }
    }

    public function getTemplateById(int $id): ?array
    {
        try {
            $tid = $this->tenantId;
            $sql = "SELECT t.*, c.name as category_name, c.icon as category_icon,
                (SELECT COUNT(*) FROM legal_documents d WHERE d.template_id = t.id";
            if ($tid > 1) { $sql .= " AND d.tenant_id = ?"; }
            $sql .= ") as usage_count
                FROM legal_document_templates t
                LEFT JOIN legal_document_categories c ON t.category_id = c.id
                WHERE t.id = ?";
            $params = [$id];
            if ($tid > 1) { $params[] = $tid; }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\Exception $e) {
            error_log('LegalDocumentService::getTemplateById error: ' . $e->getMessage());
            return null;
        }
    }

    public function createTemplate(array $data): array
    {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("INSERT INTO legal_document_templates (category_id, name, description, content, merge_fields, version, status, is_customer_facing, language, created_by, tenant_id) VALUES (?, ?, ?, ?, ?, 1, ?, ?, ?, ?, ?)");
            $mergeFields = !empty($data['merge_fields']) ? (is_string($data['merge_fields']) ? $data['merge_fields'] : json_encode($data['merge_fields'])) : null;
            $stmt->execute([
                $data['category_id'] ?? null,
                $data['name'],
                $data['description'] ?? null,
                $data['content'],
                $mergeFields,
                $data['status'] ?? 'draft',
                !empty($data['is_customer_facing']) ? 1 : 0,
                $data['language'] ?? 'en',
                $data['created_by'] ?? null,
                $this->tenantId
            ]);
            $templateId = (int)$this->db->lastInsertId();
            $this->saveVersion($templateId, $data['content'], 1, 'Initial version');
            $this->db->commit();
            return ['success' => true, 'id' => $templateId];
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log('LegalDocumentService::createTemplate error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function updateTemplate(int $id, array $data): array
    {
        try {
            $existing = $this->getTemplateById($id);
            if (!$existing) return ['success' => false, 'error' => 'Template not found'];

            $fields = [];
            $params = [];
            foreach (['category_id', 'name', 'description', 'content', 'merge_fields', 'status', 'is_customer_facing', 'language'] as $f) {
                if (array_key_exists($f, $data)) {
                    $fields[] = "$f = ?";
                    $val = $data[$f];
                    if ($f === 'merge_fields' && is_array($val)) $val = json_encode($val);
                    if ($f === 'is_customer_facing') $val = !empty($val) ? 1 : 0;
                    $params[] = $val;
                }
            }
            if (empty($fields)) return ['success' => false, 'error' => 'No fields to update'];

            $this->db->beginTransaction();
            $fields[] = "version = version + 1";
            $params[] = $id;
            $sql = "UPDATE legal_document_templates SET " . implode(', ', $fields) . " WHERE id = ?";
            if ($this->tenantId > 1) { $sql .= " AND tenant_id = ?"; $params[] = $this->tenantId; }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            $newVersion = $existing['version'] + 1;
            $content = $data['content'] ?? $existing['content'];
            $this->saveVersion($id, $content, $newVersion, $data['change_notes'] ?? 'Updated');
            $this->db->commit();
            return ['success' => true];
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log('LegalDocumentService::updateTemplate error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteTemplate(int $id): array
    {
        try {
            $tid = $this->tenantId;
            $sql = "UPDATE legal_document_templates SET status = 'archived' WHERE id = ?";
            $params = [$id];
            if ($tid > 1) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
            $this->db->prepare($sql)->execute($params);
            return ['success' => true];
        } catch (\Exception $e) {
            error_log('LegalDocumentService::deleteTemplate error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function saveVersion(int $templateId, string $content, int $versionNumber, string $changeNotes = ''): void
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO legal_template_versions (template_id, version_number, content, change_notes, created_by, tenant_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$templateId, $versionNumber, $content, $changeNotes, $_SESSION['admin_id'] ?? null, $this->tenantId]);
        } catch (\Exception $e) {
            error_log('LegalDocumentService::saveVersion error: ' . $e->getMessage());
        }
    }

    public function getTemplateVersions(int $templateId): array
    {
        try {
            $tid = $this->tenantId;
            $sql = "SELECT v.*, u.name as created_by_name FROM legal_template_versions v LEFT JOIN users u ON v.created_by = u.id WHERE v.template_id = ?";
            $params = [$templateId];
            if ($tid > 1) { $sql .= " AND v.tenant_id = ?"; $params[] = $tid; }
            $sql .= " ORDER BY v.version_number DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            error_log('LegalDocumentService::getTemplateVersions error: ' . $e->getMessage());
            return [];
        }
    }

    public function restoreTemplateVersion(int $templateId, int $versionNumber): array
    {
        try {
            $tid = $this->tenantId;
            $sql = "SELECT * FROM legal_template_versions WHERE template_id = ? AND version_number = ?";
            $params = [$templateId, $versionNumber];
            if ($tid > 1) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $version = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$version) return ['success' => false, 'error' => 'Version not found'];

            $this->db->beginTransaction();
            $sql2 = "UPDATE legal_document_templates SET content = ?, version = version + 1 WHERE id = ?";
            $params2 = [$version['content'], $templateId];
            if ($tid > 1) { $sql2 .= " AND tenant_id = ?"; $params2[] = $tid; }
            $this->db->prepare($sql2)->execute($params2);

            $newVer = $version['version_number'] + 1;
            $this->saveVersion($templateId, $version['content'], $newVer, 'Restored from v' . $version['version_number']);
            $this->db->commit();
            return ['success' => true];
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log('LegalDocumentService::restoreTemplateVersion error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ===== CLAUSE LIBRARY =====

    public function getClauses(array $filters = []): array
    {
        try {
            $tid = $this->tenantId;
            $sql = "SELECT c.*, cat.name as category_name FROM legal_clause_library c LEFT JOIN legal_document_categories cat ON c.category_id = cat.id WHERE c.is_active = 1";
            $params = [];
            if ($tid > 1) { $sql .= " AND c.tenant_id = ?"; $params[] = $tid; }
            if (!empty($filters['category_id'])) {
                $sql .= " AND c.category_id = ?";
                $params[] = (int)$filters['category_id'];
            }
            if (!empty($filters['search'])) {
                $sql .= " AND (c.title LIKE ? OR c.content LIKE ? OR c.tags LIKE ?)";
                $params[] = '%' . $filters['search'] . '%';
                $params[] = '%' . $filters['search'] . '%';
                $params[] = '%' . $filters['search'] . '%';
            }
            if (!empty($filters['tag'])) {
                $sql .= " AND c.tags LIKE ?";
                $params[] = '%' . $filters['tag'] . '%';
            }
            $sql .= " ORDER BY c.sort_order, c.title";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            error_log('LegalDocumentService::getClauses error: ' . $e->getMessage());
            return [];
        }
    }

    public function getClauseById(int $id): ?array
    {
        try {
            $tid = $this->tenantId;
            $sql = "SELECT c.*, cat.name as category_name FROM legal_clause_library c LEFT JOIN legal_document_categories cat ON c.category_id = cat.id WHERE c.id = ?";
            $params = [$id];
            if ($tid > 1) { $sql .= " AND c.tenant_id = ?"; $params[] = $tid; }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\Exception $e) {
            error_log('LegalDocumentService::getClauseById error: ' . $e->getMessage());
            return null;
        }
    }

    public function createClause(array $data): array
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO legal_clause_library (category_id, title, content, tags, sort_order, created_by, tenant_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['category_id'] ?? null,
                $data['title'],
                $data['content'],
                $data['tags'] ?? null,
                (int)($data['sort_order'] ?? 0),
                $data['created_by'] ?? null,
                $this->tenantId
            ]);
            return ['success' => true, 'id' => (int)$this->db->lastInsertId()];
        } catch (\Exception $e) {
            error_log('LegalDocumentService::createClause error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function updateClause(int $id, array $data): array
    {
        try {
            $fields = [];
            $params = [];
            $tid = $this->tenantId;
            foreach (['category_id', 'title', 'content', 'tags', 'sort_order', 'is_active'] as $f) {
                if (array_key_exists($f, $data)) {
                    $fields[] = "$f = ?";
                    $params[] = $data[$f];
                }
            }
            if (empty($fields)) return ['success' => false, 'error' => 'No fields to update'];
            $params[] = $id;
            $sql = "UPDATE legal_clause_library SET " . implode(', ', $fields) . " WHERE id = ?";
            if ($tid > 1) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return ['success' => true];
        } catch (\Exception $e) {
            error_log('LegalDocumentService::updateClause error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteClause(int $id): array
    {
        try {
            $tid = $this->tenantId;
            $sql = "DELETE FROM legal_clause_library WHERE id = ?";
            $params = [$id];
            if ($tid > 1) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
            $this->db->prepare($sql)->execute($params);
            return ['success' => true];
        } catch (\Exception $e) {
            error_log('LegalDocumentService::deleteClause error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ===== DOCUMENTS (Generated) =====

    public function getDocuments(array $filters = []): array
    {
        try {
            $tid = $this->tenantId;
            $sql = "SELECT d.*, t.name as template_name, c.name as category_name, u.name as customer_name, u.phone as customer_phone,
                cr.name as created_by_name
                FROM legal_documents d
                LEFT JOIN legal_document_templates t ON d.template_id = t.id
                LEFT JOIN legal_document_categories c ON t.category_id = c.id
                LEFT JOIN users u ON d.customer_id = u.id
                LEFT JOIN users cr ON d.created_by = cr.id
                WHERE 1=1";
            $params = [];
            if ($tid > 1) { $sql .= " AND d.tenant_id = ?"; $params[] = $tid; }
            if (!empty($filters['status'])) {
                $sql .= " AND d.status = ?";
                $params[] = $filters['status'];
            }
            if (!empty($filters['entity_type'])) {
                $sql .= " AND d.entity_type = ?";
                $params[] = $filters['entity_type'];
            }
            if (!empty($filters['entity_id'])) {
                $sql .= " AND d.entity_id = ?";
                $params[] = (int)$filters['entity_id'];
            }
            if (!empty($filters['customer_id'])) {
                $sql .= " AND d.customer_id = ?";
                $params[] = (int)$filters['customer_id'];
            }
            if (!empty($filters['category_id'])) {
                $sql .= " AND t.category_id = ?";
                $params[] = (int)$filters['category_id'];
            }
            if (!empty($filters['search'])) {
                $sql .= " AND (d.title LIKE ? OR d.document_number LIKE ?)";
                $params[] = '%' . $filters['search'] . '%';
                $params[] = '%' . $filters['search'] . '%';
            }
            if (!empty($filters['date_from'])) {
                $sql .= " AND d.created_at >= ?";
                $params[] = $filters['date_from'];
            }
            if (!empty($filters['date_to'])) {
                $sql .= " AND d.created_at <= ?";
                $params[] = $filters['date_to'] . ' 23:59:59';
            }
            $sql .= " ORDER BY d.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            error_log('LegalDocumentService::getDocuments error: ' . $e->getMessage());
            return [];
        }
    }

    public function getDocumentById(int $id): ?array
    {
        try {
            $tid = $this->tenantId;
            $sql = "SELECT d.*, t.name as template_name, t.content as template_content, t.merge_fields,
                c.name as category_name, c.slug as category_slug,
                u.name as customer_name, u.phone as customer_phone, u.email as customer_email, u.address as customer_address,
                cr.name as created_by_name
                FROM legal_documents d
                LEFT JOIN legal_document_templates t ON d.template_id = t.id
                LEFT JOIN legal_document_categories c ON t.category_id = c.id
                LEFT JOIN users u ON d.customer_id = u.id
                LEFT JOIN users cr ON d.created_by = cr.id
                WHERE d.id = ?";
            $params = [$id];
            if ($tid > 1) { $sql .= " AND d.tenant_id = ?"; $params[] = $tid; }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\Exception $e) {
            error_log('LegalDocumentService::getDocumentById error: ' . $e->getMessage());
            return null;
        }
    }

    public function generateDocumentNumber(string $prefix = 'LEG'): string
    {
        try {
            $year = date('Y');
            $tid = $this->tenantId;
            $sql = "SELECT COUNT(*) FROM legal_documents WHERE document_number LIKE ?";
            $params = [$prefix . '-' . $year . '-%'];
            if ($tid > 1) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $count = (int)$stmt->fetchColumn() + 1;
            return $prefix . '-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
        } catch (\Exception $e) {
            return $prefix . '-' . date('Y') . '-' . strtoupper(substr(uniqid(), -4));
        }
    }

    public function createDocument(array $data): array
    {
        try {
            $this->db->beginTransaction();

            $docNumber = $data['document_number'] ?? $this->generateDocumentNumber();
            $content = $data['content'] ?? null;

            // If no content but template_id provided, merge from template
            if (empty($content) && !empty($data['template_id'])) {
                $template = $this->getTemplateById((int)$data['template_id']);
                if ($template) {
                    $content = $this->mergeFields($template['content'], $data['merge_data'] ?? []);
                }
            }

            $stmt = $this->db->prepare("INSERT INTO legal_documents (template_id, entity_type, entity_id, customer_id, title, document_number, content, status, effective_date, expiry_date, notes, created_by, tenant_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['template_id'] ?? null,
                $data['entity_type'] ?? 'general',
                $data['entity_id'] ?? null,
                $data['customer_id'] ?? null,
                $data['title'],
                $docNumber,
                $content,
                $data['status'] ?? 'draft',
                $data['effective_date'] ?? date('Y-m-d'),
                $data['expiry_date'] ?? null,
                $data['notes'] ?? null,
                $data['created_by'] ?? null,
                $this->tenantId
            ]);
            $docId = (int)$this->db->lastInsertId();
            $this->db->commit();
            return ['success' => true, 'id' => $docId, 'document_number' => $docNumber];
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log('LegalDocumentService::createDocument error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function updateDocument(int $id, array $data): array
    {
        try {
            $fields = [];
            $params = [];
            foreach (['title', 'content', 'status', 'entity_type', 'entity_id', 'customer_id', 'effective_date', 'expiry_date', 'notes'] as $f) {
                if (array_key_exists($f, $data)) {
                    $fields[] = "$f = ?";
                    $params[] = $data[$f];
                }
            }
            if (empty($fields)) return ['success' => false, 'error' => 'No fields to update'];
            $params[] = $id;
            $sql = "UPDATE legal_documents SET " . implode(', ', $fields) . " WHERE id = ?";
            if ($this->tenantId > 1) { $sql .= " AND tenant_id = ?"; $params[] = $this->tenantId; }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return ['success' => true];
        } catch (\Exception $e) {
            error_log('LegalDocumentService::updateDocument error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function updateDocumentStatus(int $id, string $status): array
    {
        try {
            $valid = ['active', 'inactive'];
            if (!in_array($status, $valid, true)) {
                $status = 'active';
            }
            $tid = $this->tenantId;
            $sql = "UPDATE legal_documents SET status = ? WHERE id = ?";
            $params = [$status, $id];
            if ($tid > 1) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
            $this->db->prepare($sql)->execute($params);
            return ['success' => true];
        } catch (\Exception $e) {
            error_log('LegalDocumentService::updateDocumentStatus error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function markSubmittedOnline(int $id): array
    {
        try {
            $tid = $this->tenantId;
            $sql = "UPDATE legal_documents SET submitted_online = 1, submitted_online_at = NOW(), status = 'final' WHERE id = ?";
            $params = [$id];
            if ($tid > 1) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
            $this->db->prepare($sql)->execute($params);
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function markSubmittedPhysically(int $id): array
    {
        try {
            $tid = $this->tenantId;
            $sql = "UPDATE legal_documents SET submitted_physically = 1, submitted_physically_at = NOW(), status = 'final' WHERE id = ?";
            $params = [$id];
            if ($tid > 1) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
            $this->db->prepare($sql)->execute($params);
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function markKycVerified(int $id, int $verifiedBy): array
    {
        try {
            $tid = $this->tenantId;
            $sql = "UPDATE legal_documents SET kyc_verified = 1, kyc_verified_at = NOW(), kyc_verified_by = ? WHERE id = ?";
            $params = [$verifiedBy, $id];
            if ($tid > 1) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
            $this->db->prepare($sql)->execute($params);
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Sign a document digitally
     * Creates a record in booking_document_signatures table
     */
    public function signDocument(int $documentId, array $signatureData): array
    {
        try {
            $this->db->beginTransaction();
            
            // Check if document exists
            $doc = $this->getDocumentById($documentId);
            if (!$doc) {
                return ['success' => false, 'error' => 'Document not found'];
            }
            
            // Insert signature record
            $insertCols = "document_id, booking_id, customer_id, signature_data, signature_type, signed_at, ip_address, user_agent, video_consent, video_url";
            $insertVals = "?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?";
            $execParams = [
                $documentId,
                $doc['entity_id'] ?? 0,
                $doc['customer_id'] ?? null,
                $signatureData['signature_data'] ?? '',
                $signatureData['signature_type'] ?? 'digital',
                $signatureData['ip_address'] ?? '',
                $signatureData['user_agent'] ?? '',
                !empty($signatureData['video_consent']) ? 1 : 0,
                $signatureData['video_url'] ?? null,
            ];
            if ($this->tenantId > 1) {
                $insertCols .= ", tenant_id";
                $insertVals .= ", ?";
                $execParams[] = $this->tenantId;
            }
            $stmt = $this->db->prepare("
                INSERT INTO booking_document_signatures 
                ($insertCols)
                VALUES ($insertVals)
                ON DUPLICATE KEY UPDATE
                    signature_data = VALUES(signature_data),
                    signature_type = VALUES(signature_type),
                    signed_at = VALUES(signed_at),
                    video_consent = VALUES(video_consent),
                    video_url = VALUES(video_url)
            ");
            $stmt->execute($execParams);
            
            // Update document status to signed
            $sqlDoc = "UPDATE legal_documents SET status = 'active' WHERE id = ?";
            $docParams = [$documentId];
            if ($this->tenantId > 1) { $sqlDoc .= " AND tenant_id = ?"; $docParams[] = $this->tenantId; }
            $this->db->prepare($sqlDoc)->execute($docParams);
            
            $this->db->commit();
            return ['success' => true, 'document' => ['id' => $documentId, 'status' => 'signed']];
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log('LegalDocumentService::signDocument error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteDocument(int $id): array
    {
        try {
            $tid = $this->tenantId;
            $sql = "UPDATE legal_documents SET status = 'archived' WHERE id = ?";
            $params = [$id];
            if ($tid > 1) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
            $this->db->prepare($sql)->execute($params);
            return ['success' => true];
        } catch (\Exception $e) {
            error_log('LegalDocumentService::deleteDocument error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ===== DOCUMENT UPLOADS =====

    public function getUploads(int $documentId): array
    {
        try {
            $tid = $this->tenantId;
            $sql = "SELECT u.*, uv.name as verified_by_name FROM legal_document_uploads u LEFT JOIN users uv ON u.verified_by = uv.id WHERE u.document_id = ?";
            $params = [$documentId];
            if ($tid > 1) { $sql .= " AND u.tenant_id = ?"; $params[] = $tid; }
            $sql .= " ORDER BY u.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            error_log('LegalDocumentService::getUploads error: ' . $e->getMessage());
            return [];
        }
    }

    public function getUploadById(int $id): ?array
    {
        try {
            $tid = $this->tenantId;
            $sql = "SELECT * FROM legal_document_uploads WHERE id = ?";
            $params = [$id];
            if ($tid > 1) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function createUpload(array $data): array
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO legal_document_uploads (document_id, customer_id, file_path, file_name, file_type, file_size, upload_type, status, notes, tenant_id) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?)");
            $stmt->execute([
                $data['document_id'] ?? null,
                $data['customer_id'] ?? null,
                $data['file_path'],
                $data['file_name'],
                $data['file_type'] ?? null,
                (int)($data['file_size'] ?? 0),
                $data['upload_type'] ?? 'other',
                $data['notes'] ?? null,
                $this->tenantId
            ]);
            return ['success' => true, 'id' => (int)$this->db->lastInsertId()];
        } catch (\Exception $e) {
            error_log('LegalDocumentService::createUpload error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function verifyUpload(int $id, int $verifiedBy, string $status, ?string $reason = null): array
    {
        try {
            $tid = $this->tenantId;
            $sql = "UPDATE legal_document_uploads SET status = ?, verified_by = ?, verified_at = NOW(), rejection_reason = ? WHERE id = ?";
            $params = [$status, $verifiedBy, $reason, $id];
            if ($tid > 1) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
            $this->db->prepare($sql)->execute($params);
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteUpload(int $id): array
    {
        try {
            $upload = $this->getUploadById($id);
            if ($upload && file_exists($upload['file_path'])) {
                unlink($upload['file_path']);
            }
            $tid = $this->tenantId;
            $sql = "DELETE FROM legal_document_uploads WHERE id = ?";
            $params = [$id];
            if ($tid > 1) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
            $this->db->prepare($sql)->execute($params);
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ===== AI PROMPTS =====

    public function getAiPrompts(array $filters = []): array
    {
        try {
            $tid = $this->tenantId;
            $sql = "SELECT * FROM legal_ai_prompts WHERE is_active = 1";
            $params = [];
            if ($tid > 1) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
            if (!empty($filters['category'])) {
                $sql .= " AND document_category = ?";
                $params[] = $filters['category'];
            }
            $sql .= " ORDER BY name";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getAiPromptById(int $id): ?array
    {
        try {
            $tid = $this->tenantId;
            $sql = "SELECT * FROM legal_ai_prompts WHERE id = ?";
            $params = [$id];
            if ($tid > 1) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function createAiPrompt(array $data): array
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO legal_ai_prompts (name, description, prompt_template, document_category, model, temperature, max_tokens, created_by, tenant_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['name'],
                $data['description'] ?? null,
                $data['prompt_template'],
                $data['document_category'] ?? null,
                $data['model'] ?? 'gemini',
                (float)($data['temperature'] ?? 0.30),
                (int)($data['max_tokens'] ?? 2048),
                $data['created_by'] ?? null,
                $this->tenantId
            ]);
            return ['success' => true, 'id' => (int)$this->db->lastInsertId()];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function updateAiPrompt(int $id, array $data): array
    {
        try {
            $fields = [];
            $params = [];
            $tid = $this->tenantId;
            foreach (['name', 'description', 'prompt_template', 'document_category', 'model', 'temperature', 'max_tokens', 'is_active'] as $f) {
                if (array_key_exists($f, $data)) {
                    $fields[] = "$f = ?";
                    $params[] = $data[$f];
                }
            }
            if (empty($fields)) return ['success' => false, 'error' => 'No fields to update'];
            $params[] = $id;
            $sql = "UPDATE legal_ai_prompts SET " . implode(', ', $fields) . " WHERE id = ?";
            if ($tid > 1) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteAiPrompt(int $id): array
    {
        try {
            $tid = $this->tenantId;
            $sql = "UPDATE legal_ai_prompts SET is_active = 0 WHERE id = ?";
            $params = [$id];
            if ($tid > 1) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
            $this->db->prepare($sql)->execute($params);
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ===== MERGE FIELDS =====

    public function getAvailableMergeFields(): array
    {
        return [
            'customer' => [
                '{{customer_name}}' => 'Customer Full Name',
                '{{customer_phone}}' => 'Customer Phone',
                '{{customer_email}}' => 'Customer Email',
                '{{customer_address}}' => 'Customer Address',
            ],
            'property' => [
                '{{plot_no}}' => 'Plot Number',
                '{{colony_name}}' => 'Colony/Society Name',
                '{{colony_address}}' => 'Colony Address',
                '{{plot_area}}' => 'Plot Area (sq.yds)',
                '{{plot_price}}' => 'Plot Total Price',
            ],
            'booking' => [
                '{{booking_date}}' => 'Booking Date',
                '{{booking_id}}' => 'Booking ID/Reference',
                '{{booking_amount}}' => 'Booking Amount Paid',
                '{{payment_terms}}' => 'Payment Terms Summary',
            ],
            'associate' => [
                '{{associate_name}}' => 'Associate Name',
                '{{associate_code}}' => 'Associate Code',
                '{{associate_level}}' => 'Associate Level/Rank',
                '{{commission_rate}}' => 'Commission Rate (%)',
            ],
            'company' => [
                '{{company_name}}' => 'Company Name',
                '{{company_address}}' => 'Company Address',
                '{{current_date}}' => 'Current Date',
                '{{current_year}}' => 'Current Year',
            ],
            'cancellation' => [
                '{{cancellation_date}}' => 'Cancellation Date',
                '{{cancellation_reason}}' => 'Cancellation Reason',
                '{{refund_amount}}' => 'Refund Amount',
            ],
            'transfer' => [
                '{{transfer_from_colony}}' => 'Transfer From Colony',
                '{{transfer_to_colony}}' => 'Transfer To Colony',
                '{{transfer_date}}' => 'Transfer Date',
            ],
            'loan' => [
                '{{loan_amount}}' => 'Loan Amount',
                '{{interest_rate}}' => 'Interest Rate (%)',
                '{{emi_amount}}' => 'EMI Amount',
                '{{tenure}}' => 'Loan Tenure (months)',
                '{{due_date}}' => 'Payment Due Date',
                '{{pending_amount}}' => 'Pending/Outstanding Amount',
            ],
        ];
    }

    public function mergeFields(string $content, array $data): string
    {
        $replacements = [
            '{{customer_name}}' => $data['customer_name'] ?? '',
            '{{customer_phone}}' => $data['customer_phone'] ?? '',
            '{{customer_email}}' => $data['customer_email'] ?? '',
            '{{customer_address}}' => $data['customer_address'] ?? '',
            '{{plot_no}}' => $data['plot_no'] ?? '',
            '{{colony_name}}' => $data['colony_name'] ?? '',
            '{{colony_address}}' => $data['colony_address'] ?? '',
            '{{plot_area}}' => $data['plot_area'] ?? '',
            '{{plot_price}}' => isset($data['plot_price']) ? '₹' . number_format((float)$data['plot_price']) : '',
            '{{booking_date}}' => $data['booking_date'] ?? '',
            '{{booking_id}}' => $data['booking_id'] ?? '',
            '{{booking_amount}}' => isset($data['booking_amount']) ? '₹' . number_format((float)$data['booking_amount']) : '',
            '{{payment_terms}}' => $data['payment_terms'] ?? '',
            '{{associate_name}}' => $data['associate_name'] ?? '',
            '{{associate_code}}' => $data['associate_code'] ?? '',
            '{{associate_level}}' => $data['associate_level'] ?? '',
            '{{commission_rate}}' => $data['commission_rate'] ?? '',
            '{{company_name}}' => $data['company_name'] ?? 'APS Dream Home',
            '{{company_address}}' => $data['company_address'] ?? 'APS Dream Home, [City]',
            '{{current_date}}' => date('d/m/Y'),
            '{{current_year}}' => date('Y'),
            '{{cancellation_date}}' => $data['cancellation_date'] ?? '',
            '{{cancellation_reason}}' => $data['cancellation_reason'] ?? '',
            '{{refund_amount}}' => isset($data['refund_amount']) ? '₹' . number_format((float)$data['refund_amount']) : '',
            '{{transfer_from_colony}}' => $data['transfer_from_colony'] ?? '',
            '{{transfer_to_colony}}' => $data['transfer_to_colony'] ?? '',
            '{{transfer_date}}' => $data['transfer_date'] ?? '',
            '{{loan_amount}}' => isset($data['loan_amount']) ? '₹' . number_format((float)$data['loan_amount']) : '',
            '{{interest_rate}}' => $data['interest_rate'] ?? '',
            '{{emi_amount}}' => isset($data['emi_amount']) ? '₹' . number_format((float)$data['emi_amount']) : '',
            '{{tenure}}' => $data['tenure'] ?? '',
            '{{due_date}}' => $data['due_date'] ?? '',
            '{{pending_amount}}' => isset($data['pending_amount']) ? '₹' . number_format((float)$data['pending_amount']) : '',
        ];
        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    // ===== DASHBOARD STATS =====

    public function getDashboardStats(): array
    {
        try {
            $tid = $this->tenantId;
            $stats = [];

            $stmt1 = $this->db->prepare("SELECT COUNT(*) FROM legal_documents");
            $p1 = [];
            if ($tid > 1) { $stmt1 = $this->db->prepare("SELECT COUNT(*) FROM legal_documents WHERE tenant_id = ?"); $p1[] = $tid; }
            $stmt1->execute($p1);
            $stats['total_documents'] = (int)$stmt1->fetchColumn();

            $stmt2 = $this->db->prepare("SELECT COUNT(*) FROM legal_documents WHERE status = 'draft'");
            $p2 = [];
            if ($tid > 1) { $stmt2 = $this->db->prepare("SELECT COUNT(*) FROM legal_documents WHERE status = 'draft' AND tenant_id = ?"); $p2[] = $tid; }
            $stmt2->execute($p2);
            $stats['draft_documents'] = (int)$stmt2->fetchColumn();

            $stmt3 = $this->db->prepare("SELECT COUNT(*) FROM legal_documents WHERE status = 'final'");
            $p3 = [];
            if ($tid > 1) { $stmt3 = $this->db->prepare("SELECT COUNT(*) FROM legal_documents WHERE status = 'final' AND tenant_id = ?"); $p3[] = $tid; }
            $stmt3->execute($p3);
            $stats['final_documents'] = (int)$stmt3->fetchColumn();

            $stmt4 = $this->db->prepare("SELECT COUNT(*) FROM legal_documents WHERE status = 'signed'");
            $p4 = [];
            if ($tid > 1) { $stmt4 = $this->db->prepare("SELECT COUNT(*) FROM legal_documents WHERE status = 'signed' AND tenant_id = ?"); $p4[] = $tid; }
            $stmt4->execute($p4);
            $stats['signed_documents'] = (int)$stmt4->fetchColumn();

            $stmt5 = $this->db->prepare("SELECT COUNT(*) FROM legal_document_templates WHERE status = 'active'");
            $p5 = [];
            if ($tid > 1) { $stmt5 = $this->db->prepare("SELECT COUNT(*) FROM legal_document_templates WHERE status = 'active' AND tenant_id = ?"); $p5[] = $tid; }
            $stmt5->execute($p5);
            $stats['active_templates'] = (int)$stmt5->fetchColumn();

            $stmt6 = $this->db->prepare("SELECT COUNT(*) FROM legal_document_categories WHERE is_active = 1");
            $p6 = [];
            if ($tid > 1) { $stmt6 = $this->db->prepare("SELECT COUNT(*) FROM legal_document_categories WHERE is_active = 1 AND tenant_id = ?"); $p6[] = $tid; }
            $stmt6->execute($p6);
            $stats['total_categories'] = (int)$stmt6->fetchColumn();

            $stmt7 = $this->db->prepare("SELECT COUNT(*) FROM legal_clause_library WHERE is_active = 1");
            $p7 = [];
            if ($tid > 1) { $stmt7 = $this->db->prepare("SELECT COUNT(*) FROM legal_clause_library WHERE is_active = 1 AND tenant_id = ?"); $p7[] = $tid; }
            $stmt7->execute($p7);
            $stats['total_clauses'] = (int)$stmt7->fetchColumn();

            $stmt8 = $this->db->prepare("SELECT COUNT(*) FROM legal_documents WHERE kyc_verified = 0 AND submitted_online = 1");
            $p8 = [];
            if ($tid > 1) { $stmt8 = $this->db->prepare("SELECT COUNT(*) FROM legal_documents WHERE kyc_verified = 0 AND submitted_online = 1 AND tenant_id = ?"); $p8[] = $tid; }
            $stmt8->execute($p8);
            $stats['pending_kyc'] = (int)$stmt8->fetchColumn();

            $stmt9 = $this->db->prepare("SELECT COUNT(*) FROM legal_document_uploads WHERE status = 'pending'");
            $p9 = [];
            if ($tid > 1) { $stmt9 = $this->db->prepare("SELECT COUNT(*) FROM legal_document_uploads WHERE status = 'pending' AND tenant_id = ?"); $p9[] = $tid; }
            $stmt9->execute($p9);
            $stats['pending_uploads'] = (int)$stmt9->fetchColumn();

            $sql10 = "SELECT d.id, d.title, d.document_number, d.status, d.created_at, u.name as customer_name FROM legal_documents d LEFT JOIN users u ON d.customer_id = u.id WHERE 1=1";
            $p10 = [];
            if ($tid > 1) { $sql10 .= " AND d.tenant_id = ?"; $p10[] = $tid; }
            $sql10 .= " ORDER BY d.created_at DESC LIMIT 10";
            $stmt10 = $this->db->prepare($sql10);
            $stmt10->execute($p10);
            $stats['recent_documents'] = $stmt10->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $sql11 = "SELECT c.name, c.icon, COUNT(d.id) as count FROM legal_document_categories c LEFT JOIN legal_document_templates t ON t.category_id = c.id LEFT JOIN legal_documents d ON d.template_id = t.id WHERE c.is_active = 1";
            $p11 = [];
            if ($tid > 1) { $sql11 .= " AND c.tenant_id = ?"; $p11[] = $tid; }
            $sql11 .= " GROUP BY c.id ORDER BY count DESC";
            $stmt11 = $this->db->prepare($sql11);
            $stmt11->execute($p11);
            $stats['documents_by_category'] = $stmt11->fetchAll(PDO::FETCH_ASSOC) ?: [];

            return $stats;
        } catch (\Exception $e) {
            error_log('LegalDocumentService::getDashboardStats error: ' . $e->getMessage());
            return [];
        }
    }

    // ===== ENTITY DATA HELPERS =====

    public function getCustomers(): array
    {
        try {
            $tid = $this->tenantId;
            $sql = "SELECT id, name, phone, email FROM users WHERE role IN ('customer', 'associate')";
            $params = [];
            if ($tid > 1) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
            $sql .= " ORDER BY name";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getBookings(): array
    {
        try {
            $tid = $this->tenantId;
            $sql = "SELECT b.id, b.booking_number, u.name as customer_name, p.plot_number, c.name as colony_name FROM plot_bookings b JOIN users u ON b.user_id = u.id LEFT JOIN plots p ON b.plot_id = p.id LEFT JOIN colonies c ON p.colony_id = c.id WHERE 1=1";
            $params = [];
            if ($tid > 1) { $sql .= " AND b.tenant_id = ?"; $params[] = $tid; }
            $sql .= " ORDER BY b.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getPlots(): array
    {
        try {
            $tid = $this->tenantId;
            $sql = "SELECT p.id, p.plot_number, c.name as colony_name, p.total_price FROM plots p JOIN colonies c ON p.colony_id = c.id WHERE p.status = 'available'";
            $params = [];
            if ($tid > 1) { $sql .= " AND p.tenant_id = ?"; $params[] = $tid; }
            $sql .= " ORDER BY c.name, p.plot_number";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getAssociates(): array
    {
        try {
            $tid = $this->tenantId;
            $sql = "SELECT id, name, code as associate_code, level FROM associates WHERE status = 'active'";
            $params = [];
            if ($tid > 1) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
            $sql .= " ORDER BY name";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getColonies(): array
    {
        try {
            $tid = $this->tenantId;
            $sql = "SELECT id, name, address as colony_address FROM colonies WHERE status = 'active'";
            $params = [];
            if ($tid > 1) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
            $sql .= " ORDER BY name";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }
}
