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
        $db = Database::getInstance();
        $this->data['page_title'] = 'Create Project';
        $this->data['states'] = $db->fetchAll("SELECT * FROM states ORDER BY name");
        $this->data['districts'] = [];
        $this->data['colonies'] = [];
        return $this->render('admin/projects/create');
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            $fields = ['name', 'project_type', 'description', 'developer_name', 'developer_phone',
                'address', 'state_id', 'district_id', 'colony_id',
                'total_area', 'total_plots', 'available_plots', 'booked_plots', 'sold_plots',
                'price_range_min', 'price_range_max', 'avg_price_per_sqft', 'status',
                'launch_date', 'completion_date', 'possession_date',
                'marketing_description', 'tags', 'is_featured', 'is_hot_deal'];

            $data = [];
            foreach ($fields as $f) {
                $v = $_POST[$f] ?? null;
                if (in_array($f, ['is_featured', 'is_hot_deal'])) $v = $v ? 1 : 0;
                if ($v === '' || $v === null) $v = null;
                $data[] = $v;
            }

            $placeholders = rtrim(str_repeat('?,', count($fields)), ',');
            $cols = implode(',', $fields);

            $db = Database::getInstance();
            $stmt = $db->prepare("INSERT INTO projects ($cols, created_at) VALUES ($placeholders, NOW())");
            $stmt->execute($data);

            $this->setFlash('success', 'Project created successfully');
            $this->redirect('/admin/projects');
        }
    }

    public function edit($id)
    {
        $db = Database::getInstance();
        $project = $db->fetch("SELECT * FROM projects WHERE id = ?", [$id]);
        $this->data['project'] = $project ?: [];
        $this->data['states'] = $db->fetchAll("SELECT * FROM states ORDER BY name");
        $this->data['districts'] = $db->fetchAll("SELECT * FROM districts ORDER BY name");
        $this->data['colonies'] = $db->fetchAll("SELECT * FROM colonies ORDER BY name");
        $this->data['page_title'] = 'Edit Project';
        return $this->render('admin/projects/edit');
    }

    public function show($id)
    {
        $db = Database::getInstance();
        $project = $db->fetch("SELECT * FROM projects WHERE id = ?", [$id]);
        $this->data['project'] = $project ?: [];
        $this->data['page_title'] = 'View Project';
        return $this->render('admin/projects/view');
    }

    public function images($id)
    {
        $db = Database::getInstance();
        $project = $db->fetch("SELECT * FROM projects WHERE id = ?", [$id]);
        try {
            $images = $db->fetchAll("SELECT * FROM project_images WHERE project_id = ? ORDER BY display_order", [$id]);
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
        }
        $this->data['project'] = $project ?: [];
        $this->data['images'] = $images ?: [];
        $this->data['page_title'] = 'Project Images';
        return $this->render('admin/projects/images');
    }

    /**
     * Update project status
     */
    public function status($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            $db = Database::getInstance();
            $status = $_POST['status'] ?? 'planning';

            $stmt = $db->prepare("UPDATE projects SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$status, $id]);

            $this->setFlash('success', 'Project status updated');
        }
        $this->redirect('/admin/projects');
    }

    /**
     * Update project details
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            $db = Database::getInstance();
            $name = $_POST['name'] ?? '';
            $status = $_POST['status'] ?? 'planning';
            $description = $_POST['description'] ?? '';

            $stmt = $db->prepare("UPDATE projects SET name = ?, status = ?, description = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$name, $status, $description, $id]);

            $this->setFlash('success', 'Project updated successfully');
        }
        $this->redirect('/admin/projects');
    }

    /**
     * View a single project (alias for show)
     */
    public function detail($id)
    {
        return $this->show($id);
    }

    public function delete($id)
    {
        $this->destroy($id);
    }

    public function destroy($id)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$id]);
        $this->redirect('/admin/projects');
    }
}