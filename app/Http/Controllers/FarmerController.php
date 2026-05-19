<?php

namespace App\Http\Controllers;

use App\Services\FarmerService;

/**
 * Controller for Farmer Management operations
 */
class FarmerController extends BaseController
{
    private FarmerService $farmerService;

    public function __construct(FarmerService $farmerService = null)
    {
        parent::__construct();
        $this->farmerService = $farmerService ?? new FarmerService();
    }

    /**
     * Get all farmers with filtering and pagination
     */
    public function index(): void
    {
        try {
            if (!$this->farmerService) {
                $this->jsonResponse(['success' => false, 'message' => 'Farmer service unavailable'], 500);
                return;
            }
            $filters = [
                'status' => isset($_REQUEST['status']) ? $_REQUEST['status'] : null,
                'district' => isset($_REQUEST['district']) ? $_REQUEST['district'] : null,
                'state' => isset($_REQUEST['state']) ? $_REQUEST['state'] : null,
                'search' => isset($_REQUEST['search']) ? $_REQUEST['search'] : null
            ];

            $perPage = (int) (isset($_REQUEST['per_page']) ? $_REQUEST['per_page'] : 20);
            $farmers = $this->farmerService->getAllFarmers($filters, $perPage);

            $this->jsonResponse([
                'success' => true,
                'data' => $farmers
            ]);
        } catch (\Throwable $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to retrieve farmers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific farmer by ID
     */
    public function show(int $id): void
    {
        try {
            $farmer = $this->farmerService->getFarmer($id);

            if (!$farmer) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Farmer not found'
                ], 404);
            }

            $this->jsonResponse([
                'success' => true,
                'data' => $farmer
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to retrieve farmer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new farmer
     */
    public function store(): void
    {
        try {
            $data = [
                'name' => isset($_REQUEST['name']) ? $_REQUEST['name'] : null,
                'email' => isset($_REQUEST['email']) ? $_REQUEST['email'] : null,
                'phone' => isset($_REQUEST['phone']) ? $_REQUEST['phone'] : null,
                'address' => isset($_REQUEST['address']) ? $_REQUEST['address'] : null,
                'district' => isset($_REQUEST['district']) ? $_REQUEST['district'] : null,
                'state' => isset($_REQUEST['state']) ? $_REQUEST['state'] : null,
                'farm_size' => isset($_REQUEST['farm_size']) ? $_REQUEST['farm_size'] : null,
                'crop_type' => isset($_REQUEST['crop_type']) ? $_REQUEST['crop_type'] : null,
                'status' => 'active'
            ];

            $farmer = $this->farmerService->createFarmer($data);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Farmer created successfully',
                'data' => $farmer
            ], 201);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to create farmer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update farmer
     */
    public function update(int $id): void
    {
        try {
            $data = [
                'name' => isset($_REQUEST['name']) ? $_REQUEST['name'] : null,
                'email' => isset($_REQUEST['email']) ? $_REQUEST['email'] : null,
                'phone' => isset($_REQUEST['phone']) ? $_REQUEST['phone'] : null,
                'address' => isset($_REQUEST['address']) ? $_REQUEST['address'] : null,
                'district' => isset($_REQUEST['district']) ? $_REQUEST['district'] : null,
                'state' => isset($_REQUEST['state']) ? $_REQUEST['state'] : null,
                'farm_size' => isset($_REQUEST['farm_size']) ? $_REQUEST['farm_size'] : null,
                'crop_type' => isset($_REQUEST['crop_type']) ? $_REQUEST['crop_type'] : null,
                'status' => isset($_REQUEST['status']) ? $_REQUEST['status'] : null
            ];

            $farmer = $this->farmerService->updateFarmer($id, $data);

            if (!$farmer) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Farmer not found'
                ], 404);
            }

            $this->jsonResponse([
                'success' => true,
                'message' => 'Farmer updated successfully',
                'data' => $farmer
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to update farmer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete farmer
     */
    public function destroy(int $id): void
    {
        try {
            $success = $this->farmerService->deleteFarmer($id);

            if (!$success) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Farmer not found'
                ], 404);
            }

            $this->jsonResponse([
                'success' => true,
                'message' => 'Farmer deleted successfully'
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to delete farmer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get farmer statistics
     */
    public function statistics(): void
    {
        try {
            $stats = $this->farmerService->getStatistics();

            $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search farmers
     */
    public function search(): void
    {
        try {
            $query = isset($_REQUEST['query']) ? $_REQUEST['query'] : '';
            $filters = [
                'district' => isset($_REQUEST['district']) ? $_REQUEST['district'] : null,
                'state' => isset($_REQUEST['state']) ? $_REQUEST['state'] : null,
                'crop_type' => isset($_REQUEST['crop_type']) ? $_REQUEST['crop_type'] : null,
                'status' => isset($_REQUEST['status']) ? $_REQUEST['status'] : null
            ];

            if (!$this->farmerService) {
                $this->jsonResponse(['success' => false, 'message' => 'Farmer service unavailable'], 500);
                return;
            }

            $results = $this->farmerService->searchFarmers($query, $filters);

            $this->jsonResponse([
                'success' => true,
                'data' => $results
            ]);
        } catch (\Throwable $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to search farmers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk operations
     */
    public function bulkOperation(): void
    {
        try {
            $operation = isset($_REQUEST['operation']) ? $_REQUEST['operation'] : null;
            $farmerIds = isset($_REQUEST['farmer_ids']) ? $_REQUEST['farmer_ids'] : [];

            $results = $this->farmerService->bulkOperation($operation, $farmerIds);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Bulk operation completed',
                'data' => $results
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to perform bulk operation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ===== MISSING METHODS FOR ROUTE COMPATIBILITY =====

    public function landHoldings($farmerId)
    {
        header('Content-Type: application/json');
        try {
            $db = \App\Core\Database\Database::getInstance();
            $holdings = $db->fetchAll("SELECT * FROM farmer_land_holdings WHERE farmer_id = ?", [$farmerId]);
            echo json_encode(['success' => true, 'data' => $holdings ?? []]);
        } catch (\Exception $e) {
            echo json_encode(['success' => true, 'data' => [], 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function addLandHolding()
    {
        header('Content-Type: application/json');
        try {
            $data = $_POST ?: json_decode(file_get_contents('php://input'), true) ?: [];
            $db = \App\Core\Database\Database::getInstance();
            $db->insert('farmer_land_holdings', $data);
            echo json_encode(['success' => true, 'message' => 'Land holding added', 'id' => $db->lastInsertId()]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function transactions($farmerId)
    {
        header('Content-Type: application/json');
        try {
            $db = \App\Core\Database\Database::getInstance();
            $txns = $db->fetchAll("SELECT * FROM transactions WHERE user_id = ? OR customer_id = ? ORDER BY created_at DESC LIMIT 50", [$farmerId, $farmerId]);
            echo json_encode(['success' => true, 'data' => $txns ?? []]);
        } catch (\Exception $e) {
            echo json_encode(['success' => true, 'data' => [], 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function addTransaction()
    {
        header('Content-Type: application/json');
        try {
            $data = $_POST ?: json_decode(file_get_contents('php://input'), true) ?: [];
            $db = \App\Core\Database\Database::getInstance();
            $db->insert('transactions', $data);
            echo json_encode(['success' => true, 'message' => 'Transaction added', 'id' => $db->lastInsertId()]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function loans($farmerId)
    {
        header('Content-Type: application/json');
        try {
            $db = \App\Core\Database\Database::getInstance();
            $loans = $db->fetchAll("SELECT * FROM farmer_loans WHERE farmer_id = ? ORDER BY created_at DESC", [$farmerId]);
            echo json_encode(['success' => true, 'data' => $loans ?? []]);
        } catch (\Exception $e) {
            echo json_encode(['success' => true, 'data' => [], 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function supportRequests($farmerId)
    {
        header('Content-Type: application/json');
        try {
            $db = \App\Core\Database\Database::getInstance();
            $requests = $db->fetchAll("SELECT * FROM farmer_support_requests WHERE farmer_id = ? ORDER BY created_at DESC", [$farmerId]);
            echo json_encode(['success' => true, 'data' => $requests ?? []]);
        } catch (\Exception $e) {
            echo json_encode(['success' => true, 'data' => [], 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function createSupportRequest()
    {
        header('Content-Type: application/json');
        try {
            $data = $_POST ?: json_decode(file_get_contents('php://input'), true) ?: [];
            $db = \App\Core\Database\Database::getInstance();
            $db->insert('farmer_support_requests', $data);
            echo json_encode(['success' => true, 'message' => 'Support request created', 'id' => $db->lastInsertId()]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function dashboard($farmerId)
    {
        header('Content-Type: application/json');
        try {
            $db = \App\Core\Database\Database::getInstance();
            $farmer = $db->fetchOne("SELECT id, name, email, phone, status FROM farmers WHERE id = ?", [$farmerId]);
            $landCount = $db->fetchOne("SELECT COUNT(*) as count FROM farmer_land_holdings WHERE farmer_id = ?", [$farmerId])['count'] ?? 0;
            $loanCount = $db->fetchOne("SELECT COUNT(*) as count FROM farmer_loans WHERE farmer_id = ?", [$farmerId])['count'] ?? 0;
            $supportCount = $db->fetchOne("SELECT COUNT(*) as count FROM farmer_support_requests WHERE farmer_id = ?", [$farmerId])['count'] ?? 0;
            echo json_encode(['success' => true, 'data' => [
                'farmer' => $farmer,
                'total_land_holdings' => $landCount,
                'total_loans' => $loanCount,
                'total_support_requests' => $supportCount
            ]]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function stats()
    {
        header('Content-Type: application/json');
        try {
            $db = \App\Core\Database\Database::getInstance();
            $totalFarmers = $db->fetchOne("SELECT COUNT(*) as c FROM farmers")['c'] ?? 0;
            $activeFarmers = $db->fetchOne("SELECT COUNT(*) as c FROM farmers WHERE status = 'active'")['c'] ?? 0;
            $totalLand = $db->fetchOne("SELECT COUNT(*) as c FROM farmer_land_holdings")['c'] ?? 0;
            $totalAcquisitions = $db->fetchOne("SELECT COUNT(*) as c FROM land_acquisitions")['c'] ?? 0;
            echo json_encode(['success' => true, 'data' => [
                'total_farmers' => $totalFarmers,
                'active_farmers' => $activeFarmers,
                'total_land_holdings' => $totalLand,
                'total_acquisitions' => $totalAcquisitions
            ]]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function summary()
    {
        header('Content-Type: application/json');
        try {
            $db = \App\Core\Database\Database::getInstance();
            $farmers = $db->fetchAll("SELECT id, name, email, phone, status FROM farmers ORDER BY created_at DESC LIMIT 20");
            $recentAcquisitions = $db->fetchAll("SELECT id, acquisition_date, acquisition_cost, status FROM land_acquisitions ORDER BY acquisition_date DESC LIMIT 10");
            echo json_encode(['success' => true, 'data' => [
                'recent_farmers' => $farmers ?? [],
                'recent_acquisitions' => $recentAcquisitions ?? []
            ]]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function updateAcquisitionStatus()
    {
        header('Content-Type: application/json');
        try {
            $data = $_POST ?: json_decode(file_get_contents('php://input'), true) ?: [];
            $holdingId = $_REQUEST['holdingId'] ?? $data['holding_id'] ?? null;
            $status = $data['acquisition_status'] ?? $data['status'] ?? null;
            if (!$holdingId || !$status) {
                echo json_encode(['success' => false, 'message' => 'holding_id and status required']);
                exit;
            }
            $db = \App\Core\Database\Database::getInstance();
            $db->update('farmer_land_holdings', ['acquisition_status' => $status], 'id = ?', [$holdingId]);
            echo json_encode(['success' => true, 'message' => 'Acquisition status updated']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}
