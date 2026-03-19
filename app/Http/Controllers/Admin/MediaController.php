<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\CoreFunctionsServiceCustom;
use App\Services\LoggingService;
use App\Core\Database;
use Exception;

/**
 * Media Controller - Custom MVC Implementation
 * Handles media management operations in the Admin panel
 */
class MediaController extends AdminController
{
    private $loggingService;
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->loggingService = new LoggingService();
        $this->db = Database::getInstance()->getConnection();
        
        // Register middlewares
        $this->middleware('csrf', ['only' => ['store', 'update', 'destroy']]);
    }

    /**
     * Display media library
     */
    public function index()
    {
        try {
            $search = $_GET['search'] ?? '';
            $type = $_GET['type'] ?? '';
            $page = (int)($_GET['page'] ?? 1);
            $perPage = (int)($_GET['per_page'] ?? 20);

            $offset = ($page - 1) * $perPage;

            // Build query
            $sql = "SELECT m.*, u.name as uploaded_by_name
                    FROM media m
                    LEFT JOIN users u ON m.uploaded_by = u.id
                    WHERE 1=1";
            $params = [];

            // Apply filters
            if (!empty($search)) {
                $sql .= " AND (m.original_name LIKE ? OR m.description LIKE ?)";
                $searchParam = '%' . $search . '%';
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if (!empty($type)) {
                $sql .= " AND m.file_type = ?";
                $params[] = $type;
            }

            $sql .= " ORDER BY m.created_at DESC";

            // Count total
            $countSql = str_replace("SELECT m.*, u.name as uploaded_by_name", "SELECT COUNT(DISTINCT m.id) as total", $sql);
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetch()['total'];

            // Apply pagination
            $sql .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $perPage;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $media = $stmt->fetchAll();

            // Get file types for filter
            $sql = "SELECT DISTINCT file_type, COUNT(*) as count FROM media GROUP BY file_type ORDER BY count DESC";
            $fileTypes = $this->db->fetchAll($sql);

            $data = [
                'page_title' => 'Media Library - APS Dream Home',
                'active_page' => 'media',
                'media' => $media,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
                'filters' => [
                    'search' => $search,
                    'type' => $type
                ],
                'file_types' => $fileTypes
            ];

            return $this->render('admin/media/index', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Media Index error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load media library');
            return $this->redirect('admin/dashboard');
        }
    }

    /**
     * Show the form for uploading new media
     */
    public function create()
    {
        try {
            $data = [
                'page_title' => 'Upload Media - APS Dream Home',
                'active_page' => 'media'
            ];

            return $this->render('admin/media/create', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Media Create error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load upload form');
            return $this->redirect('admin/media');
        }
    }

    /**
     * Store newly uploaded media
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                return $this->jsonError('No file uploaded or upload error', 400);
            }

            $file = $_FILES['file'];
            $description = $_POST['description'] ?? '';

            // Validate file
            $validation = $this->validateFile($file);
            if (!$validation['valid']) {
                return $this->jsonError($validation['error'], 400);
            }

            // Generate unique filename
            $originalName = $file['name'];
            $fileExtension = pathinfo($originalName, PATHINFO_EXTENSION);
            $fileName = uniqid('media_') . '.' . $fileExtension;

            // Create upload directory if it doesn't exist
            $uploadDir = 'uploads/media/' . date('Y/m');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filePath = $uploadDir . '/' . $fileName;

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                return $this->jsonError('Failed to move uploaded file', 500);
            }

            // Get file info
            $fileSize = $file['size'];
            $mimeType = mime_content_type($filePath);
            $fileType = $this->getFileType($mimeType);

            // Insert media record
            $sql = "INSERT INTO media 
                    (original_name, file_name, file_path, file_size, mime_type, file_type, description, uploaded_by, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $originalName,
                $fileName,
                $filePath,
                $fileSize,
                $mimeType,
                $fileType,
                CoreFunctionsServiceCustom::validateInput($description, 'string'),
                $_SESSION['user_id'] ?? 0
            ]);

            if ($result) {
                $mediaId = $this->db->lastInsertId();

                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'media_uploaded', [
                    'media_id' => $mediaId,
                    'file_name' => $originalName
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Media uploaded successfully',
                    'media_id' => $mediaId,
                    'file_url' => BASE_URL . '/' . $filePath
                ]);
            }

            // Clean up uploaded file if database insert failed
            unlink($filePath);

            return $this->jsonError('Failed to save media record', 500);
        } catch (Exception $e) {
            $this->loggingService->error("Media Store error: " . $e->getMessage());
            return $this->jsonError('Failed to upload media', 500);
        }
    }

    /**
     * Display the specified media
     */
    public function show($id)
    {
        try {
            $mediaId = intval($id);
            if ($mediaId <= 0) {
                $this->setFlash('error', 'Invalid media ID');
                return $this->redirect('admin/media');
            }

            // Get media details
            $sql = "SELECT m.*, u.name as uploaded_by_name
                    FROM media m
                    LEFT JOIN users u ON m.uploaded_by = u.id
                    WHERE m.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$mediaId]);
            $media = $stmt->fetch();

            if (!$media) {
                $this->setFlash('error', 'Media not found');
                return $this->redirect('admin/media');
            }

            $data = [
                'page_title' => 'Media Details - APS Dream Home',
                'active_page' => 'media',
                'media' => $media
            ];

            return $this->render('admin/media/show', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Media Show error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load media details');
            return $this->redirect('admin/media');
        }
    }

    /**
     * Show the form for editing the specified media
     */
    public function edit($id)
    {
        try {
            $mediaId = intval($id);
            if ($mediaId <= 0) {
                $this->setFlash('error', 'Invalid media ID');
                return $this->redirect('admin/media');
            }

            // Get media details
            $sql = "SELECT * FROM media WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$mediaId]);
            $media = $stmt->fetch();

            if (!$media) {
                $this->setFlash('error', 'Media not found');
                return $this->redirect('admin/media');
            }

            $data = [
                'page_title' => 'Edit Media - APS Dream Home',
                'active_page' => 'media',
                'media' => $media
            ];

            return $this->render('admin/media/edit', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Media Edit error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load media form');
            return $this->redirect('admin/media');
        }
    }

    /**
     * Update the specified media
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $mediaId = intval($id);
            if ($mediaId <= 0) {
                return $this->jsonError('Invalid media ID', 400);
            }

            $data = $_POST;

            // Check if media exists
            $sql = "SELECT * FROM media WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$mediaId]);
            $media = $stmt->fetch();

            if (!$media) {
                return $this->jsonError('Media not found', 404);
            }

            // Build update query
            $updateFields = [];
            $updateValues = [];

            if (isset($data['description'])) {
                $updateFields[] = "description = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['description'], 'string');
            }

            if (isset($data['alt_text'])) {
                $updateFields[] = "alt_text = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['alt_text'], 'string');
            }

            if (empty($updateFields)) {
                return $this->jsonError('No fields to update', 400);
            }

            $updateFields[] = "updated_at = NOW()";
            $updateValues[] = $mediaId;

            $sql = "UPDATE media SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($updateValues);

            if ($result) {
                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'media_updated', [
                    'media_id' => $mediaId,
                    'changes' => $data
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Media updated successfully'
                ]);
            }

            return $this->jsonError('Failed to update media', 500);
        } catch (Exception $e) {
            $this->loggingService->error("Media Update error: " . $e->getMessage());
            return $this->jsonError('Failed to update media', 500);
        }
    }

    /**
     * Remove the specified media
     */
    public function destroy($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $mediaId = intval($id);
            if ($mediaId <= 0) {
                return $this->jsonError('Invalid media ID', 400);
            }

            // Check if media exists
            $sql = "SELECT * FROM media WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$mediaId]);
            $media = $stmt->fetch();

            if (!$media) {
                return $this->jsonError('Media not found', 404);
            }

            // Delete file from filesystem
            if (file_exists($media['file_path'])) {
                unlink($media['file_path']);
            }

            // Delete media record
            $sql = "DELETE FROM media WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$mediaId]);

            if ($result) {
                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'media_deleted', [
                    'media_id' => $mediaId,
                    'file_name' => $media['original_name']
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Media deleted successfully'
                ]);
            }

            return $this->jsonError('Failed to delete media', 500);
        } catch (Exception $e) {
            $this->loggingService->error("Media Destroy error: " . $e->getMessage());
            return $this->jsonError('Failed to delete media', 500);
        }
    }

    /**
     * Validate uploaded file
     */
    private function validateFile(array $file): array
    {
        // Check file size (10MB max)
        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'error' => 'File size too large. Maximum 10MB allowed.'];
        }

        // Check file type
        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'application/pdf' => 'pdf',
            'text/plain' => 'txt',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx'
        ];

        $mimeType = mime_content_type($file['tmp_name']);
        if (!isset($allowedTypes[$mimeType])) {
            return ['valid' => false, 'error' => 'File type not allowed. Allowed types: JPG, PNG, GIF, WebP, PDF, TXT, DOC, DOCX, XLS, XLSX'];
        }

        return ['valid' => true];
    }

    /**
     * Get file type from MIME type
     */
    private function getFileType(string $mimeType): string
    {
        $imageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $documentTypes = ['application/pdf', 'text/plain', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $spreadsheetTypes = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];

        if (in_array($mimeType, $imageTypes)) {
            return 'image';
        } elseif (in_array($mimeType, $documentTypes)) {
            return 'document';
        } elseif (in_array($mimeType, $spreadsheetTypes)) {
            return 'spreadsheet';
        } else {
            return 'other';
        }
    }

    /**
     * Get media statistics
     */
    public function getStats()
    {
        try {
            $stats = [];

            // Total media files
            $sql = "SELECT COUNT(*) as total FROM media";
            $result = $this->db->fetchOne($sql);
            $stats['total_files'] = (int)($result['total'] ?? 0);

            // Total storage used
            $sql = "SELECT COALESCE(SUM(file_size), 0) as total FROM media";
            $result = $this->db->fetchOne($sql);
            $stats['total_storage'] = (int)($result['total'] ?? 0);

            // Files by type
            $sql = "SELECT file_type, COUNT(*) as count FROM media GROUP BY file_type";
            $stats['by_type'] = $this->db->fetchAll($sql) ?: [];

            // This month's uploads
            $sql = "SELECT COUNT(*) as total FROM media 
                    WHERE MONTH(created_at) = MONTH(CURRENT_DATE) 
                    AND YEAR(created_at) = YEAR(CURRENT_DATE)";
            $result = $this->db->fetchOne($sql);
            $stats['monthly_uploads'] = (int)($result['total'] ?? 0);

            return $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            $this->loggingService->error("Get Media Stats error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch media stats'
            ], 500);
        }
    }

    /**
     * JSON response helper
     */
    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * JSON error helper
     */
    private function jsonError(string $message, int $statusCode = 400): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }
}