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
     * Plot categories/types management
     */
    public function categories()
    {
        $this->requireAdmin();
        $this->render('admin/plots/categories', [
            'page_title' => 'Plot Categories'
        ]);
    }

    /**
     * Show create plot form
     */
    public function create()
    {
        try {
            $sites = $this->db->fetchAll("SELECT id, name, state_name, district_name, name as colony_name FROM colonies ORDER BY name");
        } catch (\Exception $e) {
            $sites = [];
        }
        $this->render('admin/plots/create', [
            'page_title' => 'Create New Plot',
            'colonies' => $sites
        ]);
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
            $required = ['plot_number', 'colony_id'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return $this->jsonError(ucfirst(str_replace('_', ' ', $field)) . ' is required', 400);
                }
            }

            $sql = "INSERT INTO plots (site_id, plot_number, total_area, status, location, price, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                intval($data['colony_id']),
                CoreFunctionsServiceCustom::validateInput($data['plot_number'], 'string'),
                floatval($data['total_area'] ?? 0),
                $data['status'] ?? 'available',
                CoreFunctionsServiceCustom::validateInput($data['location'] ?? '', 'string'),
                floatval($data['price'] ?? 0)
            ]);

            if ($result) {
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'plot_created', [
                    'plot_number' => $data['plot_number'],
                    'site_id' => $data['site_id']
                ]);
                $this->setFlash('success', 'Plot created successfully');
                return $this->redirect('/admin/plots');
            }

            $this->setFlash('error', 'Failed to create plot');
            return $this->redirect('/admin/plots/create');
        } catch (Exception $e) {
            $this->loggingService->error("Plot Store error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to create plot: ' . $e->getMessage());
            return $this->redirect('/admin/plots/create');
        }
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
                           '' as land_title,
                           0 as property_count,
                           0 as developed_area
                    FROM plots p
                    LEFT JOIN sites s ON p.site_id = s.id
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

            $sql .= " ORDER BY p.created_at DESC";

            // Count total
            $countSql = preg_replace('/SELECT .* FROM/', 'SELECT COUNT(*) as total FROM', $sql, 1);
            $countSql = str_replace('s.site_name', '1', $countSql);
            $countSql = str_replace("'' as land_title", '1', $countSql);
            $countSql = str_replace('0 as property_count', '1', $countSql);
            $countSql = str_replace('0 as developed_area', '1', $countSql);
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = ($countStmt->fetch()['total'] ?? 0);

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

            return $this->render('admin/plots/dashboard', $data);
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
            $total = ($countStmt->fetch()['total'] ?? 0);

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

            return $this->render('admin/plots/allocation', $data);
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
            $total = ($countStmt->fetch()['total'] ?? 0);

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

            return $this->render('admin/plots/development', $data);
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

    public function show($id = null)
    {
        if (!$id) { $this->redirect('/admin/plots'); return; }
        $this->data['page_title'] = 'Plot Details';
        $stmt = $this->db->prepare("SELECT p.*, c.name as colony_name, d.name as district_name, s.name as state_name FROM plots p LEFT JOIN colonies c ON p.colony_id = c.id LEFT JOIN districts d ON c.district_id = d.id LEFT JOIN states s ON d.state_id = s.id WHERE p.id = ?");
        $stmt->execute([$id]);
        $this->data['plot'] = $stmt->fetch() ?: [];

        // Price history
        $phStmt = $this->db->prepare("SELECT ph.*, u.name as changed_by_name FROM price_history ph LEFT JOIN users u ON ph.changed_by = u.id WHERE ph.plot_id = ? ORDER BY ph.created_at DESC");
        $phStmt->execute([$id]);
        $this->data['priceHistory'] = $phStmt->fetchAll() ?: [];

        // Related bookings
        $bkStmt = $this->db->prepare("SELECT b.*, u.name as customer_name FROM bookings b LEFT JOIN users u ON b.customer_id = u.id WHERE b.plot_id = ? ORDER BY b.created_at DESC");
        $bkStmt->execute([$id]);
        $this->data['bookings'] = $bkStmt->fetchAll() ?: [];

        // Status history from plot_status_log
        try {
            $histStmt = $this->db->prepare("SELECT * FROM plot_status_log WHERE plot_id = ? ORDER BY created_at DESC");
            $histStmt->execute([$id]);
            $this->data['history'] = $histStmt->fetchAll() ?: [];
        } catch (\Exception $e) {
            $this->data['history'] = [];
        }
        $this->render('admin/plots/show');
    }

    public function edit($id = null)
    {
        if (!$id) { $this->redirect('/admin/plots'); return; }
        $this->data['page_title'] = 'Edit Plot';
        $stmt = $this->db->prepare("SELECT p.*, c.name as colony_name FROM plots p LEFT JOIN colonies c ON p.colony_id = c.id WHERE p.id = ?");
        $stmt->execute([$id]);
        $this->data['plot'] = $stmt->fetch() ?: [];

        // Price history for edit view
        $phStmt = $this->db->prepare("SELECT * FROM price_history WHERE plot_id = ? ORDER BY created_at DESC LIMIT 10");
        $phStmt->execute([$id]);
        $this->data['priceHistory'] = $phStmt->fetchAll() ?: [];

        $this->render('admin/plots/edit');
    }

    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $data = $_POST;

            // Get old values for price change detection
            $old = $this->db->fetchRow("SELECT total_price, price_per_sqft, negotiated_price, status FROM plots WHERE id = ?", [$id]);
            if (!$old) {
                $this->setFlash('error', 'Plot not found');
                return $this->redirect('/admin/plots');
            }

            // Calculate dimension if width and length provided
            $width = floatval($data['width_ft'] ?? 0);
            $length = floatval($data['length_ft'] ?? 0);
            $dimLabel = $data['dimension_label'] ?? '';
            if ($width > 0 && $length > 0 && empty($dimLabel)) {
                $dimLabel = $width . 'x' . $length;
            }
            $areaSqft = floatval($data['area_sqft'] ?? ($width * $length));
            $pricePerSqft = floatval($data['price_per_sqft'] ?? $old['price_per_sqft']);
            $totalPrice = floatval($data['total_price'] ?? ($areaSqft * $pricePerSqft));

            $this->db->execute("UPDATE plots SET 
                plot_number = ?, block = ?, sector = ?, plot_type = ?, 
                area_sqft = ?, area_sqm = ?, width_ft = ?, length_ft = ?, dimension_label = ?,
                frontage_ft = ?, depth_ft = ?, road_width_ft = ?,
                base_price_per_sqft = ?, price_per_sqft = ?, total_price = ?,
                negotiated_price = ?, price_override_reason = ?, status = ?,
                facing = ?, corner_plot = ?, park_facing = ?,
                booking_amount = ?, total_paid = ?, payment_status = ?,
                description = ?
                WHERE id = ?", [
                $data['plot_number'], $data['block'] ?? '', $data['sector'] ?? '', $data['plot_type'] ?? 'residential',
                $areaSqft, floatval($data['area_sqm'] ?? 0), $width, $length, $dimLabel,
                floatval($data['frontage_ft'] ?? 0), floatval($data['depth_ft'] ?? 0), floatval($data['road_width_ft'] ?? 0),
                floatval($data['base_price_per_sqft'] ?? $pricePerSqft), $pricePerSqft, $totalPrice,
                !empty($data['negotiated_price']) ? floatval($data['negotiated_price']) : null,
                $data['price_override_reason'] ?? '', $data['status'] ?? 'available',
                $data['facing'] ?? '', !empty($data['corner_plot']) ? 1 : 0, !empty($data['park_facing']) ? 1 : 0,
                floatval($data['booking_amount'] ?? 0), floatval($data['total_paid'] ?? 0), $data['payment_status'] ?? 'pending',
                $data['description'] ?? '', $id
            ]);

            // Log price change to price_history
            $newTotal = $totalPrice;
            if (floatval($old['total_price']) != $newTotal) {
                $changeType = (!empty($data['negotiated_price']) && floatval($data['negotiated_price']) > 0) ? 'negotiated' : 'override';
                $this->db->insert('price_history', [
                    'plot_id' => $id,
                    'old_price' => $old['total_price'],
                    'new_price' => $newTotal,
                    'old_price_per_sqft' => $old['price_per_sqft'],
                    'new_price_per_sqft' => $pricePerSqft,
                    'change_type' => $changeType,
                    'reason' => $data['price_override_reason'] ?? 'Price updated by admin',
                    'changed_by' => $_SESSION['user_id'] ?? 1,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            // Log status change
            if ($old['status'] != $data['status']) {
                try {
                    $this->db->insert('plot_status_log', [
                        'plot_id' => $id,
                        'old_status' => $old['status'],
                        'new_status' => $data['status'],
                        'changed_by' => $_SESSION['user_id'] ?? 1,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                } catch (\Exception $e) {
                    // status log table may not exist, skip
                            error_log("PlotManagementController.php: " . $e->getMessage());
                }
            }

            $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'plot_updated', [
                'plot_id' => $id,
                'price_changed' => floatval($old['total_price']) != $newTotal
            ]);

            $this->setFlash('success', 'Plot updated successfully');
            return $this->redirect('/admin/plots/show/' . $id);
        } catch (\Exception $e) {
            $this->loggingService->error("Plot Update error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to update plot: ' . $e->getMessage());
            return $this->redirect('/admin/plots/edit/' . $id);
        }
    }

    public function destroy($id)
    {
        try {
            $this->db->execute("UPDATE plots SET is_active = 0 WHERE id = ?", [$id]);
            $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'plot_deactivated', ['plot_id' => $id]);
            $this->setFlash('success', 'Plot deactivated successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to deactivate plot: ' . $e->getMessage());
        }
        $this->redirect('/admin/plots');
    }

    public function checkAvailability()
    {
        header('Content-Type: application/json');
        $plotId = $_GET['id'] ?? 0;
        if ($plotId) {
            $plot = $this->db->fetchRow("SELECT id, status FROM plots WHERE id = ? AND is_active = 1", [$plotId]);
            echo json_encode(['available' => $plot && $plot['status'] === 'available', 'status' => $plot['status'] ?? 'not_found']);
        } else {
            echo json_encode(['available' => false, 'error' => 'No plot ID provided']);
        }
        exit;
    }

    public function updateStatus($id)
    {
        header('Content-Type: application/json');
        $status = $_POST['status'] ?? '';
        if (!in_array($status, ['available', 'booked', 'sold', 'hold', 'reserved'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
            exit;
        }
        try {
            $old = $this->db->fetchOne("SELECT status FROM plots WHERE id = ?", [$id]);
            $this->db->execute("UPDATE plots SET status = ? WHERE id = ?", [$status, $id]);
            try {
                $this->db->insert('plot_status_log', [
                    'plot_id' => $id, 'old_status' => $old, 'new_status' => $status,
                    'changed_by' => $_SESSION['user_id'] ?? 1, 'created_at' => date('Y-m-d H:i:s'),
                ]);
            } catch (\Exception $e) { error_log('PlotManagementController updateStatus log: ' . $e->getMessage()); }
            echo json_encode(['success' => true, 'message' => 'Status updated to ' . $status]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Bulk price update for a colony/block
     */
    public function bulkPriceUpdate()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid method', 400);
        }
        try {
            $colonyId = intval($_POST['colony_id'] ?? 0);
            $block = $_POST['block'] ?? '';
            $newPps = floatval($_POST['new_price_per_sqft'] ?? 0);
            $reason = $_POST['reason'] ?? 'Bulk price update';

            if (!$colonyId || $newPps <= 0) {
                return $this->jsonError('Colony and valid price required', 400);
            }

            $where = "colony_id = ? AND is_active = 1";
            $params = [$colonyId];
            if ($block) { $where .= " AND block = ?"; $params[] = $block; }

            $plots = $this->db->fetchAll("SELECT id, total_price, price_per_sqft, area_sqft FROM plots WHERE $where", $params);
            $count = 0;
            foreach ($plots as $p) {
                $oldTotal = $p['total_price'];
                $newTotal = $p['area_sqft'] * $newPps;
                $this->db->execute("UPDATE plots SET price_per_sqft = ?, total_price = ?, base_price_per_sqft = COALESCE(base_price_per_sqft, price_per_sqft) WHERE id = ?", [$newPps, $newTotal, $p['id']]);
                $this->db->insert('price_history', [
                    'plot_id' => $p['id'], 'old_price' => $oldTotal, 'new_price' => $newTotal,
                    'old_price_per_sqft' => $p['price_per_sqft'], 'new_price_per_sqft' => $newPps,
                    'change_type' => 'bulk_update', 'reason' => $reason,
                    'changed_by' => $_SESSION['user_id'] ?? 1, 'created_at' => date('Y-m-d H:i:s'),
                ]);
                $count++;
            }

            echo json_encode(['success' => true, 'message' => "$count plots updated", 'count' => $count]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * API: Price history for a plot (JSON)
     */
    public function apiPriceHistory($plotId)
    {
        header('Content-Type: application/json');
        $history = $this->db->fetchAll("SELECT * FROM price_history WHERE plot_id = ? ORDER BY created_at DESC", [$plotId]);
        echo json_encode(['success' => true, 'data' => $history]);
        exit;
    }

    /**
     * Show plot booking form (GET) or process booking (POST)
     */
    public function book($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->storeBooking($id);
        }
        try {
            $plot = $this->db->fetchRow("SELECT p.*, c.name as colony_name, d.name as district_name, s.name as state_name,
                u.name as current_owner_name, u.email as current_owner_email, u.phone as current_owner_phone
                FROM plots p
                LEFT JOIN colonies c ON p.colony_id = c.id
                LEFT JOIN districts d ON c.district_id = d.id
                LEFT JOIN states s ON d.state_id = s.id
                LEFT JOIN users u ON p.customer_id = u.id
                WHERE p.id = ?", [$id]);
            if (!$plot) {
                $this->setFlash('error', 'Plot not found');
                return $this->redirect('/admin/plots');
            }
            $customers = $this->db->fetchAll("SELECT id, name, email, phone FROM users WHERE role IN ('customer','agent','associate') ORDER BY name");
            $this->render('admin/plots/book', [
                'page_title' => 'Book Plot',
                'plot' => $plot,
                'customers' => $customers ?: [],
            ]);
        } catch (\Exception $e) {
            $this->loggingService->error("Book plot view error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load booking page');
            $this->redirect('/admin/plots');
        }
    }

    /**
     * Store a new booking from admin
     */
    public function storeBooking($id)
    {
        $this->requireAdmin();
        try {
            $plot = $this->db->fetchRow("SELECT * FROM plots WHERE id = ?", [$id]);
            if (!$plot) {
                return $this->jsonError('Plot not found', 404);
            }
            if (!in_array($plot['status'], ['available', 'hold'])) {
                return $this->jsonError('Plot is not available for booking (status: ' . $plot['status'] . ')', 400);
            }

            $customerId = intval($_POST['customer_id'] ?? 0);
            $tokenAmount = floatval($_POST['token_amount'] ?? 0);
            $bookingDate = $_POST['booking_date'] ?? date('Y-m-d');
            $possessionDate = $_POST['possession_date'] ?? null;
            $negotiatedPrice = floatval($_POST['negotiated_price'] ?? $plot['total_price']);
            $paymentPlan = $_POST['payment_plan'] ?? 'Full Payment';
            $notes = $_POST['notes'] ?? '';

            if (!$customerId) {
                return $this->jsonError('Customer is required', 400);
            }

            $this->db->beginTransaction();

            try {
                // Create booking in bookings table
                $bookingId = $this->db->insert('bookings', [
                    'plot_id' => $id,
                    'colony_id' => $plot['colony_id'],
                    'customer_id' => $customerId,
                    'booking_date' => $bookingDate,
                    'status' => 'pending',
                    'payment_status' => 'pending',
                    'total_amount' => $negotiatedPrice,
                    'amount' => $tokenAmount,
                    'negotiated_price' => $negotiatedPrice,
                    'notes' => $notes . "\nPayment Plan: " . $paymentPlan,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

                // Also create in plot_allocations
                try {
                    $this->db->insert('plot_allocations', [
                        'plot_id' => $id,
                        'customer_id' => $customerId,
                        'allocation_date' => $bookingDate,
                        'total_plot_value' => $negotiatedPrice,
                        'amount_paid' => $tokenAmount,
                        'amount_pending' => $negotiatedPrice - $tokenAmount,
                        'status' => 'booked',
                        'notes' => $notes,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                } catch (\Exception $e) {
                    $this->loggingService->error("plot_allocations insert skipped: " . $e->getMessage());
                }

                // Update plot status to booked
                $this->db->execute("UPDATE plots SET status = 'booked', customer_id = ?, booking_date = ?, negotiated_price = ?, total_paid = ?, updated_at = NOW() WHERE id = ?",
                    [$customerId, $bookingDate, $negotiatedPrice, $tokenAmount, $id]);

                // Log status change
                try {
                    $this->db->insert('plot_status_log', [
                        'plot_id' => $id, 'old_status' => $plot['status'], 'new_status' => 'booked',
                        'changed_by' => $_SESSION['user_id'] ?? 1, 'created_at' => date('Y-m-d H:i:s'),
                    ]);
                } catch (\Exception $e) {
                            error_log("PlotManagementController.php: " . $e->getMessage());
                }

                $this->db->commit();

                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'plot_booked', [
                    'plot_id' => $id, 'customer_id' => $customerId, 'booking_id' => $bookingId
                ]);

                $this->setFlash('success', 'Plot booked successfully. Booking #' . $bookingId);
                return $this->redirect('/admin/plots/' . $id);
            } catch (\Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            $this->loggingService->error("Store Booking error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to create booking: ' . $e->getMessage());
            return $this->redirect('/admin/plots/' . $id . '/book');
        }
    }

    /**
     * Show plot transfer form (GET) or process transfer (POST)
     */
    public function transfer($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->transferPlot($id);
        }
        try {
            $plot = $this->db->fetchRow("SELECT p.*, c.name as colony_name, d.name as district_name, s.name as state_name,
                u.name as current_owner_name, u.email as current_owner_email, u.phone as current_owner_phone
                FROM plots p
                LEFT JOIN colonies c ON p.colony_id = c.id
                LEFT JOIN districts d ON c.district_id = d.id
                LEFT JOIN states s ON d.state_id = s.id
                LEFT JOIN users u ON p.customer_id = u.id
                WHERE p.id = ?", [$id]);
            if (!$plot) {
                $this->setFlash('error', 'Plot not found');
                return $this->redirect('/admin/plots');
            }
            $customers = $this->db->fetchAll("SELECT id, name, email, phone FROM users WHERE role IN ('customer','agent','associate') ORDER BY name");
            $this->render('admin/plots/transfer', [
                'page_title' => 'Plot Transfer',
                'plot' => $plot,
                'customers' => $customers ?: [],
            ]);
        } catch (\Exception $e) {
            $this->loggingService->error("Transfer plot view error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load transfer page');
            $this->redirect('/admin/plots');
        }
    }

    /**
     * Process plot ownership transfer
     */
    public function transferPlot($id)
    {
        $this->requireAdmin();
        try {
            $plot = $this->db->fetchRow("SELECT * FROM plots WHERE id = ?", [$id]);
            if (!$plot) {
                return $this->jsonError('Plot not found', 404);
            }

            $newOwnerId = intval($_POST['new_owner_id'] ?? 0);
            $transferReason = $_POST['transfer_reason'] ?? '';
            $transferDate = $_POST['transfer_date'] ?? date('Y-m-d');
            $transferAmount = floatval($_POST['transfer_amount'] ?? $plot['total_price']);
            $transferFee = floatval($_POST['transfer_fee'] ?? 0);
            $newStatus = $_POST['new_status'] ?? 'sold';
            $documentRef = $_POST['document_ref'] ?? '';
            $remarks = $_POST['remarks'] ?? '';

            if (!$newOwnerId) {
                return $this->jsonError('New owner is required', 400);
            }
            if (!$transferReason) {
                return $this->jsonError('Transfer reason is required', 400);
            }

            $this->db->beginTransaction();

            try {
                // Ensure plot_transfers table exists
                try {
                    $this->db->execute("ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                } catch (\Exception $e) {
                            error_log("PlotManagementController.php: " . $e->getMessage());
                }

                // Insert transfer record
                $this->db->insert('plot_transfers', [
                    'plot_id' => $id,
                    'from_customer_id' => $plot['customer_id'],
                    'to_customer_id' => $newOwnerId,
                    'transfer_date' => $transferDate,
                    'transfer_reason' => $transferReason,
                    'transfer_amount' => $transferAmount,
                    'transfer_fee' => $transferFee,
                    'old_status' => $plot['status'],
                    'new_status' => $newStatus,
                    'document_ref' => $documentRef,
                    'remarks' => $remarks,
                    'processed_by' => $_SESSION['user_id'] ?? 1,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);

                // Update plot ownership
                $this->db->execute("UPDATE plots SET customer_id = ?, status = ?, sale_date = ?, updated_at = NOW() WHERE id = ?",
                    [$newOwnerId, $newStatus, $transferDate, $id]);

                // Log status change
                try {
                    $this->db->insert('plot_status_log', [
                        'plot_id' => $id, 'old_status' => $plot['status'], 'new_status' => $newStatus,
                        'changed_by' => $_SESSION['user_id'] ?? 1, 'created_at' => date('Y-m-d H:i:s'),
                    ]);
                } catch (\Exception $e) {
                            error_log("PlotManagementController.php: " . $e->getMessage());
                }

                $this->db->commit();

                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'plot_transferred', [
                    'plot_id' => $id, 'from' => $plot['customer_id'], 'to' => $newOwnerId
                ]);

                $this->setFlash('success', 'Plot transferred successfully');
                return $this->redirect('/admin/plots/' . $id);
            } catch (\Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            $this->loggingService->error("Transfer Plot error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to transfer plot: ' . $e->getMessage());
            return $this->redirect('/admin/plots/' . $id . '/transfer');
        }
    }

    /**
     * Show plot availability dashboard
     */
    public function availability()
    {
        $this->requireAdmin();
        try {
            $colonyFilter = intval($_GET['colony_id'] ?? 0);
            $statusFilter = $_GET['status'] ?? '';

            $where = "WHERE p.is_active = 1 OR p.is_active IS NULL";
            $params = [];
            if ($colonyFilter > 0) {
                $where .= " AND p.colony_id = ?";
                $params[] = $colonyFilter;
            }
            if (!empty($statusFilter)) {
                $where .= " AND p.status = ?";
                $params[] = $statusFilter;
            }

            $plots = $this->db->fetchAll("SELECT p.*, c.name as colony_name
                FROM plots p
                LEFT JOIN colonies c ON p.colony_id = c.id
                $where
                ORDER BY c.name, p.block, p.plot_number", $params) ?: [];

            // Stats
            $allPlots = $this->db->fetchAll("SELECT status FROM plots WHERE is_active = 1 OR is_active IS NULL") ?: [];
            $stats = ['available' => 0, 'booked' => 0, 'sold' => 0, 'hold' => 0, 'reserved' => 0, 'total' => count($allPlots)];
            foreach ($allPlots as $p) {
                $s = $p['status'] ?? 'available';
                if (isset($stats[$s])) $stats[$s]++;
            }

            $colonies = $this->db->fetchAll("SELECT id, name FROM colonies ORDER BY name") ?: [];

            $this->render('admin/plots/availability', [
                'page_title' => 'Plot Availability',
                'plots' => $plots,
                'stats' => $stats,
                'colonies' => $colonies,
            ]);
        } catch (\Exception $e) {
            $this->loggingService->error("Availability view error: " . $e->getMessage());
            $this->render('admin/plots/availability', [
                'page_title' => 'Plot Availability',
                'plots' => [],
                'stats' => ['available' => 0, 'booked' => 0, 'sold' => 0, 'hold' => 0, 'total' => 0],
                'colonies' => [],
                'error' => 'Failed to load availability data',
            ]);
        }
    }

    /**
     * JSON endpoint returning plot availability data for AJAX
     */
    public function availabilityData()
    {
        header('Content-Type: application/json');
        try {
            $colonyId = intval($_GET['colony_id'] ?? 0);

            $where = "WHERE (p.is_active = 1 OR p.is_active IS NULL)";
            $params = [];
            if ($colonyId > 0) {
                $where .= " AND p.colony_id = ?";
                $params[] = $colonyId;
            }

            $plots = $this->db->fetchAll("SELECT p.id, p.plot_number, p.block, p.dimension_label, p.width_ft, p.length_ft,
                p.area_sqft, p.price_per_sqft, p.total_price, p.status, p.facing, p.corner_plot, p.colony_id,
                c.name as colony_name
                FROM plots p
                LEFT JOIN colonies c ON p.colony_id = c.id
                $where
                ORDER BY c.name, p.block, p.plot_number", $params) ?: [];

            echo json_encode(['success' => true, 'data' => $plots]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function map()
    {
        try {
            $colonies = $this->db->fetchAll("SELECT id, name FROM colonies ORDER BY name") ?: [];
            $all_plots = $this->db->fetchAll("SELECT p.id, p.plot_number, p.block, p.width_ft, p.length_ft,
                p.area_sqft, p.total_price, p.status, p.facing, p.colony_id, c.name as colony_name
                FROM plots p
                LEFT JOIN colonies c ON p.colony_id = c.id
                ORDER BY c.name, p.plot_number") ?: [];

            return $this->render('admin/plots/map', [
                'colonies' => $colonies,
                'all_plots' => $all_plots,
                'page_title' => 'Plot Layout Map'
            ]);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error loading plot map: ' . $e->getMessage());
            $this->redirect('/admin/plots');
        }
    }
}
