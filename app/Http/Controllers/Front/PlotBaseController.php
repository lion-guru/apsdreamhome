<?php
namespace App\Http\Controllers\Front;

use App\Core\Database\Database;
use App\Http\Controllers\BaseController;
use App\Services\Booking\BookingComplianceService;

/**
 * PlotBaseController — shared foundation for the public plot flow.
 *
 * Children:
 * - PlotIndexController   (index/colonyPlots/show/apiByColony)
 * - PlotBookingController (bookPlot/storeBooking/bookingConfirmation/receipt)
 * - PlotPaymentController (payBooking/processPayment)
 *
 * Security notes:
 * - CSRF IS enforced on POST endpoints (storeBooking/processPayment) by
 *   BaseController::__construct() + routes/router.php. Both booking forms
 *   render a csrf_token hidden field. These controllers intentionally do NOT
 *   override skipCsrfProtection() — POSTs without a valid token 403.
 * - Global POST/API rate limiting runs in BaseController::enforceRateLimit().
 *   processPayment() adds a stricter per-booking throttle on top (see
 *   throttlePayment()).
 */
abstract class PlotBaseController extends BaseController
{
    use \App\Traits\TenantAwareTrait;

    /** Token percentage of deal price due within TOKEN_DUE_DAYS. */
    public const TOKEN_PCT = 25;
    /** Days given to pay the token amount after booking. */
    public const TOKEN_DUE_DAYS = 15;
    /** Plot statuses that may be used as a public filter. */
    public const FILTERABLE_STATUSES = ['available', 'booked', 'sold', 'hold', 'reserved'];
    /** All valid plot statuses (mirrors plots.status enum). */
    public const VALID_PLOT_STATUSES = ['available', 'booked', 'sold', 'hold', 'reserved', 'under_construction'];
    /** Allowed token-payment modes (booking_pay.php select options). */
    public const ALLOWED_PAYMENT_MODES = ['online', 'bank_transfer', 'cash', 'cheque'];
    /** Allowed booking_type values (bookings.booking_type enum). */
    public const ALLOWED_BOOKING_TYPES = ['site_visit', 'online_consultation', 'direct_booking'];
    /** Pagination defaults/caps. */
    public const PLOTS_PER_PAGE = 24;
    public const API_PER_PAGE = 50;
    public const MAX_PER_PAGE = 100;
    /** Payment throttle: max attempts per window per booking+user. */
    public const PAY_MAX_ATTEMPTS = 10;
    public const PAY_WINDOW_SECONDS = 600;
    /** Input length caps. */
    public const MAX_NOTES_LEN = 2000;
    public const MAX_REFERENCE_LEN = 100;

    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
    }

    protected function requireCustomerLogin()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        $userType = $_SESSION['role'] ?? '';
        if ($userType !== '' && $userType !== 'customer') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    protected function getUser()
    {
        $tid = (int)$this->tenantId();
        $uid = (int)($_SESSION['user_id'] ?? 0);
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?" . ($tid > 1 ? ' AND tenant_id = ?' : ''));
        $stmt->execute($tid > 1 ? [$uid, $tid] : [$uid]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$user) {
            header('Location: ' . BASE_URL . '/user/logout');
            exit;
        }
        return $user;
    }

    /**
     * Single canonical booking-with-details fetch (plots + colony + district +
     * state). Always scoped to the owning customer AND tenant.
     */
    protected function getBookingWithDetails(int $bookingId, int $userId): ?array
    {
        $tid = (int)$this->tenantId();
        $sql = "
            SELECT b.*, p.plot_number, p.block, p.area_sqft, p.dimension_label, p.total_price as plot_price,
                   p.corner_plot, p.park_facing, c.name as colony_name,
                   d.name as district_name, s.name as state_name
            FROM bookings b
            LEFT JOIN plots p ON b.plot_id = p.id
            LEFT JOIN colonies c ON p.colony_id = c.id
            LEFT JOIN districts d ON c.district_id = d.id
            LEFT JOIN states s ON d.state_id = s.id
            WHERE b.id = ? AND b.customer_id = ?" . ($tid > 1 ? ' AND b.tenant_id = ?' : '');
        $params = $tid > 1 ? [$bookingId, $userId, $tid] : [$bookingId, $userId];
        $row = $this->db->fetchRow($sql, $params);
        return $row ?: null;
    }

    /**
     * Canonical EMI-schedule fetch for a booking. Never throws — returns [].
     */
    protected function getBookingEmis(int $bookingId): array
    {
        try {
            return $this->db->fetchAll(
                "SELECT * FROM booking_emis WHERE booking_id = ? ORDER BY installment_no",
                [$bookingId]
            ) ?: [];
        } catch (\Throwable $e) {
            error_log(static::class . '::getBookingEmis error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Token percentage (percent units, e.g. 25). Prefers the compliance
     * service single source of truth, falls back to class constant.
     */
    protected function tokenPct(): float
    {
        try {
            if (class_exists(BookingComplianceService::class)) {
                $svc = new BookingComplianceService();
                if (method_exists($svc, 'getTokenPercentage')) {
                    return (float)$svc->getTokenPercentage();
                }
            }
        } catch (\Throwable $e) {
            error_log(static::class . '::tokenPct fallback: ' . $e->getMessage());
        }
        return (float)static::TOKEN_PCT;
    }

    /**
     * Token amount for a deal price. Prefers the compliance service,
     * falls back to local math.
     */
    protected function calculateTokenAmount(float $dealPrice): float
    {
        try {
            if (class_exists(BookingComplianceService::class)) {
                $svc = new BookingComplianceService();
                if (method_exists($svc, 'calculateTokenAmount')) {
                    return (float)$svc->calculateTokenAmount($dealPrice);
                }
            }
        } catch (\Throwable $e) {
            error_log(static::class . '::calculateTokenAmount fallback: ' . $e->getMessage());
        }
        return round($dealPrice * ($this->tokenPct() / 100), 2);
    }

    /**
     * Cryptographically-unique booking number (booking_number has no UNIQUE
     * key, but random_bytes makes collisions practically impossible).
     */
    protected function generateBookingNumber(): string
    {
        return 'BK-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
    }

    /**
     * Ensure the idempotency column exists on bookings (idempotent guard —
     * adds it only when missing, like other session guards).
     * MUST be called OUTSIDE any transaction: ALTER TABLE implicitly commits.
     */
    protected function ensureIdempotencyColumn(): void
    {
        try {
            $col = $this->db->fetchRow("SHOW COLUMNS FROM bookings LIKE 'idempotency_key'");
            if (!$col) {
                $this->db->execute("ALTER TABLE bookings ADD COLUMN idempotency_key VARCHAR(64) NULL DEFAULT NULL, ADD INDEX idx_bookings_idem (idempotency_key)");
            }
        } catch (\Throwable $e) {
            // Fail open: callers fall back to transaction_id dedup.
            error_log(static::class . '::ensureIdempotencyColumn: ' . $e->getMessage());
        }
    }

    /**
     * Per-booking payment throttle (abuse prevention on top of the global
     * BaseController rate limiter). Fail-open on logging errors.
     */
    protected function throttlePayment(int $bookingId, int $userId): bool
    {
        try {
            $key = 'plotpay:' . $bookingId . ':' . $userId;
            $cutoff = date('Y-m-d H:i:s', time() - static::PAY_WINDOW_SECONDS);
            $count = (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM rate_limit_logs WHERE request_key = ? AND created_at >= ?",
                [$key, $cutoff]
            );
            if ($count >= static::PAY_MAX_ATTEMPTS) {
                return false;
            }
            $this->db->execute(
                "INSERT INTO rate_limit_logs (request_key, ip_address, user_agent, expires_at) VALUES (?, ?, ?, ?)",
                [$key, $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', $_SERVER['HTTP_USER_AGENT'] ?? '', date('Y-m-d H:i:s', time() + static::PAY_WINDOW_SECONDS)]
            );
        } catch (\Throwable $e) {
            error_log(static::class . '::throttlePayment: ' . $e->getMessage());
        }
        return true;
    }

    /**
     * Clamp ?page=/ ?per_page= into [page, perPage, offset].
     */
    protected function pagination(int $defaultPerPage): array
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = (int)($_GET['per_page'] ?? $defaultPerPage);
        if ($perPage < 1) $perPage = $defaultPerPage;
        if ($perPage > static::MAX_PER_PAGE) $perPage = static::MAX_PER_PAGE;
        return [$page, $perPage, ($page - 1) * $perPage];
    }

    /**
     * Sanitize a free-text reference (transaction ref). Strict length cap,
     * tags stripped.
     */
    protected function sanitizeReference(string $raw): string
    {
        $ref = trim(strip_tags($raw));
        if (function_exists('mb_substr')) {
            return mb_substr($ref, 0, static::MAX_REFERENCE_LEN);
        }
        return substr($ref, 0, static::MAX_REFERENCE_LEN);
    }
}
