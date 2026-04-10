<?php

namespace App\Http\Controllers\Admin;

class LocationAdminController
{
    private $db;

    public function __construct()
    {
        $this->db = new \PDO('mysql:host=localhost;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
        $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    // Check if user is logged in and has admin access
    private function checkAuth()
    {
        session_start();
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
            header('Location: /admin/login');
            exit();
        }

        if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'employee') {
            header('Location: /admin/login');
            exit();
        }
    }

    // States Management
    public function index()
    {
        $this->checkAuth();

        // Get all states with district count
        $sql = "SELECT s.*, COUNT(d.id) as district_count 
                FROM states s 
                LEFT JOIN districts d ON s.id = d.state_id 
                WHERE s.is_active = 1 
                GROUP BY s.id 
                ORDER BY s.name";
        $stmt = $this->db->query($sql);
        $states = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        include __DIR__ . '/../../views/admin/locations/states/index.php';
    }

    public function createState()
    {
        $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name']);
            $code = strtoupper(trim($_POST['code']));

            if (empty($name) || empty($code)) {
                $_SESSION['error'] = 'All fields are required';
                header('Location: /admin/locations/states/create');
                exit();
            }

            try {
                $stmt = $this->db->prepare("INSERT INTO states (name, code) VALUES (?, ?)");
                $stmt->execute([$name, $code]);

                $_SESSION['success'] = 'State created successfully';
                header('Location: /admin/locations/states');
                exit();
            } catch (\PDOException $e) {
                $_SESSION['error'] = 'State already exists or error occurred';
                header('Location: /admin/locations/states/create');
                exit();
            }
        }

        include __DIR__ . '/../../views/admin/locations/states/create.php';
    }

    public function editState($id)
    {
        $this->checkAuth();

        $stmt = $this->db->prepare("SELECT * FROM states WHERE id = ?");
        $stmt->execute([$id]);
        $state = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$state) {
            $_SESSION['error'] = 'State not found';
            header('Location: /admin/locations/states');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name']);
            $code = strtoupper(trim($_POST['code']));
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if (empty($name) || empty($code)) {
                $_SESSION['error'] = 'All fields are required';
                header("Location: /admin/locations/states/edit/$id");
                exit();
            }

            try {
                $stmt = $this->db->prepare("UPDATE states SET name = ?, code = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$name, $code, $is_active, $id]);

                $_SESSION['success'] = 'State updated successfully';
                header('Location: /admin/locations/states');
                exit();
            } catch (\PDOException $e) {
                $_SESSION['error'] = 'State already exists or error occurred';
                header("Location: /admin/locations/states/edit/$id");
                exit();
            }
        }

        include __DIR__ . '/../../views/admin/locations/states/edit.php';
    }

    public function deleteState($id)
    {
        $this->checkAuth();

        try {
            $stmt = $this->db->prepare("DELETE FROM states WHERE id = ?");
            $stmt->execute([$id]);

            $_SESSION['success'] = 'State deleted successfully';
        } catch (\PDOException $e) {
            $_SESSION['error'] = 'Cannot delete state - it has associated districts';
        }

        header('Location: /admin/locations/states');
        exit();
    }

    // Districts Management
    public function districts()
    {
        $this->checkAuth();

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

        include __DIR__ . '/../../views/admin/locations/districts/index.php';
    }

    public function createDistrict()
    {
        $this->checkAuth();

        $states = \App\Models\State::getActive(['id', 'name', 'code']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $state_id = $_POST['state_id'];
            $name = trim($_POST['name']);
            $code = strtoupper(trim($_POST['code']));

            if (empty($state_id) || empty($name) || empty($code)) {
                $_SESSION['error'] = 'All fields are required';
                header('Location: /admin/locations/districts/create');
                exit();
            }

            try {
                $stmt = $this->db->prepare("INSERT INTO districts (state_id, name, code) VALUES (?, ?, ?)");
                $stmt->execute([$state_id, $name, $code]);

                $_SESSION['success'] = 'District created successfully';
                header('Location: /admin/locations/districts');
                exit();
            } catch (\PDOException $e) {
                $_SESSION['error'] = 'District already exists or error occurred';
                header('Location: /admin/locations/districts/create');
                exit();
            }
        }

        include __DIR__ . '/../../views/admin/locations/districts/create.php';
    }

    public function editDistrict($id)
    {
        $this->checkAuth();

        $stmt = $this->db->prepare("SELECT d.*, s.name as state_name FROM districts d LEFT JOIN states s ON d.state_id = s.id WHERE d.id = ?");
        $stmt->execute([$id]);
        $district = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$district) {
            $_SESSION['error'] = 'District not found';
            header('Location: /admin/locations/districts');
            exit();
        }

        $states = \App\Models\State::getActive(['id', 'name', 'code']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $state_id = $_POST['state_id'];
            $name = trim($_POST['name']);
            $code = strtoupper(trim($_POST['code']));
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if (empty($state_id) || empty($name) || empty($code)) {
                $_SESSION['error'] = 'All fields are required';
                header("Location: /admin/locations/districts/edit/$id");
                exit();
            }

            try {
                $stmt = $this->db->prepare("UPDATE districts SET state_id = ?, name = ?, code = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$state_id, $name, $code, $is_active, $id]);

                $_SESSION['success'] = 'District updated successfully';
                header('Location: /admin/locations/districts');
                exit();
            } catch (\PDOException $e) {
                $_SESSION['error'] = 'District already exists or error occurred';
                header("Location: /admin/locations/districts/edit/$id");
                exit();
            }
        }

        include __DIR__ . '/../../views/admin/locations/districts/edit.php';
    }

    public function deleteDistrict($id)
    {
        $this->checkAuth();

        try {
            $stmt = $this->db->prepare("DELETE FROM districts WHERE id = ?");
            $stmt->execute([$id]);

            $_SESSION['success'] = 'District deleted successfully';
        } catch (\PDOException $e) {
            $_SESSION['error'] = 'Cannot delete district - it has associated colonies';
        }

        header('Location: /admin/locations/districts');
        exit();
    }

    // Colonies Management
    public function colonies()
    {
        $this->checkAuth();

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

        include __DIR__ . '/../../views/admin/locations/colonies/index.php';
    }

    public function createColony()
    {
        $this->checkAuth();

        $states = \App\Models\State::getActive(['id', 'name', 'code']);
        $districts = \App\Models\District::getWithStateName(['id', 'name', 'state_id'], true);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

            if (empty($district_id) || empty($name)) {
                $_SESSION['error'] = 'District and Colony Name are required';
                header('Location: /admin/locations/colonies/create');
                exit();
            }

            try {
                $stmt = $this->db->prepare("INSERT INTO colonies (district_id, name, description, amenities, map_link, total_plots, available_plots, starting_price, image_path, brochure_path, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$district_id, $name, $description, $amenities, $map_link, $total_plots, $available_plots, $starting_price, $image_path, $brochure_path, $is_featured]);

                $_SESSION['success'] = 'Colony created successfully';
                header('Location: /admin/locations/colonies');
                exit();
            } catch (\PDOException $e) {
                $_SESSION['error'] = 'Colony already exists or error occurred';
                header('Location: /admin/locations/colonies/create');
                exit();
            }
        }

        include __DIR__ . '/../../views/admin/locations/colonies/create.php';
    }

    public function editColony($id)
    {
        $this->checkAuth();

        $stmt = $this->db->prepare("SELECT c.*, d.name as district_name, s.name as state_name FROM colonies c LEFT JOIN districts d ON c.district_id = d.id LEFT JOIN states s ON d.state_id = s.id WHERE c.id = ?");
        $stmt->execute([$id]);
        $colony = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$colony) {
            $_SESSION['error'] = 'Colony not found';
            header('Location: /admin/locations/colonies');
            exit();
        }

        $states = \App\Models\State::getActive(['id', 'name', 'code']);
        $districts = \App\Models\District::getWithStateName(['id', 'name', 'state_id'], true);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

            if (empty($district_id) || empty($name)) {
                $_SESSION['error'] = 'District and Colony Name are required';
                header("Location: /admin/locations/colonies/edit/$id");
                exit();
            }

            try {
                $stmt = $this->db->prepare("UPDATE colonies SET district_id = ?, name = ?, description = ?, amenities = ?, map_link = ?, total_plots = ?, available_plots = ?, starting_price = ?, image_path = ?, brochure_path = ?, is_featured = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$district_id, $name, $description, $amenities, $map_link, $total_plots, $available_plots, $starting_price, $image_path, $brochure_path, $is_featured, $is_active, $id]);

                $_SESSION['success'] = 'Colony updated successfully';
                header('Location: /admin/locations/colonies');
                exit();
            } catch (\PDOException $e) {
                $_SESSION['error'] = 'Colony already exists or error occurred';
                header("Location: /admin/locations/colonies/edit/$id");
                exit();
            }
        }

        include __DIR__ . '/../../views/admin/locations/colonies/edit.php';
    }

    public function deleteColony($id)
    {
        $this->checkAuth();

        try {
            $stmt = $this->db->prepare("DELETE FROM colonies WHERE id = ?");
            $stmt->execute([$id]);

            $_SESSION['success'] = 'Colony deleted successfully';
        } catch (\PDOException $e) {
            $_SESSION['error'] = 'Cannot delete colony - it may have associated data';
        }

        header('Location: /admin/locations/colonies');
        exit();
    }

    // API endpoints for AJAX calls
    public function getDistrictsByState($state_id)
    {
        $this->checkAuth();

        header('Content-Type: application/json');

        $districts = \App\Models\District::getByState($state_id, ['*'], true);

        echo json_encode($districts);
        exit();
    }

    public function getColoniesByDistrict($district_id)
    {
        $this->checkAuth();

        header('Content-Type: application/json');

        $stmt = $this->db->prepare("SELECT * FROM colonies WHERE district_id = ? AND is_active = 1 ORDER BY name");
        $stmt->execute([$district_id]);
        $colonies = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        echo json_encode($colonies);
        exit();
    }
}
