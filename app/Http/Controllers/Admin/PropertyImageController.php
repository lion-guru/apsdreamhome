<?php
/**
 * Property Image Upload Controller
 * Handles multi-image upload, gallery management, and image optimization
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;

class PropertyImageController extends BaseController
{
    private $db;
    private $uploadPath;
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private $maxFileSize = 10 * 1024 * 1024; // 10MB
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
        $this->uploadPath = __DIR__ . '/../../../../public/uploads/properties/';
        
        // Create upload directory if not exists
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }
    
    /**
     * Show image management page for a property
     */
    public function manage($propertyId)
    {
        // Check admin auth
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['admin_id'])) {
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }
        
        // Get property details
        $property = $this->db->fetchOne(
            "SELECT p.*, u.name as owner_name, u.email as owner_email 
             FROM properties p 
             LEFT JOIN users u ON p.user_id = u.id 
             WHERE p.id = ?",
            [$propertyId]
        );
        
        if (!$property) {
            $_SESSION['error'] = "Property not found";
            header('Location: ' . BASE_URL . '/admin/properties');
            exit;
        }
        
        // Get existing images
        $images = $this->db->fetchAll(
            "SELECT * FROM property_images WHERE property_id = ? ORDER BY is_primary DESC, sort_order ASC, id ASC",
            [$propertyId]
        );
        
        $base = BASE_URL;
        include __DIR__ . '/../../../views/admin/properties/images.php';
    }
    
    /**
     * Handle multiple image uploads
     */
    public function upload()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        $propertyId = $_POST['property_id'] ?? null;
        
        if (!$propertyId) {
            $_SESSION['error'] = "Property ID required";
            header('Location: ' . BASE_URL . '/admin/properties');
            exit;
        }
        
        // Check if files were uploaded
        if (empty($_FILES['images'])) {
            $_SESSION['error'] = "No images selected";
            header('Location: ' . BASE_URL . '/admin/properties/' . $propertyId . '/images');
            exit;
        }
        
        $uploadedCount = 0;
        $errors = [];
        
        // Process each uploaded file
        $files = $this->reArrayFiles($_FILES['images']);
        
        foreach ($files as $file) {
            if ($file['error'] !== UPLOAD_ERR_OK) {
                continue;
            }
            
            // Validate file
            if (!in_array($file['type'], $this->allowedTypes)) {
                $errors[] = $file['name'] . ": Invalid file type. Only JPG, PNG, GIF, WEBP allowed.";
                continue;
            }
            
            if ($file['size'] > $this->maxFileSize) {
                $errors[] = $file['name'] . ": File too large. Max 10MB.";
                continue;
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'prop_' . $propertyId . '_' . time() . '_' . uniqid() . '.' . $extension;
            $filepath = $this->uploadPath . $filename;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Create thumbnail
                $thumbFilename = 'thumb_' . $filename;
                $this->createThumbnail($filepath, $this->uploadPath . $thumbFilename, 400, 300);
                
                // Create medium version
                $mediumFilename = 'medium_' . $filename;
                $this->createThumbnail($filepath, $this->uploadPath . $mediumFilename, 800, 600);
                
                // Check if this is the first image (make it primary)
                $existingCount = $this->db->fetchOne(
                    "SELECT COUNT(*) as count FROM property_images WHERE property_id = ?",
                    [$propertyId]
                )['count'];
                
                $isPrimary = ($existingCount == 0) ? 1 : 0;
                
                // Save to database
                $this->db->insert('property_images', [
                    'property_id' => $propertyId,
                    'image_path' => 'uploads/properties/' . $filename,
                    'thumbnail_path' => 'uploads/properties/' . $thumbFilename,
                    'medium_path' => 'uploads/properties/' . $mediumFilename,
                    'original_name' => $file['name'],
                    'file_size' => $file['size'],
                    'mime_type' => $file['type'],
                    'is_primary' => $isPrimary,
                    'sort_order' => $existingCount,
                    'caption' => $_POST['caption'] ?? null,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                
                $uploadedCount++;
            } else {
                $errors[] = $file['name'] . ": Failed to upload.";
            }
        }
        
        // Set feedback message
        if ($uploadedCount > 0) {
            $_SESSION['success'] = "$uploadedCount image(s) uploaded successfully!";
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
        }
        
        header('Location: ' . BASE_URL . '/admin/properties/' . $propertyId . '/images');
        exit;
    }
    
    /**
     * AJAX upload for drag & drop
     */
    public function ajaxUpload()
    {
        header('Content-Type: application/json');
        
        $propertyId = $_POST['property_id'] ?? null;
        
        if (!$propertyId || empty($_FILES['file'])) {
            echo json_encode(['success' => false, 'error' => 'Invalid request']);
            exit;
        }
        
        $file = $_FILES['file'];
        
        // Validate
        if (!in_array($file['type'], $this->allowedTypes)) {
            echo json_encode(['success' => false, 'error' => 'Invalid file type']);
            exit;
        }
        
        if ($file['size'] > $this->maxFileSize) {
            echo json_encode(['success' => false, 'error' => 'File too large']);
            exit;
        }
        
        // Generate filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'prop_' . $propertyId . '_' . time() . '_' . uniqid() . '.' . $extension;
        $filepath = $this->uploadPath . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Create thumbnails
            $thumbFilename = 'thumb_' . $filename;
            $this->createThumbnail($filepath, $this->uploadPath . $thumbFilename, 400, 300);
            
            $mediumFilename = 'medium_' . $filename;
            $this->createThumbnail($filepath, $this->uploadPath . $mediumFilename, 800, 600);
            
            // Get sort order
            $existingCount = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM property_images WHERE property_id = ?",
                [$propertyId]
            )['count'];
            
            $isPrimary = ($existingCount == 0) ? 1 : 0;
            
            // Save to database
            $imageId = $this->db->insert('property_images', [
                'property_id' => $propertyId,
                'image_path' => 'uploads/properties/' . $filename,
                'thumbnail_path' => 'uploads/properties/' . $thumbFilename,
                'medium_path' => 'uploads/properties/' . $mediumFilename,
                'original_name' => $file['name'],
                'file_size' => $file['size'],
                'mime_type' => $file['type'],
                'is_primary' => $isPrimary,
                'sort_order' => $existingCount,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            echo json_encode([
                'success' => true,
                'image_id' => $imageId,
                'path' => 'uploads/properties/' . $filename,
                'thumbnail' => 'uploads/properties/' . $thumbFilename,
                'is_primary' => $isPrimary
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Upload failed']);
        }
        exit;
    }
    
    /**
     * Set image as primary
     */
    public function setPrimary()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        $imageId = $_POST['image_id'] ?? null;
        $propertyId = $_POST['property_id'] ?? null;
        
        if (!$imageId || !$propertyId) {
            $_SESSION['error'] = "Invalid request";
            header('Location: ' . BASE_URL . '/admin/properties');
            exit;
        }
        
        // Remove primary from all images of this property
        $this->db->query(
            "UPDATE property_images SET is_primary = 0 WHERE property_id = ?",
            [$propertyId]
        );
        
        // Set new primary
        $this->db->query(
            "UPDATE property_images SET is_primary = 1 WHERE id = ?",
            [$imageId]
        );
        
        $_SESSION['success'] = "Primary image updated!";
        header('Location: ' . BASE_URL . '/admin/properties/' . $propertyId . '/images');
        exit;
    }
    
    /**
     * Update image caption
     */
    public function updateCaption()
    {
        header('Content-Type: application/json');
        
        $imageId = $_POST['image_id'] ?? null;
        $caption = $_POST['caption'] ?? '';
        
        if (!$imageId) {
            echo json_encode(['success' => false, 'error' => 'Invalid request']);
            exit;
        }
        
        $this->db->query(
            "UPDATE property_images SET caption = ? WHERE id = ?",
            [$caption, $imageId]
        );
        
        echo json_encode(['success' => true]);
        exit;
    }
    
    /**
     * Delete image
     */
    public function delete()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        $imageId = $_POST['image_id'] ?? null;
        $propertyId = $_POST['property_id'] ?? null;
        
        if (!$imageId) {
            $_SESSION['error'] = "Invalid request";
            header('Location: ' . BASE_URL . '/admin/properties');
            exit;
        }
        
        // Get image info
        $image = $this->db->fetchOne("SELECT * FROM property_images WHERE id = ?", [$imageId]);
        
        if ($image) {
            // Delete files
            $basePath = __DIR__ . '/../../../../public/';
            
            if (file_exists($basePath . $image['image_path'])) {
                unlink($basePath . $image['image_path']);
            }
            if (file_exists($basePath . $image['thumbnail_path'])) {
                unlink($basePath . $image['thumbnail_path']);
            }
            if ($image['medium_path'] && file_exists($basePath . $image['medium_path'])) {
                unlink($basePath . $image['medium_path']);
            }
            
            // Delete from database
            $this->db->query("DELETE FROM property_images WHERE id = ?", [$imageId]);
            
            // If deleted image was primary, set another as primary
            if ($image['is_primary']) {
                $this->db->query(
                    "UPDATE property_images SET is_primary = 1 WHERE property_id = ? ORDER BY id ASC LIMIT 1",
                    [$propertyId]
                );
            }
            
            $_SESSION['success'] = "Image deleted successfully!";
        }
        
        header('Location: ' . BASE_URL . '/admin/properties/' . $propertyId . '/images');
        exit;
    }
    
    /**
     * Reorder images
     */
    public function reorder()
    {
        header('Content-Type: application/json');
        
        $order = $_POST['order'] ?? [];
        
        foreach ($order as $index => $imageId) {
            $this->db->query(
                "UPDATE property_images SET sort_order = ? WHERE id = ?",
                [$index, $imageId]
            );
        }
        
        echo json_encode(['success' => true]);
        exit;
    }
    
    /**
     * Create thumbnail
     */
    private function createThumbnail($source, $destination, $width, $height)
    {
        list($origWidth, $origHeight, $type) = getimagesize($source);
        
        // Calculate aspect ratio
        $ratio = min($width / $origWidth, $height / $origHeight);
        $newWidth = $origWidth * $ratio;
        $newHeight = $origHeight * $ratio;
        
        // Create image resource
        switch ($type) {
            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($source);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($source);
                break;
            case IMAGETYPE_GIF:
                $sourceImage = imagecreatefromgif($source);
                break;
            case IMAGETYPE_WEBP:
                $sourceImage = imagecreatefromwebp($source);
                break;
            default:
                return false;
        }
        
        // Create thumbnail canvas
        $thumb = imagecreatetruecolor($width, $height);
        
        // Fill with white background (for PNG transparency)
        $white = imagecolorallocate($thumb, 255, 255, 255);
        imagefill($thumb, 0, 0, $white);
        
        // Resize and crop to center
        $x = ($width - $newWidth) / 2;
        $y = ($height - $newHeight) / 2;
        
        imagecopyresampled($thumb, $sourceImage, $x, $y, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
        
        // Save thumbnail
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($thumb, $destination, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($thumb, $destination, 8);
                break;
            case IMAGETYPE_GIF:
                imagegif($thumb, $destination);
                break;
            case IMAGETYPE_WEBP:
                imagewebp($thumb, $destination, 85);
                break;
        }
        
        imagedestroy($sourceImage);
        imagedestroy($thumb);
        
        return true;
    }
    
    /**
     * Re-array files from $_FILES format
     */
    private function reArrayFiles($filePost)
    {
        $files = [];
        $fileCount = count($filePost['name']);
        $fileKeys = array_keys($filePost);
        
        for ($i = 0; $i < $fileCount; $i++) {
            foreach ($fileKeys as $key) {
                $files[$i][$key] = $filePost[$key][$i];
            }
        }
        
        return $files;
    }
}
