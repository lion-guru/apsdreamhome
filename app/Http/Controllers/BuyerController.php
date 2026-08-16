<?php

namespace App\Http\Controllers;

use App\Core\Database\Database;

/**
 * Buyer Controller — Handle buyer interests and property matching
 */
class BuyerController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Buyer dashboard — list interests + matched properties
     * GET /buyer/dashboard
     */
    public function dashboard()
    {
        @session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        $userId = $_SESSION['user_id'];

        $db = Database::getInstance();
        $interests = [];
        $matched = [];

        try {
            $interests = $db->fetchAll(
                "SELECT * FROM buyer_interests WHERE user_id = ? ORDER BY created_at DESC",
                [$userId]
            );
        } catch (\Throwable $e) { error_log('BuyerController dashboard: ' . $e->getMessage()); }

        try {
            $matched = $db->fetchAll(
                "SELECT bi.*, up.name, up.address, up.price, up.image, up.property_type
                 FROM buyer_interests bi
                 LEFT JOIN user_properties up ON bi.matched_property_id = up.id
                 WHERE bi.user_id = ? AND bi.status = 'matched'
                 ORDER BY bi.updated_at DESC",
                [$userId]
            );
        } catch (\Throwable $e) { error_log('BuyerController matched: ' . $e->getMessage()); }

        $this->render('buyer/dashboard', [
            'page_title' => 'Buyer Dashboard',
            'interests' => $interests,
            'matched' => $matched,
            'total_interests' => count($interests),
            'total_matched' => count($matched),
        ], 'layouts/base');
    }

    /**
     * Submit buyer interest
     * POST /buyer/interest/submit
     */
    public function submitInterest()
    {
        @session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $propertyType = trim($_POST['property_type'] ?? '');
        $budgetMin = (float)($_POST['budget_min'] ?? 0);
        $budgetMax = (float)($_POST['budget_max'] ?? 0);
        $location = trim($_POST['preferred_location'] ?? '');
        $area = trim($_POST['preferred_area'] ?? '');
        $areaMin = (int)($_POST['area_min'] ?? 0);
        $areaMax = (int)($_POST['area_max'] ?? 0);
        $bedrooms = (int)($_POST['bedrooms_needed'] ?? 0);
        $requirements = trim($_POST['requirements'] ?? '');

        if ($budgetMax <= 0) $budgetMax = $budgetMin > 0 ? $budgetMin * 1.5 : 0;

        try {
            $db = Database::getInstance();
            $db->insert('buyer_interests', [
                'user_id'            => $userId,
                'property_type'      => $propertyType,
                'budget_min'         => $budgetMin,
                'budget_max'         => $budgetMax,
                'preferred_location' => $location,
                'preferred_area'     => $area,
                'area_min'           => $areaMin,
                'area_max'           => $areaMax,
                'bedrooms_needed'    => $bedrooms,
                'requirements'       => $requirements,
                'status'             => 'active',
                'created_at'         => date('Y-m-d H:i:s'),
            ]);

            // Auto-match with available properties
            $this->autoMatch($userId);

            $_SESSION['success'] = 'Your property requirement has been submitted! We will find matching properties.';
        } catch (\Throwable $e) {
            error_log('BuyerController submitInterest: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to submit. Please try again.';
        }

        header('Location: ' . BASE_URL . '/buyer/dashboard');
        exit;
    }

    /**
     * Auto-match buyer interest with available properties
     */
    private function autoMatch($userId)
    {
        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();

            // Get active unmatched interests
            $interests = $pdo->prepare("SELECT * FROM buyer_interests WHERE user_id = ? AND status = 'active' ORDER BY created_at DESC LIMIT 5");
            $interests->execute([$userId]);
            $interests = $interests->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($interests as $interest) {
                $where = "WHERE up.status = 'active' AND up.property_type = ?";
                $params = [$interest['property_type']];

                if ($interest['budget_min'] > 0) {
                    $where .= " AND up.price >= ?";
                    $params[] = $interest['budget_min'];
                }
                if ($interest['budget_max'] > 0) {
                    $where .= " AND up.price <= ?";
                    $params[] = $interest['budget_max'];
                }
                if (!empty($interest['preferred_location'])) {
                    $where .= " AND up.address LIKE ?";
                    $params[] = "%{$interest['preferred_location']}%";
                }
                if ($interest['area_min'] > 0) {
                    $where .= " AND up.area_sqft >= ?";
                    $params[] = $interest['area_min'];
                }

                $match = $pdo->prepare("SELECT up.id FROM user_properties up $where LIMIT 1");
                $match->execute($params);
                $matched = $match->fetch(\PDO::FETCH_ASSOC);

                if ($matched) {
                    $pdo->prepare("UPDATE buyer_interests SET status = 'matched', matched_property_id = ?, updated_at = NOW() WHERE id = ?")
                        ->execute([$matched['id'], $interest['id']]);
                }
            }
        } catch (\Throwable $e) {
            error_log('BuyerController autoMatch: ' . $e->getMessage());
        }
    }
}
