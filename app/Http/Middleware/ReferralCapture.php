<?php

namespace App\Http\Middleware;

use App\Core\Database\Database;
use App\Core\Http\Request;
use Closure;

/**
 * Capture referral code from GET parameter and store it in session.
 * If no code is supplied, fall back to the system root referral (APS0001).
 */
class ReferralCapture
{
    public function handle(Request $request, Closure $next)
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        // Only act on GET requests where a ref may be present
        $ref = trim((string) ($request->input('ref') ?? $_GET['ref'] ?? ''));
        if ($ref !== '') {
            $ref = substr(preg_replace('/[^A-Za-z0-9\-_]/', '', $ref), 0, 50);
            if ($ref !== '') {
                $_SESSION['referral_code'] = $ref;
                $_SESSION['aps_ref'] = $ref;
            }
        } elseif (empty($_SESSION['referral_code'])) {
            // Load root referral from DB lazily – cache in session
            $db = Database::getInstance();
            $row = $db->fetchOne("SELECT referral_code FROM users WHERE email = ? LIMIT 1", ['company@apsdreamhome.com']);
            if ($row && !empty($row['referral_code'])) {
                $_SESSION['referral_code'] = $row['referral_code'];
                if (empty($_SESSION['aps_ref'])) {
                    $_SESSION['aps_ref'] = $row['referral_code'];
                }
            }
        }

        return $next($request);
    }
}
