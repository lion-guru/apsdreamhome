<?php

namespace App\Http\Controllers\Associate;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;
use App\Core\Middleware\TenantContext;

/**
 * LegalConsentController
 * Handles Associate Code of Conduct first-login agreement.
 *
 * The modal blocks dashboard access until legal_consent_accepted is non-NULL.
 */
class LegalConsentController extends BaseController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Require associate authentication
     */
    private function requireAuth()
    {
        @session_start();
        if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'associate') {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            exit;
        }
    }

    /**
     * AJAX endpoint: Check if associate has accepted the Code of Conduct.
     * GET /associate/legal-consent/status
     *
     * Returns JSON: { accepted: bool, accepted_at: string|null }
     */
    public function status()
    {
        $this->requireAuth();
        $userId = (int)$_SESSION['user_id'];

        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT legal_consent_accepted FROM associates WHERE user_id = ? LIMIT 1");
            $stmt->execute([$userId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$row) {
                return $this->jsonResponse(['accepted' => false, 'accepted_at' => null]);
            }

            $accepted = !empty($row['legal_consent_accepted']);
            return $this->jsonResponse([
                'accepted'    => $accepted,
                'accepted_at' => $row['legal_consent_accepted'] ?? null,
            ]);
        } catch (\Throwable $e) {
            error_log('[LegalConsentController::status] ' . $e->getMessage());
            return $this->jsonResponse(['accepted' => false, 'accepted_at' => null]);
        }
    }

    /**
     * AJAX endpoint: Record acceptance of the Code of Conduct.
     * POST /associate/legal-consent/accept
     *
     * Body: { csrf_token: string, consent_confirmed: '1' }
     *
     * Returns JSON: { success: bool, message: string, accepted_at: string }
     */
    public function accept()
    {
        $this->requireAuth();

        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid security token.'], 403);
        }

        $consentConfirmed = $_POST['consent_confirmed'] ?? '';
        if ($consentConfirmed !== '1') {
            return $this->jsonResponse(['success' => false, 'message' => 'You must explicitly confirm acceptance.']);
        }

        $userId = (int)$_SESSION['user_id'];
        $now = date('Y-m-d H:i:s');

        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();

            // Check if already accepted
            $stmt = $db->prepare("SELECT legal_consent_accepted FROM associates WHERE user_id = ? LIMIT 1");
            $stmt->execute([$userId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!empty($row['legal_consent_accepted'])) {
                return $this->jsonResponse([
                    'success'     => true,
                    'message'     => 'Code of Conduct already accepted.',
                    'accepted_at' => $row['legal_consent_accepted'],
                ]);
            }

            // Record acceptance
            $stmt = $db->prepare("UPDATE associates SET legal_consent_accepted = ? WHERE user_id = ?");
            $stmt->execute([$now, $userId]);

            // Audit log
            try {
                $stmt = $db->prepare("INSERT INTO user_activity_logs_unified (user_id, action, context, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->execute([
                    $userId,
                    'legal_consent_accepted',
                    json_encode(['type' => 'associate_code_of_conduct', 'version' => '1.0']),
                    $_SERVER['REMOTE_ADDR'] ?? '',
                    $_SERVER['HTTP_USER_AGENT'] ?? '',
                ]);
            } catch (\Throwable $e) {
                error_log('[LegalConsentController] audit log failed: ' . $e->getMessage());
            }

            return $this->jsonResponse([
                'success'     => true,
                'message'     => 'Code of Conduct accepted successfully. Welcome to the Associate Portal.',
                'accepted_at' => $now,
            ]);
        } catch (\Throwable $e) {
            error_log('[LegalConsentController::accept] ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Failed to record acceptance. Please try again.'], 500);
        }
    }
}
