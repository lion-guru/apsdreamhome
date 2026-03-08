<?php

namespace App\Services\Media;

use App\Core\Database\Database;
use App\Services\LoggingService;
use Exception;
use InvalidArgumentException;
use RuntimeException;

/**
 * Media Library Service - APS Dream Home
 * Complete file and image management system for dynamic templates
 * Custom MVC implementation without Laravel dependencies
 */
class MediaLibraryServiceEnhanced
{
    private $database;
    private $logger;
    private $uploadDir;
    private $allowedTypes;
    private $maxFileSize;

    public function __construct($database = null, $logger = null)
    {
        $this->database = $database ?: Database::getInstance();
        $this->logger = $logger ?: LoggingService::getInstance();
        $this->uploadDir = 'uploads/media/';
        $this->allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
        $this->maxFileSize = 10 * 1024 * 1024; // 10MB

        // Ensure upload directory exists
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }

        $this->createMediaTables();
    }

    /**
     * Create media library table
     */
    private function createMediaTables()
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS media_library (
                id INT AUTO_INCREMENT PRIMARY KEY,
                filename VARCHAR(255) NOT NULL,
                original_name VARCHAR(255) NOT NULL,
                title VARCHAR(255),
                description TEXT,
                category VARCHAR(100) DEFAULT 'general',
                tags VARCHAR(500),
                mime_type VARCHAR(100),
                file_size BIGINT,
                upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                created_by BIGINT(20) UNSIGNED,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
            )";
            $this->database->query($sql);
        } catch (Exception $e) {
            $this->logger->log("Error creating media tables: " . $e->getMessage(), 'error', 'media');
            throw new RuntimeException("Failed to create media tables: " . $e->getMessage());
        }
    }

    /**
     * Handle file upload
     */
    public function handleUpload($fileData, $metadata = [])
    {
        try {
            if (!isset($fileData['tmp_name']) || !is_uploaded_file($fileData['tmp_name'])) {
                throw new InvalidArgumentException('Invalid file upload');
            }

            // Validate file
            $this->validateFile($fileData);

            // Generate unique filename
            $filename = $this->generateUniqueFilename($fileData['name']);
            $filepath = $this->uploadDir . $filename;

            // Move file
            if (!move_uploaded_file($fileData['tmp_name'], $filepath)) {
                throw new RuntimeException('Failed to upload file');
            }

            // Save to database
            $mediaId = $this->saveMediaRecord($filename, $fileData, $metadata);

            $this->logger->log("File uploaded successfully: $filename", 'info', 'media');
            return [
                'success' => true,
                'message' => 'File uploaded successfully',
                'media_id' => $mediaId,
                'filename' => $filename
            ];

        } catch (Exception $e) {
            $this->logger->log("File upload failed: " . $e->getMessage(), 'error', 'media');
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Validate uploaded file
     */
    private function validateFile($file)
    {
        // Check if file was uploaded
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('File upload error: ' . $this->getUploadErrorMessage($file['error']));
        }

        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            throw new InvalidArgumentException('File size exceeds maximum limit of 10MB');
        }

        // Check file type
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedTypes)) {
            throw new InvalidArgumentException('File type not allowed. Allowed types: ' . implode(', ', $this->allowedTypes));
        }
    }

    /**
     * Generate unique filename
     */
    private function generateUniqueFilename($originalName)
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $basename = pathinfo($originalName, PATHINFO_FILENAME);
        $basename = preg_replace('/[^a-zA-Z0-9-_]/', '', $basename);

        $counter = 1;
        $filename = $basename . '.' . $extension;

        while (file_exists($this->uploadDir . $filename)) {
            $filename = $basename . '_' . $counter . '.' . $extension;
            $counter++;
        }

        return $filename;
    }

    /**
     * Save media record to database
     */
    private function saveMediaRecord($filename, $fileData, $metadata)
    {
        $sql = "INSERT INTO media_library (filename, original_name, title, description, category, tags, mime_type, file_size, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $filename,
            $fileData['name'],
            $metadata['title'] ?? null,
            $metadata['description'] ?? null,
            $metadata['category'] ?? 'general',
            $metadata['tags'] ?? null,
            $fileData['type'],
            $fileData['size'],
            $metadata['created_by'] ?? null
        ];

        try {
            $this->database->execute($sql, $params);
            return $this->database->lastInsertId();
        } catch (Exception $e) {
            throw new RuntimeException('Failed to save media record: ' . $e->getMessage());
        }
    }

    /**
     * Get media files with filtering
     */
    public function getMediaFiles($filters = [], $limit = 50, $offset = 0)
    {
        $sql = "SELECT * FROM media_library WHERE 1=1";
        $params = [];

        if (!empty($filters['category'])) {
            $sql .= " AND category = ?";
            $params[] = $filters['category'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (title LIKE ? OR description LIKE ? OR tags LIKE ? OR original_name LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY upload_date DESC";

        if ($limit > 0) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = (int)$limit;
            $params[] = (int)$offset;
        }

        try {
            return $this->database->fetchAll($sql, $params);
        } catch (Exception $e) {
            $this->logger->log("Error fetching media files: " . $e->getMessage(), 'error', 'media');
            return [];
        }
    }

    /**
     * Get media file by ID
     */
    public function getMediaFile($id)
    {
        if (empty($id)) {
            throw new InvalidArgumentException('Media file ID is required');
        }

        $sql = "SELECT * FROM media_library WHERE id = ?";
        
        try {
            return $this->database->fetchOne($sql, [$id]);
        } catch (Exception $e) {
            $this->logger->log("Error fetching media file $id: " . $e->getMessage(), 'error', 'media');
            return null;
        }
    }

    /**
     * Delete media file
     */
    public function deleteMediaFile($id)
    {
        if (empty($id)) {
            throw new InvalidArgumentException('Media file ID is required');
        }

        $file = $this->getMediaFile($id);
        if (!$file) {
            return ['success' => false, 'message' => 'File not found'];
        }

        try {
            // Delete physical file
            $filepath = $this->uploadDir . $file['filename'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }

            // Delete database record
            $sql = "DELETE FROM media_library WHERE id = ?";
            $this->database->execute($sql, [$id]);

            $this->logger->log("Media file deleted: $id", 'info', 'media');
            return ['success' => true, 'message' => 'File deleted successfully'];

        } catch (Exception $e) {
            $this->logger->log("Error deleting media file $id: " . $e->getMessage(), 'error', 'media');
            return ['success' => false, 'message' => 'Failed to delete file'];
        }
    }

    /**
     * Update media file metadata
     */
    public function updateMediaFile($id, $metadata)
    {
        if (empty($id)) {
            throw new InvalidArgumentException('Media file ID is required');
        }

        $sql = "UPDATE media_library SET title = ?, description = ?, category = ?, tags = ? WHERE id = ?";
        
        $params = [
            $metadata['title'] ?? null,
            $metadata['description'] ?? null,
            $metadata['category'] ?? 'general',
            $metadata['tags'] ?? null,
            $id
        ];

        try {
            $this->database->execute($sql, $params);
            $this->logger->log("Media file updated: $id", 'info', 'media');
            return ['success' => true, 'message' => 'File updated successfully'];
        } catch (Exception $e) {
            $this->logger->log("Error updating media file $id: " . $e->getMessage(), 'error', 'media');
            return ['success' => false, 'message' => 'Failed to update file'];
        }
    }

    /**
     * Get media statistics
     */
    public function getMediaStats()
    {
        $stats = [];

        try {
            // Total files
            $result = $this->database->fetchOne("SELECT COUNT(*) as total FROM media_library");
            $stats['total_files'] = $result['total'] ?? 0;

            // Total size
            $result = $this->database->fetchOne("SELECT SUM(file_size) as total_size FROM media_library");
            $stats['total_size'] = $result['total_size'] ?? 0;

            // By category
            $results = $this->database->fetchAll("SELECT category, COUNT(*) as count FROM media_library GROUP BY category");
            $stats['by_category'] = [];
            foreach ($results as $row) {
                $stats['by_category'][$row['category']] = $row['count'];
            }

            // Recent uploads
            $result = $this->database->fetchOne("SELECT COUNT(*) as recent FROM media_library WHERE upload_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
            $stats['recent_uploads'] = $result['recent'] ?? 0;

        } catch (Exception $e) {
            $this->logger->log("Error fetching media stats: " . $e->getMessage(), 'error', 'media');
        }

        return $stats;
    }

    /**
     * Get upload error message
     */
    private function getUploadErrorMessage($errorCode)
    {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
            case UPLOAD_ERR_FORM_SIZE:
                return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
            case UPLOAD_ERR_PARTIAL:
                return 'The uploaded file was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing a temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'A PHP extension stopped the file upload';
            default:
                return 'Unknown upload error';
        }
    }

    /**
     * Get file URL
     */
    public function getFileUrl($filename)
    {
        return $this->uploadDir . $filename;
    }

    /**
     * Get file path
     */
    public function getFilePath($filename)
    {
        return $this->uploadDir . $filename;
    }

    /**
     * Check if file is image
     */
    public function isImage($mimeType)
    {
        return strpos($mimeType, 'image/') === 0;
    }

    /**
     * Get image dimensions
     */
    public function getImageDimensions($filepath)
    {
        if (file_exists($filepath) && $this->isImage(mime_content_type($filepath))) {
            $size = getimagesize($filepath);
            return $size ? [$size[0], $size[1]] : null;
        }
        return null;
    }

    /**
     * Create media category
     */
    public function createCategory($name, $description = null)
    {
        if (empty($name)) {
            throw new InvalidArgumentException('Category name is required');
        }

        // This could be extended to have a separate categories table
        // For now, categories are stored as strings in the media_library table
        $this->logger->log("Media category created: $name", 'info', 'media');
        return ['success' => true, 'message' => 'Category created successfully'];
    }

    /**
     * Get all categories
     */
    public function getCategories()
    {
        try {
            $sql = "SELECT DISTINCT category, COUNT(*) as file_count 
                    FROM media_library 
                    WHERE category IS NOT NULL AND category != ''
                    GROUP BY category 
                    ORDER BY category";
            return $this->database->fetchAll($sql);
        } catch (Exception $e) {
            $this->logger->log("Error fetching categories: " . $e->getMessage(), 'error', 'media');
            return [];
        }
    }
}
