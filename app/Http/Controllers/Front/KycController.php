<?php
namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Services\KYCService;

class KycController extends BaseController
{
    public function index()
    {
        $this->requireLogin();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) {
            $this->redirect('/login');
            return;
        }

        $service = new KYCService();
        $user = $this->fetchUser($userId);
        $existing = $this->fetchKycRequest($userId);
        $history = $this->fetchKycHistory($userId);

        $data = [
            'page_title' => 'KYC Verification - APS Dream Home',
            'page_description' => 'Complete your KYC to unlock all features',
            'current_page' => 'kyc',
            'user' => $user,
            'existing' => $existing,
            'history' => $history,
            'aadhaarMasked' => $existing ? $this->maskAadhaar($existing['aadhaar_number'] ?? '') : null,
            'panMasked' => $existing ? $this->maskPan($existing['pan_number'] ?? '') : null,
        ];
        $this->render('pages/user/kyc', $data);
    }

    public function submit()
    {
        $this->requireLogin();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) {
            $this->json(['success' => false, 'error' => 'Not authenticated'], 401);
            return;
        }
        // CSRF handled by BaseController constructor

        $pan = strtoupper(trim($_POST['pan_number'] ?? ''));
        $aadhaar = preg_replace('/\D/', '', $_POST['aadhaar_number'] ?? '');
        $name = trim($_POST['legal_name'] ?? '');
        $dob = $_POST['dob'] ?? null;

        // Handle file uploads with UploadValidator
        $uploadDir = __DIR__ . '/../../../assets/uploads/kyc/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $documents = [];

        foreach (['pan_document', 'aadhaar_front_document', 'aadhaar_back_document'] as $field) {
            if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
                $this->json(['success' => false, 'error' => ucfirst(str_replace('_', ' ', $field)) . ' is required'], 400);
                return;
            }
            $v = \UploadValidator::validate($_FILES[$field], ['types' => 'documents', 'max_size' => 5]);
            if (!$v['valid']) {
                $this->json(['success' => false, 'error' => ucfirst(str_replace('_', ' ', $field)) . ': ' . $v['error']], 400);
                return;
            }
            $safeName = \UploadValidator::safeFilename($_FILES[$field]['name']);
            $ext = pathinfo($safeName, PATHINFO_EXTENSION);
            $filename = $field . '_' . $userId . '_' . time() . '.' . $ext;
            $destPath = $uploadDir . $filename;
            if (!move_uploaded_file($_FILES[$field]['tmp_name'], $destPath)) {
                $this->json(['success' => false, 'error' => 'Failed to upload ' . ucfirst(str_replace('_', ' ', $field))], 500);
                return;
            }
            $documents[$field] = 'assets/uploads/kyc/' . $filename;
        }

        $service = new KYCService();
        $panResult = $service->verifyPAN($pan, $name);
        $aadhaarResult = $service->verifyAadhaar($aadhaar);

        $status = 'pending';
        $reason = null;
        if (empty($panResult['success'])) { $status = 'rejected'; $reason = $panResult['message'] ?? 'PAN verification failed'; }
        elseif (empty($aadhaarResult['success'])) { $status = 'rejected'; $reason = $aadhaarResult['message'] ?? 'Aadhaar verification failed'; }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO kyc_requests (user_id, pan_number, aadhaar_number, legal_name, dob, status, reason, 
                    pan_document, aadhaar_front_document, aadhaar_back_document, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ON DUPLICATE KEY UPDATE 
                    pan_number=VALUES(pan_number), aadhaar_number=VALUES(aadhaar_number), 
                    legal_name=VALUES(legal_name), dob=VALUES(dob), status=VALUES(status), 
                    reason=VALUES(reason), pan_document=VALUES(pan_document), 
                    aadhaar_front_document=VALUES(aadhaar_front_document), 
                    aadhaar_back_document=VALUES(aadhaar_back_document), updated_at=NOW()
            ");
            $stmt->execute([
                $userId, $pan, $aadhaar, $name, $dob, $status, $reason,
                $documents['pan_document'],
                $documents['aadhaar_front_document'],
                $documents['aadhaar_back_document']
            ]);
            $this->json(['success' => true, 'status' => $status, 'message' => $status === 'pending' ? 'KYC submitted with documents. Under review.' : $reason]);
        } catch (\Throwable $e) {
            $this->json(['success' => false, 'error' => 'Could not save KYC: ' . $e->getMessage()], 500);
        }
    }

    private function fetchUser(int $userId): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT id, name, email, phone, role, created_at FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) { return null; }
    }

    public function status()
    {
        $this->requireLogin();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $kyc = $this->fetchKycRequest($userId);
        $this->json([
            'success' => true,
            'status' => $kyc['status'] ?? 'not_started',
            'kyc' => $kyc ? [
                'id' => $kyc['id'],
                'status' => $kyc['status'],
                'legal_name' => $kyc['legal_name'] ?? '',
                'submitted_at' => $kyc['created_at'] ?? null,
                'verified_at' => $kyc['verified_at'] ?? null,
                'rejection_reason' => $kyc['rejection_reason'] ?? null,
            ] : null
        ]);
    }

    private function fetchKycRequest(int $userId): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM kyc_requests WHERE user_id = ? ORDER BY id DESC LIMIT 1");
            $stmt->execute([$userId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\Throwable $e) { return null; }
    }

    private function fetchKycHistory(int $userId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT id, pan_number, status, reason, created_at, updated_at FROM kyc_requests WHERE user_id = ? ORDER BY id DESC LIMIT 5");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) { return []; }
    }

    private function maskAadhaar(string $a): string
    {
        if (strlen($a) < 12) return $a;
        return 'XXXX XXXX ' . substr($a, -4);
    }
    private function maskPan(string $p): string
    {
        if (strlen($p) < 10) return $p;
        return substr($p, 0, 5) . 'XXX' . substr($p, -1);
    }
}
