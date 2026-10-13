<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Services\Legal\DigitalSignatureService;

class VerifyController extends BaseController
{
    private $digitalSignatureService;

    public function __construct()
    {
        parent::__construct();
        $this->digitalSignatureService = new \App\Services\Legal\DigitalSignatureService();
    }

    /**
     * Verify a document via QR code scan
     * GET /verify/document/{type}/{id}
     */
    public function verifyDocument(string $type, int $id): void
    {
        $service = new \App\Services\Legal\DigitalSignatureService();
        $result = $service->verifyDocument($type, $id);
        
        $this->render('pages/verify/result', [
            'page_title' => 'Document Verification - APS Dream Home',
            'result' => $result,
            'type' => $type,
            'id' => $id
        ]);
    }

    /**
     * Verify stamp paper
     * GET /verify/stamp-paper/{id}
     */
    public function verifyStampPaper(int $id): void
    {
        $service = new \App\Services\Legal\DigitalSignatureService();
        $result = $service->verifyStampPaper($id);
        
        $this->render('pages/verify/stamp_paper', [
            'page_title' => 'Stamp Paper Verification - APS Dream Home',
            'result' => $result,
            'id' => $id
        ]);
    }

    /**
     * Generate QR code for a document (AJAX endpoint)
     * POST /verify/generate-qr
     */
    public function generateQr(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Method not allowed', 405);
            return;
        }

        $type = $_POST['type'] ?? '';
        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0 || empty($type)) {
            $this->jsonError('Invalid parameters');
            return;
        }

        $service = new \App\Services\Legal\DigitalSignatureService();
        
        $result = match ($type) {
            'registry_deed', 'cancellation_deed', 'title_certificate', 
            'possession_certificate', 'allotment_letter' => $service->generateDeedVerificationQr($id, $type),
            'stamp_paper' => $service->generateStampPaperQr($id),
            default => ['success' => false, 'message' => 'Invalid document type']
        };

        $this->jsonResponse($result);
    }
}