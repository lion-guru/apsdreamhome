<?php

namespace App\Http\Controllers\Admin;

use App\Services\File\FileManagerService;

/**
 * Admin File Manager Controller
 * Manage files and documents from admin panel
 */
class AdminFileController extends AdminController
{
    private $fileService;
    
    public function __construct()
    {
        parent::__construct();
        $this->fileService = new FileManagerService();
    }
    
    /**
     * File manager dashboard
     */
    public function index(): void
    {
        $page = $_GET['page'] ?? 1;
        $category = $_GET['category'] ?? null;
        $search = $_GET['search'] ?? null;
        
        $filters = [
            'category' => $category,
            'search' => $search
        ];
        
        $files = $this->fileService->listFiles($filters, $page, 50);
        $stats = $this->fileService->getStorageStats();
        
        $this->render('admin/files/index', [
            'files' => $files,
            'stats' => $stats,
            'category' => $category,
            'search' => $search,
            'title' => 'File Manager'
        ]);
    }
    
    /**
     * Upload file
     */
    public function upload(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) { $this->validateCsrfOrFail();
            $options = [
                'category' => $_POST['category'] ?? 'general',
                'description' => $_POST['description'] ?? null,
                'uploaded_by' => $_SESSION['admin_id'] ?? 1,
                'uploaded_by_type' => 'admin',
                'is_public' => isset($_POST['is_public']) ? 1 : 0,
                'tags' => !empty($_POST['tags']) ? explode(',', $_POST['tags']) : []
            ];
            
            $result = $this->fileService->upload($_FILES['file'], $options);
            
            if ($result['success']) {
                $_SESSION['success'] = 'File uploaded successfully';
            } else {
                $_SESSION['error'] = $result['error'];
            }
            
            redirect('/admin/files');
            exit;
        }
        
        $this->render('admin/files/upload', [
            'title' => 'Upload File'
        ]);
    }
    
    /**
     * View file details
     */
    public function fileDetails(string $uuid): void
    {
        $file = $this->fileService->getFile($uuid);
        
        if (!$file) {
            $_SESSION['error'] = 'File not found';
            redirect('/admin/files');
            exit;
        }
        
        // Get versions if versioned
        $versions = [];
        if ($file['is_versioned']) {
            $versions = $this->fileService->getVersions($file['id']);
        }
        
        $this->render('admin/files/view', [
            'file' => $file,
            'versions' => $versions,
            'title' => 'File: ' . $file['original_name']
        ]);
    }
    
    /**
     * Download file
     */
    public function download(string $uuid): void
    {
        $result = $this->fileService->download($uuid, $_SESSION['admin_id'] ?? 1, 'admin');
        
        if ($result['success']) {
            $filePath = $result['file_path'];
            $originalName = $result['original_name'];
            $mimeType = $result['mime_type'] ?? 'application/octet-stream';
            
            if (file_exists($filePath)) {
                header('Content-Type: ' . $mimeType);
                header('Content-Disposition: attachment; filename="' . $originalName . '"');
                header('Content-Length: ' . filesize($filePath));
                readfile($filePath);
                exit;
            }
        }
        
        $_SESSION['error'] = 'File not found or access denied';
        redirect('/admin/files');
        exit;
    }
    
    /**
     * Delete file
     */
    public function delete(string $uuid): void
    {
        $result = $this->fileService->delete($uuid, $_SESSION['admin_id'] ?? 1, 'admin');
        
        if ($result['success']) {
            $_SESSION['success'] = 'File deleted successfully';
        } else {
            $_SESSION['error'] = $result['error'];
        }
        
        redirect('/admin/files');
        exit;
    }
    
    /**
     * Upload new version
     */
    public function uploadVersion(string $uuid): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) { $this->validateCsrfOrFail();
            $options = [
                'uploaded_by' => $_SESSION['admin_id'] ?? 1,
                'uploaded_by_type' => 'admin',
                'change_notes' => $_POST['change_notes'] ?? 'New version uploaded'
            ];
            
            $result = $this->fileService->createVersion($uuid, $_FILES['file'], $options);
            
            if ($result['success']) {
                $_SESSION['success'] = 'New version uploaded successfully';
            } else {
                $_SESSION['error'] = $result['error'];
            }
            
            redirect('/admin/files/details/' . $uuid);
            exit;
        }
    }
    
    /**
     * Browse files by category
     */
    public function browse(): void
    {
        $category = $_GET['category'] ?? 'general';
        $page = $_GET['page'] ?? 1;
        
        $files = $this->fileService->listFiles(['category' => $category], $page, 50);
        
        $this->render('admin/files/browse', [
            'files' => $files,
            'category' => $category,
            'categories' => ['property', 'user', 'document', 'payment', 'general'],
            'title' => 'Browse: ' . ucfirst($category)
        ]);
    }
    
    /**
     * Storage analytics
     */
    public function storage(): void
    {
        $stats = $this->fileService->getStorageStats();
        
        // Get file type breakdown
        $db = \App\Core\Database\Database::getInstance();
        $sql = "SELECT 
            file_type,
            COUNT(*) as count,
            SUM(size_bytes) as total_size
            FROM files
            GROUP BY file_type
            ORDER BY total_size DESC";
        
        $typeBreakdown = $db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        
        // Get monthly upload trend
        $trendSql = "SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as uploads,
            SUM(size_bytes) as size
            FROM files
            WHERE created_at > DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY month
            ORDER BY month";
        
        $trend = $db->query($trendSql)->fetchAll(\PDO::FETCH_ASSOC);
        
        $this->render('admin/files/storage', [
            'stats' => $stats,
            'type_breakdown' => $typeBreakdown,
            'trend' => $trend,
            'title' => 'Storage Analytics'
        ]);
    }
}
