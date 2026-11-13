<?php

namespace App\Http\Controllers\Admin;

use App\Services\FestivalOfferService;

/**
 * Admin CRUD & Management for Customer-Facing Promotional & Festival Offers
 * (Property discounts, festive promo codes, and plot booking concessions).
 */
class PromotionalOfferController extends AdminController
{
    private FestivalOfferService $festivalService;

    public function __construct()
    {
        parent::__construct();
        $this->festivalService = new FestivalOfferService($this->db instanceof \PDO ? $this->db : null);
    }

    public function index()
    {
        $this->requireAdmin();
        try {
            $offers = $this->db->fetchAll("
                SELECT po.*, c.name AS colony_name
                FROM promotional_offers po
                LEFT JOIN colonies c ON c.id = po.colony_id
                ORDER BY CASE WHEN po.status = 'active' AND (po.valid_until IS NULL OR po.valid_until >= CURDATE()) THEN 0 ELSE 1 END,
                         po.valid_until DESC, po.id DESC
                LIMIT 200
            ") ?? [];
        } catch (\Throwable $e) {
            $offers = [];
        }

        // Fetch colonies for scoping dropdown
        try {
            $colonies = $this->db->fetchAll("SELECT id, name FROM colonies WHERE deleted_at IS NULL ORDER BY name ASC") ?? [];
        } catch (\Throwable $e) {
            $colonies = [];
        }

        // Aggregate high-level stats
        $stats = [
            'active_offers'     => 0,
            'total_redeemed'    => 0,
            'total_saved_est'   => 0.0,
            'unique_codes'      => 0,
        ];
        $today = date('Y-m-d');
        foreach ($offers as $o) {
            $isActive = ($o['status'] ?? '') === 'active' && (empty($o['valid_until']) || $o['valid_until'] >= $today);
            if ($isActive) {
                $stats['active_offers']++;
            }
            $redeemed = (int)($o['times_redeemed'] ?? 0);
            $stats['total_redeemed'] += $redeemed;
            if ($o['discount_type'] === 'flat_amount') {
                $stats['total_saved_est'] += ($redeemed * (float)($o['discount_value'] ?? 0));
            }
            if (!empty($o['promo_code'])) {
                $stats['unique_codes']++;
            }
        }

        return $this->render('admin/promotional_offers/index', [
            'page_title' => 'Customer Festival & Promotional Offers',
            'offers'     => $offers,
            'colonies'   => $colonies,
            'stats'      => $stats,
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/promotional-offers');
        }
        $this->validateCsrfOrFail();

        $title = trim($_POST['title'] ?? '');
        if ($title === '') {
            $this->setFlash('error', 'Offer title is required');
            $this->redirect('/admin/promotional-offers');
        }

        $promoCode = strtoupper(preg_replace('/[^A-Za-z0-9_]/', '', trim($_POST['promo_code'] ?? '')));
        $discountType = in_array($_POST['discount_type'] ?? '', ['percentage', 'flat_amount', 'per_sqft', 'gift_item', 'cashback_wallet'], true)
            ? $_POST['discount_type']
            : 'percentage';

        $discountValue = (float)($_POST['discount_value'] ?? 0.0);
        $discountPercentage = ($discountType === 'percentage')
            ? (float)($_POST['discount_percentage'] ?? $discountValue)
            : (float)($_POST['discount_percentage'] ?? 0.0);

        $minBooking = (float)($_POST['min_booking_amount'] ?? 0.0);
        $colonyId = !empty($_POST['colony_id']) ? (int)$_POST['colony_id'] : null;
        $validFrom = !empty($_POST['valid_from']) ? $_POST['valid_from'] : date('Y-m-d');
        $validUntil = !empty($_POST['valid_until']) ? $_POST['valid_until'] : date('Y-m-d', strtotime('+30 days'));
        $maxRedemptions = (int)($_POST['max_redemptions'] ?? 0);
        $festivalName = trim($_POST['festival_name'] ?? '');
        if ($festivalName === '') {
            $festivalName = 'Festival Special';
        }

        try {
            $this->db->query("
                INSERT INTO promotional_offers (
                    tenant_id, title, festival_name, description,
                    discount_type, discount_value, discount_percentage,
                    min_booking_amount, colony_id, promo_code,
                    valid_from, valid_until, max_redemptions, times_redeemed,
                    status, created_at
                ) VALUES (
                    1, ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?, 0,
                    'active', NOW()
                )
            ", [
                $title, $festivalName, trim($_POST['description'] ?? ''),
                $discountType, $discountValue, $discountPercentage,
                $minBooking, $colonyId, $promoCode !== '' ? $promoCode : null,
                $validFrom, $validUntil, $maxRedemptions
            ]);

            $this->setFlash('success', "Customer festival offer '{$title}' created successfully!");
        } catch (\Throwable $e) {
            $this->setFlash('error', 'Failed to create offer: ' . $e->getMessage());
        }

        $this->redirect('/admin/promotional-offers');
    }

    public function setStatus()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/promotional-offers');
        }
        $this->validateCsrfOrFail();

        $status = in_array($_POST['status'] ?? '', ['active', 'inactive', 'expired'], true) ? $_POST['status'] : 'inactive';
        try {
            $this->db->query("UPDATE promotional_offers SET status=?, updated_at=NOW() WHERE id=?", [$status, (int)($_POST['id'] ?? 0)]);
            $this->setFlash('success', 'Offer status updated');
        } catch (\Throwable $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/promotional-offers');
    }

    public function delete()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/promotional-offers');
        }
        $this->validateCsrfOrFail();

        try {
            $this->db->query("DELETE FROM promotional_offers WHERE id=?", [(int)($_POST['id'] ?? 0)]);
            $this->setFlash('success', 'Promotional offer deleted');
        } catch (\Throwable $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/promotional-offers');
    }

    /**
     * AJAX endpoint to validate customer promo code in real time during booking
     */
    public function validatePromo()
    {
        header('Content-Type: application/json; charset=utf-8');
        $jsonBody = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($jsonBody)) {
            $jsonBody = [];
        }
        $code = trim((string)($_POST['promo_code'] ?? $jsonBody['promo_code'] ?? ($_GET['promo_code'] ?? '')));
        $plotValue = (float)($_POST['plot_value'] ?? $jsonBody['plot_value'] ?? ($_GET['plot_value'] ?? 0.0));
        $rawColonyId = $_POST['colony_id'] ?? $jsonBody['colony_id'] ?? ($_GET['colony_id'] ?? null);
        $colonyId = ($rawColonyId !== null && $rawColonyId !== '') ? (int)$rawColonyId : null;

        if ($code === '') {
            echo json_encode(['valid' => false, 'message' => 'Please enter a promo code']);
            exit;
        }

        $result = $this->festivalService->validateCustomerPromo($code, $plotValue, $colonyId);
        echo json_encode($result);
        exit;
    }
}
