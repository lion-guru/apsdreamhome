<?php

namespace App\Models;

use App\Core\Database;
use DateTime;

/**
 * Document Repository Model
 * Handles employee document management, categories, types, and sharing
 */
class Document extends Model
{
    protected $table = 'employee_documents';
    protected $fillable = [
        'employee_id',
        'document_type_id',
        'title',
        'description',
        'file_name',
        'original_name',
        'file_path',
        'file_size',
        'file_extension',
        'mime_type',
        'version',
        'is_latest',
        'status',
        'uploaded_by',
        'expires_at',
        'metadata',
        'created_at',
        'updated_at'
    ];

    const STATUS_ACTIVE = 'active';
    const STATUS_ARCHIVED = 'archived';
    const STATUS_DELETED = 'deleted';

    /**
     * Upload a document for an employee
     */
    public function uploadDocument(array $data, array $file): array
    {
        $employeeId = $data['employee_id'];
        $documentTypeId = $data['document_type_id'] ?? null;
        $title = $data['title'];
        $description = $data['description'] ?? null;

        // Validate file
        $validation = $this->validateFile($file, $documentTypeId);
        if (!$validation['valid']) {
            return ['success' => false, 'message' => $validation['message']];
        }

        // Generate unique filename
        $fileName = $this->generateUniqueFileName($file['name']);
        $uploadPath = $this->getUploadPath($employeeId);

        // Ensure directory exists
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $fullPath = $uploadPath . '/' . $fileName;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            return ['success' => false, 'message' => 'Failed to save uploaded file'];
        }

        // Handle versioning for existing documents
        $existingDoc = null;
        if ($documentTypeId) {
            $existingDoc = $this->query(
                "SELECT id FROM employee_documents WHERE employee_id = ? AND document_type_id = ? AND status = ?",
                [$employeeId, $documentTypeId, self::STATUS_ACTIVE]
            )->fetch();
        }

        // If existing document, mark it as not latest
        if ($existingDoc) {
            $this->update($existingDoc['id'], [
                'is_latest' => false,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $version = $existingDoc['version'] + 1;
        } else {
            $version = 1;
        }

        // Save document record
        $documentData = [
            'employee_id' => $employeeId,
            'document_type_id' => $documentTypeId,
            'title' => $title,
            'description' => $description,
            'file_name' => $fileName,
            'original_name' => $file['name'],
            'file_path' => str_replace($_SERVER['DOCUMENT_ROOT'], '', $fullPath),
            'file_size' => $file['size'],
            'file_extension' => strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)),
            'mime_type' => $file['type'],
            'version' => $version,
            'is_latest' => true,
            'status' => self::STATUS_ACTIVE,
            'uploaded_by' => $data['uploaded_by'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'metadata' => json_encode($data['metadata'] ?? []),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $documentId = $this->insert($documentData);

        // Log the upload action
        $this->logDocumentAction($documentId, 'upload', $data['uploaded_by'] ?? null, $data);

        return [
            'success' => true,
            'document_id' => $documentId,
            'message' => 'Document uploaded successfully'
        ];
    }

    /**
     * Get documents for an employee
     */
    public function getEmployeeDocuments(int $employeeId, array $filters = []): array
    {
        $sql = "SELECT ed.*, dt.name as document_type_name, dt.category_id,
                       dc.name as category_name, a.auser as uploaded_by_name
                FROM employee_documents ed
                LEFT JOIN document_types dt ON ed.document_type_id = dt.id
                LEFT JOIN document_categories dc ON dt.category_id = dc.id
                LEFT JOIN admin a ON ed.uploaded_by = a.aid
                WHERE ed.employee_id = ? AND ed.status = ?";

        $params = [$employeeId, self::STATUS_ACTIVE];

        // Apply filters
        if (!empty($filters['document_type_id'])) {
            $sql .= " AND ed.document_type_id = ?";
            $params[] = $filters['document_type_id'];
        }

        if (!empty($filters['category_id'])) {
            $sql .= " AND dt.category_id = ?";
            $params[] = $filters['category_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (ed.title LIKE ? OR ed.description LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY ed.created_at DESC";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = $filters['limit'];
        }

        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Download a document
     */
    public function downloadDocument(int $documentId, int $userId): array
    {
        $document = $this->find($documentId);

        if (!$document) {
            return ['success' => false, 'message' => 'Document not found'];
        }

        if ($document['status'] !== self::STATUS_ACTIVE) {
            return ['success' => false, 'message' => 'Document is not available for download'];
        }

        // Check permissions (employee can download their own documents, admin can download any)
        if ($document['employee_id'] != $userId) {
            // Check if user is admin or has sharing permissions
            if (!$this->hasDocumentAccess($documentId, $userId)) {
                return ['success' => false, 'message' => 'Access denied'];
            }
        }

        $filePath = $_SERVER['DOCUMENT_ROOT'] . $document['file_path'];

        if (!file_exists($filePath)) {
            return ['success' => false, 'message' => 'File not found on server'];
        }

        // Log the download action
        $this->logDocumentAction($documentId, 'download', $userId);

        return [
            'success' => true,
            'file_path' => $filePath,
            'file_name' => $document['original_name'],
            'mime_type' => $document['mime_type']
        ];
    }

    /**
     * Share a document
     */
    public function shareDocument(int $documentId, array $shareData): array
    {
        $db = Database::getInstance();

        $shareRecord = [
            'document_id' => $documentId,
            'shared_with_employee_id' => $shareData['employee_id'] ?? null,
            'shared_with_admin_id' => $shareData['admin_id'] ?? null,
            'permissions' => $shareData['permissions'] ?? 'view',
            'expires_at' => $shareData['expires_at'] ?? null,
            'shared_by' => $shareData['shared_by'],
            'created_at' => date('Y-m-d H:i:s')
        ];

        $db->query(
            "INSERT INTO document_sharing (document_id, shared_with_employee_id, shared_with_admin_id, permissions, expires_at, shared_by, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE permissions = ?, expires_at = ?",
            [
                $shareRecord['document_id'], $shareRecord['shared_with_employee_id'], $shareRecord['shared_with_admin_id'],
                $shareRecord['permissions'], $shareRecord['expires_at'], $shareRecord['shared_by'], $shareRecord['created_at'],
                $shareRecord['permissions'], $shareRecord['expires_at']
            ]
        );

        // Log the share action
        $this->logDocumentAction($documentId, 'share', $shareData['shared_by'], $shareData);

        return [
            'success' => true,
            'message' => 'Document shared successfully'
        ];
    }

    /**
     * Archive a document
     */
    public function archiveDocument(int $documentId, int $userId): array
    {
        $document = $this->find($documentId);

        if (!$document) {
            return ['success' => false, 'message' => 'Document not found'];
        }

        $this->update($documentId, [
            'status' => self::STATUS_ARCHIVED,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Log the archive action
        $this->logDocumentAction($documentId, 'archive', $userId);

        return [
            'success' => true,
            'message' => 'Document archived successfully'
        ];
    }

    /**
     * Delete a document (soft delete)
     */
    public function deleteDocument(int $documentId, int $userId): array
    {
        $document = $this->find($documentId);

        if (!$document) {
            return ['success' => false, 'message' => 'Document not found'];
        }

        $this->update($documentId, [
            'status' => self::STATUS_DELETED,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Log the delete action
        $this->logDocumentAction($documentId, 'delete', $userId);

        return [
            'success' => true,
            'message' => 'Document deleted successfully'
        ];
    }

    /**
     * Get document categories
     */
    public function getCategories(): array
    {
        return $this->query("SELECT * FROM document_categories WHERE is_active = 1 ORDER BY sort_order, name")->fetchAll();
    }

    /**
     * Get document types
     */
    public function getDocumentTypes(int $categoryId = null): array
    {
        $sql = "SELECT dt.*, dc.name as category_name
                FROM document_types dt
                LEFT JOIN document_categories dc ON dt.category_id = dc.id
                WHERE dt.is_active = 1";

        $params = [];
        if ($categoryId) {
            $sql .= " AND dt.category_id = ?";
            $params[] = $categoryId;
        }

        $sql .= " ORDER BY dt.name";

        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Validate uploaded file
     */
    private function validateFile(array $file, int $documentTypeId = null): array
    {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'message' => 'File upload failed'];
        }

        // Get document type restrictions if specified
        if ($documentTypeId) {
            $docType = $this->query("SELECT * FROM document_types WHERE id = ?", [$documentTypeId])->fetch();

            if ($docType) {
                // Check file size
                if ($file['size'] > $docType['max_file_size']) {
                    return ['valid' => false, 'message' => 'File size exceeds limit'];
                }

                // Check file extension
                $allowedExtensions = json_decode($docType['file_extensions'], true);
                $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                if (!in_array($fileExtension, $allowedExtensions)) {
                    return ['valid' => false, 'message' => 'File type not allowed'];
                }
            }
        }

        // General file validation
        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'message' => 'File size exceeds maximum limit of 10MB'];
        }

        $allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg',
            'image/png',
            'image/gif'
        ];

        if (!in_array($file['type'], $allowedTypes)) {
            return ['valid' => false, 'message' => 'File type not supported'];
        }

        return ['valid' => true];
    }

    /**
     * Generate unique filename
     */
    private function generateUniqueFileName(string $originalName): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        return uniqid('doc_', true) . '.' . $extension;
    }

    /**
     * Get upload path for employee documents
     */
    private function getUploadPath(int $employeeId): string
    {
        return $_SERVER['DOCUMENT_ROOT'] . '/uploads/employee_documents/' . $employeeId;
    }

    /**
     * Check if user has access to a document
     */
    private function hasDocumentAccess(int $documentId, int $userId): bool
    {
        // Check if document is shared with this user
        $shared = $this->query(
            "SELECT id FROM document_sharing
             WHERE document_id = ? AND (shared_with_employee_id = ? OR shared_with_admin_id = ?)
             AND (expires_at IS NULL OR expires_at > NOW())",
            [$documentId, $userId, $userId]
        )->fetch();

        return $shared !== false;
    }

    /**
     * Log document action
     */
    private function logDocumentAction(int $documentId, string $action, int $userId = null, array $data = null): void
    {
        if (!$userId) return;

        $db = Database::getInstance();

        $db->query(
            "INSERT INTO document_audit_log (document_id, action, performed_by, ip_address, user_agent, old_data, new_data, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $documentId,
                $action,
                $userId,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
                null,
                json_encode($data),
                date('Y-m-d H:i:s')
            ]
        );
    }

    /**
     * Get document statistics
     */
    public function getDocumentStats(int $employeeId = null): array
    {
        $db = Database::getInstance();

        $stats = [
            'total_documents' => 0,
            'active_documents' => 0,
            'archived_documents' => 0,
            'total_size' => 0,
            'categories' => []
        ];

        $whereClause = "";
        $params = [];

        if ($employeeId) {
            $whereClause = "WHERE employee_id = ?";
            $params[] = $employeeId;
        }

        // Get document counts
        $docStats = $db->query(
            "SELECT status, COUNT(*) as count, SUM(file_size) as total_size
             FROM employee_documents
             $whereClause
             GROUP BY status",
            $params
        )->fetchAll();

        foreach ($docStats as $stat) {
            $stats['total_documents'] += $stat['count'];
            $stats['total_size'] += $stat['total_size'];

            switch ($stat['status']) {
                case self::STATUS_ACTIVE:
                    $stats['active_documents'] = $stat['count'];
                    break;
                case self::STATUS_ARCHIVED:
                    $stats['archived_documents'] = $stat['count'];
                    break;
            }
        }

        // Get category breakdown
        $categoryStats = $db->query(
            "SELECT dc.name as category, COUNT(ed.id) as count
             FROM employee_documents ed
             LEFT JOIN document_types dt ON ed.document_type_id = dt.id
             LEFT JOIN document_categories dc ON dt.category_id = dc.id
             $whereClause AND ed.status = ?
             GROUP BY dc.id, dc.name
             ORDER BY count DESC",
            array_merge($params, [self::STATUS_ACTIVE])
        )->fetchAll();

        $stats['categories'] = $categoryStats;

        return $stats;
    }

    /**
     * Get expiring documents
     */
    public function getExpiringDocuments(int $days = 30): array
    {
        $futureDate = date('Y-m-d', strtotime("+{$days} days"));

        return $this->query(
            "SELECT ed.*, e.name as employee_name
             FROM employee_documents ed
             LEFT JOIN employees e ON ed.employee_id = e.id
             WHERE ed.expires_at IS NOT NULL
             AND ed.expires_at <= ?
             AND ed.expires_at >= CURDATE()
             AND ed.status = ?
             ORDER BY ed.expires_at ASC",
            [$futureDate, self::STATUS_ACTIVE]
        )->fetchAll();
    }
}
