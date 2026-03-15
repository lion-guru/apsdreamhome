<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Modern Farmer Management Service
 * Complete management system for farmers and agricultural land relationships
 */
class FarmerService
{
    private int $cacheTtl = 3600; // 1 hour

    /**
     * Get all farmers with optional filtering
     */
    public function getAllFarmers(array $filters = [], int $perPage = 20): array
    {
        $cacheKey = 'farmers:' . md5(json_encode($filters) . $perPage);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($filters, $perPage) {
            $query = DB::table('farmer_profiles as fp')
                ->leftJoin('associates as a', 'fp.associate_id', '=', 'a.id')
                ->leftJoin('users as ua', 'a.user_id', '=', 'ua.id')
                ->select(
                    'fp.*',
                    'ua.name as associate_name',
                    DB::raw('(SELECT SUM(land_area) FROM farmer_land_holdings WHERE farmer_id = fp.id) as total_land_area'),
                    DB::raw('(SELECT COUNT(*) FROM farmer_transactions WHERE farmer_id = fp.id) as transaction_count')
                );

            // Apply filters
            if (!empty($filters['status'])) {
                $query->where('fp.status', $filters['status']);
            }

            if (!empty($filters['district'])) {
                $query->where('fp.district', 'like', '%' . $filters['district'] . '%');
            }

            if (!empty($filters['state'])) {
                $query->where('fp.state', $filters['state']);
            }

            if (!empty($filters['search'])) {
                $search = '%' . $filters['search'] . '%';
                $query->where(function ($q) use ($search) {
                    $q->where('fp.full_name', 'like', $search)
                        ->orWhere('fp.farmer_number', 'like', $search)
                        ->orWhere('fp.phone', 'like', $search)
                        ->orWhere('fp.village', 'like', $search);
                });
            }

            return $query->orderBy('fp.created_at', 'desc')
                ->paginate($perPage)
                ->toArray();
        });
    }

    /**
     * Get farmer by ID with full details
     */
    public function getFarmer(int $id): ?array
    {
        $cacheKey = "farmer:{$id}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($id) {
            $farmer = DB::table('farmer_profiles as fp')
                ->leftJoin('associates as a', 'fp.associate_id', '=', 'a.id')
                ->leftJoin('users as ua', 'a.user_id', '=', 'ua.id')
                ->leftJoin('users as u', 'fp.created_by', '=', 'u.id')
                ->select(
                    'fp.*',
                    'ua.name as associate_name',
                    'u.name as created_by_name'
                )
                ->where('fp.id', $id)
                ->first();

            if (!$farmer) {
                return null;
            }

            $farmer = (array) $farmer;

            // Get related data
            $farmer['land_holdings'] = $this->getFarmerLandHoldings($id);
            $farmer['transactions'] = $this->getFarmerTransactions($id, 10);
            $farmer['loans'] = $this->getFarmerLoans($id);
            $farmer['support_requests'] = $this->getFarmerSupportRequests($id, 5);

            return $farmer;
        });
    }

    /**
     * Create new farmer
     */
    public function createFarmer(array $data): int
    {
        try {
            DB::beginTransaction();

            // Generate unique farmer number if not provided
            if (empty($data['farmer_number'])) {
                $data['farmer_number'] = $this->generateFarmerNumber();
            }

            // Validate required fields
            $this->validateFarmerData($data);

            // Insert farmer profile
            $farmerId = DB::table('farmer_profiles')->insertGetId([
                'farmer_number' => $data['farmer_number'],
                'full_name' => $data['full_name'],
                'father_name' => $data['father_name'] ?? null,
                'spouse_name' => $data['spouse_name'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'gender' => $data['gender'] ?? 'male',
                'phone' => $data['phone'],
                'alternate_phone' => $data['alternate_phone'] ?? null,
                'email' => $data['email'] ?? null,
                'address' => $data['address'] ?? null,
                'village' => $data['village'],
                'post_office' => $data['post_office'] ?? null,
                'tehsil' => $data['tehsil'] ?? null,
                'district' => $data['district'],
                'state' => $data['state'],
                'pincode' => $data['pincode'] ?? null,
                'aadhar_number' => $data['aadhar_number'] ?? null,
                'pan_number' => $data['pan_number'] ?? null,
                'voter_id' => $data['voter_id'] ?? null,
                'bank_account_number' => $data['bank_account_number'],
                'bank_name' => $data['bank_name'],
                'ifsc_code' => $data['ifsc_code'],
                'account_holder_name' => $data['account_holder_name'] ?? $data['full_name'],
                'total_land_holding' => $data['total_land_holding'] ?? 0,
                'cultivated_area' => $data['cultivated_area'] ?? 0,
                'irrigated_area' => $data['irrigated_area'] ?? 0,
                'non_irrigated_area' => $data['non_irrigated_area'] ?? 0,
                'crop_types' => json_encode($data['crop_types'] ?? []),
                'farming_experience' => $data['farming_experience'] ?? 0,
                'education_level' => $data['education_level'] ?? null,
                'family_members' => $data['family_members'] ?? 0,
                'family_income' => $data['family_income'] ?? 0,
                'credit_score' => $data['credit_score'] ?? 'fair',
                'credit_limit' => $data['credit_limit'] ?? 50000,
                'outstanding_loans' => $data['outstanding_loans'] ?? 0,
                'payment_history' => json_encode($data['payment_history'] ?? []),
                'status' => $data['status'] ?? 'active',
                'associate_id' => $data['associate_id'] ?? null,
                'created_by' => $data['created_by'],
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Clear cache
            $this->clearFarmerCache();

            DB::commit();

            Log::info('Farmer created successfully', [
                'farmer_id' => $farmerId,
                'farmer_number' => $data['farmer_number'],
                'created_by' => $data['created_by']
            ]);

            return $farmerId;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating farmer', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Update farmer information
     */
    public function updateFarmer(int $id, array $data): bool
    {
        try {
            DB::beginTransaction();

            // Check if farmer exists
            $existing = DB::table('farmer_profiles')->where('id', $id)->first();
            if (!$existing) {
                throw new \Exception("Farmer with ID {$id} not found");
            }

            // Update farmer profile
            DB::table('farmer_profiles')
                ->where('id', $id)
                ->update([
                    'full_name' => $data['full_name'] ?? $existing->full_name,
                    'father_name' => $data['father_name'] ?? $existing->father_name,
                    'spouse_name' => $data['spouse_name'] ?? $existing->spouse_name,
                    'date_of_birth' => $data['date_of_birth'] ?? $existing->date_of_birth,
                    'gender' => $data['gender'] ?? $existing->gender,
                    'phone' => $data['phone'] ?? $existing->phone,
                    'alternate_phone' => $data['alternate_phone'] ?? $existing->alternate_phone,
                    'email' => $data['email'] ?? $existing->email,
                    'address' => $data['address'] ?? $existing->address,
                    'village' => $data['village'] ?? $existing->village,
                    'post_office' => $data['post_office'] ?? $existing->post_office,
                    'tehsil' => $data['tehsil'] ?? $existing->tehsil,
                    'district' => $data['district'] ?? $existing->district,
                    'state' => $data['state'] ?? $existing->state,
                    'pincode' => $data['pincode'] ?? $existing->pincode,
                    'aadhar_number' => $data['aadhar_number'] ?? $existing->aadhar_number,
                    'pan_number' => $data['pan_number'] ?? $existing->pan_number,
                    'voter_id' => $data['voter_id'] ?? $existing->voter_id,
                    'bank_account_number' => $data['bank_account_number'] ?? $existing->bank_account_number,
                    'bank_name' => $data['bank_name'] ?? $existing->bank_name,
                    'ifsc_code' => $data['ifsc_code'] ?? $existing->ifsc_code,
                    'account_holder_name' => $data['account_holder_name'] ?? $existing->account_holder_name,
                    'total_land_holding' => $data['total_land_holding'] ?? $existing->total_land_holding,
                    'cultivated_area' => $data['cultivated_area'] ?? $existing->cultivated_area,
                    'irrigated_area' => $data['irrigated_area'] ?? $existing->irrigated_area,
                    'non_irrigated_area' => $data['non_irrigated_area'] ?? $existing->non_irrigated_area,
                    'crop_types' => isset($data['crop_types']) ? json_encode($data['crop_types']) : $existing->crop_types,
                    'farming_experience' => $data['farming_experience'] ?? $existing->farming_experience,
                    'education_level' => $data['education_level'] ?? $existing->education_level,
                    'family_members' => $data['family_members'] ?? $existing->family_members,
                    'family_income' => $data['family_income'] ?? $existing->family_income,
                    'credit_score' => $data['credit_score'] ?? $existing->credit_score,
                    'credit_limit' => $data['credit_limit'] ?? $existing->credit_limit,
                    'outstanding_loans' => $data['outstanding_loans'] ?? $existing->outstanding_loans,
                    'payment_history' => isset($data['payment_history']) ? json_encode($data['payment_history']) : $existing->payment_history,
                    'status' => $data['status'] ?? $existing->status,
                    'associate_id' => $data['associate_id'] ?? $existing->associate_id,
                    'updated_at' => now()
                ]);

            // Clear cache
            $this->clearFarmerCache($id);

            DB::commit();

            Log::info('Farmer updated successfully', [
                'farmer_id' => $id,
                'updated_fields' => array_keys($data)
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating farmer', [
                'farmer_id' => $id,
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Get farmer land holdings
     */
    public function getFarmerLandHoldings(int $farmerId, int $limit = null): array
    {
        $query = DB::table('farmer_land_holdings')
            ->where('farmer_id', $farmerId)
            ->orderBy('created_at', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get()->toArray();
    }

    /**
     * Add land holding for farmer
     */
    public function addLandHolding(int $farmerId, array $data): int
    {
        try {
            $holdingId = DB::table('farmer_land_holdings')->insertGetId([
                'farmer_id' => $farmerId,
                'khasra_number' => $data['khasra_number'] ?? null,
                'land_area' => $data['land_area'],
                'land_area_unit' => $data['land_area_unit'] ?? 'sqft',
                'land_type' => $data['land_type'] ?? 'agricultural',
                'soil_type' => $data['soil_type'] ?? null,
                'irrigation_source' => $data['irrigation_source'] ?? null,
                'water_source' => $data['water_source'] ?? null,
                'electricity_available' => $data['electricity_available'] ?? false,
                'road_access' => $data['road_access'] ?? false,
                'location' => $data['location'] ?? null,
                'village' => $data['village'],
                'tehsil' => $data['tehsil'] ?? null,
                'district' => $data['district'],
                'state' => $data['state'],
                'land_value' => $data['land_value'] ?? 0,
                'current_status' => $data['current_status'] ?? 'cultivated',
                'ownership_document' => $data['ownership_document'] ?? null,
                'mutation_document' => $data['mutation_document'] ?? null,
                'acquisition_status' => $data['acquisition_status'] ?? 'not_acquired',
                'acquisition_date' => $data['acquisition_date'] ?? null,
                'acquisition_amount' => $data['acquisition_amount'] ?? 0,
                'payment_status' => $data['payment_status'] ?? 'pending',
                'payment_received' => $data['payment_received'] ?? 0,
                'remarks' => $data['remarks'] ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Update farmer's total land holding
            $this->updateFarmerTotalLand($farmerId);

            // Clear cache
            $this->clearFarmerCache($farmerId);

            Log::info('Land holding added successfully', [
                'farmer_id' => $farmerId,
                'holding_id' => $holdingId,
                'land_area' => $data['land_area']
            ]);

            return $holdingId;
        } catch (\Exception $e) {
            Log::error('Error adding land holding', [
                'farmer_id' => $farmerId,
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Update acquisition status for land holding
     */
    public function updateAcquisitionStatus(int $holdingId, string $status, ?float $amount = null): bool
    {
        try {
            $updateData = [
                'acquisition_status' => $status,
                'updated_at' => now()
            ];

            if ($amount !== null) {
                $updateData['acquisition_amount'] = $amount;
                $updateData['payment_received'] = $amount;
            }

            if ($status === 'acquired') {
                $updateData['acquisition_date'] = now();
                $updateData['payment_status'] = 'completed';
            }

            $result = DB::table('farmer_land_holdings')
                ->where('id', $holdingId)
                ->update($updateData);

            if ($result) {
                // Get farmer ID to update total land
                $holding = DB::table('farmer_land_holdings')->where('id', $holdingId)->first();
                if ($holding) {
                    $this->updateFarmerTotalLand($holding->farmer_id);
                    $this->clearFarmerCache($holding->farmer_id);
                }
            }

            return $result > 0;
        } catch (\Exception $e) {
            Log::error('Error updating acquisition status', [
                'holding_id' => $holdingId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get farmer transactions
     */
    public function getFarmerTransactions(int $farmerId, int $limit = null): array
    {
        $query = DB::table('farmer_transactions')
            ->where('farmer_id', $farmerId)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get()->toArray();
    }

    /**
     * Add transaction for farmer
     */
    public function addTransaction(int $farmerId, array $data): int
    {
        try {
            $transactionNumber = $data['transaction_number'] ?? $this->generateTransactionNumber();

            $transactionId = DB::table('farmer_transactions')->insertGetId([
                'farmer_id' => $farmerId,
                'transaction_type' => $data['transaction_type'],
                'transaction_number' => $transactionNumber,
                'amount' => $data['amount'],
                'transaction_date' => $data['transaction_date'],
                'payment_method' => $data['payment_method'] ?? 'cash',
                'bank_reference' => $data['bank_reference'] ?? null,
                'transaction_id' => $data['transaction_id'] ?? null,
                'description' => $data['description'] ?? null,
                'land_acquisition_id' => $data['land_acquisition_id'] ?? null,
                'commission_id' => $data['commission_id'] ?? null,
                'status' => $data['status'] ?? 'completed',
                'created_by' => $data['created_by'],
                'created_at' => now()
            ]);

            // Update farmer payment history
            $this->updateFarmerPaymentHistory($farmerId, [
                'transaction_number' => $transactionNumber,
                'amount' => $data['amount'],
                'type' => $data['transaction_type'],
                'date' => $data['transaction_date'],
                'status' => $data['status'] ?? 'completed'
            ]);

            // Clear cache
            $this->clearFarmerCache($farmerId);

            Log::info('Transaction added successfully', [
                'farmer_id' => $farmerId,
                'transaction_id' => $transactionId,
                'amount' => $data['amount']
            ]);

            return $transactionId;
        } catch (\Exception $e) {
            Log::error('Error adding transaction', [
                'farmer_id' => $farmerId,
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Get farmer loans
     */
    public function getFarmerLoans(int $farmerId): array
    {
        return DB::table('farmer_loans')
            ->where('farmer_id', $farmerId)
            ->orderBy('sanction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Get farmer support requests
     */
    public function getFarmerSupportRequests(int $farmerId, int $limit = null): array
    {
        $query = DB::table('farmer_support_requests')
            ->where('farmer_id', $farmerId)
            ->orderBy('created_at', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get()->toArray();
    }

    /**
     * Create support request for farmer
     */
    public function createSupportRequest(int $farmerId, array $data): int
    {
        try {
            $requestNumber = $data['request_number'] ?? $this->generateSupportRequestNumber();

            $requestId = DB::table('farmer_support_requests')->insertGetId([
                'farmer_id' => $farmerId,
                'request_number' => $requestNumber,
                'request_type' => $data['request_type'],
                'priority' => $data['priority'] ?? 'medium',
                'subject' => $data['subject'],
                'description' => $data['description'],
                'status' => $data['status'] ?? 'open',
                'assigned_to' => $data['assigned_to'] ?? null,
                'created_by' => $data['created_by'],
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Clear cache
            $this->clearFarmerCache($farmerId);

            Log::info('Support request created successfully', [
                'farmer_id' => $farmerId,
                'request_id' => $requestId,
                'request_number' => $requestNumber
            ]);

            return $requestId;
        } catch (\Exception $e) {
            Log::error('Error creating support request', [
                'farmer_id' => $farmerId,
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Get farmer dashboard data
     */
    public function getFarmerDashboard(int $farmerId): array
    {
        $cacheKey = "farmer_dashboard:{$farmerId}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($farmerId) {
            $dashboard = [];

            // Farmer basic info
            $dashboard['farmer_info'] = $this->getFarmer($farmerId);

            // Land holding summary
            $dashboard['land_summary'] = DB::table('farmer_land_holdings')
                ->where('farmer_id', $farmerId)
                ->selectRaw('
                    SUM(land_area) as total_area,
                    SUM(CASE WHEN current_status = ? THEN land_area ELSE 0 END) as cultivated_area,
                    SUM(CASE WHEN current_status = ? THEN land_area ELSE 0 END) as under_acquisition,
                    SUM(CASE WHEN acquisition_status = ? THEN land_area ELSE 0 END) as acquired_area
                ', ['cultivated', 'under_acquisition', 'acquired'])
                ->first();

            // Transaction summary
            $dashboard['transaction_summary'] = DB::table('farmer_transactions')
                ->where('farmer_id', $farmerId)
                ->selectRaw('
                    SUM(CASE WHEN transaction_type = ? AND status = ? THEN amount ELSE 0 END) as total_received,
                    SUM(CASE WHEN transaction_type = ? AND status = ? THEN amount ELSE 0 END) as total_loans,
                    SUM(CASE WHEN transaction_type = ? AND status = ? THEN amount ELSE 0 END) as total_commissions
                ', ['payment', 'completed', 'loan', 'active', 'commission', 'completed'])
                ->first();

            // Recent transactions
            $dashboard['recent_transactions'] = $this->getFarmerTransactions($farmerId, 5);

            // Active loans
            $dashboard['active_loans'] = DB::table('farmer_loans')
                ->where('farmer_id', $farmerId)
                ->whereIn('status', ['active', 'disbursed'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->toArray();

            // Support requests summary
            $dashboard['support_summary'] = DB::table('farmer_support_requests')
                ->where('farmer_id', $farmerId)
                ->selectRaw('
                    COUNT(*) as total_requests,
                    SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as open_requests,
                    SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as resolved_requests
                ', ['open', 'resolved'])
                ->first();

            return $dashboard;
        });
    }

    /**
     * Get farmer statistics
     */
    public function getFarmerStats(): array
    {
        $cacheKey = 'farmer_stats';

        return Cache::remember($cacheKey, $this->cacheTtl, function () {
            return [
                'total_farmers' => DB::table('farmer_profiles')->count(),
                'active_farmers' => DB::table('farmer_profiles')->where('status', 'active')->count(),
                'total_land_area' => DB::table('farmer_land_holdings')->sum('land_area'),
                'acquired_land_area' => DB::table('farmer_land_holdings')
                    ->where('acquisition_status', 'acquired')
                    ->sum('land_area'),
                'total_payments' => DB::table('farmer_transactions')
                    ->where('transaction_type', 'payment')
                    ->where('status', 'completed')
                    ->sum('amount'),
                'pending_support_requests' => DB::table('farmer_support_requests')
                    ->where('status', 'open')
                    ->count(),
                'active_loans' => DB::table('farmer_loans')
                    ->whereIn('status', ['active', 'disbursed'])
                    ->count()
            ];
        });
    }

    /**
     * Generate unique farmer number
     */
    private function generateFarmerNumber(): string
    {
        $prefix = 'F';
        $year = date('Y');
        $sequence = DB::table('farmer_profiles')
            ->where('farmer_number', 'like', $prefix . $year . '%')
            ->count() + 1;

        return $prefix . $year . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique transaction number
     */
    private function generateTransactionNumber(): string
    {
        $prefix = 'TXN';
        $date = date('Ymd');
        $sequence = DB::table('farmer_transactions')
            ->where('transaction_number', 'like', $prefix . $date . '%')
            ->count() + 1;

        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique support request number
     */
    private function generateSupportRequestNumber(): string
    {
        $prefix = 'SR';
        $year = date('Y');
        $sequence = DB::table('farmer_support_requests')
            ->where('request_number', 'like', $prefix . $year . '%')
            ->count() + 1;

        return $prefix . $year . str_pad($sequence, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Validate farmer data
     */
    private function validateFarmerData(array $data): void
    {
        $required = ['full_name', 'phone', 'village', 'district', 'state', 'bank_account_number', 'bank_name', 'ifsc_code'];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \Exception("Field '{$field}' is required");
            }
        }

        // Validate phone number
        if (!preg_match('/^[0-9]{10,15}$/', $data['phone'])) {
            throw new \Exception('Invalid phone number format');
        }

        // Validate email if provided
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('Invalid email format');
        }

        // Check for duplicate farmer number
        if (!empty($data['farmer_number'])) {
            $exists = DB::table('farmer_profiles')
                ->where('farmer_number', $data['farmer_number'])
                ->where('id', '!=', $data['id'] ?? 0)
                ->exists();

            if ($exists) {
                throw new \Exception('Farmer number already exists');
            }
        }

        // Check for duplicate phone
        $exists = DB::table('farmer_profiles')
            ->where('phone', $data['phone'])
            ->where('id', '!=', $data['id'] ?? 0)
            ->exists();

        if ($exists) {
            throw new \Exception('Phone number already exists');
        }
    }

    /**
     * Update farmer's total land holding
     */
    private function updateFarmerTotalLand(int $farmerId): void
    {
        $totalLand = DB::table('farmer_land_holdings')
            ->where('farmer_id', $farmerId)
            ->sum('land_area');

        DB::table('farmer_profiles')
            ->where('id', $farmerId)
            ->update(['total_land_holding' => $totalLand]);
    }

    /**
     * Update farmer payment history
     */
    private function updateFarmerPaymentHistory(int $farmerId, array $transaction): void
    {
        $farmer = DB::table('farmer_profiles')
            ->where('id', $farmerId)
            ->first();

        if (!$farmer) {
            return;
        }

        $paymentHistory = json_decode($farmer->payment_history ?? '[]', true);
        $paymentHistory[] = $transaction;

        // Keep only last 10 transactions
        $paymentHistory = array_slice($paymentHistory, -10);

        DB::table('farmer_profiles')
            ->where('id', $farmerId)
            ->update(['payment_history' => json_encode($paymentHistory)]);
    }

    /**
     * Clear farmer cache
     */
    private function clearFarmerCache(?int $farmerId = null): void
    {
        if ($farmerId) {
            Cache::forget("farmer:{$farmerId}");
            Cache::forget("farmer_dashboard:{$farmerId}");
        }

        Cache::forget('farmer_stats');
        Cache::flush(); // Clear all farmer-related cache
    }

    /**
     * Search farmers
     */
    public function searchFarmers(string $query, array $filters = []): array
    {
        $searchQuery = DB::table('farmer_profiles as fp')
            ->leftJoin('associates as a', 'fp.associate_id', '=', 'a.id')
            ->leftJoin('users as ua', 'a.user_id', '=', 'ua.id')
            ->select(
                'fp.*',
                'ua.name as associate_name'
            )
            ->where(function ($q) use ($query) {
                $q->where('fp.full_name', 'like', '%' . $query . '%')
                    ->orWhere('fp.farmer_number', 'like', '%' . $query . '%')
                    ->orWhere('fp.phone', 'like', '%' . $query . '%')
                    ->orWhere('fp.village', 'like', '%' . $query . '%')
                    ->orWhere('fp.aadhar_number', 'like', '%' . $query . '%');
            });

        // Apply additional filters
        if (!empty($filters['status'])) {
            $searchQuery->where('fp.status', $filters['status']);
        }

        if (!empty($filters['district'])) {
            $searchQuery->where('fp.district', $filters['district']);
        }

        return $searchQuery->orderBy('fp.full_name')
            ->limit(50)
            ->get()
            ->toArray();
    }

    /**
     * Delete farmer
     */
    public function deleteFarmer(int $id): bool
    {
        try {
            $deleted = DB::table('farmer_profiles')
                ->where('id', $id)
                ->delete();

            if ($deleted) {
                Cache::forget('farmer:' . $id);
                Cache::forget('farmer_stats');
            }

            return $deleted > 0;
        } catch (\Exception $e) {
            Log::error('Failed to delete farmer', ['id' => $id, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get farmer statistics
     */
    public function getStatistics(): array
    {
        $cacheKey = 'farmer_stats';

        return Cache::remember($cacheKey, $this->cacheTtl, function () {
            return [
                'total_farmers' => DB::table('farmer_profiles')->count(),
                'active_farmers' => DB::table('farmer_profiles')->where('status', 'active')->count(),
                'inactive_farmers' => DB::table('farmer_profiles')->where('status', 'inactive')->count(),
                'new_this_month' => DB::table('farmer_profiles')
                    ->where('created_at', '>=', now()->subMonth())
                    ->count(),
                'by_state' => DB::table('farmer_profiles')
                    ->select('state', DB::raw('count(*) as count'))
                    ->groupBy('state')
                    ->orderBy('count', 'desc')
                    ->get()
                    ->toArray(),
                'by_district' => DB::table('farmer_profiles')
                    ->select('district', DB::raw('count(*) as count'))
                    ->groupBy('district')
                    ->orderBy('count', 'desc')
                    ->limit(10)
                    ->get()
                    ->toArray()
            ];
        });
    }

    /**
     * Bulk operations
     */
    public function bulkOperation(string $operation, array $farmerIds): array
    {
        $results = [];

        foreach ($farmerIds as $farmerId) {
            try {
                switch ($operation) {
                    case 'activate':
                        $updated = DB::table('farmer_profiles')
                            ->where('id', $farmerId)
                            ->update(['status' => 'active', 'updated_at' => now()]);
                        $results[] = ['id' => $farmerId, 'success' => $updated, 'action' => 'activated'];
                        break;

                    case 'deactivate':
                        $updated = DB::table('farmer_profiles')
                            ->where('id', $farmerId)
                            ->update(['status' => 'inactive', 'updated_at' => now()]);
                        $results[] = ['id' => $farmerId, 'success' => $updated, 'action' => 'deactivated'];
                        break;

                    case 'delete':
                        $deleted = $this->deleteFarmer($farmerId);
                        $results[] = ['id' => $farmerId, 'success' => $deleted, 'action' => 'deleted'];
                        break;

                    default:
                        $results[] = ['id' => $farmerId, 'success' => false, 'error' => 'Unknown operation'];
                }
            } catch (\Exception $e) {
                $results[] = ['id' => $farmerId, 'success' => false, 'error' => $e->getMessage()];
            }
        }

        // Clear cache after bulk operations
        Cache::forget('farmer_stats');

        return $results;
    }
}
