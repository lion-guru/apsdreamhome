<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Models\News;
use App\Helpers\SecurityHelper;

class NewsController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->loadModel('News');
    }

    public function index()
    {
        $news = News::all();
        $this->render('admin/news/index', [
            'news' => $news,
            'title' => $this->mlSupport->translate('Manage News')
        ]);
    }

    public function create()
    {
        $this->render('admin/news/create', [
            'title' => $this->mlSupport->translate('Add News')
        ]);
    }

    public function store()
    {
        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Invalid CSRF token'));
            $this->redirect('admin/news/create');
            return;
        }

        $title = SecurityHelper::sanitize($_POST['title']);
        $date = SecurityHelper::sanitize($_POST['date']);
        $summary = SecurityHelper::sanitize($_POST['summary']);
        $content = $_POST['content']; // Allow HTML for content, sanitize in view or use purifier

        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $uploadDir = 'uploads/news/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileName = time() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $image = $targetPath;
            }
        }

        $news = new News();
        $news->title = $title;
        $news->date = $date;
        $news->summary = $summary;
        $news->content = $content;
        $news->image = $image;

        if ($news->save()) {
            $this->setFlash('success', $this->mlSupport->translate('News added successfully'));
            $this->redirect('admin/news');
        } else {
            $this->setFlash('error', $this->mlSupport->translate('Failed to add news'));
            $this->redirect('admin/news/create');
        }
    }

    public function edit($id)
    {
        $news = News::find($id);
        if (!$news) {
            $this->setFlash('error', $this->mlSupport->translate('News not found'));
            $this->redirect('admin/news');
            return;
        }

        $this->render('admin/news/edit', [
            'news' => $news,
            'title' => $this->mlSupport->translate('Edit News')
        ]);
    }

    public function update($id)
    {
        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Invalid CSRF token'));
            $this->redirect('admin/news/edit/' . $id);
            return;
        }

        $news = News::find($id);
        if (!$news) {
            $_SESSION['error'] = 'News not found';
            $this->redirect('admin/news');
            return;
        }

        $news->title = SecurityHelper::sanitize($_POST['title']);
        $news->date = SecurityHelper::sanitize($_POST['date']);
        $news->summary = SecurityHelper::sanitize($_POST['summary']);
        $news->content = $_POST['content'];

        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $uploadDir = 'uploads/news/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileName = time() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                // Delete old image if exists
                if ($news->image && file_exists($news->image)) {
                    unlink($news->image);
                }
                $news->image = $targetPath;
            }
        }

        if ($news->save()) {
            $_SESSION['success'] = 'News updated successfully';
            $this->redirect('admin/news');
        } else {
            $_SESSION['error'] = 'Failed to update news';
            $this->redirect('admin/news/edit/' . $id);
        }
    }

    public function delete($id)
    {
        if (!$this->validateCsrfToken()) {
            $_SESSION['error'] = 'Invalid CSRF token';
            $this->redirect('admin/news');
            return;
        }

        $news = News::find($id);
        if ($news) {
            if ($news->image && file_exists($news->image)) {
                unlink($news->image);
            }
            $news->delete();
            $_SESSION['success'] = 'News deleted successfully';
        } else {
            $_SESSION['error'] = 'News not found';
        }
        $this->redirect('admin/news');
    }
}
