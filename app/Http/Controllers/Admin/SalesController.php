<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\CoreFunctionsServiceCustom;
use App\Services\LoggingService;
use App\Core\Database;
use Exception;

/**
 * Sales Controller - Custom MVC Implementation
 * Handles sales management operations in the Admin panel
 */
class SalesController extends AdminController
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
     * Display sales list
     */
    public function index()
    {
        try {
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            $associateId = $_GET['associate_id'] ?? '';
            $page = (int)($_GET['page'] ?? 1);
            $perPage = (int)($_GET['per_page'] ?? 20);

            $offset = ($page - 1) * $perPage;

            // Build query
            $sql = "SELECT s.*, 
                           u.name as associate_name,
                           u.email as associate_email,
                           pr.title as property_title,
                           pr.price as property_price,
                           c.name as customer_name,
                           c.email as customer_email,
                           b.booking_number
                    FROM sales s
                    LEFT JOIN users u ON s.associate_id = u.id
                    LEFT JOIN properties pr ON s.property_id = pr.id
                    LEFT JOIN users c ON s.customer_id = c.id
                    LEFT JOIN bookings b ON s.booking_id = b.id
                    WHERE 1=1";
            $params = [];

            // Apply filters
            if (!empty($search)) {
                $sql .= " AND (s.sale_number LIKE ? OR u.name LIKE ? OR c.name LIKE ? OR pr.title LIKE ?)";
                $searchParam = '%' . $search . '%';
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if (!empty($status)) {
                $sql .= " AND s.status = ?";
                $params[] = $status;
            }

            if (!empty($associateId)) {
                $sql .= " AND s.associate_id = ?";
                $params[] = $associateId;
            }

            $sql .= " ORDER BY s.created_at DESC";

            // Count total
            $countSql = str_replace("SELECT s.*, u.name as associate_name, u.email as associate_email, pr.title as property_title, pr.price as property_price, c.name as customer_name, c.email as customer_email, b.booking_number", "SELECT COUNT(DISTINCT s.id) as total", $sql);
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetch()['total'];

            // Apply pagination
            $sql .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $perPage;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $sales = $stmt->fetchAll();

            // Get associates for filter
            $associates = $this->db->fetchAll("SELECT id, name FROM users WHERE role = 'associate' ORDER BY name");

            $data = [
                'page_title' => 'Sales Management - APS Dream Home',
                'active_page' => 'sales',
                'sales' => $sales,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
                'filters' => [
                    'search' => $search,
                    'status' => $status,
                    'associate_id' => $associateId
                ],
                'associates' => $associates
            ];

            return $this->render('admin/sales/index', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Sales Index error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load sales');
            return $this->redirect('admin/dashboard');
        }
    }

    /**
     * Show the form for creating a new sale
     */
    public function create()
    {
        try {
            // Fetch associates with their names from users table
            $associates = $this->db->fetchAll("SELECT id, name FROM users WHERE role = 'associate' ORDER BY name");

            // Get available properties
            $properties = $this->db->fetchAll("SELECT id, title, price, location FROM properties WHERE status = 'available' ORDER BY title");

            // Get customers
            $customers = $this->db->fetchAll("SELECT id, name, email FROM users WHERE role = 'customer' ORDER BY name");

            $data = [
                'page_title' => 'Create Sale - APS Dream Home',
                'active_page' => 'sales',
                'associates' => $associates,
                'properties' => $properties,
                'customers' => $customers
            ];

            return $this->render('admin/sales/create', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Sales Create error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load sales form');
            return $this->redirect('admin/sales');
        }
    }

    /**
     * Store a newly created sale
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $data = $_POST;

            // Validate required fields
            $required = ['property_id', 'customer_id', 'associate_id', 'sale_amount'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return $this->jsonError(ucfirst(str_replace('_', ' ', $field)) . ' is required', 400);
                }
            }

            // Validate numeric fields
            $saleAmount = (float)$data['sale_amount'];
            $commissionAmount = (float)($data['commission_amount'] ?? 0);

            if ($saleAmount <= 0) {
                return $this->jsonError('Sale amount must be greater than 0', 400);
            }

            // Generate unique sale number
            $saleNumber = 'SALE' . date('YmdHis') . rand(1000, 9999);

            // Insert sale
            $sql = "INSERT INTO sales 
                    (sale_number, property_id, customer_id, associate_id, sale_amount, commission_amount,
                     commission_percentage, sale_date, status, notes, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $saleNumber,
                (int)$data['property_id'],
                (int)$data['customer_id'],
                (int)$data['associate_id'],
                $saleAmount,
                $commissionAmount,
                (float)($data['commission_percentage'] ?? 0),
                $data['sale_date'] ?? date('Y-m-d'),
                CoreFunctionsServiceCustom::validateInput($data['status'] ?? 'pending', 'string'),
                CoreFunctionsServiceCustom::validateInput($data['notes'] ?? '', 'string')
            ]);

            if ($result) {
                $saleId = $this->db->lastInsertId();

                // Update property status
                $sql = "UPDATE properties SET status = 'sold', updated_at = NOW() WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([(int)$data['property_id']]);

                // Create commission record if applicable
                if ($commissionAmount > 0) {
                    $sql = "INSERT INTO mlm_commission_ledger 
                            (associate_id, commission_type, amount, source_type, source_id, status, created_at)
                            VALUES (?, 'sale_commission', ?, 'sale', ?, 'pending', NOW())";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([
                        (int)$data['associate_id'],
                        $commissionAmount,
                        $saleId
                    ]);
                }

                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'sale_created', [
                    'sale_id' => $saleId,
                    'sale_number' => $saleNumber,
                    'property_id' => $data['property_id'],
                    'customer_id' => $data['customer_id'],
                    'associate_id' => $data['associate_id']
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Sale created successfully',
                    'sale_id' => $saleId,
                    'sale_number' => $saleNumber
                ]);
            }

            return $this->jsonError('Failed to create sale', 500);
        } catch (Exception $e) {
            $this->loggingService->error("Sales Store error: " . $e->getMessage());
            return $this->jsonError('Failed to create sale', 500);
        }
    }

    /**
     * Display the specified sale
     */
    public function show($id)
    {
        try {
            $saleId = intval($id);
            if ($saleId <= 0) {
                $this->setFlash('error', 'Invalid sale ID');
                return $this->redirect('admin/sales');
            }

            // Get sale details
            $sql = "SELECT s.*, 
                           u.name as associate_name,
                           u.email as associate_email,
                           pr.title as property_title,
                           pr.price as property_price,
                           pr.location as property_location,
                           c.name as customer_name,
                           c.email as customer_email,
                           c.phone as customer_phone,
                           b.booking_number
                    FROM sales s
                    LEFT JOIN users u ON s.associate_id = u.id
                    LEFT JOIN properties pr ON s.property_id = pr.id
                    LEFT JOIN users c ON s.customer_id = c.id
                    LEFT JOIN bookings b ON s.booking_id = b.id
                    WHERE s.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$saleId]);
            $sale = $stmt->fetch();

            if (!$sale) {
                $this->setFlash('error', 'Sale not found');
                return $this->redirect('admin/sales');
            }

            // Get commission details
            $sql = "SELECT * FROM mlm_commission_ledger 
                    WHERE source_type = 'sale' AND source_id = ? 
                    ORDER BY created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$saleId]);
            $commissions = $stmt->fetchAll();

            $data = [
                'page_title' => 'Sale Details - APS Dream Home',
                'active_page' => 'sales',
                'sale' => $sale,
                'commissions' => $commissions
            ];

            return $this->render('admin/sales/show', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Sales Show error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load sale details');
            return $this->redirect('admin/sales');
        }
    }

    /**
     * Show the form for editing the specified sale
     */
    public function edit($id)
    {
        try {
            $saleId = intval($id);
            if ($saleId <= 0) {
                $this->setFlash('error', 'Invalid sale ID');
                return $this->redirect('admin/sales');
            }

            // Get sale details
            $sql = "SELECT * FROM sales WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$saleId]);
            $sale = $stmt->fetch();

            if (!$sale) {
                $this->setFlash('error', 'Sale not found');
                return $this->redirect('admin/sales');
            }

            // Get dropdown options
            $associates = $this->db->fetchAll("SELECT id, name FROM users WHERE role = 'associate' ORDER BY name");
            $properties = $this->db->fetchAll("SELECT id, title, price FROM properties ORDER BY title");
            $customers = $this->db->fetchAll("SELECT id, name, email FROM users WHERE role = 'customer' ORDER BY name");

            $data = [
                'page_title' => 'Edit Sale - APS Dream Home',
                'active_page' => 'sales',
                'sale' => $sale,
                'associates' => $associates,
                'properties' => $properties,
                'customers' => $customers
            ];

            return $this->render('admin/sales/edit', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Sales Edit error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load sales form');
            return $this->redirect('admin/sales');
        }
    }

    /**
     * Update the specified sale
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $saleId = intval($id);
            if ($saleId <= 0) {
                return $this->jsonError('Invalid sale ID', 400);
            }

            $data = $_POST;

            // Check if sale exists
            $sql = "SELECT * FROM sales WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$saleId]);
            $sale = $stmt->fetch();

            if (!$sale) {
                return $this->jsonError('Sale not found', 404);
            }

            // Build update query
            $updateFields = [];
            $updateValues = [];

            if (isset($data['property_id'])) {
                $updateFields[] = "property_id = ?";
                $updateValues[] = (int)$data['property_id'];
            }

            if (isset($data['customer_id'])) {
                $updateFields[] = "customer_id = ?";
                $updateValues[] = (int)$data['customer_id'];
            }

            if (isset($data['associate_id'])) {
                $updateFields[] = "associate_id = ?";
                $updateValues[] = (int)$data['associate_id'];
            }

            if (isset($data['sale_amount'])) {
                $saleAmount = (float)$data['sale_amount'];
                if ($saleAmount <= 0) {
                    return $this->jsonError('Sale amount must be greater than 0', 400);
                }
                $updateFields[] = "sale_amount = ?";
                $updateValues[] = $saleAmount;
            }

            if (isset($data['commission_amount'])) {
                $updateFields[] = "commission_amount = ?";
                $updateValues[] = (float)$data['commission_amount'];
            }

            if (isset($data['commission_percentage'])) {
                $updateFields[] = "commission_percentage = ?";
                $updateValues[] = (float)$data['commission_percentage'];
            }

            if (isset($data['sale_date'])) {
                $updateFields[] = "sale_date = ?";
                $updateValues[] = $data['sale_date'];
            }

            if (isset($data['status'])) {
                $validStatuses = ['pending', 'confirmed', 'completed', 'cancelled'];
                if (in_array($data['status'], $validStatuses)) {
                    $updateFields[] = "status = ?";
                    $updateValues[] = $data['status'];
                }
            }

            if (isset($data['notes'])) {
                $updateFields[] = "notes = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['notes'], 'string');
            }

            if (empty($updateFields)) {
                return $this->jsonError('No fields to update', 400);
            }

            $updateFields[] = "updated_at = NOW()";
            $updateValues[] = $saleId;

            $sql = "UPDATE sales SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($updateValues);

            if ($result) {
                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'sale_updated', [
                    'sale_id' => $saleId,
                    'changes' => $data
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Sale updated successfully'
                ]);
            }

            return $this->jsonError('Failed to update sale', 500);
        } catch (Exception $e) {
            $this->loggingService->error("Sales Update error: " . $e->getMessage());
            return $this->jsonError('Failed to update sale', 500);
        }
    }

    /**
     * Remove the specified sale
     */
    public function destroy($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $saleId = intval($id);
            if ($saleId <= 0) {
                return $this->jsonError('Invalid sale ID', 400);
            }

            // Check if sale exists
            $sql = "SELECT * FROM sales WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$saleId]);
            $sale = $stmt->fetch();

            if (!$sale) {
                return $this->jsonError('Sale not found', 404);
            }

            $this->db->beginTransaction();

            try {
                // Update property status back to available
                $sql = "UPDATE properties SET status = 'available', updated_at = NOW() WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$sale['property_id']]);

                // Delete related commissions
                $sql = "DELETE FROM mlm_commission_ledger WHERE source_type = 'sale' AND source_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$saleId]);

                // Delete sale
                $sql = "DELETE FROM sales WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$saleId]);

                $this->db->commit();

                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'sale_deleted', [
                    'sale_id' => $saleId,
                    'sale_number' => $sale['sale_number']
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Sale deleted successfully'
                ]);
            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            $this->loggingService->error("Sales Destroy error: " . $e->getMessage());
            return $this->jsonError('Failed to delete sale', 500);
        }
    }

    /**
     * Display sales analytics
     */
    public function analytics()
    {
        try {
            $data = [
                'page_title' => 'Sales Analytics - APS Dream Home',
                'active_page' => 'sales',
                'analytics_data' => $this->getSalesAnalytics()
            ];

            return $this->render('admin/sales/analytics', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Sales Analytics error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load sales analytics');
            return $this->redirect('admin/sales');
        }
    }

    /**
     * Get sales analytics
     */
    private function getSalesAnalytics(): array
    {
        try {
            $analytics = [];

            // Sales trends (last 30 days)
            $sql = "SELECT DATE(sale_date) as date, COUNT(*) as count, COALESCE(SUM(sale_amount), 0) as total
                    FROM sales
                    WHERE sale_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY DATE(sale_date)
                    ORDER BY date DESC";
            $analytics['trends'] = $this->db->fetchAll($sql) ?: [];

            // Top performing associates
            $sql = "SELECT u.name, u.email, COUNT(s.id) as sale_count, COALESCE(SUM(s.sale_amount), 0) as total_sales
                    FROM sales s
                    JOIN users u ON s.associate_id = u.id
                    GROUP BY u.id
                    ORDER BY total_sales DESC
                    LIMIT 10";
            $analytics['top_associates'] = $this->db->fetchAll($sql) ?: [];

            // Sales by status
            $sql = "SELECT status, COUNT(*) as count, COALESCE(SUM(sale_amount), 0) as total
                    FROM sales
                    GROUP BY status
                    ORDER BY count DESC";
            $analytics['by_status'] = $this->db->fetchAll($sql) ?: [];

            // Commission analytics
            $sql = "SELECT u.name as associate_name, 
                           COUNT(mcl.id) as commission_count,
                           COALESCE(SUM(mcl.amount), 0) as total_commissions
                    FROM mlm_commission_ledger mcl
                    JOIN users u ON mcl.associate_id = u.id
                    WHERE mcl.commission_type = 'sale_commission'
                    GROUP BY u.id
                    ORDER BY total_commissions DESC
                    LIMIT 10";
            $analytics['commission_analytics'] = $this->db->fetchAll($sql) ?: [];

            return $analytics;
        } catch (Exception $e) {
            $this->loggingService->error("Get Sales Analytics error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get sales statistics
     */
    public function getStats()
    {
        try {
            $stats = [];

            // Total sales
            $sql = "SELECT COUNT(*) as total, COALESCE(SUM(sale_amount), 0) as total_amount FROM sales";
            $result = $this->db->fetchOne($sql);
            $stats['total_sales'] = (int)($result['total'] ?? 0);
            $stats['total_amount'] = (float)($result['total_amount'] ?? 0);

            // This month's sales
            $sql = "SELECT COUNT(*) as total, COALESCE(SUM(sale_amount), 0) as total_amount
                    FROM sales 
                    WHERE MONTH(sale_date) = MONTH(CURRENT_DATE) 
                    AND YEAR(sale_date) = YEAR(CURRENT_DATE)";
            $result = $this->db->fetchOne($sql);
            $stats['monthly_sales'] = (int)($result['total'] ?? 0);
            $stats['monthly_amount'] = (float)($result['total_amount'] ?? 0);

            // Pending sales
            $sql = "SELECT COUNT(*) as total FROM sales WHERE status = 'pending'";
            $result = $this->db->fetchOne($sql);
            $stats['pending_sales'] = (int)($result['total'] ?? 0);

            // Completed sales
            $sql = "SELECT COUNT(*) as total FROM sales WHERE status = 'completed'";
            $result = $this->db->fetchOne($sql);
            $stats['completed_sales'] = (int)($result['total'] ?? 0);

            return $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            $this->loggingService->error("Get Sales Stats error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch sales stats'
            ], 500);
        }
    }
}
