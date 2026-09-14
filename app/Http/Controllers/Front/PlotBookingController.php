<?php
namespace App\Http\Controllers\Front;

use App\Services\Booking\BookingComplianceService;
use App\Services\Notification\BookingNotificationService;

/**
 * PlotBookingController — customer plot-booking flow.
 * Routes: /plot/{id}/book, /plot/book (POST), /booking/{id}/confirmation,
 * /booking/{id}/receipt
 */
class PlotBookingController extends PlotBaseController
{
    /**
     * Show booking form for a specific plot
     */
    public function bookPlot($plotId)
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();
        $plotId = (int)$plotId;
        $tid = (int)$this->tenantId();

        $plotParams = [$plotId];
        $tidScope = '';
        if ($tid > 1) { $tidScope = ' AND p.tenant_id = ?'; $plotParams[] = $tid; }
        $plot = $this->db->fetchRow("
            SELECT p.*, c.name as colony_name, c.slug as colony_slug,
                   d.name as district_name, s.name as state_name
            FROM plots p
            JOIN colonies c ON p.colony_id = c.id
            LEFT JOIN districts d ON c.district_id = d.id
            LEFT JOIN states s ON d.state_id = s.id
            WHERE p.id = ? AND p.is_active = 1" . $tidScope . "
        ", $plotParams);

        if (!$plot || $plot['status'] !== 'available') {
            $this->setFlash('error', 'This plot is not available for booking');
            return $this->redirect('/colony/' . ($plot['colony_slug'] ?? '') . '/plots');
        }

        $userBookings = $this->db->fetchAll(
            "SELECT * FROM bookings WHERE customer_id = ? ORDER BY created_at DESC LIMIT 5",
            [$user['id']]
        ) ?: [];

        $this->layout = 'layouts/customer';
        $this->render('pages/plot_booking', [
            'page_title' => 'Book Plot - ' . $plot['plot_number'] . ' - ' . $plot['colony_name'],
            'plot' => $plot,
            'user' => $user,
            'userBookings' => $userBookings,
        ]);
    }

    /**
     * Process booking form submission (POST, CSRF enforced by BaseController).
     *
     * Concurrency: the plot row is locked with SELECT ... FOR UPDATE inside
     * the transaction, and the hold UPDATE is atomic
     * (WHERE status='available' + rowCount check) so two concurrent requests
     * cannot book the same plot.
     */
    public function storeBooking()
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirect('/plots');
        }

        // Validated + sanitized input
        $plotId = (int)($_POST['plot_id'] ?? 0);
        $bookingType = $_POST['booking_type'] ?? 'online_consultation';
        if (!in_array($bookingType, self::ALLOWED_BOOKING_TYPES, true)) {
            $bookingType = 'online_consultation';
        }
        $notes = trim(strip_tags((string)($_POST['notes'] ?? '')));
        if (function_exists('mb_substr')) {
            $notes = mb_substr($notes, 0, self::MAX_NOTES_LEN);
        } else {
            $notes = substr($notes, 0, self::MAX_NOTES_LEN);
        }
        if ($plotId <= 0) {
            $this->setFlash('error', 'Invalid plot selected. Please try again.');
            return $this->redirect('/plots');
        }
        $tid = (int)$this->tenantId();

        $this->db->beginTransaction();
        try {
            $pdo = $this->db->getPdo();

            // 1. Lock the plot row — concurrent requests serialize here.
            $lockSql = "SELECT * FROM plots WHERE id = ? AND is_active = 1"
                . ($tid > 1 ? ' AND tenant_id = ?' : '') . " FOR UPDATE";
            $lockStmt = $pdo->prepare($lockSql);
            $lockStmt->execute($tid > 1 ? [$plotId, $tid] : [$plotId]);
            $plot = $lockStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$plot || $plot['status'] !== 'available') {
                $this->db->rollBack();
                $this->setFlash('error', 'This plot is no longer available. Please choose another plot.');
                return $this->redirect('/plots');
            }

            // Determine final price (negotiated or total)
            $dealPrice = floatval($_POST['negotiated_price'] ?? $plot['negotiated_price'] ?? $plot['total_price']);
            if ($dealPrice <= 0) $dealPrice = floatval($plot['total_price']);

            // 2. Create booking (unique number via random_bytes)
            $bookingId = $this->db->insert('bookings', [
                'customer_id' => $user['id'],
                'plot_id' => $plot['id'],
                'colony_id' => $plot['colony_id'],
                'booking_number' => $this->generateBookingNumber(),
                'booking_type' => $bookingType,
                'booking_date' => date('Y-m-d'),
                'status' => 'pending',
                'payment_status' => 'pending',
                'total_amount' => $dealPrice,
                'amount' => 0,
                'negotiated_price' => $dealPrice,
                'notes' => $notes,
                'tenant_id' => $tid,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            // 3. Atomically hold the plot — rowCount 0 means we lost the race.
            $holdSql = "UPDATE plots SET status = 'hold', held_by = ?, held_at = NOW() WHERE id = ? AND status = 'available'"
                . ($tid > 1 ? ' AND tenant_id = ?' : '');
            $holdStmt = $pdo->prepare($holdSql);
            $holdStmt->execute($tid > 1 ? [$user['id'], $plot['id'], $tid] : [$user['id'], $plot['id']]);
            if ($holdStmt->rowCount() === 0) {
                $this->db->rollBack();
                $this->setFlash('error', 'This plot was just booked by someone else. Please choose another plot.');
                return $this->redirect('/plots');
            }

            // 4. Create initial token schedule via the compliance plan
            //    service (configurable token % / due days). The service shares
            //    this PDO connection, so the INSERT joins this transaction.
            $compliance = new BookingComplianceService();
            $schedule = $compliance->createTokenSchedule((int)$bookingId, $dealPrice);
            $tokenAmount = $schedule['token_amount'];

            // 5. Create the post-token balance schedule (default: 24 monthly
            //    0%-interest installments in the same transaction).
            $balancePlan = $compliance->createBalanceSchedule(
                (int)$bookingId, $dealPrice, $tokenAmount,
                ['anchor_date' => $schedule['due_date']]
            );

            $this->db->commit();

            try {
                $notifService = new BookingNotificationService();
                $notifService->ensureNotificationsTable();
                $notifService->notifyBookingCreated($bookingId, ['id' => $bookingId], $user);
            } catch (\Throwable $e) {
                error_log('PlotBookingController::storeBooking notify: ' . $e->getMessage());
            }

            $planNote = '';
            if (!empty($balancePlan['generated'])) {
                $planNote = ' Balance ₹' . number_format($balancePlan['balance'], 2)
                    . ' in ' . $balancePlan['generated'] . ' ' . $balancePlan['frequency']
                    . ' installments of ₹' . number_format($balancePlan['per_emi'], 2)
                    . ' from ' . $balancePlan['first_due'] . '.';
            }
            $this->setFlash('success', 'Booking request submitted! Pay the ' . $this->tokenPct() . '% token amount (₹' . number_format($tokenAmount, 2) . ') to confirm your booking.' . $planNote);
            return $this->redirect('/booking/' . $bookingId . '/pay');
        } catch (\Throwable $e) {
            try { $this->db->rollBack(); } catch (\Throwable $ignored) { error_log($ignored->getMessage()); }
            error_log('PlotBookingController::storeBooking error: ' . $e->getMessage());
            $this->setFlash('error', 'We could not complete your booking. Please try again.');
            return $this->redirect('/plot/' . $plotId . '/book');
        }
    }

    /**
     * Show booking confirmation / payment page
     */
    public function bookingConfirmation($bookingId)
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        $booking = $this->getBookingWithDetails((int)$bookingId, (int)$user['id']);

        if (!$booking) {
            $this->setFlash('error', 'Booking not found');
            return $this->redirect('/user/dashboard');
        }

        $emis = $this->getBookingEmis((int)$bookingId);

        $this->layout = 'layouts/customer';
        $this->render('pages/booking_confirmation', [
            'page_title' => 'Booking Confirmation #' . $bookingId,
            'booking' => $booking,
            'emis' => $emis,
            'user' => $user,
        ]);
    }

    /**
     * Printable booking receipt (renders the receipt view directly without
     * the customer layout; returns normally so the framework cycle completes).
     */
    public function receipt($bookingId)
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        try {
            $booking = $this->getBookingWithDetails((int)$bookingId, (int)$user['id']);

            if (!$booking) {
                echo '<h2>Booking not found</h2><a href="' . BASE_URL . '/user/dashboard">Back to Dashboard</a>';
                return;
            }

            $emis = $this->getBookingEmis((int)$bookingId);
            $currentStatus = $booking['status'] ?? 'pending';

            $viewFile = __DIR__ . '/../../views/pages/booking_receipt.php';
            if (file_exists($viewFile)) {
                require $viewFile;
            } else {
                echo '<h2>View file not found</h2>';
            }
        } catch (\Throwable $e) {
            error_log('PlotBookingController::receipt error: ' . $e->getMessage());
            echo '<h2>We could not load this receipt. Please try again later.</h2>';
        }
        return;
    }
}
