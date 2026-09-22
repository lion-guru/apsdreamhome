<?php
/**
 * JourneyStageNotifier — Centralized trigger for customer journey stage notifications
 *
 * Maps the 7-stage journey to multi-channel notifications (Email, SMS, WhatsApp, Push).
 * Called from: CustomerJourneyController, BookingController, RegistryController, cron, etc.
 */

namespace App\Services\Communication;

use App\Services\NotificationService;

class JourneyStageNotifier
{
    private NotificationService $notifier;

    // Journey stages in order (match CustomerJourneyController::buildRegistryStages)
    private const STAGES = [
        'allotment_done'       => ['label' => 'Token & Allotment',       'methods' => ['booking_confirmed']],
        'agreement_signed'     => ['label' => 'Agreement for Sale',     'methods' => ['agreement_generated']],
        'noc_cleared'          => ['label' => 'NOC Verification',       'methods' => []],
        'stamp_duty_paid'      => ['label' => 'Stamp Duty & Valuation', 'methods' => []],
        'appointment_scheduled'=> ['label' => 'Sub-Registrar Appointment','methods' => []],
        'registered'           => ['label' => 'Registry Executed',      'methods' => ['registry_update']],
        'completed'            => ['label' => 'Mutation & Possession',  'methods' => ['possession_completed']],
    ];

    public function __construct(NotificationService $notifier = null, $db = null)
    {
        $this->notifier = $notifier ?? new NotificationService($db ?? \App\Core\Database\Database::getInstance());
    }

    /**
     * Trigger notification for a stage completion.
     *
     * @param int    $bookingId   Booking ID (plot_bookings or bookings)
     * @param string $stageKey    One of the 7 stage keys above
     * @param array  $extraData   Additional data (e.g., appointment date, registry number)
     */
    public function onStageCompleted(int $bookingId, string $stageKey, array $extraData = []): array
    {
        if (!isset(self::STAGES[$stageKey])) {
            return ['success' => false, 'error' => "Unknown stage: $stageKey"];
        }

        $stage = self::STAGES[$stageKey];
        $results = [];

        foreach ($stage['methods'] as $method) {
            if (method_exists($this->notifier, $method)) {
                try {
                    if ($method === 'registry_update') {
                        $this->notifier->$method($bookingId, $stageKey, ...array_values($extraData));
                    } elseif ($method === 'possession_completed') {
                        $this->notifier->$method($bookingId);
                    } else {
                        $this->notifier->$method($bookingId, ...array_values($extraData));
                    }
                    $results[$method] = ['success' => true];
                } catch (\Throwable $e) {
                    $results[$method] = ['success' => false, 'error' => $e->getMessage()];
                    error_log("JourneyStageNotifier::$method failed: " . $e->getMessage());
                }
            }
        }

        // Log the stage completion for audit
        $this->logStageEvent($bookingId, $stageKey, $extraData);

        return ['success' => true, 'stage' => $stageKey, 'label' => $stage['label'], 'notifications' => $results];
    }

    /**
     * Trigger payment received notification (for EMI payments)
     */
    public function onPaymentReceived(int $bookingId, float $amount, array $extraData = []): array
    {
        try {
            $this->notifier->sendPaymentReceived($bookingId, $amount);
            return ['success' => true];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Trigger payment thank-you (Biziverse-style "thank you on receipt")
     */
    public function onPaymentThankYou(int $bookingId, float $amount): array
    {
        try {
            $userId = $this->notifier->getBookingCustomerUserId($bookingId);
            if (!$userId) return ['success' => false, 'error' => 'Customer not found'];

            $title = 'Thank You for Your Payment';
            $message = 'We have received your payment of ₹' . number_format($amount) . ' for booking #' . $bookingId . '. Thank you for your timely payment!';
            $data = ['event_type' => 'payment_thank_you', 'booking_id' => $bookingId, 'amount' => $amount, 'action_url' => '/user/bookings/' . $bookingId];

            $this->notifier->send($userId, 'email', $title, $message, $data);
            $this->notifier->send($userId, 'whatsapp', $title, $message, $data);
            $this->notifier->send($userId, 'push', $title, $message, $data);

            return ['success' => true];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get available stages and their notification methods
     */
    public static function getStages(): array
    {
        return self::STAGES;
    }

    /**
     * Log stage event for audit trail
     */
    private function logStageEvent(int $bookingId, string $stageKey, array $extraData): void
    {
        try {
            $db = \App\Core\Database\Database::getInstance();
            $db->execute("
                INSERT INTO journey_stage_log (booking_id, stage_key, stage_label, extra_data, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ", [$bookingId, $stageKey, self::STAGES[$stageKey]['label'] ?? $stageKey, json_encode($extraData)]);
        } catch (\Throwable $e) {
            // Table might not exist yet - fail silently
        }
    }
}