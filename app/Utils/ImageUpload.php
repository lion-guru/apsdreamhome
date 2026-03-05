<?php

namespace App\Utils;

/**
 * Image Upload Utility Class
 * Handles image upload, validation, and management for properties
 */
class ImageUpload
{
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private $maxFileSize = 5242880; // 5MB in bytes
    private $uploadDir;
    private $tempDir;

    public function __construct()
    {
        $this->uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/properties/';
        $this->tempDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/temp/';

        // Create directories if they don't exist
        $this->ensureDirectoriesExist();
    }

    /**
     * Ensure upload directories exist
     */
    private function ensureDirectoriesExist()
    {
        $directories = [$this->uploadDir, $this->tempDir];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            // Create .htaccess for security
            $htaccessPath = $dir . '.htaccess';
            if (!file_exists($htaccessPath)) {
                file_put_contents($htaccessPath, "Order deny,allow\nDeny from all\n");
            }
        }
    }

    /**
     * Upload single image
     */
    public function uploadImage($file, $propertyId = null)
    {
        try {
            // Validate file
            $this->validateFile($file);

            // Generate unique filename
            $filename = $this->generateUniqueFilename($file['name'], $propertyId);
            $targetPath = $this->uploadDir . $filename;

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                throw new \Exception('Failed to move uploaded file');
            }

            // Create thumbnail
            $thumbnailPath = $this->createThumbnail($targetPath, $filename);

            return [
                'success' => true,
                'filename' => $filename,
                'path' => '/uploads/properties/' . $filename,
                'thumbnail' => $thumbnailPath ? '/uploads/properties/thumbnails/' . basename($thumbnailPath) : null,
                'size' => $file['size'],
                'type' => $file['type']
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Upload multiple images
     */
    public function uploadMultipleImages($files, $propertyId = null)
    {
        $results = [];

        if (is_array($files['name'])) {
            // Multiple files
            $count = count($files['name']);

            for ($i = 0; $i < $count; $i++) {
                $file = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ];

                if ($file['error'] === UPLOAD_ERR_OK) {
                    $result = $this->uploadImage($file, $propertyId);
                    $results[] = $result;
                }
            }
        } else {
            // Single file
            $result = $this->uploadImage($files, $propertyId);
            $results[] = $result;
        }

        return $results;
    }

    /**
     * Validate uploaded file
     */
    private function validateFile($file)
    {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception('File upload error: ' . $this->getUploadErrorMessage($file['error']));
        }

        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            throw new \Exception('File size too large. Maximum allowed size is 5MB.');
        }

        // Check file type
        if (!in_array($file['type'], $this->allowedTypes)) {
            throw new \Exception('Invalid file type. Only JPEG, PNG, GIF, and WebP images are allowed.');
        }

        // Validate image
        $imageInfo = getimagesize($file['tmp_name']);
        if (!$imageInfo) {
            throw new \Exception('Invalid image file.');
        }

        // Check minimum dimensions (optional)
        if ($imageInfo[0] < 100 || $imageInfo[1] < 100) {
            throw new \Exception('Image dimensions too small. Minimum size is 100x100 pixels.');
        }
    }

    /**
     * Generate unique filename
     */
    private function generateUniqueFilename($originalName, $propertyId = null)
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $timestamp = time();
        $random = bin2hex(random_bytes(4));

        $prefix = $propertyId ? "property_{$propertyId}_" : "temp_";

        return $prefix . $timestamp . '_' . $random . '.' . $extension;
    }

    /**
     * Create thumbnail image
     */
    private function createThumbnail($originalPath, $filename)
    {
        try {
            $thumbnailDir = $this->uploadDir . 'thumbnails/';
            if (!is_dir($thumbnailDir)) {
                mkdir($thumbnailDir, 0755, true);
            }

            $thumbnailPath = $thumbnailDir . 'thumb_' . $filename;

            // Get image info
            $imageInfo = getimagesize($originalPath);
            $width = $imageInfo[0];
            $height = $imageInfo[1];
            $mime = $imageInfo['mime'];

            // Calculate thumbnail size (300px max)
            $maxSize = 300;
            if ($width > $height) {
                $newWidth = $maxSize;
                $newHeight = ($height / $width) * $maxSize;
            } else {
                $newHeight = $maxSize;
                $newWidth = ($width / $height) * $maxSize;
            }

            // Create thumbnail based on image type
            switch ($mime) {
                case 'image/jpeg':
                    $source = imagecreatefromjpeg($originalPath);
                    $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
                    imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                    imagejpeg($thumbnail, $thumbnailPath, 85);
                    break;

                case 'image/png':
                    $source = imagecreatefrompng($originalPath);
                    $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
                    imagealphablending($thumbnail, false);
                    imagesavealpha($thumbnail, true);
                    imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                    imagepng($thumbnail, $thumbnailPath, 8);
                    break;

                case 'image/gif':
                    $source = imagecreatefromgif($originalPath);
                    $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
                    imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                    imagegif($thumbnail, $thumbnailPath);
                    break;

                case 'image/webp':
                    $source = imagecreatefromwebp($originalPath);
                    $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
                    imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                    imagewebp($thumbnail, $thumbnailPath, 85);
                    break;
            }

            // Clean up memory
            if (isset($source)) imagedestroy($source);
            if (isset($thumbnail)) imagedestroy($thumbnail);

            return $thumbnailPath;

        } catch (\Exception $e) {
            // Thumbnail creation failed, return null
            return null;
        }
    }

    /**
     * Delete image and its thumbnail
     */
    public function deleteImage($filename)
    {
        $paths = [
            $this->uploadDir . $filename,
            $this->uploadDir . 'thumbnails/thumb_' . $filename
        ];

        $deleted = false;
        foreach ($paths as $path) {
            if (file_exists($path)) {
                unlink($path);
                $deleted = true;
            }
        }

        return $deleted;
    }

    /**
     * Get upload error message
     */
    private function getUploadErrorMessage($errorCode)
    {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'File exceeds maximum size allowed by server.';
            case UPLOAD_ERR_FORM_SIZE:
                return 'File exceeds maximum size allowed by form.';
            case UPLOAD_ERR_PARTIAL:
                return 'File was only partially uploaded.';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded.';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing temporary folder.';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk.';
            case UPLOAD_ERR_EXTENSION:
                return 'File upload stopped by extension.';
            default:
                return 'Unknown upload error.';
        }
    }

    /**
     * Clean up old temporary files
     */
    public function cleanupTempFiles($maxAge = 3600) // 1 hour default
    {
        $files = glob($this->tempDir . '*');
        $now = time();

        foreach ($files as $file) {
            if (is_file($file)) {
                if ($now - filemtime($file) >= $maxAge) {
                    unlink($file);
                }
            }
        }
    }

    /**
     * Get image URL
     */
    public static function getImageUrl($filename, $thumbnail = false)
    {
        if (!$filename) return '/assets/images/property-placeholder.jpg';

        $prefix = $thumbnail ? 'thumbnails/thumb_' : '';
        return '/uploads/properties/' . $prefix . $filename;
    }
}
