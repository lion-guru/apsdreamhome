<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Admin\AdminController;
use App\Services\Media\MediaLibraryService;

class MediaLibraryController extends AdminController
{
    private MediaLibraryService $mediaService;

    public function __construct()
    {
        parent::__construct();
        $this->mediaService = new MediaLibraryService();
    }

    public function index()
    {
        $category = $_GET['category'] ?? null;
        $search = $_GET['search'] ?? null;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 24;
        $offset = ($page - 1) * $perPage;

        $result = $this->mediaService->getMediaFiles($category, $search, $perPage, $offset);
        $files = $result['success'] ? $result['data'] : [];
        $stats = $this->mediaService->getMediaStats();
        $categories = $this->mediaService->getCategories();

        $this->render('admin.media.index', [
            'files' => $files,
            'stats' => $stats['success'] ? $stats['data'] : [],
            'categories' => $categories['success'] ? $categories['data'] : [],
            'currentCategory' => $category,
            'search' => $search,
            'page' => $page
        ]);
    }

    public function upload()
    {
        $this->render('admin.media.upload', [
            'categories' => $this->mediaService->getCategories()['data'] ?? []
        ]);
    }

    public function handleUpload()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->response(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $data = $_POST;
        $files = $_FILES;
        $result = $this->mediaService->handleUpload($data, $files);

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            $this->response($result, $result['success'] ? 200 : 400);
            return;
        }

        if ($result['success']) {
            header('Location: /admin/media-library?success=uploaded');
        } else {
            header('Location: /admin/media-library?error=' . urlencode($result['message']));
        }
        exit;
    }

    public function details($id = null)
    {
        $id = (int)($id ?? $_GET['id'] ?? 0);
        if ($id <= 0) {
            header('Location: /admin/media-library');
            exit;
        }

        $result = $this->mediaService->getMediaFile($id);
        if (!$result['success']) {
            header('Location: /admin/media-library?error=not_found');
            exit;
        }

        $this->render('admin.media.show', [
            'file' => $result['data']
        ]);
    }

    public function update($id = null)
    {
        $id = (int)($id ?? $_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->response(['success' => false, 'message' => 'Invalid ID'], 400);
            return;
        }

        $result = $this->mediaService->updateMediaFile(
            $id,
            $_POST['title'] ?? '',
            $_POST['description'] ?? '',
            $_POST['category'] ?? 'general',
            $_POST['tags'] ?? ''
        );

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            $this->response($result, $result['success'] ? 200 : 400);
            return;
        }

        header('Location: /admin/media-library/details/' . $id . ($result['success'] ? '?success=updated' : '?error=' . urlencode($result['message'])));
        exit;
    }

    public function delete($id = null)
    {
        $id = (int)($id ?? $_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->response(['success' => false, 'message' => 'Invalid ID'], 400);
            return;
        }

        $result = $this->mediaService->deleteMediaFile($id);

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            $this->response($result, $result['success'] ? 200 : 400);
            return;
        }

        header('Location: /admin/media-library' . ($result['success'] ? '?success=deleted' : '?error=' . urlencode($result['message'])));
        exit;
    }

    public function getMediaFiles()
    {
        $category = $_GET['category'] ?? null;
        $search = $_GET['search'] ?? null;
        $limit = min(100, max(1, (int)($_GET['limit'] ?? 50)));
        $offset = max(0, (int)($_GET['offset'] ?? 0));

        $result = $this->mediaService->getMediaFiles($category, $search, $limit, $offset);
        $this->response($result, $result['success'] ? 200 : 500);
    }

    public function getMediaFile($id = null)
    {
        $id = (int)($id ?? $_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->response(['success' => false, 'message' => 'Invalid ID'], 400);
            return;
        }

        $result = $this->mediaService->getMediaFile($id);
        $this->response($result, $result['success'] ? 200 : 404);
    }

    public function getCategories()
    {
        $result = $this->mediaService->getCategories();
        $this->response($result, $result['success'] ? 200 : 500);
    }

    public function getMediaStats()
    {
        $result = $this->mediaService->getMediaStats();
        $this->response($result, $result['success'] ? 200 : 500);
    }

    public function uploadFile()
    {
        $data = $_POST;
        $files = $_FILES;
        $result = $this->mediaService->handleUpload($data, $files);
        $this->response($result, $result['success'] ? 200 : 400);
    }

    public function updateFile($id = null)
    {
        $id = (int)($id ?? $_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->response(['success' => false, 'message' => 'Invalid ID'], 400);
            return;
        }

        $result = $this->mediaService->updateMediaFile(
            $id,
            $_POST['title'] ?? '',
            $_POST['description'] ?? '',
            $_POST['category'] ?? 'general',
            $_POST['tags'] ?? ''
        );
        $this->response($result, $result['success'] ? 200 : 400);
    }

    public function deleteFile($id = null)
    {
        $id = (int)($id ?? $_POST['id'] ?? $_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->response(['success' => false, 'message' => 'Invalid ID'], 400);
            return;
        }

        $result = $this->mediaService->deleteMediaFile($id);
        $this->response($result, $result['success'] ? 200 : 400);
    }

    public function download($id = null)
    {
        $id = (int)($id ?? $_GET['id'] ?? 0);
        if ($id <= 0) {
            header('Location: /admin/media-library');
            exit;
        }

        $result = $this->mediaService->getMediaFile($id);
        if (!$result['success']) {
            header('Location: /admin/media-library?error=not_found');
            exit;
        }

        $file = $result['data'];
        $filepath = $this->mediaService->getFilePath($file['filename']);

        if (!file_exists($filepath)) {
            header('Location: /admin/media-library?error=file_missing');
            exit;
        }

        header('Content-Type: ' . ($file['mime_type'] ?? 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . ($file['original_name'] ?? $file['filename']) . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    }

    public function preview($id = null)
    {
        $id = (int)($id ?? $_GET['id'] ?? 0);
        if ($id <= 0) {
            header('Location: /admin/media-library');
            exit;
        }

        $result = $this->mediaService->getMediaFile($id);
        if (!$result['success']) {
            header('Location: /admin/media-library?error=not_found');
            exit;
        }

        $file = $result['data'];
        $filepath = $this->mediaService->getFilePath($file['filename']);

        if (!file_exists($filepath)) {
            header('Location: /admin/media-library?error=file_missing');
            exit;
        }

        header('Content-Type: ' . ($file['mime_type'] ?? 'application/octet-stream'));
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    }

    public function createThumbnail($id = null)
    {
        $id = (int)($id ?? $_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->response(['success' => false, 'message' => 'Invalid ID'], 400);
            return;
        }

        $result = $this->mediaService->getMediaFile($id);
        if (!$result['success']) {
            $this->response(['success' => false, 'message' => 'File not found'], 404);
            return;
        }

        $file = $result['data'];
        $width = (int)($_POST['width'] ?? 300);
        $height = (int)($_POST['height'] ?? 300);

        $thumbPath = $this->mediaService->createThumbnail($file['filename'], $width, $height);
        if ($thumbPath) {
            $this->response(['success' => true, 'message' => 'Thumbnail created', 'path' => $thumbPath]);
        } else {
            $this->response(['success' => false, 'message' => 'Failed to create thumbnail'], 500);
        }
    }
}
