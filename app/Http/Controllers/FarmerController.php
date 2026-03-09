<?php

namespace App\Http\Controllers;

use App\Services\FarmerService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Controller for Farmer Management operations
 */
class FarmerController extends BaseController
{
    private FarmerService $farmerService;

    public function __construct(FarmerService $farmerService)
    {
        $this->farmerService = $farmerService;
    }

    /**
     * Get all farmers with filtering and pagination
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'status' => $request->get('status'),
                'district' => $request->get('district'),
                'state' => $request->get('state'),
                'search' => $request->get('search')
            ];

            $perPage = (int) $request->get('per_page', 20);
            $farmers = $this->farmerService->getAllFarmers($filters, $perPage);

            return response()->json([
                'success' => true,
                'data' => $farmers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve farmers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific farmer by ID
     */
    public function show(int $id): JsonResponse
    {
        try {
            $farmer = $this->farmerService->getFarmer($id);

            if (!$farmer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Farmer not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $farmer
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve farmer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new farmer
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'farmer_number' => 'nullable|string|unique:farmer_profiles,farmer_number',
                'full_name' => 'required|string|max:100',
                'father_name' => 'nullable|string|max:100',
                'spouse_name' => 'nullable|string|max:100',
                'date_of_birth' => 'nullable|date',
                'gender' => 'in:male,female,other',
                'phone' => 'required|string|max:15|unique:farmer_profiles,phone',
                'alternate_phone' => 'nullable|string|max:15',
                'email' => 'nullable|email|max:100',
                'address' => 'nullable|string',
                'village' => 'required|string|max:100',
                'post_office' => 'nullable|string|max:100',
                'tehsil' => 'nullable|string|max:100',
                'district' => 'required|string|max:100',
                'state' => 'required|string|max:100',
                'pincode' => 'nullable|string|max:10',
                'aadhar_number' => 'nullable|string|max:20',
                'pan_number' => 'nullable|string|max:20',
                'voter_id' => 'nullable|string|max:20',
                'bank_account_number' => 'required|string|max:30',
                'bank_name' => 'required|string|max:100',
                'ifsc_code' => 'required|string|max:20',
                'account_holder_name' => 'nullable|string|max:100',
                'total_land_holding' => 'nullable|numeric|min:0',
                'cultivated_area' => 'nullable|numeric|min:0',
                'irrigated_area' => 'nullable|numeric|min:0',
                'non_irrigated_area' => 'nullable|numeric|min:0',
                'crop_types' => 'nullable|array',
                'farming_experience' => 'nullable|integer|min:0',
                'education_level' => 'nullable|string|max:50',
                'family_members' => 'nullable|integer|min:0',
                'family_income' => 'nullable|numeric|min:0',
                'credit_score' => 'in:excellent,good,fair,poor',
                'credit_limit' => 'nullable|numeric|min:0',
                'outstanding_loans' => 'nullable|numeric|min:0',
                'payment_history' => 'nullable|array',
                'status' => 'in:active,inactive,blacklisted,under_review',
                'associate_id' => 'nullable|integer|exists:associates,id',
                'created_by' => 'required|integer|exists:users,id'
            ]);

            $farmerId = $this->farmerService->createFarmer($validated);

            return response()->json([
                'success' => true,
                'message' => 'Farmer created successfully',
                'data' => [
                    'farmer_id' => $farmerId
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create farmer',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Update farmer information
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'full_name' => 'sometimes|required|string|max:100',
                'father_name' => 'sometimes|nullable|string|max:100',
                'spouse_name' => 'sometimes|nullable|string|max:100',
                'date_of_birth' => 'sometimes|nullable|date',
                'gender' => 'sometimes|in:male,female,other',
                'phone' => 'sometimes|required|string|max:15|unique:farmer_profiles,phone,' . $id,
                'alternate_phone' => 'sometimes|nullable|string|max:15',
                'email' => 'sometimes|nullable|email|max:100',
                'address' => 'sometimes|nullable|string',
                'village' => 'sometimes|required|string|max:100',
                'post_office' => 'sometimes|nullable|string|max:100',
                'tehsil' => 'sometimes|nullable|string|max:100',
                'district' => 'sometimes|required|string|max:100',
                'state' => 'sometimes|required|string|max:100',
                'pincode' => 'sometimes|nullable|string|max:10',
                'aadhar_number' => 'sometimes|nullable|string|max:20',
                'pan_number' => 'sometimes|nullable|string|max:20',
                'voter_id' => 'sometimes|nullable|string|max:20',
                'bank_account_number' => 'sometimes|required|string|max:30',
                'bank_name' => 'sometimes|required|string|max:100',
                'ifsc_code' => 'sometimes|required|string|max:20',
                'account_holder_name' => 'sometimes|nullable|string|max:100',
                'total_land_holding' => 'sometimes|nullable|numeric|min:0',
                'cultivated_area' => 'sometimes|nullable|numeric|min:0',
                'irrigated_area' => 'sometimes|nullable|numeric|min:0',
                'non_irrigated_area' => 'sometimes|nullable|numeric|min:0',
                'crop_types' => 'sometimes|nullable|array',
                'farming_experience' => 'sometimes|nullable|integer|min:0',
                'education_level' => 'sometimes|nullable|string|max:50',
                'family_members' => 'sometimes|nullable|integer|min:0',
                'family_income' => 'sometimes|nullable|numeric|min:0',
                'credit_score' => 'sometimes|in:excellent,good,fair,poor',
                'credit_limit' => 'sometimes|nullable|numeric|min:0',
                'outstanding_loans' => 'sometimes|nullable|numeric|min:0',
                'payment_history' => 'sometimes|nullable|array',
                'status' => 'sometimes|in:active,inactive,blacklisted,under_review',
                'associate_id' => 'sometimes|nullable|integer|exists:associates,id'
            ]);

            $result = $this->farmerService->updateFarmer($id, $validated);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Farmer not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Farmer updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update farmer',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get farmer land holdings
     */
    public function landHoldings(int $id): JsonResponse
    {
        try {
            $landHoldings = $this->farmerService->getFarmerLandHoldings($id);

            return response()->json([
                'success' => true,
                'data' => $landHoldings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve land holdings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add land holding for farmer
     */
    public function addLandHolding(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'khasra_number' => 'nullable|string|max:50',
                'land_area' => 'required|numeric|min:0',
                'land_area_unit' => 'in:sqft,acre,hectare,bigha',
                'land_type' => 'in:agricultural,residential,commercial,mixed',
                'soil_type' => 'nullable|string|max:100',
                'irrigation_source' => 'nullable|string|max:100',
                'water_source' => 'nullable|string|max:100',
                'electricity_available' => 'boolean',
                'road_access' => 'boolean',
                'location' => 'nullable|string|max:255',
                'village' => 'required|string|max:100',
                'tehsil' => 'nullable|string|max:100',
                'district' => 'required|string|max:100',
                'state' => 'required|string|max:100',
                'land_value' => 'nullable|numeric|min:0',
                'current_status' => 'in:cultivated,fallow,sold,under_acquisition,disputed',
                'ownership_document' => 'nullable|string|max:255',
                'mutation_document' => 'nullable|string|max:255',
                'acquisition_status' => 'in:not_acquired,under_negotiation,acquired,rejected',
                'acquisition_date' => 'nullable|date',
                'acquisition_amount' => 'nullable|numeric|min:0',
                'payment_status' => 'in:pending,partial,completed',
                'payment_received' => 'nullable|numeric|min:0',
                'remarks' => 'nullable|string'
            ]);

            $holdingId = $this->farmerService->addLandHolding($id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Land holding added successfully',
                'data' => [
                    'holding_id' => $holdingId
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add land holding',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Update acquisition status
     */
    public function updateAcquisitionStatus(Request $request, int $holdingId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:not_acquired,under_negotiation,acquired,rejected',
                'amount' => 'nullable|numeric|min:0'
            ]);

            $result = $this->farmerService->updateAcquisitionStatus(
                $holdingId,
                $validated['status'],
                $validated['amount'] ?? null
            );

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Land holding not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Acquisition status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update acquisition status',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get farmer transactions
     */
    public function transactions(int $id): JsonResponse
    {
        try {
            $limit = request()->get('limit', 50);
            $transactions = $this->farmerService->getFarmerTransactions($id, (int) $limit);

            return response()->json([
                'success' => true,
                'data' => $transactions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve transactions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add transaction for farmer
     */
    public function addTransaction(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'transaction_type' => 'required|in:land_acquisition,payment,loan,commission,refund,penalty',
                'transaction_number' => 'nullable|string|max:50|unique:farmer_transactions,transaction_number',
                'amount' => 'required|numeric|min:0',
                'transaction_date' => 'required|date',
                'payment_method' => 'in:cash,cheque,bank_transfer,online',
                'bank_reference' => 'nullable|string|max:100',
                'transaction_id' => 'nullable|string|max:100',
                'description' => 'nullable|string',
                'land_acquisition_id' => 'nullable|integer|exists:land_acquisitions,id',
                'commission_id' => 'nullable|integer|exists:commission_tracking,id',
                'status' => 'in:pending,completed,failed,cancelled',
                'created_by' => 'required|integer|exists:users,id'
            ]);

            $transactionId = $this->farmerService->addTransaction($id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Transaction added successfully',
                'data' => [
                    'transaction_id' => $transactionId
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add transaction',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get farmer loans
     */
    public function loans(int $id): JsonResponse
    {
        try {
            $loans = $this->farmerService->getFarmerLoans($id);

            return response()->json([
                'success' => true,
                'data' => $loans
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve loans',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get farmer support requests
     */
    public function supportRequests(int $id): JsonResponse
    {
        try {
            $limit = request()->get('limit', 20);
            $supportRequests = $this->farmerService->getFarmerSupportRequests($id, (int) $limit);

            return response()->json([
                'success' => true,
                'data' => $supportRequests
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve support requests',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create support request for farmer
     */
    public function createSupportRequest(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'request_number' => 'nullable|string|max:50|unique:farmer_support_requests,request_number',
                'request_type' => 'required|in:technical,financial,legal,infrastructure,other',
                'priority' => 'in:low,medium,high,urgent',
                'subject' => 'required|string|max:255',
                'description' => 'required|string',
                'status' => 'in:open,in_progress,resolved,closed,rejected',
                'assigned_to' => 'nullable|integer|exists:users,id',
                'created_by' => 'required|integer|exists:users,id'
            ]);

            $requestId = $this->farmerService->createSupportRequest($id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Support request created successfully',
                'data' => [
                    'request_id' => $requestId
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create support request',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get farmer dashboard data
     */
    public function dashboard(int $id): JsonResponse
    {
        try {
            $dashboard = $this->farmerService->getFarmerDashboard($id);

            if (!$dashboard['farmer_info']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Farmer not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $dashboard
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get farmer statistics
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->farmerService->getFarmerStats();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search farmers
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'query' => 'required|string|min:2|max:100',
                'status' => 'nullable|in:active,inactive,blacklisted,under_review',
                'district' => 'nullable|string|max:100'
            ]);

            $results = $this->farmerService->searchFarmers(
                $validated['query'],
                [
                    'status' => $validated['status'] ?? null,
                    'district' => $validated['district'] ?? null
                ]
            );

            return response()->json([
                'success' => true,
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get farmer statistics summary
     */
    public function summary(): JsonResponse
    {
        try {
            $stats = $this->farmerService->getFarmerStats();

            // Calculate additional metrics
            $summary = [
                'total_farmers' => $stats['total_farmers'],
                'active_farmers' => $stats['active_farmers'],
                'inactive_farmers' => $stats['total_farmers'] - $stats['active_farmers'],
                'total_land_area' => $stats['total_land_area'],
                'acquired_land_area' => $stats['acquired_land_area'],
                'acquisition_percentage' => $stats['total_land_area'] > 0 
                    ? round(($stats['acquired_land_area'] / $stats['total_land_area']) * 100, 2) 
                    : 0,
                'total_payments' => $stats['total_payments'],
                'pending_support_requests' => $stats['pending_support_requests'],
                'active_loans' => $stats['active_loans'],
                'average_land_per_farmer' => $stats['total_farmers'] > 0 
                    ? round($stats['total_land_area'] / $stats['total_farmers'], 2) 
                    : 0
            ];

            return response()->json([
                'success' => true,
                'data' => $summary
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate summary',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
