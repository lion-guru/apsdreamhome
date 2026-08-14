<?php
/**
 * Digital Booking Controller
 * Handles customer-facing digital booking flow with legal docs & e-signature
 */

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Services\Sales\BookingLifecycleService;
use App\Services\Legal\LegalDocumentService;
use Exception;

class DigitalBookingController extends BaseController
{
    use \App\Traits\TenantAwareTrait;

    /** @var BookingLifecycleService */
    protected $bookingService;
    
    /** @var LegalDocumentService */
    protected $legalService;

    public function __construct()
    {
        parent::__construct();
        $this->bookingService = new BookingLifecycleService();
        $this->legalService = new LegalDocumentService();
    }

    /**
     * Digital booking page - shared link for customer
     * GET /booking/digital/{bookingNumber}
     */
    public function show($bookingNumber)
    {
        $this->requireLogin();
        
        $booking = $this->getBookingByNumber($bookingNumber);
        if (!$booking) {
            $this->setFlash('error', 'Booking not found');
            return $this->redirect('/');
        }
        
        // Verify customer owns this booking
        if ((int)($booking['customer_id'] ?? 0) !== (int)($_SESSION['user_id'] ?? 0)) {
            $this->setFlash('error', 'Unauthorized access');
            return $this->redirect('/user/dashboard');
        }
        
        // Get legal documents for this booking
        $documents = $this->legalService->getDocuments([
            'entity_type' => 'booking',
            'entity_id' => $booking['id'],
        ]);
        
        // Get payment schedule
        $schedule = $this->bookingService->getPaymentSchedule($booking['id']);
        
        // Get plot details
        $plot = $this->getPlotDetails($booking['plot_id']);
        
        $this->render('front/digital-booking', [
            'page_title'   => 'Booking #' . $booking['booking_number'],
            'page_heading' => 'Digital Booking â€” ' . htmlspecialchars($booking['booking_number']),
            'booking'      => $booking,
            'plot'         => $plot,
            'documents'    => $documents,
            'schedule'     => $schedule,
            'booking_token'=> $this->generateBookingToken($booking['id']),
        ]);
    }
    
    /**
     * View & sign a document
     * GET /booking/digital/{bookingNumber}/document/{docId}
     */
    public function viewDocument($bookingNumber, $docId)
    {
        $this->requireLogin();
        
        $booking = $this->getBookingByNumber($bookingNumber);
        if (!$booking || (int)($booking['customer_id'] ?? 0) !== (int)($_SESSION['user_id'] ?? 0)) {
            $this->setFlash('error', 'Unauthorized');
            return $this->redirect('/');
        }
        
        $doc = $this->legalService->getDocumentById((int)$docId);
        if (!$doc || (int)($doc['entity_id'] ?? 0) !== (int)$booking['id']) {
            $this->setFlash('error', 'Document not found');
            return $this->redirect("/booking/digital/{$bookingNumber}");
        }
        
        $this->render('front/digital-document', [
            'page_title'   => 'Document â€” ' . htmlspecialchars($doc['title']),
            'page_heading' => htmlspecialchars($doc['title']),
            'booking'      => $booking,
            'document'     => $doc,
        ]);
    }

    /**
     * Submit digital signature
     * POST /booking/digital/{bookingNumber}/document/{docId}/sign
     */
    public function signDocument($bookingNumber, $docId)
    {
        $this->requireLogin();
        $this->validateCsrfOrFail();
        
        $booking = $this->getBookingByNumber($bookingNumber);
        if (!$booking || (int)($booking['customer_id'] ?? 0) !== (int)($_SESSION['user_id'] ?? 0)) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
        }
        
        $doc = $this->legalService->getDocumentById((int)$docId);
        if (!$doc || (int)($doc['entity_id'] ?? 0) !== (int)$booking['id']) {
            return $this->jsonResponse(['success' => false, 'error' => 'Document not found'], 404);
        }
        
        try {
            $signatureData = [
                'signed_by'      => (int)$_SESSION['user_id'],
                'signed_at'      => date('Y-m-d H:i:s'),
                'ip_address'     => $_SERVER['REMOTE_ADDR'] ?? '',
                'user_agent'     => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'signature_type' => $_POST['signature_type'] ?? 'digital',
                'signature_data' => $_POST['signature_data'] ?? '',
                'video_consent'  => !empty($_POST['video_consent']),
                'video_url'      => $_POST['video_url'] ?? null,
            ];
            
            $result = $this->legalService->signDocument((int)$docId, $signatureData);
            
            if (!empty($result['success'])) {
                // Check if all required documents are signed
                $this->checkBookingCompletion($booking['id']);
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Document signed successfully',
                    'document' => $result['document'] ?? []
                ]);
            }
            
            return $this->jsonResponse(['success' => false, 'error' => $result['error'] ?? 'Signing failed'], 500);
        } catch (\Exception $e) {
            error_log('[DigitalBookingController::signDocument] ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'error' => 'Server error'], 500);
        }
    }

    /**
     * Record video consent
     * POST /booking/digital/{bookingNumber}/video-consent
     */
    public function recordVideoConsent($bookingNumber)
    {
        $this->requireLogin();
        $this->validateCsrfOrFail();
        
        $booking = $this->getBookingByNumber($bookingNumber);
        if (!$booking || (int)($booking['customer_id'] ?? 0) !== (int)($_SESSION['user_id'] ?? 0)) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
        }
        
        try {
            $videoUrl = $_POST['video_url'] ?? '';
            
            // Save video consent record
            $consentData = [
                'booking_id'      => $booking['id'],
                'customer_id'     => $booking['customer_id'],
                'video_url'       => $videoUrl,
                'recorded_at'     => date('Y-m-d H:i:s'),
                'ip_address'      => $_SERVER['REMOTE_ADDR'] ?? '',
                'terms_accepted'  => !empty($_POST['terms_accepted']),
                'privacy_accepted'=> !empty($_POST['privacy_accepted']),
            ];
            
            $db = \App\Core\Database\Database::getInstance();
            $pdo = $db->getConnection();
            
            $stmt = $pdo->prepare("
                INSERT INTO booking_video_consents 
                (booking_id, customer_id, video_url, recorded_at, ip_address, terms_accepted, privacy_accepted, tenant_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    video_url = VALUES(video_url),
                    terms_accepted = VALUES(terms_accepted),
                    privacy_accepted = VALUES(privacy_accepted),
                    recorded_at = VALUES(recorded_at)
            ");
            $stmt->execute([
                $consentData['booking_id'],
                $consentData['customer_id'],
                $consentData['video_url'],
                $consentData['recorded_at'],
                $consentData['ip_address'],
                $consentData['terms_accepted'] ? 1 : 0,
                $consentData['privacy_accepted'] ? 1 : 0,
                $this->tenantId()
            ]);
            
            return $this->jsonResponse(['success' => true, 'message' => 'Video consent recorded']);
        } catch (\Exception $e) {
            error_log('[DigitalBookingController::recordVideoConsent] ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'error' => 'Failed to record consent'], 500);
        }
    }

    /**
     * Generate EMI calculator preview
     * GET /booking/digital/{bookingNumber}/emi-preview
     */
    public function emiPreview($bookingNumber)
    {
        $this->requireLogin();
        
        $booking = $this->getBookingByNumber($bookingNumber);
        if (!$booking || (int)($booking['customer_id'] ?? 0) !== (int)($_SESSION['user_id'] ?? 0)) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
        }
        
        $tenure = (int)($_GET['tenure'] ?? 60);
        $rate = (float)($_GET['rate'] ?? 9.5);
        $type = $_GET['type'] ?? 'reducing';
        
        try {
            $result = $this->bookingService->generatePaymentSchedule($booking['id'], $tenure, $rate);
            
            if (!empty($result['success'])) {
                $summary = $this->calculateEMISummary($result['installments']);
                return $this->jsonResponse([
                    'success' => true,
                    'schedule' => $result['installments'],
                    'summary' => $summary,
                ]);
            }
            
            return $this->jsonResponse(['success' => false, 'error' => $result['error'] ?? 'Failed to generate schedule']);
        } catch (\Exception $e) {
            error_log('[DigitalBookingController::emiPreview] ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'error' => 'Server error'], 500);
        }
    }

    /**
     * Confirm EMI schedule and generate agreement
     * POST /booking/digital/{bookingNumber}/emi-confirm
     */
    public function emiConfirm($bookingNumber)
    {
        $this->requireLogin();
        $this->validateCsrfOrFail();
        
        $booking = $this->getBookingByNumber($bookingNumber);
        if (!$booking || (int)($booking['customer_id'] ?? 0) !== (int)($_SESSION['user_id'] ?? 0)) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
        }
        
        $tenure = (int)($_POST['tenure'] ?? 60);
        $rate = (float)($_POST['rate'] ?? 9.5);
        $type = $_POST['type'] ?? 'reducing';
        $moratorium = (int)($_POST['moratorium'] ?? 0);
        
        try {
            // Generate EMI schedule using BookingLifecycleService
            $result = $this->bookingService->generatePaymentSchedule($booking['id'], $tenure, $rate);
            
            if (!empty($result['success'])) {
                // Create EMI agreement record
                $agreementId = $this->createEMIAgreement($booking['id'], $result, $tenure, $rate, $type, $moratorium);
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'EMI schedule confirmed and agreement generated',
                    'agreement_id' => $agreementId,
                    'schedule' => $result['installments'],
                    'summary' => [
                        'emi' => $result['emi'],
                        'total_principal' => $result['total_principal'],
                        'total_interest' => $result['total_interest'],
                        'total_payable' => $result['total_payable'],
                    ],
                ]);
            }
            
            return $this->jsonResponse(['success' => false, 'error' => $result['error'] ?? 'Failed to generate schedule']);
        } catch (\Exception $e) {
            error_log('[DigitalBookingController::emiConfirm] ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'error' => 'Server error'], 500);
        }
    }

    /**
     * Download signed agreement PDF (legal documents)
     * GET /booking/digital/{bookingNumber}/download/{documentId}
     */
    public function downloadDocument($bookingNumber, $documentId)
    {
        $this->requireLogin();
        
        $booking = $this->getBookingByNumber($bookingNumber);
        if (!$booking || (int)($booking['customer_id'] ?? 0) !== (int)($_SESSION['user_id'] ?? 0)) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
        }
        
        $doc = $this->legalService->getDocumentById((int)$documentId);
        if (!$doc || (int)($doc['entity_id'] ?? 0) !== (int)$booking['id']) {
            return $this->jsonResponse(['success' => false, 'error' => 'Document not found'], 404);
        }
        
        // Check if user has signed this document
        $signature = $this->getDocumentSignature($booking['id'], $documentId);
        
        // Generate PDF with signatures
        $pdfContent = $this->generateSignedPDF($doc, $booking, $signature);
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $doc['document_number'] . '_signed.pdf"');
        echo $pdfContent;
        exit;
    }

    /**
     * Download EMI agreement PDF
     * GET /booking/digital/{bookingNumber}/download-emi/{agreementId}
     */
    public function downloadEMIAgreement($bookingNumber, $agreementId)
    {
        $this->requireLogin();
        
        $booking = $this->getBookingByNumber($bookingNumber);
        if (!$booking || (int)($booking['customer_id'] ?? 0) !== (int)($_SESSION['user_id'] ?? 0)) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
        }
        
        try {
            $db = \App\Core\Database\Database::getInstance();
            $pdo = $db->getConnection();
            
            // Check if it's an EMI agreement
            $stmt = $pdo->prepare("
                SELECT * FROM booking_emi_agreements 
                WHERE id = ? AND booking_id = ?
            ");
            $stmt->execute([$agreementId, $booking['id']]);
            $agreement = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$agreement) {
                return $this->jsonResponse(['success' => false, 'error' => 'Agreement not found'], 404);
            }
            
            // Get installments
            $stmt = $pdo->prepare("
                SELECT * FROM booking_emi_installments 
                WHERE agreement_id = ? ORDER BY installment_no
            ");
            $stmt->execute([$agreementId]);
            $installments = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Generate PDF
            $pdfHtml = $this->generateEMIAgreementPDF($booking, $agreement, $installments);
            
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="EMI_Agreement_' . $booking['booking_number'] . '.pdf"');
            echo $pdfHtml;
            exit;
            
        } catch (\Exception $e) {
            error_log('[DigitalBookingController::downloadEMIAgreement] ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'error' => 'Download failed'], 500);
        }
    }

    /**
     * Get booking documents for preview (AJAX)
     * GET /booking/digital/{bookingNumber}/documents
     */
    public function getDocuments($bookingNumber)
    {
        $this->requireLogin();
        
        $booking = $this->getBookingByNumber($bookingNumber);
        if (!$booking || (int)($booking['customer_id'] ?? 0) !== (int)($_SESSION['user_id'] ?? 0)) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
        }
        
        $documents = $this->legalService->getDocuments([
            'entity_type' => 'booking',
            'entity_id' => $booking['id'],
        ]);
        
        foreach ($documents as &$d) {
            $d['signed'] = (bool)$this->getDocumentSignature($booking['id'], $d['id']);
            $d['pdf_url'] = "/booking/digital/{$bookingNumber}/download/{$d['id']}";
        }
        
        return $this->jsonResponse(['documents' => $documents]);
    }

    /**
     * Success page after digital booking
     * GET /booking/digital/{bookingNumber}/success
     */
    public function success($bookingNumber)
    {
        $this->requireLogin();
        
        $booking = $this->getBookingByNumber($bookingNumber);
        if (!$booking || (int)($booking['customer_id'] ?? 0) !== (int)($_SESSION['user_id'] ?? 0)) {
            $this->setFlash('error', 'Booking not found');
            return $this->redirect('/');
        }
        
        $this->render('front/digital-success', [
            'page_title'   => 'Booking Complete â€” ' . htmlspecialchars($booking['booking_number']),
            'page_heading' => 'Booking Completed Successfully',
            'booking'      => $booking,
        ]);
    }

    /**
     * Finalize digital booking â€” POST /booking/digital/{bookingNumber}/submit
     */
    public function submit($bookingNumber)
    {
        $this->requireLogin();
        $this->validateCsrfOrFail();

        $booking = $this->getBookingByNumber($bookingNumber);
        if (!$booking || (int)($booking['customer_id'] ?? 0) !== (int)($_SESSION['user_id'] ?? 0)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Booking not found']);
            exit;
        }

        $bookingId = (int)$booking['id'];
        $token = $_POST['booking_token'] ?? '';
        if (!$this->verifyBookingToken($bookingId, $token)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid or expired token']);
            exit;
        }

        try {
            $db = \App\Core\Database\Database::getInstance();
            $pdo = $db->getConnection();

            // Mark terms accepted
            $pdo->prepare("UPDATE plot_bookings SET terms_accepted = 1, updated_at = NOW() WHERE id = ?")->execute([$bookingId]);

            // Check completion (signs docs + generates EMI schedule)
            $this->checkBookingCompletion($bookingId);

            echo json_encode([
                'success'  => true,
                'redirect' => '/booking/digital/' . urlencode($bookingNumber) . '/success',
            ]);
        } catch (\Throwable $e) {
            error_log('[DigitalBookingController::submit] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Server error']);
        }
        exit;
    }

    // ========== Helper Methods ==========

    /**
     * Validate CSRF token or exit with 403
     */
    protected function validateCsrfOrFail(): void
    {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
            exit;
        }
    }

    protected function getBookingByNumber(string $bookingNumber): ?array
    {
        try {
            $db = \App\Core\Database\Database::getInstance();
            $pdo = $db->getConnection();
            
            $stmt = $pdo->prepare("
                SELECT b.*, c.name as customer_name, c.email as customer_email, c.phone as customer_phone,
                       p.plot_code, p.plot_number, p.total_price as plot_price,
                       col.name as colony_name
                FROM plot_bookings b
                LEFT JOIN users c ON b.customer_id = c.id
                LEFT JOIN plots p ON b.plot_id = p.id
                LEFT JOIN colonies col ON p.colony_id = col.id
                WHERE b.booking_number = ?
            ");
            $stmt->execute([$bookingNumber]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            error_log('[DigitalBookingController::getBookingByNumber] ' . $e->getMessage());
            return null;
        }
    }

    protected function getPlotDetails(int $plotId): ?array
    {
        try {
            $db = \App\Core\Database\Database::getInstance();
            $pdo = $db->getConnection();
            
            $stmt = $pdo->prepare("SELECT * FROM plots WHERE id = ?");
            $stmt->execute([$plotId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function generateBookingToken(int $bookingId): string
    {
        $payload = [
            'booking_id' => $bookingId,
            'exp'        => time() + (7 * 24 * 3600),
            'iat'        => time()
        ];
        
        return base64_encode(json_encode($payload)) . '.' . hash_hmac('sha256', json_encode($payload), $this->getSecret());
    }

    protected function verifyBookingToken(int $bookingId, string $token): bool
    {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 2) return false;
            
            $payload = json_decode(base64_decode($parts[0]), true);
            if (!$payload || ($payload['booking_id'] ?? 0) !== $bookingId) return false;
            if (($payload['exp'] ?? 0) < time()) return false;
            
            $expectedSig = hash_hmac('sha256', json_encode($payload), $this->getSecret());
            return hash_equals($expectedSig, $parts[1]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function getSecret(): string
    {
        return $_ENV['JWT_SECRET'] ?? 'aps-dream-home-secret-key-2024';
    }

    protected function saveDocumentSignature(int $bookingId, array $docSig): void
    {
        if (empty($docSig['document_id']) || empty($docSig['signature_data'])) return;
        
        try {
            $db = \App\Core\Database\Database::getInstance();
            $pdo = $db->getConnection();
            
            $sql = "INSERT INTO booking_document_signatures 
                    (booking_id, document_id, signature_data, signed_at, ip_address, user_agent)
                    VALUES (?, ?, ?, NOW(), ?, ?)
                    ON DUPLICATE KEY UPDATE 
                        signature_data = VALUES(signature_data),
                        signed_at = VALUES(signed_at)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $bookingId,
                (int)$docSig['document_id'],
                $docSig['signature_data'],
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
        } catch (\Throwable $e) {
            error_log('[DigitalBookingController::saveDocumentSignature] ' . $e->getMessage());
        }
    }

    protected function getDocumentSignature(int $bookingId, int $documentId): ?array
    {
        try {
            $db = \App\Core\Database\Database::getInstance();
            $pdo = $db->getConnection();
            
            $stmt = $pdo->prepare("SELECT * FROM booking_document_signatures WHERE booking_id = ? AND document_id = ?");
            $stmt->execute([$bookingId, $documentId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function calculateEMISummary(array $schedule): array
    {
        $totalPrincipal = 0;
        $totalInterest = 0;
        $totalPayable = 0;
        
        foreach ($schedule as $inst) {
            $totalPrincipal += (float)($inst['principal_amount'] ?? 0);
            $totalInterest += (float)($inst['interest_amount'] ?? 0);
            $totalPayable += (float)($inst['total_amount'] ?? 0);
        }
        
        return [
            'total_principal'   => $totalPrincipal,
            'total_interest'    => $totalInterest,
            'total_payable'     => $totalPayable,
            'monthly_emi'       => count($schedule) > 0 ? round($totalPayable / count($schedule), 2) : 0,
            'installment_count' => count($schedule),
        ];
    }

    protected function checkBookingCompletion(int $bookingId): void
    {
        try {
            // Check if all documents for this booking are signed
            $db = \App\Core\Database\Database::getInstance();
            $pdo = $db->getConnection();
            
            // Total documents attached to this booking
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM legal_documents 
                WHERE entity_type = 'booking' AND entity_id = ?
            ");
            $stmt->execute([$bookingId]);
            $requiredCount = (int)$stmt->fetchColumn();
            
            if ($requiredCount === 0) return;
            
            // Signed count
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM booking_document_signatures bds
                JOIN legal_documents d ON bds.document_id = d.id
                WHERE bds.booking_id = ? AND d.entity_type = 'booking' AND d.entity_id = ?
            ");
            $stmt->execute([$bookingId, $bookingId]);
            $signedCount = (int)$stmt->fetchColumn();
            
            if ($signedCount >= $requiredCount) {
                // All required docs signed - update booking status
                $this->bookingService->updateBookingStatus($bookingId, 'agreement_signed', 'All required documents digitally signed');
                
                // Generate EMI schedule if not exists
                $schedule = $this->bookingService->getPaymentSchedule($bookingId);
                if (empty($schedule)) {
                    $tenure = 60; // default 5 years
                    $rate = 9.5;  // default 9.5%
                    $this->bookingService->generatePaymentSchedule($bookingId, $tenure, $rate);
                }
            }
        } catch (\Throwable $e) {
            error_log('[DigitalBookingController::checkBookingCompletion] ' . $e->getMessage());
        }
    }

    /**
     * Create EMI agreement record
     */
    protected function createEMIAgreement(int $bookingId, array $scheduleResult, int $tenure, float $rate, string $type, int $moratorium): int
    {
        $db = \App\Core\Database\Database::getInstance();
        $pdo = $db->getConnection();
        
        $principal = $scheduleResult['total_principal'] ?? 0;
        $totalInterest = $scheduleResult['total_interest'] ?? 0;
        $totalPayable = $scheduleResult['total_payable'] ?? 0;
        $emi = $scheduleResult['emi'] ?? 0;
        
        // Create agreement content
        $content = $this->generateEMIAgreementContent($bookingId, $principal, $rate, $tenure, $type, $moratorium, $emi, $totalInterest, $totalPayable);
        
        $pdo->beginTransaction();
        try {
            // Insert agreement
            $stmt = $pdo->prepare("
                INSERT INTO booking_emi_agreements 
                (booking_id, agreement_type, title, content, version, status, terms_accepted, privacy_accepted, created_at)
                VALUES (?, 'emi_agreement', 'EMI Repayment Agreement', ?, '1.0', 'pending_signature', 1, 1, NOW())
            ");
            $stmt->execute([$bookingId, $content]);
            $agreementId = (int)$pdo->lastInsertId();
            
            // Insert installments
            $stmt = $pdo->prepare("
                INSERT INTO booking_emi_installments 
                (agreement_id, installment_no, due_date, principal_amount, interest_amount, total_amount, balance_after, status, is_moratorium)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?)
            ");
            
            foreach ($scheduleResult['installments'] as $inst) {
                $stmt->execute([
                    $agreementId,
                    $inst['installment_no'],
                    $inst['due_date'],
                    $inst['principal_amount'],
                    $inst['interest_amount'],
                    $inst['total_amount'],
                    $inst['balance_after'],
                    $inst['is_moratorium'] ?? 0,
                ]);
            }
            
            $pdo->commit();
            return $agreementId;
        } catch (\Exception $e) {
            $pdo->rollBack();
            error_log('[createEMIAgreement] ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Generate EMI agreement content
     */
    protected function generateEMIAgreementContent(int $bookingId, float $principal, float $rate, int $tenure, string $type, int $moratorium, float $emi, float $totalInterest, float $totalPayable): string
    {
        // Get booking details
        $db = \App\Core\Database\Database::getInstance();
        $pdo = $db->getConnection();
        $stmt = $pdo->prepare("
            SELECT b.*, c.name as customer_name, c.email, c.phone, c.address,
                   p.plot_code, p.plot_number, col.name as colony_name
            FROM plot_bookings b
            LEFT JOIN users c ON b.customer_id = c.id
            LEFT JOIN plots p ON b.plot_id = p.id
            LEFT JOIN colonies col ON p.colony_id = col.id
            WHERE b.id = ?
        ");
        $stmt->execute([$bookingId]);
        $booking = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$booking) return '';
        
        $methodName = $type === 'flat' ? 'Flat Rate' : 'Reducing Balance';
        
        return "
EMI REPAYMENT AGREEMENT

Booking Number: {$booking['booking_number']}
Date: " . date('d M Y') . "

BORROWER DETAILS:
Name: {$booking['customer_name']}
Email: {$booking['email']}
Phone: {$booking['phone']}
Address: {$booking['address']}

PROPERTY DETAILS:
Plot: {$booking['plot_code']} ({$booking['plot_number']})
Colony: {$booking['colony_name']}
Booking Value: â‚¹" . number_format($booking['agreement_value'], 2) . "
Token Paid: â‚¹" . number_format($booking['booking_amount'], 2) . "
Principal for EMI: â‚¹" . number_format($principal, 2) . "

LOAN TERMS:
Interest Rate: {$rate}% per annum
Tenure: {$tenure} months
EMI Type: {$methodName}
Moratorium Period: {$moratorium} months
Monthly EMI: â‚¹" . number_format($emi, 2) . "
Total Principal: â‚¹" . number_format($principal, 2) . "
Total Interest: â‚¹" . number_format($totalInterest, 2) . "
Total Payable: â‚¹" . number_format($totalPayable, 2) . "

TERMS & CONDITIONS:
1. The borrower agrees to pay the EMI of â‚¹" . number_format($emi, 2) . " on or before the due date each month.
2. Interest is calculated on {$methodName} basis at {$rate}% per annum.
3. Late payment penalty: 2% per month on overdue amount after 5 days grace period.
4. Three consecutive missed EMIs will trigger loan recall and legal proceedings.
5. Prepayment allowed with 2% prepayment charge on outstanding principal after 12 months.
6. Property documents remain with APS Dream Home until full repayment.
7. Any dispute subject to Gorakhpur jurisdiction only.
8. The borrower confirms having read, understood, and accepted all terms.

BORROWER SIGNATURE: _________________________  DATE: ________________

AUTHORIZED SIGNATORY (APS DREAM HOME): _________________________  DATE: ________________
";
    }

    /**
     * Generate EMI Agreement PDF
     */
    protected function generateEMIAgreementPDF(array $booking, array $agreement, array $installments): string
    {
        $emi = 0;
        if (!empty($installments)) {
            $emi = $installments[0]['total_amount'];
        }
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta charset="UTF-8">
            <title>EMI Agreement - <?= htmlspecialchars($booking['booking_number'] ?? '') ?></title>
            <style>
                @page { margin: 20mm; }
                body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; line-height: 1.5; color: #333; }
                .header { text-align: center; border-bottom: 3px solid #2c3e50; padding-bottom: 15px; margin-bottom: 25px; }
                .header h1 { margin: 0 0 5px 0; color: #2c3e50; font-size: 22px; }
                .header p { margin: 3px 0; color: #666; font-size: 12px; }
                .section { margin-bottom: 20px; }
                .section-title { background: #2c3e50; color: white; padding: 8px 12px; font-weight: bold; font-size: 12px; margin-bottom: 10px; }
                .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 15px; }
                .info-item { background: #f8f9fa; padding: 8px 12px; border-radius: 4px; border: 1px solid #dee2e6; }
                .info-label { font-size: 10px; color: #666; text-transform: uppercase; letter-spacing: 0.5px; }
                .info-value { font-weight: bold; font-size: 13px; }
                .info-value.highlight { color: #2c3e50; font-size: 16px; }
                .info-value.danger { color: #dc3545; }
                .info-value.success { color: #28a745; }
                table { width: 100%; border-collapse: collapse; font-size: 10px; margin-top: 10px; }
                th, td { border: 1px solid #dee2e6; padding: 6px 8px; text-align: center; }
                th { background: #2c3e50; color: white; font-weight: 600; font-size: 9px; }
                tr:nth-child(even) td { background: #f8f9fa; }
                .text-right { text-align: right; }
                .text-left { text-align: left; }
                .summary-row { display: flex; justify-content: space-between; padding: 8px 0; border-top: 1px solid #dee2e6; }
                .summary-row.total { border-top: 2px solid #2c3e50; font-weight: bold; font-size: 12px; }
                .signature-section { margin-top: 40px; display: grid; grid-template-columns: 1fr 1fr; gap: 40px; }
                .signature-box { text-align: center; }
                .signature-line { border-top: 1px solid #333; width: 250px; margin: 30px auto 10px auto; }
                .footer { margin-top: 30px; padding-top: 15px; border-top: 1px solid #dee2e6; font-size: 9px; color: #666; text-align: center; }
                .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); 
                    font-size: 80px; color: rgba(0,0,0,0.05); z-index: -1; pointer-events: none; white-space: nowrap; }
            </style>
        </head>
        <body>
            <div class="watermark">APS DREAM HOME</div>
            
            <div class="header">
                <h1>EMI REPAYMENT AGREEMENT</h1>
                <p>Booking No: <strong><?= htmlspecialchars($booking['booking_number'] ?? '') ?></strong></p>
                <p>Date: <strong><?= date('d M Y') ?></strong></p>
            </div>
            
            <div class="section">
                <div class="section-title">BORROWER DETAILS</div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Customer Name</div>
                        <div class="info-value"><?= htmlspecialchars($booking['customer_name'] ?? '') ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Booking Number</div>
                        <div class="info-value"><?= htmlspecialchars($booking['booking_number'] ?? '') ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Plot</div>
                        <div class="info-value"><?= htmlspecialchars($booking['plot_code'] ?? '') ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Colony</div>
                        <div class="info-value"><?= htmlspecialchars($booking['colony_name'] ?? '') ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Phone</div>
                        <div class="info-value"><?= htmlspecialchars($booking['customer_phone'] ?? '') ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?= htmlspecialchars($booking['customer_email'] ?? '') ?></div>
                    </div>
                </div>
            </div>
            
            <div class="section">
                <div class="section-title">LOAN TERMS</div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Principal Amount</div>
                        <div class="info-value highlight">â‚¹<?= number_format($agreement['total_amount'] ?? 0, 2) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Booking Value</div>
                        <div class="info-value">â‚¹<?= number_format($booking['agreement_value'] ?? 0, 2) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Token Paid</div>
                        <div class="info-value">â‚¹<?= number_format($booking['booking_amount'] ?? 0, 2) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Interest Rate</div>
                        <div class="info-value highlight"><?= number_format($agreement['interest_rate'] ?? 0, 2) ?>% p.a.</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Tenure</div>
                        <div class="info-value highlight"><?= $agreement['tenure_months'] ?? 0 ?> months</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">EMI Type</div>
                        <div class="info-value highlight"><?= ucfirst(str_replace('_', ' ', $agreement['emi_type'] ?? 'reducing')) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Moratorium</div>
                        <div class="info-value"><?= $agreement['moratorium_months'] ?? 0 ?> months</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Monthly EMI</div>
                        <div class="info-value success highlight">â‚¹<?= number_format($emi, 2) ?></div>
                    </div>
                </div>
            </div>
            
            <div class="section">
                <div class="section-title">COST SUMMARY</div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Total Principal</div>
                        <div class="info-value">â‚¹<?= number_format($agreement['total_principal'] ?? 0, 2) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Total Interest</div>
                        <div class="info-value danger">â‚¹<?= number_format($agreement['total_interest'] ?? 0, 2) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Total Payable</div>
                        <div class="info-value highlight">â‚¹<?= number_format($agreement['total_payable'] ?? 0, 2) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Installments</div>
                        <div class="info-value"><?= count($installments) ?></div>
                    </div>
                </div>
            </div>
            
            <div class="section page-break">
                <div class="section-title">EMI REPAYMENT SCHEDULE</div>
                <table>
                    <thead>
                        <tr>
                            <th class="style-3061">#</th>
                            <th class="style-35967">Due Date</th>
                            <th class="style-23044">Principal</th>
                            <th class="style-23044">Interest</th>
                            <th class="style-23044">Total EMI</th>
                            <th class="style-23044">Balance</th>
                            <th class="style-40164">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $showCount = min(36, count($installments));
                        for ($i = 0; $i < $showCount; $i++): 
                            $inst = $installments[$i];
                        ?>
                        <tr>
                            <td><?= $inst['installment_no'] ?></td>
                            <td><?= date('d M Y', strtotime($inst['due_date'])) ?></td>
                            <td class="text-right">â‚¹<?= number_format($inst['principal_amount'], 2) ?></td>
                            <td class="text-right">â‚¹<?= number_format($inst['interest_amount'], 2) ?></td>
                            <td class="text-right"><strong>â‚¹<?= number_format($inst['total_amount'], 2) ?></strong></td>
                            <td class="text-right">â‚¹<?= number_format($inst['balance_after'], 2) ?></td>
                            <td><?= $inst['is_moratorium'] ? 'Moratorium' : ucfirst($inst['status']) ?></td>
                        </tr>
                        <?php endfor; ?>
                        <?php if (count($installments) > $showCount): ?>
                        <tr>
                            <td colspan="7" class="style-24766">
                                ... and <?= count($installments) - $showCount ?> more installments (full schedule attached)
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="section">
                <div class="section-title">TERMS & CONDITIONS</div>
                <ol class="style-65903">
                    <li>The borrower agrees to pay the EMI amount of <strong>â‚¹<?= number_format($emi, 2) ?></strong> on or before the due date each month.</li>
                    <li>Interest is calculated on <strong><?= ucfirst(str_replace('_', ' ', $agreement['emi_type'] ?? 'reducing')) ?></strong> basis at <strong><?= number_format($agreement['interest_rate'] ?? 0, 2) ?>% per annum</strong>.</li>
                    <li>Late payment penalty: <strong>2% per month</strong> on overdue amount after <strong>5 days grace period</strong>.</li>
                    <li>Three consecutive missed EMIs will trigger loan recall and legal proceedings.</li>
                    <li>Prepayment allowed with <strong>2% prepayment charge</strong> on outstanding principal after 12 months.</li>
                    <li>Property documents remain with APS Dream Home until full repayment.</li>
                    <li>Any dispute subject to Gorakhpur jurisdiction only.</li>
                    <li>The borrower confirms having read, understood, and accepted all terms.</li>
                </ol>
            </div>
            
            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div><strong>Borrower Signature</strong></div>
                    <div class="text-muted" class="style-67878"><?= htmlspecialchars($booking['customer_name'] ?? '') ?></div>
                    <div class="text-muted" class="style-67878">Date: _______________</div>
                </div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div><strong>Authorized Signatory</strong></div>
                    <div class="text-muted" class="style-67878">APS Dream Home</div>
                    <div class="text-muted" class="style-67878">Date: _______________</div>
                </div>
            </div>
            
            <div class="footer">
                <p><strong>APS Dream Home</strong> | Registered Office: Gorakhpur, Uttar Pradesh</p>
                <p>This agreement is digitally generated and legally valid under the Information Technology Act, 2000.</p>
                <p>Document ID: EMI-<?= date('Ymd') ?>-<?= strtoupper(substr($booking['booking_number'] ?? 'XXX', -6)) ?> | Generated: <?= date('d M Y H:i') ?></p>
            </div>
        </body>
        </html>
        <?php
        $html = ob_get_clean();
        
        if (class_exists('Dompdf\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            return $dompdf->output();
        }
        
        return $html;
    }

    protected function generateSignedPDF(array $document, array $booking, ?array $signature): string
    {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title><?= htmlspecialchars($document['name']) ?> - Signed</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
                .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #2c3e50; padding-bottom: 20px; }
                .header h1 { color: #2c3e50; margin: 0; }
                .booking-info { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
                .booking-info h3 { margin-top: 0; color: #2c3e50; }
                .info-row { display: flex; margin: 10px 0; }
                .info-label { font-weight: bold; width: 250px; color: #555; }
                .info-value { flex: 1; }
                .content { white-space: pre-wrap; font-family: Georgia, serif; }
                .signature-section { margin-top: 50px; page-break-inside: avoid; }
                .signature-box { border: 1px solid #ddd; padding: 20px; border-radius: 8px; background: #fafafa; }
                .signature-img { max-width: 300px; max-height: 100px; }
                .footer { margin-top: 50px; text-align: center; color: #888; font-size: 0.9em; }
                @media print { .no-print { display: none; } }
            </style>
        </head>
        <body>
            <div class="header">
                <h1><?= htmlspecialchars($document['name']) ?></h1>
                <p>Document #: <?= htmlspecialchars($document['document_number']) ?></p>
                <p>Category: <?= htmlspecialchars($document['category_name'] ?? '') ?></p>
            </div>
            
            <div class="booking-info">
                <h3>Booking Details</h3>
                <div class="info-row">
                    <span class="info-label">Booking Number:</span>
                    <span class="info-value"><?= htmlspecialchars($booking['booking_number'] ?? '') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Customer:</span>
                    <span class="info-value"><?= htmlspecialchars($booking['customer_name'] ?? '') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Plot:</span>
                    <span class="info-value"><?= htmlspecialchars($booking['plot_code'] ?? '') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Total Value:</span>
                    <span class="info-value">â‚¹<?= number_format((float)($booking['total_plot_value'] ?? 0), 2) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Agreement Value:</span>
                    <span class="info-value">â‚¹<?= number_format((float)($booking['agreement_value'] ?? 0), 2) ?></span>
                </div>
            </div>
            
            <div class="content">
                <?= nl2br(htmlspecialchars($document['content'] ?? '')) ?>
            </div>
            
            <?php if ($signature): ?>
            <div class="signature-section">
                <h3>Digital Signature</h3>
                <div class="signature-box">
                    <p><strong>Signed by:</strong> <?= htmlspecialchars($booking['customer_name'] ?? '') ?></p>
                    <p><strong>Date:</strong> <?= date('d M Y H:i', strtotime($signature['signed_at'])) ?></p>
                    <p><strong>IP:</strong> <?= htmlspecialchars($signature['ip_address'] ?? '') ?></p>
                    <?php if (!empty($signature['signature_data'])): ?>
                    <img src="data:image/png;base64,<?= htmlspecialchars($signature['signature_data']) ?>" class="signature-img" alt="Digital Signature">
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="footer">
                <p>APS Dream Home - Digital Agreement System</p>
                <p>Generated on <?= date('d M Y H:i') ?> | This is a digitally signed document</p>
            </div>
        </body>
        </html>
        <?php
        $html = ob_get_clean();
        
        // Try to use Dompdf if available
        if (class_exists('Dompdf\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            return $dompdf->output();
        }
        
        // Return HTML with print styles as fallback
        return $html;
    }
}