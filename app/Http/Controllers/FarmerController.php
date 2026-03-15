<?php

namespace App\Http\Controllers;

use App\Services\FarmerService;

/**
 * Controller for Farmer Management operations
 */
class FarmerController extends BaseController
{
    private FarmerService $farmerService;

    public function __construct(FarmerService $farmerService)
    {
        parent::__construct();
        $this->farmerService = $farmerService;
    }

    /**
     * Get all farmers with filtering and pagination
     */
    public function index(): void
    {
        try {
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
        } catch (\Exception $e) {
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
            $query = isset($_REQUEST['query']) ? $_REQUEST['query'] : null;
            $filters = [
                'district' => isset($_REQUEST['district']) ? $_REQUEST['district'] : null,
                'state' => isset($_REQUEST['state']) ? $_REQUEST['state'] : null,
                'crop_type' => isset($_REQUEST['crop_type']) ? $_REQUEST['crop_type'] : null,
                'status' => isset($_REQUEST['status']) ? $_REQUEST['status'] : null
            ];

            $results = $this->farmerService->searchFarmers($query, $filters);

            $this->jsonResponse([
                'success' => true,
                'data' => $results
            ]);
        } catch (\Exception $e) {
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
}
