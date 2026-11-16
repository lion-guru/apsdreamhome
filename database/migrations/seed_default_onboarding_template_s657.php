<?php
/**
 * Idempotent seed: default "Standard Employee Onboarding" template with starter items.
 * Safe to re-run; matches by template name.
 */

require_once __DIR__ . '/../../config/bootstrap.php';

echo "Onboarding default template seed\n";

$connect = function (): PDO {
    $dsn = 'mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_NAME') . ';charset=utf8mb4';
    return new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
};

$pdo = $connect();

$name = 'Standard Employee Onboarding';

$row = $pdo->prepare("SELECT id FROM onboarding_templates WHERE name = ? LIMIT 1");
$row->execute([$name]);
$existing = $row->fetchColumn();

if ($existing) {
    echo "SKIP: '$name' already exists (id=$existing)\n";
    exit(0);
}

$items = [
    ['Collect signed offer letter', 'Get the original signed offer letter and file it in employee documents.', 'document', true, null],
    ['ID verification (KYC)', 'Aadhaar / PAN / passport and emergency contact on file and verified.', 'document', true, null],
    ['Create e-mail + Slack + GitHub accounts', 'Provision corporate email, chat, and source control access.', 'access', true, null],
    ['Laptop + peripherals issued', 'Hardware assigned by IT: laptop, charger, mouse, and accessories.', 'equipment', true, null],
    ['Handbook + policy acknowledgement', 'Read and sign the employee handbook, code of conduct, and IT policy.', 'compliance', true, null],
    ['Security + data-privacy induction', 'Mandatory security awareness training completed in year 1.', 'compliance', true, null],
    ['Role-specific training plan', 'Department manager assigns first 30/60/90-day goals.', 'training', true, null],
    ['1:1 with HR in week 1', 'Welcome session: benefits, payroll, leave policy, growth plan.', 'other', true, null],
];

$pdo->beginTransaction();
try {
    $insTpl = $pdo->prepare("INSERT INTO onboarding_templates (tenant_id, name, description, is_active, created_at) VALUES (?, ?, ?, ?, NOW())");
    $insTpl->execute([1, $name, 'Default checklist for every new hire. Customize per department in Templates.', 1]);
    $templateId = (int)$pdo->lastInsertId();
    echo "  + template id=$templateId\n";

    $insItem = $pdo->prepare("INSERT INTO onboarding_template_items (tenant_id, template_id, item_name, description, category, is_mandatory, assigned_to_role, due_days, sort_order, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    foreach ($items as $i => [$itemName, $desc, $cat, $mandatory, $dueDays]) {
        $insItem->execute([1, $templateId, $itemName, $desc, $cat, $mandatory ? 1 : 0, null, $dueDays, (string)$i]);
    }
    echo "  + " . count($items) . " items\n";
    $pdo->commit();
    echo "DONE\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    echo 'FAIL: ' . $e->getMessage() . "\n";
    exit(1);
}
