<?php
// Commission Earnings Widget for Admin Dashboard
require_once __DIR__ . '/core/init.php';
$db = \App\Core\App::database();

// Create associates table if not exists
$db->execute("CREATE TABLE IF NOT EXISTS associates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    contact VARCHAR(50),
    email VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Create MLM Commission Ledger table if not exists
$db->execute("CREATE TABLE IF NOT EXISTS mlm_commission_ledger (
    id INT AUTO_INCREMENT PRIMARY KEY,
    associate_id INT,
    commission_amount DECIMAL(10,2) NOT NULL,
    commission_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    description TEXT,
    status ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
    FOREIGN KEY (associate_id) REFERENCES associates(id)
)");

// Total commission distributed to all associates
$row = $db->fetchOne("SELECT SUM(commission_amount) as total FROM mlm_commission_ledger");
$total_commission = $row['total'] ?? 0;

// Top 5 earners
$top = $db->fetchAll("SELECT a.name, l.associate_id, SUM(l.commission_amount) as earned FROM mlm_commission_ledger l JOIN associates a ON l.associate_id=a.id GROUP BY l.associate_id ORDER BY earned DESC LIMIT 5");
?>
<div class="card">
    <div class="card-body">
        <h5 class="card-title">MLM Commission Overview</h5>
        <div class="mb-2"><strong>Total Commission Distributed:</strong> ₹<?php echo number_format($total_commission, 2); ?></div>
        <h6 class="mt-3">Top 5 Earners</h6>
        <ol>
            <?php foreach ($top as $row): ?>
                <li><?php echo h($row['name'] ?? 'Associate ' . $row['associate_id']); ?> (ID: <?php echo h($row['associate_id']); ?>): ₹<?php echo number_format($row['earned'], 2); ?></li>
            <?php endforeach; ?>
        </ol>
        <a href="associate_commission_report.php" class="btn btn-primary btn-sm mt-2">View All Reports</a>
    </div>
</div>
