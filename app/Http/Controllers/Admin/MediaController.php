<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Models\Media;
use App\Helpers\SecurityHelper;

class MediaController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->loadModel('Media');
    }

    public function index()
    {
        $media = Media::all(); // Assuming all() fetches all records
        $this->render('admin/media/index', [
            'media' => $media,
            'title' => $this->mlSupport->translate('Media Library')
        ]);
    }

    public function create()
    {
        $this->render('admin/media/create', [
            'title' => $this->mlSupport->translate('Upload Media')
        ]);
    }

    public function store()
    {
        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Invalid CSRF token'));
            $this->redirect('admin/media/create');
            return;
        }

        if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
            $media = new Media();
            // Assuming the Media model has an upload method that handles file movement and DB insertion
            // Based on previous file read, it does: upload($file, $userId)
            $userId = $_SESSION['admin_id'] ?? 0;

            if ($media->upload($_FILES['file'], $userId)) {
                $this->setFlash('success', $this->mlSupport->translate('Media uploaded successfully'));
                $this->redirect('admin/media');
            } else {
                $this->setFlash('error', $this->mlSupport->translate('Failed to upload media'));
                $this->redirect('admin/media/create');
            }
        } else {
            $this->setFlash('error', $this->mlSupport->translate('No file selected or upload error'));
            $this->redirect('admin/media/create');
        }
    }

    public function delete($id)
    {
        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Invalid CSRF token'));
            $this->redirect('admin/media');
            return;
        }

        $media = new Media();
        if ($media->delete($id)) {
            $this->setFlash('success', $this->mlSupport->translate('Media deleted successfully'));
        } else {
            $this->setFlash('error', $this->mlSupport->translate('Failed to delete media'));
        }
        $this->redirect('admin/media');
    }
}
