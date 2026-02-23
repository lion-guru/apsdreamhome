<?php
namespace App\Http\Middleware;

class RateLimit
{
    protected $maxAttempts = 60;
    protected $decayMinutes = 1;

    public function handle($request, $next)
    {
        $key = $this->resolveRequestSignature($request);

        if ($this->tooManyAttempts($key, $this->maxAttempts)) {
            return $this->buildResponse($key);
        }

        $this->hit($key, $this->decayMinutes);

        $response = $next($request);

        return $this->addHeaders($response, $key);
    }

    protected function resolveRequestSignature($request)
    {
        return sha1(
            $request->getMethod() .
            '|' . $request->getPathInfo() .
            '|' . $request->getHost() .
            '|' . $this->getClientIp($request)
        );
    }

    protected function getClientIp($request)
    {
        $request = $_SERVER;
        $keys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];

        foreach ($keys as $key) {
            if (!empty($request[$key])) {
                $ips = explode(',', $request[$key]);
                return trim($ips[0]);
            }
        }

        return '127.0.0.1';
    }

    protected function tooManyAttempts($key, $maxAttempts)
    {
        return $this->attempts($key) >= $maxAttempts;
    }

    protected function attempts($key)
    {
        return (int) $_SESSION['rate_limit_' . $key] ?? 0;
    }

    protected function hit($key, $decayMinutes)
    {
        $key = 'rate_limit_' . $key;

        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['attempts' => 0, 'first_attempt' => time()];
        }

        $_SESSION[$key]['attempts']++;
    }

    protected function buildResponse($key)
    {
        header('HTTP/1.1 429 Too Many Requests');
        header('Content-Type: application/json');
        header('Retry-After: 60');

        echo json_encode([
            'error' => 'Too Many Requests',
            'message' => 'Rate limit exceeded. Please try again later.',
            'retry_after' => 60
        ]);
        exit;
    }

    protected function addHeaders($response, $key)
    {
        header('X-RateLimit-Limit: ' . $this->maxAttempts);
        header('X-RateLimit-Remaining: ' . max(0, $this->maxAttempts - $this->attempts($key)));
        return $response;
    }
}