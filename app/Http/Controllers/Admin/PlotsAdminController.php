<?php

namespace App\Http\Controllers\Admin;

class PlotsAdminController
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

    // Main plots listing
    public function index()
    {
        $this->checkAuth();

        $colony_id = $_GET['colony_id'] ?? null;
        $status = $_GET['status'] ?? null;
        $plot_type = $_GET['plot_type'] ?? null;

        // Build query with filters
        $sql = "SELECT p.*, c.name as colony_name, d.name as district_name, s.name as state_name,
                u.name as customer_name
                FROM plots p 
                LEFT JOIN colonies c ON p.colony_id = c.id 
                LEFT JOIN districts d ON c.district_id = d.id 
                LEFT JOIN states s ON d.state_id = s.id 
                LEFT JOIN users u ON p.customer_id = u.id 
                WHERE p.is_active = 1";

        $params = [];

        if ($colony_id) {
            $sql .= " AND p.colony_id = ?";
            $params[] = $colony_id;
        }

        if ($status) {
            $sql .= " AND p.status = ?";
            $params[] = $status;
        }

        if ($plot_type) {
            $sql .= " AND p.plot_type = ?";
            $params[] = $plot_type;
        }

        $sql .= " ORDER BY c.name, p.block, p.sector, p.plot_number";

        $stmt = !empty($params) ? $this->db->prepare($sql) : $this->db->query($sql);
        if (!empty($params)) {
            $stmt->execute($params);
        }
        $plots = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get filters data using models
        $colonies = \App\Models\Colony::getWithDistrictAndStateName(['c.*', 'd.name as district_name', 's.name as state_name'], true);

        // Get statistics
        $stats = $this->getPlotsStatistics();

        include __DIR__ . '/../../views/admin/plots/index.php';
    }

    // Create new plot
    public function create()
    {
        $this->checkAuth();

        $colonies = \App\Models\Colony::getWithDistrictAndStateName(['c.*', 'd.name as district_name', 's.name as state_name'], true);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $colony_id = $_POST['colony_id'];
            $plot_number = trim($_POST['plot_number']);
            $block = trim($_POST['block']);
            $sector = trim($_POST['sector']);
            $plot_type = $_POST['plot_type'];
            $area_sqft = (float)$_POST['area_sqft'];
            $area_sqm = (float)$_POST['area_sqm'];
            $frontage_ft = (float)$_POST['frontage_ft'];
            $depth_ft = (float)$_POST['depth_ft'];
            $price_per_sqft = (float)$_POST['price_per_sqft'];
            $total_price = (float)$_POST['total_price'];
            $status = $_POST['status'];
            $description = trim($_POST['description']);
            $features = trim($_POST['features']);
            $facing = $_POST['facing'];
            $corner_plot = isset($_POST['corner_plot']) ? 1 : 0;
            $park_facing = isset($_POST['park_facing']) ? 1 : 0;
            $road_width_ft = (float)$_POST['road_width_ft'];
            $latitude = !empty($_POST['latitude']) ? (float)$_POST['latitude'] : null;
            $longitude = !empty($_POST['longitude']) ? (float)$_POST['longitude'] : null;
            $image_path = trim($_POST['image_path']);
            $documents_path = trim($_POST['documents_path']);
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;

            if (empty($colony_id) || empty($plot_number) || empty($area_sqft) || empty($price_per_sqft)) {
                $_SESSION['error'] = 'Required fields: Colony, Plot Number, Area, and Price per Sqft';
                header('Location: /admin/plots/create');
                exit();
            }

            try {
                $stmt = $this->db->prepare("INSERT INTO plots (colony_id, plot_number, block, sector, plot_type, area_sqft, area_sqm, frontage_ft, depth_ft, price_per_sqft, total_price, status, description, features, facing, corner_plot, park_facing, road_width_ft, latitude, longitude, image_path, documents_path, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                $stmt->execute([$colony_id, $plot_number, $block, $sector, $plot_type, $area_sqft, $area_sqm, $frontage_ft, $depth_ft, $price_per_sqft, $total_price, $status, $description, $features, $facing, $corner_plot, $park_facing, $road_width_ft, $latitude, $longitude, $image_path, $documents_path, $is_featured]);

                $_SESSION['success'] = 'Plot created successfully';
                header('Location: /admin/plots');
                exit();
            } catch (\PDOException $e) {
                $_SESSION['error'] = 'Plot already exists or error occurred: ' . $e->getMessage();
                header('Location: /admin/plots/create');
                exit();
            }
        }

        include __DIR__ . '/../../views/admin/plots/create.php';
    }

    // Edit plot
    public function edit($id)
    {
        $this->checkAuth();

        $stmt = $this->db->prepare("SELECT p.*, c.name as colony_name, d.name as district_name, s.name as state_name FROM plots p LEFT JOIN colonies c ON p.colony_id = c.id LEFT JOIN districts d ON c.district_id = d.id LEFT JOIN states s ON d.state_id = s.id WHERE p.id = ?");
        $stmt->execute([$id]);
        $plot = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$plot) {
            $_SESSION['error'] = 'Plot not found';
            header('Location: /admin/plots');
            exit();
        }

        $colonies = \App\Models\Colony::getWithDistrictAndStateName(['c.*', 'd.name as district_name', 's.name as state_name'], true);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $colony_id = $_POST['colony_id'];
            $plot_number = trim($_POST['plot_number']);
            $block = trim($_POST['block']);
            $sector = trim($_POST['sector']);
            $plot_type = $_POST['plot_type'];
            $area_sqft = (float)$_POST['area_sqft'];
            $area_sqm = (float)$_POST['area_sqm'];
            $frontage_ft = (float)$_POST['frontage_ft'];
            $depth_ft = (float)$_POST['depth_ft'];
            $price_per_sqft = (float)$_POST['price_per_sqft'];
            $total_price = (float)$_POST['total_price'];
            $status = $_POST['status'];
            $booking_amount = (float)$_POST['booking_amount'];
            $total_paid = (float)$_POST['total_paid'];
            $payment_status = $_POST['payment_status'];
            $customer_id = !empty($_POST['customer_id']) ? (int)$_POST['customer_id'] : null;
            $booking_date = !empty($_POST['booking_date']) ? $_POST['booking_date'] : null;
            $sale_date = !empty($_POST['sale_date']) ? $_POST['sale_date'] : null;
            $possession_date = !empty($_POST['possession_date']) ? $_POST['possession_date'] : null;
            $description = trim($_POST['description']);
            $features = trim($_POST['features']);
            $facing = $_POST['facing'];
            $corner_plot = isset($_POST['corner_plot']) ? 1 : 0;
            $park_facing = isset($_POST['park_facing']) ? 1 : 0;
            $road_width_ft = (float)$_POST['road_width_ft'];
            $latitude = !empty($_POST['latitude']) ? (float)$_POST['latitude'] : null;
            $longitude = !empty($_POST['longitude']) ? (float)$_POST['longitude'] : null;
            $image_path = trim($_POST['image_path']);
            $documents_path = trim($_POST['documents_path']);
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if (empty($colony_id) || empty($plot_number) || empty($area_sqft) || empty($price_per_sqft)) {
                $_SESSION['error'] = 'Required fields: Colony, Plot Number, Area, and Price per Sqft';
                header("Location: /admin/plots/edit/$id");
                exit();
            }

            try {
                $oldStatus = $plot['status'];

                $stmt = $this->db->prepare("UPDATE plots SET colony_id = ?, plot_number = ?, block = ?, sector = ?, plot_type = ?, area_sqft = ?, area_sqm = ?, frontage_ft = ?, depth_ft = ?, price_per_sqft = ?, total_price = ?, status = ?, booking_amount = ?, total_paid = ?, payment_status = ?, customer_id = ?, booking_date = ?, sale_date = ?, possession_date = ?, description = ?, features = ?, facing = ?, corner_plot = ?, park_facing = ?, road_width_ft = ?, latitude = ?, longitude = ?, image_path = ?, documents_path = ?, is_featured = ?, is_active = ? WHERE id = ?");

                $stmt->execute([$colony_id, $plot_number, $block, $sector, $plot_type, $area_sqft, $area_sqm, $frontage_ft, $depth_ft, $price_per_sqft, $total_price, $status, $booking_amount, $total_paid, $payment_status, $customer_id, $booking_date, $sale_date, $possession_date, $description, $features, $facing, $corner_plot, $park_facing, $road_width_ft, $latitude, $longitude, $image_path, $documents_path, $is_featured, $is_active, $id]);

                // Log status change if different
                if ($oldStatus !== $status) {
                    $this->logStatusChange($id, $oldStatus, $status, $_SESSION['user_id'], 'Status updated by admin');
                }

                $_SESSION['success'] = 'Plot updated successfully';
                header('Location: /admin/plots');
                exit();
            } catch (\PDOException $e) {
                $_SESSION['error'] = 'Plot already exists or error occurred: ' . $e->getMessage();
                header("Location: /admin/plots/edit/$id");
                exit();
            }
        }

        include __DIR__ . '/../../views/admin/plots/edit.php';
    }

    // Delete plot
    public function delete($id)
    {
        $this->checkAuth();

        try {
            $stmt = $this->db->prepare("DELETE FROM plots WHERE id = ?");
            $stmt->execute([$id]);

            $_SESSION['success'] = 'Plot deleted successfully';
        } catch (\PDOException $e) {
            $_SESSION['error'] = 'Cannot delete plot - it may have associated data';
        }

        header('Location: /admin/plots');
        exit();
    }

    // Update plot status (AJAX)
    public function updateStatus($id)
    {
        $this->checkAuth();

        header('Content-Type: application/json');

        $new_status = $_POST['status'] ?? '';
        $reason = $_POST['reason'] ?? '';

        if (empty($new_status)) {
            echo json_encode(['success' => false, 'message' => 'Status is required']);
            exit();
        }

        try {
            // Get current status
            $stmt = $this->db->prepare("SELECT status FROM plots WHERE id = ?");
            $stmt->execute([$id]);
            $plot = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$plot) {
                echo json_encode(['success' => false, 'message' => 'Plot not found']);
                exit();
            }

            $old_status = $plot['status'];

            // Update plot status
            $stmt = $this->db->prepare("UPDATE plots SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $id]);

            // Log status change
            $this->logStatusChange($id, $old_status, $new_status, $_SESSION['user_id'], $reason);

            echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error updating status: ' . $e->getMessage()]);
        }
        exit();
    }

    // View plot details
    public function show($id)
    {
        $this->checkAuth();

        $stmt = $this->db->prepare("SELECT p.*, c.name as colony_name, d.name as district_name, s.name as state_name, u.name as customer_name, u.phone as customer_phone, u.email as customer_email FROM plots p LEFT JOIN colonies c ON p.colony_id = c.id LEFT JOIN districts d ON c.district_id = d.id LEFT JOIN states s ON d.state_id = s.id LEFT JOIN users u ON p.customer_id = u.id WHERE p.id = ?");
        $stmt->execute([$id]);
        $plot = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$plot) {
            $_SESSION['error'] = 'Plot not found';
            header('Location: /admin/plots');
            exit();
        }

        // Get status history
        $stmt = $this->db->prepare("SELECT h.*, u.name as changed_by_name FROM plot_status_history h LEFT JOIN users u ON h.changed_by = u.id WHERE h.plot_id = ? ORDER BY h.created_at DESC");
        $stmt->execute([$id]);
        $history = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get plot images
        $stmt = $this->db->prepare("SELECT * FROM plot_images WHERE plot_id = ? AND is_active = 1 ORDER BY sort_order, created_at");
        $stmt->execute([$id]);
        $images = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        include __DIR__ . '/../../views/admin/plots/show.php';
    }

    // Bulk operations
    public function bulkStatusUpdate()
    {
        $this->checkAuth();

        header('Content-Type: application/json');

        $plot_ids = $_POST['plot_ids'] ?? [];
        $new_status = $_POST['status'] ?? '';
        $reason = $_POST['reason'] ?? '';

        if (empty($plot_ids) || empty($new_status)) {
            echo json_encode(['success' => false, 'message' => 'Plots and status are required']);
            exit();
        }

        try {
            $updated = 0;
            foreach ($plot_ids as $plot_id) {
                // Get current status
                $stmt = $this->db->prepare("SELECT status FROM plots WHERE id = ?");
                $stmt->execute([$plot_id]);
                $plot = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($plot && $plot['status'] !== $new_status) {
                    // Update status
                    $stmt = $this->db->prepare("UPDATE plots SET status = ? WHERE id = ?");
                    $stmt->execute([$new_status, $plot_id]);

                    // Log change
                    $this->logStatusChange($plot_id, $plot['status'], $new_status, $_SESSION['user_id'], $reason);
                    $updated++;
                }
            }

            echo json_encode(['success' => true, 'message' => "$updated plots updated successfully"]);
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error updating plots: ' . $e->getMessage()]);
        }
        exit();
    }

    // Export plots data
    public function export()
    {
        $this->checkAuth();

        $colony_id = $_GET['colony_id'] ?? null;
        $status = $_GET['status'] ?? null;

        // Build query
        $sql = "SELECT p.plot_number, p.block, p.sector, p.plot_type, p.area_sqft, p.area_sqm, p.price_per_sqft, p.total_price, p.status, p.facing, p.corner_plot, p.park_facing, c.name as colony_name, d.name as district_name, s.name as state_name, p.created_at FROM plots p LEFT JOIN colonies c ON p.colony_id = c.id LEFT JOIN districts d ON c.district_id = d.id LEFT JOIN states s ON d.state_id = s.id WHERE p.is_active = 1";

        $params = [];

        if ($colony_id) {
            $sql .= " AND p.colony_id = ?";
            $params[] = $colony_id;
        }

        if ($status) {
            $sql .= " AND p.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY c.name, p.plot_number";

        $stmt = !empty($params) ? $this->db->prepare($sql) : $this->db->query($sql);
        if (!empty($params)) {
            $stmt->execute($params);
        }
        $plots = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // CSV export
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="plots_export_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // Header
        fputcsv($output, ['Plot Number', 'Block', 'Sector', 'Type', 'Area (Sqft)', 'Area (Sqm)', 'Price/Sqft', 'Total Price', 'Status', 'Facing', 'Corner Plot', 'Park Facing', 'Colony', 'District', 'State', 'Created At']);

        // Data
        foreach ($plots as $plot) {
            fputcsv($output, [
                $plot['plot_number'],
                $plot['block'],
                $plot['sector'],
                $plot['plot_type'],
                $plot['area_sqft'],
                $plot['area_sqm'],
                $plot['price_per_sqft'],
                $plot['total_price'],
                $plot['status'],
                $plot['facing'],
                $plot['corner_plot'] ? 'Yes' : 'No',
                $plot['park_facing'] ? 'Yes' : 'No',
                $plot['colony_name'],
                $plot['district_name'],
                $plot['state_name'],
                $plot['created_at']
            ]);
        }

        fclose($output);
        exit();
    }

    // Helper methods
    private function getPlotsStatistics()
    {
        $stats = [];

        // Total plots
        $stats['total'] = $this->db->query("SELECT COUNT(*) as count FROM plots WHERE is_active = 1")->fetch()['count'];

        // By status
        $statusQuery = $this->db->query("SELECT status, COUNT(*) as count FROM plots WHERE is_active = 1 GROUP BY status");
        while ($row = $statusQuery->fetch()) {
            $stats['by_status'][$row['status']] = $row['count'];
        }

        // By type
        $typeQuery = $this->db->query("SELECT plot_type, COUNT(*) as count FROM plots WHERE is_active = 1 GROUP BY plot_type");
        while ($row = $typeQuery->fetch()) {
            $stats['by_type'][$row['plot_type']] = $row['count'];
        }

        // Price ranges
        $priceQuery = $this->db->query("SELECT 
            SUM(CASE WHEN total_price < 1000000 THEN 1 ELSE 0 END) as under_10lakh,
            SUM(CASE WHEN total_price >= 1000000 AND total_price < 2500000 THEN 1 ELSE 0 END) as _10_to_25lakh,
            SUM(CASE WHEN total_price >= 2500000 AND total_price < 5000000 THEN 1 ELSE 0 END) as _25_to_50lakh,
            SUM(CASE WHEN total_price >= 5000000 THEN 1 ELSE 0 END) as above_50lakh
            FROM plots WHERE is_active = 1");
        $stats['price_ranges'] = $priceQuery->fetch();

        return $stats;
    }

    private function logStatusChange($plot_id, $old_status, $new_status, $changed_by, $reason)
    {
        $stmt = $this->db->prepare("INSERT INTO plot_status_history (plot_id, old_status, new_status, changed_by, change_reason) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$plot_id, $old_status, $new_status, $changed_by, $reason]);
    }
}
