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
                       c.name as colony_name,
                       (SELECT COUNT(*) FROM property_images WHERE property_id = p.id) as image_count
                FROM properties p
                LEFT JOIN colonies c ON p.colony_id = c.id
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
}
