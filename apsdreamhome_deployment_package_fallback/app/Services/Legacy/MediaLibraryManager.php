<?php

namespace App\Services\Legacy;
/**
 * APS Dream Home - Media Library Manager
 * Complete file and image management system for dynamic templates
 */

require_once 'includes/config.php';

class MediaLibraryManager {
    private $db;
    private $uploadDir;
    private $allowedTypes;
    private $maxFileSize;

    public function __construct($db = null) {
        $this->db = $db ?: \App\Core\App::database();
        $this->uploadDir = 'uploads/media/';
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
    public function handleUpload() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media_file'])) {
            try {
                $file = $_FILES['media_file'];
                $title = $_POST['title'] ?? '';
                $description = $_POST['description'] ?? '';
                $category = $_POST['category'] ?? 'general';
                $tags = $_POST['tags'] ?? '';

                // Validate file
                $this->validateFile($file);

                // Generate unique filename
                $filename = $this->generateUniqueFilename($file['name']);
                $filepath = $this->uploadDir . $filename;

                // Move file
                if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                    throw new Exception('Failed to upload file');
                }

                // Save to database
                $this->saveMediaRecord($filename, $file['name'], $title, $description, $category, $tags, $file['type'], $file['size']);

                return ['success' => true, 'message' => 'File uploaded successfully', 'filename' => $filename];

            } catch (Exception $e) {
                return ['success' => false, 'message' => $e->getMessage()];
            }
        }

        return ['success' => false, 'message' => 'No file uploaded'];
    }

    /**
     * Validate uploaded file
     */
    private function validateFile($file) {
        // Check if file was uploaded
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload error: ' . $this->getUploadErrorMessage($file['error']));
        }

        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            throw new Exception('File size exceeds maximum limit of 10MB');
        }

        // Check file type
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedTypes)) {
            throw new Exception('File type not allowed. Allowed types: ' . implode(', ', $this->allowedTypes));
        }
    }

    /**
     * Generate unique filename
     */
    private function generateUniqueFilename($originalName) {
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
    private function saveMediaRecord($filename, $originalName, $title, $description, $category, $tags, $mimeType, $fileSize) {
        $sql = "INSERT INTO media_library (filename, original_name, title, description, category, tags, mime_type, file_size, upload_date)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $params = [$filename, $originalName, $title, $description, $category, $tags, $mimeType, $fileSize];

        if (!$this->db->execute($sql, $params)) {
            throw new Exception('Failed to save media record');
        }
    }

    /**
     * Get media files with filtering
     */
    public function getMediaFiles($category = null, $search = null, $limit = 50, $offset = 0) {
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

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get media file by ID
     */
    public function getMediaFile($id) {
        $sql = "SELECT * FROM media_library WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    /**
     * Delete media file
     */
    public function deleteMediaFile($id) {
        $file = $this->getMediaFile($id);
        if (!$file) {
            return ['success' => false, 'message' => 'File not found'];
        }

        // Delete physical file
        $filepath = $this->uploadDir . $file['filename'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }

        // Delete database record
        $sql = "DELETE FROM media_library WHERE id = ?";

        if ($this->db->execute($sql, [$id])) {
            return ['success' => true, 'message' => 'File deleted successfully'];
        }

        return ['success' => false, 'message' => 'Failed to delete file'];
    }

    /**
     * Update media file metadata
     */
    public function updateMediaFile($id, $title, $description, $category, $tags) {
        $sql = "UPDATE media_library SET title = ?, description = ?, category = ?, tags = ? WHERE id = ?";

        if ($this->db->execute($sql, [$title, $description, $category, $tags, $id])) {
            return ['success' => true, 'message' => 'File updated successfully'];
        }

        return ['success' => false, 'message' => 'Failed to update file'];
    }

    /**
     * Get media statistics
     */
    public function getMediaStats() {
        $stats = [];

        // Total files
        $result = $this->db->fetch("SELECT COUNT(*) as total FROM media_library");
        $stats['total_files'] = $result['total'] ?? 0;

        // Total size
        $result = $this->db->fetch("SELECT SUM(file_size) as total_size FROM media_library");
        $stats['total_size'] = $result['total_size'] ?? 0;

        // By category
        $results = $this->db->fetchAll("SELECT category, COUNT(*) as count FROM media_library GROUP BY category");
        $stats['by_category'] = [];
        foreach ($results as $row) {
            $stats['by_category'][$row['category']] = $row['count'];
        }

        // Recent uploads
        $result = $this->db->fetch("SELECT COUNT(*) as recent FROM media_library WHERE upload_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
        $stats['recent_uploads'] = $result['recent'] ?? 0;

        return $stats;
    }

    /**
     * Get upload error message
     */
    private function getUploadErrorMessage($errorCode) {
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
    public function getFileUrl($filename) {
        return BASE_URL . $this->uploadDir . $filename;
    }

    /**
     * Get file path
     */
    public function getFilePath($filename) {
        return $this->uploadDir . $filename;
    }

    /**
     * Check if file is image
     */
    public function isImage($mimeType) {
        return strpos($mimeType, 'image/') === 0;
    }

    /**
     * Get image dimensions
     */
    public function getImageDimensions($filepath) {
        if ($this->isImage(mime_content_type($filepath))) {
            $size = getimagesize($filepath);
            return $size ? [$size[0], $size[1]] : null;
        }
        return null;
    }
}

// Handle AJAX requests
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    require_once __DIR__ . '/session_helpers.php';
    ensureSessionStarted();

    header('Content-Type: application/json');

    // CSRF Protection for state-changing requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!validateCsrfToken()) {
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token. Action blocked.']);
            exit;
        }
    }

    try {
        $mediaManager = new MediaLibraryManager();

        $action = $_POST['action'] ?? $_GET['action'] ?? '';

        switch ($action) {
            case 'upload':
                echo json_encode($mediaManager->handleUpload());
                break;

            case 'get_files':
                $category = $_GET['category'] ?? null;
                $search = $_GET['search'] ?? null;
                $limit = intval($_GET['limit'] ?? 50);
                $offset = intval($_GET['offset'] ?? 0);
                echo json_encode($mediaManager->getMediaFiles($category, $search, $limit, $offset));
                break;

            case 'get_file':
                $id = intval($_GET['id'] ?? 0);
                echo json_encode($mediaManager->getMediaFile($id));
                break;

            case 'delete_file':
                $id = intval($_POST['id'] ?? 0);
                echo json_encode($mediaManager->deleteMediaFile($id));
                break;

            case 'update_file':
                $id = intval($_POST['id'] ?? 0);
                $title = $_POST['title'] ?? '';
                $description = $_POST['description'] ?? '';
                $category = $_POST['category'] ?? '';
                $tags = $_POST['tags'] ?? '';
                echo json_encode($mediaManager->updateMediaFile($id, $title, $description, $category, $tags));
                break;

            case 'get_stats':
                echo json_encode($mediaManager->getMediaStats());
                break;

            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
