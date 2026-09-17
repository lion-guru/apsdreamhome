<?php
namespace App\Services\Pricing;

use App\Services\ServiceConfigService;

/**
 * Preferential Location Charges (PLC) engine.
 *
 * Rates are admin-configurable via service_configs group `pricing`
 * (corner_pct, park_pct, wideroad_pct, wideroad_min_ft) with sane
 * defaults below. All math is additive on the base rate.
 */
class PlcService
{
    public static function rates(): array
    {
        return [
            'corner_pct' => (float)ServiceConfigService::getVal('pricing', 'corner_pct', 10),
            'park_pct' => (float)ServiceConfigService::getVal('pricing', 'park_pct', 5),
            'wideroad_pct' => (float)ServiceConfigService::getVal('pricing', 'wideroad_pct', 5),
            'wideroad_min_ft' => (float)ServiceConfigService::getVal('pricing', 'wideroad_min_ft', 40),
        ];
    }

    /**
     * @return array [plc_pct, plc_amount, final_pps, total, breakdown[]]
     */
    public static function calculate(float $basePps, float $areaSqft, bool $corner, bool $park, float $roadWidthFt): array
    {
        $r = self::rates();
        $pct = 0.0;
        $breakdown = [];
        if ($corner && $r['corner_pct'] > 0) {
            $pct += $r['corner_pct'];
            $breakdown[] = 'Corner +' . $r['corner_pct'] . '%';
        }
        if ($park && $r['park_pct'] > 0) {
            $pct += $r['park_pct'];
            $breakdown[] = 'Park facing +' . $r['park_pct'] . '%';
        }
        if ($roadWidthFt >= $r['wideroad_min_ft'] && $r['wideroad_pct'] > 0) {
            $pct += $r['wideroad_pct'];
            $breakdown[] = 'Wide road (' . $roadWidthFt . 'ft) +' . $r['wideroad_pct'] . '%';
        }
        $finalPps = round($basePps * (1 + $pct / 100), 2);
        $plcAmount = round(($finalPps - $basePps) * $areaSqft, 2);
        return [
            'plc_pct' => round($pct, 2),
            'plc_amount' => $plcAmount,
            'final_pps' => $finalPps,
            'total' => round($finalPps * $areaSqft, 2),
            'breakdown' => $breakdown,
        ];
    }

    /** Idempotent schema guard for PLC breakdown columns. */
    public static function ensureColumns($db): void
    {
        try {
            $cols = $db->query("SHOW COLUMNS FROM plots LIKE 'plc_amount'")->fetchAll(\PDO::FETCH_ASSOC);
            if (empty($cols)) {
                $db->exec("ALTER TABLE plots ADD COLUMN plc_amount DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER base_price_per_sqft");
            }
            $cols = $db->query("SHOW COLUMNS FROM plots LIKE 'final_price_per_sqft'")->fetchAll(\PDO::FETCH_ASSOC);
            if (empty($cols)) {
                $db->exec("ALTER TABLE plots ADD COLUMN final_price_per_sqft DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER plc_amount");
            }
        } catch (\Throwable $e) {
            error_log('[PlcService::ensureColumns] ' . $e->getMessage());
        }
    }

    /** Idempotent seed so the pricing group is visible in admin service-configs UI. */
    public static function ensureConfigRows($db): void
    {
        $defaults = [
            'corner_pct' => ['10', 'Corner plot PLC % on base rate'],
            'park_pct' => ['5', 'Park-facing PLC % on base rate'],
            'wideroad_pct' => ['5', 'Wide-road PLC % on base rate'],
            'wideroad_min_ft' => ['40', 'Road width (ft) qualifying for wide-road PLC'],
        ];
        try {
            foreach ($defaults as $key => [$val, $desc]) {
                $exists = $db->prepare("SELECT id FROM service_configs WHERE service_name = 'pricing' AND config_key = ? LIMIT 1");
                $exists->execute([$key]);
                if (!$exists->fetchColumn()) {
                    $db->prepare("INSERT INTO service_configs (service_name, config_key, config_value, config_type, description, group_name) VALUES ('pricing', ?, ?, 'number', ?, 'pricing')")->execute([$key, $val, $desc]);
                }
            }
        } catch (\Throwable $e) {
            error_log('[PlcService::ensureConfigRows] ' . $e->getMessage());
        }
    }
}
