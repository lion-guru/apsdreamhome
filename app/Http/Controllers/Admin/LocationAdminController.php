<?php

namespace App\Http\Controllers\Admin;

class LocationAdminController extends AdminController
{
    use \App\Traits\TenantAwareTrait;


    // States Management
    public function index()
    {
        // Get all states with district count
        $sql = "SELECT s.*, COUNT(d.id) as district_count 
                FROM states s 
                LEFT JOIN districts d ON s.id = d.state_id 
                GROUP BY s.id 
                ORDER BY s.name";
        $stmt = $this->db->query($sql);
        $states = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->render('admin/locations/states/index', ['states' => $states]);
    }

    public function createState()
    {
        

        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            $name = trim($_POST['name']);
            $code = strtoupper(trim($_POST['code']));

            if (empty($name) || empty($code)) {
                $_SESSION['error'] = 'All fields are required';
                redirect('/admin/locations/states/create');
                return;
            }

            try {
                $stmt = $this->db->prepare("INSERT INTO states (name, code, tenant_id) VALUES (?, ?, ?)");
                $stmt->execute([$name, $code, $this->tenantId()]);

                $_SESSION['success'] = 'State created successfully';
                redirect('/admin/locations/states');
                return;
            } catch (\PDOException $e) {
                $_SESSION['error'] = 'State already exists or error occurred';
                redirect('/admin/locations/states/create');
                return;
            }
        }

        $this->render('admin/locations/states/create', []);
    }

    public function editState($id)
    {
        

        $stmt = $this->db->prepare("SELECT * FROM states WHERE id = ?");
        $stmt->execute([$id]);
        $state = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$state) {
            $_SESSION['error'] = 'State not found';
            redirect('/admin/locations/states');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            $name = trim($_POST['name']);
            $code = strtoupper(trim($_POST['code']));
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if (empty($name) || empty($code)) {
                $_SESSION['error'] = 'All fields are required';
                redirect("/admin/locations/states/edit/$id");
                return;
            }

            try {
                $stmt = $this->db->prepare("UPDATE states SET name = ?, code = ?, is_active = ? WHERE id = ? AND tenant_id = ?");
                $stmt->execute([$name, $code, $is_active, $id, $this->tenantId()]);

                $_SESSION['success'] = 'State updated successfully';
                redirect('/admin/locations/states');
                return;
            } catch (\PDOException $e) {
                $_SESSION['error'] = 'State already exists or error occurred';
                redirect("/admin/locations/states/edit/$id");
                return;
            }
        }

        $this->render('admin/locations/states/edit', ['state' => $state ?? null]);
    }

    public function deleteState($id)
    {
        

        try {
            $stmt = $this->db->prepare("DELETE FROM states WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $this->tenantId()]);

            $_SESSION['success'] = 'State deleted successfully';
        } catch (\PDOException $e) {
            $_SESSION['error'] = 'Cannot delete state - it has associated districts';
        }

        redirect('/admin/locations/states');
        return;
    }

    // Districts Management
    public function districts()
    {
        

        $state_id = $_GET['state_id'] ?? null;

        if ($state_id) {
            $sql = "SELECT d.*, s.name as state_name, COUNT(c.id) as colony_count 
                    FROM districts d 
                    LEFT JOIN states s ON d.state_id = s.id 
                    LEFT JOIN colonies c ON d.id = c.district_id 
                    WHERE d.state_id = ? AND d.is_active = 1 
                    GROUP BY d.id 
                    ORDER BY d.name";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$state_id]);
        } else {
            $sql = "SELECT d.*, s.name as state_name, COUNT(c.id) as colony_count 
                    FROM districts d 
                    LEFT JOIN states s ON d.state_id = s.id 
                    LEFT JOIN colonies c ON d.id = c.district_id 
                    WHERE d.is_active = 1 
                    GROUP BY d.id 
                    ORDER BY s.name, d.name";
            $stmt = $this->db->query($sql);
        }

        $districts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get all states for filter using models
        $states = \App\Models\State::getActive(['id', 'name', 'code']);

        $this->render('admin/locations/districts/index', ['districts' => $districts, 'states' => $states]);
    }

    public function createDistrict()
    {
        

        $states = \App\Models\State::getActive(['id', 'name', 'code']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            $state_id = $_POST['state_id'];
            $name = trim($_POST['name']);
            $code = strtoupper(trim($_POST['code']));

            if (empty($state_id) || empty($name) || empty($code)) {
                $_SESSION['error'] = 'All fields are required';
                redirect('/admin/locations/districts/create');
                return;
            }

            try {
                $stmt = $this->db->prepare("INSERT INTO districts (state_id, name, code, tenant_id) VALUES (?, ?, ?, ?)");
                $stmt->execute([$state_id, $name, $code, $this->tenantId()]);

                $_SESSION['success'] = 'District created successfully';
                redirect('/admin/locations/districts');
                return;
            } catch (\PDOException $e) {
                $_SESSION['error'] = 'District already exists or error occurred';
                redirect('/admin/locations/districts/create');
                return;
            }
        }

        $this->render('admin/locations/districts/create', ['states' => $states]);
    }

    public function editDistrict($id)
    {
        

        $stmt = $this->db->prepare("SELECT d.*, s.name as state_name FROM districts d LEFT JOIN states s ON d.state_id = s.id WHERE d.id = ?");
        $stmt->execute([$id]);
        $district = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$district) {
            $_SESSION['error'] = 'District not found';
            redirect('/admin/locations/districts');
            return;
        }

        $states = \App\Models\State::getActive(['id', 'name', 'code']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            $state_id = $_POST['state_id'];
            $name = trim($_POST['name']);
            $code = strtoupper(trim($_POST['code']));
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if (empty($state_id) || empty($name) || empty($code)) {
                $_SESSION['error'] = 'All fields are required';
                redirect("/admin/locations/districts/edit/$id");
                return;
            }

            try {
                $stmt = $this->db->prepare("UPDATE districts SET state_id = ?, name = ?, code = ?, is_active = ? WHERE id = ? AND tenant_id = ?");
                $stmt->execute([$state_id, $name, $code, $is_active, $id, $this->tenantId()]);

                $_SESSION['success'] = 'District updated successfully';
                redirect('/admin/locations/districts');
                return;
            } catch (\PDOException $e) {
                $_SESSION['error'] = 'District already exists or error occurred';
                redirect("/admin/locations/districts/edit/$id");
                return;
            }
        }

        $this->render('admin/locations/districts/edit', ['district' => $district ?? null, 'states' => $states]);
    }

    public function deleteDistrict($id)
    {
        

        try {
            $stmt = $this->db->prepare("DELETE FROM districts WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $this->tenantId()]);

            $_SESSION['success'] = 'District deleted successfully';
        } catch (\PDOException $e) {
            $_SESSION['error'] = 'Cannot delete district - it has associated colonies';
        }

        redirect('/admin/locations/districts');
        return;
    }

    // Colonies Management
    public function colonies()
    {
        $this->requireAdmin();

        $district_id = $_GET['district_id'] ?? null;
        $state_id = $_GET['state_id'] ?? null;

        if ($district_id) {
            $sql = "SELECT c.*, d.name as district_name, s.name as state_name 
                    FROM colonies c 
                    LEFT JOIN districts d ON c.district_id = d.id 
                    LEFT JOIN states s ON d.state_id = s.id 
                    WHERE c.district_id = ? AND c.is_active = 1 
                    ORDER BY c.name";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$district_id]);
        } elseif ($state_id) {
            $sql = "SELECT c.*, d.name as district_name, s.name as state_name 
                    FROM colonies c 
                    LEFT JOIN districts d ON c.district_id = d.id 
                    LEFT JOIN states s ON d.state_id = s.id 
                    WHERE d.state_id = ? AND c.is_active = 1 
                    ORDER BY s.name, d.name, c.name";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$state_id]);
        } else {
            $sql = "SELECT c.*, d.name as district_name, s.name as state_name
                    FROM colonies c
                    LEFT JOIN districts d ON c.district_id = d.id
                    LEFT JOIN states s ON d.state_id = s.id
                    WHERE c.is_active = 1
                    ORDER BY s.name, d.name, c.name";
            $stmt = $this->db->query($sql);
        }

        $colonies = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get filters using models
        $states = \App\Models\State::getActive(['id', 'name', 'code']);
        $districts = \App\Models\District::getWithStateName(['id', 'name', 'state_id'], true);

        $this->render('admin/locations/colonies/index', ['colonies' => $colonies, 'districts' => $districts, 'states' => $states]);
    }

    public function createColony()
    {
        $this->requireAdmin();

        $states = \App\Models\State::getActive(['id', 'name', 'code']);
        $districts = \App\Models\District::getWithStateName(['id', 'name', 'state_id'], true);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            $district_id = $_POST['district_id'];
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $amenities = trim($_POST['amenities']);
            $map_link = trim($_POST['map_link']);
            $total_plots = (int)$_POST['total_plots'];
            $available_plots = (int)$_POST['available_plots'];
            $starting_price = (float)$_POST['starting_price'];
            $image_path = trim($_POST['image_path']);
            $brochure_path = trim($_POST['brochure_path']);
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $land_cost = (float)($_POST['land_cost'] ?? 0);
            $min_price_per_sqft = (float)($_POST['min_price_per_sqft'] ?? 0);
            $block_count = (int)($_POST['block_count'] ?? 0);
            $phase = trim($_POST['phase'] ?? '');

            if (empty($district_id) || empty($name)) {
                $_SESSION['error'] = 'District and Colony Name are required';
                redirect('/admin/locations/colonies/create');
                return;
            }

            try {
                $tid = $this->tenantId();
                $stmt = $this->db->prepare("INSERT INTO colonies (district_id, name, description, amenities, map_link, total_plots, available_plots, starting_price, image_path, brochure_path, is_featured, land_cost, min_price_per_sqft, block_count, phase, tenant_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$district_id, $name, $description, $amenities, $map_link, $total_plots, $available_plots, $starting_price, $image_path, $brochure_path, $is_featured, $land_cost, $min_price_per_sqft, $block_count, $phase, $tid]);

                $_SESSION['success'] = 'Colony created successfully';
                redirect('/admin/locations/colonies');
                return;
            } catch (\PDOException $e) {
                $_SESSION['error'] = 'Colony already exists or error occurred';
                redirect('/admin/locations/colonies/create');
                return;
            }
        }

        $this->render('admin/locations/colonies/create', ['districts' => $districts, 'states' => $states]);
    }

    public function editColony($id)
    {
        $this->requireAdmin();

        $stmt = $this->db->prepare("SELECT c.*, d.name as district_name, s.name as state_name FROM colonies c LEFT JOIN districts d ON c.district_id = d.id LEFT JOIN states s ON d.state_id = s.id WHERE c.id = ?");
        $stmt->execute([$id]);
        $colony = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$colony) {
            $_SESSION['error'] = 'Colony not found';
            redirect('/admin/locations/colonies');
            return;
        }

        $states = \App\Models\State::getActive(['id', 'name', 'code']);
        $districts = \App\Models\District::getWithStateName(['id', 'name', 'state_id'], true);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            $district_id = $_POST['district_id'];
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $amenities = trim($_POST['amenities']);
            $map_link = trim($_POST['map_link']);
            $total_plots = (int)$_POST['total_plots'];
            $available_plots = (int)$_POST['available_plots'];
            $starting_price = (float)$_POST['starting_price'];
            $image_path = trim($_POST['image_path']);
            $brochure_path = trim($_POST['brochure_path']);
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $land_cost = (float)($_POST['land_cost'] ?? 0);
            $min_price_per_sqft = (float)($_POST['min_price_per_sqft'] ?? 0);
            $block_count = (int)($_POST['block_count'] ?? 0);
            $phase = trim($_POST['phase'] ?? '');

            if (empty($district_id) || empty($name)) {
                $_SESSION['error'] = 'District and Colony Name are required';
                redirect("/admin/locations/colonies/edit/$id");
                return;
            }

            try {
                $stmt = $this->db->prepare("UPDATE colonies SET district_id = ?, name = ?, description = ?, amenities = ?, map_link = ?, total_plots = ?, available_plots = ?, starting_price = ?, image_path = ?, brochure_path = ?, is_featured = ?, is_active = ?, land_cost = ?, min_price_per_sqft = ?, block_count = ?, phase = ? WHERE id = ? AND tenant_id = ?");
                $stmt->execute([$district_id, $name, $description, $amenities, $map_link, $total_plots, $available_plots, $starting_price, $image_path, $brochure_path, $is_featured, $is_active, $land_cost, $min_price_per_sqft, $block_count, $phase, $id, $this->tenantId()]);

                $_SESSION['success'] = 'Colony updated successfully';
                redirect('/admin/locations/colonies');
                return;
            } catch (\PDOException $e) {
                $_SESSION['error'] = 'Colony already exists or error occurred';
                redirect("/admin/locations/colonies/edit/$id");
                return;
            }
        }

        $this->render('admin/locations/colonies/edit', ['colony' => $colony ?? null, 'districts' => $districts]);
    }

    public function deleteColony($id)
    {
        $this->requireAdmin();

        try {
            $stmt = $this->db->prepare("DELETE FROM colonies WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $this->tenantId()]);

            $_SESSION['success'] = 'Colony deleted successfully';
        } catch (\PDOException $e) {
            $_SESSION['error'] = 'Cannot delete colony - it may have associated data';
        }

        redirect('/admin/locations/colonies');
        return;
    }

    // API endpoints for AJAX calls
    public function getDistrictsByState($state_id)
    {
        

        header('Content-Type: application/json');

        $districts = \App\Models\District::getByState($state_id, ['*'], true);

        echo json_encode($districts);
        return;
    }

    public function getColoniesByDistrict($district_id)
    {
        

        header('Content-Type: application/json');

        $stmt = $this->db->prepare("SELECT * FROM colonies WHERE district_id = ? AND is_active = 1 ORDER BY name");
        $stmt->execute([$district_id]);
        $colonies = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        echo json_encode($colonies);
        return;
    }
}
