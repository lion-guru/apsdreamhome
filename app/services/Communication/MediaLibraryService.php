<?php

namespace App\Services\Communication;

use App\Core\Database;
use Psr\Log\LoggerInterface;

/**
 * Modern Media Library Service
 * Handles comprehensive media library operations with proper MVC patterns
 */
class MediaLibraryService
{
    private Database $db;
    private LoggerInterface $logger;
    private string $uploadDir;
    private array $allowedTypes;
    private int $maxFileSize;

    public function __construct(Database $db, LoggerInterface $logger, array $config = [])
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->uploadDir = $config['upload_dir'] ?? 'uploads/media/';
        $this->allowedTypes = $config['allowed_types'] ?? [
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg',
            'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
            'mp4', 'avi', 'mov', 'wmv', 'flv', 'webm'
        ];
        $this->maxFileSize = $config['max_file_size'] ?? 10 * 1024 * 1024; // 10MB

        $this->ensureUploadDirectory();
    }

    /**
     * Upload file to media library
     */
    public function uploadFile(array $file, array $metadata = []): array
    {
        try {
            // Validate file upload
            $validation = $this->validateFileUpload($file);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => 'File validation failed',
                    'errors' => $validation['errors']
                ];
            }

            // Generate unique filename
            $filename = $this->generateUniqueFilename($file['name']);
            $filepath = $this->uploadDir . $filename;

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                return [
                    'success' => false,
                    'message' => 'Failed to move uploaded file'
                ];
            }

            // Get file info
            $fileInfo = $this->getFileInfo($filepath);

            // Save to database
            $sql = "INSERT INTO media_library 
                    (filename, original_name, file_path, file_size, mime_type, file_type, metadata, uploaded_by, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $this->db->execute($sql, [
                $filename,
                $file['name'],
                $filepath,
                $file['size'],
                $fileInfo['mime_type'],
                $fileInfo['file_type'],
                json_encode(array_merge($metadata, $fileInfo)),
                $metadata['uploaded_by'] ?? null
            ]);

            $mediaId = $this->db->lastInsertId();

            $this->logger->info('File uploaded successfully', [
                'media_id' => $mediaId,
                'filename' => $filename,
                'original_name' => $file['name'],
                'size' => $file['size']
            ]);

            return [
                'success' => true,
                'message' => 'File uploaded successfully',
                'media_id' => $mediaId,
                'filename' => $filename,
                'file_path' => $filepath,
                'file_info' => $fileInfo
            ];

        } catch (\Exception $e) {
            $this->logger->error('File upload failed', [
                'filename' => $file['name'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            // Clean up uploaded file if exists
            if (isset($filepath) && file_exists($filepath)) {
                unlink($filepath);
            }

            return [
                'success' => false,
                'message' => 'File upload failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get media file by ID
     */
    public function getMedia(int $id): ?array
    {
        try {
            $sql = "SELECT * FROM media_library WHERE id = ? AND deleted_at IS NULL";
            $media = $this->db->fetchOne($sql, [$id]);
            
            if ($media) {
                $media['metadata'] = json_decode($media['metadata'] ?? '{}', true) ?? [];
                $media['file_url'] = $this->getFileUrl($media['file_path']);
            }
            
            return $media;

        } catch (\Exception $e) {
            $this->logger->error('Failed to get media', ['id' => $id, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get media files by type
     */
    public function getMediaByType(string $type, int $limit = 50, int $offset = 0): array
    {
        try {
            $sql = "SELECT * FROM media_library 
                    WHERE file_type = ? AND deleted_at IS NULL 
                    ORDER BY created_at DESC 
                    LIMIT ? OFFSET ?";
            
            $media = $this->db->fetchAll($sql, [$type, $limit, $offset]);
            
            foreach ($media as &$item) {
                $item['metadata'] = json_decode($item['metadata'] ?? '{}', true) ?? [];
                $item['file_url'] = $this->getFileUrl($item['file_path']);
            }
            
            return $media;

        } catch (\Exception $e) {
            $this->logger->error('Failed to get media by type', ['type' => $type, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get media files by category
     */
    public function getMediaByCategory(string $category, int $limit = 50): array
    {
        try {
            $sql = "SELECT * FROM media_library 
                    WHERE JSON_EXTRACT(metadata, '$.category') = ? AND deleted_at IS NULL 
                    ORDER BY created_at DESC 
                    LIMIT ?";
            
            $media = $this->db->fetchAll($sql, [$category, $limit]);
            
            foreach ($media as &$item) {
                $item['metadata'] = json_decode($item['metadata'] ?? '{}', true) ?? [];
                $item['file_url'] = $this->getFileUrl($item['file_path']);
            }
            
            return $media;

        } catch (\Exception $e) {
            $this->logger->error('Failed to get media by category', ['category' => $category, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Search media files
     */
    public function searchMedia(string $query, array $filters = []): array
    {
        try {
            $sql = "SELECT * FROM media_library WHERE deleted_at IS NULL";
            $params = [];

            // Add search conditions
            if (!empty($query)) {
                $sql .= " AND (original_name LIKE ? OR filename LIKE ?)";
                $params[] = "%{$query}%";
                $params[] = "%{$query}%";
            }

            // Add filters
            if (!empty($filters['file_type'])) {
                $sql .= " AND file_type = ?";
                $params[] = $filters['file_type'];
            }

            if (!empty($filters['uploaded_by'])) {
                $sql .= " AND uploaded_by = ?";
                $params[] = $filters['uploaded_by'];
            }

            if (!empty($filters['date_from'])) {
                $sql .= " AND created_at >= ?";
                $params[] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $sql .= " AND created_at <= ?";
                $params[] = $filters['date_to'];
            }

            $sql .= " ORDER BY created_at DESC";

            if (!empty($filters['limit'])) {
                $sql .= " LIMIT ?";
                $params[] = (int)$filters['limit'];
            }

            $media = $this->db->fetchAll($sql, $params);
            
            foreach ($media as &$item) {
                $item['metadata'] = json_decode($item['metadata'] ?? '{}', true) ?? [];
                $item['file_url'] = $this->getFileUrl($item['file_path']);
            }
            
            return $media;

        } catch (\Exception $e) {
            $this->logger->error('Failed to search media', ['query' => $query, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Update media metadata
     */
    public function updateMedia(int $id, array $metadata): array
    {
        try {
            $media = $this->getMedia($id);
            if (!$media) {
                return [
                    'success' => false,
                    'message' => 'Media file not found'
                ];
            }

            // Merge with existing metadata
            $existingMetadata = $media['metadata'];
            $updatedMetadata = array_merge($existingMetadata, $metadata);

            $sql = "UPDATE media_library 
                    SET metadata = ?, updated_at = NOW() 
                    WHERE id = ?";
            
            $this->db->execute($sql, [json_encode($updatedMetadata), $id]);

            $this->logger->info('Media metadata updated', ['id' => $id]);

            return [
                'success' => true,
                'message' => 'Metadata updated successfully'
            ];

        } catch (\Exception $e) {
            $this->logger->error('Failed to update media metadata', ['id' => $id, 'error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Failed to update metadata'
            ];
        }
    }

    /**
     * Delete media file
     */
    public function deleteMedia(int $id, bool $permanent = false): array
    {
        try {
            $media = $this->getMedia($id);
            if (!$media) {
                return [
                    'success' => false,
                    'message' => 'Media file not found'
                ];
            }

            if ($permanent) {
                // Permanent deletion
                if (file_exists($media['file_path'])) {
                    unlink($media['file_path']);
                }

                $sql = "DELETE FROM media_library WHERE id = ?";
                $this->db->execute($sql, [$id]);

                $this->logger->info('Media permanently deleted', ['id' => $id]);

            } else {
                // Soft deletion
                $sql = "UPDATE media_library SET deleted_at = NOW() WHERE id = ?";
                $this->db->execute($sql, [$id]);

                $this->logger->info('Media soft deleted', ['id' => $id]);
            }

            return [
                'success' => true,
                'message' => $permanent ? 'Media permanently deleted' : 'Media deleted'
            ];

        } catch (\Exception $e) {
            $this->logger->error('Failed to delete media', ['id' => $id, 'error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Failed to delete media'
            ];
        }
    }

    /**
     * Get media statistics
     */
    public function getMediaStats(): array
    {
        try {
            $stats = [];

            // Total files
            $stats['total_files'] = $this->db->fetchOne(
                "SELECT COUNT(*) FROM media_library WHERE deleted_at IS NULL"
            ) ?? 0;

            // Total size
            $stats['total_size'] = $this->db->fetchOne(
                "SELECT COALESCE(SUM(file_size), 0) FROM media_library WHERE deleted_at IS NULL"
            ) ?? 0;

            // Files by type
            $typeStats = $this->db->fetchAll(
                "SELECT file_type, COUNT(*) as count, COALESCE(SUM(file_size), 0) as total_size 
                 FROM media_library WHERE deleted_at IS NULL 
                 GROUP BY file_type"
            );

            $stats['by_type'] = [];
            foreach ($typeStats as $stat) {
                $stats['by_type'][$stat['file_type']] = [
                    'count' => $stat['count'],
                    'size' => $stat['total_size']
                ];
            }

            // Recent uploads
            $stats['recent_uploads'] = $this->db->fetchAll(
                "SELECT * FROM media_library 
                 WHERE deleted_at IS NULL 
                 ORDER BY created_at DESC 
                 LIMIT 10"
            );

            // Storage usage
            $stats['storage_usage'] = [
                'used' => $stats['total_size'],
                'formatted_used' => $this->formatBytes($stats['total_size']),
                'percentage' => $this->getStoragePercentage($stats['total_size'])
            ];

            return $stats;

        } catch (\Exception $e) {
            $this->logger->error('Failed to get media stats', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Create media gallery
     */
    public function createGallery(array $mediaIds, string $galleryName, array $options = []): array
    {
        try {
            // Create gallery
            $sql = "INSERT INTO media_galleries (name, options, created_by, created_at) 
                    VALUES (?, ?, ?, NOW())";
            
            $this->db->execute($sql, [
                $galleryName,
                json_encode($options),
                $options['created_by'] ?? null
            ]);

            $galleryId = $this->db->lastInsertId();

            // Add media to gallery
            foreach ($mediaIds as $mediaId) {
                $sql = "INSERT INTO media_gallery_items (gallery_id, media_id, position, created_at) 
                        VALUES (?, ?, ?, NOW())";
                
                $this->db->execute($sql, [$galleryId, $mediaId, array_search($mediaId, $mediaIds)]);
            }

            $this->logger->info('Gallery created', ['gallery_id' => $galleryId, 'name' => $galleryName]);

            return [
                'success' => true,
                'message' => 'Gallery created successfully',
                'gallery_id' => $galleryId
            ];

        } catch (\Exception $e) {
            $this->logger->error('Failed to create gallery', ['name' => $galleryName, 'error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Failed to create gallery'
            ];
        }
    }

    /**
     * Get gallery with media
     */
    public function getGallery(int $galleryId): ?array
    {
        try {
            $sql = "SELECT g.*, 
                           (SELECT JSON_ARRAYAGG(
                               JSON_OBJECT(
                                   'id', m.id,
                                   'filename', m.filename,
                                   'original_name', m.original_name,
                                   'file_path', m.file_path,
                                   'file_url', CONCAT('" . $this->getBaseUrl() . "/', m.file_path),
                                   'file_type', m.file_type,
                                   'metadata', m.metadata,
                                   'position', mgi.position
                               )
                           )) as media_items
                    FROM media_galleries g
                    LEFT JOIN media_gallery_items mgi ON g.id = mgi.gallery_id
                    LEFT JOIN media_library m ON mgi.media_id = m.id AND m.deleted_at IS NULL
                    WHERE g.id = ?
                    GROUP BY g.id";
            
            $gallery = $this->db->fetchOne($sql, [$galleryId]);
            
            if ($gallery) {
                $gallery['options'] = json_decode($gallery['options'] ?? '{}', true) ?? [];
                $gallery['media_items'] = json_decode($gallery['media_items'] ?? '[]', true) ?? [];
            }
            
            return $gallery;

        } catch (\Exception $e) {
            $this->logger->error('Failed to get gallery', ['gallery_id' => $galleryId, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Private helper methods
     */
    private function validateFileUpload(array $file): array
    {
        $errors = [];
        $valid = true;

        // Check if file was actually uploaded
        if (!is_uploaded_file($file['tmp_name'])) {
            $errors[] = 'Invalid file upload';
            $valid = false;
        }

        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            $errors[] = 'File size exceeds maximum limit';
            $valid = false;
        }

        // Check file type
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedTypes)) {
            $errors[] = 'File type not allowed';
            $valid = false;
        }

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = $this->getUploadErrorMessage($file['error']);
            $valid = false;
        }

        return ['valid' => $valid, 'errors' => $errors];
    }

    private function generateUniqueFilename(string $originalName): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $basename = pathinfo($originalName, PATHINFO_FILENAME);
        $basename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $basename);
        
        $timestamp = time();
        $random = bin2hex(random_bytes(4));
        
        return "{$basename}_{$timestamp}_{$random}.{$extension}";
    }

    private function getFileInfo(string $filepath): array
    {
        $info = [];

        // Basic file info
        $info['mime_type'] = mime_content_type($filepath);
        $info['file_type'] = $this->getFileType($info['mime_type']);
        $info['extension'] = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));

        // Image-specific info
        if (strpos($info['mime_type'], 'image/') === 0) {
            $imageInfo = getimagesize($filepath);
            if ($imageInfo) {
                $info['width'] = $imageInfo[0];
                $info['height'] = $imageInfo[1];
                $info['dimensions'] = "{$imageInfo[0]}x{$imageInfo[1]}";
            }
        }

        return $info;
    }

    private function getFileType(string $mimeType): string
    {
        if (strpos($mimeType, 'image/') === 0) return 'image';
        if (strpos($mimeType, 'video/') === 0) return 'video';
        if (strpos($mimeType, 'audio/') === 0) return 'audio';
        if (strpos($mimeType, 'application/pdf') === 0) return 'document';
        if (in_array($mimeType, ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])) return 'document';
        if (in_array($mimeType, ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])) return 'spreadsheet';
        if (in_array($mimeType, ['application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'])) return 'presentation';
        
        return 'other';
    }

    private function getFileUrl(string $filepath): string
    {
        return $this->getBaseUrl() . '/' . $filepath;
    }

    private function getBaseUrl(): string
    {
        // This should be configured based on your application
        return 'http://localhost/apsdreamhome';
    }

    private function ensureUploadDirectory(): void
    {
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    private function getUploadErrorMessage(int $errorCode): string
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
        ];

        return $errors[$errorCode] ?? 'Unknown upload error';
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    private function getStoragePercentage(int $usedBytes): float
    {
        // Assuming 1GB storage limit - this should be configurable
        $totalBytes = 1024 * 1024 * 1024; // 1GB
        return min(100, ($usedBytes / $totalBytes) * 100);
    }
}
