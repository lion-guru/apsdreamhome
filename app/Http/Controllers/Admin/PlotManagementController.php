<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\CoreFunctionsServiceCustom;
use App\Services\LoggingService;
use App\Core\Database;
use Exception;

/**
 * Plot Management Controller - Custom MVC Implementation
 * Handles advanced plot management operations in the Admin panel
 */
class PlotManagementController extends AdminController
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
     * List plots for a specific site
     */
    public function index($siteId = null)
    {
        try {
            $page = (int)($_GET['page'] ?? 1);
            $search = trim($_GET['search'] ?? '');
            $status = $_GET['status'] ?? '';
            $siteId = $siteId ? intval($siteId) : intval($_GET['site_id'] ?? 0);
            $perPage = (int)($_GET['per_page'] ?? 20);

            $offset = ($page - 1) * $perPage;

            // Build query
            $sql = "SELECT p.*, 
                           s.site_name,
                           l.land_title,
                           COUNT(pr.id) as property_count,
                           COALESCE(SUM(pr.total_area), 0) as developed_area
                    FROM plots p
                    LEFT JOIN sites s ON p.site_id = s.id
                    LEFT JOIN land_records l ON p.land_id = l.id
                    LEFT JOIN properties pr ON p.id = pr.plot_id
                    WHERE 1=1";
            $params = [];

            // Apply filters
            if ($siteId > 0) {
                $sql .= " AND p.site_id = ?";
                $params[] = $siteId;
            }

            if (!empty($search)) {
                $sql .= " AND (p.plot_number LIKE ? OR p.location LIKE ? OR s.site_name LIKE ?)";
                $searchParam = '%' . $search . '%';
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if (!empty($status)) {
                $sql .= " AND p.status = ?";
                $params[] = $status;
            }

            $sql .= " GROUP BY p.id ORDER BY p.created_at DESC";

            // Count total
            $countSql = str_replace("SELECT p.*, s.site_name, l.land_title, COUNT(pr.id) as property_count, COALESCE(SUM(pr.total_area), 0) as developed_area", "SELECT COUNT(DISTINCT p.id) as total", $sql);
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

            // Get sites for filter
            $sites = $this->db->fetchAll("SELECT * FROM sites ORDER BY site_name");

            $data = [
                'page_title' => 'Plot Management - APS Dream Home',
                'active_page' => 'plot_management',
                'plots' => $plots,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
                'filters' => [
                    'search' => $search,
                    'status' => $status,
                    'site_id' => $siteId
                ],
                'sites' => $sites
            ];

            return $this->render('admin/plots/index', $data);
        } catch (Exception $e) {
            error_log("Plot Management Index error: " . $e->getMessage());
            $data = [
                'page_title' => 'Plot Management - APS Dream Home',
                'active_page' => 'plot_management',
                'plots' => [],
                'total' => 0,
                'page' => 1,
                'per_page' => 20,
                'total_pages' => 0,
                'filters' => ['search' => '', 'status' => '', 'site_id' => 0],
                'sites' => [],
                'error' => 'Unable to load plots: ' . $e->getMessage()
            ];
            return $this->render('admin/plots/index', $data);
        }
    }

    /**
     * Display plot management dashboard
     */
    public function dashboard()
    {
        try {
            $data = [
                'page_title' => 'Plot Management Dashboard - APS Dream Home',
                'active_page' => 'plot_management',
                'dashboard_stats' => $this->getDashboardStats(),
                'site_summary' => $this->getSiteSummary(),
                'recent_activity' => $this->getRecentActivity()
            ];

            return $this->render('admin/plot_management/dashboard', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Plot Management Dashboard error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load dashboard');
            return $this->redirect('admin/plot_management');
        }
    }

    /**
     * Display plot allocation management
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
                           p.plot_number, p.total_area, p.status as plot_status,
                           u.name as requested_by_name, u.email as requested_by_email,
                           s.site_name
                    FROM plot_allocations pa
                    LEFT JOIN plots p ON pa.plot_id = p.id
                    LEFT JOIN users u ON pa.requested_by = u.id
                    LEFT JOIN sites s ON p.site_id = s.id
                    WHERE 1=1";
            $params = [];

            // Apply filters
            if (!empty($search)) {
                $sql .= " AND (p.plot_number LIKE ? OR u.name LIKE ? OR s.site_name LIKE ?)";
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
            $countSql = str_replace("SELECT pa.*, p.plot_number, p.total_area, p.status as plot_status, u.name as requested_by_name, u.email as requested_by_email, s.site_name", "SELECT COUNT(DISTINCT pa.id) as total", $sql);
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
                'page_title' => 'Plot Allocation - APS Dream Home',
                'active_page' => 'plot_management',
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

            return $this->render('admin/plot_management/allocation', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Plot Allocation error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load allocation data');
            return $this->redirect('admin/plot_management');
        }
    }

    /**
     * Process plot allocation
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
                $sql = "SELECT pa.*, p.plot_number, p.status as plot_status
                        FROM plot_allocations pa
                        LEFT JOIN plots p ON pa.plot_id = p.id
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
                    $sql = "UPDATE plot_allocations 
                            SET status = 'approved', processed_by = ?, processed_at = NOW(), notes = ?
                            WHERE id = ?";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$_SESSION['user_id'] ?? 0, $notes, $allocationId]);

                    // Update plot status
                    $sql = "UPDATE plots SET status = 'allocated', updated_at = NOW() WHERE id = ?";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$allocation['plot_id']]);
                } elseif ($action === 'reject') {
                    // Update allocation status
                    $sql = "UPDATE plot_allocations 
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
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'plot_allocation_processed', [
                    'allocation_id' => $allocationId,
                    'action' => $action,
                    'plot_id' => $allocation['plot_id']
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
            $this->loggingService->error("Process Allocation error: " . $e->getMessage());
            return $this->jsonError('Failed to process allocation', 500);
        }
    }

    /**
     * Display plot development tracking
     */
    public function development()
    {
        try {
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            $page = (int)($_GET['page'] ?? 1);
            $perPage = (int)($_GET['per_page'] ?? 20);

            $offset = ($page - 1) * $perPage;

            // Build query for development tracking
            $sql = "SELECT pd.*, 
                           p.plot_number, p.total_area, p.status as plot_status,
                           s.site_name,
                           u.name as developer_name
                    FROM plot_development pd
                    LEFT JOIN plots p ON pd.plot_id = p.id
                    LEFT JOIN sites s ON p.site_id = s.id
                    LEFT JOIN users u ON pd.developer_id = u.id
                    WHERE 1=1";
            $params = [];

            // Apply filters
            if (!empty($search)) {
                $sql .= " AND (p.plot_number LIKE ? OR s.site_name LIKE ? OR u.name LIKE ?)";
                $searchParam = '%' . $search . '%';
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if (!empty($status)) {
                $sql .= " AND pd.status = ?";
                $params[] = $status;
            }

            $sql .= " ORDER BY pd.created_at DESC";

            // Count total
            $countSql = str_replace("SELECT pd.*, p.plot_number, p.total_area, p.status as plot_status, s.site_name, u.name as developer_name", "SELECT COUNT(DISTINCT pd.id) as total", $sql);
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetch()['total'];

            // Apply pagination
            $sql .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $perPage;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $developments = $stmt->fetchAll();

            $data = [
                'page_title' => 'Plot Development - APS Dream Home',
                'active_page' => 'plot_management',
                'developments' => $developments,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
                'filters' => [
                    'search' => $search,
                    'status' => $status
                ]
            ];

            return $this->render('admin/plot_management/development', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Plot Development error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load development data');
            return $this->redirect('admin/plot_management');
        }
    }

    /**
     * Bulk update plot status
     */
    public function bulkUpdate()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $plotIds = $_POST['plot_ids'] ?? [];
            $status = $_POST['status'] ?? '';
            $notes = $_POST['notes'] ?? '';

            if (empty($plotIds) || empty($status)) {
                return $this->jsonError('Invalid parameters', 400);
            }

            $validStatuses = ['available', 'reserved', 'under_development', 'developed'];
            if (!in_array($status, $validStatuses)) {
                return $this->jsonError('Invalid status', 400);
            }

            $updated = 0;
            $failed = 0;

            foreach ($plotIds as $plotId) {
                try {
                    $sql = "UPDATE plots SET status = ?, updated_at = NOW() WHERE id = ?";
                    $stmt = $this->db->prepare($sql);
                    $result = $stmt->execute([$status, (int)$plotId]);

                    if ($result) {
                        $updated++;

                        // Log each update
                        $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'plot_bulk_updated', [
                            'plot_id' => $plotId,
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

            // Total plots
            $sql = "SELECT COUNT(*) as total FROM plots";
            $result = $this->db->fetchOne($sql);
            $stats['total_plots'] = (int)($result['total'] ?? 0);

            // Plots by status
            $sql = "SELECT status, COUNT(*) as count FROM plots GROUP BY status";
            $stats['by_status'] = $this->db->fetchAll($sql) ?: [];

            // Total area
            $sql = "SELECT COALESCE(SUM(total_area), 0) as total FROM plots";
            $result = $this->db->fetchOne($sql);
            $stats['total_area'] = (float)($result['total'] ?? 0);

            // Developed area
            $sql = "SELECT COALESCE(SUM(pr.total_area), 0) as developed
                    FROM properties pr
                    JOIN plots p ON pr.plot_id = p.id
                    WHERE p.status = 'developed'";
            $result = $this->db->fetchOne($sql);
            $stats['developed_area'] = (float)($result['developed'] ?? 0);

            // Pending allocations
            $sql = "SELECT COUNT(*) as total FROM plot_allocations WHERE status = 'pending'";
            $result = $this->db->fetchOne($sql);
            $stats['pending_allocations'] = (int)($result['total'] ?? 0);

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
                           COUNT(p.id) as plot_count,
                           COALESCE(SUM(p.total_area), 0) as total_area,
                           COUNT(CASE WHEN p.status = 'developed' THEN 1 END) as developed_plots
                    FROM sites s
                    LEFT JOIN plots p ON s.id = p.site_id
                    GROUP BY s.id, s.site_name
                    ORDER BY plot_count DESC
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

            // Recent plot allocations
            $sql = "SELECT 'allocation' as type, pa.created_at, p.plot_number, u.name as user_name, pa.status
                    FROM plot_allocations pa
                    LEFT JOIN plots p ON pa.plot_id = p.id
                    LEFT JOIN users u ON pa.requested_by = u.id
                    ORDER BY pa.created_at DESC
                    LIMIT 5";
            $activities = array_merge($activities, $this->db->fetchAll($sql) ?: []);

            // Recent plot updates
            $sql = "SELECT 'plot_update' as type, p.updated_at as created_at, p.plot_number, u.name as user_name, p.status
                    FROM plots p
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
     * Export plot management data
     */
    public function export()
    {
        try {
            $format = $_GET['format'] ?? 'csv';
            $type = $_GET['type'] ?? 'plots';
            $startDate = $_GET['start_date'] ?? date('Y-m-01');
            $endDate = $_GET['end_date'] ?? date('Y-m-t');

            switch ($type) {
                case 'plots':
                    $data = $this->getPlotsExport($startDate, $endDate);
                    break;
                case 'allocations':
                    $data = $this->getAllocationsExport($startDate, $endDate);
                    break;
                case 'development':
                    $data = $this->getDevelopmentExport($startDate, $endDate);
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
            return $this->redirect('admin/plot_management');
        } catch (Exception $e) {
            $this->loggingService->error("Plot Management Export error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to export data');
            return $this->redirect('admin/plot_management');
        }
    }

    /**
     * Get plots data for export
     */
    private function getPlotsExport(string $startDate, string $endDate): array
    {
        try {
            $sql = "SELECT p.*, s.site_name, l.land_title
                    FROM plots p
                    LEFT JOIN sites s ON p.site_id = s.id
                    LEFT JOIN land_records l ON p.land_id = l.id
                    WHERE p.created_at BETWEEN ? AND ?
                    ORDER BY p.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Plots Export error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get allocations data for export
     */
    private function getAllocationsExport(string $startDate, string $endDate): array
    {
        try {
            $sql = "SELECT pa.*, p.plot_number, u.name as requested_by_name, s.site_name
                    FROM plot_allocations pa
                    LEFT JOIN plots p ON pa.plot_id = p.id
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
     * Get development data for export
     */
    private function getDevelopmentExport(string $startDate, string $endDate): array
    {
        try {
            $sql = "SELECT pd.*, p.plot_number, u.name as developer_name, s.site_name
                    FROM plot_development pd
                    LEFT JOIN plots p ON pd.plot_id = p.id
                    LEFT JOIN users u ON pd.developer_id = u.id
                    LEFT JOIN sites s ON p.site_id = s.id
                    WHERE pd.created_at BETWEEN ? AND ?
                    ORDER BY pd.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Development Export error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Export data as CSV
     */
    private function exportCSV(array $data, string $type, string $startDate, string $endDate): void
    {
        $filename = "plot_management_{$type}_{$startDate}_to_{$endDate}.csv";

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
        $filename = "plot_management_{$type}_{$startDate}_to_{$endDate}.json";

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
