<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;

/**
 * Mobile API — Customer Live Registry & Handover Tracker.
 *
 * JSON parity for the web Module 1 pages (/customer/registry/{id},
 * /customer/possession-certificate/{id}). Ownership is enforced against
 * the Bearer identity ($GLOBALS['api_user_id']); registry data is read
 * from the canonical `bookings` table with a `plot_bookings` fallback,
 * mirroring Front\CustomerPassbookController.
 */
class RegistryTimelineApiController extends BaseController
{
    use TenantAwareTrait;

    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    private function apiUser(): int
    {
        return (int)($GLOBALS['api_user_id'] ?? 0);
    }

    private function pdo(): \PDO
    {
        return \App\Core\Database\Database::getInstance()->getConnection();
    }

    /**
     * GET /api/v2/mobile/registry/timeline/{bookingId}
     */
    public function timeline($bookingId)
    {
        try {
            $userId = $this->apiUser();
            if ($userId <= 0) {
                return $this->jsonError('Unauthorized', 401);
            }
            $resolved = $this->resolve((int)$bookingId, $userId);
            if (!$resolved) {
                return $this->jsonError('Booking not found', 404);
            }
            $stages = $this->buildStages($resolved['booking']);
            $completed = 0;
            foreach ($stages as $s) {
                if (($s['status'] ?? '') === 'completed') $completed++;
            }
            $total = count($stages) > 0 ? count($stages) : 1;
            $this->jsonResponse([
                'success' => true,
                'data' => [
                    'booking' => $resolved['booking'],
                    'source' => $resolved['source'],
                    'canonical_id' => $resolved['canonical_id'],
                    'stages' => $stages,
                    'completed_count' => $completed,
                    'progress_pct' => round(($completed / $total) * 100, 1),
                    'can_download_certificate' => (bool)$resolved['can_download'],
                    'certificate_url' => $resolved['can_download']
                        ? (defined('BASE_URL') ? BASE_URL : '') . '/api/v2/mobile/registry/possession-certificate/' . (int)$resolved['canonical_id']
                        : null,
                ],
            ]);
        } catch (\Throwable $e) {
            error_log('RegistryTimelineApiController::timeline: ' . $e->getMessage());
            return $this->jsonError('Server error', 500);
        }
    }

    /**
     * GET /api/v2/mobile/registry/possession-certificate/{bookingId}
     * Streams the branded possession PDF (gated like the web route).
     */
    public function certificate($bookingId)
    {
        try {
            $userId = $this->apiUser();
            if ($userId <= 0) {
                return $this->jsonError('Unauthorized', 401);
            }
            $resolved = $this->resolve((int)$bookingId, $userId);
            if (!$resolved || ($resolved['source'] ?? '') !== 'bookings') {
                return $this->jsonError('Certificate not available for this booking', 404);
            }
            if (empty($resolved['can_download'])) {
                return $this->jsonError('Certificate unlocks after physical handover (Stage 7)', 403);
            }
            $pdfService = new \App\Services\Pdf\PdfService();
            $result = $pdfService->generate(\App\Services\Pdf\PdfService::TYPE_POSSESSION, (int)$resolved['canonical_id']);
            if (empty($result['success']) || empty($result['data']['path']) || !file_exists($result['data']['path'])) {
                return $this->jsonError('Could not generate the certificate', 500);
            }
            while (ob_get_level() > 0) {
                @ob_end_clean();
            }
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="Possession-Certificate-' . (int)$resolved['canonical_id'] . '.pdf"');
            header('Content-Length: ' . filesize($result['data']['path']));
            readfile($result['data']['path']);
            exit;
        } catch (\Throwable $e) {
            error_log('RegistryTimelineApiController::certificate: ' . $e->getMessage());
            return $this->jsonError('Server error', 500);
        }
    }

    /**
     * Same resolution rules as the web controller: canonical `bookings` row
     * first (customer_id OR user_id), then plot_bookings → bookings mapping.
     */
    private function resolve(int $bookingId, int $customerId): ?array
    {
        if ($bookingId <= 0) return null;
        $pdo = $this->pdo();
        $tid = (int)$this->tenantId();
        $tSql = $tid > 1 ? ' AND b.tenant_id = ?' : '';
        $tParams = $tid > 1 ? [$tid] : [];
        $booking = null;
        $source = 'bookings';

        try {
            $stmt = $pdo->prepare(
                "SELECT b.*, p.plot_number, p.block, p.area_sqft, p.width_ft, p.length_ft,
                        p.facing, c.name AS colony_name, c.location AS colony_location
                 FROM bookings b
                 LEFT JOIN plots p ON p.id = b.plot_id
                 LEFT JOIN colonies c ON c.id = COALESCE(b.colony_id, p.colony_id)
                 WHERE b.id = ? AND (b.customer_id = ? OR b.user_id = ?){$tSql} LIMIT 1"
            );
            $stmt->execute(array_merge([$bookingId, $customerId, $customerId], $tParams));
            $booking = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            error_log('RegistryTimelineApiController::resolve bookings: ' . $e->getMessage());
        }

        if (!$booking) {
            try {
                $pbT = $tid > 1 ? ' AND pb.tenant_id = ?' : '';
                $stmt = $pdo->prepare(
                    "SELECT pb.*, p.plot_number, p.block, p.area_sqft, p.width_ft, p.length_ft,
                            p.facing, c.name AS colony_name, c.location AS colony_location
                     FROM plot_bookings pb
                     LEFT JOIN plots p ON p.id = pb.plot_id
                     LEFT JOIN colonies c ON c.id = COALESCE(pb.colony_id, p.colony_id)
                     WHERE pb.id = ? AND pb.customer_id = ?{$pbT} LIMIT 1"
                );
                $stmt->execute(array_merge([$bookingId, $customerId], $tParams));
                $plotBooking = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
            } catch (\Throwable $e) {
                error_log('RegistryTimelineApiController::resolve plot_bookings: ' . $e->getMessage());
                $plotBooking = null;
            }
            if (empty($plotBooking)) return null;
            try {
                $stmt = $pdo->prepare(
                    "SELECT b.*, p.plot_number, p.block, p.area_sqft, p.width_ft, p.length_ft,
                            p.facing, c.name AS colony_name, c.location AS colony_location
                     FROM bookings b
                     LEFT JOIN plots p ON p.id = b.plot_id
                     LEFT JOIN colonies c ON c.id = COALESCE(b.colony_id, p.colony_id)
                     WHERE b.plot_id = ? AND (b.customer_id = ? OR b.user_id = ?)
                       AND b.status NOT IN ('cancelled'){$tSql}
                     ORDER BY b.id DESC LIMIT 1"
                );
                $stmt->execute(array_merge([(int)$plotBooking['plot_id'], $customerId, $customerId], $tParams));
                $booking = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
            } catch (\Throwable $e) {
                error_log('RegistryTimelineApiController::resolve map: ' . $e->getMessage());
            }
            if (!$booking) {
                $booking = $plotBooking;
                $source = 'plot_bookings';
            }
        }
        if (!$booking) return null;

        $extras = ['agreement' => null, 'noc' => null, 'deed' => null, 'possession' => null];
        if ($source === 'bookings') {
            $bid = (int)$booking['id'];
            foreach ([
                'agreement' => "SELECT * FROM booking_agreements WHERE booking_id = ? ORDER BY id DESC LIMIT 1",
                'noc' => "SELECT * FROM noc_requests WHERE booking_id = ? ORDER BY id DESC LIMIT 1",
                'deed' => "SELECT * FROM booking_documents WHERE booking_id = ? AND document_type = 'registry_deed' ORDER BY id DESC LIMIT 1",
                'possession' => "SELECT * FROM possession_records WHERE booking_id = ? ORDER BY id DESC LIMIT 1",
            ] as $key => $sql) {
                try {
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$bid]);
                    $extras[$key] = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
                } catch (\Throwable $e) {
                    error_log('RegistryTimelineApiController::resolve extra ' . $key . ': ' . $e->getMessage());
                }
            }
        }

        $canDownload = $source === 'bookings' && (
            ($booking['possession_status'] ?? '') === 'handed_over'
            || ($booking['registry_status'] ?? '') === 'completed'
            || ((($extras['possession'] ?? [])['status'] ?? '') === 'completed')
        );

        return [
            'source' => $source,
            'booking' => array_merge($booking, $extras),
            'canonical_id' => $source === 'bookings' ? (int)$booking['id'] : $bookingId,
            'deed' => $extras['deed'],
            'can_download' => $canDownload,
        ];
    }

    /**
     * Compact 7-stage builder — same signals + sequential normalization
     * as Front\CustomerPassbookController::buildRegistryStages().
     */
    private function buildStages(array $b): array
    {
        $stages = [];
        $stages[] = ['key' => 'allotment_done', 'label' => 'Token & Allotment',
            'status' => !in_array(($b['status'] ?? ''), ['pending', 'cancelled'], true) ? 'completed' : 'in_progress'];
        $ag = $b['agreement'] ?? null;
        $agSt = $ag['status'] ?? '';
        $stages[] = ['key' => 'agreement_signed', 'label' => 'Agreement for Sale',
            'status' => ($agSt === 'signed' || ($b['esign_status'] ?? '') === 'signed') ? 'completed' : (!empty($ag) ? 'in_progress' : 'pending')];
        $nocSt = ($b['noc'] ?? [])['status'] ?? '';
        $stages[] = ['key' => 'noc_cleared', 'label' => 'NOC Verification',
            'status' => $nocSt === 'approved' ? 'completed' : ($nocSt !== '' ? 'in_progress' : 'pending')];
        $regStatus = $b['registry_status'] ?? '';
        $stages[] = ['key' => 'stamp_duty_paid', 'label' => 'Stamp Duty & Valuation',
            'status' => ((float)($b['stamp_duty_amount'] ?? 0) > 0
                || in_array($regStatus, ['appointment_scheduled', 'registered', 'mutation_pending', 'completed'], true))
                ? 'completed' : ($regStatus === 'stamp_duty_pending' ? 'in_progress' : 'pending')];
        $appt = $b['appointment_date'] ?? '';
        $stages[] = ['key' => 'appointment_scheduled', 'label' => 'Sub-Registrar Appointment',
            'status' => (!empty($appt) ? 'completed' : ($regStatus === 'appointment_scheduled' ? 'in_progress' : 'pending')),
            'appointment_date' => $appt ?: null, 'venue' => $b['sub_registrar_office'] ?? null];
        $regNo = $b['registry_number'] ?? '';
        $stages[] = ['key' => 'registered', 'label' => 'Registry Executed',
            'status' => (!empty($regNo) ? 'completed' : 'pending'),
            'registry_number' => $regNo ?: null, 'registry_date' => $b['registry_date'] ?? null];
        $pos = $b['possession'] ?? [];
        $stages[] = ['key' => 'completed', 'label' => 'Mutation & Possession',
            'status' => ((($b['possession_status'] ?? '') === 'handed_over' || $regStatus === 'completed' || ($pos['status'] ?? '') === 'completed')
                ? 'completed' : (in_array(($b['possession_status'] ?? ''), ['ready', 'scheduled'], true) ? 'in_progress' : 'pending')),
            'possession_date' => $b['possession_date'] ?? null, 'mutation_number' => $b['mutation_number'] ?? null];

        $maxCompleted = -1;
        foreach ($stages as $i => $s) {
            if (($s['status'] ?? '') === 'completed') $maxCompleted = $i;
        }
        for ($i = 0; $i <= $maxCompleted; $i++) {
            $stages[$i]['status'] = 'completed';
        }
        $found = false;
        foreach ($stages as $i => $s) {
            if (($s['status'] ?? '') !== 'completed' && !$found) {
                $stages[$i]['status'] = 'in_progress';
                $found = true;
            } elseif (($s['status'] ?? '') === 'in_progress' && $found) {
                $stages[$i]['status'] = 'pending';
            }
        }
        return $stages;
    }
}
