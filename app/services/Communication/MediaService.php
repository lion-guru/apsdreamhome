<?php

namespace App\Services\Communication;

use App\Traits\ServiceTenantTrait;
use App\Core\Database;
use Psr\Log\LoggerInterface;

/**
 * Modern Media Service
 * Handles media library operations with proper security and MVC architecture
 */
class MediaService
{
    use ServiceTenantTrait;

    private Database $db;
    private LoggerInterface $logger;
    private array $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'];
    private int $maxFileSize = 10 * 1024 * 1024; // 10MB

    public function __construct(Database $db, LoggerInterface $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    /**
     * Get media files for templates with security validation
     */
    public function getMediaForTemplates(string $category = null, int $limit = 10): array
    {
        try {
            $sql = "SELECT * FROM media_library WHERE mime_type LIKE 'image/%' AND tenant_id = " . $this->tenantId();
            $params = [];

            if ($category) {
                $sql .= " AND tags LIKE ?";
                $params[] = '%' . $category . '%';
            }

            $sql .= " ORDER BY uploaded_at DESC LIMIT ?";
            $params[] = $limit;

            $results = $this->db->fetchAll($sql, $params);
            $media = [];

            foreach ($results as $row) {
                $safeMedia = $this->validateAndSecureMediaFile($row);
                if ($safeMedia) {
                    $media[] = $safeMedia;
                }
            }

            return $media;

        } catch (\Exception $e) {
            $this->logger->error('Failed to get media for templates', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get header images with security validation
     */
    public function getHeaderImages(int $limit = 10): array
    {
        try {
            $sql = "SELECT * FROM media_library
                    WHERE tags IN ('general', 'property', 'project')
                    AND mime_type LIKE 'image/%' AND tenant_id = " . $this->tenantId() . "
                    ORDER BY uploaded_at DESC
                    LIMIT ?";

            $results = $this->db->fetchAll($sql, [$limit]);
            $images = [];

            foreach ($results as $row) {
                $safeImage = $this->validateAndSecureMediaFile($row);
                if ($safeImage) {
                    $images[] = $safeImage;
                }
            }

            return $images;

        } catch (\Exception $e) {
            $this->logger->error('Failed to get header images', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get team photos with security validation
     */
    public function getTeamPhotos(int $limit = 10): array
    {
        try {
            $sql = "SELECT * FROM media_library
                    WHERE tags = 'team'
                    AND mime_type LIKE 'image/%' AND tenant_id = " . $this->tenantId() . "
                    ORDER BY uploaded_at DESC
                    LIMIT ?";

            $results = $this->db->fetchAll($sql, [$limit]);
            $photos = [];

            foreach ($results as $row) {
                $safePhoto = $this->validateAndSecureMediaFile($row);
                if ($safePhoto) {
                    $photos[] = $safePhoto;
                }
            }

            return $photos;

        } catch (\Exception $e) {
            $this->logger->error('Failed to get team photos', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get property images with security validation
     */
    public function getPropertyImages(int $limit = 10): array
    {
        try {
            $sql = "SELECT * FROM media_library
                    WHERE tags = 'property'
                    AND mime_type LIKE 'image/%' AND tenant_id = " . $this->tenantId() . "
                    ORDER BY uploaded_at DESC
                    LIMIT ?";

            $results = $this->db->fetchAll($sql, [$limit]);
            $images = [];

            foreach ($results as $row) {
                $safeImage = $this->validateAndSecureMediaFile($row);
                if ($safeImage) {
                    $images[] = $safeImage;
                }
            }

            return $images;

        } catch (\Exception $e) {
            $this->logger->error('Failed to get property images', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get project images with security validation
     */
    public function getProjectImages(int $limit = 10): array
    {
        try {
            $sql = "SELECT * FROM media_library
                    WHERE tags = 'project'
                    AND mime_type LIKE 'image/%' AND tenant_id = " . $this->tenantId() . "
                    ORDER BY uploaded_at DESC
                    LIMIT ?";

            $results = $this->db->fetchAll($sql, [$limit]);
            $images = [];

            foreach ($results as $row) {
                $safeImage = $this->validateAndSecureMediaFile($row);
                if ($safeImage) {
                    $images[] = $safeImage;
                }
            }

            return $images;

        } catch (\Exception $e) {
            $this->logger->error('Failed to get project images', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get document files with security validation
     */
    public function getDocuments(string $category = null, int $limit = 10): array
    {
        try {
            $sql = "SELECT * FROM media_library WHERE mime_type LIKE 'application/%' AND tenant_id = " . $this->tenantId();
            $params = [];

            if ($category) {
                $sql .= " AND tags LIKE ?";
                $params[] = '%' . $category . '%';
            }

            $sql .= " ORDER BY uploaded_at DESC LIMIT ?";
            $params[] = $limit;

            $results = $this->db->fetchAll($sql, $params);
            $documents = [];

            foreach ($results as $row) {
                $safeDoc = $this->validateAndSecureMediaFile($row);
                if ($safeDoc) {
                    $documents[] = $safeDoc;
                }
            }

            return $documents;

        } catch (\Exception $e) {
            $this->logger->error('Failed to get documents', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get carousel images with security validation
     */
    public function getCarouselImages(int $limit = 5): array
    {
        try {
            $sql = "SELECT * FROM media_library
                    WHERE tags = 'carousel'
                    AND mime_type LIKE 'image/%' AND tenant_id = " . $this->tenantId() . "
                    ORDER BY uploaded_at DESC
                    LIMIT ?";

            $results = $this->db->fetchAll($sql, [$limit]);
            $images = [];

            foreach ($results as $row) {
                $safeImage = $this->validateAndSecureMediaFile($row);
                if ($safeImage) {
                    $images[] = $safeImage;
                }
            }

            return $images;

        } catch (\Exception $e) {
            $this->logger->error('Failed to get carousel images', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get all media files with pagination and filtering
     */
    public function getAllMedia(array $filters = [], int $page = 1, int $limit = 20): array
    {
        try {
            $offset = ($page - 1) * $limit;
            $sql = "SELECT * FROM media_library WHERE 1=1 AND tenant_id = " . $this->tenantId();
            $params = [];

            // Apply filters
            if (!empty($filters['category'])) {
                $sql .= " AND tags LIKE ?";
                $params[] = '%' . $filters['category'] . '%';
            }

            if (!empty($filters['type'])) {
                if ($filters['type'] === 'image') {
                    $sql .= " AND mime_type LIKE 'image/%'";
                } elseif ($filters['type'] === 'document') {
                    $sql .= " AND mime_type LIKE 'application/%'";
                }
            }

            if (!empty($filters['search'])) {
                $sql .= " AND (alt_text LIKE ? OR caption LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            $sql .= " ORDER BY uploaded_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;

            $results = $this->db->fetchAll($sql, $params);
            $media = [];

            foreach ($results as $row) {
                $safeMedia = $this->validateAndSecureMediaFile($row);
                if ($safeMedia) {
                    $media[] = $safeMedia;
                }
            }

            return $media;

        } catch (\Exception $e) {
            $this->logger->error('Failed to get all media', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Upload media file with security validation
     */
    public function uploadMedia(array $file, array $metadata): bool
    {
        try {
            // Validate file
            if (!$this->validateUploadedFile($file)) {
                return false;
            }

            // Generate secure filename
            $filename = $this->generateSecureFilename($file['name']);
            $uploadPath = $this->getUploadPath() . $filename;

            // Move file to secure location
            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                throw new \Exception('Failed to move uploaded file');
            }

            // Save to database
            $sql = "INSERT INTO media_library 
                    (tenant_id, media_type, file_name, original_name, file_path, file_size, mime_type, alt_text, caption, uploaded_by, uploaded_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $mediaType = 'document';
            if (str_starts_with($file['type'], 'image/')) $mediaType = 'image';
            elseif (str_starts_with($file['type'], 'video/')) $mediaType = 'video';

            $this->db->execute($sql, [
                $this->tenantId(),
                $mediaType,
                $filename,
                $file['name'],
                $uploadPath,
                $file['size'],
                $file['type'],
                $metadata['title'] ?? $file['name'],
                $metadata['description'] ?? '',
                $metadata['uploaded_by'] ?? 0,
            ]);

            $this->logger->info('Media file uploaded successfully', ['filename' => $filename]);
            return true;

        } catch (\Exception $e) {
            $this->logger->error('Failed to upload media', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get media URL with security validation
     */
    public function getMediaUrl(int $id): ?string
    {
        try {
            $sql = "SELECT file_name FROM media_library WHERE id = ?";
            $filename = $this->db->fetchOne($sql, [$id]);

            if ($filename) {
                $safeFilename = $this->validateFilename($filename);
                if ($safeFilename) {
                    return BASE_URL . 'uploads/media/' . $safeFilename;
                }
            }

            return null;

        } catch (\Exception $e) {
            $this->logger->error('Failed to get media URL', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Validate and secure media file data
     */
    private function validateAndSecureMediaFile(array $row): ?array
    {
        // Validate filename to prevent path traversal
        $filename = $this->validateFilename($row['file_name']);
        if (!$filename) {
            return null;
        }

        // Verify file exists in expected location
        $mediaPath = $this->getUploadPath() . $filename;
        if (!file_exists($mediaPath) || !is_readable($mediaPath)) {
            return null;
        }

        return [
            'id' => $row['id'],
            'filename' => $filename,
            'original_name' => $row['original_name'],
            'title' => $row['alt_text'] ?: $row['original_name'],
            'url' => BASE_URL . 'uploads/media/' . $filename,
            'description' => $row['caption'],
            'category' => $row['tags'] ?? $row['media_type'],
            'mime_type' => $row['mime_type'],
            'file_size' => $row['file_size'],
            'upload_date' => $row['uploaded_at']
        ];
    }

    /**
     * Validate filename for security
     */
    private function validateFilename(string $filename): ?string
    {
        $basename = basename($filename);
        $safeFilename = preg_replace('/[^a-zA-Z0-9._-]/', '', $basename);
        
        return ($safeFilename === $basename && !empty($safeFilename)) ? $safeFilename : null;
    }

    /**
     * Validate uploaded file
     */
    private function validateUploadedFile(array $file): bool
    {
        // Check if file was actually uploaded
        if (!is_uploaded_file($file['tmp_name'])) {
            return false;
        }

        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            return false;
        }

        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions)) {
            return false;
        }

        return true;
    }

    /**
     * Generate secure filename
     */
    private function generateSecureFilename(string $originalName): string
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        return 'media_' . bin2hex(random_bytes(16)) . '.' . $extension;
    }

    /**
     * Get upload path
     */
    private function getUploadPath(): string
    {
        return __DIR__ . '/../../../../uploads/media/';
    }
}
