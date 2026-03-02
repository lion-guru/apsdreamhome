<?php
namespace App\Http\Middleware;

use Closure;
use App\Core\Session\SessionManager;

class SessionSecurityMiddleware
{
    private $session;
    
    public function __construct()
    {
        $this->session = new SessionManager();
    }
    
    /**
     * Handle an incoming request
     */
    public function handle($request, Closure $next)
    {
        // Start session if not started
        if (!$this->session->isStarted()) {
            $this->session->start();
        }
        
        // Validate session security
        if (!$this->validateSessionSecurity($request)) {
            return $this->handleSessionSecurityViolation($request);
        }
        
        // Regenerate session ID periodically
        $this->regenerateSessionIfNeeded();
        
        // Update session activity
        $this->updateSessionActivity();
        
        $response = $next($request);
        
        // Add security headers
        $this->addSecurityHeaders($response);
        
        return $response;
    }
    
    /**
     * Validate session security
     */
    private function validateSessionSecurity($request)
    {
        // Check if session exists
        if (!$this->session->has('user_id')) {
            return true; // No session to validate
        }
        
        // Check session age
        $sessionAge = time() - $this->session->get('session_created_at', time());
        $maxSessionAge = config('session.max_lifetime', 7200); // 2 hours
        
        if ($sessionAge > $maxSessionAge) {
            return false;
        }
        
        // Check session IP address
        if ($this->session->has('session_ip')) {
            $currentIp = $request->getClientIp();
            $sessionIp = $this->session->get('session_ip');
            
            if ($currentIp !== $sessionIp) {
                // Log potential session hijacking
                $this->logSecurityEvent('session_ip_mismatch', [
                    'session_ip' => $sessionIp,
                    'current_ip' => $currentIp,
                    'user_id' => $this->session->get('user_id')
                ]);
                
                return false;
            }
        }
        
        // Check user agent
        if ($this->session->has('session_user_agent')) {
            $currentUserAgent = $request->getUserAgent();
            $sessionUserAgent = $this->session->get('session_user_agent');
            
            if ($currentUserAgent !== $sessionUserAgent) {
                $this->logSecurityEvent('session_user_agent_mismatch', [
                    'session_user_agent' => $sessionUserAgent,
                    'current_user_agent' => $currentUserAgent,
                    'user_id' => $this->session->get('user_id')
                ]);
                
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Handle session security violation
     */
    private function handleSessionSecurityViolation($request)
    {
        // Destroy current session
        $this->session->destroy();
        
        // Log security violation
        $this->logSecurityEvent('session_security_violation', [
            'ip' => $request->getClientIp(),
            'user_agent' => $request->getUserAgent()
        ]);
        
        // Redirect to login with security message
        return redirect('/login?security=session_expired');
    }
    
    /**
     * Regenerate session if needed
     */
    private function regenerateSessionIfNeeded()
    {
        // Regenerate every 30 minutes
        $lastRegeneration = $this->session->get('last_regeneration', 0);
        $regenerationInterval = 1800; // 30 minutes
        
        if (time() - $lastRegeneration > $regenerationInterval) {
            $this->session->regenerate(true);
            $this->session->set('last_regeneration', time());
        }
    }
    
    /**
     * Update session activity
     */
    private function updateSessionActivity()
    {
        $this->session->set('last_activity', time());
        
        // Update session in database
        if ($this->session->has('session_id')) {
            $this->updateSessionInDatabase();
        }
    }
    
    /**
     * Add security headers
     */
    private function addSecurityHeaders($response)
    {
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $response->headers->set('Content-Security-Policy', $this->getCSPHeader());
        
        return $response;
    }
    
    /**
     * Get CSP header
     */
    private function getCSPHeader()
    {
        return "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
               "font-src 'self' https://fonts.gstatic.com; " .
               "img-src 'self' data: https:; " .
               "connect-src 'self' https://api.apsdreamhome.com";
    }
    
    /**
     * Log security event
     */
    private function logSecurityEvent($event, $data = [])
    {
        $logData = [
            'event' => $event,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];
        
        // Log to file
        file_put_contents(
            BASE_PATH . '/logs/security_events.log',
            json_encode($logData) . PHP_EOL,
            FILE_APPEND
        );
        
        // Log to database
        $this->logSecurityEventToDatabase($logData);
    }
    
    /**
     * Update session in database
     */
    private function updateSessionInDatabase()
    {
        $sessionId = $this->session->get('session_id');
        $userId = $this->session->get('user_id');
        $lastActivity = date('Y-m-d H:i:s', $this->session->get('last_activity'));
        
        $sql = "UPDATE user_sessions 
                SET last_activity = ?, ip_address = ?, user_agent = ? 
                WHERE session_id = ? AND user_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $lastActivity,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $sessionId,
            $userId
        ]);
    }
    
    /**
     * Log security event to database
     */
    private function logSecurityEventToDatabase($logData)
    {
        $sql = "INSERT INTO security_events (event_type, event_data, ip_address, created_at) 
                VALUES (?, ?, ?, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $logData['event'],
            json_encode($logData['data']),
            $logData['ip']
        ]);
    }
}
