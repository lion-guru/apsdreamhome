<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Core\Database\Database;

class ProjectsAdminController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $db = Database::getInstance();
        $projects = $db->query("SELECT * FROM projects ORDER BY created_at DESC LIMIT 50")->fetchAll();

        $stats = [
            'total' => count($projects),
            'under_construction' => 0,
            'completed' => 0,
            'planning' => 0,
            'total_plots' => 0
        ];

        $this->data['page_title'] = 'Projects';
        $this->data['projects'] = $projects;
        $this->data['stats'] = $stats;
        
        return $this->render('admin/projects/index');
    }

    public function create()
    {
        $this->data['page_title'] = 'Create Project';
        return $this->render('admin/projects/create');
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $status = $_POST['status'] ?? 'planning';
            
            $db = Database::getInstance();
            $stmt = $db->prepare("INSERT INTO projects (name, status, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$name, $status]);
            
            $this->redirect('/admin/projects');
        }
    }

    public function edit($id)
    {
        $this->data['page_title'] = 'Edit Project';
        return $this->render('admin/projects/edit');
    }

    public function show($id)
    {
        $this->data['page_title'] = 'View Project';
        return $this->render('admin/projects/view');
    }

    public function images($id)
    {
        $this->data['page_title'] = 'Project Images';
        return $this->render('admin/projects/images');
    }

    public function destroy($id)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$id]);
        $this->redirect('/admin/projects');
    }
}