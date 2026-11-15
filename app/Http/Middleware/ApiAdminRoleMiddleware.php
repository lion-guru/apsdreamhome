<?php
namespace App\Http\Middleware;

use App\Core\Http\Request;
use Closure;

/**
 * Admin-only API gate.
 *
 * ApiAuthMiddleware verifies that a Bearer token belongs to SOMEONE. That is
 * not an authorisation model: every customer account then reaches admin
 * dashboards, query analyzers, cash drawers, P&L and executive PII.
 *
 * This middleware enforces ApiAuthMiddleware's contract AND the same role list
 * used by the web admin gate (BaseController::ADMIN_ROLES). Non-admin tokens
 * receive a JSON 403; missing/invalid tokens receive 401 (via parent).
 */
class ApiAdminRoleMiddleware extends ApiAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $header = $request->header('authorization');

        if (!$header || strpos($header, 'Bearer ') !== 0) {
            return $this->unauthorized();
        }

        $token = substr($header, 7);

        if (!$this->isValidToken($token)) {
            return $this->unauthorized();
        }

        $role = $GLOBALS['api_user_role'] ?? 'customer';
        $adminRoles = \App\Http\Controllers\BaseController::ADMIN_ROLES;
        if (!in_array($role, $adminRoles, true)) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Admin access required.']);
            exit();
        }

        $this->setTenantContext();

        return $next($request);
    }
}
