<?php

namespace App\Services\Esign;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;

/**
 * E-Sign Manager — Facade for initiating and tracking document signing.
 *
 * Delegates to LeegalityService (extensible for other providers).
 * All booking e-sign state is tracked in the plot_bookings table.
 */
class ESignManager
{
    /** @var LeegalityService */
    private $provider;

    /** @var \PDO|null */
    private $pdo;

    public function __construct(?LeegalityService $provider = null)
    {
        $this->provider = $provider ?? new LeegalityService();
        $this->pdo = $this->resolvePdo();
    }

    /**
     * Initiate e-sign for a booking.
     *
     * @param int    $bookingId     plot_bookings.id
     * @param string $agreementPath URL or path to the agreement PDF
     * @return array  {success, document_id, signing_url, error}
     */
    public function initiateEsign(int $bookingId, string $agreementPath): array
    {
        $booking = $this->fetchBooking($bookingId);
        if (!$booking) {
            return ['success' => false, 'error' => 'Booking not found.'];
        }

        // Already signed or sent
        if (in_array($booking['esign_status'] ?? '', ['sent', 'signed'])) {
            return ['success' => false, 'error' => 'E-sign already initiated or completed for this booking.'];
        }

        $user = $this->fetchUser($booking['customer_id'] ?? 0);
        $plot = $this->fetchPlot($booking['plot_id'] ?? 0);

        $result = $this->provider->createDocument([
            'title'        => 'Booking Agreement - Plot ' . ($plot['plot_number'] ?? '') . ' - ' . ($booking['booking_number'] ?? ''),
            'signers'      => [
                [
                    'name'  => $user['name'] ?? $booking['customer_name'] ?? 'Customer',
                    'email' => $user['email'] ?? '',
                    'phone' => $user['phone'] ?? '',
                ],
            ],
            'file_url'     => $agreementPath,
            'callback_url' => (defined('BASE_URL') ? BASE_URL : '') . '/webhook/esign',
        ]);

        if (!$result['success']) {
            $this->updateEsignStatus($bookingId, 'failed');
            return $result;
        }

        $this->updateEsignStatus($bookingId, 'sent', [
            'esign_document_id' => $result['document_id'],
            'esign_url'         => $result['signing_url'] ?? '',
        ]);

        return [
            'success'      => true,
            'document_id'  => $result['document_id'],
            'signing_url'  => $result['signing_url'] ?? '',
            'error'        => null,
        ];
    }

    /**
     * Handle webhook callback from Leegality.
     *
     * @param string $documentId
     * @param string $status     e.g. 'signed', 'failed', 'expired'
     * @return array  {success, booking_id, error}
     */
    public function callback(string $documentId, string $status): array
    {
        $booking = $this->fetchBookingByDocumentId($documentId);
        if (!$booking) {
            return ['success' => false, 'error' => 'No booking found for document ID.'];
        }

        $mapped = match ($status) {
            'signed'   => 'signed',
            'failed', 'expired', 'rejected' => 'failed',
            default    => 'sent',
        };

        $updateData = ['esign_status' => $mapped];
        if ($mapped === 'signed') {
            $updateData['esign_signed_at'] = date('Y-m-d H:i:s');
        }

        $this->updateEsignStatus($booking['id'], $mapped, $updateData);

        return [
            'success'    => true,
            'booking_id' => $booking['id'],
            'status'     => $mapped,
            'error'      => null,
        ];
    }

    /**
     * Get the e-sign status for a booking.
     *
     * @param int $bookingId
     * @return array  {success, status, document_id, signed_at, signing_url, error}
     */
    public function getStatus(int $bookingId): array
    {
        $booking = $this->fetchBooking($bookingId);
        if (!$booking) {
            return ['success' => false, 'error' => 'Booking not found.'];
        }

        // If document was sent, check live status from provider
        if (($booking['esign_status'] ?? '') === 'sent' && !empty($booking['esign_document_id'])) {
            $live = $this->provider->getStatus($booking['esign_document_id']);
            if ($live['success'] && ($live['status'] ?? '') === 'signed') {
                $this->updateEsignStatus($bookingId, 'signed', [
                    'esign_signed_at' => $live['signed_at'] ?? date('Y-m-d H:i:s'),
                ]);
                $booking['esign_status'] = 'signed';
                $booking['esign_signed_at'] = $live['signed_at'] ?? date('Y-m-d H:i:s');
            }
        }

        return [
            'success'      => true,
            'status'       => $booking['esign_status'] ?? 'pending',
            'document_id'  => $booking['esign_document_id'] ?? null,
            'signed_at'    => $booking['esign_signed_at'] ?? null,
            'signing_url'  => $booking['esign_url'] ?? null,
            'error'        => null,
        ];
    }

    /**
     * Check if the provider is configured.
     */
    public function isConfigured(): bool
    {
        return $this->provider->isConfigured();
    }

    /* ------------------------------------------------------------------ */
    /*  Private helpers                                                    */
    /* ------------------------------------------------------------------ */

    private function fetchBooking(int $id): ?array
    {
        if (!$this->pdo || $id <= 0) {
            return null;
        }
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM plot_bookings WHERE id = ?');
            $stmt->execute([$id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function fetchBookingByDocumentId(string $documentId): ?array
    {
        if (!$this->pdo || empty($documentId)) {
            return null;
        }
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM plot_bookings WHERE esign_document_id = ?');
            $stmt->execute([$documentId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function fetchUser(int $userId): ?array
    {
        if (!$this->pdo || $userId <= 0) {
            return null;
        }
        try {
            $tid = $this->getTenantId();
            $sql = 'SELECT * FROM users WHERE id = ?';
            $params = [$userId];
            if ($tid > 1) {
                $sql .= ' AND tenant_id = ?';
                $params[] = $tid;
            }
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function fetchPlot(int $plotId): ?array
    {
        if (!$this->pdo || $plotId <= 0) {
            return null;
        }
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM plots WHERE id = ?');
            $stmt->execute([$plotId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function updateEsignStatus(int $bookingId, string $status, array $extra = []): void
    {
        if (!$this->pdo) {
            return;
        }
        try {
            $sets   = ['esign_status = ?'];
            $params = [$status];

            if (isset($extra['esign_document_id'])) {
                $sets[]   = 'esign_document_id = ?';
                $params[] = $extra['esign_document_id'];
            }
            if (isset($extra['esign_url'])) {
                $sets[]   = 'esign_url = ?';
                $params[] = $extra['esign_url'];
            }
            if (isset($extra['esign_signed_at'])) {
                $sets[]   = 'esign_signed_at = ?';
                $params[] = $extra['esign_signed_at'];
            }

            $params[] = $bookingId;
            $sql = 'UPDATE plot_bookings SET ' . implode(', ', $sets) . ' WHERE id = ?';
            $this->pdo->prepare($sql)->execute($params);
        } catch (\Throwable $e) {
            error_log('[ESignManager::updateEsignStatus] ' . $e->getMessage());
        }
    }

    private function getTenantId(): int
    {
        try {
            return TenantContext::getId();
        } catch (\Throwable $e) {
            return 1;
        }
    }

    private function resolvePdo(): ?\PDO
    {
        try {
            if (class_exists(Database::class)) {
                $db = Database::getInstance();
                return method_exists($db, 'getConnection') ? $db->getConnection() : null;
            }
        } catch (\Throwable $e) {
        // fallback
        error_log($e->getMessage());
        }
        return null;
    }
}
