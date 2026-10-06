<?php

namespace App\Http\Middleware;

use App\Core\Database;
use App\Core\Http\Request;
use App\Core\Http\Response;
use Closure;
use PDO;

class ApiAuthMiddleware
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Handle an incoming API request.
     *
     * @param  \App\Core\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
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

        // Set tenant context for this request
        $this->setTenantContext();

        return $next($request);
    }

    /**
     * Check if the token is valid and not expired
     */
    protected function isValidToken($token)
    {
        if (!$this->db) {
            return false;
        }

        try {
            $stmt = $this->db->prepare("
                SELECT t.user_id, u.role, u.tenant_id, t.expires_at 
                FROM api_tokens t
                JOIN users u ON u.id = t.user_id
                WHERE t.token = ? AND (t.expires_at IS NULL OR t.expires_at > NOW())
            ");
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
        }
        $stmt->execute([$token]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // Attach user_id + role + tenant_id to globals for later use in controllers
            $GLOBALS['api_user_id'] = $result['user_id'];
            $GLOBALS['api_user_role'] = $result['role'] ?? 'customer';
            $GLOBALS['api_user_tenant_id'] = (int)$result['tenant_id'];
            
            // Set tenant context for this request
            try {
                \App\Core\Middleware\TenantContext::setById((int)$result['tenant_id']);
            } catch (\Throwable $e) {
                error_log('ApiAuthMiddleware: Failed to set tenant context: ' . $e->getMessage());
            }
            
            return true;
        }

        return false;
    }

    /**
     * Set tenant context for the current request
     */
    protected function setTenantContext()
    {
        if (isset($GLOBALS['api_user_tenant_id']) && $GLOBALS['api_user_tenant_id'] > 1) {
            try {
                \App\Core\Middleware\TenantContext::setById($GLOBALS['api_user_tenant_id']);
            } catch (\Throwable $e) {
                error_log('ApiAuthMiddleware: Failed to set tenant context: ' . $e->getMessage());
            }
        }
    }

    /**
     * Return an unauthorized response
     */
    protected function unauthorized()
    {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Unauthorized access. Valid API token required.'
        ]);
        exit();
    }
}