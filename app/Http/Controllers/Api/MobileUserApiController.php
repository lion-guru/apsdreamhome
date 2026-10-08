<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Core\Security;
use PDO;
use App\Traits\TenantAwareTrait;

class MobileUserApiController extends BaseController
{
    use TenantAwareTrait;
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    public function getUserProfile()
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'User ID is required']);
                return;
            }
            $profile = $this->getUserProfileData($userId);
            echo json_encode(['success' => true, 'data' => $profile]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'User Profile API error');
        }
    }

    public function getDocuments()
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'User ID is required']);
                return;
            }
            $documents = $this->getUserDocuments($userId);
            echo json_encode(['success' => true, 'data' => $documents]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'User Documents API error');
        }
    }

    public function uploadDocument()
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'User ID is required']);
                return;
            }

            if (!isset($_FILES['document'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'No document file uploaded']);
                return;
            }

            $file = $_FILES['document'];
            $documentType = \App\Core\Security::sanitize($_POST['document_type'] ?? 'general');
            
            $validator = new \App\Helpers\UploadValidator();
            if (!$validator->validate($file, 25 * 1024 * 1024)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid file upload']);
                return;
            }

            $uploadDir = 'uploads/documents/' . $userId . '/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $filename = time() . '_' . basename($file['name']);
            $targetFile = $uploadDir . $filename;

            if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                $fileUrl = '/uploads/documents/' . $userId . '/' . $filename;
                try {
                    $lockerService = new \App\Services\DocumentLockerService();
                    $title = \App\Core\Security::sanitize($_POST['title'] ?? (ucfirst($documentType) . ' Document'));
                    $lockerService->addDocument($userId, $title, $documentType, $fileUrl);
                } catch (\Exception $e) {
                    error_log("Failed to record document in locker: " . $e->getMessage());
                }
                echo json_encode(['success' => true, 'message' => 'Document uploaded successfully', 'url' => $fileUrl]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to save document']);
            }
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Upload Document API error');
        }
    }

    public function getCustomerDocuments()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'User ID is required']);
            return;
        }

        try {
            $documents = [];
            $docLimit = 50;

            // 1. KYC Documents
            try {
                $kycSql = "SELECT id, CASE WHEN pan_document IS NOT NULL THEN 'PAN Document' WHEN aadhaar_front_document IS NOT NULL THEN 'Aadhaar Document' ELSE 'KYC Document' END as name, 'kyc' as type, 'kyc' as category, COALESCE(pan_document, aadhaar_front_document, aadhaar_back_document) as url, created_at as uploaded_at, status, 'kyc' as source FROM kyc_requests WHERE user_id = ? ORDER BY created_at DESC LIMIT {$docLimit}";
                $kycStmt = $this->db->prepare($kycSql);
                $kycStmt->execute([$userId]);
                $documents = array_merge($documents, $kycStmt->fetchAll(PDO::FETCH_ASSOC));
            } catch (\Throwable $e) { error_log("[getCustomerDocuments] kyc skipped: " . $e->getMessage()); }

            // 2. Booking Agreements
            try {
                $bookingSql = "SELECT b.id, CONCAT('Booking Agreement - ', p.title) as name, 'agreement' as type, 'booking' as category, ba.content as url, ba.created_at as uploaded_at, ba.status, 'booking' as source FROM bookings b JOIN properties p ON b.property_id = p.id LEFT JOIN booking_agreements ba ON b.id = ba.booking_id WHERE b.customer_id = ? ORDER BY ba.created_at DESC LIMIT {$docLimit}";
                $bookingStmt = $this->db->prepare($bookingSql);
                $bookingStmt->execute([$userId]);
                $documents = array_merge($documents, $bookingStmt->fetchAll(PDO::FETCH_ASSOC));
            } catch (\Throwable $e) { error_log("[getCustomerDocuments] booking agreements skipped: " . $e->getMessage()); }

            // 3. Payment Receipts
            try {
                $paymentSql = "SELECT pay.id, CONCAT('Payment Receipt - ', p.title) as name, 'receipt' as type, 'payment' as category, NULL as url, pay.created_at as uploaded_at, pay.status, 'payment' as source FROM payments pay JOIN bookings b ON pay.booking_id = b.id JOIN properties p ON b.property_id = p.id WHERE b.customer_id = ? ORDER BY pay.created_at DESC LIMIT {$docLimit}";
                $paymentStmt = $this->db->prepare($paymentSql);
                $paymentStmt->execute([$userId]);
                $documents = array_merge($documents, $paymentStmt->fetchAll(PDO::FETCH_ASSOC));
            } catch (\Throwable $e) { error_log("[getCustomerDocuments] payments skipped: " . $e->getMessage()); }

            // 4. Plot Allotment Letters
            try {
                $allotmentSql = "SELECT pa.id, CONCAT('Allotment Letter - ', p.title) as name, 'allotment' as type, 'booking' as category, pa.allotment_date as url, pa.created_at as uploaded_at, pa.status, 'allotment' as source FROM plot_allotments pa JOIN bookings b ON pa.booking_id = b.id JOIN properties p ON b.property_id = p.id WHERE b.customer_id = ? ORDER BY pa.created_at DESC LIMIT {$docLimit}";
                $allotmentStmt = $this->db->prepare($allotmentSql);
                $allotmentStmt->execute([$userId]);
                $documents = array_merge($documents, $allotmentStmt->fetchAll(PDO::FETCH_ASSOC));
            } catch (\Throwable $e) { error_log("[getCustomerDocuments] allotments skipped: " . $e->getMessage()); }

            usort($documents, function($a, $b) {
                return strtotime($b['uploaded_at'] ?? 0) - strtotime($a['uploaded_at'] ?? 0);
            });

            echo json_encode(['success' => true, 'data' => $documents]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Get Customer Documents API error');
        }
    }

    private function getUserProfileData($userId)
    {
        try {
            $sql = "SELECT u.id, u.name, u.email, u.phone, u.role, u.created_at, u.updated_at, COALESCE(mp.current_level, 'Customer') as rank FROM users u LEFT JOIN mlm_profiles mp ON u.id = mp.user_id WHERE u.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getUserDocuments($userId)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM user_documents WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function createBooking($userId, $data)
    {
        $stmt = $this->db->prepare("INSERT INTO bookings (customer_id, property_id, booking_date, amount, status, created_at) VALUES (?, ?, ?, 0, 'pending', NOW())");
        $stmt->execute([$userId, $data['property_id'], $data['booking_date'] ?? date('Y-m-d')]);
        return $this->db->lastInsertId();
    }

    private function getInquiriesData($userId)
    {
        $stmt = $this->db->prepare("SELECT id, property_id, subject, message, status, created_at FROM inquiries WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function createInquiry($data)
    {
        $stmt = $this->db->prepare("INSERT INTO inquiries (property_id, name, email, phone, message, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $data['property_id'],
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['message']
        ]);
        return $this->db->lastInsertId();
    }

    private function getReferralDashboardData($userId)
    {
        $data = [];
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM referrals WHERE referrer_id = ?");
        $stmt->execute([$userId]);
        $data['total_referrals'] = (int)$stmt->fetchColumn();

        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM referrals WHERE referrer_id = ? AND status = 'converted'");
        $stmt->execute([$userId]);
        $data['converted_referrals'] = (int)$stmt->fetchColumn();

        $stmt = $this->db->prepare("SELECT COALESCE(SUM(bonus), 0) as total FROM referral_commissions WHERE referrer_id = ?");
        $stmt->execute([$userId]);
        $data['total_earned'] = (float)$stmt->fetchColumn();

        return $data;
    }

    private function trackReferralShareData($userId, $medium)
    {
        $stmt = $this->db->prepare("INSERT INTO referral_shares (user_id, medium, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$userId, $medium]);
    }

    public function createBookingRequest()
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
            [$validated, $errors] = $this->validateInput($input, [
                'property_id' => 'required|integer',
                'booking_date' => 'nullable|string',
            ]);
            if (!empty($errors)) {
                $this->validationError($errors);
            }
            $bookingId = $this->createBooking($userId, $validated);
            echo json_encode(['success' => true, 'booking_id' => $bookingId]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Create Booking API error');
        }
    }

    public function listInquiries()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $inquiries = $this->getInquiriesData($userId);
            echo json_encode(['success' => true, 'data' => $inquiries]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'List Inquiries API error');
        }
    }

    public function submitInquiryV2()
    {
        $this->setCorsHeaders();
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            [$validated, $errors] = $this->validateInput($input, [
                'property_id' => 'required|integer',
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:20',
                'message' => 'required|string|max:5000',
            ]);
            if (!empty($errors)) {
                $this->validationError($errors);
            }
            $inquiryId = $this->createInquiry($validated);
            echo json_encode([
                'success' => true,
                'message' => 'Inquiry submitted successfully',
                'inquiry_id' => $inquiryId
            ]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Submit Inquiry API error');
        }
    }

    public function getReferralDashboard()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $dashboard = $this->getReferralDashboardData($userId);
            echo json_encode(['success' => true, 'data' => $dashboard]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Referral Dashboard API error');
        }
    }

    public function trackReferralShare()
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
            $medium = \App\Core\Security::sanitize($input['medium'] ?? 'share');
            $this->trackReferralShareData($userId, $medium);
            echo json_encode(['success' => true, 'message' => 'Referral share tracked']);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Track Referral Share API error');
        }
    }


    public function getCustomerBookings()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'User ID is required']);
            return;
        }
        try {
            $bookings = $this->getCustomerBookingsData($userId);
            echo json_encode(['success' => true, 'data' => $bookings]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Customer Bookings API error');
        }
    }

    public function getEmiSchedule()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'User ID is required']);
            return;
        }
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: $_GET;
            $bookingId = (int)($input['booking_id'] ?? 0);
            $emiSchedule = $this->getEmiScheduleData($userId, $bookingId);
            echo json_encode(['success' => true, 'data' => $emiSchedule]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'EMI Schedule API error');
        }
    }

    public function makeEmiPayment()
    {
        $this->setCorsHeaders();
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $bookingId = (int)($input['booking_id'] ?? 0);
            $amount = (float)($input['amount'] ?? 0);
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId || !$bookingId || $amount <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'User ID, booking ID and valid amount are required']);
                return;
            }
            $result = $this->processEmiPayment($userId, $bookingId, $amount);
            echo json_encode(['success' => true, 'message' => 'EMI payment processed', 'payment_id' => $result]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'EMI Payment API error');
        }
    }

    public function profileV2()
    {
        $this->setCorsHeaders();
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                $token = str_replace('Bearer ', '', ($_SERVER['HTTP_AUTHORIZATION'] ?? ''));
                $userId = (int)($GLOBALS['api_user_id'] ?? 0);
                if (!$userId) {
                    http_response_code(401);
                    echo json_encode(['success' => false, 'error' => 'Authentication required']);
                    return;
                }
            }
            $profile = $this->getUserProfileData($userId);
            echo json_encode(['success' => true, 'data' => $profile]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Profile V2 API error');
        }
    }

    public function userProfile()
    {
        $this->profileV2();
    }

    public function mobileProperties()
    {
        $this->setCorsHeaders();
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: $_GET;
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            $properties = MobilePropertyApiController::getPropertiesWithFilters($this->db, $input, $userId);
            echo json_encode(['success' => true, 'data' => $properties]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Mobile Properties API error');
        }
    }

    public function dashboardV2()
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            $dashboard = $this->getDashboardData($userId);
            echo json_encode(['success' => true, 'data' => $dashboard]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Dashboard V2 API error');
        }
    }

    public function dashboardV3()
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            $dashboard = $this->getDashboardData($userId);
            echo json_encode(['success' => true, 'data' => $dashboard]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Dashboard V3 API error');
        }
    }

    public function registerPushTokenV2()
    {
        $this->setCorsHeaders();
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            $token = $input['token'] ?? $_POST['token'] ?? '';
            if (!$userId || empty($token)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'User ID and token are required']);
                return;
            }
            $token = \App\Core\Security::sanitize(substr(trim($token), 0, 512));
            if (strlen($token) < 10) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid push token']);
                return;
            }
            $stmt = $this->db->prepare("INSERT INTO fcm_tokens (user_id, token) VALUES (?, ?) ON DUPLICATE KEY UPDATE token = VALUES(token)");
            $stmt->execute([$userId, $token]);
            echo json_encode(['success' => true, 'message' => 'Push token registered']);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Register Push Token API error');
        }
    }

    public function uploadAvatar()
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Authentication required']);
                return;
            }
            $imageData = $_POST['image'] ?? $_POST['avatar'] ?? null;
            if (!$imageData) {
                if (!isset($_FILES['avatar'])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'No avatar file provided']);
                    return;
                }
            }

            $uploadDir = 'uploads/avatars/' . $userId . '/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            if (isset($_FILES['avatar'])) {
                $validator = new \App\Helpers\UploadValidator();
                if (!$validator->validate($_FILES['avatar'], 10 * 1024 * 1024)) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Invalid avatar file']);
                    return;
                }
                $filename = time() . '_' . basename($_FILES['avatar']['name']);
                $targetFile = $uploadDir . $filename;
                if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $targetFile)) {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'error' => 'Failed to upload avatar']);
                    return;
                }
                $avatarUrl = '/uploads/avatars/' . $userId . '/' . $filename;
            } else {
                $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
                $imageData = base64_decode($imageData);
                if ($imageData === false || strlen($imageData) > 5 * 1024 * 1024) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Invalid or oversized avatar data']);
                    return;
                }
                $tmpFile = tempnam(sys_get_temp_dir(), 'avatar_');
                file_put_contents($tmpFile, $imageData);
                $imageInfo = @getimagesize($tmpFile);
                if ($imageInfo === false || !in_array($imageInfo[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP])) {
                    unlink($tmpFile);
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Avatar must be a valid JPEG, PNG, GIF, or WebP image']);
                    return;
                }
                $extensions = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif', IMAGETYPE_WEBP => 'webp'];
                $ext = $extensions[$imageInfo[2]];
                $filename = time() . '_avatar.' . $ext;
                $targetFile = $uploadDir . $filename;
                rename($tmpFile, $targetFile);
                $avatarUrl = '/uploads/avatars/' . $userId . '/' . $filename;
            }

            $stmt = $this->db->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
            $stmt->execute([$avatarUrl, $userId]);
            echo json_encode(['success' => true, 'message' => 'Avatar uploaded successfully', 'url' => $avatarUrl]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Upload Avatar API error');
        }
    }

    public function updateProfileV2()
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
            $updates = [];
            $params = [];
            $allowedFields = ['name', 'email', 'phone', 'address'];
            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    $updates[] = "$field = ?";
                    $params[] = \App\Core\Security::sanitize($input[$field]);
                }
            }
            if (!empty($updates)) {
                $params[] = $userId;
                $stmt = $this->db->prepare("UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?");
                $stmt->execute($params);
            }
            $profile = $this->getUserProfileData($userId);
            echo json_encode(['success' => true, 'data' => $profile]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Update Profile API error');
        }
    }

    public function getPendingPayouts()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $payouts = MobileMLMApiController::getPendingPayoutsData($this->db, $userId);
            echo json_encode(['success' => true, 'data' => $payouts]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Pending Payouts API error');
        }
    }

    public function processPayouts()
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
            $paymentMethod = \App\Core\Security::sanitize($input['payment_method'] ?? 'bank');
            $result = MobileMLMApiController::processPayoutsAction($this->db, $userId, $paymentMethod);
            echo json_encode(['success' => true, 'message' => 'Payouts processed', 'processed' => $result]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Process Payouts API error');
        }
    }

    public function getPayoutHistory()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $history = MobileMLMApiController::getPayoutHistoryData($this->db, $userId);
            echo json_encode(['success' => true, 'data' => $history]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Payout History API error');
        }
    }

    public function getCustomerNotifications()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $notifications = $this->getNotificationsData($userId);
            echo json_encode(['success' => true, 'data' => $notifications]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Customer Notifications API error');
        }
    }

    public function markNotificationsRead()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
            $stmt->execute([$userId]);
            echo json_encode(['success' => true, 'message' => 'All notifications marked as read']);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Mark Notifications Read API error');
        }
    }

    public function getSupportTickets()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $tickets = $this->getSupportTicketsData($userId);
            echo json_encode(['success' => true, 'data' => $tickets]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Support Tickets API error');
        }
    }

    public function createSupportTicket()
    {
        $this->setCorsHeaders();
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            [$validated, $errors] = $this->validateInput($input, [
                'subject' => 'required|string|max:255',
                'message' => 'required|string|max:5000',
                'category' => 'nullable|string',
            ]);
            if (!empty($errors)) {
                $this->validationError($errors);
            }
            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Authentication required']);
                return;
            }
            $ticketId = $this->createTicket($userId, $validated);
            echo json_encode(['success' => true, 'ticket_id' => $ticketId]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Create Support Ticket API error');
        }
    }

    public function getSupportTicketDetail($id)
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $ticket = $this->getTicketDetail($userId, (int)$id);
            echo json_encode(['success' => true, 'data' => $ticket]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Support Ticket Detail API error');
        }
    }

    public function getConversations()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $conversations = $this->getConversationsData($userId);
            echo json_encode(['success' => true, 'data' => $conversations]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Conversations API error');
        }
    }

    public function getMessages($otherUserId)
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $messages = $this->getMessagesData($userId, (int)$otherUserId);
            echo json_encode(['success' => true, 'data' => $messages]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Messages API error');
        }
    }

    public function sendMessage()
    {
        $this->setCorsHeaders();
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            [$validated, $errors] = $this->validateInput($input, [
                'to_user_id' => 'required|integer',
                'message' => 'required|string|max:10000',
            ]);
            if (!empty($errors)) {
                $this->validationError($errors);
            }
            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Authentication required']);
                return;
            }
            $messageId = $this->sendMessageData($userId, $validated);
            echo json_encode(['success' => true, 'message_id' => $messageId]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Send Message API error');
        }
    }

    public function markMessagesRead($otherUserId)
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $stmt = $this->db->prepare("UPDATE messages SET read_at = NOW() WHERE sender_id = ? AND receiver_id = ? AND read_at IS NULL");
            $stmt->execute([$otherUserId, $userId]);
            echo json_encode(['success' => true, 'message' => 'Messages marked as read']);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Mark Messages Read API error');
        }
    }

    public function getUnreadCount()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $count = $this->getUnreadCountData($userId);
            echo json_encode(['success' => true, 'count' => $count]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Unread Count API error');
        }
    }

    public function createNotification()
    {
        $this->setCorsHeaders();
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            [$validated, $errors] = $this->validateInput($input, [
                'title' => 'required|string|max:255',
                'message' => 'required|string|max:5000',
                'type' => 'nullable|string',
            ]);
            if (!empty($errors)) {
                $this->validationError($errors);
            }
            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Authentication required']);
                return;
            }

            // Create notification via NotificationService
            $notifService = new \App\Services\NotificationService($this->db);
            $notifId = $notifService->notify($validated['title'], $validated['message'], $userId, $validated['type'] ?? 'system');
            echo json_encode(['success' => true, 'notification_id' => $notifId]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Create Notification API error');
        }
    }

    public function markNotificationRead($id)
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND id = ?");
            $stmt->execute([$userId, (int)$id]);
            echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Mark Notification Read API error');
        }
    }

    public function deleteNotification($id)
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $stmt = $this->db->prepare("DELETE FROM notifications WHERE user_id = ? AND id = ?");
            $stmt->execute([$userId, (int)$id]);
            echo json_encode(['success' => true, 'message' => 'Notification deleted']);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Delete Notification API error');
        }
    }

    public function getUserBankAccounts()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $accounts = $this->getUserBankAccountsData($userId);
            echo json_encode(['success' => true, 'data' => $accounts]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Bank Accounts API error');
        }
    }

    public function saveUserBankAccount()
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
            [$validated, $errors] = $this->validateInput($input, [
                'account_holder_name' => 'required|string|max:255',
                'account_number' => 'required|string|max:50',
                'ifsc_code' => 'required|string|max:20',
                'bank_name' => 'nullable|string|max:255',
            ]);
            if (!empty($errors)) {
                $this->validationError($errors);
            }
            $accountId = $this->saveUserBankAccountData($userId, $validated);
            echo json_encode(['success' => true, 'message' => 'Bank account saved', 'account_id' => $accountId]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Save Bank Account API error');
        }
    }

    public function deleteUserBankAccount()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $accountId = (int)($_POST['account_id'] ?? $_DELETE['account_id'] ?? 0);
            if (!$accountId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Account ID is required']);
                return;
            }
            $stmt = $this->db->prepare("DELETE FROM user_bank_accounts WHERE user_id = ? AND id = ?");
            $stmt->execute([$userId, $accountId]);
            echo json_encode(['success' => true, 'message' => 'Bank account deleted']);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Delete Bank Account API error');
        }
    }

    public function getUserAddresses()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $addresses = $this->getUserAddressesData($userId);
            echo json_encode(['success' => true, 'data' => $addresses]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'User Addresses API error');
        }
    }

    public function saveUserAddress()
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
            [$validated, $errors] = $this->validateInput($input, [
                'address_line1' => 'required|string|max:500',
                'address_line2' => 'nullable|string|max:500',
                'city' => 'required|string|max:100',
                'state' => 'required|string|max:100',
                'postal_code' => 'required|string|max:20',
            ]);
            if (!empty($errors)) {
                $this->validationError($errors);
            }
            $addressId = $this->saveUserAddressData($userId, $validated);
            echo json_encode(['success' => true, 'message' => 'Address saved', 'address_id' => $addressId]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Save Address API error');
        }
    }

    public function deleteUserAddress()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $addressId = (int)($_POST['address_id'] ?? $_DELETE['address_id'] ?? 0);
            if (!$addressId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Address ID is required']);
                return;
            }
            $stmt = $this->db->prepare("DELETE FROM user_addresses WHERE user_id = ? AND id = ?");
            $stmt->execute([$userId, $addressId]);
            echo json_encode(['success' => true, 'message' => 'Address deleted']);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Delete Address API error');
        }
    }

    private function getCustomerBookingsData($userId)
    {
        $stmt = $this->db->prepare("
            SELECT b.id, b.booking_date, b.amount, b.status, b.created_at,
                   p.title as property_title,
                   p.price as property_price
            FROM bookings b
            LEFT JOIN properties p ON b.property_id = p.id
            WHERE b.customer_id = ?
            ORDER BY b.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getEmiScheduleData($userId, $bookingId = 0)
    {
        $bookSql = $bookingId > 0 ? " AND e.booking_id = ?" : "";
        $params = $bookingId > 0 ? [$userId, $bookingId] : [$userId];
        $stmt = $this->db->prepare("
            SELECT e.id, e.booking_id, e.due_date, e.amount, e.paid_amount, e.status, e.paid_date,
                   b.booking_date,
                   p.title as property_title
            FROM emi_schedule e
            LEFT JOIN bookings b ON e.booking_id = b.id
            LEFT JOIN properties p ON b.property_id = p.id
            WHERE b.customer_id = ?{$bookSql}
            ORDER BY e.due_date ASC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function processEmiPayment($userId, $bookingId, $amount)
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("SELECT e.id FROM emi_schedule e JOIN bookings b ON e.booking_id = b.id WHERE b.id = ? AND b.customer_id = ? AND e.status = 'pending' ORDER BY e.due_date ASC LIMIT 1");
            $stmt->execute([$bookingId, $userId]);
            $emi = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$emi) {
                $this->db->rollBack();
                return false;
            }

            $stmt = $this->db->prepare("UPDATE emi_schedule SET status = 'paid', paid_amount = ?, paid_date = NOW() WHERE id = ?");
            $stmt->execute([$amount, $emi['id']]);

            $stmt = $this->db->prepare("INSERT INTO payments (booking_id, user_id, amount, payment_type, status, created_at) VALUES (?, ?, ?, 'emi', 'completed', NOW())");
            $stmt->execute([$bookingId, $userId, $amount]);

            $this->db->commit();
            return $emi['id'];
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function getDashboardData($userId)
    {
        $data = [];

        // My Bookings
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM bookings WHERE customer_id = ? AND status IN ('confirmed', 'pending')");
        $stmt->execute([$userId]);
        $data['my_bookings'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Pending Payments
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(e.amount - e.paid_amount), 0) as total FROM emi_schedule e JOIN bookings b ON e.booking_id = b.id WHERE b.customer_id = ? AND e.status = 'pending'");
        $stmt->execute([$userId]);
        $data['pending_payments'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Recent Activities
        $stmt = $this->db->prepare("SELECT id, title, message, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
        $stmt->execute([$userId]);
        $data['recent_activities'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }

    private function getNotificationsData($userId)
    {
        // NOTE: notifications has no `data` column; template_data (TEXT, JSON payload) is its equivalent.
        $stmt = $this->db->prepare("SELECT id, title, message, type, template_data AS data, action_url, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getSupportTicketsData($userId)
    {
        $stmt = $this->db->prepare("SELECT id, subject, category, status, created_at, updated_at FROM support_tickets WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function createTicket($userId, $data)
    {
        $stmt = $this->db->prepare("INSERT INTO support_tickets (user_id, subject, message, category, status, created_at, updated_at) VALUES (?, ?, ?, ?, 'open', NOW(), NOW())");
        $stmt->execute([$userId, $data['subject'], $data['message'], $data['category'] ?? 'general']);
        return $this->db->lastInsertId();
    }

    private function getTicketDetail($userId, $id)
    {
        $stmt = $this->db->prepare("SELECT t.*, u.name as user_name FROM support_tickets t JOIN users u ON t.user_id = u.id WHERE t.id = ? AND t.user_id = ?");
        $stmt->execute([$id, $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getConversationsData($userId)
    {
        $stmt = $this->db->prepare("
            SELECT m.id, m.sender_id, m.content as message, m.sent_at as created_at,
                   u.name as sender_name,
                   (SELECT COUNT(*) FROM messages WHERE sender_id = m.sender_id AND receiver_id = ? AND read_at IS NULL) as unread_count
            FROM messages m
            JOIN users u ON m.sender_id = u.id
            WHERE m.receiver_id = ?
            GROUP BY m.sender_id
            ORDER BY MAX(m.sent_at) DESC
        ");
        $stmt->execute([$userId, $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getMessagesData($userId, $otherUserId)
    {
        $stmt = $this->db->prepare("
            SELECT m.id, m.sender_id, m.content as message, m.sent_at as created_at, (m.read_at IS NOT NULL) as is_read,
                   u.name as sender_name
            FROM messages m
            JOIN users u ON m.sender_id = u.id
            WHERE ((m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?))
            ORDER BY m.sent_at ASC
        ");
        $stmt->execute([$userId, $otherUserId, $otherUserId, $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function sendMessageData($userId, $data)
    {
        $stmt = $this->db->prepare("INSERT INTO messages (sender_id, receiver_id, content, sent_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$userId, $data['to_user_id'], $data['message']]);
        return $this->db->lastInsertId();
    }

    private function getUnreadCountData($userId)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM messages WHERE receiver_id = ? AND read_at IS NULL");
        $stmt->execute([$userId]);
        return (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    private function getUserBankAccountsData($userId)
    {
        $stmt = $this->db->prepare("SELECT id, account_holder_name, account_number, ifsc_code, bank_name, is_default, created_at FROM user_bank_accounts WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function saveUserBankAccountData($userId, $data)
    {
        $stmt = $this->db->prepare("INSERT INTO user_bank_accounts (user_id, account_holder_name, account_number, ifsc_code, bank_name, is_primary, created_at) VALUES (?, ?, ?, ?, ?, 0, NOW())");
        $stmt->execute([$userId, $data['account_holder_name'], $data['account_number'], $data['ifsc_code'], $data['bank_name'] ?? null]);
        return $this->db->lastInsertId();
    }

    private function getUserAddressesData($userId)
    {
        $stmt = $this->db->prepare("SELECT id, address_line1, address_line2, city, state, postal_code, is_default, created_at FROM user_addresses WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function saveUserAddressData($userId, $data)
    {
        $stmt = $this->db->prepare("INSERT INTO user_addresses (user_id, address_line1, address_line2, city, state, pincode, is_primary, created_at) VALUES (?, ?, ?, ?, ?, ?, 0, NOW())");
        $stmt->execute([$userId, $data['address_line1'], $data['address_line2'] ?? null, $data['city'], $data['state'], $data['postal_code']]);
        return $this->db->lastInsertId();
    }

    public function updateNotificationPreferences() {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = [];
        $input = $this->sanitizeArrayDeep($input);
        try {
            $prefs = json_encode($input);
            $this->db->prepare("UPDATE users SET notification_preferences = ? WHERE id = ?")->execute([$prefs, $userId]);
            echo json_encode(['success'=>true]);
        } catch (\Exception $e) {
            echo json_encode(['success'=>true]);
        }
    }

    public function updateUserPreferences() {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = [];
        $input = $this->sanitizeArrayDeep($input);
        try {
            $prefs = json_encode($input);
            $this->db->prepare("UPDATE users SET preferences = ? WHERE id = ?")->execute([$prefs, $userId]);
            echo json_encode(['success'=>true]);
        } catch (\Exception $e) {
            echo json_encode(['success'=>true]);
        }
    }

    public function deleteAccount() {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        $input = json_decode(file_get_contents('php://input'), true);
        $password = $input['password'] ?? '';
        try {
            if (!empty($password)) {
                $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user && !password_verify($password, $user['password'])) {
                    http_response_code(400); echo json_encode(['success'=>false,'error'=>'Incorrect password']); return;
                }
            }
            $this->db->prepare("UPDATE users SET status = 'inactive', name = CONCAT(name, '_deleted_', id), email = CONCAT('deleted_', id, '@deleted.com'), updated_at = NOW() WHERE id = ?")->execute([$userId]);
            try { $this->db->prepare("DELETE FROM api_tokens WHERE user_id = ?")->execute([$userId]); } catch (\Throwable $t) { error_log("MobileUserApiController::" . __FUNCTION__ . " query failed: " . $t->getMessage()); }
            echo json_encode(['success'=>true,'message'=>'Account deleted']);
        } catch (\Exception $e) {
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    public function getPaymentHistory() {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        try {
            $stmt = $this->db->prepare("SELECT p.payment_id AS id, p.booking_id, b.booking_number, b.plot_id, p.payment_amount AS amount, p.payment_method, p.transaction_id, p.payment_notes, p.payment_date FROM booking_payments p JOIN plot_bookings b ON p.booking_id = b.id WHERE b.customer_id = ? ORDER BY p.payment_date DESC LIMIT 50");
            $stmt->execute([$userId]);
            $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stats = [
                'total_paid' => 0,
                'total_count' => count($payments),
                'completed' => 0,
                'pending' => 0,
                'failed' => 0,
            ];
            foreach ($payments as &$p) {
                $p['amount'] = (float)$p['amount'];
                $p['status'] = 'completed';
                $stats['total_paid'] += $p['amount'];
                $stats['completed']++;
            }
            echo json_encode(['success' => true, 'data' => ['payments' => $payments, 'stats' => $stats]]);
        } catch (\Exception $e) {
            echo json_encode(['success' => true, 'data' => ['payments' => [], 'stats' => ['total_paid' => 0, 'total_count' => 0, 'completed' => 0, 'pending' => 0, 'failed' => 0]]]);
        }
    }

    public function getBlogPosts() {
        $this->setCorsHeaders();
        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            $stmt = $pdo->query("SELECT id, title, slug, excerpt, featured_image, category, created_at as published_at, 'APS Dream Home' as author, 5 as reading_time FROM blog_posts WHERE status = 'published' ORDER BY created_at DESC LIMIT 20");
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($posts as &$post) {
                if (!empty($post['featured_image'])) {
                    $post['featured_image'] = (defined('BASE_URL') ? BASE_URL : '') . '/' . ltrim($post['featured_image'], '/');
                }
            }
            echo json_encode(['success' => true, 'data' => $posts]);
        } catch (\Throwable $e) {
            error_log('getBlogPosts error: ' . $e->getMessage());
            echo json_encode(['success' => true, 'data' => []]);
        }
    }

    public function getBlogPostDetail($slug) {
        $this->setCorsHeaders();
        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("SELECT id, title, slug, content, excerpt, featured_image, category, tags, views, created_at as published_at, 'APS Dream Home' as author, 5 as reading_time FROM blog_posts WHERE slug = ? AND status = 'published' LIMIT 1");
            $stmt->execute([$slug]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$post) { http_response_code(404); echo json_encode(['success'=>false,'error'=>'Not found']); return; }
            if (!empty($post['featured_image'])) {
                $post['featured_image'] = (defined('BASE_URL') ? BASE_URL : '') . '/' . ltrim($post['featured_image'], '/');
            }
            echo json_encode(['success' => true, 'data' => $post]);
        } catch (\Throwable $e) {
            error_log('getBlogPostDetail error: ' . $e->getMessage());
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    public function getJobListings() {
        $this->setCorsHeaders();
        try {
            $stmt = $this->db->query("SELECT id, title, department, location, employment_type, experience_required, salary_range, vacancies, description, requirements, created_at FROM careers WHERE status = 'open' ORDER BY created_at DESC");
            $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $jobs]);
        } catch (\Exception $e) {
            echo json_encode(['success' => true, 'data' => []]);
        }
    }

    public function getJobDetail($id) {
        $this->setCorsHeaders();
        try {
            $stmt = $this->db->prepare("SELECT * FROM careers WHERE id = ? AND status = 'open' LIMIT 1");
            $stmt->execute([$id]);
            $job = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$job) { http_response_code(404); echo json_encode(['success'=>false,'error'=>'Not found']); return; }
            echo json_encode(['success' => true, 'data' => $job]);
        } catch (\Exception $e) {
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    public function submitJobApplication() {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        $input = json_decode(file_get_contents('php://input'), true);
        $jobId = $input['job_id'] ?? null;
        $name = \App\Core\Security::sanitize($input['name'] ?? '');
        $email = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $phone = preg_replace('/[^0-9+\-\s()]/', '', $input['phone'] ?? '');
        $coverLetter = \App\Core\Security::sanitize($input['cover_letter'] ?? '');
        $experience = (int)($input['experience'] ?? 0);
        $company = \App\Core\Security::sanitize($input['current_company'] ?? '');
        if (!$jobId || empty($name) || empty($email) || empty($phone)) {
            http_response_code(400); echo json_encode(['success'=>false,'error'=>'Required fields missing']); return;
        }
        try {
            $tid = (int)$this->tenantId();
            $stmt = $this->db->prepare("INSERT INTO career_applications (career_id, full_name, email, phone, cover_letter, experience_years, current_company, status, tenant_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'new', ?, NOW())");
            $stmt->execute([$jobId, $name, $email, $phone, $coverLetter, $experience, $company, $tid]);
            echo json_encode(['success' => true, 'message' => 'Application submitted successfully']);
        } catch (\Exception $e) {
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    public function getAboutInfo() {
        $this->setCorsHeaders();
        try {
            $stmt = $this->db->query("SELECT content_key, content_value FROM site_content WHERE section = 'about' AND is_active = 1");
            $content = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            $team = [];
            try {
                $stmt2 = $this->db->query("SELECT id, name, position, photo, bio, experience, expertise, linkedin, facebook_url, instagram_url, category, group_name, sort_order FROM team_members WHERE status = 'active' ORDER BY sort_order ASC, id ASC LIMIT 10");
                $team = $stmt2->fetchAll(PDO::FETCH_ASSOC);
            } catch (\Exception $e) { error_log("MobileUserApiController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }
            echo json_encode(['success' => true, 'data' => ['content' => $content, 'team' => $team, 'stats' => ['projects' => 4, 'plots' => 5000, 'families' => 500, 'colonies' => 4]]]);
        } catch (\Exception $e) {
            echo json_encode(['success' => true, 'data' => ['content' => [], 'team' => [], 'stats' => ['projects' => 4, 'plots' => 5000, 'families' => 500, 'colonies' => 4]]]);
        }
    }

    public function getLoans() {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        try {
            $svc = new \App\Services\Loan\CompanyLoanService($this->db->getConnection());
            $loans = $svc->listLoans(['customer_id' => $userId]);
            echo json_encode(['success'=>true, 'data' => $loans]);
        } catch (\Exception $e) {
            echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
        }
    }

    public function getLoanDetail($id) {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        try {
            $svc = new \App\Services\Loan\CompanyLoanService($this->db->getConnection());
            $loan = $svc->getLoanById((int)$id);
            if (!$loan) { http_response_code(404); echo json_encode(['success'=>false,'error'=>'Not found']); return; }
            $installments = $svc->getInstallments((int)$id);
            $guarantors = $svc->getGuarantors((int)$id);
            $documents = $svc->getDocuments((int)$id);
            echo json_encode(['success'=>true, 'data'=>['loan'=>$loan,'installments'=>$installments,'guarantors'=>$guarantors,'documents'=>$documents]]);
        } catch (\Exception $e) {
            echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
        }
    }

    public function getLoanInstallments($id) {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        try {
            $svc = new \App\Services\Loan\CompanyLoanService($this->db->getConnection());
            $installments = $svc->getInstallments((int)$id);
            echo json_encode(['success'=>true, 'data'=>$installments]);
        } catch (\Exception $e) {
            echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
        }
    }

    public function applyLoan() {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input)) $input = $_POST;
        $input['customer_id'] = $userId;
        if (isset($input['purpose'])) $input['purpose'] = \App\Core\Security::sanitize((string)$input['purpose']);
        if (isset($input['notes'])) $input['notes'] = \App\Core\Security::sanitize((string)$input['notes']);
        if (isset($input['interest_type'])) $input['interest_type'] = in_array($input['interest_type'], ['flat','reducing','bullet']) ? $input['interest_type'] : 'flat';
        if (isset($input['start_date'])) {
            $ts = strtotime($input['start_date']);
            $input['start_date'] = $ts ? date('Y-m-d', $ts) : date('Y-m-d');
        }
        try {
            $svc = new \App\Services\Loan\CompanyLoanService($this->db->getConnection());
            $result = $svc->createLoan($input);
            echo json_encode($result);
        } catch (\Exception $e) {
            echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
        }
    }

    public function getLoanOffers() {
        $this->setCorsHeaders();
        try {
            $svc = new \App\Services\Loan\CompanyLoanService($this->db->getConnection());
            $offers = $svc->getOffers();
            $offers = array_map(function($o) { return ['id'=>$o['id'],'name'=>$o['name'],'description'=>$o['description'],'offer_type'=>$o['offer_type'],'interest_free_months'=>$o['interest_free_months'],'max_tenure_months'=>$o['max_tenure_months'],'max_amount'=>$o['max_amount']]; }, $offers);
            echo json_encode(['success'=>true, 'data'=>$offers]);
        } catch (\Exception $e) {
            echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
        }
    }

    public function calculateLoanEligibility() {
        $this->setCorsHeaders();
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input)) $input = $_GET;
        $amount = (float)($input['amount'] ?? 0);
        $rate = (float)($input['rate'] ?? 10);
        $tenure = (int)($input['tenure_months'] ?? 60);
        $interestFreeMonths = (int)($input['interest_free_months'] ?? 0);
        try {
            $svc = new \App\Services\Loan\InterestFreeOfferService($this->db->getConnection());
            $result = $svc->calculateSavings($amount, $rate, $tenure, $interestFreeMonths);
            echo json_encode(['success'=>true, 'data'=>$result]);
        } catch (\Throwable $e) {
            echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
        }
    }

    public function getEarlySettlement($id) {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        try {
            $svc = new \App\Services\Loan\CompanyLoanService($this->db->getConnection());
            $result = $svc->calculateEarlySettlement((int)$id);
            echo json_encode($result);
        } catch (\Exception $e) {
            echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
        }
    }

    public function reraVerify($reraNumber = null) {
        $this->setCorsHeaders();
        $reraNumber = $reraNumber ?? ($_GET['rera_number'] ?? '');
        $stateCode = strtoupper($_GET['state_code'] ?? 'UP');

        if (empty($reraNumber)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'RERA number is required']);
            return;
        }

        try {
            $service = new \App\Services\Legal\RERAVerificationService();
            $result = $service->verifyByReraNumber($reraNumber, $stateCode);

            if (!empty($result['success']) && !empty($result['project'])) {
                $project = $result['project'];
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'rera_number' => $project['rera_number'] ?? $reraNumber,
                        'project_name' => $project['project_name'] ?? '',
                        'builder_name' => $project['builder_name'] ?? '',
                        'status' => $project['status'] ?? 'Unknown',
                        'registration_date' => $project['registration_date'] ?? '',
                        'valid_upto' => $project['valid_upto'] ?? '',
                        'total_area' => $project['total_area'] ?? '',
                        'total_units' => $project['total_units'] ?? 0,
                        'address' => $project['address'] ?? '',
                        'city' => $project['city'] ?? '',
                        'state_code' => $project['state_code'] ?? $stateCode,
                    ],
                    'source' => $result['source'] ?? 'database',
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'No project found for RERA number: ' . $reraNumber,
                ]);
            }
        } catch (\Throwable $e) {
            error_log('reraVerify error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Verification failed']);
        }
    }

    public function reraSearch() {
        $this->setCorsHeaders();
        $builder = $_GET['builder'] ?? '';
        $city = $_GET['city'] ?? '';
        $stateCode = strtoupper($_GET['state_code'] ?? 'UP');

        try {
            $service = new \App\Services\Legal\RERAVerificationService();
            $criteria = ['state_code' => $stateCode];
            if ($builder) $criteria['builder_name'] = $builder;
            if ($city) $criteria['city'] = $city;

            $result = $service->searchProjects($criteria);
            echo json_encode([
                'success' => true,
                'data' => $result['projects'] ?? [],
            ]);
        } catch (\Throwable $e) {
            error_log('reraSearch error: ' . $e->getMessage());
            echo json_encode(['success' => true, 'data' => []]);
        }
    }

    public function reraProjects() {
        $this->setCorsHeaders();
        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            $stmt = $pdo->query("SELECT id, rera_number, project_name, builder_name, status, registration_date, valid_upto, total_area, total_units, city, state_code FROM rera_projects WHERE is_active = 1 ORDER BY created_at DESC LIMIT 20");
            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $projects]);
        } catch (\Throwable $e) {
            error_log('reraProjects error: ' . $e->getMessage());
            echo json_encode(['success' => true, 'data' => []]);
        }
    }

    public function directoryCategories() {
        $this->setCorsHeaders();
        try {
            $service = new \App\Services\DirectoryService();
            $categories = $service->getActiveCategories();
            $catData = [];
            foreach ($categories as $cat) {
                $catData[] = [
                    'id' => $cat['id'],
                    'name' => $cat['name'],
                    'slug' => $cat['slug'] ?? '',
                    'description' => $cat['description'] ?? '',
                    'icon' => $cat['icon'] ?? 'fas fa-building',
                    'listing_count' => (int)($cat['listing_count'] ?? 0),
                ];
            }
            echo json_encode(['success' => true, 'data' => $catData]);
        } catch (\Throwable $e) {
            error_log('directoryCategories error: ' . $e->getMessage());
            echo json_encode(['success' => true, 'data' => []]);
        }
    }

    public function directoryFeatured() {
        $this->setCorsHeaders();
        try {
            $service = new \App\Services\DirectoryService();
            $listings = $service->getFeaturedListings(10);
            $listingData = [];
            foreach ($listings as $l) {
                $listingData[] = [
                    'id' => $l['id'],
                    'business_name' => $l['business_name'] ?? '',
                    'category_name' => $l['category_name'] ?? '',
                    'rating' => (float)($l['rating'] ?? 0),
                    'review_count' => (int)($l['review_count'] ?? 0),
                    'city' => $l['city'] ?? '',
                    'is_verified' => (bool)($l['is_verified'] ?? false),
                    'description' => $l['description'] ?? '',
                    'phone' => $l['phone'] ?? '',
                ];
            }
            echo json_encode(['success' => true, 'data' => $listingData]);
        } catch (\Throwable $e) {
            error_log('directoryFeatured error: ' . $e->getMessage());
            echo json_encode(['success' => true, 'data' => []]);
        }
    }

    public function directoryJobs() {
        $this->setCorsHeaders();
        try {
            $service = new \App\Services\DirectoryService();
            $jobs = $service->getJobs('', '', -1, 1);
            $jobData = [];
            foreach ($jobs as $j) {
                $jobData[] = [
                    'id' => $j['id'],
                    'title' => $j['title'] ?? '',
                    'company' => $j['business_name'] ?? '',
                    'location' => $j['location'] ?? $j['city'] ?? '',
                    'salary_min' => (int)($j['salary_min'] ?? 0),
                    'salary_max' => (int)($j['salary_max'] ?? 0),
                    'job_type' => $j['job_type'] ?? '',
                    'category' => $j['category'] ?? '',
                ];
            }
            echo json_encode(['success' => true, 'data' => $jobData]);
        } catch (\Throwable $e) {
            error_log('directoryJobs error: ' . $e->getMessage());
            echo json_encode(['success' => true, 'data' => []]);
        }
    }

    public function markMessageRead($messageId) {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        $this->db->query(
            "UPDATE property_messages SET is_read = 1 WHERE id = ? AND receiver_id = ?",
            [(int)$messageId, $userId]
        );
        echo json_encode(['success' => true]);
    }

    /**
     * Deep-sanitize an array: escape all string values recursively.
     */
    private function sanitizeArrayDeep(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            $safeKey = htmlspecialchars((string)$key, ENT_QUOTES, 'UTF-8');
            if (is_string($value)) {
                $sanitized[$safeKey] = \App\Core\Security::sanitize($value);
            } elseif (is_array($value)) {
                $sanitized[$safeKey] = $this->sanitizeArrayDeep($value);
            } else {
                $sanitized[$safeKey] = $value;
            }
        }
        return $sanitized;
    }

    // ============================================================
    // WALLET ACTIVATION PACKAGES
    // ============================================================

    public function walletActivationPackages() {
        $this->setCorsHeaders();
        try {
            $service = new \App\Services\WalletActivationService();
            $packages = $service->getActivePackages();
            
            $data = [];
            foreach ($packages as $pkg) {
                $data[] = [
                    'id' => $pkg['id'],
                    'name' => $pkg['name'],
                    'slug' => $pkg['slug'],
                    'description' => $pkg['description'],
                    'price' => (float)$pkg['price'],
                    'referral_reward' => (float)($pkg['referral_reward'] ?? 0),
                    'referral_pct_l1' => (float)($pkg['referral_pct_l1'] ?? 0),
                    'referral_pct_l2' => (float)($pkg['referral_pct_l2'] ?? 0),
                    'features' => $pkg['features'],
                    'is_popular' => $pkg['slug'] === 'pro',
                ];
            }
            echo json_encode(['success' => true, 'data' => $data]);
        } catch (\Throwable $e) {
            error_log('walletActivationPackages error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to fetch packages']);
        }
    }

    public function walletActivationMyWallet() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Unauthorized']);
                return;
            }

            $service = new \App\Services\WalletActivationService();
            $isActivated = $service->isWalletActivated($userId);
            $userPurchase = $service->getUserPurchase($userId);
            $packages = $service->getActivePackages();

            $data = [
                'is_activated' => $isActivated,
                'current_package' => $userPurchase ? [
                    'id' => $userPurchase['id'],
                    'name' => $userPurchase['package_name'],
                    'slug' => $userPurchase['slug'],
                    'activated_at' => $userPurchase['activated_at'],
                    'expires_at' => $userPurchase['activation_expires_at'],
                    'features' => $userPurchase['features'],
                ] : null,
                'packages' => array_map(function($pkg) {
                    $pkg['features'] = $pkg['features'] ?? [];
                    $pkg['is_owned'] = $userPurchase && $userPurchase['package_id'] == $pkg['id'];
                    $pkg['is_higher_tier'] = $userPurchase && in_array($userPurchase['slug'], ['pro', 'premium']) && $pkg['slug'] === 'basic';
                    return $pkg;
                }, $packages),
            ];

            echo json_encode(['success' => true, 'data' => $data]);
        } catch (\Throwable $e) {
            error_log('walletActivationMyWallet error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to fetch wallet data']);
        }
    }

    public function walletActivationPurchase() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Unauthorized']);
                return;
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $packageId = (int)($input['package_id'] ?? 0);
            $paymentMode = $input['payment_mode'] ?? 'razorpay';

            if (!$packageId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid package']);
                return;
            }

            $service = new \App\Services\WalletActivationService();
            $result = $service->purchasePackage($userId, $packageId, $paymentMode);

            echo json_encode($result);
        } catch (\Throwable $e) {
            error_log('walletActivationPurchase error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Purchase failed']);
        }
    }

    public function walletActivationVerifyPayment() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Unauthorized']);
                return;
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $purchaseId = (int)($input['purchase_id'] ?? 0);
            $razorpayPaymentId = $input['razorpay_payment_id'] ?? '';
            $razorpayOrderId = $input['razorpay_order_id'] ?? '';
            $razorpaySignature = $input['razorpay_signature'] ?? '';

            if (!$purchaseId || !$razorpayPaymentId || !$razorpayOrderId || !$razorpaySignature) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid payment data']);
                return;
            }

            $service = new \App\Services\WalletActivationService();
            $result = $service->activatePurchase($purchaseId, $userId);

            echo json_encode($result);
        } catch (\Throwable $e) {
            error_log('walletActivationVerifyPayment error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Activation failed']);
        }
    }

    // ============================================================
    // REFERRAL EARNINGS
    // ============================================================

    public function referralEarnings() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Unauthorized']);
                return;
            }

            $service = new \App\Services\ReferralService();
            $breakdown = $service->getReferralEarningsBreakdown($userId);

            // Format for mobile
            $data = [
                'summary' => [
                    'total_referrals' => (int)($breakdown['summary']['total_referrals'] ?? 0),
                    'active_referrals' => (int)($breakdown['summary']['active_referrals'] ?? 0),
                    'total_earned' => (float)($breakdown['summary']['total_earned'] ?? 0),
                    'pending_earned' => (float)($breakdown['summary']['pending_earned'] ?? 0),
                    'this_month' => (float)($breakdown['summary']['this_month'] ?? 0),
                ],
                'by_type' => [],
                'recent' => [],
            ];

            foreach ($breakdown['by_type'] as $type => $row) {
                if (($row['count'] ?? 0) > 0) {
                    $data['by_type'][] = [
                        'type' => $type,
                        'label' => $row['label'],
                        'count' => (int)$row['count'],
                        'amount' => (float)$row['amount'],
                    ];
                }
            }

            foreach (array_slice($breakdown['recent'] ?? [], 0, 10) as $item) {
                $data['recent'][] = [
                    'type' => $item['commission_type'] ?? 'referral',
                    'amount' => (float)($item['amount'] ?? 0),
                    'status' => $item['status'] ?? 'pending',
                    'created_at' => $item['created_at'] ?? '',
                    'booking_id' => (int)($item['booking_id'] ?? 0),
                    'notes' => $item['notes'] ?? '',
                ];
            }

            echo json_encode(['success' => true, 'data' => $data]);
        } catch (\Throwable $e) {
            error_log('referralEarnings error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to fetch earnings']);
        }
    }

    public function associateOffers() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Unauthorized']);
                return;
            }
            // NOTE: plot_bookings.associate_id stores users.id, so pass $userId.
            $offers = (new \App\Services\AssociateOfferService())->visibleOffers($userId);
            echo json_encode(['success' => true, 'data' => $offers]);
        } catch (\Throwable $e) {
            error_log('associateOffers error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to fetch offers']);
        }
    }

    public function referralLeaderboard() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Unauthorized']);
                return;
            }

            $service = new \App\Services\ReferralService();
            $leaderboard = $service->getLeaderboard(20, 'all');
            $userRank = $service->getUserRank($userId);

            $data = [
                'leaderboard' => array_map(function($item) {
                    return [
                        'rank' => (int)($item['rank'] ?? 0),
                        'name' => $item['name'] ?? 'Unknown',
                        'referral_code' => $item['referral_code'] ?? '',
                        'total_referrals' => (int)($item['total_referrals'] ?? 0),
                        'total_earned' => (float)($item['total_earned'] ?? 0),
                        'tier' => $item['tier'] ?? 'bronze',
                    ];
                }, $leaderboard),
                'my_rank' => [
                    'rank' => $userRank['rank'] ?? 0,
                    'total' => $userRank['total'] ?? 0,
                    'referral_count' => $userRank['referral_count'] ?? 0,
                    'tier' => $userRank['tier'] ?? 'bronze',
                ],
            ];

            echo json_encode(['success' => true, 'data' => $data]);
        } catch (\Throwable $e) {
            error_log('referralLeaderboard error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to fetch leaderboard']);
        }
    }

    public function referralShareUrl() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Unauthorized']);
                return;
            }

            $service = new \App\Services\ReferralService();
            $code = $service->getReferralCode($userId);
            $url = $service->getShareUrl($code);
            $qr = $service->getQRCode($url);

            echo json_encode([
                'success' => true,
                'data' => [
                    'referral_code' => $code,
                    'share_url' => $url,
                    'qr_code' => $qr,
                ],
            ]);
        } catch (\Throwable $e) {
            error_log('referralShareUrl error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to generate share URL']);
        }
    }

    // ============================================================
    // WALLET BALANCE
    // ============================================================

    public function walletBalance() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Unauthorized']);
                return;
            }

            $service = new \App\Services\WalletService();
            $balance = $service->getBalance($userId);
            $transactions = $service->getTransactions($userId, 10);

            echo json_encode([
                'success' => true,
                'data' => [
                    'balance' => (float)$balance,
                    'recent_transactions' => array_map(function($txn) {
                        return [
                            'id' => $txn['id'] ?? 0,
                            'type' => $txn['transaction_type'] ?? '',
                            'category' => $txn['transaction_category'] ?? '',
                            'amount' => (float)($txn['amount'] ?? 0),
                            'balance_after' => (float)($txn['balance_after'] ?? 0),
                            'description' => $txn['description'] ?? '',
                            'created_at' => $txn['created_at'] ?? '',
                        ];
                    }, $transactions),
                ],
            ]);
        } catch (\Throwable $e) {
            error_log('walletBalance error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to fetch wallet']);
        }
    }

    // ============================================================
    // EMPLOYEE SELF-SERVICE PORTAL API
    // ============================================================

    public function getTaxRegime() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }

            $service = new \App\Services\EmployeeSelfServiceService();
            $currentYear = (int)date('Y');
            $regime = $service->getTaxRegime($userId, $currentYear);

            echo json_encode(['success' => true, 'data' => $regime]);
        } catch (\Throwable $e) {
            error_log('getTaxRegime error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to fetch tax regime']);
        }
    }

    public function setTaxRegime() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }

            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $regime = $input['regime'] ?? 'new';
            $financialYear = (int)($input['financial_year'] ?? date('Y'));

            if (!in_array($regime, ['old', 'new'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid regime']);
                return;
            }

            $service = new \App\Services\EmployeeSelfServiceService();
            $result = $service->setTaxRegime($userId, $financialYear, $regime);
            echo json_encode($result);
        } catch (\Throwable $e) {
            error_log('setTaxRegime error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to set tax regime']);
        }
    }

    public function getInvestmentDeclaration() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }

            $financialYear = (int)($_GET['financial_year'] ?? date('Y'));
            $service = new \App\Services\EmployeeSelfServiceService();
            $declarations = $service->getInvestmentDeclaration($userId, $financialYear);

            // Section limits
            $sectionLimits = [
                '80C' => 150000, '80CCD1B' => 50000, '80D' => 25000, 'HRA' => 0,
                '24B' => 200000, '80E' => 0, '80G' => 0, '80TTA' => 10000,
            ];

            echo json_encode([
                'success' => true,
                'data' => [
                    'financial_year' => $financialYear,
                    'declarations' => $declarations,
                    'section_limits' => $sectionLimits,
                ],
            ]);
        } catch (\Throwable $e) {
            error_log('getInvestmentDeclaration error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to fetch declarations']);
        }
    }

    public function saveInvestmentDeclaration() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }

            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $financialYear = (int)($input['financial_year'] ?? date('Y'));
            $declarations = $input['declarations'] ?? [];

            $service = new \App\Services\EmployeeSelfServiceService();
            $result = $service->saveInvestmentDeclaration($userId, $financialYear, $declarations);
            echo json_encode($result);
        } catch (\Throwable $e) {
            error_log('saveInvestmentDeclaration error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to save declarations']);
        }
    }

    public function uploadInvestmentProof() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }

            $financialYear = (int)($_POST['financial_year'] ?? date('Y'));
            $section = $_POST['section'] ?? '';

            if (!$section || empty($_FILES['proof_file']['tmp_name'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Section and file required']);
                return;
            }

            $service = new \App\Services\EmployeeSelfServiceService();
            $result = $service->uploadInvestmentProof($userId, $financialYear, $section, $_FILES['proof_file']);
            echo json_encode($result);
        } catch (\Throwable $e) {
            error_log('uploadInvestmentProof error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to upload proof']);
        }
    }

    public function getForm16List() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }

            $service = new \App\Services\EmployeeSelfServiceService();
            $form16List = $service->getForm16List($userId);

            echo json_encode(['success' => true, 'data' => $form16List]);
        } catch (\Throwable $e) {
            error_log('getForm16List error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to fetch Form 16 list']);
        }
    }

    public function generateForm16() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }

            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $financialYear = (int)($input['financial_year'] ?? 0);
            if (!$financialYear) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'Financial year required']); return; }

            $service = new \App\Services\EmployeeSelfServiceService();
            $result = $service->generateForm16($userId, $financialYear);
            echo json_encode($result);
        } catch (\Throwable $e) {
            error_log('generateForm16 error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to generate Form 16']);
        }
    }

    public function downloadForm16($financialYear) {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }

            $service = new \App\Services\EmployeeSelfServiceService();
            $result = $service->generateForm16($userId, (int)$financialYear);
            if (!$result['success']) { http_response_code(404); echo json_encode($result); return; }

            // Return download URL
            echo json_encode(['success' => true, 'download_url' => BASE_URL . '/employee/self-service/form16/download/' . $financialYear]);
        } catch (\Throwable $e) {
            error_log('downloadForm16 error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to download Form 16']);
        }
    }

    public function getPayslips() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }

            $limit = (int)($_GET['limit'] ?? 24);
            $service = new \App\Services\EmployeeSelfServiceService();
            $payslips = $service->getPayslipHistory($userId, $limit);

            echo json_encode(['success' => true, 'data' => $payslips]);
        } catch (\Throwable $e) {
            error_log('getPayslips error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to fetch payslips']);
        }
    }

    public function downloadPayslip($id) {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }

            $service = new \App\Services\EmployeeSelfServiceService();
            $pdfPath = $service->getPayslipPdf((int)$id);

            if (!$pdfPath || !file_exists(APP_PATH . '/' . $pdfPath)) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Payslip PDF not found']);
                return;
            }

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="Payslip_' . $id . '.pdf"');
            header('Content-Length: ' . filesize(APP_PATH . '/' . $pdfPath));
            readfile(APP_PATH . '/' . $pdfPath);
            exit;
        } catch (\Throwable $e) {
            error_log('downloadPayslip error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to download payslip']);
        }
    }

    public function getLeaveBalances() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }

            $year = (int)($_GET['year'] ?? date('Y'));
            $service = new \App\Services\EmployeeSelfServiceService();
            $balances = $service->getLeaveBalances($userId, $year);

            echo json_encode(['success' => true, 'data' => $balances]);
        } catch (\Throwable $e) {
            error_log('getLeaveBalances error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to fetch leave balances']);
        }
    }

    public function applyLeave() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }

            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $service = new \App\Services\EmployeeSelfServiceService();
            $result = $service->applyLeave($userId, $input);
            echo json_encode($result);
        } catch (\Throwable $e) {
            error_log('applyLeave error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to apply leave']);
        }
    }

    public function getLeaveHistory() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }

            $limit = (int)($_GET['limit'] ?? 50);
            $service = new \App\Services\EmployeeSelfServiceService();
            $history = $service->getLeaveHistory($userId, $limit);

            echo json_encode(['success' => true, 'data' => $history]);
        } catch (\Throwable $e) {
            error_log('getLeaveHistory error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to fetch leave history']);
        }
    }

    public function getReimbursements() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }

            $limit = (int)($_GET['limit'] ?? 50);
            $service = new \App\Services\EmployeeSelfServiceService();
            $reimbursements = $service->getReimbursementHistory($userId, $limit);

            // Claim types for dropdown
            $claimTypes = [
                'medical' => 'Medical Reimbursement',
                'lta' => 'Leave Travel Allowance (LTA)',
                'fuel' => 'Fuel / Conveyance',
                'phone' => 'Phone / Mobile',
                'internet' => 'Internet / Broadband',
                'books' => 'Books & Periodicals',
                'training' => 'Training & Development',
                'other' => 'Other',
            ];

            echo json_encode(['success' => true, 'data' => $reimbursements, 'claim_types' => $claimTypes]);
        } catch (\Throwable $e) {
            error_log('getReimbursements error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to fetch reimbursements']);
        }
    }

    public function submitReimbursement() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }

            // Handle multipart/form-data
            $data = $_POST;
            $data['receipt_file'] = $_FILES['receipt_file'] ?? null;

            $service = new \App\Services\EmployeeSelfServiceService();
            $result = $service->submitReimbursement($userId, $data);
            echo json_encode($result);
        } catch (\Throwable $e) {
            error_log('submitReimbursement error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to submit reimbursement']);
        }
    }

    public function getProfile() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }

            $stmt = $this->db->prepare("SELECT u.id, u.name, u.email, u.phone, u.role, u.date_of_birth, u.address, u.emergency_contact, u.pan_number, u.aadhaar_number, u.bank_account, u.bank_ifsc, e.employee_code, e.designation, e.department, e.joining_date, e.status as employment_status FROM users u LEFT JOIN employees e ON e.user_id = u.id WHERE u.id = ? LIMIT 1");
            $stmt->execute([$userId]);
            $profile = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

            echo json_encode(['success' => true, 'data' => $profile]);
        } catch (\Throwable $e) {
            error_log('getProfile error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to fetch profile']);
        }
    }

    public function getLeaveTypes() {
        $this->setCorsHeaders();
        try {
            $stmt = $this->db->prepare("SELECT id, name, code, days_per_year, color, description FROM leave_types WHERE status = 'active' ORDER BY name");
            $stmt->execute();
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        } catch (\Throwable $e) {
            error_log('getLeaveTypes error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to fetch leave types']);
        }
    }

    public function getSelfServiceDashboard() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }

            $service = new \App\Services\EmployeeSelfServiceService();
            $year = (int)date('Y');
            $month = date('Y-m');
            echo json_encode(['success' => true, 'data' => [
                'tax_regime' => $service->getTaxRegime($userId, $year),
                'leave_balances' => $service->getLeaveBalances($userId, $year),
                'payslips' => $service->getPayslipHistory($userId, 6),
                'reimbursements' => $service->getReimbursementHistory($userId, 5),
                'attendance_stats' => $service->getAttendanceStats($userId, $month),
            ]]);
        } catch (\Throwable $e) {
            error_log('getSelfServiceDashboard error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to fetch dashboard']);
        }
    }

    public function updateProfile() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }

            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $service = new \App\Services\EmployeeSelfServiceService();
            $result = $service->updateProfile($userId, $input);
            echo json_encode($result);
        } catch (\Throwable $e) {
            error_log('updateProfile error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to update profile']);
        }
    }

    public function changePassword() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }

            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $currentPassword = $input['current_password'] ?? '';
            $newPassword = $input['new_password'] ?? '';

            $service = new \App\Services\EmployeeSelfServiceService();
            $result = $service->changePassword($userId, $currentPassword, $newPassword);
            echo json_encode($result);
        } catch (\Throwable $e) {
            error_log('changePassword error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to change password']);
        }
    }

    public function getAttendance() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }

            $month = $_GET['month'] ?? date('Y-m');
            $service = new \App\Services\EmployeeSelfServiceService();
            $attendance = $service->getAttendance($userId, $month);

            echo json_encode(['success' => true, 'data' => $attendance]);
        } catch (\Throwable $e) {
            error_log('getAttendance error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to fetch attendance']);
        }
    }

    public function getAttendanceStats() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }

            $month = $_GET['month'] ?? date('Y-m');
            $service = new \App\Services\EmployeeSelfServiceService();
            $stats = $service->getAttendanceStats($userId, $month);

            echo json_encode(['success' => true, 'data' => $stats]);
        } catch (\Throwable $e) {
            error_log('getAttendanceStats error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to fetch attendance stats']);
        }
    }

    // ============================================================
    // GRATUITY CALCULATOR
    // ============================================================

    public function gratuityCalculator() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }

            $calculationDate = $_GET['calculation_date'] ?? date('Y-m-d');
            $service = new \App\Services\GratuityService();
            $result = $service->calculateGratuity($userId, $calculationDate);
            echo json_encode($result);
        } catch (\Throwable $e) {
            error_log('gratuityCalculator error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to calculate gratuity']);
        }
    }

    public function gratuityEligibilityReport() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }

            $service = new \App\Services\GratuityService();
            $report = $service->getLiabilityReport();
            echo json_encode(['success' => true, 'data' => $report]);
        } catch (\Throwable $e) {
            error_log('gratuityEligibilityReport error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to fetch report']);
        }
    }

    public function gratuityDetail($id) {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }

            $service = new \App\Services\GratuityService();
            $result = $service->calculateGratuity((int)$id, date('Y-m-d'));
            echo json_encode($result);
        } catch (\Throwable $e) {
            error_log('gratuityDetail error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to fetch detail']);
        }
    }

    // ============================================================
    // FULL & FINAL SETTLEMENT
    // ============================================================

    public function fnfCalculator() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }

            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $params = [
                'last_working_day' => $input['last_working_day'] ?? date('Y-m-d'),
                'resignation_date' => $input['resignation_date'] ?? date('Y-m-d'),
                'notice_period_days' => (int)($input['notice_period_days'] ?? 30),
                'notice_served_days' => (int)($input['notice_served_days'] ?? 0),
                'exit_type' => $input['exit_type'] ?? 'resignation',
            ];

            $service = new \App\Services\FullAndFinalSettlementService();
            $result = $service->calculateSettlement((int)$input['employee_id'] ?? 0, $params);
            echo json_encode($result);
        } catch (\Throwable $e) {
            error_log('fnfCalculator error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to calculate settlement']);
        }
    }

    public function fnfProcess() {
        $this->setCorsHeaders();
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $employeeId = (int)($input['employee_id'] ?? 0);
            $params = [
                'last_working_day' => $input['last_working_day'] ?? date('Y-m-d'),
                'resignation_date' => $input['resignation_date'] ?? date('Y-m-d'),
                'notice_period_days' => (int)($input['notice_period_days'] ?? 30),
                'notice_served_days' => (int)($input['notice_served_days'] ?? 0),
                'exit_type' => $input['exit_type'] ?? 'resignation',
            ];

            $service = new \App\Services\FullAndFinalSettlementService();
            $result = $service->processSettlement($employeeId, $params);
            echo json_encode($result);
        } catch (\Throwable $e) {
            error_log('fnfProcess error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to process settlement']);
        }
    }

    // ============================================================
    // SHIFT ROSTER & OVERTIME
    // ============================================================

    public function shiftTypes() {
        $this->setCorsHeaders();
        try {
            $service = new \App\Services\ShiftRosterService();
            $shiftTypes = $service->getShiftTypes();
            echo json_encode(['success' => true, 'data' => $shiftTypes]);
        } catch (\Throwable $e) {
            error_log('shiftTypes error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to fetch shift types']);
        }
    }

    public function getRoster() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }

            $startDate = $_GET['start_date'] ?? date('Y-m-d');
            $endDate = $_GET['end_date'] ?? date('Y-m-d', strtotime('+7 days'));
            $employeeId = (int)($_GET['employee_id'] ?? 0);

            $service = new \App\Services\ShiftRosterService();
            $roster = $service->getRoster($startDate, $endDate, $employeeId);
            echo json_encode(['success' => true, 'data' => $roster]);
        } catch (\Throwable $e) {
            error_log('getRoster error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to fetch roster']);
        }
    }

    public function assignShift() {
        $this->setCorsHeaders();
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $service = new \App\Services\ShiftRosterService();
            $result = $service->assignShift($input);
            echo json_encode($result);
        } catch (\Throwable $e) {
            error_log('assignShift error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to assign shift']);
        }
    }

    public function overtimeRequests() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }

            $status = $_GET['status'] ?? 'pending';
            $service = new \App\Services\ShiftRosterService();
            if ($status === 'pending') {
                $requests = $service->getPendingOvertime();
            } else {
                // Service works on employees.id, not users.id — resolve first
                $emp = $this->db->fetch("SELECT id FROM employees WHERE user_id=? LIMIT 1", [$userId]);
                $requests = $emp ? $service->getEmployeeOvertime((int)$emp['id']) : [];
            }
            echo json_encode(['success' => true, 'data' => $requests]);
        } catch (\Throwable $e) {
            error_log('overtimeRequests error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to fetch OT requests']);
        }
    }

    public function requestOvertime() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }

            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $emp = $this->db->fetch("SELECT id FROM employees WHERE user_id=? LIMIT 1", [$userId]);
            if (!$emp) { http_response_code(404); echo json_encode(['success'=>false,'error'=>'Employee record not found']); return; }

            $service = new \App\Services\ShiftRosterService();
            $result = $service->requestOvertime((int)$emp['id'], [
                'overtime_date' => $input['overtime_date'] ?? date('Y-m-d'),
                'hours' => (float)($input['hours'] ?? 0),
                'reason' => trim($input['reason'] ?? ''),
            ]);
            echo json_encode($result);
        } catch (\Throwable $e) {
            error_log('requestOvertime error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to request OT']);
        }
    }

    public function processOvertime($id) {
        $this->setCorsHeaders();
        try {
            $approverId = (int)($GLOBALS['api_user_id'] ?? 0);
            // Approval is an HR/admin action: employees must not self-approve via API.
            $approver = $this->db->fetch("SELECT role FROM users WHERE id=? LIMIT 1", [$approverId]);
            if (!$approver || !in_array($approver['role'] ?? '', ['admin', 'super_admin', 'manager'], true)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Only admin/manager can approve overtime']);
                return;
            }
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $action = $input['action'] ?? '';
            $remarks = $input['remarks'] ?? '';

            $service = new \App\Services\ShiftRosterService();
            $result = $service->processOvertime((int)$id, $action, $approverId, $remarks);
            echo json_encode($result);
        } catch (\Throwable $e) {
            error_log('processOvertime error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to process OT']);
        }
    }

    public function overtimeReports() {
        $this->setCorsHeaders();
        try {
            $startDate = $_GET['start_date'] ?? date('Y-m-01');
            $endDate = $_GET['end_date'] ?? date('Y-m-t');

            $service = new \App\Services\ShiftRosterService();
            $summary = $service->getOvertimeSummaryReport($startDate, $endDate);
            echo json_encode(['success' => true, 'data' => $summary]);
        } catch (\Throwable $e) {
            error_log('overtimeReports error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to fetch OT report']);
        }
    }

    public function shiftCoverage() {
        $this->setCorsHeaders();
        try {
            $startDate = $_GET['start_date'] ?? date('Y-m-01');
            $endDate = $_GET['end_date'] ?? date('Y-m-t');

            $service = new \App\Services\ShiftRosterService();
            $coverage = $service->getShiftCoverageReport($startDate, $endDate);
            echo json_encode(['success' => true, 'data' => $coverage]);
        } catch (\Throwable $e) {
            error_log('shiftCoverage error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to fetch coverage']);
        }
    }

    // ── CUSTOMER INVESTMENTS ──────────────────────────────────────
    public function investmentPlans() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Unauthorized']);
                return;
            }
            $service = new \App\Services\InvestmentService();
            $plans = $service->listPlans();
            echo json_encode(['success' => true, 'data' => $plans]);
        } catch (\Throwable $e) {
            error_log('investmentPlans error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to fetch plans']);
        }
    }

    public function userInvestments() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Unauthorized']);
                return;
            }
            $service = new \App\Services\InvestmentService();
            $investments = $service->getUserInvestments($userId);
            $stats = $service->getStats($userId);
            echo json_encode(['success' => true, 'data' => ['investments' => $investments, 'stats' => $stats]]);
        } catch (\Throwable $e) {
            error_log('userInvestments error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to fetch investments']);
        }
    }

    public function investmentCreate() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Unauthorized']);
                return;
            }
            $in = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $planId = (int)($in['plan_id'] ?? 0);
            $amount = (float)($in['amount'] ?? 0);
            if (!$planId || $amount <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'plan_id and amount are required']);
                return;
            }
            $service = new \App\Services\InvestmentService();
            $result = $service->invest($userId, $planId, [
                'amount' => $amount,
                'payment_mode' => $in['payment_mode'] ?? 'wallet',
                'referrer_user_id' => (int)($in['referrer_user_id'] ?? 0),
            ]);
            if (empty($result['success'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => $result['error'] ?? 'Investment failed']);
                return;
            }
            echo json_encode(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            error_log('investmentCreate error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Investment failed']);
        }
    }

    // ============================================================
    // MARKETPLACE MOBILE API
    // ============================================================

    /**
     * Get user's saved properties
     */
    public function getSavedProperties() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Unauthorized']);
                return;
            }

            $listingType = $_GET['type'] ?? '';
            $marketplace = new \App\Services\MarketplaceService();
            $properties = $marketplace->getSavedProperties($userId, $listingType);

            echo json_encode(['success' => true, 'data' => $properties]);
        } catch (\Throwable $e) {
            error_log('getSavedProperties error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to fetch saved properties']);
        }
    }

    /**
     * Save/unsave property to shortlist
     */
    public function saveProperty() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Unauthorized']);
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $propertyId = (int)($data['property_id'] ?? 0);
            $listingType = $data['listing_type'] ?? 'user';
            $notes = $data['notes'] ?? null;

            if ($propertyId <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid property']);
                return;
            }

            $marketplace = new \App\Services\MarketplaceService();
            $result = $marketplace->toggleSaveProperty($userId, $propertyId, $listingType, $notes);

            echo json_encode($result);
        } catch (\Throwable $e) {
            error_log('saveProperty error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to save property']);
        }
    }

    /**
     * Remove saved property
     */
    public function removeSavedProperty($id) {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Unauthorized']);
                return;
            }

            $savedId = (int)$id;
            if ($savedId <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid saved property ID']);
                return;
            }

            $tid = $this->tenantId();
            $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
            $tenantParams = $tid > 1 ? [$tid] : [];

            $stmt = $this->db->prepare("DELETE FROM user_saved_properties WHERE id = ? AND user_id = ?{$tenantWhere}");
            $stmt->execute(array_merge([$savedId, $userId], $tenantParams));

            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Property removed from saved list']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Saved property not found']);
            }
        } catch (\Throwable $e) {
            error_log('removeSavedProperty error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to remove saved property']);
        }
    }

    /**
     * Get user's followups
     */
    public function getFollowups() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Unauthorized']);
                return;
            }

            $marketplace = new \App\Services\MarketplaceService();
            $todayFollowups = $marketplace->getTodayFollowups($userId);
            $overdueFollowups = $marketplace->getOverdueFollowups($userId);

            echo json_encode([
                'success' => true,
                'data' => [
                    'today' => $todayFollowups,
                    'overdue' => $overdueFollowups,
                ]
            ]);
        } catch (\Throwable $e) {
            error_log('getFollowups error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to fetch followups']);
        }
    }

    /**
     * Complete a followup
     */
    public function completeFollowup($id) {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Unauthorized']);
                return;
            }

            $followupId = (int)$id;
            if ($followupId <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid followup ID']);
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $outcome = $data['outcome'] ?? '';
            $notes = $data['notes'] ?? '';

            if (empty($outcome)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Outcome is required']);
                return;
            }

            $marketplace = new \App\Services\MarketplaceService();
            $result = $marketplace->completeFollowup($followupId, $outcome, $notes, $userId);

            echo json_encode($result);
        } catch (\Throwable $e) {
            error_log('completeFollowup error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to complete followup']);
        }
    }

    /**
     * Get user transactions
     */
    public function getTransactions() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Unauthorized']);
                return;
            }

            $role = $_GET['role'] ?? 'all';
            $resellTxnService = new \App\Services\ResellTransactionService();
            $transactions = $resellTxnService->getUserTransactions($userId, $role);

            echo json_encode(['success' => true, 'data' => $transactions]);
        } catch (\Throwable $e) {
            error_log('getTransactions error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to fetch transactions']);
        }
    }

    /**
     * Initiate boost payment
     */
    public function initiateBoostPayment() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Unauthorized']);
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $propertyId = (int)($data['property_id'] ?? 0);
            $boostType = $data['boost_type'] ?? 'featured';
            $duration = (int)($data['duration'] ?? 7);

            if ($propertyId <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid property']);
                return;
            }

            $tid = $this->tenantId();
            $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
            $tenantParams = $tid > 1 ? [$tid] : [];

            // Verify property ownership
            $stmt = $this->db->prepare("SELECT id, user_id FROM user_properties WHERE id = ? AND user_id = ?{$tenantWhere}");
            $stmt->execute(array_merge([$data['property_id'], $userId], $tenantParams));
            $property = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$property) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Property not found or not owned by you']);
                return;
            }

            $boostAmount = match($boostType) {
                'featured' => 499,
                'urgent' => 299,
                'premium' => 999,
                default => 499,
            };

            $service = new \App\Services\Gateway\RazorpayService();
            $resp = $service->createOrder($boostAmount, 'INR', 'BOOST_' . $propertyId . '_' . time(), [
                'property_id' => $propertyId,
                'user_id' => $userId,
                'boost_type' => $boostType,
                'duration' => $duration,
                'description' => "Property boost: {$boostType} for {$duration} days",
            ]);

            if (!$resp['success']) {
                echo json_encode(['success' => false, 'error' => $resp['error'] ?? 'Failed to create payment order']);
                return;
            }

            echo json_encode([
                'success' => true,
                'order_id' => $resp['data']['id'],
                'amount_paise' => $resp['data']['amount'],
                'amount' => $boostAmount,
                'currency' => $resp['data']['currency'] ?? 'INR',
                'key_id' => $service->getKeyId(),
                'property_id' => $propertyId,
                'boost_type' => $boostType,
                'duration' => $duration,
            ]);

        } catch (\Throwable $e) {
            error_log("initiateBoostPayment error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to initiate payment']);
        }
    }

    /**
     * Verify boost payment
     */
    public function verifyBoostPayment() {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Unauthorized']);
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $orderId = $data['razorpay_order_id'] ?? '';
            $paymentId = $data['razorpay_payment_id'] ?? '';
            $signature = $data['razorpay_signature'] ?? '';

            if (!$orderId || !$paymentId || !$signature) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Missing payment parameters']);
                return;
            }

            $service = new \App\Services\Gateway\RazorpayService();

            if (!$service->verifyPaymentSignature($orderId, $paymentId, $signature)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid payment signature']);
                return;
            }

            $tid = $this->tenantId();
            $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
            $tenantParams = $tid > 1 ? [$tid] : [];

            // Fetch order details from Razorpay to get notes
            $orderResp = $service->fetchOrder($orderId);
            if (!$orderResp['success'] || !isset($orderResp['data']['notes'])) {
                echo json_encode(['success' => false, 'error' => 'Failed to fetch order details']);
                return;
            }

            $notes = $orderResp['data']['notes'] ?? [];
            $propertyId = (int)($notes['property_id'] ?? 0);
            $boostType = $notes['boost_type'] ?? 'featured';
            $duration = (int)($notes['duration'] ?? 7);

            if ($propertyId <= 0) {
                echo json_encode(['success' => false, 'error' => 'Invalid property in payment order']);
                return;
            }

            // Apply boost via shared service (deduplicated logic)
            $marketplace = new \App\Services\MarketplaceService();
            $result = $marketplace->applyBoostAfterPayment($propertyId, $userId, $boostType, $duration, $paymentId, $orderId);

            if ($result['success']) {
                echo json_encode(['success' => true, 'message' => 'Property boosted successfully!', 'payment_id' => $paymentId]);
                return;
            }

            echo json_encode(['success' => false, 'error' => $result['message'] ?? 'Failed to apply boost']);

        } catch (\Throwable $e) {
            error_log("verifyBoostPayment error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Payment verification failed']);
        }
    }
}
