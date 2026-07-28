<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Services\Gateway\RazorpayService;
use App\Core\Database\Database;

/**
 * Checkout flow:
 *  - /checkout/{bookingId}              GET   show payment page
 *  - /checkout/process/{bookingId}     POST   create Razorpay order
 *  - /checkout/verify                  POST   verify signature + record success
 *  - /checkout/success/{paymentId}     GET    receipt page
 *  - /checkout/failed                  GET    failure page
 *  - /webhook/razorpay                 POST   Razorpay webhook callback
 *
 * Razorpay webhook handler uses HMAC-SHA256 signature verification
 * via RazorpayService::verifyWebhookSignature(); CSRF is skipped for
 * that route only (verified by signature, not session token).
 */
class CheckoutController extends BaseController
{
    use \App\Traits\TenantAwareTrait;

    protected $layout = 'layouts/base';

    /** GET /checkout/{bookingId} */
    public function checkout($bookingId)
    {
        $bookingId = (int)$bookingId;
        if ($bookingId <= 0) {
            return $this->render('pages/payment_failed', [
                'page_title' => 'Invalid Booking',
                'error_message' => 'Invalid booking identifier.',
            ]);
        }

        $booking = $this->fetchBooking($bookingId);
        if (!$booking) {
            return $this->render('pages/payment_failed', [
                'page_title' => 'Booking Not Found',
                'error_message' => "We couldn't find booking #{$bookingId}.",
            ]);
        }

        $service = new RazorpayService();
        return $this->render('pages/checkout', [
            'page_title'    => 'Complete Your Payment — APS Dream Home',
            'booking'       => $booking,
            'razorpay'      => [
                'key_id'     => $service->getKeyId(),
                'is_test'    => $service->isTestMode() || !$service->isConfigured(),
                'configured' => $service->isConfigured(),
            ],
            'csrf_token'    => $_SESSION['csrf_token'] ?? $this->issueCsrf(),
        ]);
    }

    /** POST /checkout/process/{bookingId} */
    public function processPayment($bookingId)
    {
        $bookingId = (int)$bookingId;
        $booking = $this->fetchBooking($bookingId);
        if (!$booking) {
            return $this->jsonError('Booking not found', 404);
        }

        $amount = (float)($booking['amount'] ?? $booking['total_amount'] ?? 0);
        if ($amount <= 0) {
            return $this->jsonError('Invalid booking amount', 400);
        }

        $service = new RazorpayService();
        $resp = $service->createOrder($amount, 'INR', 'BOOKING_' . $bookingId, [
            'booking_id'     => $bookingId,
            'user_id'        => (int)($booking['user_id'] ?? 0),
            'customer_name'  => $booking['customer_name'] ?? null,
            'customer_email' => $booking['customer_email'] ?? null,
            'customer_phone' => $booking['customer_phone'] ?? null,
            'description'    => 'Booking payment for #' . $bookingId,
        ]);

        if (!$resp['success']) {
            return $this->jsonResponse([
                'success' => false,
                'error'   => $resp['error'] ?? 'Failed to create order',
            ], 502);
        }

        return $this->jsonResponse([
            'success'      => true,
            'order_id'     => $resp['data']['id'],
            'amount_paise' => $resp['data']['amount'],
            'amount'       => $amount,
            'currency'     => $resp['data']['currency'] ?? 'INR',
            'key_id'       => $service->getKeyId(),
            'booking_id'   => $bookingId,
        ]);
    }

    /** POST /checkout/verify — callback from Razorpay checkout */
    public function verifyPayment()
    {
        $orderId   = $_POST['razorpay_order_id']   ?? '';
        $paymentId = $_POST['razorpay_payment_id'] ?? '';
        $signature = $_POST['razorpay_signature']  ?? '';

        $service = new RazorpayService();

        if (!$service->verifyPaymentSignature($orderId, $paymentId, $signature)) {
            try {
                $db = Database::getInstance()->getConnection();
                $db->prepare("UPDATE payment_orders SET status = 'failed', error_code = 'BAD_SIGNATURE', error_description = ? WHERE order_id = ?")
                   ->execute(['Invalid payment signature', $orderId]);
            } catch (\Throwable $e) {}
        $this->redirectLocal('/checkout/failed?reason=bad_signature&order=' . urlencode($orderId));
        return;
    }

        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE payment_orders
                SET status = 'paid', payment_id = ?, signature = ?, paid_at = NOW()
                WHERE order_id = ?");
            $stmt->execute([$paymentId, $signature, $orderId]);

            $order = $db->prepare("SELECT booking_id, user_id, amount, currency FROM payment_orders WHERE order_id = ?");
            $order->execute([$orderId]);
            $row = $order->fetch(\PDO::FETCH_ASSOC);
            if ($row) {
                $existsStmt = $db->prepare("SELECT id FROM payments WHERE gateway_transaction_id = ?");
                $existsStmt->execute([$paymentId]);
                if (!$existsStmt->fetch()) {
                    $db->prepare("INSERT INTO payments
                        (payment_id, transaction_id, reference_id, customer_id, booking_id, amount, currency, gateway, gateway_transaction_id, status, payment_date, payment_time, created_at, updated_at, user_id)
                        VALUES (?, ?, ?, ?, ?, ?, ?, 'razorpay', ?, 'completed', CURDATE(), CURTIME(), NOW(), NOW(), ?)")
                       ->execute([
                           $paymentId,
                           $paymentId,
                           $row['booking_id'] ?? null,
                           $row['user_id'] ?? null,
                           $row['booking_id'] ?? null,
                           $row['amount'] ?? 0,
                           $row['currency'] ?? 'INR',
                           $paymentId,
                           $row['user_id'] ?? null,
                       ]);
                }
                if (!empty($row['booking_id'])) {
                    $db->prepare("UPDATE bookings SET payment_status = 'paid', status = IF(status = 'pending', 'confirmed', status) WHERE id = ?")
                       ->execute([$row['booking_id']]);
                }
            }
        } catch (\Throwable $e) {
            error_log('verifyPayment: ' . $e->getMessage());
        }

        $this->redirectLocal('/checkout/success/' . urlencode($paymentId) . '?order=' . urlencode($orderId));
    }

    /** GET /checkout/success/{paymentId} */
    public function paymentSuccess($paymentId)
    {
        $paymentId = htmlspecialchars((string)$paymentId, ENT_QUOTES, 'UTF-8');
        $orderId   = htmlspecialchars((string)($_GET['order'] ?? ''), ENT_QUOTES, 'UTF-8');
        // Try to resolve the booking ID so we can offer a PDF receipt link
        $bookingId = 0;
        try {
            $db = Database::getInstance()->getConnection();
            // Look up by order first, then by payment_id
            $stmt = $db->prepare("SELECT booking_id FROM payment_orders WHERE order_id = ? LIMIT 1");
            $stmt->execute([$orderId]);
            $bookingId = (int)($stmt->fetchColumn() ?: 0);
            if (!$bookingId && $paymentId) {
                $stmt = $db->prepare("SELECT booking_id FROM payments WHERE gateway_transaction_id = ? LIMIT 1");
                $stmt->execute([$paymentId]);
                $bookingId = (int)($stmt->fetchColumn() ?: 0);
            }
        } catch (\Throwable $e) {
            // Non-fatal - just skip the PDF link
        }
        return $this->render('pages/payment_success', [
            'page_title' => 'Payment Successful — APS Dream Home',
            'payment_id' => $paymentId,
            'order_id'   => $orderId,
            'booking_id' => $bookingId,
        ]);
    }

    /** GET /checkout/failed */
    public function paymentFailed()
    {
        $reason = htmlspecialchars((string)($_GET['reason'] ?? 'unknown'), ENT_QUOTES, 'UTF-8');
        $order  = htmlspecialchars((string)($_GET['order']  ?? ''), ENT_QUOTES, 'UTF-8');
        $msg    = $this->reasonMessage($reason);
        return $this->render('pages/payment_failed', [
            'page_title'     => 'Payment Failed — APS Dream Home',
            'error_message'  => $msg,
            'order_id'       => $order,
        ]);
    }

    /** POST /webhook/razorpay — Razorpay server-side callback */
    public function webhook()
    {
        $rawBody = file_get_contents('php://input') ?: '';
        $signature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';

        $service = new RazorpayService();
        $valid = $service->verifyWebhookSignature($rawBody, $signature);

        $payload = json_decode($rawBody, true) ?: [];
        $event = $payload['event'] ?? 'unknown';

        // Log the public webhook URL on the first hit of every process. This is
        // useful in production to confirm Razorpay is hitting the right URL
        // (especially if you have multiple environments behind a load balancer).
        // The URL is either the explicit WEBHOOK_PUBLIC_URL env var or derived
        // from BASE_URL + /webhook/razorpay.
        $publicUrl = $_ENV['WEBHOOK_PUBLIC_URL'] ?? getenv('WEBHOOK_PUBLIC_URL') ?: '';
        if ($publicUrl === '') {
            $publicUrl = (defined('BASE_URL') ? BASE_URL : '') . '/webhook/razorpay';
        }
        static $urlLogged = false;
        if (!$urlLogged) {
            error_log(sprintf('[razorpay-webhook] receiving %s on %s (event=%s, valid=%s)',
                $_SERVER['REQUEST_METHOD'] ?? 'POST', $publicUrl, $event, $valid ? 'yes' : 'no'));
            $urlLogged = true;
        }

        try {
            $db = Database::getInstance()->getConnection();
            $db->prepare("INSERT INTO payment_webhook_logs
                (gateway, event_type, event_id, payload, signature, signature_verified, processed, ip_address)
                VALUES ('razorpay', ?, ?, ?, ?, ?, 0, ?)")
               ->execute([
                   $event,
                   $payload['account_id'] ?? null,
                   $rawBody,
                   $signature,
                   $valid ? 1 : 0,
                   $_SERVER['REMOTE_ADDR'] ?? null,
               ]);
        } catch (\Throwable $e) {
            error_log('webhook log failed: ' . $e->getMessage());
        }

        if (!$valid) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid signature']);
            return;
        }

        try {
            $this->processWebhookEvent($event, $payload);
            $db = Database::getInstance()->getConnection();
            $db->prepare("UPDATE payment_webhook_logs SET processed = 1 WHERE event_type = ? AND created_at = (SELECT MAX(created_at) FROM (SELECT created_at FROM payment_webhook_logs) AS x)")
               ->execute([$event]);
        } catch (\Throwable $e) {
            error_log('webhook processing failed: ' . $e->getMessage());
            try {
                $db = Database::getInstance()->getConnection();
                $db->prepare("UPDATE payment_webhook_logs SET processing_error = ? WHERE event_type = ? AND created_at = (SELECT MAX(created_at) FROM (SELECT created_at FROM payment_webhook_logs) AS x)")
                   ->execute([$e->getMessage(), $event]);
            } catch (\Throwable $e2) {}
        }

        http_response_code(200);
        echo json_encode(['success' => true, 'event' => $event]);
    }

    /**
     * Webhook must NOT use the framework's CSRF protection — it's a
     * server-to-server call authenticated via HMAC signature, not a
     * session-bound form submission.
     */
    protected function skipCsrfProtection(): bool
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        return str_contains($uri, '/webhook/');
    }

    /* ------------------------------------------------------------------ */

    private function fetchBooking(int $bookingId): ?array
    {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT b.*, u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone
                FROM bookings b
                LEFT JOIN users u ON u.id = b.user_id
                WHERE b.id = ? LIMIT 1");
            $stmt->execute([$bookingId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('fetchBooking: ' . $e->getMessage());
            return null;
        }
    }

    private function processWebhookEvent(string $event, array $payload): void
    {
        $db = Database::getInstance()->getConnection();
        switch ($event) {
            case 'payment.captured':
                $payment = $payload['payload']['payment']['entity'] ?? null;
                if ($payment && isset($payment['id'], $payment['order_id'])) {
                    $amount = ($payment['amount'] ?? 0) / 100;
                    $db->prepare("UPDATE payment_orders
                        SET status = 'paid', payment_id = ?, paid_at = NOW()
                        WHERE order_id = ?")
                       ->execute([$payment['id'], $payment['order_id']]);
                    $bookingId = $this->resolveBookingId($payment['order_id']);
                    $userId    = $this->resolveUserIdFromOrder($payment['order_id']);
                    if ($bookingId) {
                        $db->prepare("UPDATE bookings SET payment_status = 'paid' WHERE id = ?")
                           ->execute([$bookingId]);
                    }
                }
                break;
            case 'payment.failed':
                $payment = $payload['payload']['payment']['entity'] ?? null;
                if ($payment && isset($payment['order_id'])) {
                    $db->prepare("UPDATE payment_orders SET status = 'failed', error_code = ?, error_description = ? WHERE order_id = ?")
                       ->execute([$payment['error_code'] ?? null, $payment['error_description'] ?? null, $payment['order_id']]);
                }
                break;
            case 'refund.processed':
                $refund = $payload['payload']['refund']['entity'] ?? null;
                if ($refund && isset($refund['payment_id'])) {
                    $db->prepare("UPDATE payments SET status = 'refunded', refund_amount = ? WHERE gateway_transaction_id = ?")
                       ->execute([($refund['amount'] ?? 0) / 100, $refund['payment_id']]);
                }
                break;
        }
    }

    private function resolveBookingId(string $orderId): ?int
    {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT booking_id FROM payment_orders WHERE order_id = ?");
            $stmt->execute([$orderId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row && $row['booking_id'] ? (int)$row['booking_id'] : null;
        } catch (\Throwable $e) { return null; }
    }

    private function resolveUserIdFromOrder(string $orderId): ?int
    {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT user_id FROM payment_orders WHERE order_id = ?");
            $stmt->execute([$orderId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row && $row['user_id'] ? (int)$row['user_id'] : null;
        } catch (\Throwable $e) { return null; }
    }

    private function reasonMessage(string $reason): string
    {
        $map = [
            'bad_signature' => "We couldn't verify the payment signature. If you were charged, please contact support and we'll refund you.",
            'cancelled'     => "You cancelled the payment before completing it.",
            'declined'      => "Your bank declined the payment. Please try a different payment method.",
            'timeout'       => "The payment session timed out. Please try again.",
            'unknown'       => "Something went wrong while processing your payment. Please try again or contact support.",
        ];
        return $map[$reason] ?? $map['unknown'];
    }

    private function issueCsrf(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    private function redirectLocal(string $url): void
    {
        $prefix = defined('BASE_URL') ? BASE_URL : '';
        header('Location: ' . $prefix . $url);
        exit;
    }
}
