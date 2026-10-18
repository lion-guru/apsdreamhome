<?php

namespace App\Services;

use App\Traits\ServiceTenantTrait;
use Exception;

class CurrencyService
{
    use ServiceTenantTrait;

    private $db;
    private $fx;

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
        $this->fx = new ExchangeRateService();
    }

    public function getRates(): array
    {
        $tid = $this->tenantId();
        return $this->db->fetchAll("SELECT * FROM exchange_rates WHERE is_active = 1" . ($tid > 1 ? " AND tenant_id = ?" : "") . " ORDER BY target_currency", $tid > 1 ? [$tid] : []) ?: [];
    }

    public function getRate(int $id): ?array
    {
        return $this->db->fetchOne("SELECT * FROM exchange_rates WHERE id = ?", [$id]);
    }

    public function addRate(array $data): int
    {
        $tid = $this->tenantId();
        $insertData = [
            'tenant_id' => $tid,
            'base_currency' => strtoupper($data['base_currency'] ?? 'INR'),
            'target_currency' => strtoupper($data['target_currency']),
            'rate' => (float)$data['rate'],
            'source' => $data['source'] ?? 'manual',
            'fetched_at' => $data['fetched_at'] ?? date('Y-m-d H:i:s'),
            'is_active' => isset($data['is_active']) ? 1 : 0,
        ];
        $this->db->insert('exchange_rates', $insertData);
        return (int)$this->db->lastInsertId();
    }

    public function updateRate(int $id, array $data): bool
    {
        $allowed = ['base_currency','target_currency','rate','source','is_active'];
        $updateData = [];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) $updateData[$f] = $f === 'rate' ? (float)$data[$f] : ($f === 'is_active' ? (int)$data[$f] : $data[$f]);
        }
        if (isset($updateData['base_currency'])) $updateData['base_currency'] = strtoupper($updateData['base_currency']);
        if (isset($updateData['target_currency'])) $updateData['target_currency'] = strtoupper($updateData['target_currency']);
        if (empty($updateData)) return false;
        return $this->db->update('exchange_rates', $updateData, 'id = ?', [$id]);
    }

    public function deleteRate(int $id): bool
    {
        return $this->db->delete('exchange_rates', 'id = ?', [$id]);
    }

    public function refreshRates(): array
    {
        $tid = $this->tenantId();
        $result = ['updated' => 0, 'failed' => 0];
        $rates = $this->getRates();
        foreach ($rates as $r) {
            try {
                $fxResult = $this->fx->getRate($r['base_currency'], $r['target_currency']);
                if ($fxResult['success']) {
                    $this->db->update('exchange_rates', [
                        'rate' => (float)$fxResult['rate'],
                        'source' => 'api',
                        'fetched_at' => $fxResult['fetched_at'] ?? date('Y-m-d H:i:s'),
                    ], 'id = ?', [$r['id']]);
                    $result['updated']++;
                } else {
                    $result['failed']++;
                }
            } catch (Exception $e) {
                $result['failed']++;
            }
        }
        return $result;
    }

    public function convert(float $amount, string $from, string $to): array
    {
        $result = $this->fx->convertAmount($amount, $from, $to);
        if ($result['success']) {
            $this->db->beginTransaction();
            try {
                $existing = $this->db->fetchOne("SELECT id FROM exchange_rates WHERE base_currency = ? AND target_currency = ?", [strtoupper($from), strtoupper($to)]);
                if ($existing) {
                    $this->db->update('exchange_rates', ['rate' => $result['rate'], 'source' => 'api', 'fetched_at' => $result['fetched_at']], 'id = ?', [$existing['id']]);
                } else {
                    $this->db->insert('exchange_rates', [
                        'tenant_id' => $this->tenantId(),
                        'base_currency' => strtoupper($from),
                        'target_currency' => strtoupper($to),
                        'rate' => $result['rate'],
                        'source' => 'api',
                        'fetched_at' => $result['fetched_at'],
                        'is_active' => 1,
                    ]);
                }
                $this->db->commit();
            } catch (Exception $e) {
                $this->db->rollBack();
            }
        }
        return $result;
    }

    public function getSupportedCurrencies(): array
    {
        return $this->fx->getSupportedCurrencies();
    }

    public function getStats(): array
    {
        $tid = $this->tenantId();
        $tf = $tid > 1 ? " AND tenant_id = ?" : "";
        $tp = $tid > 1 ? [$tid] : [];
        return [
            'total_pairs' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM exchange_rates WHERE 1=1" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tp),
            'active' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM exchange_rates WHERE is_active=1" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tp),
            'inactive' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM exchange_rates WHERE is_active=0" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tp),
            'api_sourced' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM exchange_rates WHERE source='api'" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tp),
            'manual_sourced' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM exchange_rates WHERE source='manual'" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tp),
        ];
    }
}