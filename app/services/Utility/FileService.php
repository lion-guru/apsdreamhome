<?php

namespace App\Services\Utility;

use App\Core\Database\Database;
use App\Services\LoggingService;

/**
 * File Service - APS Dream Home
 * Advanced file operations and management
 * Custom MVC implementation without Laravel dependencies
 */
class FileService
{
    private $database;
    private $logger;
    private $allowedExtensions;
    private $maxFileSize;
    private $uploadPath;

    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->logger = new LoggingService();
        $this->allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip'];
        $this->maxFileSize = 10 * 1024 * 1024; // 10MB
        $this->uploadPath = STORAGE_PATH . '/uploads/';
    }

    /**
     * Upload file with validation
     */
    public function uploadFile($file, $category = 'general')
    {
        try {
            // Validate file
            if (!$this->validateFile($file)) {
                return false;
            }

            // Generate unique filename
            $filename = $this->generateUniqueFilename($file['name']);
            $filepath = $this->uploadPath . $category . '/' . $filename;

            // Ensure directory exists
            $this->ensureDirectoryExists($this->uploadPath . $category);

            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Save file record to database
                $fileId = $this->saveFileRecord($file, $filename, $category);
                
                if ($fileId) {
                    $this->logger->info("File uploaded successfully: {$filename}");
                    return [
                        'success' => true,
                        'file_id' => $fileId,
                        'filename' => $filename,
                        'filepath' => $filepath,
                        'original_name' => $file['name'],
                        'size' => $file['size'],
                        'type' => $file['type']
                    ];
                }
            }

            return false;
        } catch (Exception $e) {
            $this->logger->error("Error uploading file: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate uploaded file
     */
    private function validateFile($file)
    {
        // Check if file was uploaded
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $this->logger->error("Invalid file upload");
            return false;
        }

        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            $this->logger->error("File size exceeds limit: " . $file['size']);
            return false;
        }

        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions)) {
            $this->logger->error("Invalid file extension: " . $extension);
            return false;
        }

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->logger->error("File upload error: " . $file['error']);
            return false;
        }

        return true;
    }

    /**
     * Generate unique filename
     */
    private function generateUniqueFilename($originalName)
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $basename = pathinfo($originalName, PATHINFO_FILENAME);
        $timestamp = time();
        $random = mt_rand(1000, 9999);
        
        return $basename . '_' . $timestamp . '_' . $random . '.' . $extension;
    }

    /**
     * Ensure directory exists
     */
    private function ensureDirectoryExists($directory)
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    /**
     * Save file record to database
     */
    private function saveFileRecord($file, $filename, $category)
    {
        try {
            $sql = "INSERT INTO files 
                    (original_name, filename, filepath, file_size, mime_type, category, uploaded_at) 
                    VALUES (:original_name, :filename, :filepath, :file_size, :mime_type, :category, NOW())";
            
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':original_name', $file['name']);
            $stmt->bindParam(':filename', $filename);
            $stmt->bindParam(':filepath', $this->uploadPath . $category . '/' . $filename);
            $stmt->bindParam(':file_size', $file['size']);
            $stmt->bindParam(':mime_type', $file['type']);
            $stmt->bindParam(':category', $category);
            
            $result = $stmt->execute();
            
            if ($result) {
                return $this->database->lastInsertId();
            }
            
            return false;
        } catch (Exception $e) {
            $this->logger->error("Error saving file record: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get file by ID
     */
    public function getFileById($fileId)
    {
        try {
            $sql = "SELECT * FROM files WHERE id = :id";
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':id', $fileId);
            $stmt->execute();
            return $stmt->fetch();
        } catch (Exception $e) {
            $this->logger->error("Error getting file by ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get files by category
     */
    public function getFilesByCategory($category, $limit = 50)
    {
        try {
            $sql = "SELECT * FROM files WHERE category = :category ORDER BY uploaded_at DESC LIMIT :limit";
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':category', $category);
            $stmt->bindParam(':limit', $limit);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            $this->logger->error("Error getting files by category: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Delete file
     */
    public function deleteFile($fileId)
    {
        try {
            $file = $this->getFileById($fileId);
            
            if (!$file) {
                return false;
            }

            // Delete physical file
            if (file_exists($file['filepath'])) {
                unlink($file['filepath']);
            }

            // Delete database record
            $sql = "DELETE FROM files WHERE id = :id";
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':id', $fileId);
            $result = $stmt->execute();

            if ($result) {
                $this->logger->info("File deleted successfully: ID {$fileId}");
                return true;
            }

            return false;
        } catch (Exception $e) {
            $this->logger->error("Error deleting file: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Process image (resize, optimize)
     */
    public function processImage($filepath, $width = null, $height = null)
    {
        try {
            $imageInfo = getimagesize($filepath);
            
            if (!$imageInfo) {
                return false;
            }

            $imageType = $imageInfo[2];
            $originalWidth = $imageInfo[0];
            $originalHeight = $imageInfo[1];

            // Calculate new dimensions
            if ($width || $height) {
                $aspectRatio = $originalWidth / $originalHeight;
                
                if ($width && !$height) {
                    $height = $width / $aspectRatio;
                } elseif ($height && !$width) {
                    $width = $height * $aspectRatio;
                }
            } else {
                $width = $originalWidth;
                $height = $originalHeight;
            }

            // Create image resource based on type
            switch ($imageType) {
                case IMAGETYPE_JPEG:
                    $source = imagecreatefromjpeg($filepath);
                    break;
                case IMAGETYPE_PNG:
                    $source = imagecreatefrompng($filepath);
                    break;
                case IMAGETYPE_GIF:
                    $source = imagecreatefromgif($filepath);
                    break;
                default:
                    return false;
            }

            // Create new image
            $destination = imagecreatetruecolor($width, $height);
            
            // Handle transparency for PNG
            if ($imageType == IMAGETYPE_PNG) {
                imagealphablending($destination, false);
                imagesavealpha($destination, true);
            }

            // Resize image
            imagecopyresampled($destination, $source, 0, 0, 0, 0, $width, $height, $originalWidth, $originalHeight);

            // Save processed image
            $outputPath = $filepath;
            
            switch ($imageType) {
                case IMAGETYPE_JPEG:
                    imagejpeg($destination, $outputPath, 90);
                    break;
                case IMAGETYPE_PNG:
                    imagepng($destination, $outputPath, 9);
                    break;
                case IMAGETYPE_GIF:
                    imagegif($destination, $outputPath);
                    break;
            }

            // Free memory
            imagedestroy($source);
            imagedestroy($destination);

            $this->logger->info("Image processed successfully: {$filepath}");
            return true;
        } catch (Exception $e) {
            $this->logger->error("Error processing image: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create thumbnail
     */
    public function createThumbnail($filepath, $width = 150, $height = 150)
    {
        try {
            $pathInfo = pathinfo($filepath);
            $thumbnailPath = $pathInfo['dirname'] . '/thumb_' . $pathInfo['basename'];
            
            // Copy original to thumbnail location
            if (!copy($filepath, $thumbnailPath)) {
                return false;
            }

            // Process thumbnail
            if ($this->processImage($thumbnailPath, $width, $height)) {
                return $thumbnailPath;
            }

            return false;
        } catch (Exception $e) {
            $this->logger->error("Error creating thumbnail: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get file statistics
     */
    public function getFileStatistics()
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_files,
                        SUM(file_size) as total_size,
                        COUNT(DISTINCT category) as categories,
                        COUNT(CASE WHEN uploaded_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as recent_files
                    FROM files";
            
            $stmt = $this->database->prepare($sql);
            $stmt->execute();
            return $stmt->fetch();
        } catch (Exception $e) {
            $this->logger->error("Error getting file statistics: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Clean up old files
     */
    public function cleanupOldFiles($daysOld = 30)
    {
        try {
            $sql = "SELECT * FROM files WHERE uploaded_at < DATE_SUB(NOW(), INTERVAL :days DAY)";
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':days', $daysOld);
            $stmt->execute();
            $oldFiles = $stmt->fetchAll();

            $deletedCount = 0;
            
            foreach ($oldFiles as $file) {
                if ($this->deleteFile($file['id'])) {
                    $deletedCount++;
                }
            }

            $this->logger->info("Cleaned up {$deletedCount} old files");
            return $deletedCount;
        } catch (Exception $e) {
            $this->logger->error("Error cleaning up old files: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Batch file operations
     */
    public function batchOperation($operation, $fileIds)
    {
        try {
            $results = [];
            
            foreach ($fileIds as $fileId) {
                switch ($operation) {
                    case 'delete':
                        $results[$fileId] = $this->deleteFile($fileId);
                        break;
                    case 'process':
                        $file = $this->getFileById($fileId);
                        if ($file && $this->isImageFile($file['mime_type'])) {
                            $results[$fileId] = $this->processImage($file['filepath']);
                        } else {
                            $results[$fileId] = false;
                        }
                        break;
                    default:
                        $results[$fileId] = false;
                }
            }

            return $results;
        } catch (Exception $e) {
            $this->logger->error("Error in batch operation: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if file is image
     */
    private function isImageFile($mimeType)
    {
        return in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    /**
     * Get file URL
     */
    public function getFileUrl($fileId)
    {
        $file = $this->getFileById($fileId);
        
        if ($file) {
            return BASE_URL . '/uploads/' . $file['category'] . '/' . $file['filename'];
        }

        return false;
    }

    /**
     * Search files
     */
    public function searchFiles($query, $category = null)
    {
        try {
            $sql = "SELECT * FROM files WHERE original_name LIKE :query";
            $params = ['query' => '%' . $query . '%'];
            
            if ($category) {
                $sql .= " AND category = :category";
                $params['category'] = $category;
            }
            
            $sql .= " ORDER BY uploaded_at DESC LIMIT 50";
            
            $stmt = $this->database->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindParam(':' . $key, $value);
            }
            
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            $this->logger->error("Error searching files: " . $e->getMessage());
            return [];
        }
    }
}
