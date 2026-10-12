<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Services\Accounting\RegistryNocService;

/**
 * Enterprise Sub-Registrar Office (SRO) Registry Appointment & Legal Execution Service
 * Manages customer registry appointment slots, Gorakhpur SRO office assignment,
 * UP Stamp Duty calculations, witness verifications, and deed handover.
 */
class RegistryAppointmentService
{
    private \PDO $pdo;
    private RegistryNocService $nocService;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
        $this->nocService = new RegistryNocService();
    }

    /**
     * Customer requests a Registry Appointment for their plot booking.
     */
    public function requestAppointment(int $bookingId, array $data): array
    {
        // 1. Verify booking exists
        $st = $this->pdo->prepare(
            "SELECT pb.*, p.plot_number, c.name AS colony_name
             FROM plot_bookings pb
             JOIN plots p ON p.id = pb.plot_id
             JOIN colonies c ON c.id = p.colony_id
             WHERE pb.id = ?"
        );
        $st->execute([$bookingId]);
        $booking = $st->fetch(\PDO::FETCH_ASSOC);

        if (!$booking) {
            return ['success' => false, 'message' => 'Plot booking record not found.'];
        }

        // 2. Pre-registry clearance check via RegistryNocService
        $noDues = $this->nocService->checkNoDues($bookingId);
        if (!$noDues) {
            return ['success' => false, 'message' => 'Plot not cleared for registry: Pending financial dues or society maintenance charges exist.'];
        }

        // 3. Prevent duplicate active appointment
        $chk = $this->pdo->prepare(
            "SELECT id FROM registry_appointments
             WHERE booking_id = ? AND status IN ('requested', 'eligible', 'slot_confirmed')
             LIMIT 1"
        );
        $chk->execute([$bookingId]);
        if ($chk->fetch()) {
            return ['success' => false, 'message' => 'An active registry appointment is already scheduled or under review for this booking.'];
        }

        // 4. Calculate UP Stamp Duty and Registration Fee
        $plotValue = (float)($booking['total_plot_value'] ?? $booking['total_amount'] ?? 0);
        $gender = strtolower(trim((string)($data['buyer_gender'] ?? 'male')));
        $stampPct = ($gender === 'female') ? 6.0 : (($gender === 'joint') ? 6.5 : 7.0);
        $stampDuty = round(($plotValue * $stampPct) / 100, 2);
        // UP SRO Registration fee: 1% capped at ₹20,000 for standard residential deeds
        $regFee = min(20000.0, round(($plotValue * 1.0) / 100, 2));

        // Generate Token: APS-REG-GOR-YEAR-XXXX
        $year = date('Y');
        $random = strtoupper(substr(uniqid(), -4));
        $token = "APS-REG-GOR-{$year}-{$random}";

        $sro = trim((string)($data['sro_office'] ?? 'Tehsil Sadar Gorakhpur'));
        $prefDate1 = !empty($data['preferred_date_1']) ? $data['preferred_date_1'] : date('Y-m-d', strtotime('+3 days'));
        $prefDate2 = !empty($data['preferred_date_2']) ? $data['preferred_date_2'] : null;

        $ins = $this->pdo->prepare(
            "INSERT INTO registry_appointments (
                tenant_id, booking_id, appointment_token, sro_office,
                preferred_date_1, preferred_date_2,
                witness_1_name, witness_1_phone, witness_1_aadhaar,
                witness_2_name, witness_2_phone, witness_2_aadhaar,
                stamp_duty_amount, reg_fee_amount,
                status, notes, created_at
            ) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'requested', ?, NOW())"
        );
        $ins->execute([
            $bookingId,
            $token,
            $sro,
            $prefDate1,
            $prefDate2,
            trim((string)($data['witness_1_name'] ?? '')),
            trim((string)($data['witness_1_phone'] ?? '')),
            trim((string)($data['witness_1_aadhaar'] ?? '')),
            trim((string)($data['witness_2_name'] ?? '')),
            trim((string)($data['witness_2_phone'] ?? '')),
            trim((string)($data['witness_2_aadhaar'] ?? '')),
            $stampDuty,
            $regFee,
            trim((string)($data['notes'] ?? ''))
        ]);

        $id = (int)$this->pdo->lastInsertId();

        return [
            'success' => true,
            'appointment_id' => $id,
            'appointment_token' => $token,
            'stamp_duty' => $stampDuty,
            'reg_fee' => $regFee,
            'message' => "Registry appointment requested! Your Token: {$token}"
        ];
    }

    /**
     * Admin confirms registry appointment date slot and assigns executive.
     */
    public function confirmSlot(int $id, string $confirmedDate, string $executiveName, string $executivePhone, string $notes = ''): array
    {
        $st = $this->pdo->prepare(
            "UPDATE registry_appointments
             SET confirmed_date = ?,
                 assigned_executive_name = ?,
                 assigned_executive_phone = ?,
                 notes = CASE WHEN ? != '' THEN ? ELSE notes END,
                 status = 'slot_confirmed',
                 updated_at = NOW()
             WHERE id = ?"
        );
        $st->execute([
            $confirmedDate,
            trim($executiveName),
            trim($executivePhone),
            trim($notes),
            trim($notes),
            $id
        ]);

        return ['success' => true, 'message' => "Registry slot confirmed for {$confirmedDate}."];
    }

    /**
     * Complete registry execution with registered deed number & document upload.
     */
    public function completeRegistration(int $id, string $deedNumber, ?string $deedFile = null): array
    {
        $st = $this->pdo->prepare(
            "UPDATE registry_appointments
             SET deed_registration_number = ?,
                 registered_deed_file = COALESCE(?, registered_deed_file),
                 status = 'completed',
                 updated_at = NOW()
             WHERE id = ?"
        );
        $st->execute([trim($deedNumber), $deedFile, $id]);

        // Fetch booking ID to update plot booking status
        $bSt = $this->pdo->prepare("SELECT booking_id FROM registry_appointments WHERE id = ?");
        $bSt->execute([$id]);
        $bookingId = (int)$bSt->fetchColumn();

        if ($bookingId > 0) {
            $this->pdo->prepare(
                "UPDATE plot_bookings SET status = 'registration_done', updated_at = NOW() WHERE id = ?"
            )->execute([$bookingId]);
        }

        return ['success' => true, 'message' => "Registration deed #{$deedNumber} recorded and marked completed."];
    }

    /**
     * Customer appointments list.
     */
    public function getCustomerAppointments(int $userId): array
    {
        $st = $this->pdo->prepare(
            "SELECT ra.*, pb.booking_number, pb.total_plot_value, p.plot_number, c.name AS colony_name
             FROM registry_appointments ra
             JOIN plot_bookings pb ON pb.id = ra.booking_id
             JOIN plots p ON p.id = pb.plot_id
             JOIN colonies c ON c.id = p.colony_id
             WHERE pb.customer_id = ?
             ORDER BY ra.created_at DESC"
        );
        $st->execute([$userId]);
        return $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Detailed appointment for slip/gatepass printing and admin review.
     */
    public function getAppointmentDetail(int $id): ?array
    {
        $st = $this->pdo->prepare(
            "SELECT ra.*, pb.booking_number, pb.total_plot_value, p.plot_number, p.area_sqft,
                    c.name AS colony_name, c.location AS colony_location,
                    u.name AS customer_name, u.phone AS customer_phone, u.email AS customer_email
             FROM registry_appointments ra
             JOIN plot_bookings pb ON pb.id = ra.booking_id
             JOIN plots p ON p.id = pb.plot_id
             JOIN colonies c ON c.id = p.colony_id
             JOIN users u ON u.id = pb.customer_id
             WHERE ra.id = ?"
        );
        $st->execute([$id]);
        return $st->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Admin registry desk appointments with filter.
     */
    public function getAllAppointments(?string $status = null): array
    {
        $sql = "SELECT ra.*, pb.booking_number, p.plot_number, c.name AS colony_name,
                       u.name AS customer_name, u.phone AS customer_phone
                FROM registry_appointments ra
                JOIN plot_bookings pb ON pb.id = ra.booking_id
                JOIN plots p ON p.id = pb.plot_id
                JOIN colonies c ON c.id = p.colony_id
                JOIN users u ON u.id = pb.customer_id";

        $params = [];
        if ($status && $status !== 'all') {
            $sql .= " WHERE ra.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY ra.created_at DESC";

        $st = $this->pdo->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }
}
