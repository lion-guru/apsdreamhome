<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;
use PDO;
use Exception;

/**
 * MlmInvestmentEngine - Joining package purchase processing
 * 
 * Handles package purchases, sponsor bonus distribution, and upline chain bonuses.
 */
class MlmInvestmentEngine
{
    use ServiceTenantTrait;

    private $pdo;

    public function __construct()
    {
        $db = Database::getInstance();
        $this->pdo = method_exists($db, 'getPdo') ? $db->getPdo() : (method_exists($db, 'getConnection') ? $db->getConnection() : $db);
    }

    public function processJoiningPackage(int $packageId, int $payerUserId, array $context = []): array
    {
        try {
            $this->pdo->beginTransaction();

            $package = $this->fetchPackage($packageId);
            if (!$package) {
                $this->pdo->rollBack();
                return ['success' => false, 'error' => 'Package not found or inactive'];
            }

            $associate = $this->fetchAssociate($payerUserId);
            if (!$associate) {
                $this->pdo->rollBack();
                return ['success' => false, 'error' => 'User is not an associate'];
            }

            if ($this->checkDuplicate($payerUserId, $packageId)) {
                $this->pdo->rollBack();
                return ['success' => false, 'error' => 'User already has this package'];
            }

            $regNumber = $this->generateRegistrationNumber();
            $this->createRegistration($regNumber, $payerUserId, $packageId, $context);
            $upline = $this->getUplineChain($payerUserId, 6);

            $directBonus = (float) $package['direct_sponsor_bonus'];
            $ledgerEntries = [];
            $totalDistributed = 0.0;

            if ($directBonus > 0 && !empty($upline[0])) {
                $sponsorId = (int) $upline[0]['id'];
                $this->writeLedger($sponsorId, $payerUserId, (float)$package['price'], $directBonus, 'joining_package', 1, "Direct sponsor bonus - package {$package['package_code']}");
                $ledgerEntries[] = ['beneficiary' => $sponsorId, 'amount' => $directBonus, 'level' => 1, 'type' => 'direct_sponsor'];
                $totalDistributed += $directBonus;
            }

            $levelPayouts = json_decode($package['level_payout_json'], true) ?: [];
            foreach ($levelPayouts as $level => $amount) {
                $levelInt = (int) $level;
                $amountFloat = (float) $amount;
                if ($amountFloat <= 0) continue;
                $uplineIndex = $levelInt - 1;
                if (!isset($upline[$uplineIndex])) continue;
                $beneficiaryId = (int) $upline[$uplineIndex]['id'];
                $this->writeLedger($beneficiaryId, $payerUserId, (float)$package['price'], $amountFloat, 'joining_package', $levelInt, "Level {$levelInt} bonus - package {$package['package_code']}");
                $ledgerEntries[] = ['beneficiary' => $beneficiaryId, 'amount' => $amountFloat, 'level' => $levelInt, 'type' => 'level_bonus'];
                $totalDistributed += $amountFloat;
            }

            $this->pdo->commit();

            return [
                'success' => true,
                'registration_number' => $regNumber,
                'ledger_entries' => count($ledgerEntries),
                'total_distributed' => $totalDistributed,
                'details' => $ledgerEntries,
            ];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("MlmInvestmentEngine::processJoiningPackage ERROR: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function listPackages(): array
    {
        $stmt = $this->pdo->prepare("SELECT id, package_code, package_name, price, direct_sponsor_bonus, level_payout_json, description, is_active FROM mlm_joining_packages WHERE is_active = 1" . $this->tenantSql() . " ORDER BY price ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getPackage(int $packageId): ?array
    {
        return $this->fetchPackage($packageId);
    }

    public function getUserRegistrations(int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT r.*, p.package_name, p.package_code FROM mlm_associate_registrations r JOIN mlm_joining_packages p ON p.id = r.package_id WHERE r.user_id = ? ORDER BY r.registered_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getStats(): array
    {
        $stats = [];
        $stats['total_registrations'] = (int) $this->pdo->query("SELECT COUNT(*) FROM mlm_associate_registrations")->fetchColumn();
        $stats['paid_registrations'] = (int) $this->pdo->query("SELECT COUNT(*) FROM mlm_associate_registrations WHERE payment_status = 'paid'")->fetchColumn();
        $stats['total_revenue'] = (float) $this->pdo->query("SELECT COALESCE(SUM(amount_paid), 0) FROM mlm_associate_registrations WHERE payment_status = 'paid'")->fetchColumn();
        $stats['total_distributed'] = (float) $this->pdo->query("SELECT COALESCE(SUM(amount), 0) FROM mlm_commission_ledger WHERE commission_type = 'joining_package' AND status = 'paid'")->fetchColumn();
        $stats['active_packages'] = (int) $this->pdo->query("SELECT COUNT(*) FROM mlm_joining_packages WHERE is_active = 1")->fetchColumn();
        return $stats;
    }

    private function fetchPackage(int $packageId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM mlm_joining_packages WHERE id = ? AND is_active = 1");
        $stmt->execute([$packageId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function fetchAssociate(int $userId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM mlm_profiles WHERE user_id = ?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function checkDuplicate(int $userId, int $packageId): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM mlm_associate_registrations WHERE user_id = ? AND package_id = ? AND payment_status = 'paid'");
        $stmt->execute([$userId, $packageId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    private function generateRegistrationNumber(): string
    {
        return 'REG-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }

    private function createRegistration(string $regNumber, int $userId, int $packageId, array $context): void
    {
        $columns = array_merge(['user_id', 'package_id', 'registration_number', 'payment_status', 'payment_method', 'payment_reference', 'amount_paid', 'registered_at', 'paid_at'], array_keys($this->tenantInsertData()));
        $values  = array_merge([$userId, $packageId, $regNumber, 'paid', $context['payment_method'] ?? null, $context['payment_reference'] ?? null, $this->getPackagePrice($packageId), date('Y-m-d H:i:s'), date('Y-m-d H:i:s')], array_values($this->tenantInsertData()));
        $placeholders = str_repeat('?,', count($columns) - 1) . '?';
        $stmt = $this->pdo->prepare("INSERT INTO mlm_associate_registrations (" . implode(', ', $columns) . ") VALUES ({$placeholders})");
        $stmt->execute($values);
    }

    private function getPackagePrice(int $packageId): float
    {
        $stmt = $this->pdo->prepare("SELECT price FROM mlm_joining_packages WHERE id = ?");
        $stmt->execute([$packageId]);
        return (float) $stmt->fetchColumn();
    }

    private function getUplineChain(int $userId, int $maxLevels = 6): array
    {
        $chain = [];
        $currentId = $userId;
        for ($i = 0; $i < $maxLevels; $i++) {
            $stmt = $this->pdo->prepare("SELECT u.id, u.name, u.email FROM mlm_network_tree mnt JOIN users u ON u.id = mnt.parent_id WHERE mnt.associate_id = ? LIMIT 1");
            $stmt->execute([$currentId]);
            $parent = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$parent) break;
            $chain[] = $parent;
            $currentId = (int) $parent['id'];
        }
        return $chain;
    }

    private function writeLedger(int $beneficiaryId, int $sourceId, float $saleAmount, float $amount, string $type, int $level, string $notes): int
    {
        $columns = array_merge(['beneficiary_user_id', 'source_user_id', 'commission_type', 'amount', 'level', 'sale_amount', 'status', 'notes', 'created_at'], array_keys($this->tenantInsertData()));
        $values  = array_merge([$beneficiaryId, $sourceId, $type, round($amount, 2), $level, $saleAmount, 'pending', $notes, date('Y-m-d H:i:s')], array_values($this->tenantInsertData()));
        $placeholders = str_repeat('?,', count($columns) - 1) . '?';
        $stmt = $this->pdo->prepare("INSERT INTO mlm_commission_ledger (" . implode(', ', $columns) . ") VALUES ({$placeholders})");
        $stmt->execute($values);
        return (int) $this->pdo->lastInsertId();
    }
}
