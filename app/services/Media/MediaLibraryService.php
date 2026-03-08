<?php

namespace App\Services\Media;

use App\Core\Database\Database;
use App\Services\LoggingService;
use App\Core\Config;

/**
 * Media Library Service - APS Dream Home
 * Custom MVC implementation without Laravel dependencies
 */
class MediaLibraryService
{
    private $database;
    private $logger;
    private $config;
    private $uploadDir;
    private $allowedTypes;
    private $maxFileSize;

    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->logger = LoggingService::getInstance();
        $this->config = Config::getInstance();

        $this->uploadDir = STORAGE_PATH . '/uploads/media/';
        $this->allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
        $this->maxFileSize = 10 * 1024 * 1024; // 10MB

        // Ensure upload directory exists
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * Handle file upload
     */
    public function handleUpload(array $data, array $files = [])
    {
        try {
            if (!isset($files['media_file'])) {
                return [
                    'success' => false,
                    'message' => 'No file uploaded'
                ];
            }

            $file = $files['media_file'];
            $title = trim($data['title'] ?? '');
            $description = trim($data['description'] ?? '');
            $category = trim($data['category'] ?? 'general');
            $tags = trim($data['tags'] ?? '');

            // Validate file
            $this->validateFile($file);

            // Generate unique filename
            $filename = $this->generateUniqueFilename($file['name']);
            $filepath = $this->uploadDir . $filename;

            // Move file
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                throw new \Exception('Failed to upload file');
            }

            // Save to database
            $mediaId = $this->saveMediaRecord($filename, $file['name'], $title, $description, $category, $tags, $file['type'], $file['size']);

            $this->logger->info('Media file uploaded', [
                'media_id' => $mediaId,
                'filename' => $filename,
                'original_name' => $file['name'],
                'category' => $category
            ]);

            return [
                'success' => true,
                'message' => 'File uploaded successfully',
                'media_id' => $mediaId,
                'filename' => $filename
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to upload media file', [
                'error' => $e->getMessage(),
                'file' => $files['media_file']['name'] ?? 'unknown'
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get media files with filtering
     */
    public function getMediaFiles($category = null, $search = null, $limit = 50, $offset = 0)
    {
        try {
            $sql = "SELECT * FROM media_library WHERE 1=1";
            $params = [];

            if ($category) {
                $sql .= " AND category = ?";
                $params[] = $category;
            }

            if ($search) {
                $sql .= " AND (title LIKE ? OR description LIKE ? OR tags LIKE ? OR original_name LIKE ?)";
                $searchTerm = '%' . $search . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            $sql .= " ORDER BY upload_date DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;

            $files = $this->database->fetchAll($sql, $params);

            // Add file URLs and additional info
            foreach ($files as &$file) {
                $file['url'] = $this->getFileUrl($file['filename']);
                $file['is_image'] = $this->isImage($file['mime_type']);

                if ($file['is_image']) {
                    $dimensions = $this->getImageDimensions($this->uploadDir . $file['filename']);
                    $file['dimensions'] = $dimensions;
                }
            }

            return [
                'success' => true,
                'data' => $files
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to get media files', [
                'error' => $e->getMessage(),
                'category' => $category,
                'search' => $search
            ]);

            return [
                'success' => false,
                'message' => 'Failed to retrieve media files'
            ];
        }
    }

    /**
     * Get media file by ID
     */
    public function getMediaFile($id)
    {
        try {
            $file = $this->database->fetchOne(
                "SELECT * FROM media_library WHERE id = ?",
                [$id]
            );

            if (!$file) {
                return [
                    'success' => false,
                    'message' => 'File not found'
                ];
            }

            // Add additional info
            $file['url'] = $this->getFileUrl($file['filename']);
            $file['is_image'] = $this->isImage($file['mime_type']);

            if ($file['is_image']) {
                $dimensions = $this->getImageDimensions($this->uploadDir . $file['filename']);
                $file['dimensions'] = $dimensions;
            }

            return [
                'success' => true,
                'data' => $file
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to get media file', [
                'error' => $e->getMessage(),
                'media_id' => $id
            ]);

            return [
                'success' => false,
                'message' => 'Failed to retrieve media file'
            ];
        }
    }

    /**
     * Delete media file
     */
    public function deleteMediaFile($id)
    {
        try {
            $file = $this->database->fetchOne(
                "SELECT * FROM media_library WHERE id = ?",
                [$id]
            );

            if (!$file) {
                return [
                    'success' => false,
                    'message' => 'File not found'
                ];
            }

            // Delete physical file
            $filepath = $this->uploadDir . $file['filename'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }

            // Delete database record
            $this->database->query(
                "DELETE FROM media_library WHERE id = ?",
                [$id]
            );

            $this->logger->info('Media file deleted', [
                'media_id' => $id,
                'filename' => $file['filename']
            ]);

            return [
                'success' => true,
                'message' => 'File deleted successfully'
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete media file', [
                'error' => $e->getMessage(),
                'media_id' => $id
            ]);

            return [
                'success' => false,
                'message' => 'Failed to delete file'
            ];
        }
    }

    /**
     * Update media file metadata
     */
    public function updateMediaFile($id, $title, $description, $category, $tags)
    {
        try {
            $this->database->update('media_library', [
                'title' => trim($title),
                'description' => trim($description),
                'category' => trim($category),
                'tags' => trim($tags),
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$id]);

            $this->logger->info('Media file updated', [
                'media_id' => $id,
                'category' => $category
            ]);

            return [
                'success' => true,
                'message' => 'File updated successfully'
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to update media file', [
                'error' => $e->getMessage(),
                'media_id' => $id
            ]);

            return [
                'success' => false,
                'message' => 'Failed to update file'
            ];
        }
    }

    /**
     * Get media statistics
     */
    public function getMediaStats()
    {
        try {
            $stats = [];

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

            // By type
            $results = $this->database->fetchAll("SELECT mime_type, COUNT(*) as count FROM media_library GROUP BY mime_type");
            $stats['by_type'] = [];
            foreach ($results as $row) {
                $stats['by_type'][$row['mime_type']] = $row['count'];
            }

            // Recent uploads
            $result = $this->database->fetchOne("SELECT COUNT(*) as recent FROM media_library WHERE upload_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
            $stats['recent_uploads'] = $result['recent'] ?? 0;

            // Storage usage
            $stats['storage_usage'] = [
                'used' => $stats['total_size'],
                'formatted' => $this->formatBytes($stats['total_size'])
            ];

            return [
                'success' => true,
                'data' => $stats
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to get media stats', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to retrieve media statistics'
            ];
        }
    }

    /**
     * Get categories
     */
    public function getCategories()
    {
        try {
            $categories = $this->database->fetchAll(
                "SELECT DISTINCT category FROM media_library ORDER BY category ASC"
            );

            return [
                'success' => true,
                'data' => array_column($categories, 'category')
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to get categories', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to retrieve categories'
            ];
        }
    }

    /**
     * Validate uploaded file
     */
    private function validateFile($file)
    {
        // Check if file was uploaded
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception('File upload error: ' . $this->getUploadErrorMessage($file['error']));
        }

        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            throw new \Exception('File size exceeds maximum limit of 10MB');
        }

        // Check file type
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedTypes)) {
            throw new \Exception('File type not allowed. Allowed types: ' . implode(', ', $this->allowedTypes));
        }

        // Security: Check if file was actually uploaded
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new \Exception('Invalid file upload source');
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
    private function saveMediaRecord($filename, $originalName, $title, $description, $category, $tags, $mimeType, $fileSize)
    {
        $this->database->insert('media_library', [
            'filename' => $filename,
            'original_name' => $originalName,
            'title' => $title,
            'description' => $description,
            'category' => $category,
            'tags' => $tags,
            'mime_type' => $mimeType,
            'file_size' => $fileSize,
            'upload_date' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return $this->database->lastInsertId();
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
        $baseUrl = $this->config->get('app.base_url', 'http://localhost');
        return $baseUrl . '/uploads/media/' . $filename;
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
        if (!file_exists($filepath)) {
            return null;
        }

        $size = getimagesize($filepath);
        return $size ? [$size[0], $size[1]] : null;
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Create thumbnails for images
     */
    public function createThumbnail($filename, $width = 300, $height = 300)
    {
        try {
            $sourcePath = $this->uploadDir . $filename;

            if (!file_exists($sourcePath)) {
                return false;
            }

            $imageInfo = getimagesize($sourcePath);
            if (!$imageInfo) {
                return false;
            }

            $mimeType = $imageInfo['mime'];

            // Create image resource based on mime type
            switch ($mimeType) {
                case 'image/jpeg':
                    $source = imagecreatefromjpeg($sourcePath);
                    break;
                case 'image/png':
                    $source = imagecreatefrompng($sourcePath);
                    break;
                case 'image/gif':
                    $source = imagecreatefromgif($sourcePath);
                    break;
                case 'image/webp':
                    $source = imagecreatefromwebp($sourcePath);
                    break;
                default:
                    return false;
            }

            if (!$source) {
                return false;
            }

            // Calculate dimensions
            $sourceWidth = imagesx($source);
            $sourceHeight = imagesy($source);

            $ratio = min($width / $sourceWidth, $height / $sourceHeight);
            $newWidth = (int)($sourceWidth * $ratio);
            $newHeight = (int)($sourceHeight * $ratio);

            // Create thumbnail
            $thumbnail = imagecreatetruecolor($newWidth, $newHeight);

            if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
                imagealphablending($thumbnail, false);
                imagesavealpha($thumbnail, true);
            }

            imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $newWidth, $newHeight, $sourceWidth, $sourceHeight);

            // Save thumbnail
            $pathInfo = pathinfo($filename);
            $thumbnailFilename = $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
            $thumbnailPath = $this->uploadDir . 'thumbnails/' . $thumbnailFilename;

            // Ensure thumbnails directory exists
            $thumbnailsDir = $this->uploadDir . 'thumbnails/';
            if (!is_dir($thumbnailsDir)) {
                mkdir($thumbnailsDir, 0755, true);
            }

            switch ($mimeType) {
                case 'image/jpeg':
                    imagejpeg($thumbnail, $thumbnailPath, 85);
                    break;
                case 'image/png':
                    imagepng($thumbnail, $thumbnailPath, 8);
                    break;
                case 'image/gif':
                    imagegif($thumbnail, $thumbnailPath);
                    break;
                case 'image/webp':
                    imagewebp($thumbnail, $thumbnailPath, 85);
                    break;
            }

            imagedestroy($source);
            imagedestroy($thumbnail);

            return $thumbnailFilename;
        } catch (\Exception $e) {
            $this->logger->error('Failed to create thumbnail', [
                'error' => $e->getMessage(),
                'filename' => $filename
            ]);

            return false;
        }
    }
}
