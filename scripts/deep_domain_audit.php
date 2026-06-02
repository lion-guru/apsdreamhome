<?php
/**
 * PHASE 1 (OPTIMIZED): Quick domain audit
 * Pre-cached code references to avoid recursion bottleneck
 */
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

// Use a single regex over all PHP files to count refs
echo "Scanning code references (one pass)...\n";
$allFiles = [];
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app'));
foreach ($iter as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $allFiles[$f->getPathname()] = file_get_contents($f->getPathname());
    }
}
echo "Files: " . count($allFiles) . "\n";

$codeRef = [];
foreach ($tables as $t) {
    $count = 0;
    $pattern = "/\b(FROM|JOIN|INTO|UPDATE)\s+`?$t`?/i";
    foreach ($allFiles as $content) {
        $count += preg_match_all($pattern, $content);
    }
    $codeRef[$t] = $count;
}
echo "Code refs scanned.\n\n";

// Domain grouping
$domains = [
    'USER' => [],
    'ADDRESS' => [],
    'BANK_KYC' => [],
    'CONTACT' => [],
    'PROPERTY' => [],
    'BOOKING' => [],
    'PAYMENT' => [],
    'INVOICE' => [],
    'LEAD' => [],
    'DEAL' => [],
    'COMMISSION_PAYOUT' => [],
    'MLM' => [],
    'WALLET' => [],
    'NOTIFICATION' => [],
    'AI_CHAT' => [],
    'VOICE' => [],
    'HRM' => [],
    'PAYROLL' => [],
    'CAMPAIGN' => [],
    'CONTENT' => [],
    'MEDIA' => [],
    'CHAT' => [],
    'SUPPORT' => [],
    'LOCATION' => [],
    'PROJECT' => [],
    'FARMER' => [],
    'AUDIT' => [],
    'ANALYTICS' => [],
    'SETTINGS' => [],
    'API' => [],
    'AUTH' => [],
    'PERMISSION' => [],
    'MENU' => [],
    'FILE' => [],
    'WORKFLOW' => [],
    'GAMIFICATION' => [],
    'TRAINING' => [],
    'CAREER' => [],
    'SCHEDULER' => [],
    'REPORT' => [],
    'OTHER' => [],
];

foreach ($tables as $t) {
    $lower = strtolower($t);
    $rows = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    $code = $codeRef[$t];

    $cat = 'OTHER';
    if (preg_match('/^(users?|customers?|admin_users?|associates?|employees?|agents?|builders?|investors?|companies?)$/', $lower)) $cat = 'USER';
    elseif (preg_match('/(address|user_addr)/', $lower)) $cat = 'ADDRESS';
    elseif (preg_match('/(bank|kyc|account|ifsc)/', $lower)) $cat = 'BANK_KYC';
    elseif (preg_match('/(social_account|contact|user_phone|email_verif)/', $lower)) $cat = 'CONTACT';
    elseif (preg_match('/(colony|plot|user_properties|property|resell)/', $lower)) $cat = 'PROPERTY';
    elseif (preg_match('/(booking|allocation|visit)/', $lower)) $cat = 'BOOKING';
    elseif (preg_match('/(payment|emi|transaction|refund|wallet)/', $lower)) $cat = 'PAYMENT';
    elseif (preg_match('/(invoice|expense|receipt|purchase_invoice|sales_invoice)/', $lower)) $cat = 'INVOICE';
    elseif (preg_match('/^lead/', $lower)) $cat = 'LEAD';
    elseif (preg_match('/(deal|opportunity|kanban|pipeline_)/', $lower)) $cat = 'DEAL';
    elseif (preg_match('/(commission|payout)/', $lower)) $cat = 'COMMISSION_PAYOUT';
    elseif (preg_match('/^(mlm|network|referral|sponsor|associate_|associate$)/', $lower)) $cat = 'MLM';
    elseif (preg_match('/(wallet|points|reward|loyalty|rank_)/', $lower)) $cat = 'WALLET';
    elseif (preg_match('/(notification|email_queue|sms_queue|whatsapp|email_log|sms_log|push_)/', $lower)) $cat = 'NOTIFICATION';
    elseif (preg_match('/^(ai_|chat_)/', $lower)) $cat = 'AI_CHAT';
    elseif (preg_match('/(voice_|call_|telecall)/', $lower)) $cat = 'VOICE';
    elseif (preg_match('/(hrm|hr_|recruit|job_app|interview|designation|department|company_employees|team)/', $lower)) $cat = 'HRM';
    elseif (preg_match('/(payroll|salary|attendance|leave|shift)/', $lower)) $cat = 'PAYROLL';
    elseif (preg_match('/(campaign|marketing|popup|ad_slot)/', $lower)) $cat = 'CAMPAIGN';
    elseif (preg_match('/(blog|news|legal_page|page_|content|knowledge|testimonial|review|rating|faq|feedback)/', $lower)) $cat = 'CONTENT';
    elseif (preg_match('/(media|gallery|image_)/', $lower)) $cat = 'MEDIA';
    elseif (preg_match('/(support|ticket|help)/', $lower)) $cat = 'SUPPORT';
    elseif (preg_match('/(state|district|city|country|location_|pincode)/', $lower)) $cat = 'LOCATION';
    elseif (preg_match('/^(projects?|project_)/', $lower)) $cat = 'PROJECT';
    elseif (preg_match('/(farmer|land|acquisition)/', $lower)) $cat = 'FARMER';
    elseif (preg_match('/(audit|log|history|track|security_|activity|error_)/', $lower)) $cat = 'AUDIT';
    elseif (preg_match('/(analytics|metrics|stat|forecast|kpi)/', $lower)) $cat = 'ANALYTICS';
    elseif (preg_match('/(setting|config|company_)/', $lower)) $cat = 'SETTINGS';
    elseif (preg_match('/(api_|integration|webhook|developer_)/', $lower)) $cat = 'API';
    elseif (preg_match('/(auth|session|token|two_factor|remember_|password_reset)/', $lower)) $cat = 'AUTH';
    elseif (preg_match('/(permission|role_|user_role|user_perm)/', $lower)) $cat = 'PERMISSION';
    elseif (preg_match('/(menu|sidebar|nav_)/', $lower)) $cat = 'MENU';
    elseif (preg_match('/(file_|document|attachment|version)/', $lower)) $cat = 'FILE';
    elseif (preg_match('/(workflow|task|step)/', $lower)) $cat = 'WORKFLOW';
    elseif (preg_match('/(gamif|leaderboard|badge|challenge|achievement)/', $lower)) $cat = 'GAMIFICATION';
    elseif (preg_match('/(training|module_progress|course|learning)/', $lower)) $cat = 'TRAINING';
    elseif (preg_match('/(career|job$|job_)/', $lower)) $cat = 'CAREER';
    elseif (preg_match('/(schedul|cron|sync)/', $lower)) $cat = 'SCHEDULER';
    elseif (preg_match('/(report)/', $lower)) $cat = 'REPORT';

    $domains[$cat][] = ['name' => $t, 'rows' => $rows, 'code' => $code];
}

foreach ($domains as $cat => $list) {
    if (empty($list)) continue;
    echo "\n=== $cat (" . count($list) . " tables) ===\n";
    usort($list, fn($a, $b) => $b['rows'] - $a['rows']);
    foreach ($list as $t) {
        echo sprintf("  %-45s %6d rows  Code:%d\n", $t['name'], $t['rows'], $t['code']);
    }
}

$total = 0;
foreach ($domains as $list) $total += count($list);
echo "\n=== TOTAL: $total tables ===\n";
