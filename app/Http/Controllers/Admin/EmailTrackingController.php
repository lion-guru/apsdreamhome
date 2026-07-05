<?php
namespace App\Http\Controllers\Admin;
use App\Services\EmailTrackingService;

class EmailTrackingController extends AdminController {
    public function stats() {
        $this->requireAdmin();
        $service = new EmailTrackingService();
        $days = (int)($_GET['days'] ?? 30);
        $overall = $service->getOverallStats($days);
        $daily = $service->getDailyStats($days);
        $topLinks = $service->getTopClickedLinks(20);
        return $this->render('admin/crm/email_tracking/stats', [
            'overall' => $overall, 'daily' => $daily, 'top_links' => $topLinks,
            'days' => $days, 'page_title' => 'Email Tracking'
        ]);
    }

    public function trackOpen($id) {
        $service = new EmailTrackingService();
        $recipient = $_GET['r'] ?? '';
        $service->trackOpen((int)$id, $recipient, $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '');
        header('Content-Type: image/gif');
        echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        exit;
    }

    public function trackClick($id) {
        $service = new EmailTrackingService();
        $recipient = $_GET['r'] ?? '';
        $url = $_GET['url'] ?? '/admin/crm';
        $service->trackClick((int)$id, $recipient, $url, $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '');
        header('Location: ' . $url);
        exit;
    }
}
