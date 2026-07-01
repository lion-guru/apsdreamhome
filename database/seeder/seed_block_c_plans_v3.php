<?php
$root   = dirname(__DIR__, 2);
$config = require $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "Connected.\n";

    // ── Add columns to investment_plans if missing ────────────────────
    $planCols = [
        'plot_promised_value' => "DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Total plot value company will deliver at maturity'",
        'plot_promised_sqft'  => "INT NOT NULL DEFAULT 0 COMMENT 'Plot area (sqft) delivered at maturity'",
        'block_restriction'   => "VARCHAR(50) NULL DEFAULT NULL COMMENT 'e.g. Block C only'",
        'cancellation_lock_days' => "INT NOT NULL DEFAULT 365 COMMENT 'Days after which cancellation is locked'",
        'cancellation_charge_pct' => "DECIMAL(5,2) NOT NULL DEFAULT 10.00 COMMENT '% of principal charged on cancellation'",
    ];

    foreach ($planCols as $col => $def) {
        $exists = $pdo->query("
            SELECT COUNT(*) FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'investment_plans'
              AND COLUMN_NAME = '$col'
        ")->fetchColumn();

        if (!$exists) {
            $pdo->exec("ALTER TABLE investment_plans ADD COLUMN $col $def");
            echo "✅ investment_plans.$col — added\n";
        } else {
            echo "ℹ️  investment_plans.$col — already exists\n";
        }
    }

    // ── Insert / update Block-C plans ─────────────────────────────────
    $plans = [
        [
            'name'            => 'Block-C Investment Plan (₹5 Lakh)',
            'code'            => 'BLOCK-C-5L',
            'category'        => 'plot_investment',
            'description'     => 'Invest ₹5,00,000 for 3 years. Company pays remaining ₹4,99,000. Receive 1000 sqft plot worth ₹9,99,000 after 3 years.',
            'min_amount'      => 500000,
            'max_amount'      => 500000,
            'tenure_months'   => 36,
            'expected_return' => 0,
            'plot_sqft'       => 1000,
            'plot_value'      => 999000,
            'company_contrib' => 499000,
            'block'           => 'Block-C',
        ],
        [
            'name'            => 'Block-C Investment Plan (₹3 Lakh)',
            'code'            => 'BLOCK-C-3L',
            'category'        => 'plot_investment',
            'description'     => 'Invest ₹3,00,000 for 3 years. Company pays remaining ₹2,99,400. Receive 600 sqft plot worth ₹5,99,400 after 3 years.',
            'min_amount'      => 300000,
            'max_amount'      => 300000,
            'tenure_months'   => 36,
            'expected_return' => 0,
            'plot_sqft'       => 600,
            'plot_value'      => 599400,
            'company_contrib' => 299400,
            'block'           => 'Block-C',
        ],
    ];

    $stmt = $pdo->prepare("
        INSERT INTO investment_plans
            (plan_name, plan_code, plan_category, description,
             min_amount, max_amount, tenure_months, expected_return_pct,
             plot_promised_sqft, plot_promised_value, block_restriction,
             cancellation_lock_days, cancellation_charge_pct,
             is_active, is_featured, display_order, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 365, 10.00, 1, 1, ?, NOW())
        ON DUPLICATE KEY UPDATE
            plan_name             = VALUES(plan_name),
            description           = VALUES(description),
            plot_promised_sqft    = VALUES(plot_promised_sqft),
            plot_promised_value   = VALUES(plot_promised_value),
            block_restriction     = VALUES(block_restriction),
            cancellation_lock_days= VALUES(cancellation_lock_days),
            is_active             = 1
    ");

    foreach ($plans as $i => $p) {
        $hasCode = $pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='investment_plans' AND COLUMN_NAME='plan_code'")->fetchColumn();
        if (!$hasCode) {
            $pdo->exec("ALTER TABLE investment_plans ADD COLUMN plan_code VARCHAR(50) NULL UNIQUE");
            echo "✅ investment_plans.plan_code — added\n";
        }

        $stmt->execute([
            $p['name'], $p['code'], $p['category'], $p['description'],
            $p['min_amount'], $p['max_amount'], $p['tenure_months'], $p['expected_return'],
            $p['plot_sqft'], $p['plot_value'], $p['block'],
            $i + 10,
        ]);
        echo "✅ Plan seeded: {$p['name']} (₹" . number_format($p['min_amount']) . " → {$p['plot_sqft']} sqft)\n";
    }

    echo "\n✅ Block-C investment plans seeded successfully.\n";

} catch (Exception $e) {
    echo "❌ FAILED on line " . $e->getLine() . ": " . $e->getMessage() . "\n";
    exit(1);
}
