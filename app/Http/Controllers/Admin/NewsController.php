<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\CoreFunctionsServiceCustom;
use App\Services\LoggingService;
use App\Core\Database;
use Exception;

/**
 * News Controller - Custom MVC Implementation
 * Handles news management operations in the Admin panel
 */
class NewsController extends AdminController
{
    private $loggingService;

    public function __construct()
    {
        parent::__construct();
        $this->loggingService = new LoggingService();

        // Register middlewares
        $this->middleware('csrf', ['only' => ['store', 'update', 'destroy']]);
    }

    /**
     * Display news list
     */
    public function index()
    {
        try {
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            $page = (int)($_GET['page'] ?? 1);
            $perPage = (int)($_GET['per_page'] ?? 20);

            $offset = ($page - 1) * $perPage;

            // Build query
            $sql = "SELECT n.*, COALESCE(n.author_name, 'Admin') as author_name
                    FROM news n
                    WHERE 1=1";
            $params = [];

            // Apply filters
            if (!empty($search)) {
                $sql .= " AND (n.title LIKE ? OR n.summary LIKE ? OR n.content LIKE ?)";
                $searchParam = '%' . $search . '%';
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if (!empty($status) && in_array($status, ['published', 'draft', 'archived'])) {
                $sql .= " AND n.status = ?";
                $params[] = $status;
            }

            $sql .= " ORDER BY n.created_at DESC";

            // Count total
            $countSql = preg_replace('/SELECT .* FROM/', 'SELECT COUNT(*) as total FROM', $sql, 1);
            $countResult = $this->db->fetch($countSql, $params);
            $total = $countResult['total'];

            // Apply pagination
            $sql .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $perPage;

            $news = $this->db->fetchAll($sql, $params);

            $data = [
                'page_title' => 'News Management - APS Dream Home',
                'active_page' => 'news',
                'news' => $news,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
                'filters' => [
                    'search' => $search,
                    'status' => $status
                ]
            ];

            return $this->render('admin/news/index', $data);
        } catch (Exception $e) {
            $this->loggingService->error("News Index error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load news');
            return $this->redirect(BASE_URL . '/admin/dashboard');
        }
    }

    /**
     * Show the form for creating a new news article
     */
    public function create()
    {
        try {
            $data = [
                'page_title' => 'Create News Article - APS Dream Home',
                'active_page' => 'news'
            ];

            return $this->render('admin/news/create', $data);
        } catch (Exception $e) {
            $this->loggingService->error("News Create error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load news form');
            return $this->redirect(BASE_URL . '/admin/news');
        }
    }

    /**
     * Store a newly created news article
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $data = $_POST;

            // Validate required fields
            $required = ['title', 'summary', 'content'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return $this->jsonError(ucfirst($field) . ' is required', 400);
                }
            }

            // Handle image upload
            $imagePath = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $imageValidation = $this->validateImage($_FILES['image']);
                if (!$imageValidation['valid']) {
                    return $this->jsonError($imageValidation['error'], 400);
                }
                $imagePath = $this->uploadImage($_FILES['image']);
                if (!$imagePath) {
                    return $this->jsonError('Failed to upload image', 500);
                }
            }

            // Insert news article
            $sql = "INSERT INTO news 
                    (title, summary, content, image, featured, status, author_id, published_at, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                CoreFunctionsServiceCustom::validateInput($data['title'], 'string'),
                CoreFunctionsServiceCustom::validateInput($data['summary'], 'string'),
                CoreFunctionsServiceCustom::validateInput($data['content'], 'string'),
                $imagePath,
                (int)($data['featured'] ?? 0),
                CoreFunctionsServiceCustom::validateInput($data['status'] ?? 'draft', 'string'),
                $_SESSION['user_id'] ?? 0,
                $data['published_at'] ?? null
            ]);

            if ($result) {
                $newsId = $this->db->lastInsertId();

                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'news_created', [
                    'news_id' => $newsId,
                    'title' => $data['title']
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'News article created successfully',
                    'news_id' => $newsId
                ]);
            }

            // Clean up uploaded image if database insert failed
            if ($imagePath && file_exists($imagePath)) {
                unlink($imagePath);
            }

            return $this->jsonError('Failed to create news article', 500);
        } catch (Exception $e) {
            $this->loggingService->error("News Store error: " . $e->getMessage());
            return $this->jsonError('Failed to create news article', 500);
        }
    }

    /**
     * Display the specified news article
     */
    public function show($id)
    {
        try {
            $newsId = intval($id);
            if ($newsId <= 0) {
                $this->setFlash('error', 'Invalid news ID');
                return $this->redirect(BASE_URL . '/admin/news');
            }

            // Get news article details
            $sql = "SELECT n.*, u.name as author_name
                    FROM news n
                    LEFT JOIN users u ON n.author_id = u.id
                    WHERE n.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$newsId]);
            $news = $stmt->fetch();

            if (!$news) {
                $this->setFlash('error', 'News article not found');
                return $this->redirect(BASE_URL . '/admin/news');
            }

            $data = [
                'page_title' => 'News Article Details - APS Dream Home',
                'active_page' => 'news',
                'news' => $news
            ];

            return $this->render('admin/news/show', $data);
        } catch (Exception $e) {
            $this->loggingService->error("News Show error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load news article');
            return $this->redirect(BASE_URL . '/admin/news');
        }
    }

    /**
     * Show the form for editing the specified news article
     */
    public function edit($id)
    {
        try {
            $newsId = intval($id);
            if ($newsId <= 0) {
                $this->setFlash('error', 'Invalid news ID');
                return $this->redirect(BASE_URL . '/admin/news');
            }

            // Get news article details
            $sql = "SELECT * FROM news WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$newsId]);
            $news = $stmt->fetch();

            if (!$news) {
                $this->setFlash('error', 'News article not found');
                return $this->redirect(BASE_URL . '/admin/news');
            }

            $data = [
                'page_title' => 'Edit News Article - APS Dream Home',
                'active_page' => 'news',
                'news' => $news
            ];

            return $this->render('admin/news/edit', $data);
        } catch (Exception $e) {
            $this->loggingService->error("News Edit error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load news form');
            return $this->redirect(BASE_URL . '/admin/news');
        }
    }

    /**
     * Update the specified news article
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $newsId = intval($id);
            if ($newsId <= 0) {
                return $this->jsonError('Invalid news ID', 400);
            }

            $data = $_POST;

            // Check if news article exists
            $sql = "SELECT * FROM news WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$newsId]);
            $news = $stmt->fetch();

            if (!$news) {
                return $this->jsonError('News article not found', 404);
            }

            // Handle image upload
            $imagePath = $news['image']; // Keep existing image by default
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $imageValidation = $this->validateImage($_FILES['image']);
                if (!$imageValidation['valid']) {
                    return $this->jsonError($imageValidation['error'], 400);
                }

                $newImagePath = $this->uploadImage($_FILES['image']);
                if (!$newImagePath) {
                    return $this->jsonError('Failed to upload image', 500);
                }

                // Delete old image if exists
                if ($news['image'] && file_exists($news['image'])) {
                    unlink($news['image']);
                }

                $imagePath = $newImagePath;
            }

            // Build update query
            $updateFields = [];
            $updateValues = [];

            if (!empty($data['title'])) {
                $updateFields[] = "title = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['title'], 'string');
            }

            if (!empty($data['summary'])) {
                $updateFields[] = "summary = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['summary'], 'string');
            }

            if (!empty($data['content'])) {
                $updateFields[] = "content = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['content'], 'string');
            }

            if (isset($data['featured'])) {
                $updateFields[] = "featured = ?";
                $updateValues[] = (int)$data['featured'];
            }

            if (isset($data['status'])) {
                $updateFields[] = "status = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['status'], 'string');
            }

            if (isset($data['published_at'])) {
                $updateFields[] = "published_at = ?";
                $updateValues[] = $data['published_at'];
            }

            $updateFields[] = "image = ?";
            $updateValues[] = $imagePath;
            $updateFields[] = "updated_at = NOW()";
            $updateValues[] = $newsId;

            $sql = "UPDATE news SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($updateValues);

            if ($result) {
                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'news_updated', [
                    'news_id' => $newsId,
                    'changes' => $data
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'News article updated successfully'
                ]);
            }

            return $this->jsonError('Failed to update news article', 500);
        } catch (Exception $e) {
            $this->loggingService->error("News Update error: " . $e->getMessage());
            return $this->jsonError('Failed to update news article', 500);
        }
    }

    /**
     * Remove the specified news article
     */
    public function destroy($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $newsId = intval($id);
            if ($newsId <= 0) {
                return $this->jsonError('Invalid news ID', 400);
            }

            // Check if news article exists
            $sql = "SELECT * FROM news WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$newsId]);
            $news = $stmt->fetch();

            if (!$news) {
                return $this->jsonError('News article not found', 404);
            }

            // Delete image if exists
            if ($news['image'] && file_exists($news['image'])) {
                unlink($news['image']);
            }

            // Delete news article
            $sql = "DELETE FROM news WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$newsId]);

            if ($result) {
                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'news_deleted', [
                    'news_id' => $newsId,
                    'title' => $news['title']
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'News article deleted successfully'
                ]);
            }

            return $this->jsonError('Failed to delete news article', 500);
        } catch (Exception $e) {
            $this->loggingService->error("News Destroy error: " . $e->getMessage());
            return $this->jsonError('Failed to delete news article', 500);
        }
    }

    /**
     * Validate uploaded image
     */
    private function validateImage(array $file): array
    {
        // Check file size (5MB max)
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'error' => 'Image size too large. Maximum 5MB allowed.'];
        }

        // Check image type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array(mime_content_type($file['tmp_name']), $allowedTypes)) {
            return ['valid' => false, 'error' => 'Invalid image type. Allowed types: JPG, PNG, GIF, WebP'];
        }

        return ['valid' => true];
    }

    /**
     * Upload image
     */
    private function uploadImage(array $file): ?string
    {
        try {
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = uniqid('news_') . '.' . $extension;

            // Create upload directory if it doesn't exist
            $uploadDir = 'uploads/news/' . date('Y/m');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filePath = $uploadDir . '/' . $fileName;

            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                return $filePath;
            }

            return null;
        } catch (Exception $e) {
            $this->loggingService->error("Upload Image error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get news statistics
     */
    public function getStats()
    {
        try {
            $stats = [];

            // Total news articles
            $sql = "SELECT COUNT(*) as total FROM news";
            $result = $this->db->fetchOne($sql);
            $stats['total_articles'] = (int)($result['total'] ?? 0);

            // Published articles
            $sql = "SELECT COUNT(*) as total FROM news WHERE status = 'published'";
            $result = $this->db->fetchOne($sql);
            $stats['published_articles'] = (int)($result['total'] ?? 0);

            // Draft articles
            $sql = "SELECT COUNT(*) as total FROM news WHERE status = 'draft'";
            $result = $this->db->fetchOne($sql);
            $stats['draft_articles'] = (int)($result['total'] ?? 0);

            // Featured articles
            $sql = "SELECT COUNT(*) as total FROM news WHERE featured = 1";
            $result = $this->db->fetchOne($sql);
            $stats['featured_articles'] = (int)($result['total'] ?? 0);

            // This month's articles
            $sql = "SELECT COUNT(*) as total FROM news 
                    WHERE MONTH(created_at) = MONTH(CURRENT_DATE) 
                    AND YEAR(created_at) = YEAR(CURRENT_DATE)";
            $result = $this->db->fetchOne($sql);
            $stats['monthly_articles'] = (int)($result['total'] ?? 0);

            return $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            $this->loggingService->error("Get News Stats error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch news stats'
            ], 500);
        }
    }
}
