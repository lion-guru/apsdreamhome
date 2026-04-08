<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\CoreFunctionsServiceCustom;
use App\Services\LoggingService;
use App\Core\Database;
use Exception;

/**
 * Property Management Controller - Custom MVC Implementation
 * Handles advanced property management operations in the Admin panel
 */
class PropertyManagementController extends AdminController
{
    private $loggingService;

    public function __construct()
    {
        parent::__construct();
        $this->loggingService = new LoggingService();

        // Register middlewares
        $this->middleware('csrf', ['only' => ['store', 'update', 'destroy', 'bulkUpdate']]);
    }

    /**
     * List properties with site integration
     */
    public function index()
    {
        try {
            $page = (int)($_GET['page'] ?? 1);
            $search = trim($_GET['search'] ?? '');
            $status = $_GET['status'] ?? '';
            $type = $_GET['type'] ?? '';
            $siteId = intval($_GET['site_id'] ?? 0);
            $perPage = (int)($_GET['per_page'] ?? 20);

            $offset = ($page - 1) * $perPage;

            // Build query
            $sql = "SELECT p.*, 
                           s.site_name,
                           pr.name as project_name,
                           pl.plot_number,
                           l.land_title,
                           c.name as category_name,
                           u.name as customer_name,
                           COUNT(pi.id) as image_count
                    FROM properties p
                    LEFT JOIN sites s ON p.site_id = s.id
                    LEFT JOIN projects pr ON p.project_id = pr.id
                    LEFT JOIN plots pl ON p.plot_id = pl.id
                    LEFT JOIN land_records l ON p.land_id = l.id
                    LEFT JOIN property_categories c ON p.category_id = c.id
                    LEFT JOIN bookings b ON p.id = b.property_id
                    LEFT JOIN users u ON b.customer_id = u.id
                    LEFT JOIN property_images pi ON p.id = pi.property_id
                    WHERE 1=1";
            $params = [];

            // Apply filters
            if ($siteId > 0) {
                $sql .= " AND p.site_id = ?";
                $params[] = $siteId;
            }

            if (!empty($search)) {
                $sql .= " AND (p.title LIKE ? OR p.description LIKE ? OR p.location LIKE ? OR s.site_name LIKE ?)";
                $searchParam = '%' . $search . '%';
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if (!empty($status)) {
                $sql .= " AND p.status = ?";
                $params[] = $status;
            }

            if (!empty($type)) {
                $sql .= " AND p.property_type = ?";
                $params[] = $type;
            }

            $sql .= " GROUP BY p.id ORDER BY p.created_at DESC";

            // Count total
            $countSql = str_replace("SELECT p.*, s.site_name, pr.name as project_name, pl.plot_number, l.land_title, c.name as category_name, u.name as customer_name, COUNT(pi.id) as image_count", "SELECT COUNT(DISTINCT p.id) as total", $sql);
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetch()['total'];

            // Apply pagination
            $sql .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $perPage;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $properties = $stmt->fetchAll();

            // Get sites for filter
            $sites = $this->db->fetchAll("SELECT * FROM sites ORDER BY site_name");

            $data = [
                'page_title' => 'Property Management - APS Dream Home',
                'active_page' => 'property_management',
                'properties' => $properties,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
                'filters' => [
                    'search' => $search,
                    'status' => $status,
                    'type' => $type,
                    'site_id' => $siteId
                ],
                'sites' => $sites
            ];

            return $this->render('admin/properties/index', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Property Management Index error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load property management data');
            return $this->redirect('admin/dashboard');
        }
    }

    /**
     * Display property management dashboard
     */
    public function dashboard()
    {
        try {
            $data = [
                'page_title' => 'Property Management Dashboard - APS Dream Home',
                'active_page' => 'property_management',
                'dashboard_stats' => $this->getDashboardStats(),
                'site_summary' => $this->getSiteSummary(),
                'recent_activity' => $this->getRecentActivity()
            ];

            return $this->render('admin/properties/dashboard', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Property Management Dashboard error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load dashboard');
            return $this->redirect('admin/property_management');
        }
    }

    /**
     * Display property allocation management
     */
    public function allocation()
    {
        try {
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            $page = (int)($_GET['page'] ?? 1);
            $perPage = (int)($_GET['per_page'] ?? 20);

            $offset = ($page - 1) * $perPage;

            // Build query for allocation requests
            $sql = "SELECT pa.*, 
                           p.title as property_title, p.price, p.location,
                           u.name as requested_by_name, u.email as requested_by_email,
                           s.site_name
                    FROM property_allocations pa
                    LEFT JOIN properties p ON pa.property_id = p.id
                    LEFT JOIN users u ON pa.requested_by = u.id
                    LEFT JOIN sites s ON p.site_id = s.id
                    WHERE 1=1";
            $params = [];

            // Apply filters
            if (!empty($search)) {
                $sql .= " AND (p.title LIKE ? OR u.name LIKE ? OR s.site_name LIKE ?)";
                $searchParam = '%' . $search . '%';
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if (!empty($status)) {
                $sql .= " AND pa.status = ?";
                $params[] = $status;
            }

            $sql .= " ORDER BY pa.created_at DESC";

            // Count total
            $countSql = str_replace("SELECT pa.*, p.title as property_title, p.price, p.location, u.name as requested_by_name, u.email as requested_by_email, s.site_name", "SELECT COUNT(DISTINCT pa.id) as total", $sql);
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetch()['total'];

            // Apply pagination
            $sql .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $perPage;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $allocations = $stmt->fetchAll();

            $data = [
                'page_title' => 'Property Allocation - APS Dream Home',
                'active_page' => 'property_management',
                'allocations' => $allocations,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
                'filters' => [
                    'search' => $search,
                    'status' => $status
                ]
            ];

            return $this->render('admin/properties/allocation', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Property Allocation error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load allocation data');
            return $this->redirect('admin/property_management');
        }
    }

    /**
     * Process property allocation
     */
    public function processAllocation($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $allocationId = intval($id);
            $action = $_POST['action'] ?? '';
            $notes = $_POST['notes'] ?? '';

            if ($allocationId <= 0 || empty($action)) {
                return $this->jsonError('Invalid parameters', 400);
            }

            $this->db->beginTransaction();

            try {
                // Get allocation details
                $sql = "SELECT pa.*, p.title as property_title, p.status as property_status
                        FROM property_allocations pa
                        LEFT JOIN properties p ON pa.property_id = p.id
                        WHERE pa.id = ? AND pa.status = 'pending'";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$allocationId]);
                $allocation = $stmt->fetch();

                if (!$allocation) {
                    $this->db->rollBack();
                    return $this->jsonError('Allocation not found or already processed', 404);
                }

                // Process allocation based on action
                if ($action === 'approve') {
                    // Update allocation status
                    $sql = "UPDATE property_allocations 
                            SET status = 'approved', processed_by = ?, processed_at = NOW(), notes = ?
                            WHERE id = ?";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$_SESSION['user_id'] ?? 0, $notes, $allocationId]);

                    // Update property status
                    $sql = "UPDATE properties SET status = 'reserved', updated_at = NOW() WHERE id = ?";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$allocation['property_id']]);
                } elseif ($action === 'reject') {
                    // Update allocation status
                    $sql = "UPDATE property_allocations 
                            SET status = 'rejected', processed_by = ?, processed_at = NOW(), notes = ?
                            WHERE id = ?";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$_SESSION['user_id'] ?? 0, $notes, $allocationId]);
                } else {
                    $this->db->rollBack();
                    return $this->jsonError('Invalid action', 400);
                }

                $this->db->commit();

                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'property_allocation_processed', [
                    'allocation_id' => $allocationId,
                    'action' => $action,
                    'property_id' => $allocation['property_id']
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => "Allocation {$action}d successfully"
                ]);
            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            $this->loggingService->error("Process Property Allocation error: " . $e->getMessage());
            return $this->jsonError('Failed to process allocation', 500);
        }
    }

    /**
     * Display property maintenance tracking
     */
    public function maintenance()
    {
        try {
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            $page = (int)($_GET['page'] ?? 1);
            $perPage = (int)($_GET['per_page'] ?? 20);

            $offset = ($page - 1) * $perPage;

            // Build query for maintenance tracking
            $sql = "SELECT pm.*, 
                           p.title as property_title, p.location,
                           s.site_name,
                           u.name as assigned_to_name
                    FROM property_maintenance pm
                    LEFT JOIN properties p ON pm.property_id = p.id
                    LEFT JOIN sites s ON p.site_id = s.id
                    LEFT JOIN users u ON pm.assigned_to = u.id
                    WHERE 1=1";
            $params = [];

            // Apply filters
            if (!empty($search)) {
                $sql .= " AND (p.title LIKE ? OR s.site_name LIKE ? OR u.name LIKE ?)";
                $searchParam = '%' . $search . '%';
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if (!empty($status)) {
                $sql .= " AND pm.status = ?";
                $params[] = $status;
            }

            $sql .= " ORDER BY pm.created_at DESC";

            // Count total
            $countSql = str_replace("SELECT pm.*, p.title as property_title, p.location, s.site_name, u.name as assigned_to_name", "SELECT COUNT(DISTINCT pm.id) as total", $sql);
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetch()['total'];

            // Apply pagination
            $sql .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $perPage;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $maintenance = $stmt->fetchAll();

            $data = [
                'page_title' => 'Property Maintenance - APS Dream Home',
                'active_page' => 'property_management',
                'maintenance' => $maintenance,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
                'filters' => [
                    'search' => $search,
                    'status' => $status
                ]
            ];

            return $this->render('admin/properties/maintenance', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Property Maintenance error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load maintenance data');
            return $this->redirect('admin/property_management');
        }
    }

    /**
     * Bulk update property status
     */
    public function bulkUpdate()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $propertyIds = $_POST['property_ids'] ?? [];
            $status = $_POST['status'] ?? '';
            $notes = $_POST['notes'] ?? '';

            if (empty($propertyIds) || empty($status)) {
                return $this->jsonError('Invalid parameters', 400);
            }

            $validStatuses = ['available', 'sold', 'reserved', 'under_maintenance'];
            if (!in_array($status, $validStatuses)) {
                return $this->jsonError('Invalid status', 400);
            }

            $updated = 0;
            $failed = 0;

            foreach ($propertyIds as $propertyId) {
                try {
                    $sql = "UPDATE properties SET status = ?, updated_at = NOW() WHERE id = ?";
                    $stmt = $this->db->prepare($sql);
                    $result = $stmt->execute([$status, (int)$propertyId]);

                    if ($result) {
                        $updated++;

                        // Log each update
                        $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'property_bulk_updated', [
                            'property_id' => $propertyId,
                            'status' => $status,
                            'notes' => $notes
                        ]);
                    } else {
                        $failed++;
                    }
                } catch (Exception $e) {
                    $failed++;
                }
            }

            return $this->jsonResponse([
                'success' => true,
                'message' => "Bulk update completed: {$updated} updated, {$failed} failed",
                'updated' => $updated,
                'failed' => $failed
            ]);
        } catch (Exception $e) {
            $this->loggingService->error("Bulk Update error: " . $e->getMessage());
            return $this->jsonError('Failed to perform bulk update', 500);
        }
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStats(): array
    {
        try {
            $stats = [];

            // Total properties
            $sql = "SELECT COUNT(*) as total FROM properties";
            $result = $this->db->fetchOne($sql);
            $stats['total_properties'] = (int)($result['total'] ?? 0);

            // Properties by status
            $sql = "SELECT status, COUNT(*) as count FROM properties GROUP BY status";
            $stats['by_status'] = $this->db->fetchAll($sql) ?: [];

            // Total value
            $sql = "SELECT COALESCE(SUM(price), 0) as total FROM properties";
            $result = $this->db->fetchOne($sql);
            $stats['total_value'] = (float)($result['total'] ?? 0);

            // Pending allocations
            $sql = "SELECT COUNT(*) as total FROM property_allocations WHERE status = 'pending'";
            $result = $this->db->fetchOne($sql);
            $stats['pending_allocations'] = (int)($result['total'] ?? 0);

            // Maintenance requests
            $sql = "SELECT COUNT(*) as total FROM property_maintenance WHERE status = 'pending'";
            $result = $this->db->fetchOne($sql);
            $stats['pending_maintenance'] = (int)($result['total'] ?? 0);

            return $stats;
        } catch (Exception $e) {
            $this->loggingService->error("Get Dashboard Stats error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get site summary
     */
    private function getSiteSummary(): array
    {
        try {
            $sql = "SELECT s.site_name, 
                           COUNT(p.id) as property_count,
                           COALESCE(SUM(p.price), 0) as total_value,
                           COUNT(CASE WHEN p.status = 'available' THEN 1 END) as available_properties
                    FROM sites s
                    LEFT JOIN properties p ON s.id = p.site_id
                    GROUP BY s.id, s.site_name
                    ORDER BY property_count DESC
                    LIMIT 10";
            return $this->db->fetchAll($sql) ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Site Summary error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent activity
     */
    private function getRecentActivity(): array
    {
        try {
            $activities = [];

            // Recent property allocations
            $sql = "SELECT 'allocation' as type, pa.created_at, p.title as property_title, u.name as user_name, pa.status
                    FROM property_allocations pa
                    LEFT JOIN properties p ON pa.property_id = p.id
                    LEFT JOIN users u ON pa.requested_by = u.id
                    ORDER BY pa.created_at DESC
                    LIMIT 5";
            $activities = array_merge($activities, $this->db->fetchAll($sql) ?: []);

            // Recent property updates
            $sql = "SELECT 'property_update' as type, p.updated_at as created_at, p.title as property_title, u.name as user_name, p.status
                    FROM properties p
                    LEFT JOIN users u ON p.updated_by = u.id
                    WHERE p.updated_at IS NOT NULL
                    ORDER BY p.updated_at DESC
                    LIMIT 5";
            $activities = array_merge($activities, $this->db->fetchAll($sql) ?: []);

            // Sort by date and limit
            usort($activities, function ($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });

            return array_slice($activities, 0, 10);
        } catch (Exception $e) {
            $this->loggingService->error("Get Recent Activity error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Export property management data
     */
    public function export()
    {
        try {
            $format = $_GET['format'] ?? 'csv';
            $type = $_GET['type'] ?? 'properties';
            $startDate = $_GET['start_date'] ?? date('Y-m-01');
            $endDate = $_GET['end_date'] ?? date('Y-m-t');

            switch ($type) {
                case 'properties':
                    $data = $this->getPropertiesExport($startDate, $endDate);
                    break;
                case 'allocations':
                    $data = $this->getAllocationsExport($startDate, $endDate);
                    break;
                case 'maintenance':
                    $data = $this->getMaintenanceExport($startDate, $endDate);
                    break;
                default:
                    $data = [];
            }

            if ($format === 'csv') {
                return $this->exportCSV($data, $type, $startDate, $endDate);
            } elseif ($format === 'json') {
                return $this->exportJSON($data, $type, $startDate, $endDate);
            }

            $this->setFlash('error', 'Invalid export format');
            return $this->redirect('admin/property_management');
        } catch (Exception $e) {
            $this->loggingService->error("Property Management Export error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to export data');
            return $this->redirect('admin/property_management');
        }
    }

    /**
     * Get properties data for export
     */
    private function getPropertiesExport(string $startDate, string $endDate): array
    {
        try {
            $sql = "SELECT p.*, s.site_name, pr.name as project_name, c.name as category_name
                    FROM properties p
                    LEFT JOIN sites s ON p.site_id = s.id
                    LEFT JOIN projects pr ON p.project_id = pr.id
                    LEFT JOIN property_categories c ON p.category_id = c.id
                    WHERE p.created_at BETWEEN ? AND ?
                    ORDER BY p.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Properties Export error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get allocations data for export
     */
    private function getAllocationsExport(string $startDate, string $endDate): array
    {
        try {
            $sql = "SELECT pa.*, p.title as property_title, u.name as requested_by_name, s.site_name
                    FROM property_allocations pa
                    LEFT JOIN properties p ON pa.property_id = p.id
                    LEFT JOIN users u ON pa.requested_by = u.id
                    LEFT JOIN sites s ON p.site_id = s.id
                    WHERE pa.created_at BETWEEN ? AND ?
                    ORDER BY pa.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Allocations Export error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get maintenance data for export
     */
    private function getMaintenanceExport(string $startDate, string $endDate): array
    {
        try {
            $sql = "SELECT pm.*, p.title as property_title, u.name as assigned_to_name, s.site_name
                    FROM property_maintenance pm
                    LEFT JOIN properties p ON pm.property_id = p.id
                    LEFT JOIN users u ON pm.assigned_to = u.id
                    LEFT JOIN sites s ON p.site_id = s.id
                    WHERE pm.created_at BETWEEN ? AND ?
                    ORDER BY pm.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Maintenance Export error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Export data as CSV
     */
    private function exportCSV(array $data, string $type, string $startDate, string $endDate): void
    {
        $filename = "property_management_{$type}_{$startDate}_to_{$endDate}.csv";

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        if (!empty($data)) {
            // Header row
            fputcsv($output, array_keys($data[0]));

            // Data rows
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }

        fclose($output);
        exit;
    }

    /**
     * Export data as JSON
     */
    private function exportJSON(array $data, string $type, string $startDate, string $endDate): void
    {
        $filename = "property_management_{$type}_{$startDate}_to_{$endDate}.json";

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo json_encode([
            'type' => $type,
            'period' => ['start' => $startDate, 'end' => $endDate],
            'data' => $data,
            'exported_at' => date('Y-m-d H:i:s')
        ]);

        exit;
    }
}
