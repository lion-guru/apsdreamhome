<?php

namespace App\Services;

use \App\Traits\ServiceTenantTrait;

/**
 * Modern Farmer Management Service
 * Complete management system for farmers and agricultural land relationships
 */
class FarmerService
{
    use \App\Traits\ServiceTenantTrait;

    private int $cacheTtl = 3600; // 1 hour

    /**
     * Get database instance
     */
    private function db(): \App\Core\Database\Database
    {
        return \App\Core\Database\Database::getInstance();
    }

    /**
     * Cache helper - remember
     */
    private function cacheRemember(string $key, int $ttl, callable $callback)
    {
        if (!isset($_SESSION['_cache'])) {
            $_SESSION['_cache'] = [];
        }
        $cached = $_SESSION['_cache'][$key] ?? null;
        if ($cached !== null && isset($cached['expires']) && $cached['expires'] > time()) {
            return $cached['data'];
        }
        $data = $callback();
        $_SESSION['_cache'][$key] = ['data' => $data, 'expires' => time() + $ttl];
        return $data;
    }

    /**
     * Cache helper - forget
     */
    private function cacheForget(string $key): void
    {
        if (isset($_SESSION['_cache'][$key])) {
            unset($_SESSION['_cache'][$key]);
        }
    }

    /**
     * Cache helper - flush
     */
    private function cacheFlush(): void
    {
        $_SESSION['_cache'] = [];
    }

    /**
     * Get all farmers with optional filtering
     */
    public function getAllFarmers(array $filters = [], int $perPage = 20): array
    {
        $cacheKey = 'farmers:' . md5(json_encode($filters) . $perPage);

        return $this->cacheRemember($cacheKey, $this->cacheTtl, function () use ($filters, $perPage) {
            $db = $this->db();
            $sql = "SELECT fp.*, ua.name as associate_name,
                    (SELECT COALESCE(SUM(land_area), 0) FROM farmer_land_holdings WHERE farmer_id = fp.id) as total_land_area,
                    (SELECT COUNT(*) FROM farmer_transactions WHERE farmer_id = fp.id) as transaction_count
                    FROM farmer_profiles fp
                    LEFT JOIN users a ON fp.associate_id = a.id
                    LEFT JOIN users ua ON a.user_id = ua.id" . $this->tenantSql();
            $params = [];
            if ($this->tenantId() > 1) $params[] = $this->tenantId();
            $conditions = [];

            if (!empty($filters['status'])) {
                $conditions[] = "fp.status = ?";
                $params[] = $filters['status'];
            }

            if (!empty($filters['district'])) {
                $conditions[] = "fp.district LIKE ?";
                $params[] = '%' . $filters['district'] . '%';
            }

            if (!empty($filters['state'])) {
                $conditions[] = "fp.state = ?";
                $params[] = $filters['state'];
            }

            if (!empty($filters['search'])) {
                $search = '%' . $filters['search'] . '%';
                $conditions[] = "(fp.full_name LIKE ? OR fp.farmer_number LIKE ? OR fp.phone LIKE ? OR fp.village LIKE ?)";
                $params = array_merge($params, [$search, $search, $search, $search]);
            }

            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }

            $sql .= " ORDER BY fp.created_at DESC";

            // Count total
            $countSql = "SELECT COUNT(*) FROM farmer_profiles fp
                         LEFT JOIN users a ON fp.associate_id = a.id
                         LEFT JOIN users ua ON a.user_id = ua.id" . $this->tenantSql();
            if (!empty($conditions)) {
                $countSql .= " WHERE " . implode(" AND ", $conditions);
            }
            $countParams = $params;
            if ($this->tenantId() > 1) $countParams[] = $this->tenantId();
            $total = (int) $db->fetchColumn($countSql, $countParams);

            $page = max(1, (int) ($_GET['page'] ?? 1));
            $perPage = min(100, max(1, $perPage));
            $offset = ($page - 1) * $perPage;
            $sql .= " LIMIT " . (int) $perPage . " OFFSET " . (int) $offset;
            $rows = $db->fetchAll($sql, $params);

            return [
                'data' => $rows ?: [],
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => max(1, (int) ceil($total / $perPage))
            ];
        });
    }

    /**
     * Get farmer by ID with full details
     */
    public function getFarmer(int $id): ?array
    {
        $cacheKey = "farmer:{$id}";

        return $this->cacheRemember($cacheKey, $this->cacheTtl, function () use ($id) {
            $db = $this->db();
            $sql = "SELECT fp.*, ua.name as associate_name, u.name as created_by_name
                    FROM farmer_profiles fp
                    LEFT JOIN users a ON fp.associate_id = a.id
                    LEFT JOIN users ua ON a.user_id = ua.id
                    LEFT JOIN users u ON fp.created_by = u.id
                    WHERE fp.id = ?";
            $farmer = $db->fetch($sql, [$id]);

            if (!$farmer) {
                return null;
            }

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
            $db = $this->db();
            $db->beginTransaction();

            // Generate unique farmer number if not provided
            if (empty($data['farmer_number'])) {
                $data['farmer_number'] = $this->generateFarmerNumber();
            }

            // Validate required fields
            $this->validateFarmerData($data);

            $farmerId = $db->insert('farmer_profiles', array_merge([
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
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ], $this->tenantInsertData()));

            $this->clearFarmerCache();

            $db->commit();

            error_log('Farmer created successfully: id=' . $farmerId . ' number=' . $data['farmer_number']);

            return $farmerId;
        } catch (\Exception $e) {
            $this->db()->rollBack();
            error_log('Error creating farmer: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update farmer information
     */
    public function updateFarmer(int $id, array $data): bool
    {
        try {
            $db = $this->db();
            $db->beginTransaction();

            $existing = $db->table('farmer_profiles')->where('id', $id)->first();
            if (!$existing) {
                throw new \Exception("Farmer with ID {$id} not found");
            }

            $db->table('farmer_profiles')
                ->where('id', $id)
                ->update([
                    'full_name' => $data['full_name'] ?? $existing['full_name'],
                    'father_name' => $data['father_name'] ?? $existing['father_name'],
                    'spouse_name' => $data['spouse_name'] ?? $existing['spouse_name'],
                    'date_of_birth' => $data['date_of_birth'] ?? $existing['date_of_birth'],
                    'gender' => $data['gender'] ?? $existing['gender'],
                    'phone' => $data['phone'] ?? $existing['phone'],
                    'alternate_phone' => $data['alternate_phone'] ?? $existing['alternate_phone'],
                    'email' => $data['email'] ?? $existing['email'],
                    'address' => $data['address'] ?? $existing['address'],
                    'village' => $data['village'] ?? $existing['village'],
                    'post_office' => $data['post_office'] ?? $existing['post_office'],
                    'tehsil' => $data['tehsil'] ?? $existing['tehsil'],
                    'district' => $data['district'] ?? $existing['district'],
                    'state' => $data['state'] ?? $existing['state'],
                    'pincode' => $data['pincode'] ?? $existing['pincode'],
                    'aadhar_number' => $data['aadhar_number'] ?? $existing['aadhar_number'],
                    'pan_number' => $data['pan_number'] ?? $existing['pan_number'],
                    'voter_id' => $data['voter_id'] ?? $existing['voter_id'],
                    'bank_account_number' => $data['bank_account_number'] ?? $existing['bank_account_number'],
                    'bank_name' => $data['bank_name'] ?? $existing['bank_name'],
                    'ifsc_code' => $data['ifsc_code'] ?? $existing['ifsc_code'],
                    'account_holder_name' => $data['account_holder_name'] ?? $existing['account_holder_name'],
                    'total_land_holding' => $data['total_land_holding'] ?? $existing['total_land_holding'],
                    'cultivated_area' => $data['cultivated_area'] ?? $existing['cultivated_area'],
                    'irrigated_area' => $data['irrigated_area'] ?? $existing['irrigated_area'],
                    'non_irrigated_area' => $data['non_irrigated_area'] ?? $existing['non_irrigated_area'],
                    'crop_types' => isset($data['crop_types']) ? json_encode($data['crop_types']) : $existing['crop_types'],
                    'farming_experience' => $data['farming_experience'] ?? $existing['farming_experience'],
                    'education_level' => $data['education_level'] ?? $existing['education_level'],
                    'family_members' => $data['family_members'] ?? $existing['family_members'],
                    'family_income' => $data['family_income'] ?? $existing['family_income'],
                    'credit_score' => $data['credit_score'] ?? $existing['credit_score'],
                    'credit_limit' => $data['credit_limit'] ?? $existing['credit_limit'],
                    'outstanding_loans' => $data['outstanding_loans'] ?? $existing['outstanding_loans'],
                    'payment_history' => isset($data['payment_history']) ? json_encode($data['payment_history']) : $existing['payment_history'],
                    'status' => $data['status'] ?? $existing['status'],
                    'associate_id' => $data['associate_id'] ?? $existing['associate_id'],
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            $this->clearFarmerCache($id);

            $db->commit();

            error_log('Farmer updated successfully: id=' . $id);

            return true;
        } catch (\Exception $e) {
            $this->db()->rollBack();
            error_log('Error updating farmer: id=' . $id . ' error=' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get farmer land holdings
     */
    public function getFarmerLandHoldings(int $farmerId, int $limit = null): array
    {
        $db = $this->db();
        $sql = "SELECT * FROM farmer_land_holdings WHERE farmer_id = ? ORDER BY created_at DESC";
        $params = [$farmerId];

        if ($limit) {
            $sql .= " LIMIT " . (int) $limit;
        }

        return $db->fetchAll($sql, $params) ?: [];
    }

    /**
     * Add land holding for farmer
     */
    public function addLandHolding(int $farmerId, array $data): int
    {
        try {
            $db = $this->db();
            $holdingId = $db->insert('farmer_land_holdings', [
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
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ], $this->tenantInsertData()));

            $this->updateFarmerTotalLand($farmerId);
            $this->clearFarmerCache($farmerId);

            error_log('Land holding added: farmer=' . $farmerId . ' holding=' . $holdingId);

            return $holdingId;
        } catch (\Exception $e) {
            error_log('Error adding land holding: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update acquisition status for land holding
     */
    public function updateAcquisitionStatus(int $holdingId, string $status, ?float $amount = null): bool
    {
        try {
            $db = $this->db();
            $updateData = [
                'acquisition_status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($amount !== null) {
                $updateData['acquisition_amount'] = $amount;
                $updateData['payment_received'] = $amount;
            }

            if ($status === 'acquired') {
                $updateData['acquisition_date'] = date('Y-m-d H:i:s');
                $updateData['payment_status'] = 'completed';
            }

            $result = $db->table('farmer_land_holdings')
                ->where('id', $holdingId)
                ->update($updateData);

            if ($result > 0) {
                $holding = $db->fetch("SELECT farmer_id FROM farmer_land_holdings WHERE id = ?", [$holdingId]);
                if ($holding) {
                    $this->updateFarmerTotalLand($holding['farmer_id']);
                    $this->clearFarmerCache($holding['farmer_id']);
                }
            }

            return $result > 0;
        } catch (\Exception $e) {
            error_log('Error updating acquisition status: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get farmer transactions
     */
    public function getFarmerTransactions(int $farmerId, int $limit = null): array
    {
        $db = $this->db();
        $sql = "SELECT * FROM farmer_transactions WHERE farmer_id = ? ORDER BY transaction_date DESC, created_at DESC";
        $params = [$farmerId];

        if ($limit) {
            $sql .= " LIMIT " . (int) $limit;
        }

        return $db->fetchAll($sql, $params) ?: [];
    }

    /**
     * Add transaction for farmer
     */
    public function addTransaction(int $farmerId, array $data): int
    {
        try {
            $db = $this->db();
            $transactionNumber = $data['transaction_number'] ?? $this->generateTransactionNumber();

            $transactionId = $db->insert('farmer_transactions', [
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
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $this->updateFarmerPaymentHistory($farmerId, [
                'transaction_number' => $transactionNumber,
                'amount' => $data['amount'],
                'type' => $data['transaction_type'],
                'date' => $data['transaction_date'],
                'status' => $data['status'] ?? 'completed'
            ]);

            $this->clearFarmerCache($farmerId);

            error_log('Transaction added: farmer=' . $farmerId . ' txn=' . $transactionId);

            return $transactionId;
        } catch (\Exception $e) {
            error_log('Error adding transaction: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get farmer loans
     */
    public function getFarmerLoans(int $farmerId): array
    {
        $db = $this->db();
        return $db->fetchAll(
            "SELECT * FROM farmer_loans WHERE farmer_id = ? ORDER BY sanction_date DESC, created_at DESC",
            [$farmerId]
        ) ?: [];
    }

    /**
     * Get farmer support requests
     */
    public function getFarmerSupportRequests(int $farmerId, int $limit = null): array
    {
        $db = $this->db();
        try {
            $sql = "SELECT * FROM farmer_support_requests WHERE farmer_id = ? ORDER BY created_at DESC";
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        $params = [$farmerId];

        if ($limit) {
            $sql .= " LIMIT " . (int) $limit;
        }

        return $db->fetchAll($sql, $params) ?: [];
    }

    /**
     * Create support request for farmer
     */
    public function createSupportRequest(int $farmerId, array $data): int
    {
        try {
            $db = $this->db();
            $requestNumber = $data['request_number'] ?? $this->generateSupportRequestNumber();

            $requestId = $db->insert('farmer_support_requests', [
                'farmer_id' => $farmerId,
                'request_number' => $requestNumber,
                'request_type' => $data['request_type'],
                'priority' => $data['priority'] ?? 'medium',
                'subject' => $data['subject'],
                'description' => $data['description'],
                'status' => $data['status'] ?? 'open',
                'assigned_to' => $data['assigned_to'] ?? null,
                'created_by' => $data['created_by'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $this->clearFarmerCache($farmerId);

            error_log('Support request created: farmer=' . $farmerId . ' request=' . $requestId);

            return $requestId;
        } catch (\Exception $e) {
            error_log('Error creating support request: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get farmer dashboard data
     */
    public function getFarmerDashboard(int $farmerId): array
    {
        $cacheKey = "farmer_dashboard:{$farmerId}";

        return $this->cacheRemember($cacheKey, $this->cacheTtl, function () use ($farmerId) {
            $db = $this->db();
            $dashboard = [];

            $dashboard['farmer_info'] = $this->getFarmer($farmerId);

            $dashboard['land_summary'] = $db->fetch(
                "SELECT COALESCE(SUM(land_area), 0) as total_area,
                        COALESCE(SUM(CASE WHEN current_status = ? THEN land_area ELSE 0 END), 0) as cultivated_area,
                        COALESCE(SUM(CASE WHEN current_status = ? THEN land_area ELSE 0 END), 0) as under_acquisition,
                        COALESCE(SUM(CASE WHEN acquisition_status = ? THEN land_area ELSE 0 END), 0) as acquired_area
                 FROM farmer_land_holdings WHERE farmer_id = ?",
                ['cultivated', 'under_acquisition', 'acquired', $farmerId]
            );

            $dashboard['transaction_summary'] = $db->fetch(
                "SELECT COALESCE(SUM(CASE WHEN transaction_type = ? AND status = ? THEN amount ELSE 0 END), 0) as total_received,
                        COALESCE(SUM(CASE WHEN transaction_type = ? AND status = ? THEN amount ELSE 0 END), 0) as total_loans,
                        COALESCE(SUM(CASE WHEN transaction_type = ? AND status = ? THEN amount ELSE 0 END), 0) as total_commissions
                 FROM farmer_transactions WHERE farmer_id = ?",
                ['payment', 'completed', 'loan', 'active', 'commission', 'completed', $farmerId]
            );

            $dashboard['recent_transactions'] = $this->getFarmerTransactions($farmerId, 5);

            $dashboard['active_loans'] = $db->fetchAll(
                "SELECT * FROM farmer_loans WHERE farmer_id = ? AND status IN (?, ?) ORDER BY created_at DESC",
                [$farmerId, 'active', 'disbursed']
            ) ?: [];

            try {
                $dashboard['support_summary'] = $db->fetch(
                    "SELECT COUNT(*) as total_requests,
                            COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as open_requests,
                            COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as resolved_requests
                     FROM farmer_support_requests WHERE farmer_id = ?",
                    ['open', 'resolved', $farmerId]
                );
            } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
            error_log($e->getMessage());
            }

            return $dashboard;
        });
    }

    /**
     * Get farmer statistics
     */
    public function getFarmerStats(): array
    {
        $cacheKey = 'farmer_stats';

        return $this->cacheRemember($cacheKey, $this->cacheTtl, function () {
            $db = $this->db();
            try {
                return [
                    'total_farmers' => (int) $db->fetchColumn("SELECT COUNT(*) FROM farmer_profiles"),
                    'active_farmers' => (int) $db->fetchColumn("SELECT COUNT(*) FROM farmer_profiles WHERE status = ?", ['active']),
                    'total_land_area' => (float) ($db->fetchColumn("SELECT COALESCE(SUM(land_area), 0) FROM farmer_land_holdings") ?? 0),
                    'acquired_land_area' => (float) ($db->fetchColumn("SELECT COALESCE(SUM(land_area), 0) FROM farmer_land_holdings WHERE acquisition_status = ?", ['acquired']) ?? 0),
                    'total_payments' => (float) ($db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM farmer_transactions WHERE transaction_type = ? AND status = ?", ['payment', 'completed']) ?? 0),
                    'pending_support_requests' => (int) $db->fetchColumn("SELECT COUNT(*) FROM farmer_support_requests WHERE status = ?", ['open']),
                    'active_loans' => (int) $db->fetchColumn("SELECT COUNT(*) FROM farmer_loans WHERE status IN (?, ?)", ['active', 'disbursed'])
                ];
            } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
            error_log($e->getMessage());
            }
        });
    }

    /**
     * Generate unique farmer number
     */
    private function generateFarmerNumber(): string
    {
        $prefix = 'F';
        $year = date('Y');
        $db = $this->db();
        $count = (int) $db->fetchColumn(
            "SELECT COUNT(*) FROM farmer_profiles WHERE farmer_number LIKE ?",
            [$prefix . $year . '%']
        );
        return $prefix . $year . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique transaction number
     */
    private function generateTransactionNumber(): string
    {
        $prefix = 'TXN';
        $date = date('Ymd');
        $db = $this->db();
        $count = (int) $db->fetchColumn(
            "SELECT COUNT(*) FROM farmer_transactions WHERE transaction_number LIKE ?",
            [$prefix . $date . '%']
        );
        return $prefix . $date . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique support request number
     */
    private function generateSupportRequestNumber(): string
    {
        $prefix = 'SR';
        $year = date('Y');
        $db = $this->db();
        try {
            $count = (int) $db->fetchColumn(
                "SELECT COUNT(*) FROM farmer_support_requests WHERE request_number LIKE ?",
                [$prefix . $year . '%']
            );
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        return $prefix . $year . str_pad($count + 1, 5, '0', STR_PAD_LEFT);
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

        if (!preg_match('/^[0-9]{10,15}$/', $data['phone'])) {
            throw new \Exception('Invalid phone number format');
        }

        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('Invalid email format');
        }

        $db = $this->db();
        if (!empty($data['farmer_number'])) {
            $exists = $db->fetchColumn(
                "SELECT 1 FROM farmer_profiles WHERE farmer_number = ? AND id != ? LIMIT 1",
                [$data['farmer_number'], $data['id'] ?? 0]
            );
            if ($exists) {
                throw new \Exception('Farmer number already exists');
            }
        }

        $exists = $db->fetchColumn(
            "SELECT 1 FROM farmer_profiles WHERE phone = ? AND id != ? LIMIT 1",
            [$data['phone'], $data['id'] ?? 0]
        );
        if ($exists) {
            throw new \Exception('Phone number already exists');
        }
    }

    /**
     * Update farmer's total land holding
     */
    private function updateFarmerTotalLand(int $farmerId): void
    {
        $db = $this->db();
        $totalLand = (float) ($db->fetchColumn(
            "SELECT COALESCE(SUM(land_area), 0) FROM farmer_land_holdings WHERE farmer_id = ?",
            [$farmerId]
        ) ?? 0);

        $db->execute("UPDATE farmer_profiles SET total_land_holding = ? WHERE id = ?", [$totalLand, $farmerId]);
    }

    /**
     * Update farmer payment history
     */
    private function updateFarmerPaymentHistory(int $farmerId, array $transaction): void
    {
        $db = $this->db();
        $farmer = $db->fetch("SELECT payment_history FROM farmer_profiles WHERE id = ?", [$farmerId]);

        if (!$farmer) {
            return;
        }

        $paymentHistory = json_decode($farmer['payment_history'] ?? '[]', true);
        $paymentHistory[] = $transaction;

        // Keep only last 10 transactions
        $paymentHistory = array_slice($paymentHistory, -10);

        $db->execute("UPDATE farmer_profiles SET payment_history = ? WHERE id = ?", [json_encode($paymentHistory), $farmerId]);
    }

    /**
     * Clear farmer cache
     */
    private function clearFarmerCache(?int $farmerId = null): void
    {
        if ($farmerId) {
            $this->cacheForget("farmer:{$farmerId}");
            $this->cacheForget("farmer_dashboard:{$farmerId}");
        }

        $this->cacheForget('farmer_stats');
        $this->cacheFlush();
    }

    /**
     * Search farmers
     */
    public function searchFarmers(string $query, array $filters = []): array
    {
        try {
            $db = \App\Core\Database\Database::getInstance();
            $sql = "SELECT fp.*, ua.name as associate_name
                    FROM farmer_profiles fp
                    LEFT JOIN users a ON fp.associate_id = a.id
                    LEFT JOIN users ua ON a.user_id = ua.id
                    WHERE (fp.full_name LIKE ? OR fp.farmer_number LIKE ? OR fp.phone LIKE ? OR fp.village LIKE ? OR fp.aadhar_number LIKE ?)";
            $params = array_fill(0, 5, '%' . $query . '%');

            if (!empty($filters['status'])) {
                $sql .= " AND fp.status = ?";
                $params[] = $filters['status'];
            }
            if (!empty($filters['district'])) {
                $sql .= " AND fp.district = ?";
                $params[] = $filters['district'];
            }

            $sql .= " ORDER BY fp.full_name LIMIT 50";
            $rows = $db->fetchAll($sql, $params);
            return $rows ?: [];
        } catch (\Exception $e) {
            error_log('FarmerService::searchFarmers error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Delete farmer
     */
    public function deleteFarmer(int $id): bool
    {
        try {
            $db = $this->db();
            $deleted = $db->table('farmer_profiles')->where('id', $id)->delete();

            if ($deleted > 0) {
                $this->cacheForget('farmer:' . $id);
                $this->cacheForget('farmer_stats');
            }

            return $deleted > 0;
        } catch (\Exception $e) {
            error_log('Failed to delete farmer: id=' . $id . ' error=' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get farmer statistics (alias)
     */
    public function getStatistics(): array
    {
        return $this->getFarmerStats();
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
                        $updated = $this->db()->execute(
                            "UPDATE farmer_profiles SET status = ?, updated_at = ? WHERE id = ?",
                            ['active', date('Y-m-d H:i:s'), $farmerId]
                        );
                        $results[] = ['id' => $farmerId, 'success' => (bool) ($updated !== false), 'action' => 'activated'];
                        break;

                    case 'deactivate':
                        $updated = $this->db()->execute(
                            "UPDATE farmer_profiles SET status = ?, updated_at = ? WHERE id = ?",
                            ['inactive', date('Y-m-d H:i:s'), $farmerId]
                        );
                        $results[] = ['id' => $farmerId, 'success' => (bool) ($updated !== false), 'action' => 'deactivated'];
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

        $this->cacheForget('farmer_stats');

        return $results;
    }
}
