<?php
/**
 * Customer Unified Journey — merges Payment Ledger (Passbook) + Registry Timeline
 * Route: /customer/journey/{bookingId}
 */

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Front\PlotBaseController;
use App\Traits\TenantAwareTrait;
use App\Services\Communication\JourneyStageNotifier;

class CustomerJourneyController extends PlotBaseController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
    }

    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    private function pdo(): \PDO
    {
        return $this->db->getConnection();
    }

    /**
     * Unified journey page for a booking
     */
    public function journey($bookingId)
    {
        $customerId = (int)$_SESSION['user_id'];
        [$tSql, $tParams] = $this->tenantWhere();

        $bookingId = (int)$bookingId;
        if ($bookingId <= 0) {
            $this->setFlash('error', 'Invalid booking');
            $this->redirect('/customer/passbook');
        }

        try {
            $db = $this->pdo();

            // Fetch booking with ownership check (plot_bookings or bookings)
            $booking = $db->fetch("
                SELECT pb.*, p.plot_number, p.block, p.area_sqft, p.width_ft, p.length_ft,
                       p.facing, p.corner_plot, c.name as colony_name, c.slug as colony_slug,
                       u.name as customer_name, u.phone as customer_phone, u.email as customer_email
                FROM plot_bookings pb
                JOIN plots p ON pb.plot_id = p.id
                JOIN colonies c ON pb.colony_id = c.id
                LEFT JOIN users u ON u.id = pb.customer_id
                WHERE pb.id = ? AND pb.customer_id = ?{$tSql}
            ", array_merge([$bookingId, $customerId], $tParams));

            if (!$booking) {
                // Try bookings table (canonical registry flow)
                $booking = $db->fetch("
                    SELECT b.*, p.plot_number, p.block, p.area_sqft, p.width_ft, p.length_ft,
                           p.facing, p.corner_plot, c.name as colony_name, c.slug as colony_slug,
                           u.name as customer_name, u.phone as customer_phone, u.email as customer_email
                    FROM bookings b
                    LEFT JOIN plots p ON p.id = b.plot_id
                    LEFT JOIN colonies c ON c.id = COALESCE(b.colony_id, p.colony_id)
                    LEFT JOIN users u ON u.id = COALESCE(b.customer_id, b.user_id)
                    WHERE b.id = ? AND (b.customer_id = ? OR b.user_id = ?){$tSql}
                ", array_merge([$bookingId, $customerId, $customerId], $tParams));
            }

            if (!$booking) {
                $this->setFlash('error', 'Booking not found or access denied');
                $this->redirect('/customer/passbook');
            }

            // ---- PAYMENT LEDGER (from passbook logic) ----
            $paymentSchedules = [];
            $paymentRecords = [];
            $stats = [
                'total_investment'  => 0,
                'total_paid'        => 0,
                'total_outstanding' => 0,
                'next_emi_date'     => null,
                'overdue_count'     => 0,
                'overdue_amount'    => 0,
            ];

            // Fetch payment schedules
            $schedStmt = $db->prepare("SELECT * FROM booking_payment_schedules WHERE booking_id = ? ORDER BY installment_no ASC");
            $schedStmt->execute([$bookingId]);
            $paymentSchedules = $schedStmt->fetchAll(\PDO::FETCH_ASSOC);

            // Fetch payment records
            $paySql = "SELECT p.* FROM payments p WHERE p.booking_id = ?{$tSql} ORDER BY p.created_at ASC";
            $payParams = array_merge([$bookingId], $tParams);
            $payStmt = $db->prepare($paySql);
            $payStmt->execute($payParams);
            $paymentRecords = $payStmt->fetchAll(\PDO::FETCH_ASSOC);

            // Compute stats & unified ledger
            $unifiedLedger = [];
            $plotPrice = (float)($booking['total_plot_value'] ?? $booking['total_amount'] ?? 0);
            $stats['total_investment'] = $plotPrice;

            foreach ($paymentSchedules as $sch) {
                $paid = (float)($sch['paid_amount'] ?? 0);
                $due = (float)($sch['amount'] ?? 0);
                $status = $sch['status'] ?? 'pending';

                $stats['total_paid'] += $paid;
                if ($status === 'overdue' || ($status === 'pending' && strtotime($sch['due_date']) < time())) {
                    $stats['overdue_count']++;
                    $stats['overdue_amount'] += max(0, $due - $paid);
                }
                if ($status === 'pending' && ($stats['next_emi_date'] === null || $sch['due_date'] < $stats['next_emi_date'])) {
                    $stats['next_emi_date'] = $sch['due_date'];
                }

                $unifiedLedger[] = [
                    'date'        => $sch['due_date'],
                    'type'        => 'schedule',
                    'description' => 'EMI #' . ($sch['installment_no'] ?? '?') . ' Due',
                    'debit'       => $due,
                    'credit'      => 0,
                    'balance'     => null, // computed later
                    'status'      => $status,
                    'ref'         => $sch['installment_no'],
                    'paid_date'   => $sch['paid_date'] ?? null,
                    'paid_amount' => $paid,
                ];

                if ($paid > 0) {
                    $unifiedLedger[] = [
                        'date'        => $sch['paid_date'] ?? $sch['due_date'],
                        'type'        => 'payment',
                        'description' => 'EMI #' . ($sch['installment_no'] ?? '?') . ' Paid',
                        'debit'       => 0,
                        'credit'      => $paid,
                        'balance'     => null,
                        'status'      => 'paid',
                        'ref'         => $sch['installment_no'],
                        'paid_date'   => $sch['paid_date'],
                        'paid_amount' => $paid,
                    ];
                }
            }

            foreach ($paymentRecords as $rec) {
                $amt = (float)($rec['paid_amount'] ?? $rec['amount'] ?? 0);
                $gateway = $rec['gateway'] ?? 'N/A';
                $emiLabel = !empty($rec['emi_month']) ? ' (EMI #' . $rec['emi_month'] . ')' : '';
                $unifiedLedger[] = [
                    'date'        => $rec['created_at'],
                    'type'        => 'payment_record',
                    'description' => 'Payment via ' . $gateway . $emiLabel,
                    'debit'       => 0,
                    'credit'      => $amt,
                    'balance'     => null,
                    'status'      => 'paid',
                    'ref'         => $rec['transaction_ref'] ?? $rec['payment_reference'] ?? '',
                ];
                $stats['total_paid'] += $amt;
            }

            // Sort ledger by date
            usort($unifiedLedger, function($a, $b) {
                return strcmp($a['date'], $b['date']);
            });

            // Compute running balance
            $running = $plotPrice;
            foreach ($unifiedLedger as &$entry) {
                $running -= ($entry['credit'] ?? 0);
                $running += ($entry['debit'] ?? 0);
                $entry['balance'] = max(0, $running);
            }
            unset($entry);

            $stats['total_outstanding'] = max(0, $plotPrice - $stats['total_paid']);

            // ---- REGISTRY STAGES (from registryTimeline logic) ----
            $stages = $this->buildRegistryStages($db, $bookingId, $tSql, $tParams);
            $completedCount = 0;
            $currentIndex = -1;
            foreach ($stages as $i => $s) {
                if (!empty($s['completed'])) $completedCount++;
                if (empty($s['completed']) && $currentIndex === -1) $currentIndex = $i;
            }
            $progressPct = count($stages) > 0 ? round(($completedCount / count($stages)) * 100) : 0;

            // Determine if possession certificate can be downloaded
            $canDownload = false;
            if (!empty($stages[6]['completed']) || ($booking['possession_status'] ?? '') === 'handed_over' || ($booking['registry_status'] ?? '') === 'completed') {
                $canDownload = true;
            }

            // Fetch extras: agreement, NOC, registry deed, possession record
            $extras = [];
            try { $extras['agreement'] = $db->fetch("SELECT * FROM booking_agreements WHERE booking_id = ? ORDER BY created_at DESC LIMIT 1", [$bookingId]); } catch (\Throwable $e) {}
            try { $extras['noc'] = $db->fetch("SELECT * FROM noc_requests WHERE booking_id = ? ORDER BY created_at DESC LIMIT 1", [$bookingId]); } catch (\Throwable $e) {}
            try { $extras['deed'] = $db->fetch("SELECT * FROM booking_documents WHERE booking_id = ? AND document_type = 'registry_deed' ORDER BY created_at DESC LIMIT 1", [$bookingId]); } catch (\Throwable $e) {}
            try { $extras['possession'] = $db->fetch("SELECT * FROM possession_records WHERE booking_id = ? ORDER BY created_at DESC LIMIT 1", [$bookingId]); } catch (\Throwable $e) {}

            return $this->render('customer/journey', [
                'page_title' => 'Booking Journey',
                'page_description' => 'Complete journey: Payments, EMI Schedule & Registry Progress',
                'booking' => $booking,
                'stats' => $stats,
                'ledger' => $unifiedLedger,
                'stages' => $stages,
                'completed_count' => $completedCount,
                'current_index' => $currentIndex,
                'progress_pct' => $progressPct,
                'can_download' => $canDownload,
                'extras' => $extras,
            ]);
        } catch (\Throwable $e) {
            error_log('CustomerJourneyController error: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to load journey');
            $this->redirect('/customer/passbook');
        }
    }

    /**
     * Build registry stages (reused from CustomerPassbookController)
     */
    private function buildRegistryStages($db, $bookingId, $tSql, $tParams): array
    {
        $stages = [];

        // 1. Token & Allotment
        $stages[] = [
            'key' => 'allotment_done',
            'title' => 'Token & Allotment',
            'icon' => 'fa-handshake',
            'description' => 'Booking token paid and plot allotted',
            'completed' => true, // If we have a booking, token is done
            'meta' => [
                'booking_number' => $bookingId,
                'token_amount' => 0, // could fetch from token schedule
            ],
        ];

        // 2. Agreement for Sale
        $agreement = $extras['agreement'] ?? null;
        $agreementSigned = $agreement && in_array($agreement['status'] ?? '', ['signed','executed','registered']);
        $stages[] = [
            'key' => 'agreement_signed',
            'title' => 'Agreement for Sale',
            'icon' => 'fa-file-contract',
            'description' => 'Sale agreement executed between buyer and seller',
            'completed' => (bool)$agreementSigned,
            'meta' => $agreement ? [
                'status' => $agreement['status'] ?? 'draft',
                'agreement_date' => $agreement['agreement_date'] ?? null,
                'esign_status' => $agreement['esign_status'] ?? null,
            ] : [],
        ];

        // 3. NOC Verification
        $noc = $extras['noc'] ?? null;
        $nocCleared = $noc && ($noc['status'] ?? '') === 'approved';
        $stages[] = [
            'key' => 'noc_cleared',
            'title' => 'NOC Verification',
            'icon' => 'fa-shield-check',
            'description' => 'No Objection Certificate from authorities',
            'completed' => (bool)$nocCleared,
            'meta' => $noc ? ['status' => $noc['status'] ?? 'pending'] : [],
        ];

        // 4. Stamp Duty & Valuation
        $stampPaid = (float)($booking['stamp_duty_amount'] ?? 0) > 0;
        $registryStatus = $booking['registry_status'] ?? '';
        $stages[] = [
            'key' => 'stamp_duty_paid',
            'title' => 'Stamp Duty & Valuation',
            'icon' => 'fa-stamp',
            'description' => 'Stamp duty paid and property valuation completed',
            'completed' => $stampPaid || in_array($registryStatus, ['appointment_scheduled','registered','mutation_pending','completed']),
            'meta' => ['stamp_duty_amount' => $booking['stamp_duty_amount'] ?? 0],
        ];

        // 5. Sub-Registrar Appointment
        $appointment = $booking['appointment_date'] ?? null;
        $stages[] = [
            'key' => 'appointment_scheduled',
            'title' => 'Sub-Registrar Appointment',
            'icon' => 'fa-calendar-check',
            'description' => 'Appointment booked at Sub-Registrar office for registry',
            'completed' => !empty($appointment),
            'meta' => [
                'appointment_date' => $appointment,
                'sub_registrar_office' => $booking['sub_registrar_office'] ?? null,
            ],
        ];

        // 6. Registry Executed
        $registryNum = $booking['registry_number'] ?? null;
        $registryDate = $booking['registry_date'] ?? null;
        $deed = $extras['deed'] ?? null;
        $stages[] = [
            'key' => 'registered',
            'title' => 'Registry Executed',
            'icon' => 'fa-file-signature',
            'description' => 'Property registered at Sub-Registrar office',
            'completed' => !empty($registryNum) || !empty($registryDate) || ($deed && !empty($deed['file_path'])),
            'meta' => [
                'registry_number' => $registryNum,
                'registry_date' => $registryDate,
                'deed_file' => $deed['file_path'] ?? null,
            ],
        ];

        // 7. Mutation & Possession
        $possession = $extras['possession'] ?? null;
        $possessed = ($booking['possession_status'] ?? '') === 'handed_over' || ($booking['registry_status'] ?? '') === 'completed' || ($possession && in_array($possession['status'] ?? '', ['handed_over','completed']));
        $stages[] = [
            'key' => 'completed',
            'title' => 'Mutation & Possession',
            'icon' => 'fa-key',
            'description' => 'Mutation completed and physical possession handed over',
            'completed' => (bool)$possessed,
            'meta' => [
                'mutation_number' => $booking['mutation_number'] ?? null,
                'possession_date' => $booking['possession_date'] ?? ($possession['possession_date'] ?? null),
                'possession_letter' => $possession['possession_letter_number'] ?? null,
            ],
        ];

        // Sequential normalization: first non-completed becomes 'in_progress'
        $foundInProgress = false;
        foreach ($stages as &$s) {
            if (!$foundInProgress && !$s['completed']) {
                $s['in_progress'] = true;
                $foundInProgress = true;
            } else {
                $s['in_progress'] = false;
            }
        }
        unset($s);

        return $stages;
    }

    /**
     * Update a journey stage and trigger notifications
     * POST /customer/journey/{bookingId}/stage
     */
    public function updateStage($bookingId)
    {
        $this->requireCustomerLogin();
        $customerId = (int)$_SESSION['user_id'];
        [$tSql, $tParams] = $this->tenantWhere();

        $bookingId = (int)$bookingId;
        $stageKey = trim($_POST['stage_key'] ?? '');
        $extraData = json_decode($_POST['extra_data'] ?? '{}', true);

        if (!$stageKey) {
            $this->json(['success' => false, 'error' => 'Stage key required'], 400);
        }

        // Verify ownership
        $db = $this->pdo();
        $booking = $db->fetch("
            SELECT pb.id FROM plot_bookings pb
            WHERE pb.id = ? AND pb.customer_id = ?{$tSql}
        ", array_merge([$bookingId, $customerId], $tParams));

        if (!$booking) {
            $booking = $db->fetch("
                SELECT b.id FROM bookings b
                WHERE b.id = ? AND (b.customer_id = ? OR b.user_id = ?){$tSql}
            ", array_merge([$bookingId, $customerId, $customerId], $tParams));
        }

        if (!$booking) {
            $this->json(['success' => false, 'error' => 'Booking not found or access denied'], 403);
        }

        // Trigger notifier
        $notifier = new JourneyStageNotifier(null, $this->pdo());
        $result = $notifier->onStageCompleted($bookingId, $stageKey, $extraData);

        $this->json($result);
    }

    /**
     * Trigger payment thank-you notification
     * POST /customer/journey/{bookingId}/payment-thank-you
     */
    public function paymentThankYou($bookingId)
    {
        $this->requireCustomerLogin();
        $customerId = (int)$_SESSION['user_id'];
        [$tSql, $tParams] = $this->tenantWhere();

        $bookingId = (int)$bookingId;
        $amount = (float)($_POST['amount'] ?? 0);

        if ($amount <= 0) {
            $this->json(['success' => false, 'error' => 'Invalid amount'], 400);
        }

        // Verify ownership
        $db = $this->pdo();
        $booking = $db->fetch("
            SELECT pb.id FROM plot_bookings pb
            WHERE pb.id = ? AND pb.customer_id = ?{$tSql}
        ", array_merge([$bookingId, $customerId], $tParams));

        if (!$booking) {
            $booking = $db->fetch("
                SELECT b.id FROM bookings b
                WHERE b.id = ? AND (b.customer_id = ? OR b.user_id = ?){$tSql}
            ", array_merge([$bookingId, $customerId, $customerId], $tParams));
        }

        if (!$booking) {
            $this->json(['success' => false, 'error' => 'Booking not found or access denied'], 403);
        }

        $notifier = new JourneyStageNotifier(null, $this->pdo());
        $result = $notifier->onPaymentThankYou($bookingId, $amount);

        $this->json($result);
    }

    /**
     * Trigger payment received notification (for EMI payments)
     * POST /customer/journey/{bookingId}/payment-received
     */
    public function paymentReceived($bookingId)
    {
        $this->requireCustomerLogin();
        $customerId = (int)$_SESSION['user_id'];
        [$tSql, $tParams] = $this->tenantWhere();

        $bookingId = (int)$bookingId;
        $amount = (float)($_POST['amount'] ?? 0);

        if ($amount <= 0) {
            $this->json(['success' => false, 'error' => 'Invalid amount'], 400);
        }

        // Verify ownership
        $db = $this->pdo();
        $booking = $db->fetch("
            SELECT pb.id FROM plot_bookings pb
            WHERE pb.id = ? AND pb.customer_id = ?{$tSql}
        ", array_merge([$bookingId, $customerId], $tParams));

        if (!$booking) {
            $booking = $db->fetch("
                SELECT b.id FROM bookings b
                WHERE b.id = ? AND (b.customer_id = ? OR b.user_id = ?){$tSql}
            ", array_merge([$bookingId, $customerId, $customerId], $tParams));
        }

        if (!$booking) {
            $this->json(['success' => false, 'error' => 'Booking not found or access denied'], 403);
        }

        $notifier = new JourneyStageNotifier(null, $this->pdo());
        $result = $notifier->onPaymentReceived($bookingId, (float)$_POST['amount']);

        $this->json($result);
    }
}