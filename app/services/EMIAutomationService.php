<?php

namespace App\Services;

use App\Core\Database;
use App\Services\NotificationService;
use Exception;

/**
 * Phase 30: EMI Automation Service — rewritten to use correct Module 2 tables.
 * Tables: booking_payment_schedules, plot_bookings, plots, users, dunning_log
 */
class EMIAutomationService
{
    use \App\Traits\ServiceTenantTrait;

    protected $db;
    protected $notificationService;
    protected $rootPath;

    public function __construct($db = null)
    {
        $this->db = $db ?? Database::getInstance()->getConnection();
        $this->notificationService = new NotificationService($this->db);
        $this->rootPath = dirname(__DIR__, 2);
    }

    /**
     * Run all EMI automation tasks (cron entry point).
     */
    public function runAll(): array
    {
        $results = [
            'status_update'   => false,
            'penalties'       => false,
            'reminders'       => false,
            'dunning'         => false,
            'defaults_check'  => false,
            'auto_payments'   => null,
        ];

        try {
            $this->db->beginTransaction();

            $results['status_update']  = $this->updateInstallmentStatus();
            $results['penalties']      = $this->applyDailyPenalties();
            $results['reminders']      = $this->sendUpcomingPaymentReminders();
            $results['dunning']        = $this->sendDunningEmails();
            $results['defaults_check'] = $this->checkDefaultedBookings();
            $results['auto_payments']  = $this->runAutoPaymentCron();

            $this->db->commit();
            return $results;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("EMI Automation Error: " . $e->getMessage());
            return $results;
        }
    }

    /**
     * Run EMI auto-payment processing via mandates.
     * Called from runAll() or standalone cron.
     *
     * @return array|null  Result from EMIAutoPaymentService::processDueEmiPayments()
     */
    public function runAutoPaymentCron(): ?array
    {
        try {
            $autoPayService = new \App\Services\Payment\EMIAutoPaymentService($this->db);
            return $autoPayService->processDueEmiPayments();
        } catch (Exception $e) {
            error_log("runAutoPaymentCron: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Mark overdue installments: pending → overdue when past due_date.
     */
    public function updateInstallmentStatus(): bool
    {
        try {
            $sql = "UPDATE booking_payment_schedules
                    SET status = 'overdue', updated_at = NOW()
                    WHERE status = 'pending'
                      AND due_date < CURDATE()"
                  . $this->tenantSql();
            $this->db->exec($sql);
            return true;
        } catch (\Exception $e) {
            error_log("updateInstallmentStatus: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Apply daily penalty (18% p.a.) after 5-day grace period.
     * Same logic as MoneyWorkflowService::applyDailyPenalties but called from cron.
     */
    public function applyDailyPenalties(): bool
    {
        try {
            $sql = "SELECT bps.id, bps.booking_id, bps.amount, bps.due_date, bps.accrued_penalty,
                           pb.booking_date,
                           DATEDIFF(CURDATE(), bps.due_date) AS days_overdue
                    FROM booking_payment_schedules bps
                    LEFT JOIN plot_bookings pb ON pb.id = bps.booking_id
                    WHERE bps.status IN ('overdue','pending')
                      AND bps.due_date < DATE_SUB(CURDATE(), INTERVAL 5 DAY)
                      AND bps.due_date >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)";
            $rows = $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            $advanceCache = [];

            foreach ($rows as $row) {
                $bookingId = (int)$row['booking_id'];

                // 1. Advance Payment Check
                if (!isset($advanceCache[$bookingId])) {
                    $stmtPaid = $this->db->prepare("SELECT COALESCE(SUM(paid_amount), 0) AS total FROM booking_payment_schedules WHERE booking_id = ?");
                    $stmtPaid->execute([$bookingId]);
                    $totalPaid = (float)$stmtPaid->fetchColumn();

                    $stmtScheduled = $this->db->prepare("SELECT COALESCE(SUM(amount), 0) AS total FROM booking_payment_schedules WHERE booking_id = ? AND due_date <= CURDATE()");
                    $stmtScheduled->execute([$bookingId]);
                    $totalScheduled = (float)$stmtScheduled->fetchColumn();

                    $advanceCache[$bookingId] = ($totalPaid >= $totalScheduled);
                }

                if ($advanceCache[$bookingId]) {
                    // Skip completely: customer has paid in advance
                    continue;
                }

                // 2. 3-Year Interest Free Check
                $bookingDate = $row['booking_date'] ?? null;
                $isInterestFree = false;
                if ($bookingDate) {
                    $bDate = new \DateTime($bookingDate);
                    $dDate = new \DateTime($row['due_date']);
                    $threeYearsLimit = (clone $bDate)->modify('+3 years');
                    if ($dDate <= $threeYearsLimit) {
                        $isInterestFree = true;
                    }
                }

                // 3. Lose Interest-Free status if 3 consecutive bounces
                if ($isInterestFree) {
                    if ($this->hasThreeConsecutiveOverdueEMIs($bookingId)) {
                        $isInterestFree = false;
                    }
                }

                $days = (int)$row['days_overdue'];

                if ($isInterestFree) {
                    $penalty = 0.0;
                } else {
                    $penalty = round((float)$row['amount'] * 0.18 * $days / 365, 2);
                }

                $currentAccrued = (float)($row['accrued_penalty'] ?? 0);
                if ($isInterestFree || $penalty > $currentAccrued) {
                    $uStmt = $this->db->prepare(
                        "UPDATE booking_payment_schedules
                         SET accrued_penalty = ?, status = 'overdue', updated_at = NOW()
                         WHERE id = ?" . $this->tenantSql()
                    );
                    $uParams = [$penalty, $row['id']];
                    if ($this->tenantId() > 1) $uParams[] = $this->tenantId();
                    $uStmt->execute($uParams);
                } else {
                    $uStmt = $this->db->prepare(
                        "UPDATE booking_payment_schedules
                         SET status = 'overdue', updated_at = NOW()
                         WHERE id = ?" . $this->tenantSql()
                    );
                    $uParams = [$row['id']];
                    if ($this->tenantId() > 1) $uParams[] = $this->tenantId();
                    $uStmt->execute($uParams);
                }
            }
            return true;
        } catch (\Exception $e) {
            error_log("applyDailyPenalties: " . $e->getMessage());
            return false;
        }
    }

    private function hasThreeConsecutiveOverdueEMIs(int $bookingId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT status, due_date FROM booking_payment_schedules 
             WHERE booking_id = ? 
             ORDER BY installment_no ASC"
        );
        $stmt->execute([$bookingId]);
        $installments = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $consecutive = 0;
        $today = date('Y-m-d');
        foreach ($installments as $inst) {
            $isUnpaid = ($inst['status'] !== 'paid');
            $isPastDue = ($inst['due_date'] < $today);
            if ($isUnpaid && $isPastDue) {
                $consecutive++;
                if ($consecutive >= 3) {
                    return true;
                }
            } else {
                $consecutive = 0;
            }
        }
        return false;
    }

    /**
     * Send upcoming payment reminders (due within 3 days).
     */
    public function sendUpcomingPaymentReminders(): bool
    {
        try {
            $rows = $this->db->query(
                "SELECT bps.id, bps.booking_id, bps.installment_no, bps.amount, bps.due_date,
                        pb.booking_number, pb.customer_id,
                        p.plot_number,
                        c.name AS colony_name,
                        u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone
                 FROM booking_payment_schedules bps
                 JOIN plot_bookings pb ON pb.id = bps.booking_id
                 JOIN plots p ON p.id = pb.plot_id
                 JOIN colonies c ON c.id = p.colony_id
                 JOIN users u ON u.id = pb.customer_id
                 WHERE bps.status = 'pending'
                   AND bps.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)
                   AND bps.reminder_count = 0"
            )->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            foreach ($rows as $row) {
                $sent = $this->sendEmail(
                    $row['customer_email'],
                    "Upcoming EMI Reminder - {$row['booking_number']}",
                    $this->renderEmail('dunning_reminder', $row)
                );
                if ($sent) {
                    $upStmt = $this->db->prepare(
                        "UPDATE booking_payment_schedules
                         SET reminder_count = reminder_count + 1, last_reminder_at = NOW()
                         WHERE id = ?" . $this->tenantSql()
                    );
                    $upParams = [$row['id']];
                    if ($this->tenantId() > 1) $upParams[] = $this->tenantId();
                    $upStmt->execute($upParams);
                    $this->logDunning($row, 'reminder', 'email');
                }
            }
            return true;
        } catch (\Exception $e) {
            error_log("sendUpcomingPaymentReminders: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send graduated dunning emails based on days overdue.
     * Tiers: 7 days → overdue_7, 14 → overdue_14, 30 → overdue_30, 60 → overdue_60, 90 → final_demand
     */
    public function sendDunningEmails(): bool
    {
        $tierConfig = [
            7  => ['tier' => 'overdue_7',   'template' => 'dunning_overdue',     'subject_prefix' => 'Payment Overdue — 7 Days'],
            14 => ['tier' => 'overdue_14',  'template' => 'dunning_overdue',     'subject_prefix' => 'Payment Overdue — 14 Days'],
            30 => ['tier' => 'overdue_30',  'template' => 'dunning_overdue',     'subject_prefix' => 'Payment Overdue — 30 Days'],
            60 => ['tier' => 'overdue_60',  'template' => 'dunning_final_demand','subject_prefix' => 'FINAL DEMAND — 60 Days'],
            90 => ['tier' => 'overdue_90',  'template' => 'dunning_defaulted',   'subject_prefix' => 'BOOKING DEFAULTED — 90+ Days'],
        ];

        try {
            $rows = $this->db->query(
                "SELECT bps.*, pb.booking_number, pb.customer_id,
                        p.plot_number,
                        c.name AS colony_name,
                        u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone,
                        DATEDIFF(CURDATE(), bps.due_date) AS days_overdue
                 FROM booking_payment_schedules bps
                 JOIN plot_bookings pb ON pb.id = bps.booking_id
                 JOIN plots p ON p.id = pb.plot_id
                 JOIN colonies c ON c.id = p.colony_id
                 JOIN users u ON u.id = pb.customer_id
                 WHERE bps.status IN ('overdue','pending')
                   AND bps.due_date < DATE_SUB(CURDATE(), INTERVAL 5 DAY)
                 ORDER BY bps.due_date ASC"
            )->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            foreach ($rows as $row) {
                $days = (int)$row['days_overdue'];
                $tier = $this->getDunningTier($days, $tierConfig);
                if (!$tier) continue;

                // Check if we already sent this tier
                $already = $this->db->prepare(
                    "SELECT COUNT(*) FROM dunning_log
                     WHERE installment_id = ? AND dunning_tier = ? AND status = 'sent'"
                );
                $already->execute([$row['id'], $tier['tier']]);
                if ((int)$already->fetchColumn() > 0) continue;

                // For defaulted tier, check at least 90 days
                if ($tier['tier'] === 'overdue_90' && $days < 90) continue;

                $totalDue = (float)$row['amount'] + (float)($row['accrued_penalty'] ?? 0);

                $emailData = array_merge($row, [
                    'total_due'    => number_format($totalDue),
                    'total_overdue'=> number_format($totalDue),
                    'total_penalty'=> number_format((float)($row['accrued_penalty'] ?? 0)),
                    'penalty'      => number_format((float)($row['accrued_penalty'] ?? 0)),
                    'payment_url'  => BASE_URL . '/user/bookings/' . $row['booking_id'],
                ]);

                $sent = $this->sendEmail(
                    $row['customer_email'],
                    "{$tier['subject_prefix']} — {$row['booking_number']}",
                    $this->renderEmail($tier['template'], $emailData)
                );

                $esStmt = $this->db->prepare(
                    "UPDATE booking_payment_schedules
                     SET escalation_level = ?, reminder_count = reminder_count + 1, last_reminder_at = NOW()
                     WHERE id = ?" . $this->tenantSql()
                );
                $esParams = [$this->tierLevel($tier['tier']), $row['id']];
                if ($this->tenantId() > 1) $esParams[] = $this->tenantId();
                $esStmt->execute($esParams);

                $this->logDunning($row, $tier['tier'], 'email', $sent ? 'sent' : 'failed');

                // At 90+ days, mark as defaulted in the booking (RERA compliance: 'cancelled' hides from regulatory tracking)
                if ($tier['tier'] === 'overdue_90') {
                    $pbStmt = $this->db->prepare(
                        "UPDATE plot_bookings SET status = 'defaulted', updated_at = NOW()
                         WHERE id = ? AND status = 'emi_active'" . $this->tenantSql()
                    );
                    $pbParams = [$row['booking_id']];
                    if ($this->tenantId() > 1) $pbParams[] = $this->tenantId();
                    $pbStmt->execute($pbParams);
                }
            }
            return true;
        } catch (\Exception $e) {
            error_log("sendDunningEmails: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check for defaulted bookings (90+ days overdue, 3+ consecutive missed payments).
     * Notify admins.
     */
    public function checkDefaultedBookings(): bool
    {
        try {
            $rows = $this->db->query(
                "SELECT pb.id AS booking_id, pb.booking_number, pb.customer_id,
                        COUNT(bps.id) AS overdue_count,
                        MAX(DATEDIFF(CURDATE(), bps.due_date)) AS worst_days,
                        SUM(bps.amount) AS total_overdue,
                        SUM(bps.accrued_penalty) AS total_penalty,
                        u.name AS customer_name, u.email AS customer_email,
                        p.plot_number, col.name AS colony_name
                 FROM plot_bookings pb
                 JOIN booking_payment_schedules bps ON bps.booking_id = pb.id
                 JOIN plots p ON p.id = pb.plot_id
                 JOIN colonies col ON col.id = p.colony_id
                 JOIN users u ON u.id = pb.customer_id
                 WHERE bps.status IN ('overdue','pending')
                   AND bps.due_date < DATE_SUB(CURDATE(), INTERVAL 90 DAY)
                 GROUP BY pb.id
                 HAVING overdue_count >= 2"
            )->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            foreach ($rows as $row) {
                // Notify admin
                $adminEmail = $_ENV['ADMIN_EMAIL'] ?? 'admin@apsdreamhome.com';
                $this->sendEmail(
                    $adminEmail,
                    "EMI Default Alert: {$row['customer_name']} ({$row['booking_number']})",
                    $this->renderEmail('dunning_defaulted', array_merge($row, [
                        'total_overdue' => number_format((float)$row['total_overdue']),
                        'total_penalty' => number_format((float)$row['total_penalty']),
                        'days_overdue'  => $row['worst_days'],
                        'payment_url'   => BASE_URL . '/admin/bookings/' . $row['booking_id'],
                    ]))
                );
            }
            return true;
        } catch (\Exception $e) {
            error_log("checkDefaultedBookings: " . $e->getMessage());
            return false;
        }
    }

    // ---- Helpers ----

    private function getDunningTier(int $days, array $config): ?array
    {
        $selected = null;
        foreach ($config as $threshold => $tier) {
            if ($days >= $threshold) {
                $selected = $tier;
            }
        }
        return $selected;
    }

    private function tierLevel(string $tier): int
    {
        $map = [
            'reminder' => 0, 'overdue_7' => 1, 'overdue_14' => 2,
            'overdue_30' => 3, 'overdue_60' => 4, 'overdue_90' => 5,
            'defaulted' => 6,
        ];
        return $map[$tier] ?? 0;
    }

    private function renderEmail(string $template, array $data): string
    {
        $path = $this->rootPath . "/app/views/emails/{$template}.php";
        if (!file_exists($path)) {
            return "<p>Email template {$template} not found.</p>";
        }
        ob_start();
        extract($data, EXTR_SKIP);
        include $path;
        return ob_get_clean();
    }

    private function sendEmail(string $to, string $subject, string $html): bool
    {
        if (empty($to)) return false;
        try {
            $from = $_ENV['SMTP_FROM_EMAIL'] ?? 'notifications@apsdreamhome.com';
            $headers = "From: APS Dream Home <{$from}>\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            return @mail($to, $subject, $html, $headers);
        } catch (\Exception $e) {
            error_log("sendEmail error: " . $e->getMessage());
            return false;
        }
    }

    private function logDunning(array $row, string $tier, string $channel, string $status = 'sent'): void
    {
        try {
            $dlCols = "booking_id, installment_id, customer_id, dunning_tier, channel, subject, status, days_overdue, penalty_amount, created_at";
            $dlVals = "?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()";
            $dlParams = [
                $row['booking_id'],
                $row['id'],
                $row['customer_id'] ?? null,
                $tier,
                $channel,
                "Dunning: {$tier}",
                $status,
                $row['days_overdue'] ?? 0,
                $row['accrued_penalty'] ?? 0,
            ];
            if ($this->tenantId() > 1) {
                $dlCols .= ", tenant_id";
                $dlVals .= ", ?";
                $dlParams[] = $this->tenantId();
            }
            $this->db->prepare(
                "INSERT INTO dunning_log ({$dlCols}) VALUES ({$dlVals})"
            )->execute($dlParams);
        } catch (\Exception $e) {
            error_log("logDunning: " . $e->getMessage());
        }
    }
}
