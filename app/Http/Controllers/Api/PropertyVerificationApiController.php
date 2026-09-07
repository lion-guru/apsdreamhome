<?php

namespace App\Http\Controllers\Api;

use App\Services\PropertyVerificationService;
use App\Core\Middleware\TenantContext;

class PropertyVerificationApiController extends BaseApiController
{
    protected PropertyVerificationService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new PropertyVerificationService();
    }

    // ===== Verification Levels =====

    public function getLevels()
    {
        $this->setCorsHeaders();
        try {
            $levels = $this->service->getAllLevels();
            foreach ($levels as &$level) {
                if (!empty($level['features'])) {
                    $level['features'] = json_decode($level['features'], true) ?: [];
                }
            }
            echo json_encode(['success' => true, 'data' => $levels]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Get Levels API error');
        }
    }

    public function getLevel($id)
    {
        $this->setCorsHeaders();
        try {
            $level = $this->service->getLevelById((int)$id);
            if (!$level) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Level not found']);
                return;
            }
            if (!empty($level['features'])) {
                $level['features'] = json_decode($level['features'], true) ?: [];
            }
            echo json_encode(['success' => true, 'data' => $level]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Get Level API error');
        }
    }

    // ===== Verification Requests =====

    public function createRequest()
    {
        $this->setCorsHeaders();
        try {
            $input = $this->getJsonInput();
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            
            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Authentication required']);
                return;
            }

            $data = [
                'property_id' => (int)($input['property_id'] ?? 0),
                'property_type' => $input['property_type'] ?? 'plot',
                'verification_level_id' => (int)($input['verification_level_id'] ?? 0),
                'requested_by' => $userId,
                'status' => 'draft',
                'priority' => $input['priority'] ?? 'standard',
                'title_deed' => $input['title_deed'] ?? null,
                'sale_deed' => $input['sale_deed'] ?? null,
                'tax_receipts' => $input['tax_receipts'] ?? null,
                'encumbrance_certificate' => $input['encumbrance_certificate'] ?? null,
                'approved_building_plan' => $input['approved_building_plan'] ?? null,
                'identity_proof' => $input['identity_proof'] ?? null,
                'additional_documents' => $input['additional_documents'] ?? null,
                'priority' => $input['priority'] ?? 'standard',
            ];

            if (!$input['property_id'] || !$input['verification_level_id']) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Property ID and verification level required']);
                return;
            }

            $result = $this->service->createRequest($data);
            if ($result['success']) {
                echo json_encode(['success' => true, 'data' => ['request_id' => $result['id']]], 201);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => $result['error']]);
            }
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Create Request API error');
        }
    }

    public function getRequest($id)
    {
        $this->setCorsHeaders();
        try {
            $request = $this->service->getRequestById((int)$id);
            if (!$request) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Request not found']);
                return;
            }
            echo json_encode(['success' => true, 'data' => $request]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Get Request API error');
        }
    }

    public function getMyRequests()
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            $status = $_GET['status'] ?? null;
            
            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Authentication required']);
                return;
            }

            $requests = $this->service->getRequestsByUser($userId, $status);
            echo json_encode(['success' => true, 'data' => $requests]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Get My Requests API error');
        }
    }

    public function getPropertyRequests($propertyId)
    {
        $this->setCorsHeaders();
        try {
            $requests = $this->service->getRequestsByProperty((int)$propertyId);
            echo json_encode(['success' => true, 'data' => $requests]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Get Property Requests API error');
        }
    }

    public function updateRequest($id)
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Authentication required']);
                return;
            }

            $input = $this->getJsonInput();
            $result = $this->service->updateRequest((int)$id, $input);
            
            if ($result['success']) {
                echo json_encode(['success' => true, 'message' => $result['message']]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => $result['error']]);
            }
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Update Request API error');
        }
    }

    public function submitRequest($id)
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Authentication required']);
                return;
            }

            $result = $this->service->submitRequest((int)$id, $userId);
            if ($result['success']) {
                echo json_encode(['success' => true, 'message' => 'Request submitted for review']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => $result['error']]);
            }
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Submit Request API error');
        }
    }

    public function approveRequest($id)
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Authentication required']);
                return;
            }

            $result = $this->service->approveRequest((int)$id, $userId);
            if ($result['success']) {
                echo json_encode(['success' => true, 'message' => 'Request approved']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => $result['error']]);
            }
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Approve Request API error');
        }
    }

    public function rejectRequest($id)
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            $input = $this->getJsonInput();
            $reason = $input['reason'] ?? 'No reason provided';
            
            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Authentication required']);
                return;
            }

            $result = $this->service->rejectRequest((int)$id, $userId, $reason);
            if ($result['success']) {
                echo json_encode(['success' => true, 'message' => 'Request rejected']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => $result['error']]);
            }
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Reject Request API error');
        }
    }

    // ===== Documents =====

    public function uploadDocument($requestId)
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Authentication required']);
                return;
            }

            $input = $this->getJsonInput();
            $data = [
                'request_id' => (int)$requestId,
                'document_type' => $input['document_type'] ?? 'other',
                'file_path' => $input['file_path'] ?? '',
                'file_name' => $input['file_name'] ?? '',
                'file_size' => (int)($input['file_size'] ?? 0),
                'mime_type' => $input['mime_type'] ?? null,
                'uploaded_by' => $userId,
            ];

            if (empty($data['file_path']) || empty($data['file_name'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'File path and name required']);
                return;
            }

            $result = $this->service->addDocument($data);
            if ($result['success']) {
                echo json_encode(['success' => true, 'data' => ['document_id' => $result['id']]]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => $result['error']]);
            }
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Upload Document API error');
        }
    }

    public function getDocuments($requestId)
    {
        $this->setCorsHeaders();
        try {
            $documents = $this->service->getDocuments((int)$requestId);
            echo json_encode(['success' => true, 'data' => $documents]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Get Documents API error');
        }
    }

    public function verifyDocument($docId)
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            $input = $this->getJsonInput();
            $verified = $input['verified'] ?? true;
            $notes = $input['notes'] ?? '';

            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Authentication required']);
                return;
            }

            $result = $this->service->verifyDocument((int)$docId, $userId, $verified, $input['notes'] ?? '');
            if ($result['success']) {
                echo json_encode(['success' => true, 'message' => $result['message']]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => $result['error']]);
            }
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Verify Document API error');
        }
    }

    // ===== Badges =====

    public function issueBadge($requestId)
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            $input = $this->getJsonInput();
            
            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Authentication required']);
                return;
            }

            $propertyId = (int)($input['property_id'] ?? 0);
            $levelId = (int)($input['verification_level_id'] ?? 0);
            
            if (!$propertyId || !$levelId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Property ID and verification level required']);
                return;
            }

            $result = $this->service->issueBadge((int)$requestId, $propertyId, $levelId, $userId);
            if ($result['success']) {
                echo json_encode(['success' => true, 'data' => ['badge_id' => $result['id'], 'badge_code' => $result['badge_code']]]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => $result['error']]);
            }
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Issue Badge API error');
        }
    }

    public function verifyBadge($code)
    {
        $this->setCorsHeaders();
        try {
            $badge = $this->service->getBadgeByCode($code);
            if (!$badge) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Badge not found']);
                return;
            }
            echo json_encode(['success' => true, 'data' => $badge]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Verify Badge API error');
        }
    }

    public function revokeBadge($badgeId)
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            $input = $this->getJsonInput();
            $reason = $input['reason'] ?? 'No reason provided';
            
            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Authentication required']);
                return;
            }

            $result = $this->service->revokeBadge((int)$badgeId, $userId, $reason);
            if ($result['success']) {
                echo json_encode(['success' => true, 'message' => $result['message']]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => $result['error']]);
            }
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Revoke Badge API error');
        }
    }

    // ===== Stats =====

    public function getStats()
    {
        $this->setCorsHeaders();
        try {
            $stats = $this->service->getStats();
            $levelStats = $this->service->getLevelStats();
            echo json_encode(['success' => true, 'data' => ['overview' => $stats, 'levels' => $levelStats]]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Get Stats API error');
        }
    }
}