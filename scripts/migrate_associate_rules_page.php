<?php
/**
 * Migration: Seed associate-rules CMS page + verify terms-conditions & refund-policy
 *
 * Usage: php scripts/migrate_associate_rules_page.php
 */

$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: 3306;
$user = getenv('DB_USERNAME') ?: 'root';
$pass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : (getenv('DB_PASS') ?: '');
$db   = getenv('DB_DATABASE') ?: 'apsdreamhome';

try {
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$db}", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $pdo->exec("CREATE TABLE IF NOT EXISTS pages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        content LONGTEXT DEFAULT NULL,
        meta_description VARCHAR(500) DEFAULT NULL,
        meta_keywords VARCHAR(500) DEFAULT NULL,
        status ENUM('draft','published','archived') DEFAULT 'draft',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY idx_pages_tenant_id (tenant_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Check if associate-rules page exists
    $stmt = $pdo->prepare("SELECT id FROM pages WHERE slug = ? LIMIT 1");
    $stmt->execute(['associate-rules']);
    $existing = $stmt->fetch();

    if ($existing) {
        echo "[OK] Page 'associate-rules' already exists (id={$existing['id']}). Skipping.\n";
    } else {
        $content = <<<HTML
<h2>Associate Code of Conduct</h2>
<p><strong>Last Updated:</strong> September 2026</p>
<p>This Associate Code of Conduct constitutes a legally binding agreement between APS Dream Homes Pvt. Ltd. ("the Company") and the undersigned Associate ("the Associate"), governed by the Indian Information Technology Act, 2000 and the Indian Contract Act, 1872.</p>

<h3>1. Parties &amp; Scope</h3>
<p>This Code applies to all individuals registered as Associates of APS Dream Home, including Independent Associates, MLM Associates, and Agent Track Associates. It supplements the Tripartite Master Deed and the Associate Agreement.</p>

<h3>2. Professional Conduct</h3>
<ul>
    <li><strong>Ethical Standards:</strong> The Associate shall conduct all business dealings in a professional, ethical, and lawful manner, consistent with the standards expected of a licensed real estate associate under RERA.</li>
    <li><strong>Fiduciary Duty:</strong> The Associate shall act in the best interests of both the Company and the Client, disclosing all material facts regarding any property listing, transaction, or investment opportunity.</li>
    <li><strong>No Conflicts of Interest:</strong> The Associate must disclose any personal or financial interest that may conflict with their duties to the Company or its clients.</li>
</ul>

<h3>3. Anti-Bribery &amp; Anti-Corruption</h3>
<p>The Associate shall not offer, give, solicit, or accept any bribe, kickback, or improper inducement in connection with any Company business. Any violation will result in immediate termination and legal proceedings.</p>

<h3>4. Confidentiality &amp; Data Protection</h3>
<ul>
    <li>The Associate shall maintain strict confidentiality of all proprietary information, client data, pricing strategies, and business plans of the Company.</li>
    <li>All personal data of clients shall be handled in compliance with the Digital Personal Data Protection Act, 2023.</li>
    <li>The Associate shall not share client data with unauthorized third parties under any circumstances.</li>
</ul>

<h3>5. Marketing &amp; RERA Compliance</h3>
<ul>
    <li>All marketing materials shall comply with the Real Estate (Regulation and Development) Act, 2016 (RERA).</li>
    <li>No misleading or false claims shall be made regarding property specifications, pricing, possession timelines, or amenities.</li>
    <li>All advertisements must clearly state the RERA registration number and project details as required by law.</li>
</ul>

<h3>6. Commission Structure</h3>
<p>The Associate acknowledges and agrees to the commission structure as outlined in the Tripartite Master Deed. The commission is strictly governed by the following:</p>
<ul>
    <li><strong>Differential Commission:</strong> The Associate earns the differential between their rank rate and their downline rate on qualifying sales.</li>
    <li><strong>No Unauthorized Modifications:</strong> Any deviation from the approved commission structure must be authorized in writing by the Company.</li>
    <li><strong>Clawback Provisions:</strong> Commissions may be clawed back if the underlying transaction is cancelled or the customer defaults within the applicable period.</li>
</ul>

<h3>7. Code of Ethics</h3>
<p>The Associate shall:</p>
<ol>
    <li>Treat all clients, colleagues, and competitors with dignity and respect.</li>
    <li>Avoid discriminatory practices in any form.</li>
    <li>Never misrepresent their qualifications, authority, or affiliation.</li>
    <li>Promptly disclose any information that may affect a client decision.</li>
    <li>Maintain accurate records of all transactions and communications.</li>
</ol>

<h3>8. Breach &amp; Consequences</h3>
<p>Any breach of this Code of Conduct may result in:</p>
<ul>
    <li>Immediate suspension of the Associate account.</li>
    <li>Forfeiture of pending commissions and bonuses.</li>
    <li>Permanent termination of the association.</li>
    <li>Legal action for damages, including recovery of any losses caused to the Company.</li>
    <li>Criminal prosecution where applicable under Indian law.</li>
</ul>

<h3>9. Governing Law &amp; Jurisdiction</h3>
<p>This Code of Conduct is governed by the laws of India. Any legal disputes shall be subject to the exclusive jurisdiction of the competent courts in <strong>Gorakhpur, Uttar Pradesh</strong>.</p>
HTML;

        $stmt = $pdo->prepare("INSERT INTO pages (title, slug, content, meta_description, meta_keywords, status, tenant_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, 1, NOW(), NOW())");
        $stmt->execute([
            'Associate Code of Conduct',
            'associate-rules',
            $content,
            'APS Dream Home Associate Code of Conduct - professional standards, anti-corruption, confidentiality, RERA compliance, and breach consequences.',
            'associate rules, code of conduct, real estate compliance, RERA, APS Dream Home',
            'published',
        ]);

        echo "[SUCCESS] Created 'associate-rules' page (id={$pdo->lastInsertId()}).\n";
    }

    // Verify terms-conditions page content quality
    $stmt = $pdo->prepare("SELECT content FROM pages WHERE slug = ? LIMIT 1");
    $stmt->execute(['terms-conditions']);
    $row = $stmt->fetch();
    if ($row) {
        $content = $row['content'];
        $checks = [
            'Tripartite Master Deed' => stripos($content, 'tripartite') !== false,
            'EMI default penalty'    => stripos($content, '18%') !== false,
            '180-day refund'         => stripos($content, '180') !== false,
            'Gorakhpur jurisdiction' => stripos($content, 'gorakhpur') !== false,
            'Anti-defamation'        => stripos($content, 'defamation') !== false || stripos($content, 'defamatory') !== false,
        ];
        echo "\n[VERIFY] terms-conditions content checks:\n";
        foreach ($checks as $label => $ok) {
            echo "  " . ($ok ? "[PASS]" : "[MISSING]") . " {$label}\n";
        }
    }

    // Verify refund-policy page content quality
    $stmt = $pdo->prepare("SELECT content FROM pages WHERE slug = ? LIMIT 1");
    $stmt->execute(['refund-policy']);
    $row = $stmt->fetch();
    if ($row) {
        $content = $row['content'];
        $checks = [
            '25% deduction (1yr)'     => strpos($content, '25%') !== false,
            '10% deduction (1-3yr)'   => strpos($content, '10%') !== false,
            '180-day cycle'           => strpos($content, '180') !== false,
            'Document surrender'      => stripos($content, 'surrender') !== false || stripos($content, 'original') !== false,
            'Signature block'         => stripos($content, 'signature') !== false,
        ];
        echo "\n[VERIFY] refund-policy content checks:\n";
        foreach ($checks as $label => $ok) {
            echo "  " . ($ok ? "[PASS]" : "[MISSING]") . " {$label}\n";
        }
    }

    // List all published pages
    echo "\n[PAGES] All CMS pages:\n";
    $stmt = $pdo->query("SELECT id, title, slug, status FROM pages ORDER BY id");
    while ($row = $stmt->fetch()) {
        echo "  [{$row['id']}] {$row['title']} ({$row['slug']}) -- {$row['status']}\n";
    }

} catch (PDOException $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
