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
                'status' => $this->request->get('status'),
                'district' => $this->request->get('district'),
                'state' => $this->request->get('state'),
                'search' => $this->request->get('search')
            ];

            $perPage = (int) $this->request->get('per_page', 20);
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
                'name' => $this->request->get('name'),
                'email' => $this->request->get('email'),
                'phone' => $this->request->get('phone'),
                'address' => $this->request->get('address'),
                'district' => $this->request->get('district'),
                'state' => $this->request->get('state'),
                'farm_size' => $this->request->get('farm_size'),
                'crop_type' => $this->request->get('crop_type'),
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
                'name' => $this->request->get('name'),
                'email' => $this->request->get('email'),
                'phone' => $this->request->get('phone'),
                'address' => $this->request->get('address'),
                'district' => $this->request->get('district'),
                'state' => $this->request->get('state'),
                'farm_size' => $this->request->get('farm_size'),
                'crop_type' => $this->request->get('crop_type'),
                'status' => $this->request->get('status')
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
            $query = $this->request->get('query');
            $filters = [
                'district' => $this->request->get('district'),
                'state' => $this->request->get('state'),
                'crop_type' => $this->request->get('crop_type'),
                'status' => $this->request->get('status')
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
            $operation = $this->request->get('operation');
            $farmerIds = $this->request->get('farmer_ids', []);

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
