<?php
/**
 * MLM Investment / Joining Package Engine
 *
 * Handles ₹1,000 joining package commission distribution up the sponsor chain.
 *
 * Payout Structure:
 *  - Direct sponsor bonus: configurable per package
 *  - Multi-level bonus: distributed via mlm_joining_packages.level_payout_json
 *  - Written to mlm_commission_ledger with commission_type='joining_package'
 *
 * Usage:
 *   $engine = new MlmInvestmentEngine();
 *   $result = $engine->processJoiningPackage($registrationId, $packageId, $payerUserId);
 */

namespace App\Services;

use PDO;
use Exception;
use \App\Traits\ServiceTenantTrait;

class MlmInvestmentEngine
{
    use \App\Traits\ServiceTenantTrait;

    /** @var PDO|null */
    private $pdo;

    /** @var MlmPolicyGuard|null */
    private $guard;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
        if (!$this->pdo) {
            try {
                $db = \App\Core\Database\Database::getInstance();
                $this->pdo = method_exists($db, 'getPdo') ? $db->getPdo() : $db;
            } catch (Exception $e) {
                throw new Exception('MlmInvestmentEngine requires database connection: ' . $e->getMessage());
            }
        }
        $this->guard = new MlmPolicyGuard($this->pdo);
    }

    /* ================================================================
       PUBLIC API
       ================================================================ */

    /**
     * Process a joining package purchase.
     *
     * 1. Records registration in mlm_associate_registrations
     * 2. Pays direct sponsor bonus
     * 3. Distributes level bonuses up the upline chain
     * 4. Returns ledger entries and summary
     *
     * @param int    $packageId  mlm_joining_packages.id
     * @param int    $payerUserId  users.id of the buyer
     * @param array  $context    Optional: payment_method, payment_reference
     * @return array{success: bool, registration_number: string, ledger_entries: int, total_distributed: float, details: array}
     */
    public function processJoiningPackage(int $packageId, int $payerUserId, array $context = []): array
    {
        try {
            $this->pdo->beginTransaction();

            // 1. Validate package exists and is active
            $package = $this->fetchPackage($packageId);
            if (!$package) {
                $this->pdo->rollBack();
                return ['success' => false, 'error' => 'Package not found or inactive'];
            }

            // 2. Validate payer is an associate
            $associate = $this->fetchAssociate($payerUserId);
            if (!$associate) {
                $this->pdo->rollBack();
                return ['success' => false, 'error' => 'User is not an associate'];
            }

            // 3. Check for duplicate registration
            $existing = $this->checkDuplicate($payerUserId, $packageId);
            if ($existing) {
                $this->pdo->rollBack();
                return ['success' => false, 'error' => 'User already has this package'];
            }

            // 4. Create registration record
            $regNumber = $this->generateRegistrationNumber();
            $this->createRegistration($regNumber, $payerUserId, $packageId, $context);

            // 5. Get upline chain (sponsor, sponsor's sponsor, ...)
            $upline = $this->getUplineChain($payerUserId, 6);

            // 6. Pay direct sponsor bonus (L1)
            $directBonus = (float) $package['direct_sponsor_bonus'];
            $ledgerEntries = [];
            $totalDistributed = 0.0;

            if ($directBonus > 0 && !empty($upline[0])) {
                $sponsorId = (int) $upline[0]['id'];
                $this->writeLedger(
                    $sponsorId,
                    $payerUserId,
                    (float) $package['price'],
                    0,
                    $directBonus,
                    'joining_package',
                    1,
                    0,
                    0,
                    "Direct sponsor bonus — package {$package['package_code']}, reg: {$regNumber}"
                );
                $ledgerEntries[] = [
                    'beneficiary' => $sponsorId,
                    'amount'      => $directBonus,
                    'level'       => 1,
                    'type'        => 'direct_sponsor',
                ];
                $totalDistributed += $directBonus;
            }

            // 7. Distribute level bonuses from level_payout_json
            $levelPayouts = json_decode($package['level_payout_json'], true) ?: [];
            foreach ($levelPayouts as $level => $amount) {
                $levelInt = (int) $level;
                $amountFloat = (float) $amount;
                if ($amountFloat <= 0) continue;

                // Upline is 0-indexed: level 2 → upline[1], level 3 → upline[2], ...
                $uplineIndex = $levelInt - 1;
                if (!isset($upline[$uplineIndex])) continue;

                $beneficiaryId = (int) $upline[$uplineIndex]['id'];
                $this->writeLedger(
                    $beneficiaryId,
                    $payerUserId,
                    (float) $package['price'],
                    0,
                    $amountFloat,
                    'joining_package',
                    $levelInt,
                    0,
                    0,
                    "Level {$levelInt} bonus — package {$package['package_code']}, reg: {$regNumber}"
                );
                $ledgerEntries[] = [
                    'beneficiary' => $beneficiaryId,
                    'amount'      => $amountFloat,
                    'level'       => $levelInt,
                    'type'        => 'level_bonus',
                ];
                $totalDistributed += $amountFloat;
            }

            // 8. Log registration activity
            $this->logActivity($payerUserId, 'joining_package', [
                'package_code'       => $package['package_code'],
                'registration_number' => $regNumber,
                'amount_paid'        => $package['price'],
                'total_distributed'  => $totalDistributed,
            ]);

            $this->pdo->commit();

            return [
                'success'            => true,
                'registration_number' => $regNumber,
                'ledger_entries'     => count($ledgerEntries),
                'total_distributed'  => $totalDistributed,
                'details'            => $ledgerEntries,
            ];

        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("MlmInvestmentEngine::processJoiningPackage ERROR: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get all joining packages available.
     */
    public function listPackages(): array
    {
        $stmt = $this->pdo->query(
            "SELECT id, package_code, package_name, price, direct_sponsor_bonus,
                    level_payout_json, description, is_active
             FROM mlm_joining_packages
             WHERE is_active = 1
             ORDER BY price ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get a single package by ID.
     */
    public function getPackage(int $packageId): ?array
    {
        return $this->fetchPackage($packageId);
    }

    /**
     * Get all registrations for a user.
     */
    public function getUserRegistrations(int $userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT r.*, p.package_name, p.package_code
            FROM mlm_associate_registrations r
            JOIN mlm_joining_packages p ON p.id = r.package_id
            WHERE r.user_id = ?
            ORDER BY r.registered_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get all registrations (admin).
     */
    public function listRegistrations(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare("
            SELECT r.*, p.package_name, p.package_code, u.name AS user_name
            FROM mlm_associate_registrations r
            JOIN mlm_joining_packages p ON p.id = r.package_id
            LEFT JOIN users u ON u.id = r.user_id
            ORDER BY r.registered_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get summary stats for joining packages.
     */
    public function getStats(): array
    {
        $stats = [];

        // Total registrations
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM mlm_associate_registrations");
        $stats['total_registrations'] = (int) $stmt->fetchColumn();

        // Paid registrations
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM mlm_associate_registrations WHERE payment_status = 'paid'");
        $stats['paid_registrations'] = (int) $stmt->fetchColumn();

        // Total revenue
        $stmt = $this->pdo->query("SELECT COALESCE(SUM(amount_paid), 0) FROM mlm_associate_registrations WHERE payment_status = 'paid'");
        $stats['total_revenue'] = (float) $stmt->fetchColumn();

        // Total distributed via joining packages
        $stmt = $this->pdo->query("SELECT COALESCE(SUM(amount), 0) FROM mlm_commission_ledger WHERE commission_type = 'joining_package' AND status = 'paid'");
        $stats['total_distributed'] = (float) $stmt->fetchColumn();

        // Pending registrations
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM mlm_associate_registrations WHERE payment_status = 'pending'");
        $stats['pending_registrations'] = (int) $stmt->fetchColumn();

        // Active packages
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM mlm_joining_packages WHERE is_active = 1");
        $stats['active_packages'] = (int) $stmt->fetchColumn();

        return $stats;
    }

    /* ================================================================
       PRIVATE HELPERS
       ================================================================ */

    private function fetchPackage(int $packageId): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM mlm_joining_packages WHERE id = ? AND is_active = 1"
        );
        $stmt->execute([$packageId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function fetchAssociate(int $userId): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM associates WHERE user_id = ? AND status = 'active'"
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function checkDuplicate(int $userId, int $packageId): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM mlm_associate_registrations WHERE user_id = ? AND package_id = ? AND payment_status = 'paid'"
        );
        $stmt->execute([$userId, $packageId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    private function generateRegistrationNumber(): string
    {
        return 'REG-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }

    private function createRegistration(string $regNumber, int $userId, int $packageId, array $context): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO mlm_associate_registrations
                (user_id, package_id, registration_number, payment_status, payment_method, payment_reference, amount_paid, registered_at, paid_at)
            VALUES (?, ?, ?, 'paid', ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([
            $userId,
            $packageId,
            $regNumber,
            $context['payment_method'] ?? null,
            $context['payment_reference'] ?? null,
            $this->getPackagePrice($packageId),
        ]);
    }

    private function getPackagePrice(int $packageId): float
    {
        $stmt = $this->pdo->prepare("SELECT price FROM mlm_joining_packages WHERE id = ?");
        $stmt->execute([$packageId]);
        return (float) $stmt->fetchColumn();
    }

    /**
     * Get the upline chain starting from the user's sponsor.
     *
     * Walks mlm_network_tree.parent_id chain.
     * Returns array of user records [0=sponsor, 1=sponsor's sponsor, ...].
     */
    private function getUplineChain(int $userId, int $maxLevels = 6): array
    {
        $chain = [];
        $currentId = $userId;

        for ($i = 0; $i < $maxLevels; $i++) {
            $stmt = $this->pdo->prepare("
                SELECT u.id, u.name, u.email
                FROM mlm_network_tree mnt
                JOIN users u ON u.id = mnt.parent_id
                WHERE mnt.associate_id = ?
                LIMIT 1
            ");
            $stmt->execute([$currentId]);
            $parent = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$parent) break;
            $chain[] = $parent;
            $currentId = (int) $parent['id'];
        }

        return $chain;
    }

    private function writeLedger(
        int    $beneficiaryId,
        int    $sourceId,
        float  $saleAmount,
        float  $pct,
        float  $amount,
        string $type,
        int    $level,
        int    $bookingId,
        int    $receiptId,
        string $notes
    ): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO mlm_commission_ledger
                (beneficiary_user_id, source_user_id, commission_type, amount,
                 level, sale_amount, commission_percentage, status, notes,
                 property_id, created_at" . ($this->tenantId() > 1 ? ', tenant_id' : '') . ")
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, NOW()" . ($this->tenantId() > 1 ? ', ?' : '') . ")
        ");
        $mparams = [
            $beneficiaryId,
            $sourceId,
            $type,
            round($amount, 2),
            $level,
            $saleAmount,
            round($pct, 2),
            $notes,
            null,
        ];
        if ($this->tenantId() > 1) $mparams[] = $this->tenantId();
        $stmt->execute($mparams);
        return (int) $this->pdo->lastInsertId();
    }

    private function logActivity(int $userId, string $action, array $data): void
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO daily_operations_log (user_id, activity_type, description, reference_type, reference_id, created_at)
                VALUES (?, ?, ?, 'joining_package', ?, NOW())
            ");
            $stmt->execute([
                $userId,
                $action,
                json_encode($data),
                $data['registration_number'] ?? null,
            ]);
        } catch (Exception $e) {
            error_log("MlmInvestmentEngine::logActivity ERROR: " . $e->getMessage());
        }
    }
}
