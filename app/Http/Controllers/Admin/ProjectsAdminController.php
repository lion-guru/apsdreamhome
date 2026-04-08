<?php
namespace App\Http\Controllers\Admin;

use App\Core\Database\Database;

class ProjectsAdminController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index()
    {
        $projects = $this->db->query("
            SELECT p.*, c.name as colony_name, d.name as district_name, s.name as state_name
            FROM projects p
            LEFT JOIN colonies c ON p.colony_id = c.id
            LEFT JOIN districts d ON p.district_id = d.id
            LEFT JOIN states s ON p.state_id = s.id
            ORDER BY p.created_at DESC
        ")->fetchAll();

        $stats = $this->db->query("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'under_construction' THEN 1 ELSE 0 END) as under_construction,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'planning' THEN 1 ELSE 0 END) as planning,
                SUM(total_plots) as total_plots,
                SUM(available_plots) as available_plots,
                SUM(sold_plots) as sold_plots
            FROM projects
        ")->fetch();

        include __DIR__ . "/../../../views/admin/projects/index.php";
    }

    public function create()
    {
        $states = $this->db->query("SELECT * FROM states WHERE is_active = 1 ORDER BY name")->fetchAll();
        $districts = $this->db->query("SELECT * FROM districts WHERE is_active = 1 ORDER BY name")->fetchAll();
        $colonies = $this->db->query("SELECT * FROM colonies WHERE is_active = 1 ORDER BY name")->fetchAll();

        include __DIR__ . "/../../../views/admin/projects/create.php";
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $project_type = $_POST['project_type'] ?? 'residential';
            $developer_name = $_POST['developer_name'] ?? '';
            $developer_contact = $_POST['developer_contact'] ?? '';
            $developer_email = $_POST['developer_email'] ?? '';
            $developer_phone = $_POST['developer_phone'] ?? '';
            $address = $_POST['address'] ?? '';
            $colony_id = !empty($_POST['colony_id']) ? (int)$_POST['colony_id'] : null;
            $district_id = !empty($_POST['district_id']) ? (int)$_POST['district_id'] : null;
            $state_id = !empty($_POST['state_id']) ? (int)$_POST['state_id'] : null;
            $latitude = !empty($_POST['latitude']) ? (float)$_POST['latitude'] : null;
            $longitude = !empty($_POST['longitude']) ? (float)$_POST['longitude'] : null;
            $total_area = !empty($_POST['total_area']) ? (float)$_POST['total_area'] : 0;
            $total_plots = !empty($_POST['total_plots']) ? (int)$_POST['total_plots'] : 0;
            $available_plots = !empty($_POST['available_plots']) ? (int)$_POST['available_plots'] : 0;
            $sold_plots = !empty($_POST['sold_plots']) ? (int)$_POST['sold_plots'] : 0;
            $booked_plots = !empty($_POST['booked_plots']) ? (int)$_POST['booked_plots'] : 0;
            $price_range_min = !empty($_POST['price_range_min']) ? (float)$_POST['price_range_min'] : 0;
            $price_range_max = !empty($_POST['price_range_max']) ? (float)$_POST['price_range_max'] : 0;
            $avg_price_per_sqft = !empty($_POST['avg_price_per_sqft']) ? (float)$_POST['avg_price_per_sqft'] : 0;
            $launch_date = !empty($_POST['launch_date']) ? $_POST['launch_date'] : null;
            $completion_date = !empty($_POST['completion_date']) ? $_POST['completion_date'] : null;
            $possession_date = !empty($_POST['possession_date']) ? $_POST['possession_date'] : null;
            $status = $_POST['status'] ?? 'planning';
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $is_hot_deal = isset($_POST['is_hot_deal']) ? 1 : 0;
            $marketing_description = $_POST['marketing_description'] ?? '';
            $tags = $_POST['tags'] ?? '';
            $sales_office_address = $_POST['sales_office_address'] ?? '';
            $sales_office_phone = $_POST['sales_office_phone'] ?? '';
            $sales_office_email = $_POST['sales_office_email'] ?? '';

            $amenities = isset($_POST['amenities']) ? json_encode($_POST['amenities']) : null;
            $specifications = isset($_POST['specifications']) ? json_encode($_POST['specifications']) : null;
            $features = isset($_POST['features']) ? json_encode($_POST['features']) : null;

            $stmt = $this->db->prepare("INSERT INTO projects (
                name, description, project_type, developer_name, developer_contact, developer_email, developer_phone,
                address, colony_id, district_id, state_id, latitude, longitude,
                total_area, total_plots, available_plots, sold_plots, booked_plots,
                price_range_min, price_range_max, avg_price_per_sqft,
                launch_date, completion_date, possession_date, status,
                amenities, specifications, features,
                is_featured, is_hot_deal, marketing_description, tags,
                sales_office_address, sales_office_phone, sales_office_email,
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

            $stmt->execute([
                $name, $description, $project_type, $developer_name, $developer_contact, $developer_email, $developer_phone,
                $address, $colony_id, $district_id, $state_id, $latitude, $longitude,
                $total_area, $total_plots, $available_plots, $sold_plots, $booked_plots,
                $price_range_min, $price_range_max, $avg_price_per_sqft,
                $launch_date, $completion_date, $possession_date, $status,
                $amenities, $specifications, $features,
                $is_featured, $is_hot_deal, $marketing_description, $tags,
                $sales_office_address, $sales_office_phone, $sales_office_email
            ]);

            header('Location: /admin/projects');
            exit;
        }
    }

    public function edit($id)
    {
        $project = $this->db->prepare("SELECT * FROM projects WHERE id = ?");
        $project->execute([$id]);
        $project = $project->fetch();

        if (!$project) {
            header('Location: /admin/projects');
            exit;
        }

        $states = $this->db->query("SELECT * FROM states WHERE is_active = 1 ORDER BY name")->fetchAll();
        $districts = $this->db->query("SELECT * FROM districts WHERE is_active = 1 ORDER BY name")->fetchAll();
        $colonies = $this->db->query("SELECT * FROM colonies WHERE is_active = 1 ORDER BY name")->fetchAll();

        include __DIR__ . "/../../../views/admin/projects/edit.php";
    }

    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $project_type = $_POST['project_type'] ?? 'residential';
            $developer_name = $_POST['developer_name'] ?? '';
            $developer_contact = $_POST['developer_contact'] ?? '';
            $developer_email = $_POST['developer_email'] ?? '';
            $developer_phone = $_POST['developer_phone'] ?? '';
            $address = $_POST['address'] ?? '';
            $colony_id = !empty($_POST['colony_id']) ? (int)$_POST['colony_id'] : null;
            $district_id = !empty($_POST['district_id']) ? (int)$_POST['district_id'] : null;
            $state_id = !empty($_POST['state_id']) ? (int)$_POST['state_id'] : null;
            $latitude = !empty($_POST['latitude']) ? (float)$_POST['latitude'] : null;
            $longitude = !empty($_POST['longitude']) ? (float)$_POST['longitude'] : null;
            $total_area = !empty($_POST['total_area']) ? (float)$_POST['total_area'] : 0;
            $total_plots = !empty($_POST['total_plots']) ? (int)$_POST['total_plots'] : 0;
            $available_plots = !empty($_POST['available_plots']) ? (int)$_POST['available_plots'] : 0;
            $sold_plots = !empty($_POST['sold_plots']) ? (int)$_POST['sold_plots'] : 0;
            $booked_plots = !empty($_POST['booked_plots']) ? (int)$_POST['booked_plots'] : 0;
            $price_range_min = !empty($_POST['price_range_min']) ? (float)$_POST['price_range_min'] : 0;
            $price_range_max = !empty($_POST['price_range_max']) ? (float)$_POST['price_range_max'] : 0;
            $avg_price_per_sqft = !empty($_POST['avg_price_per_sqft']) ? (float)$_POST['avg_price_per_sqft'] : 0;
            $launch_date = !empty($_POST['launch_date']) ? $_POST['launch_date'] : null;
            $completion_date = !empty($_POST['completion_date']) ? $_POST['completion_date'] : null;
            $possession_date = !empty($_POST['possession_date']) ? $_POST['possession_date'] : null;
            $status = $_POST['status'] ?? 'planning';
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $is_hot_deal = isset($_POST['is_hot_deal']) ? 1 : 0;
            $marketing_description = $_POST['marketing_description'] ?? '';
            $tags = $_POST['tags'] ?? '';
            $sales_office_address = $_POST['sales_office_address'] ?? '';
            $sales_office_phone = $_POST['sales_office_phone'] ?? '';
            $sales_office_email = $_POST['sales_office_email'] ?? '';

            // Get old status for history
            $oldProject = $this->db->prepare("SELECT status FROM projects WHERE id = ?");
            $oldProject->execute([$id]);
            $oldStatus = $oldProject->fetch()['status'];

            $amenities = isset($_POST['amenities']) ? json_encode($_POST['amenities']) : null;
            $specifications = isset($_POST['specifications']) ? json_encode($_POST['specifications']) : null;
            $features = isset($_POST['features']) ? json_encode($_POST['features']) : null;

            $stmt = $this->db->prepare("UPDATE projects SET
                name = ?, description = ?, project_type = ?, developer_name = ?, developer_contact = ?,
                developer_email = ?, developer_phone = ?, address = ?, colony_id = ?, district_id = ?,
                state_id = ?, latitude = ?, longitude = ?, total_area = ?, total_plots = ?,
                available_plots = ?, sold_plots = ?, booked_plots = ?, price_range_min = ?,
                price_range_max = ?, avg_price_per_sqft = ?, launch_date = ?, completion_date = ?,
                possession_date = ?, status = ?, amenities = ?, specifications = ?, features = ?,
                is_featured = ?, is_hot_deal = ?, marketing_description = ?, tags = ?,
                sales_office_address = ?, sales_office_phone = ?, sales_office_email = ?,
                updated_at = NOW()
            WHERE id = ?");

            $stmt->execute([
                $name, $description, $project_type, $developer_name, $developer_contact,
                $developer_email, $developer_phone, $address, $colony_id, $district_id,
                $state_id, $latitude, $longitude, $total_area, $total_plots,
                $available_plots, $sold_plots, $booked_plots, $price_range_min,
                $price_range_max, $avg_price_per_sqft, $launch_date, $completion_date,
                $possession_date, $status, $amenities, $specifications, $features,
                $is_featured, $is_hot_deal, $marketing_description, $tags,
                $sales_office_address, $sales_office_phone, $sales_office_email,
                $id
            ]);

            // Log status change
            if ($oldStatus !== $status) {
                $historyStmt = $this->db->prepare("INSERT INTO project_status_history (project_id, old_status, new_status, changed_at) VALUES (?, ?, ?, NOW())");
                $historyStmt->execute([$id, $oldStatus, $status]);
            }

            header('Location: /admin/projects');
            exit;
        }
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$id]);

        header('Location: /admin/projects');
        exit;
    }

    public function view($id)
    {
        $project = $this->db->prepare("
            SELECT p.*, c.name as colony_name, d.name as district_name, s.name as state_name
            FROM projects p
            LEFT JOIN colonies c ON p.colony_id = c.id
            LEFT JOIN districts d ON p.district_id = d.id
            LEFT JOIN states s ON p.state_id = s.id
            WHERE p.id = ?
        ");
        $project->execute([$id]);
        $project = $project->fetch();

        if (!$project) {
            header('Location: /admin/projects');
            exit;
        }

        include __DIR__ . "/../../../views/admin/projects/view.php";
    }

    public function images($id)
    {
        $project = $this->db->prepare("SELECT * FROM projects WHERE id = ?");
        $project->execute([$id]);
        $project = $project->fetch();

        if (!$project) {
            header('Location: /admin/projects');
            exit;
        }

        $images = $this->db->prepare("SELECT * FROM project_images WHERE project_id = ? ORDER BY display_order");
        $images->execute([$id]);
        $images = $images->fetchAll();

        include __DIR__ . "/../../../views/admin/projects/images.php";
    }

    public function status($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newStatus = $_POST['status'] ?? '';

            // Get old status
            $oldProject = $this->db->prepare("SELECT status FROM projects WHERE id = ?");
            $oldProject->execute([$id]);
            $oldStatus = $oldProject->fetch()['status'];

            $stmt = $this->db->prepare("UPDATE projects SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$newStatus, $id]);

            // Log status change
            $historyStmt = $this->db->prepare("INSERT INTO project_status_history (project_id, old_status, new_status, changed_at) VALUES (?, ?, ?, NOW())");
            $historyStmt->execute([$id, $oldStatus, $newStatus]);

            header('Location: /admin/projects');
            exit;
        }
    }
}