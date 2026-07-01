<?php

namespace App\Http\Controllers;

use App\Core\Database\Database;

/**
 * Property Commission Controller — Track income from property sales
 */
class PropertyCommissionController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Record a property sale and calculate commission
     * POST /api/property-commission/record
     */
    public function recordSale()
    {
        header('Content-Type: application/json');
        @session_start();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'POST required']);
            exit;
        }

        $propertyId = (int)($_POST['property_id'] ?? 0);
        $salePrice = (float)($_POST['sale_price'] ?? 0);
        $buyerId = (int)($_POST['buyer_id'] ?? 0);
        $sellerId = (int)($_POST['seller_id'] ?? 0);
        $associateId = (int)($_POST['associate_id'] ?? 0);
        $agentId = (int)($_POST['agent_id'] ?? 0);
        $listingType = $_POST['listing_type'] ?? 'sell';

        if ($propertyId <= 0 || $salePrice <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid property or price']);
            exit;
        }

        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();

            // Get commission rate from settings (default 2%)
            $rate = 2.0;
            try {
                $rateRow = $pdo->query("SELECT config_value FROM wallet_configuration WHERE config_key = 'property_commission_rate'")->fetch(\PDO::FETCH_ASSOC);
                if ($rateRow) $rate = (float)$rateRow['config_value'];
            } catch (\Throwable $e) {}

            $commissionAmount = $salePrice * ($rate / 100);
            $companyShare = $commissionAmount * 0.50;   // 50% to company
            $associateShare = $commissionAmount * 0.30;  // 30% to associate
            $agentShare = $commissionAmount * 0.10;      // 10% to agent
            $tdsAmount = $commissionAmount * 0.10;       // 10% TDS
            $gstAmount = $commissionAmount * 0.18;       // 18% GST on commission
            $netPayout = $commissionAmount - $tdsAmount - $gstAmount;

            // Get property type
            $propRow = $pdo->prepare("SELECT property_type FROM user_properties WHERE id = ?");
            $propRow->execute([$propertyId]);
            $prop = $propRow->fetch(\PDO::FETCH_ASSOC);
            $propertyType = $prop['property_type'] ?? 'plot';

            $commissionId = $db->insert('property_commissions', [
                'property_id'       => $propertyId,
                'seller_id'         => $sellerId,
                'buyer_id'          => $buyerId ?: null,
                'associate_id'      => $associateId ?: null,
                'agent_id'          => $agentId ?: null,
                'property_type'     => $propertyType,
                'listing_type'      => $listingType,
                'sale_price'        => $salePrice,
                'commission_rate'   => $rate,
                'commission_amount' => $commissionAmount,
                'company_share'     => $companyShare,
                'associate_share'   => $associateShare,
                'agent_share'       => $agentShare,
                'tds_amount'        => $tdsAmount,
                'gst_amount'        => $gstAmount,
                'net_payout'        => $netPayout,
                'status'            => 'pending',
                'created_at'        => date('Y-m-d H:i:s'),
            ]);

            // Update property status
            $pdo->prepare("UPDATE user_properties SET status = 'sold' WHERE id = ?")->execute([$propertyId]);

            // Credit associate wallet if applicable
            if ($associateId > 0 && $associateShare > 0) {
                try {
                    $db->insert('mlm_commission_ledger', [
                        'user_id'          => $associateId,
                        'booking_id'       => null,
                        'commission_type'  => 'property_sale',
                        'amount'           => $associateShare,
                        'status'           => 'pending',
                        'description'      => "Property commission ({$propertyType} sale @ ₹" . number_format($salePrice) . ")",
                        'metadata'         => json_encode(['property_id' => $propertyId, 'commission_id' => $commissionId]),
                        'created_at'       => date('Y-m-d H:i:s'),
                    ]);
                } catch (\Throwable $e) {
                    error_log('PropertyCommission wallet credit: ' . $e->getMessage());
                }
            }

            echo json_encode([
                'success' => true,
                'commission_id' => $commissionId,
                'commission_amount' => $commissionAmount,
                'company_share' => $companyShare,
                'associate_share' => $associateShare,
                'agent_share' => $agentShare,
                'net_payout' => $netPayout,
            ]);
        } catch (\Throwable $e) {
            error_log('PropertyCommission recordSale: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to record sale']);
        }
        exit;
    }

    /**
     * Get commission summary
     * GET /api/property-commission/summary
     */
    public function summary()
    {
        header('Content-Type: application/json');
        @session_start();

        $userId = $_SESSION['user_id'] ?? 0;
        $role = $_SESSION['role'] ?? '';

        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();

            $where = "";
            $params = [];
            if ($role === 'associate') {
                $where = "WHERE associate_id = ?";
                $params[] = $userId;
            } elseif ($role === 'agent') {
                $where = "WHERE agent_id = ?";
                $params[] = $userId;
            }

            $stmt = $pdo->prepare("SELECT 
                COUNT(*) as total_sales,
                COALESCE(SUM(sale_price), 0) as total_volume,
                COALESCE(SUM(commission_amount), 0) as total_commission,
                COALESCE(SUM(company_share), 0) as total_company,
                COALESCE(SUM(associate_share), 0) as total_associate,
                COALESCE(SUM(agent_share), 0) as total_agent,
                COALESCE(SUM(net_payout), 0) as total_payout
            FROM property_commissions $where");
            $stmt->execute($params);
            $summary = $stmt->fetch(\PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'summary' => $summary]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}
