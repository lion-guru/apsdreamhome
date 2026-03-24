<?php

namespace App\Controllers\Media;

use App\Services\Media\MediaLibraryService;
use App\Services\Auth\AuthenticationService;
use App\Core\ViewRenderer;

/**
 * Media Library Controller - APS Dream Home
 * Custom MVC implementation without Laravel dependencies
 */
class MediaLibraryController
{
    private $mediaService;
    private $authService;
    private $viewRenderer;

    public function __construct()
    {
        $this->mediaService = new MediaLibraryService();
        $this->authService = new AuthenticationService();
        $this->viewRenderer = new ViewRenderer();
    }

    /**
     * Show media library
     */
    public function index($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            $_SESSION['errors'] = ['Please login to access media library'];
            $this->redirect('/login');
            return;
        }

        $category = $request['get']['category'] ?? null;
        $search = $request['get']['search'] ?? null;
        $page = max(1, intval($request['get']['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        // Get media files
        $result = $this->mediaService->getMediaFiles($category, $search, $limit, $offset);

        // Get categories
        $categoriesResult = $this->mediaService->getCategories();

        $data = [
            'title' => 'Media Library - APS Dream Home',
            'user' => $this->authService->getCurrentUser(),
            'files' => $result['success'] ? $result['data'] : [],
            'categories' => $categoriesResult['success'] ? $categoriesResult['data'] : [],
            'current_category' => $category,
            'search' => $search,
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors']);

        return $this->viewRenderer->render('media/index', $data);
    }

    /**
     * Show upload form
     */
    public function upload($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            $_SESSION['errors'] = ['Please login to upload files'];
            $this->redirect('/login');
            return;
        }

        // Get categories for dropdown
        $categoriesResult = $this->mediaService->getCategories();

        $data = [
            'title' => 'Upload Media - APS Dream Home',
            'user' => $this->authService->getCurrentUser(),
            'categories' => $categoriesResult['success'] ? $categoriesResult['data'] : [],
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? [],
            'old_input' => $_SESSION['old_input'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors'], $_SESSION['old_input']);

        return $this->viewRenderer->render('media/upload', $data);
    }

    /**
     * Handle file upload
     */
    public function handleUpload($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $data = [
            'title' => trim($request['post']['title'] ?? ''),
            'description' => trim($request['post']['description'] ?? ''),
            'category' => trim($request['post']['category'] ?? 'general'),
            'tags' => trim($request['post']['tags'] ?? '')
        ];

        $files = $request['files'] ?? [];

        $result = $this->mediaService->handleUpload($data, $files);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            $this->redirect('/media');
        } else {
            $_SESSION['errors'] = [$result['message']];
            $_SESSION['old_input'] = $data;
            $this->redirect('/media/upload');
        }

        return $result;
    }

    /**
     * Show media file details
     */
    public function details($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            $_SESSION['errors'] = ['Please login to access media library'];
            $this->redirect('/login');
            return;
        }

        $id = $request['params']['id'] ?? null;

        if (!$id) {
            $_SESSION['errors'] = ['Media ID is required'];
            $this->redirect('/media');
            return;
        }

        $result = $this->mediaService->getMediaFile($id);

        if (!$result['success']) {
            $_SESSION['errors'] = [$result['message']];
            $this->redirect('/media');
            return;
        }

        $data = [
            'title' => 'Media Details - APS Dream Home',
            'user' => $this->authService->getCurrentUser(),
            'file' => $result['data'],
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors']);

        return $this->viewRenderer->render('media/details', $data);
    }

    /**
     * Update media file
     */
    public function update($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $id = $request['params']['id'] ?? null;
        $title = trim($request['post']['title'] ?? '');
        $description = trim($request['post']['description'] ?? '');
        $category = trim($request['post']['category'] ?? '');
        $tags = trim($request['post']['tags'] ?? '');

        if (!$id) {
            return [
                'success' => false,
                'message' => 'Media ID is required'
            ];
        }

        $result = $this->mediaService->updateMediaFile($id, $title, $description, $category, $tags);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['errors'] = [$result['message']];
        }

        $this->redirect("/media/details/$id");

        return $result;
    }

    /**
     * Delete media file
     */
    public function delete($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $id = $request['params']['id'] ?? null;

        if (!$id) {
            return [
                'success' => false,
                'message' => 'Media ID is required'
            ];
        }

        $result = $this->mediaService->deleteMediaFile($id);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['errors'] = [$result['message']];
        }

        $this->redirect('/media');

        return $result;
    }

    /**
     * Get media files (AJAX)
     */
    public function getMediaFiles($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $category = $request['get']['category'] ?? null;
        $search = $request['get']['search'] ?? null;
        $limit = min(max(intval($request['get']['limit'] ?? 20), 1), 100);
        $offset = max(0, intval($request['get']['offset'] ?? 0));

        return $this->mediaService->getMediaFiles($category, $search, $limit, $offset);
    }

    /**
     * Get media file (AJAX)
     */
    public function getMediaFile($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $id = $request['get']['id'] ?? null;

        if (!$id) {
            return [
                'success' => false,
                'message' => 'Media ID is required'
            ];
        }

        return $this->mediaService->getMediaFile($id);
    }

    /**
     * Get categories (AJAX)
     */
    public function getCategories($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        return $this->mediaService->getCategories();
    }

    /**
     * Get media statistics (AJAX)
     */
    public function getMediaStats($request)
    {
        // Check authentication and admin access
        if (!$this->authService->isAuthenticated() || !$this->isAdmin($this->authService->getCurrentUser())) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        return $this->mediaService->getMediaStats();
    }

    /**
     * Upload file (AJAX)
     */
    public function uploadFile($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $data = [
            'title' => trim($request['post']['title'] ?? ''),
            'description' => trim($request['post']['description'] ?? ''),
            'category' => trim($request['post']['category'] ?? 'general'),
            'tags' => trim($request['post']['tags'] ?? '')
        ];

        $files = $request['files'] ?? [];

        return $this->mediaService->handleUpload($data, $files);
    }

    /**
     * Update file (AJAX)
     */
    public function updateFile($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $id = $request['post']['id'] ?? null;
        $title = trim($request['post']['title'] ?? '');
        $description = trim($request['post']['description'] ?? '');
        $category = trim($request['post']['category'] ?? '');
        $tags = trim($request['post']['tags'] ?? '');

        if (!$id) {
            return [
                'success' => false,
                'message' => 'Media ID is required'
            ];
        }

        return $this->mediaService->updateMediaFile($id, $title, $description, $category, $tags);
    }

    /**
     * Delete file (AJAX)
     */
    public function deleteFile($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $id = $request['post']['id'] ?? null;

        if (!$id) {
            return [
                'success' => false,
                'message' => 'Media ID is required'
            ];
        }

        return $this->mediaService->deleteMediaFile($id);
    }

    /**
     * Download file
     */
    public function download($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            $_SESSION['errors'] = ['Please login to download files'];
            $this->redirect('/login');
            return;
        }

        $id = $request['params']['id'] ?? null;

        if (!$id) {
            $_SESSION['errors'] = ['Media ID is required'];
            $this->redirect('/media');
            return;
        }

        $result = $this->mediaService->getMediaFile($id);

        if (!$result['success']) {
            $_SESSION['errors'] = [$result['message']];
            $this->redirect('/media');
            return;
        }

        $file = $result['data'];
        $filepath = $this->mediaService->getFilePath($file['filename']);

        if (!file_exists($filepath)) {
            $_SESSION['errors'] = ['File not found'];
            $this->redirect('/media');
            return;
        }

        // Set headers for file download
        $filename = $file['original_name'];

        header('Content-Type: ' . $file['mime_type']);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . $file['file_size']);
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');

        readfile($filepath);
        exit;
    }

    /**
     * Show file preview
     */
    public function preview($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            $_SESSION['errors'] = ['Please login to view files'];
            $this->redirect('/login');
            return;
        }

        $id = $request['params']['id'] ?? null;

        if (!$id) {
            $_SESSION['errors'] = ['Media ID is required'];
            $this->redirect('/media');
            return;
        }

        $result = $this->mediaService->getMediaFile($id);

        if (!$result['success']) {
            $_SESSION['errors'] = [$result['message']];
            $this->redirect('/media');
            return;
        }

        $file = $result['data'];

        if (!$file['is_image']) {
            $_SESSION['errors'] = ['Preview only available for images'];
            $this->redirect("/media/details/$id");
            return;
        }

        $data = [
            'title' => 'Preview - ' . $file['title'] . ' - APS Dream Home',
            'user' => $this->authService->getCurrentUser(),
            'file' => $file
        ];

        return $this->viewRenderer->render('media/preview', $data);
    }

    /**
     * Create thumbnail (AJAX)
     */
    public function createThumbnail($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $id = $request['post']['id'] ?? null;
        $width = intval($request['post']['width'] ?? 300);
        $height = intval($request['post']['height'] ?? 300);

        if (!$id) {
            return [
                'success' => false,
                'message' => 'Media ID is required'
            ];
        }

        $result = $this->mediaService->getMediaFile($id);

        if (!$result['success']) {
            return $result;
        }

        $file = $result['data'];

        if (!$file['is_image']) {
            return [
                'success' => false,
                'message' => 'Thumbnails can only be created for images'
            ];
        }

        $thumbnailFilename = $this->mediaService->createThumbnail($file['filename'], $width, $height);

        if ($thumbnailFilename) {
            return [
                'success' => true,
                'message' => 'Thumbnail created successfully',
                'thumbnail_url' => $this->mediaService->getFileUrl('thumbnails/' . $thumbnailFilename)
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to create thumbnail'
            ];
        }
    }

    /**
     * Check if user is admin
     */
    private function isAdmin($user)
    {
        return $user && ($user['role'] === 'admin' || $user['role'] === 'super_admin');
    }

    /**
     * Redirect helper
     */
    private function redirect($url)
    {
        if (!headers_sent()) {
            header("Location: $url");
            exit;
        } else {
            echo '<script>window.location.href = "' . $url . '";</script>';
            exit;
        }
    }
}
