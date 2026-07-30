<?php

namespace App\Services;

/**
 * MaintenanceService
 *
 * Toggles site-wide maintenance mode and manages the IP allow-list.
 * Backed by the `settings` table with two keys:
 *
 *   - maintenance_mode  => "1" or "0"
 *   - maintenance_ips   => comma-separated list of allowed IPs
 *
 * Plus an optional `maintenance_message` and `maintenance_eta` for the
 * public-facing page.
 */
class MaintenanceService
{
    private $pdo;

    public const KEY_ENABLED  = 'maintenance_mode';
    public const KEY_IPS      = 'maintenance_ips';
    public const KEY_MESSAGE  = 'maintenance_message';
    public const KEY_ETA      = 'maintenance_eta';

    public function __construct($db = null)
    {
        if ($db === null) {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
        } elseif (is_object($db) && method_exists($db, 'getPdo')) {
            $db = $db->getPdo();
        }
        $this->pdo = $db;
    }

    public function isEnabled(): bool
    {
        return $this->get(self::KEY_ENABLED, '0') === '1';
    }

    public function enable(string $message = '', string $eta = ''): bool
    {
        $this->set(self::KEY_ENABLED, '1');
        if ($message !== '') $this->set(self::KEY_MESSAGE, $message);
        if ($eta !== '')     $this->set(self::KEY_ETA, $eta);
        return true;
    }

    public function disable(): bool
    {
        $this->set(self::KEY_ENABLED, '0');
        return true;
    }

    public function getMessage(): string
    {
        $default = "We're performing scheduled maintenance. We'll be back soon. Thanks for your patience!";
        return $this->get(self::KEY_MESSAGE, $default);
    }

    public function getEta(): string
    {
        return $this->get(self::KEY_ETA, '');
    }

    /**
     * @return string[] list of IPs/ CIDR ranges
     */
    public function getAllowedIps(): array
    {
        $raw = trim($this->get(self::KEY_IPS, ''));
        if ($raw === '') return [];
        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }

    public function addAllowedIp(string $ip): void
    {
        $ips = $this->getAllowedIps();
        if (!in_array($ip, $ips, true)) {
            $ips[] = $ip;
            $this->set(self::KEY_IPS, implode(',', $ips));
        }
    }

    public function removeAllowedIp(string $ip): void
    {
        $ips = array_values(array_filter($this->getAllowedIps(), fn($i) => $i !== $ip));
        $this->set(self::KEY_IPS, implode(',', $ips));
    }

    /**
     * Should the current request be allowed through?
     * Returns true when:
     *  - maintenance is disabled
     *  - the user has an active admin session
     *  - the requester IP is in the allow-list
     */
    public function isRequestAllowed(): bool
    {
        if (!$this->isEnabled()) {
            return true;
        }
        if (!empty($_SESSION['admin_id']) || !empty($_SESSION['is_admin'])) {
            return true;
        }
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        foreach ($this->getAllowedIps() as $allowed) {
            if ($this->ipMatches($ip, $allowed)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Supports exact match and /24 CIDR (a.b.c.0/24 -> matches a.b.c.x).
     */
    private function ipMatches(string $ip, string $allowed): bool
    {
        if (strpos($allowed, '/') === false) {
            return $ip === $allowed;
        }
        [$subnet, $bits] = explode('/', $allowed, 2);
        $bits = (int) $bits;
        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        if ($ipLong === false || $subnetLong === false) return false;
        $mask = $bits === 0 ? 0 : ((-1 << (32 - $bits)) & 0xFFFFFFFF);
        return ($ipLong & $mask) === ($subnetLong & $mask);
    }

    public function toggle(): bool
    {
        if ($this->isEnabled()) {
            $this->disable();
            return false;
        }
        $this->enable();
        return true;
    }

    // ---- low-level helpers ----

    private function get(string $key, string $default = ''): string
    {
        try {
            $st = $this->pdo->prepare("SELECT `value` FROM settings WHERE `key` = ? ORDER BY id DESC LIMIT 1");
            $st->execute([$key]);
            $v = $st->fetchColumn();
            return $v === false ? $default : (string) $v;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    private function set(string $key, string $value): void
    {
        try {
            // settings has no UNIQUE on `key`, so do UPDATE-then-INSERT pattern
            $st = $this->pdo->prepare("UPDATE settings SET `value` = ? WHERE `key` = ?");
            $st->execute([$value, $key]);
            if ($st->rowCount() === 0) {
                $st = $this->pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?)");
                $st->execute([$key, $value]);
            }
        } catch (\Throwable $e) {
        // table may be missing
        error_log($e->getMessage());
        }
    }
}
