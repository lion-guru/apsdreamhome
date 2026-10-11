<?php

namespace App\Http\Controllers\Front;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;

/**
 * Associate branded mini-website / lead magnet (PRO-4).
 * Public landing per verified associate: GET /p/{code}.
 * Inquiry posts store a lead in `leads` (assigned to the associate).
 */
class AssociatePageController extends PageController
{
    /**
     * Resolve an active associate by users.referral_code OR
     * associates.referral_code. Returns null when unknown/inactive.
     */
    private function resolveAssociate(string $code): ?array
    {
        $code = trim($code);
        if ($code === '') return null;
        try {
            $db = Database::getInstance();
            $tid = TenantContext::getId();
            $tidSql = $tid > 1 ? " AND a.tenant_id = ?" : "";
            $params = [$code, $code];
            if ($tid > 1) $params[] = $tid;
            $row = $db->fetchOne(
                "SELECT a.id AS associate_id, a.user_id, a.level, a.total_sales, a.team_size,
                        a.referral_code AS assoc_code, a.status,
                        u.name AS user_name, u.phone AS user_phone, u.email AS user_email,
                        u.profile_image, u.referral_code AS user_code
                 FROM associates a
                 LEFT JOIN users u ON u.id = a.user_id
                 WHERE (a.referral_code = ? OR u.referral_code = ?)
                   AND a.status = 'active'" . $tidSql . "
                 LIMIT 1",
                $params
            );
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[AssociatePageController::resolveAssociate] ' . $e->getMessage());
            return null;
        }
    }

    private function liveColonies(): array
    {
        try {
            $db = Database::getInstance();
            return $db->fetchAll(
                "SELECT c.id, c.name, COUNT(p.id) AS avail
                 FROM colonies c
                 LEFT JOIN plots p ON p.colony_id = c.id AND p.status = 'available' AND p.is_active = 1
                 WHERE c.is_active = 1
                 GROUP BY c.id ORDER BY avail DESC LIMIT 6"
            ) ?: [];
        } catch (\Throwable $e) {
            error_log('[AssociatePageController::liveColonies] ' . $e->getMessage());
            return [];
        }
    }

    public function landing($code = null)
    {
        $assoc = $this->resolveAssociate((string)($code ?? ''));
        if (!$assoc) {
            return $this->render('pages/404', ['page_title' => 'Associate Not Found']);
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $name = $assoc['user_name'] ?: ($assoc['assoc_code'] ?? 'Associate');
        $this->render('pages/associate_site', [
            'page_title' => $name . ' — APS Dream Home Associate',
            'assoc' => $assoc,
            'display_name' => $name,
            'colonies' => $this->liveColonies(),
            'saved' => ($_GET['saved'] ?? '') === '1',
        ]);
    }

    public function inquire($code = null)
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            return $this->redirect('/p/' . urlencode((string)($code ?? '')));
        }
        $assoc = $this->resolveAssociate((string)($code ?? ''));
        if (!$assoc) {
            return $this->render('pages/404', ['page_title' => 'Associate Not Found']);
        }
        // CSRF: token embedded in the public form (guests hold sessions too).
        $token = (string)($_POST['csrf_token'] ?? '');
        if ($token === '' || empty($_SESSION['csrf_token']) || !hash_equals((string)$_SESSION['csrf_token'], $token)) {
            $_SESSION['flash_error'] = 'Security check failed. Please try again.';
            return $this->redirect('/p/' . urlencode((string)$code));
        }
        $name = trim((string)($_POST['name'] ?? ''));
        $phone = trim((string)($_POST['phone'] ?? ''));
        if ($name === '' || !preg_match('/^[6-9]\d{9}$/', preg_replace('/\D/', '', $phone))) {
            $_SESSION['flash_error'] = 'Please share your name and a valid 10-digit mobile number.';
            return $this->redirect('/p/' . urlencode((string)$code));
        }
        $phoneDigits = preg_replace('/\D/', '', $phone);
        try {
            $db = Database::getInstance();
            $tid = TenantContext::getId();
            $db->insert('leads', [
                'tenant_id' => $tid,
                'name' => mb_substr($name, 0, 100),
                'phone' => $phoneDigits,
                'source' => 'mini_website',
                'source_detail' => 'associate mini-site /p/' . $code,
                'referral_source' => $assoc['assoc_code'] ?: $assoc['user_code'],
                'assigned_to' => (int)$assoc['user_id'],
                'property_interest' => mb_substr(trim((string)($_POST['interest'] ?? '')), 0, 255) ?: null,
                'status' => 'new',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            error_log('[AssociatePageController::inquire] ' . $e->getMessage());
            $_SESSION['flash_error'] = 'Could not save your request. Please WhatsApp us directly.';
            return $this->redirect('/p/' . urlencode((string)$code));
        }
        return $this->redirect('/p/' . urlencode((string)$code) . '?saved=1');
    }
}
