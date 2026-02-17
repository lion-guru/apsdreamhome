<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\Media;
use App\Helpers\AuthHelper;
use App\Helpers\SecurityHelper;

class MediaController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        if (!AuthHelper::isAdmin()) {
            $this->redirect('admin/login');
        }
        $this->loadModel('Media');
    }

    public function index()
    {
        $media = Media::all(); // Assuming all() fetches all records
        $this->render('admin/media/index', [
            'media' => $media,
            'title' => 'Media Library'
        ], 'layouts/admin');
    }

    public function create()
    {
        $this->render('admin/media/create', [
            'title' => 'Upload Media'
        ], 'layouts/admin');
    }

    public function store()
    {
        if (!$this->validateCsrfToken()) {
            $_SESSION['error'] = 'Invalid CSRF token';
            $this->redirect('admin/media/create');
            return;
        }

        if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
            $media = new Media();
            // Assuming the Media model has an upload method that handles file movement and DB insertion
            // Based on previous file read, it does: upload($file, $userId)
            $userId = $_SESSION['admin_id'] ?? 0;
            
            if ($media->upload($_FILES['file'], $userId)) {
                $_SESSION['success'] = 'Media uploaded successfully';
                $this->redirect('admin/media');
            } else {
                $_SESSION['error'] = 'Failed to upload media';
                $this->redirect('admin/media/create');
            }
        } else {
            $_SESSION['error'] = 'No file selected or upload error';
            $this->redirect('admin/media/create');
        }
    }

    public function delete($id)
    {
        if (!$this->validateCsrfToken()) {
            $_SESSION['error'] = 'Invalid CSRF token';
            $this->redirect('admin/media');
            return;
        }

        $media = new Media();
        if ($media->delete($id)) {
            $_SESSION['success'] = 'Media deleted successfully';
        } else {
            $_SESSION['error'] = 'Failed to delete media';
        }
        $this->redirect('admin/media');
    }
}
