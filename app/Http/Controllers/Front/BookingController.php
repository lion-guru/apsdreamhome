<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Services\Sales\BookingLifecycleService;
use App\Services\Esign\ESignManager;

class BookingController extends BaseController
{
    use \App\Traits\TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Skip CSRF only for the e-sign webhook endpoint.
     */
    protected function skipCsrfProtection(): bool
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        return strpos($uri, '/webhook/esign') !== false;
    }

    private function getUser()
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Browse available plots with filters (colony, size, price range).
     */
    public function browse()
    {
        $where = "p.is_active = 1 AND p.status = 'available'";
        $params = [];

        $colonyId = intval($_GET['colony'] ?? 0);
        if ($colonyId > 0) {
            $where .= " AND p.colony_id = ?";
            $params[] = $colonyId;
        }

        $minArea = floatval($_GET['min_area'] ?? 0);
        if ($minArea > 0) {
            $where .= " AND p.area_sqft >= ?";
            $params[] = $minArea;
        }

        $maxArea = floatval($_GET['max_area'] ?? 0);
        if ($maxArea > 0) {
            $where .= " AND p.area_sqft <= ?";
            $params[] = $maxArea;
        }

        $minPrice = floatval($_GET['min_price'] ?? 0);
        if ($minPrice > 0) {
            $where .= " AND p.total_price >= ?";
            $params[] = $minPrice;
        }

        $maxPrice = floatval($_GET['max_price'] ?? 0);
        if ($maxPrice > 0) {
            $where .= " AND p.total_price <= ?";
            $params[] = $maxPrice;
        }

        $sort = $_GET['sort'] ?? 'plot_number';
        $allowedSorts = [
            'plot_number'  => 'p.block, p.plot_number',
            'price_asc'    => 'p.total_price ASC',
            'price_desc'   => 'p.total_price DESC',
            'area_asc'     => 'p.area_sqft ASC',
            'area_desc'    => 'p.area_sqft DESC',
        ];
        $orderBy = $allowedSorts[$sort] ?? 'p.block, p.plot_number';

        $sql = "SELECT p.*, c.name AS colony_name, c.slug AS colony_slug
                FROM plots p
                JOIN colonies c ON p.colony_id = c.id
                WHERE {$where}
                ORDER BY {$orderBy}";
        $plots = $this->db->fetchAll($sql, $params);

        $colonies = $this->db->fetchAll(
            "SELECT c.id, c.name, c.slug,
                    (SELECT COUNT(*) FROM plots p2 WHERE p2.colony_id = c.id AND p2.status = 'available' AND p2.is_active = 1) AS available_count
             FROM colonies c WHERE c.is_active = 1 ORDER BY c.name"
        );

        $totalAvailable = $this->db->fetchRow("SELECT COUNT(*) AS cnt FROM plots p WHERE {$where}", $params);

        $this->render('pages/booking/browse', [
            'page_title'        => 'Browse Plots - APS Dream Home',
            'meta_description'  => 'Browse available plots for sale across all APS Dream Home colonies. Filter by colony, size, and price.',
            'plots'             => $plots,
            'colonies'          => $colonies,
            'total'             => (int)($totalAvailable['cnt'] ?? 0),
            'current_colony'    => $colonyId,
            'current_min_area'  => $minArea,
            'current_max_area'  => $maxArea,
            'current_min_price' => $minPrice,
            'current_max_price' => $maxPrice,
            'current_sort'      => $sort,
        ]);
    }

    /**
     * Show full plot detail page with dimensions, colony info, photos, price breakdown.
     */
    public function detail($id)
    {
        $id = intval($id);
        $plot = $this->db->fetchRow("
            SELECT p.*, c.name AS colony_name, c.slug AS colony_slug,
                   c.description AS colony_description, c.amenities, c.gallery_images,
                   d.name AS district_name, s.name AS state_name
            FROM plots p
            JOIN colonies c ON p.colony_id = c.id
            LEFT JOIN districts d ON c.district_id = d.id
            LEFT JOIN states s ON d.state_id = s.id
            WHERE p.id = ? AND p.is_active = 1
        ", [$id]);

        if (!$plot) {
            $this->setFlash('error', 'Plot not found.');
            return $this->redirect('/plots/browse');
        }

        $pricePerSqft = $plot['area_sqft'] > 0 ? round($plot['total_price'] / $plot['area_sqft'], 2) : 0;
        $tokenAmount = round($plot['total_price'] * 0.25, 2);
        $stampDuty = round($plot['total_price'] * 0.05, 2);

        $nearbyPlots = $this->db->fetchAll("
            SELECT id, plot_number, area_sqft, total_price, dimension_label, status
            FROM plots
            WHERE colony_id = ? AND id != ? AND status = 'available' AND is_active = 1
            LIMIT 4
        ", [$plot['colony_id'], $id]);

        $this->render('pages/booking/detail', [
            'page_title'       => $plot['plot_number'] . ' - ' . $plot['colony_name'] . ' - APS Dream Home',
            'meta_description' => "Plot {$plot['plot_number']} in {$plot['colony_name']}. {$plot['dimension_label']}, {$plot['area_sqft']} sqft, ₹" . number_format($plot['total_price']) . ".",
            'plot'             => $plot,
            'pricePerSqft'     => $pricePerSqft,
            'tokenAmount'      => $tokenAmount,
            'stampDuty'        => $stampDuty,
            'nearbyPlots'      => $nearbyPlots,
        ]);
    }

    /**
     * Booking form — customer fills details, selects payment plan.
     * Requires login.
     */
    public function bookForm($id)
    {
        $this->requireLogin();
        $user = $this->getUser();
        $id = intval($id);

        $plot = $this->db->fetchRow("
            SELECT p.*, c.name AS colony_name, c.slug AS colony_slug,
                   d.name AS district_name, s.name AS state_name
            FROM plots p
            JOIN colonies c ON p.colony_id = c.id
            LEFT JOIN districts d ON c.district_id = d.id
            LEFT JOIN states s ON d.state_id = s.id
            WHERE p.id = ? AND p.is_active = 1
        ", [$id]);

        if (!$plot || $plot['status'] !== 'available') {
            $this->setFlash('error', 'This plot is no longer available.');
            return $this->redirect('/plots/browse');
        }

        $tokenAmount = round($plot['total_price'] * 0.25, 2);

        $this->layout = 'layouts/customer';
        $this->render('pages/booking/form', [
            'page_title'    => 'Book Plot ' . $plot['plot_number'] . ' - APS Dream Home',
            'plot'          => $plot,
            'user'          => $user,
            'tokenAmount'   => $tokenAmount,
        ]);
    }

    /**
     * POST handler — creates the booking via BookingLifecycleService.
     * Requires login + CSRF.
     */
    public function submitBooking($id)
    {
        $this->requireLogin();
        $user = $this->getUser();
        $id = intval($id);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirect('/plots/browse');
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $this->jsonResponse(['success' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }

        $plot = $this->db->fetchRow(
            "SELECT * FROM plots WHERE id = ? AND is_active = 1 AND status = 'available'",
            [$id]
        );
        if (!$plot) {
            $this->setFlash('error', 'Plot is no longer available.');
            return $this->redirect('/plots/browse');
        }

        $paymentPlan = $_POST['payment_plan'] ?? 'emi';
        $notes = trim($_POST['notes'] ?? '');

        try {
            $svc = new BookingLifecycleService();
            $result = $svc->createBooking([
                'plot_id'          => $plot['id'],
                'customer_id'      => $user['id'],
                'total_plot_value' => (float)$plot['total_price'],
                'booking_amount'   => round($plot['total_price'] * 0.25, 2),
                'channel'          => 'direct',
                'notes'            => $notes,
            ]);

            if (!$result['success']) {
                $this->setFlash('error', 'Booking failed: ' . ($result['error'] ?? 'Unknown error'));
                return $this->redirect('/plots/' . $id . '/book');
            }

            if ($paymentPlan === 'emi') {
                $svc->generatePaymentSchedule($result['id'], 12, 10.0);
            }

            // Send booking confirmation notification
            try {
                $notifSvc = new \App\Services\Communication\NotificationService();
                $notifSvc->sendNotification($user['id'], 'in_app', 'Booking Confirmed',
                    'Your booking for plot #' . $plot['plot_number'] . ' has been confirmed. Booking #' . $result['booking_number'] . '.',
                    ['event_type' => 'booking', 'booking_id' => $result['id'], 'action_url' => '/booking/confirmation/' . $result['id']]
                );
            } catch (\Throwable $e) {
                error_log('[BookingController] notification failed: ' . $e->getMessage());
            }

            $this->redirect('/booking/confirmation/' . $result['id']);
        } catch (\Throwable $e) {
            error_log('[BookingController::submitBooking] ' . $e->getMessage());
            $this->setFlash('error', 'Something went wrong. Please try again.');
            return $this->redirect('/plots/' . $id . '/book');
        }
    }

    /**
     * Show booking confirmation with booking number, plot details, payment schedule.
     * Requires login.
     */
    public function confirmation($id)
    {
        $this->requireLogin();
        $user = $this->getUser();
        $id = intval($id);

        $booking = $this->db->fetchRow("
            SELECT pb.*, p.plot_number, p.block, p.area_sqft, p.dimension_label,
                   p.total_price AS plot_price, p.corner_plot, p.park_facing,
                   c.name AS colony_name, c.slug AS colony_slug,
                   u.name AS customer_name, u.email AS customer_email
            FROM plot_bookings pb
            LEFT JOIN plots p ON pb.plot_id = p.id
            LEFT JOIN colonies c ON p.colony_id = c.id
            LEFT JOIN users u ON pb.customer_id = u.id
            WHERE pb.id = ? AND pb.customer_id = ?
        ", [$id, $user['id']]);

        if (!$booking) {
            $this->setFlash('error', 'Booking not found.');
            return $this->redirect('/user/dashboard');
        }

        $schedule = [];
        try {
            $schedule = $this->db->fetchAll(
                "SELECT * FROM booking_payment_schedules WHERE booking_id = ? ORDER BY installment_no",
                [$id]
            );
        } catch (\Throwable $e) {
            // table may not exist yet
        }

        $totalPaid = 0;
        $totalPending = 0;
        foreach ($schedule as $inst) {
            if ($inst['status'] === 'paid') {
                $totalPaid += (float)$inst['amount'];
            } else {
                $totalPending += (float)$inst['amount'];
            }
        }

        $this->layout = 'layouts/customer';
        $this->render('pages/booking/confirmation', [
            'page_title' => 'Booking Confirmed #' . htmlspecialchars($booking['booking_number'] ?? ''),
            'booking'    => $booking,
            'schedule'   => $schedule,
            'totalPaid'  => $totalPaid,
            'totalPending' => $totalPending,
            'user'       => $user,
        ]);
    }

    /* ────────────────────────────────────────────────────────────── */
    /*  E-SIGN (Leegality)                                           */
    /* ────────────────────────────────────────────────────────────── */

    /**
     * Show the e-sign page for a booking.
     * GET /user/bookings/{id}/esign
     */
    public function esign($id)
    {
        $this->requireLogin();
        $user  = $this->getUser();
        $id    = intval($id);

        $booking = $this->db->fetchRow("
            SELECT pb.*, p.plot_number, p.block, p.area_sqft, p.dimension_label,
                   p.total_price AS plot_price, c.name AS colony_name
            FROM plot_bookings pb
            LEFT JOIN plots p ON pb.plot_id = p.id
            LEFT JOIN colonies c ON p.colony_id = c.id
            WHERE pb.id = ? AND pb.customer_id = ?
        ", [$id, $user['id']]);

        if (!$booking) {
            $this->setFlash('error', 'Booking not found.');
            return $this->redirect('/user/dashboard');
        }

        try {
            $mgr   = new ESignManager();
            $esign = $mgr->getStatus($id);
        } catch (\Throwable $e) {
            error_log('[BookingController::esign] ' . $e->getMessage());
            $esign = ['success' => true, 'status' => 'pending', 'error' => null];
        }

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            return $this->jsonResponse([
                'success'      => true,
                'status'       => $esign['status'] ?? 'pending',
                'verified'     => ($esign['status'] ?? '') === 'signed',
                'signed_at'    => $esign['signed_at'] ?? null,
                'signing_url'  => $esign['signing_url'] ?? null,
            ]);
        }

        $this->layout = 'layouts/customer';
        $this->render('pages/booking/esign', [
            'page_title' => 'E-Sign Agreement - APS Dream Home',
            'booking'    => $booking,
            'esign'      => $esign,
            'user'       => $user,
        ]);
    }

    /**
     * Initiate e-sign process for a booking.
     * POST /user/bookings/{id}/esign/initiate
     */
    public function initiateEsign($id)
    {
        $this->requireLogin();
        $user = $this->getUser();
        $id   = intval($id);

        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            return $this->jsonResponse(['success' => false, 'error' => 'Invalid CSRF token.'], 403);
        }

        $booking = $this->db->fetchRow(
            "SELECT * FROM plot_bookings WHERE id = ? AND customer_id = ?",
            [$id, $user['id']]
        );
        if (!$booking) {
            return $this->jsonResponse(['success' => false, 'error' => 'Booking not found.']);
        }

        // Build agreement path — in production this would be a generated PDF
        $agreementPath = (defined('BASE_URL') ? BASE_URL : '')
            . '/uploads/agreements/booking-' . $id . '.pdf';

        try {
            $mgr = new ESignManager();
            $result = $mgr->initiateEsign($id, $agreementPath);

            return $this->jsonResponse($result, $result['success'] ? 200 : 400);
        } catch (\Throwable $e) {
            error_log('[BookingController::initiateEsign] ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'error' => 'E-sign service unavailable. Please try again later.'], 500);
        }
    }

    /**
     * Webhook callback for Leegality e-sign events.
     * POST /webhook/esign — CSRF skipped via router exclusion for /webhook/.
     */
    public function esignWebhook()
    {
        // CSRF is skipped for /webhook/ paths in routes/router.php

        $payload = json_decode(file_get_contents('php://input'), true);
        if (!$payload) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid payload']);
            return;
        }

        $documentId = $payload['document_id'] ?? $payload['id'] ?? '';
        $status     = $payload['status'] ?? $payload['event'] ?? '';

        if (empty($documentId) || empty($status)) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing document_id or status']);
            return;
        }

        try {
            $mgr    = new ESignManager();
            $result = $mgr->callback($documentId, $status);

            http_response_code(200);
            echo json_encode($result);
        } catch (\Throwable $e) {
            error_log('[BookingController::esignWebhook] ' . $e->getMessage());
            http_response_code(200);
            echo json_encode(['success' => false, 'error' => 'Internal error']);
        }
    }

    /* ────────────────────────────────────────────────────────────── */
    /*  PLOT LOCK (30-min reservation)                                */
    /* ────────────────────────────────────────────────────────────── */

    /**
     * AJAX: Lock a plot for 30 minutes while customer fills booking form.
     * POST /plots/{id}/lock
     * Returns JSON: { success, lock_id, expires_at }
     */
    public function lockPlot($id)
    {
        $this->requireLogin();
        $user = $this->getUser();
        $id = intval($id);

        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            return $this->jsonResponse(['success' => false, 'error' => 'Invalid CSRF token'], 403);
        }

        $plot = $this->db->fetchRow("SELECT * FROM plots WHERE id = ? AND status = 'available' AND is_active = 1", [$id]);
        if (!$plot) {
            return $this->jsonResponse(['success' => false, 'error' => 'Plot is no longer available.'], 404);
        }

        // Expire old locks for this user
        try {
            $this->db->execute(
                "DELETE FROM plot_locks WHERE user_id = ? AND plot_id = ? AND expires_at > NOW()",
                [$user['id'], $id]
            );
        } catch (\Throwable $e) {
            // table may not exist
        }

        // Release expired locks on this plot
        try {
            $this->db->execute("DELETE FROM plot_locks WHERE plot_id = ? AND expires_at <= NOW()", [$id]);
        } catch (\Throwable $e) {
            // table may not exist
        }

        // Check if another user holds an active lock
        try {
            $existingLock = $this->db->fetchRow(
                "SELECT * FROM plot_locks WHERE plot_id = ? AND expires_at > NOW() AND user_id != ?",
                [$id, $user['id']]
            );
            if ($existingLock) {
                $remaining = round((strtotime($existingLock['expires_at']) - time()) / 60);
                return $this->jsonResponse([
                    'success' => false,
                    'error'   => "This plot is currently locked by another customer. Please try again in {$remaining} minutes.",
                ], 409);
            }
        } catch (\Throwable $e) {
            // table may not exist, proceed
        }

        $expiresAt = date('Y-m-d H:i:s', time() + 1800); // 30 minutes

        try {
            $this->db->execute(
                "INSERT INTO plot_locks (plot_id, user_id, locked_at, expires_at, status) VALUES (?, ?, NOW(), ?, 'active')",
                [$id, $user['id'], $expiresAt]
            );
            $lockId = $this->db->lastInsertId();
        } catch (\Throwable $e) {
            // If table doesn't exist, skip locking — just proceed
            error_log('[BookingController::lockPlot] plot_locks table missing: ' . $e->getMessage());
            return $this->jsonResponse([
                'success'  => true,
                'lock_id'  => 0,
                'expires_at' => $expiresAt,
                'message'  => 'Proceeding without reservation lock.',
            ]);
        }

        return $this->jsonResponse([
            'success'    => true,
            'lock_id'    => (int)$lockId,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * AJAX: Release a plot lock (on page close or navigation).
     * POST /plots/{id}/unlock
     */
    public function unlockPlot($id)
    {
        $this->requireLogin();
        $user = $this->getUser();
        $id = intval($id);

        try {
            $this->db->execute(
                "UPDATE plot_locks SET status = 'released' WHERE plot_id = ? AND user_id = ? AND status = 'active'",
                [$id, $user['id']]
            );
        } catch (\Throwable $e) {
            // graceful
        }

        return $this->jsonResponse(['success' => true]);
    }

    /* ────────────────────────────────────────────────────────────── */
    /*  NACH MANDATE REGISTRATION                                     */
    /* ────────────────────────────────────────────────────────────── */

    /**
     * Show NACH mandate registration form.
     * GET /user/bookings/{id}/nach
     */
    public function nachMandate($id)
    {
        $this->requireLogin();
        $user = $this->getUser();
        $id = intval($id);

        $booking = $this->db->fetchRow("
            SELECT pb.*, p.plot_number, p.block, c.name AS colony_name
            FROM plot_bookings pb
            LEFT JOIN plots p ON pb.plot_id = p.id
            LEFT JOIN colonies c ON p.colony_id = c.id
            WHERE pb.id = ? AND pb.customer_id = ?
        ", [$id, $user['id']]);

        if (!$booking) {
            $this->setFlash('error', 'Booking not found.');
            return $this->redirect('/user/dashboard');
        }

        $mandate = null;
        try {
            $bls = new BookingLifecycleService();
            $mandate = $bls->getNachMandate($id);
        } catch (\Throwable $e) {
            // graceful
        }

        $this->layout = 'layouts/customer';
        $this->render('pages/booking/nach-mandate', [
            'page_title' => 'NACH Mandate - APS Dream Home',
            'booking'    => $booking,
            'mandate'    => $mandate,
            'user'       => $user,
        ]);
    }

    /**
     * POST: Register NACH mandate for a booking.
     * POST /user/bookings/{id}/nach/register
     */
    public function registerNachMandate($id)
    {
        $this->requireLogin();
        $user = $this->getUser();
        $id = intval($id);

        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $this->setFlash('error', 'Invalid security token.');
            return $this->redirect('/user/bookings/' . $id . '/nach');
        }

        $bankName     = trim($_POST['bank_name'] ?? '');
        $accountNo    = trim($_POST['account_number'] ?? '');
        $ifscCode     = trim($_POST['ifsc_code'] ?? '');
        $accountHolder = trim($_POST['account_holder_name'] ?? '');
        $mandateType  = $_POST['mandate_type'] ?? 'emandate';
        $amount       = floatval($_POST['mandate_amount'] ?? 0);

        if (empty($bankName) || empty($accountNo) || empty($ifscCode) || $amount <= 0) {
            $this->setFlash('error', 'Please fill all required fields.');
            return $this->redirect('/user/bookings/' . $id . '/nach');
        }

        try {
            $bls = new BookingLifecycleService();
            $result = $bls->registerNachMandate($id, [
                'bank_name'        => $bankName,
                'account_number'   => $accountNo,
                'ifsc_code'        => $ifscCode,
                'account_holder'   => $accountHolder,
                'mandate_type'     => $mandateType,
                'mandate_amount'   => $amount,
                'frequency'        => 'monthly',
            ]);

            if ($result['success']) {
                $this->setFlash('success', 'NACH mandate registered successfully. You will receive confirmation from your bank within 2-3 business days.');
                return $this->redirect('/user/bookings/' . $id . '/nach');
            } else {
                $this->setFlash('error', $result['error'] ?? 'Failed to register mandate.');
                return $this->redirect('/user/bookings/' . $id . '/nach');
            }
        } catch (\Throwable $e) {
            error_log('[BookingController::registerNachMandate] ' . $e->getMessage());
            $this->setFlash('error', 'Something went wrong. Please try again.');
            return $this->redirect('/user/bookings/' . $id . '/nach');
        }
    }

    /**
     * AJAX: Verify KYC before booking submission.
     * POST /plots/{id}/verify-kyc
     */
    public function verifyKyc($id)
    {
        $this->requireLogin();
        $user = $this->getUser();

        try {
            $stmt = $this->db->prepare("SELECT status FROM kyc_requests WHERE user_id = ? ORDER BY id DESC LIMIT 1");
            $stmt->execute([$user['id']]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            $status = $row['status'] ?? 'not_started';

            $isVerified = in_array($status, ['approved', 'verified'], true);
            return $this->jsonResponse([
                'success'   => true,
                'verified'  => $isVerified,
                'status'    => $status,
                'message'   => $isVerified
                    ? 'KYC verified.'
                    : 'KYC not yet verified. Please complete KYC to proceed with booking.',
            ]);
        } catch (\Throwable $e) {
            return $this->jsonResponse([
                'success'  => true,
                'verified' => false,
                'status'   => 'unknown',
                'message'  => 'KYC status could not be determined.',
            ]);
        }
    }
}
