<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;
use App\Services\Pdf\PdfService;

/**
 * Mobile API - Legal Kit Download
 *
 * Downloads legal kit ZIP (Allotment Letter + Receipt + Passbook) for a booking.
 * Supports both admin bookings (bookings table) and sales bookings (plot_bookings table).
 */
class LegalKitApiController extends BaseController
{
    use TenantAwareTrait;

    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    /**
     * GET /api/v2/mobile/legal-kit/{bookingId}?type=admin|sales
     *
     * Downloads legal kit ZIP for a booking.
     * type=admin -> uses bookings table (BookingController::legalKit logic)
     * type=sales -> uses plot_bookings table (BookingLifecycleController::legalKit logic)
     * Default: auto-detect based on which table has the booking
     */
    public function download($bookingId, $type = 'auto')
    {
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                return $this->jsonError('Unauthorized', 401);
            }

            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $params = $tid > 1 ? [$userId, $tid] : [$userId];

            // Verify user has access to this booking
            $booking = null;
            $table = '';

            if ($type === 'admin' || $type === 'auto') {
                $stmt = $this->db->prepare("SELECT * FROM bookings WHERE id = ?{$tidSql}");
                $stmt->execute($tid > 1 ? [$bookingId, $tid] : [$bookingId]);
                $booking = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($booking) $table = 'bookings';
            }

            if (!$booking && ($type === 'sales' || $type === 'auto')) {
                $stmt = $this->db->prepare("SELECT * FROM plot_bookings WHERE id = ?{$tidSql}");
                $stmt->execute($tid > 1 ? [$bookingId, $tid] : [$bookingId]);
                $booking = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($booking) $table = 'plot_bookings';
            }

            if (!$booking) {
                return $this->jsonError('Booking not found', 404);
            }

            // For customer/associate, verify ownership
            if (!in_array($GLOBALS['api_user_role'] ?? '', ['admin', 'super_admin', 'manager'], true)) {
                $ownerId = $booking['customer_id'] ?? $booking['user_id'] ?? 0;
                if ($ownerId && $ownerId != $userId) {
                    // Check if associate owns this booking
                    $associateId = $booking['associate_id'] ?? 0;
                    if ($associateId != $userId) {
                        return $this->jsonError('Forbidden', 403);
                    }
                }
            }

            // Generate legal kit using PdfService (same as web controllers)
            $pdfService = new PdfService();
            $tempDir = sys_get_temp_dir() . '/legal_kit_' . $bookingId . '_' . time();
            @mkdir($tempDir, 0755, true);

            $files = [];

            try {
                // 1. Allotment Letter / Agreement
                $allotmentResult = $pdfService->generate(PdfService::TYPE_AGREEMENT, (int)$bookingId);
                if (!empty($allotmentResult['success']) && !empty($allotmentResult['data']['path']) && is_file($allotmentResult['data']['path'])) {
                    $allotmentPath = $tempDir . '/1-Allotment-Letter.pdf';
                    copy($allotmentResult['data']['path'], $allotmentPath);
                    $files[] = $allotmentPath;
                }

                // 2. Payment Receipt
                $receiptResult = $pdfService->generate(PdfService::TYPE_RECEIPT, (int)$bookingId);
                if (!empty($receiptResult['success']) && !empty($receiptResult['data']['path']) && is_file($receiptResult['data']['path'])) {
                    $receiptPath = $tempDir . '/2-Payment-Receipt.pdf';
                    copy($receiptResult['data']['path'], $receiptPath);
                    $files[] = $receiptPath;
                }

                // 3. Passbook / EMI Schedule
                $possessionResult = $pdfService->generate(PdfService::TYPE_POSSESSION, (int)$bookingId);
                if (!empty($possessionResult['success']) && !empty($possessionResult['data']['path']) && is_file($possessionResult['data']['path'])) {
                    $passbookPath = $tempDir . '/3-Passbook-Schedule.pdf';
                    copy($possessionResult['data']['path'], $passbookPath);
                    $files[] = $passbookPath;
                }

                if (empty($files)) {
                    return $this->jsonError('Could not generate any legal documents for this booking', 500);
                }

                // Create ZIP
                $zipName = 'legal-kit-booking-' . ($booking['booking_number'] ?? $bookingId) . '-' . date('Ymd') . '.zip';
                $zipPath = $tempDir . '/' . $zipName;
                $zip = new \ZipArchive();
                if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                    return $this->jsonError('Could not create legal kit archive', 500);
                }
                foreach ($files as $file) {
                    $zip->addFile($file, basename($file));
                }
                $zip->close();

                // Stream the ZIP file
                while (ob_get_level() > 0) {
                    @ob_end_clean();
                }
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . $zipName . '"');
                header('Content-Length: ' . filesize($zipPath));
                header('Cache-Control: no-cache, must-revalidate');
                readfile($zipPath);

                // Cleanup
                @unlink($zipPath);
                foreach ($files as $file) @unlink($file);
                @rmdir($tempDir);

                exit;

            } catch (\Throwable $e) {
                error_log('LegalKitApiController::download: ' . $e->getMessage());
                return $this->jsonError('Failed to generate legal kit: ' . $e->getMessage(), 500);
            }

        } catch (\Throwable $e) {
            error_log('LegalKitApiController::download: ' . $e->getMessage());
            return $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }
}