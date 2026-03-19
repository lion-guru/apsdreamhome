<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\CoreFunctionsServiceCustom;
use App\Services\LoggingService;
use App\Core\Database;
use Exception;

/**
 * Plot Controller - Custom MVC Implementation
 * Handles plot management operations in the Admin panel
 */
class PlotController extends AdminController
{
    private $loggingService;

    public function __construct()
    {
        parent::__construct();
        $this->loggingService = new LoggingService();

        // Register middlewares
        $this->middleware('csrf', ['only' => ['store', 'update', 'destroy']]);
    }

    /**
     * List all plots
     */
    public function index()
    {
        try {
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            $type = $_GET['type'] ?? '';
            $page = (int)($_GET['page'] ?? 1);
            $perPage = (int)($_GET['per_page'] ?? 20);

            $offset = ($page - 1) * $perPage;

            // Build query
            $sql = "SELECT p.*, 
                           l.land_title,
                           COUNT(pr.id) as property_count,
                           COALESCE(SUM(pr.total_area), 0) as developed_area
                    FROM plots p
                    LEFT JOIN land_records l ON p.land_id = l.id
                    LEFT JOIN properties pr ON p.id = pr.plot_id
                    WHERE 1=1";
            $params = [];

            // Apply filters
            if (!empty($search)) {
                $sql .= " AND (p.plot_number LIKE ? OR p.location LIKE ? OR l.land_title LIKE ?)";
                $searchParam = '%' . $search . '%';
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if (!empty($status)) {
                $sql .= " AND p.status = ?";
                $params[] = $status;
            }

            if (!empty($type)) {
                $sql .= " AND p.plot_type = ?";
                $params[] = $type;
            }

            $sql .= " GROUP BY p.id ORDER BY p.created_at DESC";

            // Count total
            $countSql = str_replace("SELECT p.*, l.land_title, COUNT(pr.id) as property_count, COALESCE(SUM(pr.total_area), 0) as developed_area", "SELECT COUNT(DISTINCT p.id) as total", $sql);
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetch()['total'];

            // Apply pagination
            $sql .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $perPage;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $plots = $stmt->fetchAll();

            $data = [
                'page_title' => 'Plot Management - APS Dream Home',
                'active_page' => 'plots',
                'plots' => $plots,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
                'filters' => [
                    'search' => $search,
                    'status' => $status,
                    'type' => $type
                ]
            ];

            return $this->render('admin/plots/index', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Plot Index error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load plots');
            return $this->redirect('admin/dashboard');
        }
    }

    /**
     * Show the form for creating a new plot
     */
    public function create()
    {
        try {
            // Get available land records
            $sql = "SELECT l.*, 
                           COUNT(p.id) as existing_plots,
                           (l.total_area - COALESCE(SUM(p.total_area), 0)) as available_area
                    FROM land_records l
                    LEFT JOIN plots p ON l.id = p.land_id
                    WHERE l.status = 'available'
                    GROUP BY l.id
                    HAVING available_area > 0
                    ORDER BY l.land_title";
            $landRecords = $this->db->fetchAll($sql);

            $data = [
                'page_title' => 'Create Plot - APS Dream Home',
                'active_page' => 'plots',
                'land_records' => $landRecords
            ];

            return $this->render('admin/plots/create', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Plot Create error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load plot form');
            return $this->redirect('admin/plots');
        }
    }

    /**
     * Store a newly created plot
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $data = $_POST;

            // Validate required fields
            $required = ['land_id', 'plot_number', 'total_area', 'plot_type'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return $this->jsonError(ucfirst(str_replace('_', ' ', $field)) . ' is required', 400);
                }
            }

            $landId = (int)$data['land_id'];
            $totalArea = (float)$data['total_area'];

            if ($landId <= 0 || $totalArea <= 0) {
                return $this->jsonError('Invalid land ID or area', 400);
            }

            // Check if land exists and has available area
            $sql = "SELECT l.*, 
                           COUNT(p.id) as existing_plots,
                           (l.total_area - COALESCE(SUM(p.total_area), 0)) as available_area
                    FROM land_records l
                    LEFT JOIN plots p ON l.id = p.land_id
                    WHERE l.id = ?
                    GROUP BY l.id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$landId]);
            $land = $stmt->fetch();

            if (!$land) {
                return $this->jsonError('Land record not found', 404);
            }

            if ($land['available_area'] < $totalArea) {
                return $this->jsonError('Insufficient available area in land record', 400);
            }

            // Check if plot number already exists for this land
            $sql = "SELECT id FROM plots WHERE land_id = ? AND plot_number = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$landId, $data['plot_number']]);
            if ($stmt->fetch()) {
                return $this->jsonError('Plot number already exists for this land', 400);
            }

            // Validate coordinates if provided
            $latitude = null;
            $longitude = null;
            if (!empty($data['latitude']) && !empty($data['longitude'])) {
                $latitude = (float)$data['latitude'];
                $longitude = (float)$data['longitude'];

                if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
                    return $this->jsonError('Invalid coordinates', 400);
                }
            }

            // Insert plot
            $sql = "INSERT INTO plots 
                    (land_id, plot_number, total_area, plot_type, location, description,
                     latitude, longitude, price_per_unit, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'available', NOW())";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $landId,
                CoreFunctionsServiceCustom::validateInput($data['plot_number'], 'string'),
                $totalArea,
                CoreFunctionsServiceCustom::validateInput($data['plot_type'], 'string'),
                CoreFunctionsServiceCustom::validateInput($data['location'] ?? '', 'string'),
                CoreFunctionsServiceCustom::validateInput($data['description'] ?? '', 'string'),
                $latitude,
                $longitude,
                (float)($data['price_per_unit'] ?? 0)
            ]);

            if ($result) {
                $plotId = $this->db->lastInsertId();

                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'plot_created', [
                    'plot_id' => $plotId,
                    'land_id' => $landId,
                    'plot_number' => $data['plot_number']
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Plot created successfully',
                    'plot_id' => $plotId
                ]);
            }

            return $this->jsonError('Failed to create plot', 500);
        } catch (Exception $e) {
            $this->loggingService->error("Plot Store error: " . $e->getMessage());
            return $this->jsonError('Failed to create plot', 500);
        }
    }

    /**
     * Display the specified plot
     */
    public function show($id)
    {
        try {
            $plotId = intval($id);
            if ($plotId <= 0) {
                $this->setFlash('error', 'Invalid plot ID');
                return $this->redirect('admin/plots');
            }

            // Get plot details
            $sql = "SELECT p.*, l.land_title, l.location as land_location
                    FROM plots p
                    LEFT JOIN land_records l ON p.land_id = l.id
                    WHERE p.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$plotId]);
            $plot = $stmt->fetch();

            if (!$plot) {
                $this->setFlash('error', 'Plot not found');
                return $this->redirect('admin/plots');
            }

            // Get properties on this plot
            $sql = "SELECT pr.*, 
                           b.booking_number,
                           c.name as customer_name
                    FROM properties pr
                    LEFT JOIN bookings b ON pr.id = b.property_id
                    LEFT JOIN users c ON b.customer_id = c.id
                    WHERE pr.plot_id = ?
                    ORDER BY pr.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$plotId]);
            $properties = $stmt->fetchAll();

            $data = [
                'page_title' => 'Plot Details - APS Dream Home',
                'active_page' => 'plots',
                'plot' => $plot,
                'properties' => $properties
            ];

            return $this->render('admin/plots/show', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Plot Show error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load plot details');
            return $this->redirect('admin/plots');
        }
    }

    /**
     * Show the form for editing the specified plot
     */
    public function edit($id)
    {
        try {
            $plotId = intval($id);
            if ($plotId <= 0) {
                $this->setFlash('error', 'Invalid plot ID');
                return $this->redirect('admin/plots');
            }

            // Get plot details
            $sql = "SELECT * FROM plots WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$plotId]);
            $plot = $stmt->fetch();

            if (!$plot) {
                $this->setFlash('error', 'Plot not found');
                return $this->redirect('admin/plots');
            }

            // Get available land records
            $sql = "SELECT * FROM land_records WHERE status = 'available' ORDER BY land_title";
            $landRecords = $this->db->fetchAll($sql);

            $data = [
                'page_title' => 'Edit Plot - APS Dream Home',
                'active_page' => 'plots',
                'plot' => $plot,
                'land_records' => $landRecords
            ];

            return $this->render('admin/plots/edit', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Plot Edit error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load plot form');
            return $this->redirect('admin/plots');
        }
    }

    /**
     * Update the specified plot
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $plotId = intval($id);
            if ($plotId <= 0) {
                return $this->jsonError('Invalid plot ID', 400);
            }

            $data = $_POST;

            // Check if plot exists
            $sql = "SELECT * FROM plots WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$plotId]);
            $plot = $stmt->fetch();

            if (!$plot) {
                return $this->jsonError('Plot not found', 404);
            }

            // Build update query
            $updateFields = [];
            $updateValues = [];

            if (isset($data['land_id'])) {
                $landId = (int)$data['land_id'];
                if ($landId <= 0) {
                    return $this->jsonError('Invalid land ID', 400);
                }
                $updateFields[] = "land_id = ?";
                $updateValues[] = $landId;
            }

            if (!empty($data['plot_number'])) {
                // Check if plot number already exists for this land (excluding current plot)
                $sql = "SELECT id FROM plots WHERE land_id = ? AND plot_number = ? AND id != ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$data['land_id'] ?? $plot['land_id'], $data['plot_number'], $plotId]);
                if ($stmt->fetch()) {
                    return $this->jsonError('Plot number already exists for this land', 400);
                }
                $updateFields[] = "plot_number = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['plot_number'], 'string');
            }

            if (!empty($data['total_area'])) {
                $totalArea = (float)$data['total_area'];
                if ($totalArea <= 0) {
                    return $this->jsonError('Total area must be greater than 0', 400);
                }
                $updateFields[] = "total_area = ?";
                $updateValues[] = $totalArea;
            }

            if (!empty($data['plot_type'])) {
                $updateFields[] = "plot_type = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['plot_type'], 'string');
            }

            if (isset($data['location'])) {
                $updateFields[] = "location = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['location'], 'string');
            }

            if (isset($data['description'])) {
                $updateFields[] = "description = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['description'], 'string');
            }

            // Validate coordinates if provided
            if (!empty($data['latitude']) && !empty($data['longitude'])) {
                $latitude = (float)$data['latitude'];
                $longitude = (float)$data['longitude'];

                if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
                    return $this->jsonError('Invalid coordinates', 400);
                }

                $updateFields[] = "latitude = ?";
                $updateValues[] = $latitude;
                $updateFields[] = "longitude = ?";
                $updateValues[] = $longitude;
            }

            if (isset($data['price_per_unit'])) {
                $updateFields[] = "price_per_unit = ?";
                $updateValues[] = (float)$data['price_per_unit'];
            }

            if (isset($data['status'])) {
                $validStatuses = ['available', 'reserved', 'under_development', 'developed'];
                if (in_array($data['status'], $validStatuses)) {
                    $updateFields[] = "status = ?";
                    $updateValues[] = $data['status'];
                }
            }

            if (empty($updateFields)) {
                return $this->jsonError('No fields to update', 400);
            }

            $updateFields[] = "updated_at = NOW()";
            $updateValues[] = $plotId;

            $sql = "UPDATE plots SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($updateValues);

            if ($result) {
                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'plot_updated', [
                    'plot_id' => $plotId,
                    'changes' => $data
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Plot updated successfully'
                ]);
            }

            return $this->jsonError('Failed to update plot', 500);
        } catch (Exception $e) {
            $this->loggingService->error("Plot Update error: " . $e->getMessage());
            return $this->jsonError('Failed to update plot', 500);
        }
    }

    /**
     * Remove the specified plot
     */
    public function destroy($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $plotId = intval($id);
            if ($plotId <= 0) {
                return $this->jsonError('Invalid plot ID', 400);
            }

            // Check if plot exists
            $sql = "SELECT * FROM plots WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$plotId]);
            $plot = $stmt->fetch();

            if (!$plot) {
                return $this->jsonError('Plot not found', 404);
            }

            // Check if plot has properties
            $sql = "SELECT COUNT(*) as property_count FROM properties WHERE plot_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$plotId]);
            $propertyCount = $stmt->fetch()['property_count'];

            if ($propertyCount > 0) {
                return $this->jsonError('Cannot delete plot with existing properties', 400);
            }

            // Delete plot
            $sql = "DELETE FROM plots WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$plotId]);

            if ($result) {
                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'plot_deleted', [
                    'plot_id' => $plotId,
                    'plot_number' => $plot['plot_number']
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Plot deleted successfully'
                ]);
            }

            return $this->jsonError('Failed to delete plot', 500);
        } catch (Exception $e) {
            $this->loggingService->error("Plot Destroy error: " . $e->getMessage());
            return $this->jsonError('Failed to delete plot', 500);
        }
    }

    /**
     * Get plot statistics
     */
    public function getStats()
    {
        try {
            $stats = [];

            // Total plots
            $sql = "SELECT COUNT(*) as total FROM plots";
            $result = $this->db->fetchOne($sql);
            $stats['total_plots'] = (int)($result['total'] ?? 0);

            // Total plot area
            $sql = "SELECT COALESCE(SUM(total_area), 0) as total FROM plots";
            $result = $this->db->fetchOne($sql);
            $stats['total_area'] = (float)($result['total'] ?? 0);

            // Plots by status
            $sql = "SELECT status, COUNT(*) as count FROM plots GROUP BY status";
            $stats['by_status'] = $this->db->fetchAll($sql) ?: [];

            // Plots by type
            $sql = "SELECT plot_type, COUNT(*) as count FROM plots GROUP BY plot_type";
            $stats['by_type'] = $this->db->fetchAll($sql) ?: [];

            // Developed plots
            $sql = "SELECT COUNT(*) as total FROM plots WHERE status = 'developed'";
            $result = $this->db->fetchOne($sql);
            $stats['developed_plots'] = (int)($result['total'] ?? 0);

            return $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            $this->loggingService->error("Get Plot Stats error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch plot stats'
            ], 500);
        }
    }
}
