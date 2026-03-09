<?php

namespace App\Http\Controllers;

use App\Services\Communication\MediaService;
use App\Http\Controllers\Controller;

/**
 * Media Controller
 * Handles all media library operations
 */
class MediaController extends Controller
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
            $filters = request()->only(['category', 'type', 'search']);
            $page = request('page', 1);
            $limit = request('limit', 20);

            $media = $this->mediaService->getAllMedia($filters, $page, $limit);
            
            return view('media.index', compact('media', 'filters', 'page', 'limit'));

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load media library: ' . $e->getMessage());
        }
    }

    /**
     * Get media for templates via AJAX
     */
    public function getMediaForTemplates()
    {
        try {
            $category = request('category');
            $limit = request('limit', 10);
            
            $media = $this->mediaService->getMediaForTemplates($category, $limit);
            return response()->json(['success' => true, 'data' => $media]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get header images via AJAX
     */
    public function getHeaderImages()
    {
        try {
            $limit = request('limit', 10);
            $images = $this->mediaService->getHeaderImages($limit);
            return response()->json(['success' => true, 'data' => $images]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get team photos via AJAX
     */
    public function getTeamPhotos()
    {
        try {
            $limit = request('limit', 10);
            $photos = $this->mediaService->getTeamPhotos($limit);
            return response()->json(['success' => true, 'data' => $photos]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get property images via AJAX
     */
    public function getPropertyImages()
    {
        try {
            $limit = request('limit', 10);
            $images = $this->mediaService->getPropertyImages($limit);
            return response()->json(['success' => true, 'data' => $images]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get project images via AJAX
     */
    public function getProjectImages()
    {
        try {
            $limit = request('limit', 10);
            $images = $this->mediaService->getProjectImages($limit);
            return response()->json(['success' => true, 'data' => $images]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get documents via AJAX
     */
    public function getDocuments()
    {
        try {
            $category = request('category');
            $limit = request('limit', 10);
            $documents = $this->mediaService->getDocuments($category, $limit);
            return response()->json(['success' => true, 'data' => $documents]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get carousel images via AJAX
     */
    public function getCarouselImages()
    {
        try {
            $limit = request('limit', 5);
            $images = $this->mediaService->getCarouselImages($limit);
            return response()->json(['success' => true, 'data' => $images]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Upload media file
     */
    public function upload()
    {
        try {
            if (!request()->hasFile('media_file')) {
                return response()->json(['success' => false, 'message' => 'No file uploaded']);
            }

            $file = request()->file('media_file');
            $metadata = request()->only(['title', 'description', 'category']);

            if ($this->mediaService->uploadMedia($file, $metadata)) {
                return response()->json(['success' => true, 'message' => 'File uploaded successfully']);
            } else {
                return response()->json(['success' => false, 'message' => 'Failed to upload file']);
            }

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
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
                return response()->json(['success' => true, 'url' => $url]);
            } else {
                return response()->json(['success' => false, 'message' => 'Media not found']);
            }

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
