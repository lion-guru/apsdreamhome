<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\AI\DocumentGeneratorService;

class DocumentGeneratorController extends AdminController
{
    private $docGenerator;

    public function __construct()
    {
        parent::__construct();
        $this->docGenerator = new DocumentGeneratorService();
    }

    /**
     * Generate Sale Agreement
     */
    public function generateAgreement()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $bookingId = (int)($input['booking_id'] ?? 0);
        $template = $input['template'] ?? 'standard';
        $generatedBy = (int)($input['generated_by'] ?? $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0);

        if (!$bookingId) {
            echo json_encode(['success' => false, 'error' => 'booking_id required']);
            exit;
        }

        $result = $this->docGenerator->generateSaleAgreement([
            'booking_id' => $bookingId,
            'template' => $template,
            'generated_by' => $generatedBy
        ]);

        echo json_encode($result);
        exit;
    }

    /**
     * Generate Allotment Letter
     */
    public function generateAllotmentLetter()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $bookingId = (int)($input['booking_id'] ?? 0);
        $generatedBy = (int)($input['generated_by'] ?? $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0);

        if (!$bookingId) {
            echo json_encode(['success' => false, 'error' => 'booking_id required']);
            exit;
        }

        $result = $this->docGenerator->generateAllotmentLetter([
            'booking_id' => $bookingId,
            'generated_by' => $generatedBy
        ]);

        echo json_encode($result);
        exit;
    }

    /**
     * Generate Receipt
     */
    public function generateReceipt()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $paymentId = (int)($input['payment_id'] ?? 0);
        $generatedBy = (int)($input['generated_by'] ?? $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0);

        if (!$paymentId) {
            echo json_encode(['success' => false, 'error' => 'payment_id required']);
            exit;
        }

        $result = $this->docGenerator->generateReceipt([
            'payment_id' => $paymentId,
            'generated_by' => $generatedBy
        ]);

        echo json_encode($result);
        exit;
    }

    /**
     * List generated documents
     */
    public function listDocuments()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');
        
        $bookingId = (int)($_GET['booking_id'] ?? 0);
        $docType = $_GET['document_type'] ?? '';
        $status = $_GET['status'] ?? '';

        $result = $this->docGenerator->getDocuments([
            'booking_id' => $bookingId ?: null,
            'document_type' => $docType ?: null,
            'status' => $status ?: null
        ]);

        echo json_encode($result);
        exit;
    }
}