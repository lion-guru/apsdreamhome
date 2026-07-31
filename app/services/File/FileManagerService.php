<?php

namespace App\Services\File;

use App\Core\Database\Database;
use \App\Traits\ServiceTenantTrait;

/**
 * File Manager Service
 * Document and file management with versioning
 */
class FileManagerService
{
    private $database;
    private $basePath;
    private $allowedTypes;
    private $maxSize;
    
    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->basePath = STORAGE_PATH . '/uploads/';
        $this->allowedTypes = [
            'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'],
            'video' => ['mp4', 'avi', 'mov', 'wmv'],
            'audio' => ['mp3', 'wav', 'ogg'],
            'archive' => ['zip', 'rar', '7z', 'tar']
        ];
        $this->maxSize = 50 * 1024 * 1024; // 50MB
        $this->ensureTablesExist();
        $this->ensureDirectoryStructure();
    }
    
    /**
     * Ensure file tables exist
     */
    private function ensureTablesExist(): void
    {
        $pdo = $this->database->getConnection();
        
        // Files table
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // File versions
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // File shares
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // File access logs
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // File tags
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // File-tag relationship
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
    }
    
    /**
     * Ensure directory structure
     */
    private function ensureDirectoryStructure(): void
    {
        $directories = [
            $this->basePath,
            $this->basePath . 'properties/',
            $this->basePath . 'users/',
            $this->basePath . 'documents/',
            $this->basePath . 'payments/',
            $this->basePath . 'temp/',
            $this->basePath . 'versions/'
        ];
        
        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }
    
    /**
     * Upload file
     */
    public function upload(array $file, array $options = []): array
    {
        try {
            // Validate file
            $validation = $this->validateFile($file);
            if (!$validation['valid']) {
                return ['success' => false, 'error' => $validation['error']];
            }
            
            // Generate unique filename
            $uuid = $this->generateUUID();
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $newFileName = $uuid . '.' . $extension;
            
            // Determine category and path
            $category = $options['category'] ?? 'general';
            $subPath = $this->getCategoryPath($category, $options);
            $fullPath = $this->basePath . $subPath . $newFileName;
            
            // Move file
            if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
                return ['success' => false, 'error' => 'Failed to move uploaded file'];
            }
            
            // Calculate checksum
            $checksum = hash_file('sha256', $fullPath);
            
            // Save to database
            $sql = "INSERT INTO files 
                (uuid, original_name, file_name, file_path, file_type, file_category, 
                 mime_type, extension, size_bytes, checksum, uploaded_by, uploaded_by_type,
                 entity_type, entity_id, is_public, metadata, description)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->database->prepare($sql);
            $stmt->execute([
                $uuid,
                $file['name'],
                $newFileName,
                $subPath . $newFileName,
                $validation['type'],
                $category,
                $file['type'] ?? null,
                $extension,
                $file['size'],
                $checksum,
                $options['uploaded_by'] ?? null,
                $options['uploaded_by_type'] ?? null,
                $options['entity_type'] ?? null,
                $options['entity_id'] ?? null,
                $options['is_public'] ?? 0,
                json_encode($options['metadata'] ?? []),
                $options['description'] ?? null
            ]);
            
            $fileId = $this->database->lastInsertId();
            
            // Log access
            $this->logAccess($fileId, 'upload', $options['uploaded_by'] ?? null, $options['uploaded_by_type'] ?? null);
            
            // Add tags if provided
            if (!empty($options['tags'])) {
                $this->addTagsToFile($fileId, $options['tags']);
            }
            
            return [
                'success' => true,
                'file_id' => $fileId,
                'uuid' => $uuid,
                'url' => $this->getFileUrl($uuid)
            ];
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get file by UUID
     */
    public function getFile(string $uuid, ?int $userId = null, ?string $userType = null): ?array
    {
        try {
            $sql = "SELECT f.*, 
                GROUP_CONCAT(t.name) as tag_names,
                GROUP_CONCAT(t.color) as tag_colors
                FROM files f
                LEFT JOIN file_tag_relations ftr ON f.id = ftr.file_id
                LEFT JOIN file_tags t ON ftr.tag_id = t.id
                WHERE f.uuid = ?
                GROUP BY f.id";
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$uuid]);
        $file = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$file) {
            return null;
        }
        
        // Check permissions
        if (!$file['is_public'] && !$this->canAccess($file, $userId, $userType)) {
            return null;
        }
        
        return $file;
    }
    
    /**
     * Download file
     */
    public function download(string $uuid, ?int $userId = null, ?string $userType = null): array
    {
        $file = $this->getFile($uuid, $userId, $userType);
        
        if (!$file) {
            return ['success' => false, 'error' => 'File not found or access denied'];
        }
        
        $fullPath = $this->basePath . $file['file_path'];
        
        if (!file_exists($fullPath)) {
            return ['success' => false, 'error' => 'File not found on disk'];
        }
        
        // Update download count
        $this->updateDownloadCount($file['id']);
        
        // Log access
        $this->logAccess($file['id'], 'download', $userId, $userType);
        
        return [
            'success' => true,
            'file_path' => $fullPath,
            'original_name' => $file['original_name'],
            'mime_type' => $file['mime_type']
        ];
    }
    
    /**
     * Delete file
     */
    public function delete(string $uuid, ?int $userId = null, ?string $userType = null): array
    {
        $file = $this->getFile($uuid, $userId, $userType);
        
        if (!$file) {
            return ['success' => false, 'error' => 'File not found'];
        }
        
        // Check delete permission
        if (!$this->canDelete($file, $userId, $userType)) {
            return ['success' => false, 'error' => 'Permission denied'];
        }
        
        try {
            // Delete physical file
            $fullPath = $this->basePath . $file['file_path'];
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
            
            // Delete versions
            if ($file['is_versioned']) {
                $this->deleteVersions($file['id']);
            }
            
            // Delete from database
            $this->deleteFileRecord($file['id']);
            
            // Log access
            $this->logAccess($file['id'], 'delete', $userId, $userType);
            
            return ['success' => true];
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Create new version
     */
    public function createVersion(string $uuid, array $newFile, array $options = []): array
    {
        $file = $this->getFile($uuid);
        
        if (!$file) {
            return ['success' => false, 'error' => 'File not found'];
        }
        
        if (!$file['is_versioned']) {
            // Enable versioning
            $this->enableVersioning($file['id']);
        }
        
        // Save current as version
        $this->saveCurrentAsVersion($file);
        
        // Upload new file
        $result = $this->upload($newFile, [
            'category' => $file['file_category'],
            'entity_type' => $file['entity_type'],
            'entity_id' => $file['entity_id'],
            'uploaded_by' => $options['uploaded_by'] ?? null,
            'uploaded_by_type' => $options['uploaded_by_type'] ?? null,
            'description' => $options['change_notes'] ?? 'New version'
        ]);
        
        if (!$result['success']) {
            return $result;
        }
        
        // Update parent file
        $newVersionNum = $file['version_number'] + 1;
        $this->updateFileVersion($file['id'], $result['file_id'], $newVersionNum);
        
        // Log access
        $this->logAccess($file['id'], 'version', $options['uploaded_by'] ?? null, $options['uploaded_by_type'] ?? null);
        
        return [
            'success' => true,
            'version_number' => $newVersionNum,
            'file_id' => $result['file_id']
        ];
    }
    
    /**
     * Get file versions
     */
    public function getVersions(int $fileId): array
    {
        try {
            $sql = "SELECT * FROM file_versions WHERE file_id = ? ORDER BY version_number DESC";
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$fileId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * List files with filters
     */
    public function listFiles(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where = ['1=1'];
        $params = [];
        
        if (!empty($filters['category'])) {
            $where[] = 'file_category = ?';
            $params[] = $filters['category'];
        }
        
        if (!empty($filters['entity_type']) && !empty($filters['entity_id'])) {
            $where[] = 'entity_type = ? AND entity_id = ?';
            $params[] = $filters['entity_type'];
            $params[] = $filters['entity_id'];
        }
        
        if (!empty($filters['uploaded_by']) && !empty($filters['uploaded_by_type'])) {
            $where[] = 'uploaded_by = ? AND uploaded_by_type = ?';
            $params[] = $filters['uploaded_by'];
            $params[] = $filters['uploaded_by_type'];
        }
        
        if (!empty($filters['file_type'])) {
            $where[] = 'file_type = ?';
            $params[] = $filters['file_type'];
        }
        
        if (!empty($filters['search'])) {
            $where[] = '(original_name LIKE ? OR description LIKE ?)';
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Get total count
        $countSql = "SELECT COUNT(*) FROM files WHERE $whereClause";
        $countStmt = $this->database->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();
        
        // Get files
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT f.* FROM files f
            WHERE $whereClause
            ORDER BY f.created_at DESC
            LIMIT ? OFFSET ?";
        
        $params[] = $perPage;
        $params[] = $offset;
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);
        $files = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        return [
            'files' => $files,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => ceil($total / $perPage)
        ];
    }
    
    /**
     * Validate file
     */
    private function validateFile(array $file): array
    {
        // Check upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'error' => 'Upload failed with error: ' . $file['error']];
        }
        
        // Check size
        if ($file['size'] > $this->maxSize) {
            return ['valid' => false, 'error' => 'File too large. Max size: ' . ($this->maxSize / 1024 / 1024) . 'MB'];
        }
        
        // Check extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = array_merge(...array_values($this->allowedTypes));
        
        if (!in_array($extension, $allowed)) {
            return ['valid' => false, 'error' => 'Invalid file type. Allowed: ' . implode(', ', $allowed)];
        }
        
        // Determine file type
        $fileType = 'unknown';
        foreach ($this->allowedTypes as $type => $exts) {
            if (in_array($extension, $exts)) {
                $fileType = $type;
                break;
            }
        }
        
        return ['valid' => true, 'type' => $fileType];
    }
    
    /**
     * Get category path
     */
    private function getCategoryPath(string $category, array $options): string
    {
        $year = date('Y');
        $month = date('m');
        
        switch ($category) {
            case 'property':
                $id = $options['entity_id'] ?? '0';
                return "properties/$year/$month/$id/";
            case 'user':
                $id = $options['uploaded_by'] ?? '0';
                return "users/$year/$month/$id/";
            case 'document':
                return "documents/$year/$month/";
            case 'payment':
                return "payments/$year/$month/";
            default:
                return "general/$year/$month/";
        }
    }
    
    /**
     * Generate UUID
     */
    private function generateUUID(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
    
    /**
     * Get file URL
     */
    public function getFileUrl(string $uuid): string
    {
        return '/uploads/' . $uuid;
    }
    
    /**
     * Check if user can access file
     */
    private function canAccess(array $file, ?int $userId, ?string $userType): bool
    {
        // Owner can always access
        if ($file['uploaded_by'] === $userId && $file['uploaded_by_type'] === $userType) {
            return true;
        }
        
        // Admin can access all
        if ($userType === 'admin') {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if user can delete file
     */
    private function canDelete(array $file, ?int $userId, ?string $userType): bool
    {
        // Owner can delete
        if ($file['uploaded_by'] === $userId && $file['uploaded_by_type'] === $userType) {
            return true;
        }
        
        // Admin can delete all
        if ($userType === 'admin') {
            return true;
        }
        
        return false;
    }
    
    /**
     * Update download count
     */
    private function updateDownloadCount(int $fileId): void
    {
        $sql = "UPDATE files SET 
            download_count = download_count + 1,
            last_downloaded_at = NOW()
            WHERE id = ?";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$fileId]);
    }
    
    /**
     * Log access
     */
    private function logAccess(int $fileId, string $action, ?int $userId, ?string $userType): void
    {
        try {
            $sql = "INSERT INTO file_access_logs 
                (file_id, user_id, user_type, action, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?, ?)";
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([
            $fileId,
            $userId,
            $userType,
            $action,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }
    
    /**
     * Add tags to file
     */
    private function addTagsToFile(int $fileId, array $tags): void
    {
        foreach ($tags as $tagName) {
            try {
                // Get or create tag
                $tagSql = "SELECT id FROM file_tags WHERE name = ?";
            } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
            error_log($e->getMessage());
            }
            $tagStmt = $this->database->prepare($tagSql);
            $tagStmt->execute([$tagName]);
            $tag = $tagStmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$tag) {
                $insertSql = "INSERT INTO file_tags (name) VALUES (?)";
                $insertStmt = $this->database->prepare($insertSql);
                $insertStmt->execute([$tagName]);
                $tagId = $this->database->lastInsertId();
            } else {
                $tagId = $tag['id'];
            }
            
            // Add relation
            $relSql = "INSERT IGNORE INTO file_tag_relations (file_id, tag_id) VALUES (?, ?)";
            $relStmt = $this->database->prepare($relSql);
            $relStmt->execute([$fileId, $tagId]);
        }
    }
    
    /**
     * Enable versioning
     */
    private function enableVersioning(int $fileId): void
    {
        $sql = "UPDATE files SET is_versioned = 1 WHERE id = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$fileId]);
    }
    
    /**
     * Save current as version
     */
    private function saveCurrentAsVersion(array $file): void
    {
        try {
            $sql = "INSERT INTO file_versions 
                (file_id, version_number, file_name, file_path, size_bytes, checksum, change_notes)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([
            $file['id'],
            $file['version_number'],
            $file['file_name'],
            $file['file_path'],
            $file['size_bytes'],
            $file['checksum'],
            'Auto-saved before new version upload'
        ]);
    }
    
    /**
     * Update file version
     */
    private function updateFileVersion(int $fileId, int $newFileId, int $versionNum): void
    {
        $sql = "UPDATE files SET 
            version_number = ?,
            parent_file_id = ?
            WHERE id = ?";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$versionNum, $newFileId, $fileId]);
    }
    
    /**
     * Delete versions
     */
    private function deleteVersions(int $fileId): void
    {
        try {
            $sql = "SELECT * FROM file_versions WHERE file_id = ?";
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$fileId]);
        $versions = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($versions as $version) {
            $path = $this->basePath . $version['file_path'];
            if (file_exists($path)) {
                unlink($path);
            }
        }
        
        $deleteSql = "DELETE FROM file_versions WHERE file_id = ?";
        $deleteStmt = $this->database->prepare($deleteSql);
        $deleteStmt->execute([$fileId]);
    }
    
    /**
     * Delete file record
     */
    private function deleteFileRecord(int $fileId): void
    {
        // Delete tag relations
        $tagSql = "DELETE FROM file_tag_relations WHERE file_id = ?";
        $tagStmt = $this->database->prepare($tagSql);
        $tagStmt->execute([$fileId]);
        
        try {
            // Delete shares
            $shareSql = "DELETE FROM file_shares WHERE file_id = ?";
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        $shareStmt = $this->database->prepare($shareSql);
        $shareStmt->execute([$fileId]);
        
        // Delete access logs
        $logSql = "DELETE FROM file_access_logs WHERE file_id = ?";
        $logStmt = $this->database->prepare($logSql);
        $logStmt->execute([$fileId]);
        
        // Delete file
        $sql = "DELETE FROM files WHERE id = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$fileId]);
    }
    
    /**
     * Get storage statistics
     */
    public function getStorageStats(): array
    {
        $sql = "SELECT 
            COUNT(*) as total_files,
            SUM(size_bytes) as total_size,
            file_category,
            file_type
            FROM files
            GROUP BY file_category, file_type";
        
        $stmt = $this->database->query($sql);
        $byCategory = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Total size
        $totalSql = "SELECT SUM(size_bytes) FROM files";
        $totalStmt = $this->database->query($totalSql);
        $totalSize = $totalStmt->fetchColumn();
        
        // Disk usage
        $diskTotal = disk_total_space($this->basePath);
        $diskFree = disk_free_space($this->basePath);
        
        return [
            'total_files' => array_sum(array_column($byCategory, 'total_files')),
            'total_size_bytes' => $totalSize,
            'total_size_human' => $this->formatBytes($totalSize),
            'by_category' => $byCategory,
            'disk_total' => $diskTotal,
            'disk_free' => $diskFree,
            'disk_used_percent' => round((($diskTotal - $diskFree) / $diskTotal) * 100, 2)
        ];
    }
    
    /**
     * Format bytes
     */
    private function formatBytes(?int $bytes): string
    {
        if ($bytes === null) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $unitIndex = 0;
        
        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }
        
        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }
}
