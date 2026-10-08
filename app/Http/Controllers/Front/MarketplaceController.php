<?php
namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Services\MarketplaceService;
use App\Services\ResellTransactionService;
use App\Services\NotificationService;
use App\Services\Gateway\RazorpayService;
use App\Core\Database\Database;
use App\Services\CacheService;

class MarketplaceController extends BaseController
{
    private $marketplaceService;
    private $resellTxnService;

    public function __construct()
    {
        parent::__construct();
        $this->marketplaceService = new MarketplaceService($this->db);
        $this->resellTxnService = new ResellTransactionService($this->db);
    }

    /**
     * Marketplace listing page
     */
    public function index()
    {
        // NOTE: BaseController has no tenantId() helper (it would fatal), so
        // resolve the tenant id safely here instead of calling $this->tenantId().
        $tid = 1;
        try { $tid = \App\Core\Middleware\TenantContext::getId(); } catch (\Throwable $e) { $tid = 1; }
        $tWhere = $tid > 1 ? " AND up.tenant_id = ?" : "";
        $tParams = $tid > 1 ? [$tid] : [];

        // Filters from query string (match the view's filter form names)
        $filters = [
            'type' => trim($_GET['type'] ?? ''),
            'listing_type' => trim($_GET['listing_type'] ?? ''),
            'min_price' => trim($_GET['min_price'] ?? ''),
            'max_price' => trim($_GET['max_price'] ?? ''),
            'location' => trim($_GET['location'] ?? ''),
        ];
        $where = ["up.status = 'approved'"];
        $params = [];
        if ($filters['type'] !== '') { $where[] = "up.property_type = ?"; $params[] = $filters['type']; }
        if ($filters['listing_type'] !== '') { $where[] = "up.listing_type = ?"; $params[] = $filters['listing_type']; }
        if (is_numeric($filters['min_price'])) { $where[] = "up.price >= ?"; $params[] = (float)$filters['min_price']; }
        if (is_numeric($filters['max_price'])) { $where[] = "up.price <= ?"; $params[] = (float)$filters['max_price']; }
        if ($filters['location'] !== '') { $where[] = "(up.location LIKE ? OR up.city_name LIKE ? OR up.address LIKE ?)"; $params[] = "%{$filters['location']}%"; $params[] = "%{$filters['location']}%"; $params[] = "%{$filters['location']}%"; }
        $whereSql = implode(' AND ', $where) . $tWhere;
        $allParams = array_merge($params, $tParams);

        // Cache key based on filters + page
        $cacheKey = 'marketplace_listings_' . md5(serialize([$filters, $_GET['page'] ?? 1, $tid]));
        $facetCacheKey = 'marketplace_facets_' . $tid;
        $countCacheKey = 'marketplace_count_' . md5(serialize([$filters, $tid]));

        $listings = [];
        $premiumListings = [];
        $total = 0;
        $propertyTypes = [];
        $listingTypes = [];
        $packages = [];

        try {
            // Cache total count (1 hour)
            $total = (int)CacheService::cache($countCacheKey, 3600, function() use ($whereSql, $allParams) {
                return (int)$this->db->fetchColumn("SELECT COUNT(*) FROM user_properties up WHERE {$whereSql}", $allParams);
            });

            $currentPage = max(1, (int)($_GET['page'] ?? 1));
            $perPage = 24;
            $totalPages = max(1, (int)ceil($total / $perPage));
            if ($currentPage > $totalPages) $currentPage = $totalPages;
            $offset = ($currentPage - 1) * $perPage;

            // Cache listings per page (5 minutes)
            $listings = CacheService::cache($cacheKey, 300, function() use ($whereSql, $allParams, $perPage, $offset) {
                $stmt = $this->db->prepare("
                    SELECT up.*, u.name as seller_name
                    FROM user_properties up
                    LEFT JOIN users u ON up.user_id = u.id
                    WHERE {$whereSql}
                    ORDER BY up.is_premium DESC, up.is_featured DESC, up.is_urgent DESC, up.created_at DESC
                    LIMIT {$perPage} OFFSET {$offset}
                ");
                $stmt->execute($allParams);
                return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            });

            // Premium carousel: boosted/featured approved listings (unfiltered) - cached 10 min
            $premiumListings = CacheService::cache('marketplace_premium_' . $tid, 600, function() use ($tWhere, $tParams) {
                try {
                    $stmt = $this->db->prepare("
                        SELECT up.*, u.name as seller_name
                        FROM user_properties up
                        LEFT JOIN users u ON up.user_id = u.id
                        WHERE up.status = 'approved' AND (up.is_premium = 1 OR up.is_featured = 1 OR up.is_urgent = 1){$tWhere}
                        ORDER BY up.is_premium DESC, up.created_at DESC
                        LIMIT 10
                    ");
                    $stmt->execute($tParams);
                    return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
                } catch (\Throwable $e) { error_log("MarketplaceController::index premium: " . $e->getMessage()); return []; }
            });

            // Facets cached 1 hour
            $propertyTypes = CacheService::cache('marketplace_property_types_' . $tid, 3600, function() use ($tWhere, $tParams) {
                try {
                    return array_column($this->db->fetchAll("SELECT DISTINCT up.property_type FROM user_properties up WHERE up.status = 'approved'{$tWhere} ORDER BY up.property_type", $tParams) ?: [], 'property_type');
                } catch (\Throwable $e) { error_log("MarketplaceController::index facets: " . $e->getMessage()); return []; }
            });
            $listingTypes = CacheService::cache('marketplace_listing_types_' . $tid, 3600, function() use ($tWhere, $tParams) {
                try {
                    return array_column($this->db->fetchAll("SELECT DISTINCT up.listing_type FROM user_properties up WHERE up.status = 'approved'{$tWhere} ORDER BY up.listing_type", $tParams) ?: [], 'listing_type');
                } catch (\Throwable $e) { error_log("MarketplaceController::index facets: " . $e->getMessage()); return []; }
            });

            try {
                $packages = $this->db->fetchAll("SELECT name, price, badge_label FROM premium_packages WHERE is_active = 1 ORDER BY priority_order ASC, price ASC LIMIT 5") ?: [];
            } catch (\Throwable $e) { /* packages table optional */ }
        } catch (\Throwable $e) {
            error_log("MarketplaceController::index: " . $e->getMessage());
            $listings = [];
            $currentPage = 1;
            $totalPages = 1;
        }

        $this->layout = 'layouts/base';
        $this->render('pages/marketplace', [
            'page_title' => 'Marketplace - APS Dream Home',
            'base' => defined('BASE_URL') ? BASE_URL : '',
            'properties' => $listings,
            'listings' => $listings,
            'premiumListings' => $premiumListings,
            'packages' => $packages,
            'propertyTypes' => $propertyTypes,
            'listingTypes' => $listingTypes,
            'filters' => $filters,
            'total' => $total,
            'currentPage' => $currentPage ?? 1,
            'totalPages' => $totalPages ?? 1,
        ]);
    }

    /**
     * Marketplace property detail page
     */
    public function detail($id)
    {
        $propertyId = (int)$id;
        if ($propertyId <= 0) {
            header('Location: ' . BASE_URL . '/marketplace');
            exit;
        }

        try {
            $tid = $this->tenantId();
            $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
            $tenantParams = $tid > 1 ? [$tid] : [];

            $stmt = $this->db->prepare("
                SELECT up.*, u.name as seller_name, u.phone as seller_phone
                FROM user_properties up
                LEFT JOIN users u ON up.user_id = u.id
                WHERE up.id = ?{$tenantWhere}
                LIMIT 1
            ");
            $stmt->execute(array_merge([$propertyId], $tenantParams));
            $property = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$property) {
                header('Location: ' . BASE_URL . '/marketplace');
                exit;
            }

            // Track view
            try {
                $this->marketplaceService->trackInterest([
                    'property_id' => $propertyId,
                    'listing_type' => 'user',
                    'user_id' => $_SESSION['user_id'] ?? null,
                    'session_id' => session_id(),
                    'interest_type' => 'view',
                ]);
            } catch (\Throwable $e) {
                // Non-fatal
            }
        } catch (\Throwable $e) {
            error_log("MarketplaceController::detail: " . $e->getMessage());
            header('Location: ' . BASE_URL . '/marketplace');
            exit;
        }

        $this->layout = 'layouts/base';
        $this->render('pages/marketplace-detail', [
            'page_title' => ($property['name'] ?? 'Property') . ' - APS Dream Home',
            'property' => $property,
        ]);
    }

    /**
     * Track buyer interest (AJAX)
     */
    public function trackInterest()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid method'], 400);
        }

        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $data['user_id'] = $_SESSION['user_id'] ?? null;
        $data['session_id'] = session_id();

        $result = $this->marketplaceService->trackInterest($data);
        return $this->jsonResponse($result);
    }

    /**
     * Save/unsave property to shortlist (AJAX)
     */
    public function toggleSave()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid method'], 400);
        }

        if (empty($_SESSION['user_id'])) {
            return $this->jsonResponse(['success' => false, 'message' => 'Please login first'], 401);
        }

        $propertyId = (int)($_POST['property_id'] ?? 0);
        $listingType = $_POST['listing_type'] ?? 'user';
        $notes = $_POST['notes'] ?? null;

        if ($propertyId <= 0) {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid property'], 400);
        }

        $result = $this->marketplaceService->toggleSaveProperty((int)$_SESSION['user_id'], $propertyId, $listingType, $notes);

        // Send notification if property was saved
        if ($result['success'] && $result['action'] === 'saved') {
            try {
                $notif = new \App\Services\NotificationService($this->db);
                $notif->send((int)$_SESSION['user_id'], 'push', 'Property Saved', 'Property has been added to your saved list.', [
                    'template_code' => 'property_saved',
                    'event_type' => 'property_saved',
                    'property_id' => $propertyId,
                    'listing_type' => $listingType,
                ]);
            } catch (\Throwable $e) {
                error_log('toggleSave notification error: ' . $e->getMessage());
            }
        }

        return $this->jsonResponse($result);
    }

    /**
     * User's saved properties page
     */
    public function savedProperties()
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $listingType = $_GET['type'] ?? '';
        $properties = $this->marketplaceService->getSavedProperties((int)$_SESSION['user_id'], $listingType);

        $this->layout = 'layouts/customer';
        $this->render('pages/user/saved_properties', [
            'page_title' => 'Saved Properties - APS Dream Home',
            'properties' => $properties,
            'current_page' => 'saved',
        ]);
    }

    /**
     * Capture lead from inquiry (called by PropertyPageController)
     */
    public function captureLead()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid method'], 400);
        }

        $data = $_POST;
        $data['user_id'] = $_SESSION['user_id'] ?? null;
        $data['created_by'] = $_SESSION['user_id'] ?? 0;

        $result = $this->marketplaceService->captureLeadFromInquiry($data);
        return $this->jsonResponse($result);
    }

    /**
     * Follow-up schedule page (for agents/associates)
     */
    public function followups()
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $todayFollowups = $this->marketplaceService->getTodayFollowups((int)$_SESSION['user_id']);
        $overdueFollowups = $this->marketplaceService->getOverdueFollowups((int)$_SESSION['user_id']);

        $this->layout = 'layouts/customer';
        $this->render('pages/user/followups', [
            'page_title' => 'Follow-ups - APS Dream Home',
            'today_followups' => $todayFollowups,
            'overdue_followups' => $overdueFollowups,
            'current_page' => 'followups',
        ]);
    }

    /**
     * Complete follow-up (AJAX)
     */
    public function completeFollowup()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid method'], 400);
        }

        $followupId = (int)($_POST['followup_id'] ?? 0);
        $outcome = $_POST['outcome'] ?? '';
        $notes = $_POST['notes'] ?? '';

        if ($followupId <= 0 || empty($outcome)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid data'], 400);
        }

        $result = $this->marketplaceService->completeFollowup($followupId, $outcome, $notes, (int)($_SESSION['user_id'] ?? 0));
        return $this->jsonResponse($result);
    }

    /**
     * Close resale deal (admin/seller)
     */
    public function closeDeal()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid method'], 400);
        }

        $data = [
            'property_id' => (int)($_POST['property_id'] ?? 0),
            'buyer_id' => (int)($_POST['buyer_id'] ?? 0),
            'final_price' => (float)($_POST['final_price'] ?? 0),
            'payment_method' => $_POST['payment_method'] ?? 'bank_transfer',
            'payment_reference' => $_POST['payment_reference'] ?? '',
            'notes' => $_POST['notes'] ?? '',
            'created_by' => $_SESSION['user_id'] ?? 0,
        ];

        if ($data['property_id'] <= 0 || $data['buyer_id'] <= 0 || $data['final_price'] <= 0) {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid data'], 400);
        }

        $result = $this->resellTxnService->closeResellDeal($data);
        return $this->jsonResponse($result);
    }

    /**
     * Close user property deal (admin/seller)
     */
    public function closeUserPropertyDeal()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid method'], 400);
        }

        $data = [
            'property_id' => (int)($_POST['property_id'] ?? 0),
            'buyer_id' => (int)($_POST['buyer_id'] ?? 0),
            'final_price' => (float)($_POST['final_price'] ?? 0),
            'payment_method' => $_POST['payment_method'] ?? 'bank_transfer',
            'payment_reference' => $_POST['payment_reference'] ?? '',
            'notes' => $_POST['notes'] ?? '',
            'created_by' => $_SESSION['user_id'] ?? 0,
        ];

        if ($data['property_id'] <= 0 || $data['buyer_id'] <= 0 || $data['final_price'] <= 0) {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid data'], 400);
        }

        $result = $this->resellTxnService->closeUserPropertyDeal($data);
        return $this->jsonResponse($result);
    }

    /**
     * Get user transactions
     */
    public function myTransactions()
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $role = $_GET['role'] ?? 'all';
        $transactions = $this->resellTxnService->getUserTransactions((int)$_SESSION['user_id'], $role);

        $this->layout = 'layouts/customer';
        $this->render('pages/user/transactions', [
            'page_title' => 'My Transactions - APS Dream Home',
            'transactions' => $transactions,
            'current_page' => 'transactions',
        ]);
    }

    /**
     * Get smart follow-up templates for a lead
     */
    public function getFollowupTemplates()
    {
        $this->requireLogin();
        $leadId = (int)($_GET['lead_id'] ?? 0);
        $channel = $_GET['channel'] ?? 'all';
        
        if ($leadId <= 0) {
            return $this->jsonResponse(['success' => false, 'message' => 'Lead ID required'], 400);
        }
        
        $result = $this->marketplaceService->getFollowupTemplates($leadId, $channel);
        return $this->jsonResponse(['success' => true, 'data' => $result]);
    }

    /**
     * Create smart follow-up sequence for a lead
     */
    public function createSmartFollowup()
    {
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid method'], 400);
        }
        
        $leadId = (int)($_POST['lead_id'] ?? 0);
        if ($leadId <= 0) {
            return $this->jsonResponse(['success' => false, 'message' => 'Lead ID required'], 400);
        }
        
        $result = $this->marketplaceService->createSmartFollowupSequence($leadId, (int)$_SESSION['user_id']);
        return $this->jsonResponse($result);
    }

/**
     * Trigger activity-based follow-up
     */
    public function triggerActivityFollowup()
    {
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid method'], 400);
        }
        
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $leadId = (int)($input['lead_id'] ?? 0);
        $activity = $input['activity'] ?? '';
        
        if ($leadId <= 0 || $activity === '') {
            return $this->jsonResponse(['success' => false, 'message' => 'Lead ID and activity required'], 400);
        }
        
        $data = $input;
        $data['user_id'] = $_SESSION['user_id'] ?? 0;
        
        $result = $this->marketplaceService->triggerActivityFollowup($leadId, $activity, $data);
        return $this->jsonResponse($result);
    }

    /**
     * Auto-reschedule overdue follow-ups
     */
    public function autoRescheduleFollowups()
    {
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid method'], 400);
        }
        
        $result = $this->marketplaceService->autoRescheduleOverdue((int)($_SESSION['user_id'] ?? 0));
        return $this->jsonResponse($result);
    }

    /**
     * Get follow-up templates for a lead

    /**
     * Boost property listing
     */
    public function boostProperty()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid method'], 400);
        }

        if (empty($_SESSION['user_id'])) {
            return $this->jsonResponse(['success' => false, 'message' => 'Please login'], 401);
        }

        $propertyId = (int)($_POST['property_id'] ?? 0);
        $boostType = $_POST['boost_type'] ?? 'featured';
        $duration = (int)($_POST['duration'] ?? 7);

        if ($propertyId <= 0) {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid property'], 400);
        }

        try {
            $tid = $this->tenantId();
            $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
            $tenantParams = $tid > 1 ? [$tid] : [];

            $boostAmount = match($boostType) {
                'featured' => 499,
                'urgent' => 299,
                'premium' => 999,
                default => 499,
            };

            $expiresAt = date('Y-m-d H:i:s', strtotime("+{$duration} days"));

            $stmt = $this->db->prepare("
                UPDATE user_properties 
                SET is_featured = CASE WHEN ? = 'featured' THEN 1 ELSE is_featured END,
                    is_urgent = CASE WHEN ? = 'urgent' THEN 1 ELSE is_urgent END,
                    is_premium = CASE WHEN ? = 'premium' THEN 1 ELSE is_premium END,
                    boosted_at = NOW(),
                    boost_expires_at = ?,
                    boost_amount = ?,
                    promoted_until = ?,
                    updated_at = NOW()
                WHERE id = ? AND user_id = ?{$tenantWhere}
            ");
            $stmt->execute(array_merge([$boostType, $boostType, $boostType, $expiresAt, $boostAmount, $expiresAt, $propertyId, $_SESSION['user_id']], $tenantParams));

            if ($stmt->rowCount() > 0) {
                // Track revenue
                $this->db->prepare("
                    INSERT INTO platform_revenue (source_type, source_id, amount, description, recorded_at)
                    VALUES ('boost', ?, ?, ?, NOW())
                ")->execute([$propertyId, $boostAmount, "Property boost: {$boostType} for {$duration} days"]);

                return $this->jsonResponse(['success' => true, 'message' => 'Property boosted successfully!']);
            }

            return $this->jsonResponse(['success' => false, 'message' => 'Property not found or not owned by you'], 404);
        } catch (\Throwable $e) {
            error_log("MarketplaceController::boostProperty: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Failed to boost property'], 500);
        }
    }

    /**
     * Initiate payment for property boost
     */
    public function initiateBoostPayment()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid method'], 400);
        }

        if (empty($_SESSION['user_id'])) {
            return $this->jsonResponse(['success' => false, 'message' => 'Please login'], 401);
        }

        $propertyId = (int)($_POST['property_id'] ?? 0);
        $boostType = $_POST['boost_type'] ?? 'featured';
        $duration = (int)($_POST['duration'] ?? 7);

        if ($propertyId <= 0) {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid property'], 400);
        }

        try {
            $tid = $this->tenantId();
            $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
            $tenantParams = $tid > 1 ? [$tid] : [];

            // Verify property ownership
            $stmt = $this->db->prepare("SELECT id, user_id FROM user_properties WHERE id = ? AND user_id = ?{$tenantWhere}");
            $stmt->execute(array_merge([$propertyId, $_SESSION['user_id']], $tenantParams));
            $property = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$property) {
                return $this->jsonResponse(['success' => false, 'message' => 'Property not found or not owned by you'], 404);
            }

            $boostAmount = match($boostType) {
                'featured' => 499,
                'urgent' => 299,
                'premium' => 999,
                default => 499,
            };

            $service = new RazorpayService();
            $resp = $service->createOrder($boostAmount, 'INR', 'BOOST_' . $propertyId . '_' . time(), [
                'property_id' => $propertyId,
                'user_id' => $_SESSION['user_id'],
                'boost_type' => $boostType,
                'duration' => $duration,
                'description' => "Property boost: {$boostType} for {$duration} days",
            ]);

            if (!$resp['success']) {
                return $this->jsonResponse(['success' => false, 'message' => $resp['error'] ?? 'Failed to create payment order'], 502);
            }

            return $this->jsonResponse([
                'success' => true,
                'order_id' => $resp['data']['id'],
                'amount_paise' => $resp['data']['amount'],
                'amount' => $boostAmount,
                'currency' => $resp['data']['currency'] ?? 'INR',
                'key_id' => $service->getKeyId(),
                'property_id' => $propertyId,
                'boost_type' => $boostType,
                'duration' => $duration,
            ]);

        } catch (\Throwable $e) {
            error_log("MarketplaceController::initiateBoostPayment: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Failed to initiate payment'], 500);
        }
    }

    /**
     * Verify payment for property boost
     */
    public function verifyBoostPayment()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid method'], 400);
        }

        $orderId = $_POST['razorpay_order_id'] ?? '';
        $paymentId = $_POST['razorpay_payment_id'] ?? '';
        $signature = $_POST['razorpay_signature'] ?? '';

        if (!$orderId || !$paymentId || !$signature) {
            return $this->jsonResponse(['success' => false, 'message' => 'Missing payment parameters'], 400);
        }

        try {
            $service = new RazorpayService();

            if (!$service->verifyPaymentSignature($orderId, $paymentId, $signature)) {
                return $this->jsonResponse(['success' => false, 'message' => 'Invalid payment signature'], 400);
            }

            // Extract property_id and boost info from order notes or receipt
            $tid = $this->tenantId();
            $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
            $tenantParams = $tid > 1 ? [$tid] : [];

            // Fetch order details from Razorpay to get notes
            $orderResp = $service->fetchOrder($orderId);
            if (!$orderResp['success'] || !isset($orderResp['data']['notes'])) {
                return $this->jsonResponse(['success' => false, 'message' => 'Failed to fetch order details'], 500);
            }

            $notes = $orderResp['data']['notes'] ?? [];
            $propertyId = (int)($notes['property_id'] ?? 0);
            $boostType = $notes['boost_type'] ?? 'featured';
            $duration = (int)($notes['duration'] ?? 7);

            if ($propertyId <= 0) {
                return $this->jsonResponse(['success' => false, 'message' => 'Invalid property in payment order'], 400);
            }

            // Apply boost via shared service (deduplicated logic)
            $result = $this->marketplaceService->applyBoostAfterPayment(
                $propertyId,
                (int)$_SESSION['user_id'],
                $boostType,
                $duration,
                $paymentId,
                $orderId
            );

            if (!$result['success']) {
                $code = str_contains($result['message'], 'not found') ? 404 : 500;
                return $this->jsonResponse(['success' => false, 'message' => $result['message']], $code);
            }

            $boostAmount = \App\Services\MarketplaceService::getBoostAmount($boostType);

            // Send notification
            try {
                $notif = new \App\Services\NotificationService($this->db);
                $notif->send((int)$_SESSION['user_id'], 'email', 'Property Boost Activated Successfully', "Your {$boostType} boost for {$duration} days has been activated!", [
                    'template_code' => 'boost_payment_success',
                    'event_type' => 'boost_payment_success',
                    'property_id' => $propertyId,
                    'boost_type' => $boostType,
                    'duration' => $duration,
                    'amount' => $boostAmount,
                    'payment_id' => $paymentId,
                ]);
                $notif->send((int)$_SESSION['user_id'], 'sms', 'Boost Activated', "Your {$boostType} boost is now active! Paid ₹{$boostAmount}.", [
                    'template_code' => 'boost_payment_success',
                    'event_type' => 'boost_payment_success',
                ]);
                $notif->send((int)$_SESSION['user_id'], 'push', 'Boost Activated', "Your {$boostType} boost is now active!", [
                    'template_code' => 'boost_payment_success',
                    'event_type' => 'boost_payment_success',
                ]);
            } catch (\Throwable $e) {
                error_log('verifyBoostPayment notification error: ' . $e->getMessage());
            }

            return $this->jsonResponse(['success' => true, 'message' => 'Property boosted successfully!', 'payment_id' => $paymentId]);

        } catch (\Throwable $e) {
            error_log("MarketplaceController::verifyBoostPayment: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Payment verification failed'], 500);
        }
    }
}