<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Core\Database\Database;

class ProjectsAdminController extends AdminController
{
    use \App\Traits\TenantAwareTrait;
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $db = Database::getInstance();
        $projects = $db->query("SELECT p.*, d.name as district_name, s.name as state_name, c.name as colony_name FROM projects p LEFT JOIN colonies c ON p.colony_id = c.id LEFT JOIN districts d ON p.district_id = d.id LEFT JOIN states s ON p.state_id = s.id ORDER BY p.created_at DESC LIMIT 50")->fetchAll();

        // Compute real stats
        $stats = [
            'total'              => count($projects),
            'under_construction' => 0,
            'completed'          => 0,
            'planning'           => 0,
            'on_hold'            => 0,
            'total_plots'        => 0,
            'available_plots'    => 0,
            'sold_plots'         => 0,
        ];
        foreach ($projects as $p) {
            $st = $p['status'] ?? 'planning';
            if ($st === 'under_construction') $stats['under_construction']++;
            elseif ($st === 'completed')      $stats['completed']++;
            elseif ($st === 'planning')       $stats['planning']++;
            elseif ($st === 'on_hold')        $stats['on_hold']++;
            $stats['total_plots']     += (int)($p['total_plots'] ?? 0);
            $stats['available_plots'] += (int)($p['available_plots'] ?? 0);
            $stats['sold_plots']      += (int)($p['sold_plots'] ?? 0);
        }

        $this->data['page_title'] = 'Projects & Townships';
        $this->data['projects']   = $projects;
        $this->data['stats']      = $stats;

        return $this->render('admin/projects/index');
    }

    public function create()
    {
        $db = Database::getInstance();
        $this->data['page_title'] = 'Create New Project';
        $this->data['states']     = $db->fetchAll("SELECT * FROM states ORDER BY name");
        $this->data['districts']  = $db->fetchAll("SELECT * FROM districts ORDER BY name");
        $this->data['colonies']   = $db->fetchAll("SELECT * FROM colonies ORDER BY name");
        return $this->render('admin/projects/create');
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfOrFail();
            $fields = [
                'name', 'project_type', 'description', 'developer_name', 'developer_contact', 'developer_phone',
                'address', 'state_id', 'district_id', 'colony_id',
                'total_area', 'total_plots', 'available_plots', 'booked_plots', 'sold_plots',
                'price_range_min', 'price_range_max', 'avg_price_per_sqft', 'status',
                'launch_date', 'completion_date', 'possession_date',
                'marketing_description', 'tags', 'is_featured', 'is_hot_deal',
                'rera_number', 'progress_pct', 'project_budget', 'amount_spent',
                'project_manager', 'site_supervisor', 'contractor_name',
            ];

            $data = [];
            foreach ($fields as $f) {
                $v = $_POST[$f] ?? null;
                if (in_array($f, ['is_featured', 'is_hot_deal'])) $v = $v ? 1 : 0;
                if ($v === '' || $v === null) $v = null;
                $data[] = $v;
            }

            $fields[] = 'tenant_id';
            $data[]   = $this->tenantId();
            $placeholders = rtrim(str_repeat('?,', count($fields)), ',');
            $cols = implode(',', $fields);

            $db   = Database::getInstance();
            $stmt = $db->prepare("INSERT INTO projects ($cols, created_at) VALUES ($placeholders, NOW())");
            $stmt->execute($data);
            $newId = $db->lastInsertId();

            $this->setFlash('success', 'Project created successfully');
            $this->redirect('/admin/projects/view/' . $newId);
        }
    }

    public function edit($id)
    {
        $db = Database::getInstance();
        $project = $db->fetch(
            "SELECT p.*, d.name as district_name, s.name as state_name, c.name as colony_name
             FROM projects p
             LEFT JOIN colonies c ON p.colony_id = c.id
             LEFT JOIN districts d ON p.district_id = d.id
             LEFT JOIN states s ON p.state_id = s.id
             WHERE p.id = ?",
            [$id]
        );
        $this->data['project']    = $project ?: [];
        $this->data['states']     = $db->fetchAll("SELECT * FROM states ORDER BY name");
        $this->data['districts']  = $db->fetchAll("SELECT * FROM districts ORDER BY name");
        $this->data['colonies']   = $db->fetchAll("SELECT * FROM colonies ORDER BY name");
        $this->data['page_title'] = 'Edit Project: ' . ($project['name'] ?? '');
        return $this->render('admin/projects/edit');
    }

    public function show($id)
    {
        $db = Database::getInstance();
        $project = $db->fetch("
            SELECT p.*, c.name as colony_name, c.slug as colony_slug,
                   d.name as district_name, s.name as state_name,
                   (SELECT COUNT(*) FROM plots WHERE colony_id = p.colony_id) as total_units,
                   (SELECT COUNT(*) FROM plots WHERE colony_id = p.colony_id AND status = 'available') as available_units,
                   (SELECT COUNT(*) FROM plots WHERE colony_id = p.colony_id AND status = 'sold') as sold_units,
                   (SELECT COUNT(*) FROM plots WHERE colony_id = p.colony_id AND status = 'booked') as booked_units
            FROM projects p
            LEFT JOIN colonies c ON p.colony_id = c.id
            LEFT JOIN districts d ON p.district_id = d.id
            LEFT JOIN states s ON p.state_id = s.id
            WHERE p.id = ?
        ", [$id]);

        if (!$project) {
            $this->setFlash('error', 'Project not found');
            $this->redirect('/admin/projects');
            return;
        }

        $amenities  = json_decode($project['amenities'] ?? '[]', true) ?: [];
        $images     = json_decode($project['images'] ?? '[]', true) ?: [];
        $milestones = json_decode($project['milestone_json'] ?? '[]', true) ?: [];

        $this->data['project']    = $project;
        $this->data['amenities']  = $amenities;
        $this->data['images']     = $images;
        $this->data['milestones'] = $milestones;
        $this->data['page_title'] = 'Project: ' . ($project['name'] ?? '');
        return $this->render('admin/projects/show');
    }

    public function images($id)
    {
        $db = Database::getInstance();
        $project = $db->fetch("SELECT * FROM projects WHERE id = ?", [$id]);
        $images  = [];
        try {
            $images = $db->fetchAll("SELECT * FROM project_images WHERE project_id = ? ORDER BY display_order", [$id]);
        } catch (\Throwable $e) {
            error_log($e->getMessage());
        }
        $this->data['project']    = $project ?: [];
        $this->data['images']     = $images;
        $this->data['page_title'] = 'Project Images';
        return $this->render('admin/projects/images');
    }

    public function status($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfOrFail();
            $db     = Database::getInstance();
            $status = $_POST['status'] ?? 'planning';
            $stmt   = $db->prepare("UPDATE projects SET status = ?, updated_at = NOW() WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$status, $id, $this->tenantId()]);
            $this->setFlash('success', 'Project status updated');
        }
        $this->redirect('/admin/projects');
    }

    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfOrFail();
            $db = Database::getInstance();

            $fields = [
                'name', 'project_type', 'description', 'developer_name', 'developer_contact', 'developer_phone',
                'address', 'state_id', 'district_id', 'colony_id', 'total_area', 'total_plots', 'available_plots',
                'booked_plots', 'sold_plots', 'price_range_min', 'price_range_max', 'avg_price_per_sqft',
                'status', 'launch_date', 'completion_date', 'possession_date', 'marketing_description', 'tags',
                'is_featured', 'is_hot_deal', 'rera_number', 'progress_pct', 'project_budget', 'amount_spent',
                'project_manager', 'site_supervisor', 'contractor_name', 'risk_flags',
            ];

            $updates = [];
            $data    = [];
            foreach ($fields as $field) {
                $value = $_POST[$field] ?? null;
                if ($field === 'is_featured' || $field === 'is_hot_deal') {
                    $value = !empty($value) ? 1 : 0;
                } elseif ($value === '' && in_array($field, ['state_id', 'district_id', 'colony_id', 'launch_date', 'completion_date', 'possession_date'])) {
                    $value = null;
                }
                $updates[] = "`$field` = ?";
                $data[]    = $value;
            }

            if (!empty($updates)) {
                $updates[] = '`updated_at` = NOW()';
                $sql       = "UPDATE projects SET " . implode(', ', $updates) . " WHERE id = ? AND tenant_id = ?";
                $data[]    = $id;
                $data[]    = $this->tenantId();
                $stmt      = $db->prepare($sql);
                $stmt->execute($data);
                $this->setFlash('success', 'Project updated successfully');
            }
        }
        $this->redirect('/admin/projects/view/' . $id);
    }

    public function detail($id) { return $this->show($id); }
    public function delete($id) { $this->destroy($id); }

    public function destroy($id)
    {
        $db   = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM projects WHERE id = ? AND tenant_id = ?");
        $stmt->execute([$id, $this->tenantId()]);
        $this->redirect('/admin/projects');
    }
}