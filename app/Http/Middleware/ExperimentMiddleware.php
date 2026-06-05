<?php

namespace App\Http\Middleware;

use App\Services\Experimentation\ExperimentService;
use Throwable;

/**
 * ExperimentMiddleware — assigns A/B variants on every page load.
 *
 * Fetches all running experiments and bins the current user into each
 * one via ExperimentService::getVariant(). Stores assignments in
 * $_SESSION['experiments'] so views can render variant-specific UI.
 *
 * Usage (in BaseController::__construct or wherever middleware runs):
 *     ExperimentMiddleware::handle();
 *
 * View-side access:
 *     $variant = $_SESSION['experiments']['homepage_cta'] ?? null;
 *     if ($variant === 'treatment') { ... }
 */
class ExperimentMiddleware
{
    /**
     * Run experiment variant assignment. Safe to call multiple times per request
     * — does the work once per request via a static flag.
     */
    public static function handle(): array
    {
        static $hasRun = false;
        static $assigned = [];

        if ($hasRun) {
            return $assigned;
        }
        $hasRun = true;

        // CLI / no session? skip silently.
        if (PHP_SAPI === 'cli') {
            return [];
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            // BaseController should already have started the session; if not,
            // try once more without failing the request.
            @session_start();
        }

        if (!isset($_SESSION['experiments']) || !is_array($_SESSION['experiments'])) {
            $_SESSION['experiments'] = [];
        }

        // Determine user identifier (logged-in, admin, or anonymous visitor)
        $userId = self::resolveUserId();

        try {
            $svc = new ExperimentService();
            $running = $svc->getRunningExperiments();

            foreach ($running as $name) {
                // Skip if this user already has a sticky assignment for this experiment
                // within the session (extra safety; getVariant is deterministic anyway)
                if (isset($_SESSION['experiments'][$name])) {
                    $assigned[$name] = $_SESSION['experiments'][$name];
                    continue;
                }

                $variant = $svc->getVariant($name, $userId);
                if ($variant !== null) {
                    $_SESSION['experiments'][$name] = $variant;
                    $assigned[$name] = $variant;

                    // Auto-track a 'view' event the first time we assign — quietly,
                    // never let tracking failure break the page.
                    try {
                        $svc->trackEvent($name, $variant, 'view', $userId);
                    } catch (Throwable $e) {
                        // swallow
                    }
                }
            }
        } catch (Throwable $e) {
            // Database down? Don't break the page — just skip experiments this request.
            error_log('ExperimentMiddleware failed: ' . $e->getMessage());
        }

        return $assigned;
    }

    /**
     * Inject assignments into a $data array (for controller use).
     */
    public static function inject(array $data): array
    {
        $data['experiments'] = self::handle();
        return $data;
    }

    /**
     * Resolve a stable identifier for the current visitor.
     * Logged-in user > admin > anonymous visitor (session-derived).
     */
    protected static function resolveUserId(): int
    {
        if (!empty($_SESSION['user_id'])) {
            return (int) $_SESSION['user_id'];
        }
        if (!empty($_SESSION['admin_id'])) {
            // negative range avoids collision with regular user_id
            return -1 * (int) $_SESSION['admin_id'];
        }
        // Anonymous: derive a stable pseudo-ID from session_id so the same
        // visitor keeps the same variant for the duration of their session.
        if (!isset($_SESSION['_ab_visitor_id'])) {
            $sid = session_id() ?: bin2hex(random_bytes(8));
            $_SESSION['_ab_visitor_id'] = abs(crc32('anon:' . $sid)) % 1000000 + 1000000;
        }
        return (int) $_SESSION['_ab_visitor_id'];
    }
}
