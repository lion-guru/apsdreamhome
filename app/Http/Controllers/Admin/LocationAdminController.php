<?php

namespace App\Http\Controllers\Admin;

class LocationAdminController extends AdminController
{
    use \App\Traits\TenantAwareTrait;

    private const PER_PAGE = 25;

    // States Management
    public function index()
    {
        $this->requireAdmin();

        $search = trim($_GET['search'] ?? '');
        $status = $_GET['status'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * self::PER_PAGE;

        // Build WHERE clause
        $where = [];
        $params = [];

        if ($search) {
            $where[] = "(s.name LIKE ? OR s.code LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if ($status !== '') {
            $where[] = "s.is_active = ?";
            $params[] = (int)$status;
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        // Get total count for pagination
        $countSql = "SELECT COUNT(*) FROM states s $whereClause";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        // Get states with pagination
        $sql = "SELECT s.*, COUNT(d.id) as district_count 
                FROM states s 
                LEFT JOIN districts d ON s.id = d.state_id 
                $whereClause
                GROUP BY s.id 
                ORDER BY s.name
                LIMIT " . self::PER_PAGE . " OFFSET $offset";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $states = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $totalPages = ceil($total / self::PER_PAGE);

        $this->render('admin/locations/states/index', [
            'states' => $states,
            'search' => $search,
            'status' => $status,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
        ]);
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
                $stmt = $this->db->prepare("INSERT INTO states (name, code) VALUES (?, ?)");
                $stmt->execute([$name, $code]);

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
                $stmt = $this->db->prepare("UPDATE states SET name = ?, code = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$name, $code, $is_active, $id]);

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
            $stmt = $this->db->prepare("DELETE FROM states WHERE id = ?");
            $stmt->execute([$id]);

            $_SESSION['success'] = 'State deleted successfully';
        } catch (\PDOException $e) {
            $_SESSION['error'] = 'Cannot delete state - it has associated districts';
        }

        redirect('/admin/locations/states');
        return;
    }

    // CSV Export - States
    public function exportStates()
    {
        $this->requireAdmin();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="states_' . date('Y-m-d') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        // BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Headers
        fputcsv($output, ['ID', 'Name', 'Code', 'Is Active', 'Created At']);

        // Fetch all states
        $sql = "SELECT id, name, code, is_active, created_at FROM states ORDER BY name";
        $stmt = $this->db->query($sql);
        $states = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($states as $state) {
            fputcsv($output, [
                $state['id'],
                $state['name'],
                $state['code'],
                $state['is_active'] ? 'Active' : 'Inactive',
                $state['created_at']
            ]);
        }

        fclose($output);
        exit;
    }

    // CSV Import - States
    public function importStates()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Please upload a valid CSV file';
            redirect('/admin/locations/states');
            return;
        }

        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');
        
        if (!$handle) {
            $_SESSION['error'] = 'Could not read CSV file';
            redirect('/admin/locations/states');
            return;
        }

        // Skip header row
        $header = fgetcsv($handle);
        $imported = 0;
        $skipped = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 2) {
                $skipped++;
                continue;
            }

            $name = trim($row[0]);
            $code = strtoupper(trim($row[1]));
            $is_active = isset($row[2]) ? (strtolower($row[2]) === 'active' ? 1 : 0) : 1;

            if (empty($name) || empty($code)) {
                $skipped++;
                continue;
            }

            // Check for duplicate
            $checkStmt = $this->db->prepare("SELECT id FROM states WHERE code = ? OR name = ?");
            $checkStmt->execute([$code, $name]);
            if ($checkStmt->fetch()) {
                $errors[] = "Duplicate: $name ($code)";
                $skipped++;
                continue;
            }

            try {
                $stmt = $this->db->prepare("INSERT INTO states (name, code, is_active) VALUES (?, ?, ?)");
                $stmt->execute([$name, $code, $is_active]);
                $imported++;
            } catch (\PDOException $e) {
                $errors[] = "Error inserting $name ($code): " . $e->getMessage();
                $skipped++;
            }
        }

        fclose($handle);

        $message = "Imported: $imported, Skipped: $skipped";
        if ($errors) {
            $message .= ". Errors: " . implode('; ', array_slice($errors, 0, 5));
        }
        
        if ($imported > 0) {
            $_SESSION['success'] = $message;
        } else {
            $_SESSION['error'] = $message;
        }

        redirect('/admin/locations/states');
    }

    // Bulk Actions - States
    public function bulkActionStates()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $action = $_POST['action'] ?? '';
        $ids = $_POST['ids'] ?? [];

        if (empty($action) || empty($ids)) {
            $_SESSION['error'] = 'No action or items selected';
            redirect('/admin/locations/states');
            return;
        }

        $ids = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        try {
            switch ($action) {
                case 'activate':
                    $stmt = $this->db->prepare("UPDATE states SET is_active = 1 WHERE id IN ($placeholders)");
                    $stmt->execute($ids);
                    $_SESSION['success'] = count($ids) . ' state(s) activated';
                    break;
                case 'deactivate':
                    $stmt = $this->db->prepare("UPDATE states SET is_active = 0 WHERE id IN ($placeholders)");
                    $stmt->execute($ids);
                    $_SESSION['success'] = count($ids) . ' state(s) deactivated';
                    break;
                case 'delete':
                    $stmt = $this->db->prepare("DELETE FROM states WHERE id IN ($placeholders)");
                    $stmt->execute($ids);
                    $_SESSION['success'] = count($ids) . ' state(s) deleted';
                    break;
                default:
                    $_SESSION['error'] = 'Invalid action';
            }
        } catch (\PDOException $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        redirect('/admin/locations/states');
    }

    // Districts Management
    public function districts()
    {
        $this->requireAdmin();

        $search = trim($_GET['search'] ?? '');
        $status = $_GET['status'] ?? '';
        $state_id = $_GET['state_id'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * self::PER_PAGE;

        // Build WHERE clause
        $where = [];
        $params = [];

        if ($search) {
            $where[] = "(d.name LIKE ? OR d.code LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if ($status !== '') {
            $where[] = "d.is_active = ?";
            $params[] = (int)$status;
        }
        if ($state_id) {
            $where[] = "d.state_id = ?";
            $params[] = (int)$state_id;
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        // Get total count for pagination
        $countSql = "SELECT COUNT(*) FROM districts d 
                     LEFT JOIN states s ON d.state_id = s.id 
                     $whereClause";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        // Get districts with pagination
        $sql = "SELECT d.*, s.name as state_name, COUNT(c.id) as colony_count 
                FROM districts d 
                LEFT JOIN states s ON d.state_id = s.id 
                LEFT JOIN colonies c ON d.id = c.district_id 
                $whereClause
                GROUP BY d.id 
                ORDER BY s.name, d.name
                LIMIT " . self::PER_PAGE . " OFFSET $offset";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $districts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $totalPages = ceil($total / self::PER_PAGE);

        // Get all states for filter
        $statesList = \App\Models\State::getActive(['id', 'name', 'code']);

        $this->render('admin/locations/districts/index', [
            'districts' => $districts,
            'states' => $statesList,
            'search' => $search,
            'status' => $status,
            'state_id' => $state_id,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
        ]);
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
                $stmt = $this->db->prepare("INSERT INTO districts (state_id, name, code) VALUES (?, ?, ?)");
                $stmt->execute([$state_id, $name, $code]);

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
                $stmt = $this->db->prepare("UPDATE districts SET state_id = ?, name = ?, code = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$state_id, $name, $code, $is_active, $id]);

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
            $stmt = $this->db->prepare("DELETE FROM districts WHERE id = ?");
            $stmt->execute([$id]);

            $_SESSION['success'] = 'District deleted successfully';
        } catch (\PDOException $e) {
            $_SESSION['error'] = 'Cannot delete district - it has associated colonies';
        }

        redirect('/admin/locations/districts');
        return;
    }

    // CSV Export - Districts
    public function exportDistricts()
    {
        $this->requireAdmin();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="districts_' . date('Y-m-d') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, ['ID', 'Name', 'Code', 'State', 'State Code', 'Is Active', 'Created At']);

        $sql = "SELECT d.id, d.name, d.code, s.name as state_name, s.code as state_code, d.is_active, d.created_at 
                FROM districts d 
                LEFT JOIN states s ON d.state_id = s.id 
                ORDER BY s.name, d.name";
        $stmt = $this->db->query($sql);
        $districts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($districts as $district) {
            fputcsv($output, [
                $district['id'],
                $district['name'],
                $district['code'],
                $district['state_name'] ?? '',
                $district['state_code'] ?? '',
                $district['is_active'] ? 'Active' : 'Inactive',
                $district['created_at']
            ]);
        }

        fclose($output);
        exit;
    }

    // CSV Import - Districts
    public function importDistricts()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Please upload a valid CSV file';
            redirect('/admin/locations/districts');
            return;
        }

        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');
        
        if (!$handle) {
            $_SESSION['error'] = 'Could not read CSV file';
            redirect('/admin/locations/districts');
            return;
        }

        $header = fgetcsv($handle);
        $imported = 0;
        $skipped = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 3) {
                $skipped++;
                continue;
            }

            $state_name = trim($row[0]);
            $name = trim($row[1]);
            $code = strtoupper(trim($row[2]));
            $is_active = isset($row[3]) ? (strtolower($row[3]) === 'active' ? 1 : 0) : 1;

            if (empty($state_name) || empty($name) || empty($code)) {
                $skipped++;
                continue;
            }

            // Find state by name
            $stateStmt = $this->db->prepare("SELECT id FROM states WHERE name = ?");
            $stateStmt->execute([$state_name]);
            $state = $stateStmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$state) {
                $errors[] = "State not found: $state_name";
                $skipped++;
                continue;
            }

            // Check for duplicate
            $checkStmt = $this->db->prepare("SELECT id FROM districts WHERE code = ? AND state_id = ?");
            $checkStmt->execute([$code, $state['id']]);
            if ($checkStmt->fetch()) {
                $errors[] = "Duplicate district: $name ($code) in $state_name";
                $skipped++;
                continue;
            }

            try {
                $stmt = $this->db->prepare("INSERT INTO districts (state_id, name, code, is_active) VALUES (?, ?, ?, ?)");
                $stmt->execute([$state['id'], $name, $code, $is_active]);
                $imported++;
            } catch (\PDOException $e) {
                $errors[] = "Error inserting $name ($code): " . $e->getMessage();
                $skipped++;
            }
        }

        fclose($handle);

        $message = "Imported: $imported, Skipped: $skipped";
        if ($errors) {
            $message .= ". Errors: " . implode('; ', array_slice($errors, 0, 5));
        }
        
        if ($imported > 0) {
            $_SESSION['success'] = $message;
        } else {
            $_SESSION['error'] = $message;
        }

        redirect('/admin/locations/districts');
    }

    // Bulk Actions - Districts
    public function bulkActionDistricts()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $action = $_POST['action'] ?? '';
        $ids = $_POST['ids'] ?? [];

        if (empty($action) || empty($ids)) {
            $_SESSION['error'] = 'No action or items selected';
            redirect('/admin/locations/districts');
            return;
        }

        $ids = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        try {
            switch ($action) {
                case 'activate':
                    $stmt = $this->db->prepare("UPDATE districts SET is_active = 1 WHERE id IN ($placeholders)");
                    $stmt->execute($ids);
                    $_SESSION['success'] = count($ids) . ' district(s) activated';
                    break;
                case 'deactivate':
                    $stmt = $this->db->prepare("UPDATE districts SET is_active = 0 WHERE id IN ($placeholders)");
                    $stmt->execute($ids);
                    $_SESSION['success'] = count($ids) . ' district(s) deactivated';
                    break;
                case 'delete':
                    $stmt = $this->db->prepare("DELETE FROM districts WHERE id IN ($placeholders)");
                    $stmt->execute($ids);
                    $_SESSION['success'] = count($ids) . ' district(s) deleted';
                    break;
                default:
                    $_SESSION['error'] = 'Invalid action';
            }
        } catch (\PDOException $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        redirect('/admin/locations/districts');
    }

    // API endpoints for AJAX calls
    public function getDistrictsByState($state_id)
    {
        header('Content-Type: application/json');

        $districts = \App\Models\District::getByState($state_id, ['*'], true);

        echo json_encode($districts);
        return;
    }
}
                $whereClause
                ORDER BY s.name, d.name, c.name
                LIMIT " . self::PER_PAGE . " OFFSET $offset";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $colonies = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $totalPages = ceil($total / self::PER_PAGE);

        // Get filters using models
        $states = \App\Models\State::getActive(['id', 'name', 'code']);
        $districts = \App\Models\District::getWithStateName(['id', 'name', 'state_id'], true);

        $this->render('admin/locations/colonies/index', [
            'colonies' => $colonies,
            'districts' => $districts,
            'states' => $states,
            'search' => $search,
            'status' => $status,
            'state_id' => $state_id,
            'district_id' => $district_id,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
        ]);
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

    // CSV Export - Colonies
    public function exportColonies()
    {
        $this->requireAdmin();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="colonies_' . date('Y-m-d') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, ['ID', 'Name', 'District', 'State', 'Total Plots', 'Available Plots', 'Starting Price', 'Featured', 'Status', 'Created At']);

        $sql = "SELECT c.id, c.name, d.name as district_name, s.name as state_name, 
                       c.total_plots, c.available_plots, c.starting_price, c.is_featured, c.is_active, c.created_at
                FROM colonies c
                LEFT JOIN districts d ON c.district_id = d.id
                LEFT JOIN states s ON d.state_id = s.id
                ORDER BY s.name, d.name, c.name";
        $stmt = $this->db->query($sql);
        $colonies = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($colonies as $colony) {
            fputcsv($output, [
                $colony['id'],
                $colony['name'],
                $colony['district_name'] ?? '',
                $colony['state_name'] ?? '',
                $colony['total_plots'],
                $colony['available_plots'],
                '₹' . number_format($colony['starting_price'], 2),
                $colony['is_featured'] ? 'Yes' : 'No',
                $colony['is_active'] ? 'Active' : 'Inactive',
                $colony['created_at']
            ]);
        }

        fclose($output);
        exit;
    }

    // CSV Import - Colonies
    public function importColonies()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Please upload a valid CSV file';
            redirect('/admin/locations/colonies');
            return;
        }

        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');
        
        if (!$handle) {
            $_SESSION['error'] = 'Could not read CSV file';
            redirect('/admin/locations/colonies');
            return;
        }

        $header = fgetcsv($handle);
        $imported = 0;
        $skipped = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 4) {
                $skipped++;
                continue;
            }

            $state_name = trim($row[0]);
            $district_name = trim($row[1]);
            $name = trim($row[2]);
            $total_plots = (int)($row[3] ?? 0);
            $available_plots = (int)($row[4] ?? 0);
            $starting_price = (float)($row[5] ?? 0);
            $is_featured = isset($row[6]) ? (strtolower($row[6]) === 'yes' ? 1 : 0) : 0;
            $is_active = isset($row[7]) ? (strtolower($row[7]) === 'active' ? 1 : 0) : 1;

            if (empty($state_name) || empty($district_name) || empty($name)) {
                $skipped++;
                continue;
            }

            // Find state
            $stateStmt = $this->db->prepare("SELECT id FROM states WHERE name = ?");
            $stateStmt->execute([$state_name]);
            $state = $stateStmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$state) {
                $errors[] = "State not found: $state_name";
                $skipped++;
                continue;
            }

            // Find district
            $districtStmt = $this->db->prepare("SELECT id FROM districts WHERE name = ? AND state_id = ?");
            $districtStmt->execute([$district_name, $state['id']]);
            $district = $districtStmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$district) {
                $errors[] = "District not found: $district_name in $state_name";
                $skipped++;
                continue;
            }

            // Check for duplicate
            $checkStmt = $this->db->prepare("SELECT id FROM colonies WHERE name = ? AND district_id = ?");
            $checkStmt->execute([$name, $district['id']]);
            if ($checkStmt->fetch()) {
                $errors[] = "Duplicate colony: $name in $district_name";
                $skipped++;
                continue;
            }

            try {
                $stmt = $this->db->prepare("INSERT INTO colonies (district_id, name, total_plots, available_plots, starting_price, is_featured, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$district['id'], $name, $total_plots, $available_plots, $starting_price, $is_featured, $is_active]);
                $imported++;
            } catch (\PDOException $e) {
                $errors[] = "Error inserting $name: " . $e->getMessage();
                $skipped++;
            }
        }

        fclose($handle);

        $message = "Imported: $imported, Skipped: $skipped";
        if ($errors) {
            $message .= ". Errors: " . implode('; ', array_slice($errors, 0, 5));
        }
        
        if ($imported > 0) {
            $_SESSION['success'] = $message;
        } else {
            $_SESSION['error'] = $message;
        }

        redirect('/admin/locations/colonies');
    }

    // Bulk Actions - Colonies
    public function bulkActionColonies()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $action = $_POST['action'] ?? '';
        $ids = $_POST['ids'] ?? [];

        if (empty($action) || empty($ids)) {
            $_SESSION['error'] = 'No action or items selected';
            redirect('/admin/locations/colonies');
            return;
        }

        $ids = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        try {
            switch ($action) {
                case 'activate':
                    $stmt = $this->db->prepare("UPDATE colonies SET is_active = 1 WHERE id IN ($placeholders) AND tenant_id = ?");
                    $stmt->execute(array_merge($ids, [$this->tenantId()]));
                    $_SESSION['success'] = count($ids) . ' colony(s) activated';
                    break;
                case 'deactivate':
                    $stmt = $this->db->prepare("UPDATE colonies SET is_active = 0 WHERE id IN ($placeholders) AND tenant_id = ?");
                    $stmt->execute(array_merge($ids, [$this->tenantId()]));
                    $_SESSION['success'] = count($ids) . ' colony(s) deactivated';
                    break;
                case 'delete':
                    $stmt = $this->db->prepare("DELETE FROM colonies WHERE id IN ($placeholders) AND tenant_id = ?");
                    $stmt->execute(array_merge($ids, [$this->tenantId()]));
                    $_SESSION['success'] = count($ids) . ' colony(s) deleted';
                    break;
                default:
                    $_SESSION['error'] = 'Invalid action';
            }
        } catch (\PDOException $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        redirect('/admin/locations/colonies');
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

    // API: Check if colony name exists in district
    public function checkColonyName()
    {
        header('Content-Type: application/json');
        
        $name = trim($_GET['name'] ?? '');
        $district_id = (int)($_GET['district_id'] ?? 0);
        $exclude_id = (int)($_GET['exclude_id'] ?? 0);

        if (empty($name) || !$district_id) {
            echo json_encode(['exists' => false, 'message' => 'Invalid parameters']);
            return;
        }

        $sql = "SELECT id FROM colonies WHERE name = ? AND district_id = ?";
        $params = [$name, $district_id];
        
        if ($exclude_id) {
            $sql .= " AND id != ?";
            $params[] = $exclude_id;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $exists = $stmt->fetch() !== false;

        echo json_encode(['exists' => $exists]);
        return;
    }
}
