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
                SELECT t.user_id, u.role, t.expires_at 
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
            // Attach user_id + role to globals for later use in controllers
            $GLOBALS['api_user_id'] = $result['user_id'];
            $GLOBALS['api_user_role'] = $result['role'] ?? 'customer';
            return true;
        }

        return false;
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
