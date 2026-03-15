<?php

namespace App\Http\Controllers;

use App\Services\Communication\MediaService;
use App\Http\Controllers\BaseController;

/**
 * Media Controller
 * Handles all media library operations
 */
class MediaController extends BaseController
{
    private MediaService $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
        $this->middleware('auth');
    }

    /**
     * Display media library
     */
    public function index()
    {
        try {
            $filters = $_REQUEST['category'] ?? '';
            $type = $_REQUEST['type'] ?? '';
            $search = $_REQUEST['search'] ?? '';
            $page = (int)($_REQUEST['page'] ?? 1);
            $limit = (int)($_REQUEST['limit'] ?? 20);

            $media = $this->mediaService->getAllMedia($filters, $page, $limit);

            return $this->render('media/index', compact('media', 'filters', 'page', 'limit'));
        } catch (\Exception $e) {
            return $this->render('media/index', compact('media', 'filters', 'page', 'limit'));
        }
    }

    /**
     * Get media for templates via AJAX
     */
    public function getMediaForTemplates()
    {
        try {
            $category = $_REQUEST['category'] ?? '';
            $limit = (int)($_REQUEST['limit'] ?? 10);

            $media = $this->mediaService->getMediaForTemplates($category, $limit);
            return $this->jsonResponse(['success' => true, 'data' => $media]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get header images via AJAX
     */
    public function getHeaderImages()
    {
        try {
            $limit = (int)($_REQUEST['limit'] ?? 10);
            $images = $this->mediaService->getHeaderImages($limit);
            return $this->jsonResponse(['success' => true, 'data' => $images]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get team photos via AJAX
     */
    public function getTeamPhotos()
    {
        try {
            $limit = (int)($_REQUEST['limit'] ?? 10);
            $photos = $this->mediaService->getTeamPhotos($limit);
            return $this->jsonResponse(['success' => true, 'data' => $photos]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get property images via AJAX
     */
    public function getPropertyImages()
    {
        try {
            $limit = (int)($_REQUEST['limit'] ?? 10);
            $images = $this->mediaService->getPropertyImages($limit);
            return $this->jsonResponse(['success' => true, 'data' => $images]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get project images via AJAX
     */
    public function getProjectImages()
    {
        try {
            $limit = (int)($_REQUEST['limit'] ?? 10);
            $images = $this->mediaService->getProjectImages($limit);
            return $this->jsonResponse(['success' => true, 'data' => $images]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get documents via AJAX
     */
    public function getDocuments()
    {
        try {
            $category = $_REQUEST['category'] ?? '';
            $limit = (int)($_REQUEST['limit'] ?? 10);
            $documents = $this->mediaService->getDocuments($category, $limit);
            return $this->jsonResponse(['success' => true, 'data' => $documents]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get carousel images via AJAX
     */
    public function getCarouselImages()
    {
        try {
            $limit = (int)($_REQUEST['limit'] ?? 5);
            $images = $this->mediaService->getCarouselImages($limit);
            return $this->jsonResponse(['success' => true, 'data' => $images]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Upload media file
     */
    public function upload()
    {
        try {
            if (!isset($_FILES['media_file']) || !$_FILES['media_file']['name']) {
                return $this->jsonResponse(['success' => false, 'message' => 'No file uploaded']);
            }

            $file = $_FILES['media_file'] ?? null;
            $metadata = $_REQUEST['title'] ?? '';
            $description = $_REQUEST['description'] ?? '';
            $category = $_REQUEST['category'] ?? '';

            if ($this->mediaService->uploadMedia($file, $metadata)) {
                return $this->jsonResponse(['success' => true, 'message' => 'File uploaded successfully']);
            } else {
                return $this->jsonResponse(['success' => false, 'message' => 'Failed to upload file']);
            }
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get media URL
     */
    public function getMediaUrl($id)
    {
        try {
            $url = $this->mediaService->getMediaUrl($id);

            if ($url) {
                return $this->jsonResponse(['success' => true, 'url' => $url]);
            } else {
                return $this->jsonResponse(['success' => false, 'message' => 'Media not found']);
            }
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
