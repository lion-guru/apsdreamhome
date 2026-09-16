<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Core\Security;
use PDO;
use App\Traits\TenantAwareTrait;

class MobileSyncApiController extends BaseController
{
    use TenantAwareTrait;
    protected $syncService;

    public function __construct()
    {
        parent::__construct();
        $this->syncService = new \App\Services\SyncService();
    }

    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    public function syncProperties()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: $_GET;
            $lastSync = \App\Core\Security::sanitize($input['last_sync'] ?? $input['lastUpdated'] ?? date('Y-m-d H:i:s', strtotime('-1 hour')));

            $stmt = $this->db->prepare("
                SELECT p.id, p.title, p.price, p.bedrooms, p.bathrooms, p.area,
                       p.description, p.status, p.created_at, p.updated_at,
                       (SELECT COUNT(*) FROM property_images WHERE property_id = p.id) as image_count
                FROM properties p
                WHERE p.updated_at >= ? OR p.created_at >= ?
                ORDER BY p.updated_at DESC
                LIMIT 100
            ");
            $stmt->execute([$lastSync, $lastSync]);
            $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'data' => $properties,
                'last_sync' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Sync Properties API error');
        }
    }

    public function batchSyncLeads()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $leads = $input['leads'] ?? [];
            if (empty($leads)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'No leads provided']);
                return;
            }

            $this->db->beginTransaction();
            $processed = 0;
            foreach ($leads as $lead) {
                $stmt = $this->db->prepare("
                    INSERT INTO leads (name, email, phone, source, assigned_to, created_by, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, 'new', NOW())
                    ON DUPLICATE KEY UPDATE
                    name = VALUES(name),
                    email = VALUES(email),
                    phone = VALUES(phone),
                    status = VALUES(status)
                ");
                $stmt->execute([
                    \App\Core\Security::sanitize($lead['name'] ?? ''),
                    filter_var($lead['email'] ?? '', FILTER_SANITIZE_EMAIL),
                    preg_replace('/[^0-9+]/', '', $lead['phone'] ?? ''),
                    \App\Core\Security::sanitize($lead['source'] ?? 'mobile'),
                    (int)($lead['assigned_to'] ?? $userId),
                    $userId
                ]);
                $processed++;
            }
            $this->db->commit();
            echo json_encode([
                'success' => true,
                'message' => 'Leads synced successfully',
                'processed' => $processed
            ]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->handleApiError($e, 'Batch Sync Leads API error');
        }
    }

    public function getUpdates()
    {
        $this->setCorsHeaders();
        
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        $lastSync = \App\Core\Security::sanitize($_GET['last_sync'] ?? null) ?? '2000-01-01 00:00:00';

        try {
            $syncPackage = $this->syncService->getSyncPackage($lastSync, $userId);
            echo json_encode([
                'success' => true,
                'data' => $syncPackage
            ]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Sync Updates API error');
        }
    }

    public function sync()
    {
        $this->setCorsHeaders();
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            
            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Authentication required']);
                return;
            }

            // Process uploads from mobile app
            $uploadResults = $this->processSyncUploads($userId, $input['uploads'] ?? []);
            
            // Get latest data for download
            $lastSync = $input['last_sync'] ?? date('Y-m-d H:i:s');
            $downloadPackage = $this->syncService->getSyncPackage($lastSync, $userId);

            echo json_encode([
                'success' => true,
                'upload' => $uploadResults,
                'download' => $downloadPackage
            ]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Sync API error');
        }
    }

    private function processSyncUploads($userId, $uploads)
    {
        $results = [];
        foreach ($uploads as $upload) {
            $type = $upload['type'] ?? '';
            $data = $upload['data'] ?? [];
            try {
                if ($type === 'lead') {
                    $res = $this->createLead($data);
                    $results[] = ['type' => 'lead', 'id' => $res, 'status' => 'success'];
                } elseif ($type === 'interaction') {
                    $res = $this->addInteraction($data);
                    $results[] = ['type' => 'interaction', 'id' => $res, 'status' => 'success'];
                } elseif ($type === 'booking') {
                    $res = $this->createOfflineBooking($userId, $data);
                    $results[] = ['type' => 'booking', 'id' => $res, 'status' => 'success'];
                } else {
                    $results[] = ['type' => $type, 'status' => 'error', 'message' => 'Unknown upload type'];
                }
            } catch (\Exception $e) {
                error_log("MobileSyncApiController::processUploads type={$type} error: " . $e->getMessage());
                $results[] = ['type' => $type, 'status' => 'error', 'message' => 'Internal server error'];
            }
        }
        return $results;
    }

    private function createOfflineBooking(int $userId, array $data): int
    {
        $tid = (int)$this->tenantId();
        $plotId = (int)($data['plot_id'] ?? 0);
        if ($plotId <= 0) {
            throw new \InvalidArgumentException('plot_id is required');
        }

        // NOTE: Flutter offline_booking_page.dart sends client_* keys + token_amount
        $customerName = \App\Core\Security::sanitize($data['client_name'] ?? $data['customer_name'] ?? '');
        $customerPhone = preg_replace('/[^0-9+]/', '', $data['client_phone'] ?? $data['customer_phone'] ?? '');
        $customerEmail = filter_var($data['client_email'] ?? $data['customer_email'] ?? '', FILTER_SANITIZE_EMAIL);
        $tokenAmount = (float)($data['token_amount'] ?? $data['booking_amount'] ?? 0);
        $notes = \App\Core\Security::sanitize($data['notes'] ?? 'Offline booking synced from mobile');
        $bookDate = substr((string)($data['booking_date'] ?? ''), 0, 10);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $bookDate)) {
            $bookDate = date('Y-m-d');
        }
        if ($customerName === '' && $customerPhone === '') {
            throw new \InvalidArgumentException('client_name or client_phone is required');
        }
        if ($customerEmail === '' && $customerPhone !== '') {
            $customerEmail = $customerPhone . '@offline.local';
        }

        // plot must exist; default colony from plot
        $pchk = $this->db->prepare("SELECT id, colony_id FROM plots WHERE id = ? LIMIT 1");
        $pchk->execute([$plotId]);
        $plot = $pchk->fetch(\PDO::FETCH_ASSOC);
        if (!$plot) {
            throw new \InvalidArgumentException('Plot not found');
        }
        $colonyId = (int)($data['colony_id'] ?? 0);
        if ($colonyId <= 0) {
            $colonyId = (int)($plot['colony_id'] ?? 0);
        }

        // idempotent retry: same plot + phone + pending => return existing
        $dup = $this->db->prepare("SELECT id FROM plot_bookings WHERE plot_id = ? AND customer_phone = ? AND status = 'pending' LIMIT 1");
        $dup->execute([$plotId, $customerPhone]);
        $existing = $dup->fetch(\PDO::FETCH_ASSOC);
        if ($existing) {
            return (int)$existing['id'];
        }

        // find-or-create customer user by phone (customer_id is NOT NULL)
        $customerId = 0;
        if ($customerPhone !== '') {
            $uchk = $this->db->prepare("SELECT id FROM users WHERE phone = ? LIMIT 1");
            $uchk->execute([$customerPhone]);
            $u = $uchk->fetch(\PDO::FETCH_ASSOC);
            if ($u) {
                $customerId = (int)$u['id'];
            } else {
                $uTidCol = $tid > 1 ? ', tenant_id' : '';
                $uTidVal = $tid > 1 ? ', ' . $tid : '';
                $this->db->prepare("INSERT INTO users (name, email, phone, role, referred_by{$uTidCol}) VALUES (?, ?, ?, 'customer', ?{$uTidVal})")
                    ->execute([$customerName !== '' ? $customerName : $customerPhone, $customerEmail, $customerPhone, $userId]);
                $customerId = (int)$this->db->lastInsertId();
            }
        }

        $bookingNumber = 'OFL-' . strtoupper(bin2hex(random_bytes(4)));

        $tidCol = $tid > 1 ? ', tenant_id' : '';
        $tidVal = $tid > 1 ? ', ?' : '';
        $params = [$plotId, $colonyId > 0 ? $colonyId : null, $customerId,
                    $customerName, $customerEmail, $customerPhone,
                    $bookingNumber, $bookDate, $tokenAmount,
                    'pending', 'mobile_app', $userId, $userId, $notes];
        if ($tid > 1) $params[] = $tid;

        $stmt = $this->db->prepare("
            INSERT INTO plot_bookings (plot_id, colony_id, customer_id, customer_name, customer_email, customer_phone,
                booking_number, booking_date, booking_amount, status, channel,
                associate_id, created_by, notes{$tidCol})
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?{$tidVal})
        ");
        $stmt->execute($params);

        $bookingId = (int)$this->db->lastInsertId();
        if ($bookingId > 0) {
            $this->db->prepare("UPDATE plots SET status = 'booked' WHERE id = ? AND status = 'available'")->execute([$plotId]);
        }
        return $bookingId;
    }
}
