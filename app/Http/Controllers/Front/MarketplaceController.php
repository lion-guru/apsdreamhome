<?php
namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Services\MarketplaceService;
use App\Services\ResellTransactionService;
use App\Services\NotificationService;

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
}