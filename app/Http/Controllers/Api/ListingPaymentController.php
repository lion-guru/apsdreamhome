<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Traits\TenantAwareTrait;

class ListingPaymentController extends BaseApiController {
    use TenantAwareTrait;

    public function __construct() {
        parent::__construct();
    }

    public function createOrder() {
        $tid = (int)$this->tenantId();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);

        if ($userId <= 0) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Login required']);
            return;
        }

        $input = $this->getJsonInput();
        $packageId = (int)($input['package_id'] ?? 0);
        $propertyId = (int)($input['property_id'] ?? 0);

        if ($packageId <= 0 || $propertyId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            return;
        }

        $package = $this->db->query(
            "SELECT * FROM listing_packages WHERE id = ? AND tenant_id = ? AND status = 'active'",
            [$packageId, $tid]
        )->fetch();

        if (!$package) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Package not found']);
            return;
        }

        $property = $this->db->query(
            "SELECT * FROM user_properties WHERE id = ? AND tenant_id = ?",
            [$propertyId, $tid]
        )->fetch();

        if (!$property) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Property not found']);
            return;
        }

        if ((int)$property['user_id'] !== $userId && (int)($property['posted_by'] ?? 0) !== $userId) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'You can only upgrade your own listings']);
            return;
        }

        if ((float)$package['price'] <= 0) {
            $this->activateListing($propertyId, $package, $userId, $tid, 'free');
            echo json_encode([
                'success' => true,
                'message' => 'Listing upgraded to ' . $package['name'] . ' (free)',
                'payment_required' => false
            ]);
            return;
        }

        $orderId = 'LSM-' . date('YmdHis') . '-' . substr(uniqid(), -6);

        $this->db->query(
            "INSERT INTO payment_orders (order_id, gateway, user_id, customer_name, customer_email, customer_phone, amount, currency, status, description, tenant_id) VALUES (?, 'razorpay', ?, ?, ?, ?, ?, 'INR', 'created', ?, ?)",
            [
                $orderId,
                $userId,
                $property['name'] ?? 'Property',
                '',
                '',
                $package['price'],
                'Listing Upgrade: ' . $package['name'] . ' (Property #' . $propertyId . ')',
                $tid
            ]
        );

        $paymentOrder = $this->db->query(
            "SELECT id FROM payment_orders WHERE order_id = ? AND tenant_id = ?",
            [$orderId, $tid]
        )->fetch();

        $boostOrderId = $paymentOrder['id'] ?? 0;

        $this->db->query(
            "INSERT INTO property_boost_orders (user_id, property_id, package_id, amount, status, payment_method, tenant_id) VALUES (?, ?, ?, ?, 'pending', 'razorpay', ?)",
            [$userId, $propertyId, $packageId, $package['price'], $tid]
        );

        $boostId = $this->db->query("SELECT LAST_INSERT_ID() as id")->fetch()['id'];

        $this->db->query(
            "UPDATE property_boost_orders SET payment_ref = ? WHERE id = ? AND tenant_id = ?",
            [$orderId, $boostId, $tid]
        );

        echo json_encode([
            'success' => true,
            'order_id' => $orderId,
            'boost_order_id' => (int)$boostId,
            'amount' => (float)$package['price'],
            'currency' => 'INR',
            'package_name' => $package['name'],
            'package_id' => (int)$package['id'],
            'property_id' => $propertyId,
            'razorpay_key_id' => 'rzp_test.placeholder',
            'prefill' => [
                'name' => $property['name'] ?? '',
                'email' => '',
                'contact' => ''
            ]
        ]);
    }

    public function verifyPayment() {
        $tid = (int)$this->tenantId();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);

        if ($userId <= 0) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Login required']);
            return;
        }

        $input = $this->getJsonInput();
        $orderId = $input['order_id'] ?? '';
        $razorpayPaymentId = $input['razorpay_payment_id'] ?? '';
        $razorpaySignature = $input['razorpay_signature'] ?? '';

        if (empty($orderId)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Order ID is required']);
            return;
        }

        $boostOrder = $this->db->query(
            "SELECT bo.*, lp.name as package_name, lp.duration_days, lp.is_featured, lp.is_premium, lp.is_urgent, lp.boost_score
             FROM property_boost_orders bo
             LEFT JOIN listing_packages lp ON bo.package_id = lp.id
             WHERE bo.payment_ref = ? AND bo.user_id = ? AND bo.tenant_id = ?",
            [$orderId, $userId, $tid]
        )->fetch();

        if (!$boostOrder) {
            $paymentOrder = $this->db->query(
                "SELECT * FROM payment_orders WHERE order_id = ? AND user_id = ? AND tenant_id = ?",
                [$orderId, $userId, $tid]
            )->fetch();

            if (!$paymentOrder) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Order not found']);
                return;
            }

            $boostOrder = $this->db->query(
                "SELECT bo.*, lp.name as package_name, lp.duration_days, lp.is_featured, lp.is_premium, lp.is_urgent, lp.boost_score
                 FROM property_boost_orders bo
                 LEFT JOIN listing_packages lp ON bo.package_id = lp.id
                 WHERE bo.user_id = ? AND bo.tenant_id = ? AND bo.status = 'pending'
                 ORDER BY bo.id DESC LIMIT 1",
                [$userId, $tid]
            )->fetch();
        }

        if (!$boostOrder) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Pending order not found']);
            return;
        }

        $this->db->query(
            "UPDATE payment_orders SET status = 'paid', payment_id = ?, signature = ?, paid_at = NOW() WHERE order_id = ? AND tenant_id = ?",
            [$razorpayPaymentId, $razorpaySignature, $orderId, $tid]
        );

        $this->db->query(
            "UPDATE property_boost_orders SET status = 'active', payment_method = 'razorpay', starts_at = NOW(), expires_at = DATE_ADD(NOW(), INTERVAL ? DAY) WHERE id = ? AND tenant_id = ?",
            [(int)($boostOrder['duration_days'] ?? 30), $boostOrder['id'], $tid]
        );

        $this->activateListing($boostOrder['property_id'], [
            'is_featured' => $boostOrder['is_featured'],
            'is_premium' => $boostOrder['is_premium'],
            'is_urgent' => $boostOrder['is_urgent'],
            'duration_days' => $boostOrder['duration_days'],
        ], $userId, $tid, 'razorpay');

        echo json_encode([
            'success' => true,
            'message' => 'Payment successful! Listing upgraded to ' . ($boostOrder['package_name'] ?? 'Premium')
        ]);
    }

    public function activateFree() {
        $tid = (int)$this->tenantId();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);

        if ($userId <= 0) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Login required']);
            return;
        }

        $input = $this->getJsonInput();
        $packageId = (int)($input['package_id'] ?? 0);
        $propertyId = (int)($input['property_id'] ?? 0);

        if ($packageId <= 0 || $propertyId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            return;
        }

        $package = $this->db->query(
            "SELECT * FROM listing_packages WHERE id = ? AND tenant_id = ? AND status = 'active'",
            [$packageId, $tid]
        )->fetch();

        if (!$package) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Package not found']);
            return;
        }

        $property = $this->db->query(
            "SELECT * FROM user_properties WHERE id = ? AND tenant_id = ?",
            [$propertyId, $tid]
        )->fetch();

        if (!$property) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Property not found']);
            return;
        }

        if ((int)$property['user_id'] !== $userId && (int)($property['posted_by'] ?? 0) !== $userId) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'You can only upgrade your own listings']);
            return;
        }

        if ((float)$package['price'] > 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'This is a paid package. Use create-order endpoint.']);
            return;
        }

        $this->activateListing($propertyId, $package, $userId, $tid, 'free');

        echo json_encode([
            'success' => true,
            'message' => 'Listing upgraded to ' . $package['name'] . ' (free)',
            'payment_required' => false
        ]);
    }

    private function activateListing($propertyId, $package, $userId, $tid, $paymentMethod) {
        $updates = [];
        if (!empty($package['is_featured'])) $updates[] = 'is_featured = 1';
        if (!empty($package['is_premium'])) $updates[] = 'is_premium = 1';
        if (!empty($package['is_urgent'])) $updates[] = 'is_urgent = 1';

        if (!empty($updates)) {
            $durationDays = (int)($package['duration_days'] ?? 30);
            $expiry = date('Y-m-d H:i:s', time() + ($durationDays * 86400));
            $updates[] = "boost_expires_at = '" . $expiry . "'";

            $this->db->query(
                "UPDATE user_properties SET " . implode(', ', $updates) . " WHERE id = ? AND tenant_id = ?",
                [$propertyId, $tid]
            );
        }

        $existing = $this->db->query(
            "SELECT id FROM property_boost_orders WHERE user_id = ? AND property_id = ? AND status = 'active' AND tenant_id = ?",
            [$userId, $propertyId, $tid]
        )->fetch();

        if (!$existing) {
            $durationDays = (int)($package['duration_days'] ?? 0);
            if ($durationDays > 0) {
                $expiresAt = date('Y-m-d H:i:s', time() + ($durationDays * 86400));
                $this->db->query(
                    "INSERT INTO property_boost_orders (user_id, property_id, package_id, amount, status, starts_at, expires_at, payment_method, tenant_id) VALUES (?, ?, ?, ?, 'active', NOW(), ?, ?, ?)",
                    [$userId, $propertyId, $package['id'] ?? 0, $package['price'] ?? 0, $expiresAt, $paymentMethod, $tid]
                );
            }
        }
    }
}
