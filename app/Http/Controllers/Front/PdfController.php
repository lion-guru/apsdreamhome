<?php
/**
 * PdfController — Download + admin UI for the PDF service.
 *
 * Routes:
 *   GET  /pdf/download/{type}/{id}    - Public, served with HMAC-signed token
 *   GET  /admin/pdfs                  - Admin: stats + recent files
 *   POST /admin/pdfs/generate         - Admin: generate a PDF on demand
 *   GET  /admin/pdfs/view/{type}/{id} - Admin: open a PDF inline
 */
namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Services\Pdf\PdfService;
use App\Traits\TenantAwareTrait;

class PdfController extends BaseController
{
    use TenantAwareTrait;
    /**
     * Public download endpoint.
     *
     * URL: /pdf/download/{type}/{id}
     * The id can be either:
     *   - "123" (single id)
     *   - "123-456" (booking_id-payment_id for receipt)
     *
     * Optional: ?token=<hmac>  (only required for non-admin users)
     */
    public function download($type, $id)
    {
        $type = preg_replace('/[^a-z_]/', '', strtolower($type));
        if (!in_array($type, PdfService::ALL_TYPES, true)) {
            http_response_code(400);
            echo "Unknown PDF type: " . htmlspecialchars($type);
            return;
        }

        // Token check (skipped for admins)
        if (!$this->isAdmin()) {
            $token = $_GET['token'] ?? '';
            $expected = $this->sign($type, $id);
            if (!hash_equals($expected, $token)) {
                http_response_code(403);
                echo "Forbidden";
                return;
            }
            // Token TTL: 1 hour
            $ts = (int)($_GET['ts'] ?? 0);
            if ($ts > 0 && (time() - $ts) > 3600) {
                http_response_code(410);
                echo "Token expired";
                return;
            }
        }

        $subId = null;
        if (strpos($id, '-') !== false) {
            [$bookingId, $subId] = explode('-', $id, 2);
            $id = $bookingId;
        }

        $svc = $this->makeService();
        $result = $svc->generateWithType($type, (int)$id);
        if (!$result['success']) {
            http_response_code(404);
            echo "PDF not found: " . htmlspecialchars($result['error'] ?? 'unknown');
            return;
        }

        $path = $result['data']['path'];
        if (!is_file($path)) {
            http_response_code(500);
            echo "PDF generation failed";
            return;
        }

        // Stream
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        readfile($path);
        exit;
    }

    /**
     * Admin: list recent PDFs + stats.
     */
    public function adminIndex()
    {
        $this->requireAdmin();
        $svc = $this->makeService();
        $this->render('admin.pdfs.index', [
            'page_title'   => 'PDF Generator',
            'page_heading' => 'PDF Generator',
            'stats'        => $svc->getStats(),
            'recent'       => $svc->getRecent(30),
            'types'        => PdfService::ALL_TYPES,
            'current_page' => 'pdfs',
            'flash'        => [
                'success' => $this->getFlash('success'),
                'error'   => $this->getFlash('error'),
            ],
        ]);
    }

    /**
     * Admin: generate a PDF on demand and return its URL.
     */
    public function adminGenerate()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Method not allowed";
            return;
        }
        $type = preg_replace('/[^a-z_]/', '', strtolower($_POST['type'] ?? ''));
        $id   = (int)($_POST['id'] ?? 0);

        $svc = $this->makeService();
        $result = $svc->generateWithType($type, $id);

        header('Content-Type: application/json');
        echo json_encode($result);
    }

    /**
     * Admin: open a PDF inline in the browser.
     */
    public function adminView($type, $id)
    {
        $this->requireAdmin();
        $type = preg_replace('/[^a-z_]/', '', strtolower($type));
        if (!in_array($type, PdfService::ALL_TYPES, true)) {
            http_response_code(400);
            echo "Unknown type";
            return;
        }
        $svc = $this->makeService();
        $result = $svc->generateWithType($type, (int)$id);
        if (!$result['success']) {
            http_response_code(404);
            echo "PDF: " . htmlspecialchars($result['error'] ?? 'not found');
            return;
        }
        $path = $result['data']['path'];
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($path) . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    /**
     * Generate a signed token for public download (for use in email links etc).
     */
    public static function signToken($type, $id)
    {
        $secret = defined('PDF_DOWNLOAD_SECRET') ? PDF_DOWNLOAD_SECRET : 'apsdreamhome-pdf-secret';
        $ts = time();
        $sig = hash_hmac('sha256', "$type|$id|$ts", $secret);
        return ['ts' => $ts, 'token' => $sig];
    }

    protected function sign($type, $id)
    {
        $secret = defined('PDF_DOWNLOAD_SECRET') ? PDF_DOWNLOAD_SECRET : 'apsdreamhome-pdf-secret';
        $ts = (int)($_GET['ts'] ?? 0);
        return hash_hmac('sha256', "$type|$id|$ts", $secret);
    }

    protected function makeService(): PdfService
    {
        return new PdfService($this->db ?? null);
    }

    protected function isAdmin(): bool
    {
        return !empty($_SESSION['admin_id']) || !empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
}
