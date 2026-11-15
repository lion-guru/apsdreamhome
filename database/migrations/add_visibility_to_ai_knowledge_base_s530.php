<?php
// Idempotent: add role-scope visibility to the AI knowledge base (S530).
// Exposure matrix after this migration:
//   public — colonies, FAQs, legal/RERA, pricing, company profile, plot index, SEO docs
//   staff  — anything customer/agent visible explanations are safe with (payment flows, site-visit policy)
//   admin  — internal company data, admin navigation, commission internals, service configs etc.
// Default is 'admin' — unknown docs must NEVER be served to public chat interfaces.

$db = new PDO('mysql:host=localhost;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
]);

function colExists(PDO $db, string $table, string $col): bool {
    $s = $db->prepare("SELECT 1 FROM information_schema.COLUMNS WHERE table_schema=DATABASE() AND table_name=? AND column_name=? LIMIT 1");
    $s->execute([$table, $col]);
    return (bool)$s->fetchColumn();
}

function run(PDO $db, string $sql): string {
    try { $db->exec($sql); return 'FIXED'; }
    catch (Throwable $e) { return 'ERROR: ' . $e->getMessage(); }
}

if (!colExists($db, 'ai_knowledge_base', 'visibility')) {
    echo "visibility ADD -> " . run($db, "ALTER TABLE ai_knowledge_base ADD COLUMN visibility ENUM('public','staff','admin') NOT NULL DEFAULT 'admin'") . "\n";
} else {
    echo "visibility SKIP\n";
}

// Index for the tenant+visibility retrieval filter
$idx = $db->prepare("SELECT 1 FROM information_schema.STATISTICS WHERE table_schema=DATABASE() AND table_name='ai_knowledge_base' AND index_name='idx_kb_visibility' LIMIT 1");
$idx->execute();
if (!$idx->fetchColumn()) {
    echo "idx_kb_visibility ADD -> " . run($db, "ALTER TABLE ai_knowledge_base ADD INDEX idx_kb_visibility (tenant_id, visibility, is_active)") . "\n";
} else {
    echo "idx_kb_visibility SKIP\n";
}

// Conservative backfill — this content is public REAL-ESTATE MARKETING and
// legal/pricing guidance material that is ALREADY shown on the public website.
foreach ([
    "colony_details",
    "plot_index",
    "faq",
    "legal",
    "pricing",
    "process",
    "company_profile",
    "colony_comparison",
    "seo",
    "finance", // EMI/pricing guidance — shown verbatim on public pages already
] as $src) {
    $n = $db->exec("UPDATE ai_knowledge_base SET visibility='public' WHERE visibility='admin' AND source=" . $db->quote($src));
    echo "visibility->public[$src]: $n rows\n";
}

// Summary counts
foreach ($db->query("SELECT visibility, COUNT(*) n FROM ai_knowledge_base GROUP BY visibility") as $row) {
    echo "  {$row['visibility']}: {$row['n']}\n";
}
