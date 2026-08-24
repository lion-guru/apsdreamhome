<?php

namespace App\Services\Accounting;

use App\Traits\ServiceTenantTrait;
use App\Core\Middleware\TenantContext;
use Exception;

/**
 * Registry & NOC Service
 * Handles registry eligibility checks and NOC generation
 */
class RegistryNocService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
    }

    public function checkRegistryEligibility(int $bookingId): array
    {
        $tid = TenantContext::getId();

        $booking = $this->db->fetchOne("
            SELECT pb.*, pl.plot_number, pl.area_sqft, c.name as customer_name, c.email, c.phone, c.pan, c.aadhaar
            FROM plot_bookings pb
            JOIN plots pl ON pl.id = pb.plot_id
            JOIN customers c ON c.id = pb.customer_id
            WHERE pb.id = ?" . ($tid > 1 ? " AND pb.tenant_id = ?" : ""),
            $tid > 1 ? [$bookingId, $tid] : [$bookingId]
        );

        if (!$booking) {
            return ['eligible' => false, 'error' => 'Booking not found'];
        }

        $checks = [
            'booking_status'       => $booking['status'] === 'fully_paid',
            'payment_complete'     => (float)$booking['total_paid'] >= (float)$booking['agreement_value'],
            'documents_submitted'  => $this->checkDocumentsSubmitted($bookingId),
            'no_dues'              => $this->checkNoDues($bookingId),
            'customer_kyc'         => $this->checkCustomerKyc($booking['customer_id']),
            'plot_clear'           => $this->checkPlotClear($booking['plot_id']),
        ];

        $eligible = array_reduce($checks, fn($carry, $item) => $carry && $item, true);
        $failed = array_filter($checks, fn($v) => !$v, ARRAY_FILTER_USE_BOTH);

        return [
            'eligible'       => $eligible,
            'booking_id'     => $bookingId,
            'checks'         => $checks,
            'failed_checks'  => array_keys($failed),
            'booking'        => $booking,
        ];
    }

    public function generateNoc(int $bookingId, int $generatedBy): array
    {
        $eligibility = $this->checkRegistryEligibility($bookingId);

        if (!$eligibility['eligible']) {
            return ['success' => false, 'error' => 'Booking not eligible for NOC', 'checks' => $eligibility['checks']];
        }

        $tid = TenantContext::getId();
        $nocNumber = 'NOC-' . date('Ymd') . '-' . str_pad($bookingId, 6, '0', STR_PAD_LEFT);

        $nocData = [
            'noc_number'       => $nocNumber,
            'booking_id'       => $bookingId,
            'generated_by'     => $generatedBy,
            'generated_at'     => date('Y-m-d H:i:s'),
            'valid_until'      => date('Y-m-d', strtotime('+90 days')),
            'status'           => 'issued',
            'tenant_id'        => $tid,
        ];
        $this->db->insert('noc_certificates', $nocData);
        $nocId = (int)$this->db->lastInsertId();

        // Generate NOC content
        $booking = $eligibility['booking'];
        $content = $this->generateNocContent($booking, $nocNumber, $generatedBy);

        return [
            'success'    => true,
            'noc_id'     => $nocId,
            'noc_number' => $nocNumber,
            'content'    => $content,
        ];
    }

    private function checkDocumentsSubmitted(int $bookingId): bool
    {
        $tid = TenantContext::getId();
        $stmt = $this->db->fetchOne("SELECT COUNT(*) as count FROM booking_documents WHERE booking_id = ? AND status = 'verified'" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$bookingId, $tid] : [$bookingId]);
        return ($stmt['count'] ?? 0) > 0;
    }

    private function checkNoDues(int $bookingId): bool
    {
        $tid = TenantContext::getId();
        // Check for any outstanding penalties, maintenance dues, etc.
        $penalties = $this->db->fetchOne("SELECT COALESCE(SUM(accrued_penalty - paid_penalty), 0) as pending FROM booking_payment_schedules WHERE booking_id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$bookingId, $tid] : [$bookingId]);
        $maintenance = $this->db->fetchOne("SELECT COALESCE(SUM(amount_due), 0) as pending FROM maintenance_dues WHERE booking_id = ? AND status = 'pending'" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$bookingId, $tid] : [$bookingId]);

        return (($penalties['pending'] ?? 0) <= 0) && (($maintenance['pending'] ?? 0) <= 0);
    }

    private function checkCustomerKyc(int $customerId): bool
    {
        $tid = TenantContext::getId();
        $stmt = $this->db->fetchOne("SELECT kyc_status FROM customers WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$customerId, $tid] : [$customerId]);
        return ($stmt['kyc_status'] ?? '') === 'verified';
    }

    private function checkPlotClear(int $plotId): bool
    {
        $tid = TenantContext::getId();
        $stmt = $this->db->fetchOne("SELECT status FROM plots WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$plotId, $tid] : [$plotId]);
        return ($stmt['status'] ?? '') === 'available';
    }

    private function generateNocContent(array $booking, string $nocNumber, int $generatedBy): string
    {
        $admin = $this->db->fetchOne("SELECT name FROM users WHERE id = ?", [$generatedBy]);
        $adminName = $admin['name'] ?? 'Authorized Signatory';

        return "
NO OBJECTION CERTIFICATE

NOC Number: {$nocNumber}
Date: " . date('d-m-Y') . "

TO WHOMSOEVER IT MAY CONCERN

This is to certify that Mr./Ms. {$booking['customer_name']} (Customer ID: {$booking['customer_id']}) 
has successfully completed all payment obligations for the property described below:

Property Details:
- Plot Number: {$booking['plot_number']}
- Area: {$booking['area_sqft']} sq ft
- Colony: {$booking['colony_name']}
- Booking Number: {$booking['booking_number']}
- Agreement Value: ₹" . number_format($booking['agreement_value'], 2) . "
- Total Paid: ₹" . number_format($booking['total_paid'], 2) . "

All payments due under the booking agreement have been received in full.
All required documents have been submitted and verified.
There are no outstanding dues, penalties, or encumbrances on the property.

This NOC is issued to facilitate the registration of the property in the name of the customer.

Valid until: " . date('d-m-Y', strtotime('+90 days')) . "

Authorized Signatory
{$adminName}
APS Dream Home

---
This is a computer-generated certificate. No physical signature required.
        ";
    }
}